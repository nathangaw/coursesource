<?php

namespace Coursesource\Woocommerce;

use Automattic\WooCommerce\Utilities\OrderUtil;

class Settings
{

    const SETTING_API_KEY = 'api_key';

    const SETTING_API_ENDPOINT_BASE = 'api_endpoint';

    const SETTING_API_URL_DEV = 'https://beta-api.course-source.net';

    const SETTING_API_URL_LIVE = 'https://api.course-source.net';

    const SETTING_API_SITE_ID = 'siteid';

    const SETTING_PORTAL_URL = 'portal_url';

    const SETTING_MYCOURSES_ID = 'mycourses_id';

    const SETTING_SKU_PREFIX = 'sku_prefix';

    const SETTING_IMPORT_PRODUCT_IMAGES = 'import_product_images';

    const SETTING_IMPORT_PRODUCT_PRICE = 'import_product_price';

    const SETTING_IMPORT_PRODUCT_SKU = 'import_product_sku';

    const SETTING_IMPORT_PRODUCT_TITLE = 'import_product_title';

    const SETTING_IMPORT_PRODUCT_DESC = 'import_product_desc';

    const SETTING_ATTRIBUTE_NAME_TRAINING_LENGTH = 'attribute_name_HoursOfTraining';

    const SETTING_ATTRIBUTE_NAME_PUBLISHER = 'attribute_name_Publisher';

    const SETTING_SCHEDULED_UPDATES_ENABLED = 'scheduled_updates_enabled';

    const SETTING_SCHEDULED_UPDATES_FREQUENCY = 'scheduled_updates_frequency';

    const SETTING_SCHEDULED_UPDATES_PRICE = 'scheduled_updates_price';

    const SETTING_ERRORS_LOGGING = 'error_logging_enabled';

    const SETTING_ERRORS_EMAIL = 'error_logging_email';

    /**
     * Bootstraps the class and hooks required actions & filters.
     *
     */
    public static function init()
    {
        self::add_actions();
        self::add_filters();
    }

    public static function add_actions()
    {
        \add_action('woocommerce_settings_tabs_coursesource_settings_tab', __CLASS__ . '::settings_tab');
        \add_action('woocommerce_update_options_coursesource_settings_tab', __CLASS__ . '::update_settings');
    }

    public static function add_filters()
    {
        \add_filter('woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 9999);
    }


    /**
     * Add a new settings tab to the WooCommerce settings tabs array.
     *
     * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
     */
    public static function add_settings_tab($settings_tabs)
    {
        $settings_tabs['coursesource_settings_tab'] = __('Course-Source', 'coursesource');
        return $settings_tabs;
    }

    /**
     * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
     *
     * @uses woocommerce_admin_fields()
     * @uses self::get_settings()
     */
    public static function settings_tab()
    {
        \woocommerce_admin_fields(self::get_settings());
    }

    /**
     * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
     *
     * @uses woocommerce_update_options()
     * @uses self::get_settings()
     */
    public static function update_settings()
    {
        \delete_transient(Coursesource\Api::TRANSIENT_CORESOURCE_COURSES);
        \delete_transient(Coursesource\Api::TRANSIENT_CORESOURCE_VENDORS);
        \woocommerce_update_options(self::get_settings());
    }

    /**
     * Get all the settings for this plugin for @return array Array of settings for @see woocommerce_admin_fields() function.
     * @see woocommerce_admin_fields() function.
     *
     */
    public static function get_settings()
    {

        $settings = array(
            'section_title_api' => array(
                'name' => __('API Settings', 'coursesource'),
                'type' => 'title',
                'desc' => __('Please contact Course-Source.com to get your API Key, Site ID & the API Endpoint to use.', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . '_api_settings',
            ),

            'site_id' => array(
                'name' => __('Site ID', 'coursesource'),
                'type' => 'text',
                'default' => null,
                //'desc_tip' => __('A helpful hint', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_API_SITE_ID,
            ),

            'api_key' => array(
                'name' => __('API Key', 'coursesource'),
                'type' => 'text',
                'default' => null,
                //'desc_tip' => __('A helpful hint', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_API_KEY,
            ),

            'api_endpoint' => array(
                'name' => __('API Endpoint', 'coursesource'),
                //'desc_tip' => __('A helpful hint', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_API_ENDPOINT_BASE,
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'options' => array(
                    self::SETTING_API_URL_DEV => __('Development (' . self::SETTING_API_URL_DEV . ')', 'coursesource'),
                    self::SETTING_API_URL_LIVE => __('Live (' . self::SETTING_API_URL_LIVE . ')', 'coursesource'),
                ),
            ),

            'section_title_api_end' => array(
                'type' => 'sectionend',
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . '_api_settings',
            ),

            'section_title_store' => array(
                'name' => __('Course Source Portal', 'coursesource'),
                'type' => 'title',
                'desc' => __('If your customers access their courses on a Course-Source portal, enter the url below ', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . '_portal_settings',
            ),

            'portal_endpoint' => array(
                'name' => __('Learning Portal Url', 'coursesource'),
                'type' => 'text',
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_PORTAL_URL,
                'default' => null,
            ),

            'section_title_store_end' => array(
                'type' => 'sectionend',
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . '_portal_settings',
            ),

            'section_title_myaccount' => array(
                'name' => __('My Account', 'coursesource'),
                'type' => 'title',
                'desc' => __('Enter the ID to the page that contains the [cs_my_courses_table] shortcode', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . '_myaccount_settings',
            ),

            'mycourses_id' => array(
                'name' => __('My Courses Page ID', 'coursesource'),
                'type' => 'text',
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_MYCOURSES_ID,
                'default' => null,
            ),

            'section_title_myaccount_end' => array(
                'type' => 'sectionend',
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . '_myaccount_settings',
            ),

            'section_title_products' => array(
                'name' => __('Product Settings', 'coursesource'),
                'type' => 'title',
                'desc' => __('Warning! Changing your Product\'s SKU prefix may result in Courses no longer being recognised', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . '_product_settings',
            ),
            'product_sku_prefix' => array(
                'name' => __('Custom SKU Prefix', 'coursesource'),
                'type' => 'text',
                'default' => 'CS',
                //'desc_tip' => __('A helpful hint', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_SKU_PREFIX,
            ),
            'section_title_products_end' => array(
                'type' => 'sectionend',
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . '_product_settings',
            ),

            'section_title_product_import' => array(
                'name' => __('Product Import Settings', 'coursesource'),
                'type' => 'title',
                'desc' => __('Configure how existing product data will treated when importing, linking or synchronising Courses from Course-Source', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . '_product_settings',
            ),
            'product_import_images' => array(
                'name' => __('Import Product Images', 'coursesource'),
                'type' => 'checkbox',
                'default' => 'no',
                'desc' => __('Note: Enabling this will significantly increase the time required to import products from Course-Source', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_IMPORT_PRODUCT_IMAGES,
            ),
            'product_import_sku' => array(
                'name' => __('Update product SKU', 'coursesource'),
                'type' => 'checkbox',
                'default' => 'no',
                'desc' => __('Product SKU will be replaced on import/synchronisation', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_IMPORT_PRODUCT_SKU,
            ),
            'product_import_price' => array(
                'name' => __('Update product price', 'coursesource'),
                'type' => 'checkbox',
                'default' => false,
                'desc' => __('Product Price will be replaced on import/synchronisation', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_IMPORT_PRODUCT_PRICE,
            ),
            'product_import_title' => array(
                'name' => __('Update product name', 'coursesource'),
                'type' => 'checkbox',
                'default' => 'no',
                'desc' => __('Product Name will be replaced on import/synchronisation', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_IMPORT_PRODUCT_TITLE,
            ),
            'product_import_desc' => array(
                'name' => __('Update product description', 'coursesource'),
                'type' => 'checkbox',
                'default' => 'no',
                'desc' => __('Product Description will be replaced on import/synchronisation', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_IMPORT_PRODUCT_DESC,
            ),
            'section_title_product_import_end' => array(
                'type' => 'sectionend',
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . '_product_settings',
            ),

            'section_title_attribute_labels' => array(
                'name' => __('Attribute Settings', 'coursesource'),
                'type' => 'title',
                'desc' => __('Note: Changing these settings will not affect existing products - they must be resynchronised to update the attribute names', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . '_attribute_labels_settings',
            ),
            'attribute_labels_training_duration' => array(
                'name' => __('Training Duration attribute label', 'coursesource'),
                'type' => 'text',
                'default' => 'Training Duration',
                //'desc_tip' => __('A helpful hint', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_ATTRIBUTE_NAME_TRAINING_LENGTH,
            ),
            'attribute_labels_publisher' => array(
                'name' => __('Publisher attribute label', 'coursesource'),
                'type' => 'text',
                'default' => 'Publisher',
                //'desc_tip' => __('A helpful hint', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_ATTRIBUTE_NAME_PUBLISHER,
            ),
            'section_title_attribute_labels_end' => array(
                'type' => 'sectionend',
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . '_attribute_labels_settings',
            ),

            'section_title_scheduled' => array(
                'name' => __('Scheduled Update Settings', 'coursesource'),
                'type' => 'title',
                'desc' => __('Automatically update existing Products by importing changes regularly from Course-Source', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . '_scheduled_settings',
            ),

            'scheduled_updates_enabled' => array(
                'name' => __('Update products automatically', 'coursesource'),
                'type' => 'checkbox',
                'default' => 'no',
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_SCHEDULED_UPDATES_ENABLED,
            ),

            'scheduled_updates_frequency' => array(
                'name' => __('Update product schedule', 'coursesource'),
                'type' => 'select',
                'options' => array(
                    'hourly' => 'Hourly',
                    'daily' => 'Daily',
                    'weekly' => 'Weekly',
                    'monthly' => 'Monthly',
                ),
                'default' => 'daily',
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_SCHEDULED_UPDATES_FREQUENCY,
            ),

            'section_title_scheduled_end' => array(
                'type' => 'sectionend',
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . '_scheduled_settings',
            ),

            'section_title_errors' => array(
                'name' => __('Error Logging Settings', 'coursesource'),
                'type' => 'title',
                'desc' => __('Enabling these options will allow more comprehensive debugging', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . '_error_settings',
            ),
            'error_reporting_enabled' => array(
                'name' => __('Log errors to a file', 'coursesource'),
                'type' => 'checkbox',
                'default' => 'no',
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_ERRORS_LOGGING,
            ),
            'error_reporting_email' => array(
                'name' => __('Email recipient for errors', 'coursesource'),
                'type' => 'text',
                'default' => null,
                //'desc_tip' => __('A helpful hint', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_ERRORS_EMAIL,
            ),
            'section_title_errors_end' => array(
                'type' => 'sectionend',
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . '_error_settings',
            ),

        );

        return apply_filters('coursesource_wc_settings_tab_settings', $settings);
    }

    /**
     * @return false|mixed|null
     */
    public static function getApiKey()
    {
        return self::get_option( self::SETTING_API_KEY);
    }

    public static function getSiteId()
    {
        return self::get_option( self::SETTING_API_SITE_ID);
    }

    public static function getApiEndpointBase()
    {
        return self::get_option( self::SETTING_API_ENDPOINT_BASE);
    }

    /**
     * @return string
     */
    public static function getApiEndpoint()
    {
        $base = self::get_option( self::SETTING_API_ENDPOINT_BASE);
        $site_id = self::get_option( self::SETTING_API_SITE_ID);
        return "{$base}/{$site_id}/jsonrpc.php";
    }

    /**
     * @return bool
     */
    public static function getPortalUrl()
    {
        $url = self::get_option( self::SETTING_PORTAL_URL);
        if (!empty($url)) {
            return $url;
        }
        return false;
    }

    /**
     * @return int|false
     */
    public static function getMyCoursesUrl()
    {
        $id = get_option(COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_MYCOURSES_ID);
        if (!empty($id)) {
            return get_permalink( $id );
        }
        return false;
    }


    /**
     * @return null
     */
    public static function getProductSkuPrefix()
    {
        $option = self::get_option( self::SETTING_SKU_PREFIX);
        if (empty($option)) {
            $option = 'CS';
        }
        return $option;
    }


    /**
     * @return null
     */
    public static function getAttributeNameTrainingDuration()
    {
        $option = self::get_option( self::SETTING_ATTRIBUTE_NAME_TRAINING_LENGTH);
        if (empty($option)) {
            $option = 'Training';
        }
        return $option;
    }


    /**
     * @return null
     */
    public static function getAttributeNamePublisher()
    {
        $option = self::get_option( self::SETTING_ATTRIBUTE_NAME_PUBLISHER);
        if (empty($option)) {
            $option = 'Publisher';
        }
        return $option;
    }

    /**
     * @return bool
     */
    public static function getErrorReportingEnabled()
    {
        if ('yes' === self::get_option( self::SETTING_ERRORS_LOGGING)) {
            return true;
        }

        return false;
    }

    /**
     * @return string|null
     */
    public static function getErrorEmailRecipient()
    {
        if (self::getErrorReportingEnabled()) {
            return self::get_option( self::SETTING_ERRORS_EMAIL);
        }
        return null;
    }


    /**
     * @return string|null
     */
    public static function getAPIMode()
    {
        $mode = null;
        $api_url = self::get_option( self::SETTING_API_ENDPOINT_BASE);
        if ($api_url == self::SETTING_API_URL_DEV) {
            $mode = "dev";
        } elseif ($api_url == self::SETTING_API_URL_LIVE) {
            $mode = "live";
        }
        return $mode;
    }


    /**
     * @return bool
     */
    public static function isProductImageImportEnabled()
    {
        if ('yes' === self::get_option( self::SETTING_IMPORT_PRODUCT_IMAGES)) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public static function isProductSkuReplacedOnImport()
    {
        if ('yes' === self::get_option( self::SETTING_IMPORT_PRODUCT_SKU)) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public static function isProductPriceReplacedOnImport()
    {
        if ('yes' === self::get_option( self::SETTING_IMPORT_PRODUCT_PRICE)) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public static function isProductTitleReplacedOnImport()
    {
        if ('yes' === self::get_option( self::SETTING_IMPORT_PRODUCT_TITLE)) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public static function isProductDescriptionReplacedOnImport()
    {
        if ('yes' === self::get_option( self::SETTING_IMPORT_PRODUCT_DESC)) {
            return true;
        }
        return false;
    }


    /**
     * @return bool
     */
    public static function isAPIModeLive()
    {
        if ('live' === self::getAPIMode()) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public static function isAPIModeDev()
    {
        if ('dev' === self::getAPIMode()) {
            return true;
        }
        return false;
    }


    /**
     * @return bool
     */
    public static function isStoreusingHighPerformanceOrderEngine()
    {
        if (OrderUtil::custom_orders_table_usage_is_enabled()) {
            return true;
        }
        return false;
    }


    /**
     * @return bool
     */
    public static function isScheduledProductUpdateEnabled()
    {
        if ('yes' === self::get_option( self::SETTING_SCHEDULED_UPDATES_ENABLED)) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public static function getScheduledProductUpdateFrequency()
    {
        $frequency = self::get_option( self::SETTING_SCHEDULED_UPDATES_FREQUENCY);
        if( $frequency ) {
            return $frequency;
        }
        return "hourly";
    }

    public static function get_option( $name )
    {
        return \get_option( COURSESOURCE_OPTION_NAME_PREFIX . $name );

    }


}