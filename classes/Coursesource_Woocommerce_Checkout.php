<?php

class Coursesource_Woocommerce_Checkout {

	public static function init() {
		self::add_actions();
		self::add_filters();
	}

	public static function add_actions() {
		add_action( 'woocommerce_checkout_update_order_meta', __CLASS__ . '::update_order_meta' );

	}

	public static function add_filters() {
		add_filter( 'woocommerce_checkout_registration_required', __CLASS__ . '::force_checkout_registration' );
		add_filter( 'woocommerce_checkout_fields', __CLASS__ . '::add_checkout_fields' );
	}


	public static function does_cart_contain_coursesource_products() {
		$has_course_source_product = false;
		if ( WC()->cart ) {
			$coursesource_product_ids = Coursesource_Woocommerce_Product::get_coursesource_product_ids(); // Replace with product ids which cannot use guest checkout
			$cart                     = WC()->cart->get_cart();
			foreach ( $cart as $item ) {
				if ( in_array( $item['product_id'], $coursesource_product_ids ) ) {
					$has_course_source_product = true;
					break;
				}
			}
		}
		return $has_course_source_product;
	}

	public static function does_cart_require_coursesource_keys() {
		$requires_keys = false;
		if ( WC()->cart ) {
			$coursesource_product_ids = Coursesource_Woocommerce_Product::get_coursesource_product_ids(); // Replace with product ids which cannot use guest checkout
			$cart                     = WC()->cart->get_cart();
			foreach ( $cart as $item ) {
				if ( in_array( $item['product_id'], $coursesource_product_ids ) && ( $item['quantity'] > 1 ) ) {
					$requires_keys = true;
				}
			}
		}
		return $requires_keys;
	}


	/**
	 * Force registration if the Cart contains a Coursesource product
	 *
	 * @param bool $registration_required
	 *
	 * @return string
	 */
	public static function force_checkout_registration( $registration_required ) {
		return self::does_cart_contain_coursesource_products();
	}

	/**
	 * Add additional checkout fields if the user is buying more than one licence of a Coursesource product
	 *
	 * @param $fields
	 *
	 * @return array
	 */
	public static function add_checkout_fields( $fields ) {
		if ( WC()->cart ) {
			if ( self::does_cart_contain_coursesource_products() ) {

				$require_keys = self::does_cart_require_coursesource_keys();
				if ( $require_keys ) {
					$fields['order'][Coursesource_Woocommerce_Order::REQUEST_COURSE_KEYS_META_NAME] = [
							'type'    => 'hidden',
							'default' => '1',
					];
				}
				else {
					$fields['order'][Coursesource_Woocommerce_Order::REQUEST_COURSE_KEYS_META_NAME] = [
							'type'  => 'checkbox',
							'value' => '1',
							'label' => __( 'I am buying all or some of these courses for someone else', 'coursesource' ),
					];
				}
			}
		}
		return $fields;
	}


	/**
	 * Add custom checkout field data to the order
	 *
	 * @param $order_id
	 *
	 * @return void
	 */
	public static function update_order_meta( $order_id ) {
		if ( self::does_cart_contain_coursesource_products() ) {
            $order = wc_get_order( $order_id );
			if ( !empty( $_POST[Coursesource_Woocommerce_Order::REQUEST_COURSE_KEYS_META_NAME] ) ) {
                $order->update_meta_data( Coursesource_Woocommerce_Order::REQUEST_COURSE_KEYS_META_NAME, (int) $_POST[Coursesource_Woocommerce_Order::REQUEST_COURSE_KEYS_META_NAME] );
			}
            $order->update_meta_data( Coursesource_Woocommerce_Order::ORDER_ENROLLMENT_KEYS, '' );
            $order->save();
		}
	}
}

Coursesource_Woocommerce_Checkout::init();
