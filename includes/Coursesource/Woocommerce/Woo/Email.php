<?php

namespace Coursesource\Woocommerce\Woo;

use Coursesource\Woocommerce\Settings;
use Coursesource\Woocommerce\Template;

class Email {

	public static function init() {
		self::add_filters();
	}

	public static function add_filters() {
        // \add_action( 'woocommerce_email_order_details',  [ __CLASS__, 'add_product_access_info_to_order_email'], 5, 4 );
		\add_filter( 'woocommerce_email_order_meta', [ __CLASS__, 'add_keys_to_order_emails' ], 10, 3 );
        // \add_filter( 'woocommerce_email_order_meta', [ __CLASS__, 'add_portal_details_to_order_emails' ], 15, 3 );
	}

	/**
	 * Add the Coursesource Enrolment keys to the Order Completed Email
	 */
	public static function add_keys_to_order_emails( $order, $sent_to_admin, $plain_text ) {
		$coursesource_data = null;
        $json_data         = get_post_meta( $order->ID, Order::ORDER_ENROLLMENT_KEYS, true );
		if ( $json_data !== false ) {
			$coursesource_data = json_decode( $json_data, true );
		}
		if ( is_array( $coursesource_data ) ) {
			Template::template( 'order/email/coursesource-keys', [
					'order'             => $order,
					'coursesource_data' => $coursesource_data,
					'company_name'      => COURSESOURCE_COMPANY_NAME,
			] );
		}
	}

    /**
     * Add the Coursesource Portal details to the Order Completed Email
     */
    /*
    public static function add_portal_details_to_order_emails( $order, $sent_to_admin, $plain_text ) {
        $has_courses = Order::does_order_contain_coursesource_products( $order->ID);
        if ( $has_courses ) {
            $portal_url = Settings::getPortalUrl();
            Template::template( 'order/email/coursesource-portal', [
                'portal_url'        => $portal_url,
                'company_name'      => COURSESOURCE_COMPANY_NAME,
            ] );
        }
    }
    */

    /**** Add message to emails based on whether product is LU or not  *******/


    /**
     * @param $order
     * @param $sent_to_admin
     * @param $plain_text
     * @param $email
     * @return void
     */
    /*
    public static function add_product_access_info_to_order_email( $order, $sent_to_admin, $plain_text, $email ) {
        // Only customers need to know about the delivery times.
        // https://www.businessbloomer.com/woocommerce-add-extra-content-order-email/
        if ( $sent_to_admin || $email->id != 'customer_completed_order' ) {
            return;
        }
        $cart_composition = Order::get_order_course_composition( $order );
        echo Template::get_template( 'order/coursesource-product-access', ['cart_content' => $cart_composition ]);
    }
    */

}