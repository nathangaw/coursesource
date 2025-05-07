<?php

namespace Coursesource\Woocommerce\Woo\Product;
use Coursesource\Woocommerce\Coursesource\Api;
use Coursesource\Woocommerce\Coursesource\Helper;
use Coursesource\Woocommerce\Settings;
use Coursesource\Woocommerce\Woo\Product;

class Cron
{

    const CRON_TASK_PREFIX = 'woocommerce_coursesource_product_cron_';


    public function __construct()
    {
        $this->hooks();
        $this->init();
    }

    public static function hooks()
    {
        register_activation_hook( COURSESOURCE_PLUGIN_FILE, array('\Coursesource\Woocommerce\Woo\Product\Cron', 'activate_cron' ) );
        register_deactivation_hook( COURSESOURCE_PLUGIN_FILE, array('\Coursesource\Woocommerce\Woo\Product\Cron', 'deactivate_cron' ) );
    }

    public function init()
    {
        //Add the method to run when the cron task runs
        add_action( self::CRON_TASK_PREFIX . 'update_products', array( $this, 'update_products' ));
        $cron_enabled_option_name = COURSESOURCE_OPTION_NAME_PREFIX .Settings::SETTING_SCHEDULED_UPDATES_ENABLED;
        \add_action("update_option_{$cron_enabled_option_name}", __CLASS__ . '::schedule_cron', 10, 3);
        $cron_frequency_option_name = COURSESOURCE_OPTION_NAME_PREFIX .Settings::SETTING_SCHEDULED_UPDATES_FREQUENCY;
        \add_action("update_option_{$cron_frequency_option_name}", __CLASS__ . '::reschedule_cron', 10, 3);
    }


    /**
     * Updates Course-Source products
     */
    public function update_products()
    {
        if( Settings::isScheduledProductUpdateEnabled() ) {
            $logger = new \WC_Logger();
            $logger->info('Starting  Product Update cron task');
            $ids_array = [];
            $product_ids = Product::get_coursesource_product_ids();
            if( count( $product_ids ) > 0 ) {
                $api = new Api();
                $vendors = $api->getVendors();
                foreach ($product_ids as $product_id) {
                    $product = \wc_get_product( $product_id );
                    if( $product ) {
                        $course_id = $product->get_attribute('CourseID');
                        if( is_numeric( $course_id ) ) {
                            $import_data = Helper::parseCourseDataForProductImport( $course_id, $api, $vendors );
                            if( $import_data ) {
                                $product_data = Product::add_product_from_course($import_data);
                                if ( empty( $product_data['error'] ) ) {
                                    $ids_array = $product_data['id'];
                                }
                            }
                        }
                    }
                    sleep(1);
                }
            }

            $ids_string = implode( ',', $ids_array );
            $logger->info(  "Updated Product IDs: {$ids_string}");
            $logger->info( 'Ending Product Update cron task');
        }
    }

    /**
     * Add registered cron jobs when plugin activated
     */
    public static function activate_cron()
    {
        //Use wp_next_scheduled to check if the event is already scheduled
        $timestamp = wp_next_scheduled( self::CRON_TASK_PREFIX . 'update_products' );
        //If $timestamp == false add cron task as it's not currently queued...
        if( $timestamp == false ){
            $cron_frequency = Settings::getScheduledProductUpdateFrequency();
            wp_schedule_event( time(), $cron_frequency, self::CRON_TASK_PREFIX . 'update_products' );
        }
    }


    /**
     * Removes registered cron jobs when plugin deactivated
     */
    public static function deactivate_cron(){
        wp_clear_scheduled_hook( self::CRON_TASK_PREFIX . 'update_products' );
    }


    /**
     * @param $value
     * @param $old_value
     * @param $option
     * @return void
     */
    public static function schedule_cron( $old_value, $value, $option ){
        if( $value != $old_value ){
            if( Settings::isScheduledProductUpdateEnabled() ){
                self::activate_cron();
            }else{
                self::deactivate_cron();
            }
        }
    }


    /**
     * @param $value
     * @param $old_value
     * @param $option
     * @return void
     */
    public static function reschedule_cron( $old_value, $value, $option ){
        if( $value != $old_value ){
            self::deactivate_cron();
            if( Settings::isScheduledProductUpdateEnabled() ){
                self::activate_cron();
            }
        }
    }

}