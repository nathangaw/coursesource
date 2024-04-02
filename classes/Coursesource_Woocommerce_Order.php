<?php

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

class Coursesource_Woocommerce_Order
{

    public const REQUEST_COURSE_KEYS_META_NAME = 'request_keys';
    public const ORDER_ENROLLMENT_KEYS = 'enrolment_keys_json';

    public const ORDER_ENROLLMENT_IDS = 'enrolment_ids_json';

    public const ORDER_ENROLMENTS_COMPLETE = 'enrolments_complete';

    public const ORDER_ENROLMENTS_ERRORS = 'enrolment_errors';

    public static function init()
    {
        self::add_actions();
    }

    public static function add_actions()
    {
        add_action('woocommerce_order_status_completed', __CLASS__ . '::process_order_with_coursesource', 10, 1);
        add_action('add_meta_boxes', __CLASS__ . '::add_meta_boxes', 10, 2);
        add_action('woocommerce_order_details_after_order_table', __CLASS__ . '::add_order_enrolment_keys');
        add_action('woocommerce_order_details_after_order_table', __CLASS__ . '::add_order_enrolment_ids');
    }


    /**
     * Get any enrolment keys that exist for an order
     *
     * @param int|WC_Order $order
     *
     * @return array|null
     */
    public static function get_order_enrolment_keys( $order )
    {
        $data = null;
        if( !is_object( $order ) ){
            $order = wc_get_order( $order );
        }
        if( $order ){
            $json_data = $order->get_meta( self::ORDER_ENROLLMENT_KEYS, true);
            if ($json_data !== false) {
                $data = json_decode($json_data, true);
            }
        }
        return $data;
    }

    /**
     * Get any enrolment ids that exist for an order
     *
     * @param int $order
     *
     * @return array|null
     */
    public static function get_order_enrolment_ids($order)
    {
        $data = null;
        $json_data = $order->get_meta( self::ORDER_ENROLLMENT_IDS, true);
        if ($json_data !== false) {
            $data = json_decode($json_data, true);
        }
        return $data;
    }


    public static function process_order_with_coursesource($order_id)
    {
        $order = wc_get_order($order_id);
        $order_edit_url = admin_url( 'post.php?page=wc-orders&action=edit&id=' . absint( $order->get_id() ) );

        $user = $order->get_user();
        if (!$user) {
            error_log('no user found for order');
            return;
        }

        //has something changed with the way custom order meta is saved? Seems that way...
        $enrolments_completed = $order->get_meta( self::ORDER_ENROLMENTS_COMPLETE, true);
        $requires_keys = $order->get_meta(self::REQUEST_COURSE_KEYS_META_NAME, true);
        if (!empty($enrolments_completed)) {
            return;
        }

        // Check if any of the products in the basket are CS products:
        $course_array = [];
        $order_data = $order->get_data();
        foreach ($order_data['line_items'] as $line_item) {
            /* @var $line_item WC_Order_Item_Product */
            $product = $line_item->get_product();
            //* @var $product WC_Product */
            $course = [];
            $course['product_id'] = $product->get_id();
            $course['product_name'] = $product->get_name();
            $course['CourseID'] = $product->get_attribute('CourseID');
            if (empty($course['CourseID'])) {
                continue;
            }
            $course['DurationID'] = $product->get_attribute('DurationID');
            $course['qty'] = $line_item->get_quantity();
            $course_array[] = $course;
        }

        // if no CS products in basket, we don't need to do anything
        if (empty($course_array)) {
            return;
        }

        $api = new Coursesource_Api();

        // if an API user does not exist, create one
        if (!$api->checkUser($user)) {
            $api->addUser(
                $user->ID,
                get_user_meta($user->ID, 'first_name', true),
                get_user_meta($user->ID, 'last_name', true),
                $user->user_email,
                $api->defaultGroup,
                1
            );
            error_log('CS - creating user');
        }

        // check user again, if still doesn't exist, something has gone wrong, send email
        if (!$api->checkUser($user)) {
            error_log('CS api didn\'t create user');
            $api->emailError('Automated message - API error', 'An error has occurred on ' . home_url() . ' whilst trying to add a user via the API.<br /><br />Site: ' . $api->site_id . '<br />Site Key: ' . $api->api_key . '<br />LoginID: ' . $user->user_email . '<br />Order Url: ' . $order_edit_url );
            return;
        }

        // enrol user
        $enrolment_keys = [];
        $enrolment_ids = [];
        $enrolment_errors = [];
        foreach ($course_array as $course) {

            if ($requires_keys) {
                $enrolment_result = $api->createEnrolmentKey($user->ID, date("Y-m-d"), date("Y-m-d", strtotime("+1 year")), $course['CourseID'], $course['DurationID'], $course['qty'], $order_id, $course['product_id']);
                if (count($enrolment_result->Errors) > 0 || count($enrolment_result->Warnings) > 0) {
                    $enrolment_errors[] = [
                        'course' => $course,
                        'error_message' => $enrolment_result->Errors
                    ];
                    $api->emailError('Automated message - API error', 'An error has occurred on ' . home_url() . ' whilst trying to create enrolment keys via the API.' . PHP_EOL . PHP_EOL . 'Site: ' . $api->site_id . PHP_EOL . PHP_EOL . 'Site Key: ' . $api->api_key . PHP_EOL . PHP_EOL . 'LoginID: ' . PHP_EOL . PHP_EOL . 'Order Url: ' . $order_edit_url . $user->user_email . PHP_EOL . PHP_EOL . 'Enrolment result: <pre>' . print_r($enrolment_result, true) . '</pre>');
                } else {
                    $enrolment_keys[] = $enrolment_result;
                }
            } else {
                $enrolment_result = $api->enrolUser($user->ID, $course['CourseID']);
                if ($enrolment_result->EnrollID === 0) {
                    $enrolment_errors[] = ['course' => $course, 'error_message' => $enrolment_result->ErrorMessage];
                    $api->emailError('Automated message - API error', 'An error has occurred on ' . home_url() . ' whilst trying to create an enrolment via the API.' . PHP_EOL . PHP_EOL . 'Site: ' . $api->site_id . PHP_EOL . PHP_EOL . 'Site Key: ' . $api->api_key . PHP_EOL . PHP_EOL . 'LoginID: ' . $user->user_email. PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . 'Order Url: ' . $order_edit_url . 'Enrolment result: <pre>' . print_r($enrolment_result, true) . '</pre>');
                } else {
                    $enrolment_data = [
                        'product_id' => $course['product_id'],
                        'name' => $course['product_name'],
                        'enrol_id' => $enrolment_result->EnrollID
                    ];
                    $enrolment_ids[] = $enrolment_data;
                }
            }
        }
        if (count($enrolment_keys) > 0) {
            $order->update_meta_data( self::ORDER_ENROLLMENT_KEYS, json_encode($enrolment_keys) );
        }
        if (count($enrolment_ids) > 0) {
            $order->update_meta_data( self::ORDER_ENROLLMENT_IDS, json_encode($enrolment_ids) );
        }
        if (empty($enrolment_errors)) {
            $order->update_meta_data(self::ORDER_ENROLMENTS_COMPLETE, '1');
        } else {
            $order->update_meta_data(self::ORDER_ENROLMENTS_ERRORS, $enrolment_errors);
        }
        $order->save();
    }


    /**
     * @param $order_id
     *
     * @return bool
     */
    public static function does_order_contain_coursesource_products($order_id)
    {
        $has_course_source_product = false;
        $coursesource_product_ids = Coursesource_Woocommerce_Product::get_coursesource_product_ids(); // Replace with product ids which cannot use guest checkout
        $order = wc_get_order($order_id);
        $order_items = $order->get_items();
        foreach ($order_items as $order_item) {
            /* @var $order_item WC_Order_Item_Product */
            if (in_array($order_item->get_product_id(), $coursesource_product_ids)) {
                $has_course_source_product = true;
                break;
            }
        }
        return $has_course_source_product;
    }


    /**
     * @param string $screen
     * @param  \Automattic\WooCommerce\Admin\Overrides\Order|WP_Post $post_or_order_object
     * @return void
     */
    public static function add_meta_boxes($screen, $post_or_order_object )
    {
        // Screen name is  'shop_order' or 'woocommerce_page_wc-orders' depending on whether High-Performance Order Storage is enabled...
        $is_using_high_performance_order = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled();
        $screen = $is_using_high_performance_order ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';

        if( in_array( $screen, ['shop_order', 'woocommerce_page_wc-orders'] ) ) {
            add_meta_box(
                'coursesource_order_keys',
                COURSESOURCE_COMPANY_NAME . ' Enrolments and Course Keys',
                [self::class, 'display_meta_box'],
                $screen
            );
        }
    }


    /**
     * Add meta box to Admin Order screen
     *
     * @param \Automattic\WooCommerce\Admin\Overrides\Order|WP_Post $post_or_order_object
     *
     * @return void
     */
    public static function display_meta_box($post_or_order_object )
    {
        if ( self::does_order_contain_coursesource_products($post_or_order_object->ID) ) {
            $id = $post_or_order_object->ID;
            $order = wc_get_order( $id );
            $enrolments_completed = $order->get_meta(self::ORDER_ENROLMENTS_COMPLETE, true);
            $coursesource_errors = $order->get_meta( self::ORDER_ENROLMENTS_ERRORS, true);
            $coursesource_data = self::get_order_enrolment_keys( $order );
            $coursesource_ids = self::get_order_enrolment_ids( $order );
            $order_completed = ($order->get_status() == 'completed');
            Coursesource_Template::template('admin/woocommerce-order/coursesource-details', [
                'order_completed' => $order_completed,
                'enrolments_completed' => $enrolments_completed,
                'coursesource_data' => $coursesource_data,
                'coursesource_ids' => $coursesource_ids,
                'coursesource_errors' => $coursesource_errors,
                'company_name' => COURSESOURCE_COMPANY_NAME,
            ]);
        }
    }

    /**
     * Add Enrolment details to My Account -> Orders -> Order details
     *
     * @param $order
     *
     * @return void
     */
    public static function add_order_enrolment_keys($order)
    {
        $coursesource_data = self::get_order_enrolment_keys($order);
        $order_completed = ($order->get_status() == 'completed');

        if (!is_null($coursesource_data)) {
            Coursesource_Template::template('order/order-details-enrolment-keys', [
                'coursesource_data' => $coursesource_data,
                'order_completed' => $order_completed
            ]);
        }
    }

    /**
     * Add Enrolment details to My Account -> Orders -> Order details
     *
     * @param int|WC_Order $order
     *
     * @return void
     */
    public static function add_order_enrolment_ids( $order )
    {
        if( !is_object( $order ) ){
            $order = wc_get_order( $order );
        }
        $coursesource_data = self::get_order_enrolment_ids( $order );
        $order_completed = ($order->get_status() == 'completed');

        if (!is_null($coursesource_data)) {
            Coursesource_Template::template('order/order-details-enrolment-ids', [
                'coursesource_data' => $coursesource_data,
                'order_completed' => $order_completed
            ]);
        }
    }


}


Coursesource_Woocommerce_Order::init();
