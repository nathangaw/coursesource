<?php

namespace Coursesource\Woocommerce;

use Coursesource\Woocommerce\Coursesource\Api;
use Coursesource\Woocommerce\Coursesource\Helper;
use Coursesource\Woocommerce\Woo\Product;

class Admin_Ajax {

	public static function init() {
		self::add_actions();
	}

	public static function add_actions() {
		\add_action( "wp_ajax_cs_course_import", __CLASS__ . "::course_import_ajax" );
		\add_action( "wp_ajax_get_course_library", __CLASS__ . "::get_course_library_ajax" );
		\add_action( "wp_ajax_cs_course_details", __CLASS__ . "::course_details_ajax" );
    }

	/**
	 * AJAX request to load Course details via Coursesource API
	 * @return void
	 */
	public static function course_details_ajax() {
		if ( !isset( $_REQUEST['nonce'] ) || !isset( $_REQUEST['course_id'] ) || (int) $_REQUEST['course_id'] == 0 ) {
			die( \json_encode( [] ) );
		}
		// Validate the request...
		$nonce = $_REQUEST['nonce'];
		if ( !\wp_verify_nonce( $nonce, 'import_courses_nonce' ) ) {
			die( \json_encode( [] ) );
		}
		$api           = new Coursesource\Api();
		$catalogueData = $api->getCatalogueCourse( $_REQUEST['course_id'] );
		header( 'Content-Type: application/json' );
		die( \json_encode( [ 'course' => $catalogueData ] ) );
	}


	/**
	 * AJAX request to import a Course via Coursesource API
	 * @return void
	 */
	public static function course_import_ajax() {
		if ( !isset( $_REQUEST['nonce'] ) || !isset( $_REQUEST['course_id'] ) || (int) $_REQUEST['course_id'] == 0 ) {
			die( \json_encode( [] ) );
		}
		// Validate the request...
		$nonce = $_REQUEST['nonce'];
		if ( !\wp_verify_nonce( $nonce, 'import_courses_nonce' ) ) {
			die( \json_encode( [] ) );
		}

		$msg =[
            'success' => 1
        ];

		$course_ids = $_REQUEST['course_id'];
		if ( is_numeric( $course_ids ) && !is_array( $course_ids ) ) {
			$course_ids = array( $course_ids );
		}

		$api     = new Api();
		$vendors = $api->getVendors();

		$attribute_names = array(
				'HoursOfTraining' => Settings::getAttributeNameTrainingDuration(),
				'Publisher'       => Settings::getAttributeNamePublisher(),
		);

		foreach ( $course_ids as $course_id ) {
			if ( !is_numeric( $course_id ) ) {
				continue;
			}
            $import_data = Helper::parseCourseDataForProductImport( $course_id, $api, $vendors, $attribute_names );
            if( $import_data ) {
                $product_data = Product::add_product_from_course( $import_data );
                if ( empty( $product_data['error'] ) ) {
                    $msg['success'] = $product_data['id'];
                }
                else {
                    $msg['error'] = "Problem loading Course data for this product";
                }
            }
		}
		header( 'Content-Type: application/json' );
		die( \json_encode( $msg ) );
	}



	/**
	 * AJAX request to load Course details via Coursesource API
	 * @return void
	 */
	public static function get_course_library_ajax() {
		if ( !isset( $_REQUEST['nonce'] ) || !isset( $_REQUEST['get_course_library'] ) ) {
			die( \json_encode( [] ) );
		}
		// Validate the request...
		$nonce = $_REQUEST['nonce'];
		if ( !\wp_verify_nonce( $nonce, 'import_courses_nonce' ) ) {
			die( \json_encode( [] ) );
		}
		$api     = new Coursesource\Api();
		$courses = $api->getCourseLibrary();
		header( 'Content-Type: application/json' );
		die( \json_encode( [ 'courses' => $courses ] ) );
	}

}
