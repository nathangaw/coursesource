<?php

class Coursesource_Woocommerce_Email {

	public static function init() {
		self::add_filters();
	}

	public static function add_filters() {
		add_filter( 'woocommerce_email_order_meta', [ __CLASS__, 'add_keys_to_order_emails' ], 10, 3 );
	}

	/**
	 * Add the Coursesource Enrolment keys to the Order Completed Email
	 */
	public static function add_keys_to_order_emails( $order, $sent_to_admin, $plain_text ) {
		$coursesource_data = null;
        $json_data         = get_post_meta( $order->ID, Coursesource_Woocommerce_Order::ORDER_ENROLLMENT_KEYS, true );
		if ( $json_data !== false ) {
			$coursesource_data = json_decode( $json_data, true );
		}
		if ( is_array( $coursesource_data ) ) {
			Coursesource_Template::template( 'order/email/coursesource-keys', [
					'order'             => $order,
					'coursesource_data' => $coursesource_data,
					'company_name'      => COURSESOURCE_COMPANY_NAME,
			] );
		}
	}

}

Coursesource_Woocommerce_Email::init();
