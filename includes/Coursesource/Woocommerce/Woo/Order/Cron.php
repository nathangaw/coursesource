<?php

namespace Coursesource\Woocommerce\Woo\Order;


use Coursesource\Woocommerce\Woo\Order;

class Cron
{

    const CRON_TASK_PREFIX = 'woocommerce_coursesource_order_cron_';


    public function __construct()
    {
        $this->hooks();
        $this->init();
    }

    public static function hooks()
    {
        register_activation_hook( COURSESOURCE_PLUGIN_FILE, array( '\Coursesource\Woocommerce\Woo\Order\Cron', 'activate_cron' ) );
        register_deactivation_hook( COURSESOURCE_PLUGIN_FILE, array( '\Coursesource\Woocommerce\Woo\Order\Cron', 'deactivate_cron' ) );
    }

    public function init()
    {
        // Add a cron schedule to run every 5 mins
        add_filter( 'cron_schedules', array( $this, 'add_five_minute_cron_schedule') );
        add_filter( 'cron_schedules', array( $this, 'add_fifteen_minute_cron_schedule') );

        //Add the method to run when the cron task runs
        add_action( self::CRON_TASK_PREFIX . 'create_order_enrolment_keys', array( $this, 'create_order_enrolment_keys' ));
    }


    public function add_five_minute_cron_schedule( $schedules ) {
        $schedules['5mins'] = array(
            'interval' => 300, // 5mins in seconds
            'display'  => __( 'Every 5 mins' ),
        );
        return $schedules;
    }


    public function add_fifteen_minute_cron_schedule( $schedules ) {
        $schedules['15mins'] = array(
            'interval' => 900, // 15mins in seconds
            'display'  => __( 'Every 15 mins' ),
        );
        return $schedules;
    }


    /**
     * Checks completed orders to see those that have not been Enroled and enrole
     */
    public function create_order_enrolment_keys()
    {
        global $wpdb;
        $logger = new \WC_Logger();
        $logger->info('Starting Auto-key generation cron task');
        $ids_array = array();

        $order_complete_meta_key = Order::ORDER_ENROLMENT_COMPLETE;

        $order_ids = $wpdb->get_col("SELECT p.id
            FROM $wpdb->posts AS p
            LEFT JOIN $wpdb->postmeta AS pm
            ON p.ID = pm.post_id
            WHERE p.`post_status` = 'wc-completed'
            AND pm.`meta_key` = '{$order_complete_meta_key}'
            AND pm.`meta_value` = 0");

        foreach ( $order_ids as $order_id ) {
            $order = new \WC_Order($order_id);
            if( $order->has_status( 'completed' )){
                Order::process_order_with_coursesource( $order_id );
                $ids_array[] = $order_id;
            }
        }
        $ids_string = implode( ',', $ids_array );
        $logger->info(  "Attempted to enrole Order ID: {$ids_string}");
        $logger->info( 'Ending Auto-enrolment cron task');

    }

    /**
     * Add registered cron jobs when plugin activated
     */
    public static function activate_cron()
    {
        //Use wp_next_scheduled to check if the event is already scheduled
        $timestamp = wp_next_scheduled( self::CRON_TASK_PREFIX . 'create_order_enrolment_keys' );

        //If $timestamp == false add cron task as it's not currently queued...
        if( $timestamp == false ){
            //Schedule the event for right now, then to repeat every 5mins using the hook CRON_TASK_PREFIX . create_order_enrolment_keys
            wp_schedule_event( time(), '5mins', self::CRON_TASK_PREFIX . 'create_order_enrolment_keys' );
        }
    }


    /**
     * Removes registered cron jobs when plugin deactivated
     */
    public static function deactivate_cron(){
        wp_clear_scheduled_hook( self::CRON_TASK_PREFIX . 'create_order_enrolment_keys' );
    }


}