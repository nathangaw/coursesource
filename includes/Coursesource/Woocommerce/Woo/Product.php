<?php

namespace Coursesource\Woocommerce\Woo;

use Coursesource\Woocommerce\Coursesource\Api;
use Coursesource\Woocommerce\Settings;
use WC_Product_Simple;

class Product
{

    public const IS_COURSESOURCE_META_NAME = '_is_coursesource';

    public const COURSESOURCE_LAST_UPDATED_META_NAME = '_coursesource_last_updated';

    /**
     * Get the IDs of any Coursesource products
     * @return array
     */
    public static function get_coursesource_product_ids()
    {
        global $wpdb;
        return $wpdb->get_col('SELECT post_id FROM ' . $wpdb->prefix . 'postmeta WHERE meta_key="' . self::IS_COURSESOURCE_META_NAME . '" AND meta_value=1');

        // The problem with this is that products without a last updated meta value are not returned...
        $args = [
            'post_type' => 'product',
            'fields' => 'ids',
            'per_page' => -1,
            'meta_query' => [
                [
                    'key' => self::IS_COURSESOURCE_META_NAME,
                    'value' => 1,
                    'compare' => '=',
                ],
            ],
            'meta_key' => self::COURSESOURCE_LAST_UPDATED_META_NAME,
            'orderby' => [
                'meta_value_num' => 'DESC'
            ],
        ];
        $query = new \WP_Query($args);
        return $query->get_posts();
    }


    /**
     * @TODO Surely we should refactor this to use native WooCommerce methods to insert/update products?
     *
     * @param $data
     *
     * @return array
     */
    public static function add_product_from_course($data)
    {
        $is_new_coursesource_product = true;
        // Do we already have this product SKU? This only works if you add products via new \WC_Product()
        if ($post_id = self::isPreviouslyImportedCoursesourceProduct($data)) {
            $product = \wc_get_product($post_id);
            $is_new_coursesource_product = false;
        } else {
            $product = new \WC_Product_Simple();
        }
        // Selectively update specific product data...
        if ($is_new_coursesource_product || Settings::isProductSkuReplacedOnImport()) {
            $product->set_sku($data['sku']);
        }
        if ($is_new_coursesource_product || Settings::isProductTitleReplacedOnImport()) {
            $product->set_name($data['title']);
        }
        if ($is_new_coursesource_product || Settings::isProductDescriptionReplacedOnImport()) {
            $product->set_description($data['desc']);
        }
        if ($is_new_coursesource_product || Settings::isProductPriceReplacedOnImport()) {
            $product->set_price($data['price']);
            $product->set_regular_price($data['price']);
        }
        $product->set_status('publish');
        $product->set_stock_status('instock');
        $product->set_catalog_visibility('visible');
        $product->set_downloadable(false);
        $product->set_virtual(true);
        $product->set_featured(false);
        $product->set_sold_individually(false);
        $product->set_manage_stock(false);
        $product->set_backorders(false);
        $product->save();

        $product_attributes = $product->get_attributes('edit');

        // Add attributes
        if (!empty($data['attributes'])) {
            foreach ($data['attributes'] as $name => $value) {
                $attribute_data = [
                    'name' => $name,
                    'options' => [$value],
                    'visible' => true,
                ];
                $product_attributes = self::addAttributeToExistingAttributes( $product_attributes, $attribute_data );
            }
        }
        if (!empty($data['hidden_attributes'])) {
            foreach ($data['hidden_attributes'] as $name => $value) {
                $attribute_data = [
                    'name' => $name,
                    'options' => [$value],
                    'visible' => false,
                ];
                $product_attributes = self::addAttributeToExistingAttributes( $product_attributes, $attribute_data );
            }
        }

        if (!empty($data['filterable_attributes'])) {
            foreach ($data['filterable_attributes'] as $taxonomy_name => $term_name) {
                // Create the taxonomy based attribute if it does not already exist
                $slug = \wc_sanitize_taxonomy_name($taxonomy_name);
                $taxonomy_attr_name = \wc_attribute_taxonomy_name($taxonomy_name);
                $taxonomy_id = \wc_attribute_taxonomy_id_by_name($taxonomy_attr_name);
                if (!$taxonomy_id) {
                    $taxonomy_id = self::save_product_attribute_from_name($slug, $taxonomy_name);
                }

                if (empty(\term_exists($term_name, $taxonomy_attr_name))) {
                    $term_data = \wp_insert_term($term_name, $taxonomy_attr_name, [
                            'slug' => \sanitize_title($term_name)
                        ]
                    );
                }else{
                    $term_data = \get_term_by('slug', $term_name, $taxonomy_attr_name, ARRAY_A );
                }

                $attribute_data = [
                    'id' => $taxonomy_id,
                    'name' => $taxonomy_attr_name,
                    'options' => [$term_data['term_id']],
                    'visible' => true,
                ];
                $product_attributes = self::addAttributeToExistingAttributes( $product_attributes, $attribute_data );

            }
        }

        // So themes and plugins can optionally add additional data to the Coursesource product when importing...
        $product_attributes = \apply_filters('cs_product_attribute_before_save', $product_attributes, $product, $data );
        $product->set_attributes( $product_attributes);
        $product->save();
        // It really would be nice to get rid of these legacy postmeta methods...
        \update_post_meta($product->get_id(), self::IS_COURSESOURCE_META_NAME, true);
        \update_post_meta($product->get_id(), self::COURSESOURCE_LAST_UPDATED_META_NAME, time());

        if (Settings::isProductImageImportEnabled()) {
            self::set_course_featured_image($data['sku'], $product);
            self::set_course_images($data['sku'], $product);
        }

        return [
            'id' => $product->get_id(),
            'product' => $product,
        ];
    }

    protected static function addAttributeToExistingAttributes( array $product_attributes, $attribute_data )
    {
        $slug = sanitize_title( $attribute_data['name'] );
        $attribute_exists = isset( $product_attributes[$slug] ) ? true : false;
        $attribute = $product_attributes[$slug] ?? new \WC_Product_Attribute();
        if( isset( $attribute_data['id'] ) ){
            $attribute->set_id(  $attribute_data['id']  );
        }
        $attribute->set_name( $attribute_data['name'] );
        $attribute->set_options( $attribute_data['options'] );
        if( !$attribute_exists ){
            $number_existing_attributes = count( $product_attributes );
            $attribute->set_position( $number_existing_attributes + 1 );
            $attribute->set_visible( $attribute_data['visible'] );
            $attribute->set_variation(false);
            $product_attributes[$slug] = $attribute;
        }
        return $product_attributes;
    }


    /**
     * @param $slug
     * @param $label
     * @param $set
     * @return int|void|\WP_Error
     */
    public static function save_product_attribute_from_name($slug, $label = null, $set = true)
    {
        if (!function_exists('wc_attribute_taxonomy_id_by_name') || !function_exists('wc_create_attribute')) {
            return;
        }

        $args = array(
            'name' => $label,
            'slug' => $slug,
            'type' => 'select',
            'order_by' => 'menu_order',
            'has_archives' => false,
        );
        $attribute_id = \wc_attribute_taxonomy_id_by_name($slug);
        if ($attribute_id > 0) {
            return wc_update_attribute($attribute_id, $args);
        }
        return \wc_create_attribute($args);
    }


    /**
     * Downloads Course image from a remote url,add to WP Media, set Product featured image.
     * @param string $url HTTP URL address of a remote file
     * @param int $post_id The post ID the media is associated with
     * @param string $desc Description of the side-loaded file
     * @param string $post_data Post data to override
     *
     * @return int|WP_Error The ID of the attachment or a WP_Error on failure
     * @example $attachment_id = sideload( $url [, $post_id [, $desc [, $post_data]]] );
     * @see https://developer.wordpress.org/reference/functions/media_handle_sideload/
     *
     */
    public static function add_remote_url_to_media_library($url, $post_id = 0, $desc = null, $post_data = array())
    {
        //https://corelearningservices.test/wp-content/uploads/2024/02/Budget-Like-a-Boss-Screenshot-3-1.png

        $attachment_id = 0;
        // URL Validation
        if (!\wp_http_validate_url($url)) {
            return new WP_Error('invalid_url', 'File URL is invalid', array('status' => 400));
        }

        // Gives us access to the download_url() and media_handle_sideload() functions.
        if (!function_exists('download_url') || !function_exists('media_handle_sideload')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }

        // Download file to temp dir.
        $temp_file = \download_url($url);

        // if the file was not able to be downloaded
        if (\is_wp_error($temp_file)) {
            return $temp_file;
        }

        // An array similar to that of a PHP `$_FILES` POST array
        $file_url_path = parse_url($url, PHP_URL_PATH);
        $file_info = \wp_check_filetype($file_url_path);
        $file = array(
            'tmp_name' => $temp_file,
            'type' => $file_info['type'],
            'name' => basename($file_url_path),
            'size' => filesize($temp_file),
        );

        if (empty($post_data)) {
            $post_data = array();
        }

        // Should we provide a plugin option to allow people to re-add the same named file? Lotta bloat potentially?
        if (!self::is_image_already_uploaded($file['name'], $post_id)) {
            // Move the temporary file into the uploads directory.
            $attachment_id = \media_handle_sideload($file, $post_id, $desc, $post_data);
        }

        @unlink($temp_file);

        return $attachment_id;
    }

    /**
     * @param $filename
     * @param $post_id
     * @return bool
     */
    public static function is_image_already_uploaded($filename, $post_id)
    {
        $upload_directory = WP_CONTENT_DIR . "/uploads/";
        $post = \get_post($post_id);
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $post->post_date);
        $date_path = $date->format("Y/m");
        $filename = \sanitize_file_name($filename);
        $image_upload_path = $upload_directory . $date_path . "/" . $filename;
        if (file_exists($image_upload_path)) {
            return true;
        }
        return false;

    }

    /**
     *
     * @param $sku
     * @return array|string|string[]
     */
    public static function get_original_coursesource_sku($sku)
    {
        return str_replace(Settings::getProductSkuPrefix(), '', $sku);
    }

    /**
     * @param $sku
     * @return mixed
     */
    public static function get_course_image_url($sku)
    {
        // Now set the Product image...
        $api = new Coursesource\Api();
        $courses = $api->getCourseLibrary();
        $coursesource_sku = self::get_original_coursesource_sku($sku);
        // This should only return a single course...
        $filtered_courses = array_filter($courses, function ($course) use ($coursesource_sku) {
            return $course->CourseID == $coursesource_sku;
        });
        return reset($filtered_courses)->Image;
    }


    /**
     * @param string $sku
     * @param \WC_Product $product
     * @return void
     */
    public static function set_course_images($sku, $product)
    {
        // Now set the Product images
        $coursesource_sku = self::get_original_coursesource_sku($sku);
        $api = new Coursesource\Api();
        $courseImages = $api->getCourseImages($coursesource_sku);
        if (count($courseImages) > 0) {
            $product_image_ids = [];
            foreach ($courseImages as $courseImage) {
                $product_image_id = self::add_remote_url_to_media_library($courseImage->URL, $product->get_id());
                if ($product_image_id > 0) {
                    $product_image_ids[] = $product_image_id;
                }
            }
            // Setting the gallery image only works if the featured image is set...
            $featured_image = $product->get_image_id();
//            if( empty( $featured_image ) ){
//                $product->set_image_id( $product_image_ids[0] );
//                $product->save();
//            }
            if (count($product_image_ids) > 0) {
                $product->set_gallery_image_ids($product_image_ids);
                $product->save();
            }
        }
    }

    /**
     * @param $sku
     * @param \WC_Product $product
     * @return bool|int|null
     */
    public static function set_course_featured_image($sku, \WC_Product $product)
    {
        $product_image_url = self::get_course_image_url($sku);
        if (!empty($product_image_url)) {
            return self::set_course_image_as_featured($product_image_url, $product);
        }
        return null;;
    }


    /**
     *
     * @param $url
     * @param \WC_Product $product
     * @return bool|int
     */
    public static function set_course_image_as_featured($url, \WC_Product $product)
    {
        $attachment_id = self::add_remote_url_to_media_library($url, $product->get_id());
        if ($attachment_id > 0) {
            $product->set_image_id($attachment_id);
            $product->save();
        }
        return null;
    }

    /**
     * Get the SKUs of the currently imported Courses
     * @return array
     */
    public static function getAllProductSkus()
    {
        global $wpdb;
        $current_skus = [];
        $current_skus_sql = "SELECT post_id, meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key='_sku'";
        foreach ($wpdb->get_results($current_skus_sql) as $sku_row) {
            $current_skus[$sku_row->meta_value] = $sku_row->post_id;
        }
        return $current_skus;
    }


    /**
     * Get the CourseSourceIds of any currently imported Courses
     * @return array
     */
    public static function getAllProductsWithCoursesourceIDs()
    {
        global $wpdb;
        $course_ids = [];
        $current_skus_sql = "SELECT post_id, meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key='_product_attributes' AND meta_value LIKE('%CourseID%')";
        foreach ($wpdb->get_results($current_skus_sql) as $product_row) {
            $attributes = \maybe_unserialize($product_row->meta_value);
            if (is_array($attributes)) {
                foreach ($attributes as $attribute) {
                    if (($attribute['name'] == 'CourseID')) {
                        $course_ids[$product_row->post_id] = $attribute['value'];
                    }
                }
            }
        }
        return $course_ids;
    }


    /**
     * @param $data
     * @return int|false
     */
    public static function isPreviouslyImportedCoursesourceProduct($data)
    {
        // Do we already have this product SKU? This only works if we are using SKUs derived from prefixed Coursesource IDs
        $sku = $data['sku'];
        $post_id = \wc_get_product_id_by_sku($sku);
        if ($post_id !== 0) {
            return $post_id;
        }
        //Try to find a product with a corresponding postmeta?
        $coursesource_id = $data['coursesource_id'];
        global $wpdb;
        $current_skus_sql = "SELECT post_id, meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key='_product_attributes' AND meta_value LIKE('%{$coursesource_id}%')";
        foreach ($wpdb->get_results($current_skus_sql) as $product_row) {
            $attributes = \maybe_unserialize($product_row->meta_value);
            if (is_array($attributes)) {
                foreach ($attributes as $attribute) {
                    if (($attribute['name'] == 'CourseID') && ($attribute['value'] == $coursesource_id)) {
                        return $product_row->post_id;
                    }
                }
                return $product_row->post_id;
            }

        }
        return false;
    }


    /**
     * @param $product
     * @return bool
     */
    public static function isCoursesourceProduct($product)
    {
        $meta_key = self::IS_COURSESOURCE_META_NAME;
        $meta = $product->get_meta($meta_key);
        return (bool)$meta;
    }

}