<?php

use Automattic\WooCommerce\Utilities\OrderUtil;

class Coursesource_Woocommerce_Settings
{

    const SETTING_API_KEY = 'api_key';

    const SETTING_API_ENDPOINT_BASE = 'api_endpoint';

    const SETTING_API_URL_DEV = 'https://beta-api.course-source.net';

    const SETTING_API_URL_LIVE = 'https://api.course-source.net';

    const SETTING_API_SITE_ID = 'siteid';

    const SETTING_SKU_PREFIX = 'sku_prefix';

    const SETTING_IMPORT_PRODUCT_IMAGES = false;

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
        add_action('woocommerce_settings_tabs_coursesource_settings_tab', __CLASS__ . '::settings_tab');
        add_action('woocommerce_update_options_coursesource_settings_tab', __CLASS__ . '::update_settings');
    }

    public static function add_filters()
    {
        add_filter('woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 9999);
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
        woocommerce_admin_fields(self::get_settings());
    }

    /**
     * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
     *
     * @uses woocommerce_update_options()
     * @uses self::get_settings()
     */
    public static function update_settings()
    {
        delete_transient( Coursesource_Api::TRANSIENT_CORESOURCE_COURSES );
        delete_transient( Coursesource_Api::TRANSIENT_CORESOURCE_VENDORS );
        woocommerce_update_options(self::get_settings());
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
                'desc_tip' => __('A helpful hint', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_API_SITE_ID,
            ),

            'api_key' => array(
                'name' => __('API Key', 'coursesource'),
                'type' => 'text',
                'default' => null,
                'desc_tip' => __('A helpful hint', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_API_KEY,
            ),

            'api_endpoint' => array(
                'name' => __('API Endpoint', 'coursesource'),
                'desc_tip' => __('A helpful hint', 'coursesource'),
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
                'desc_tip' => __('A helpful hint', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_SKU_PREFIX,
            ),
            'product_images_import' => array(
                'name' => __('Import Product Images', 'coursesource'),
                'type' => 'checkbox',
                'default' => 'no',
                'desc' => __('Note: Enabling this will significantly increase the time required to import products from Course-Source', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_IMPORT_PRODUCT_IMAGES,
            ),
            'section_title_products_end' => array(
                'type' => 'sectionend',
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . '_product_settings',
            ),

            'section_title_attribute_labels' => array(
                'name' => __('Attribute Settings', 'coursesource'),
                'type' => 'title',
                'desc' => __('Note: Changing these settings will not affect existing products - they must be re-imported to update the attribute names', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . '_attribute_labels_settings',
            ),
            'attribute_labels_training_duration' => array(
                'name' => __('Training Duration attribute label', 'coursesource'),
                'type' => 'text',
                'default' => 'Training Duration',
                'desc_tip' => __('A helpful hint', 'coursesource'),
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_ATTRIBUTE_NAME_TRAINING_LENGTH,
            ),
            'attribute_labels_publisher' => array(
                'name' => __('Publisher attribute label', 'coursesource'),
                'type' => 'text',
                'default' => 'Publisher',
                'desc_tip' => __('A helpful hint', 'coursesource'),
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

            'scheduled_updates_price' => array(
                'name' => __('Update product prices', 'coursesource'),
                'type' => 'checkbox',
                'desc' => __('Allow replacing existing Product prices with the price set in Course-Source', 'coursesource'),
                'default' => 'no',
                'id' => COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_SCHEDULED_UPDATES_PRICE,
            ),

            'scheduled_updates_frequency' => array(
                'name' => __('Update product schedule', 'coursesource'),
                'type' => 'select',
                'options'     => array(
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
                'desc_tip' => __('A helpful hint', 'coursesource'),
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
        return get_option(COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_API_KEY);
    }

    public static function getSiteId()
    {
        return get_option(COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_API_SITE_ID);
    }

    public static function getApiEndpointBase()
    {
        return get_option(COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_API_ENDPOINT_BASE);
    }

    /**
     * @return string
     */
    public static function getApiEndpoint()
    {
        $base = get_option(COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_API_ENDPOINT_BASE);
        $site_id = get_option(COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_API_SITE_ID);
        return "{$base}/{$site_id}/jsonrpc.php";
    }

    /**
     * @return null
     */
    public static function getProductSkuPrefix()
    {
        $option = get_option(COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_SKU_PREFIX);
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
        $option = get_option(COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_ATTRIBUTE_NAME_TRAINING_LENGTH);
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
        $option = get_option(COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_ATTRIBUTE_NAME_PUBLISHER);
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
        if ('yes' == get_option(COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_ERRORS_LOGGING)) {
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
            return get_option(COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_ERRORS_EMAIL);
        }
        return null;
    }


    /**
     * @return string|null
     */
    public static function getAPIMode()
    {
        $mode = null;
        $api_url = get_option(COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_API_ENDPOINT_BASE);
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
        if ('yes' == get_option(COURSESOURCE_OPTION_NAME_PREFIX . self::SETTING_IMPORT_PRODUCT_IMAGES)) {
            return true;
        }
        return false;
    }


    /**
     * @return bool
     */
    public static function isAPIModeLive()
    {
        if( 'live' === self::getAPIMode() ){
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public static function isAPIModeDev()
    {
        if( 'dev' === self::getAPIMode() ){
            return true;
        }
        return false;
    }


    /**
     * @return bool
     */
    public static function isStoreusingHighPerformanceOrderEngine() {

        if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
            return true;
        }
        return false;
    }


}

Coursesource_Woocommerce_Settings::init();
