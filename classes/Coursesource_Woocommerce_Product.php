<?php

class Coursesource_Woocommerce_Product
{

    /**
     * Get the IDs of any Coursesource products
     * @return array
     */
    public static function get_coursesource_product_ids()
    {
        global $wpdb;
        return $wpdb->get_col('SELECT post_id FROM ' . $wpdb->prefix . 'postmeta WHERE meta_key="_is_coursesource" AND meta_value=1');
    }


    /**
     * @TODO Surely we should refactor this to use native WooCommmerce methods to insert/update products?
     *
     * @param $data
     *
     * @return array
     */
    public static function add_product_from_course($data)
    {
        // Do we already have this product SKU? This only works if you add products via new WC_Product()
        $post_id = wc_get_product_id_by_sku($data['sku']);

        if ($post_id === 0) {
            $product = new WC_Product_Simple();
        } else {
            $product = wc_get_product($post_id);
        }

        $product->set_sku($data['sku']);
        $product->set_name($data['title']);
        $product->set_description((string)$data['desc']);
        $product->set_status('publish');
        $product->set_price($data['price']);
        $product->set_regular_price($data['price']);
        $product->set_stock_status('instock');
        $product->set_catalog_visibility('visible');
        $product->set_downloadable(false);
        $product->set_virtual(true);
        $product->set_featured(false);
        $product->set_sold_individually(false);
        $product->set_manage_stock(false);
        $product->set_backorders(false);
        $product->save();

        $product_attributes = [];
        // Add attributes
        if (!empty($data['attributes'])) {
            foreach ($data['attributes'] as $name => $value) {
                $product_attributes[] = array(
                    'name' => $name, // set attribute name
                    'value' => $value, // set attribute value
                    'position' => 1,
                    'is_visible' => 1,
                    'is_variation' => 0,
                    'is_taxonomy' => 0
                );
            }
        }
        if (!empty($data['hidden_attributes'])) {
            foreach ($data['hidden_attributes'] as $name => $value) {
                $product_attributes[] = array(
                    'name' => $name, // set attribute name
                    'value' => $value, // set attribute value
                    'position' => 1,
                    'is_visible' => 0,
                    'is_variation' => 0,
                    'is_taxonomy' => 0
                );
            }
        }

        if (!empty($data['filterable_attributes'])) {
            foreach ($data['filterable_attributes'] as $name => $value) {
                $tax = wc_attribute_taxonomy_name($name);
                $slug = wc_sanitize_taxonomy_name($name);

                if (empty(taxonomy_exists($tax))) {
                    self::save_product_attribute_from_name($slug, $name);
                }

                if (empty(term_exists($value, $tax))) {
                    $insert = wp_insert_term($value, $tax, [
                            'slug' => sanitize_title($value)
                        ]
                    );
                }
                wp_set_post_terms($product->get_id(), $value, $tax, true);

                $product_attributes[] = array(
                    'name' => $tax, // set attribute name
                    'value' => '', // set attribute value
                    'position' => 1,
                    'is_visible' => 1,
                    'is_variation' => 0,
                    'is_taxonomy' => 1
                );
            }
        }

        update_post_meta($product->get_id(), '_product_attributes', $product_attributes);
        update_post_meta($product->get_id(), '_is_coursesource', true);

        if (Coursesource_Woocommerce_Settings::isProductImageImportEnabled()) {
            self::set_course_featured_image($data['sku'], $product);
            self::set_course_images($data['sku'], $product);
        }

        return [
            'success' => $product->get_id()
        ];
    }


    /**
     * Add a Product Attribute type by name
     *
     * @param $slug
     * @param $label
     * @param $set
     *
     * @return void
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
        $attribute_id = wc_attribute_taxonomy_id_by_name($slug);
        if ($attribute_id > 0) {
            $args['id'] = $attribute_id;
        }
        wc_create_attribute($args);
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
        if (!wp_http_validate_url($url)) {
            return new WP_Error('invalid_url', 'File URL is invalid', array('status' => 400));
        }

        // Gives us access to the download_url() and media_handle_sideload() functions.
        if (!function_exists('download_url') || !function_exists('media_handle_sideload')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }

        // Download file to temp dir.
        $temp_file = download_url($url);

        // if the file was not able to be downloaded
        if (is_wp_error($temp_file)) {
            return $temp_file;
        }

        // An array similar to that of a PHP `$_FILES` POST array
        $file_url_path = parse_url($url, PHP_URL_PATH);
        $file_info = wp_check_filetype($file_url_path);
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
            $attachment_id = media_handle_sideload($file, $post_id, $desc, $post_data);
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
        $post = get_post($post_id);
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $post->post_date);
        $date_path = $date->format("Y/m");
        $filename = sanitize_file_name($filename);
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
        return str_replace(Coursesource_Woocommerce_Settings::getProductSkuPrefix(), '', $sku);
    }

    /**
     * @param $sku
     * @return mixed
     */
    public static function get_course_image_url($sku)
    {
        // Now set the Product image...
        $api = new Coursesource_Api();
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
     * @param WC_Product $product
     * @return void
     */
    public static function set_course_images($sku, $product)
    {
        // Now set the Product images
        $coursesource_sku = self::get_original_coursesource_sku($sku);
        $api = new Coursesource_Api();
        $courseImages = $api->getCourseImages($coursesource_sku);
        if (count($courseImages) > 0) {
            $product_image_ids = [];
            foreach ($courseImages as $courseImage) {
                $product_image_id = self::add_remote_url_to_media_library($courseImage->URL, $product->get_id() );
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
     * @param WC_Product $product
     * @return bool|int|null
     */
    public static function set_course_featured_image($sku, WC_Product $product)
    {
        $product_image_url = self::get_course_image_url($sku);
        if (!empty($product_image_url)) {
            return self::set_course_image_as_featured($product_image_url, $product );
        }
        return null;;
    }


    /**
     *
     * @param $url
     * @param WC_Product $product
     * @return bool|int
     */
    public static function set_course_image_as_featured($url, WC_Product $product)
    {
        $attachment_id = self::add_remote_url_to_media_library($url, $product->get_id());
        if ($attachment_id > 0) {
            $product->set_image_id($attachment_id);
            $product->save();
        }
        return null;
    }


}
