<?php

class Coursesource_Admin_Ajax {

	public static function init() {
		self::add_actions();
	}

	public static function add_actions() {
		add_action( "wp_ajax_cs_course_import", __CLASS__ . "::course_import_ajax" );
		add_action( "wp_ajax_get_course_library", __CLASS__ . "::get_course_library_ajax" );
		add_action( "wp_ajax_cs_course_details", __CLASS__ . "::course_details_ajax" );
	}

	/**
	 * AJAX request to load Course details via Coursesource API
	 * @return void
	 */
	public static function course_details_ajax() {
		if ( !isset( $_REQUEST['nonce'] ) || !isset( $_REQUEST['course_id'] ) || (int) $_REQUEST['course_id'] == 0 ) {
			die( json_encode( [] ) );
		}
		// Validate the request...
		$nonce = $_REQUEST['nonce'];
		if ( !wp_verify_nonce( $nonce, 'import_courses_nonce' ) ) {
			die( json_encode( [] ) );
		}
		$api           = new Coursesource_Api();
		$catalogueData = $api->getCatalogueCourse( $_REQUEST['course_id'] );
		header( 'Content-Type: application/json' );
		die( json_encode( [ 'course' => $catalogueData ] ) );
	}


	/**
	 * AJAX request to import a Course via Coursesource API
	 * @return void
	 */
	public static function course_import_ajax() {
		if ( !isset( $_REQUEST['nonce'] ) || !isset( $_REQUEST['course_id'] ) || (int) $_REQUEST['course_id'] == 0 ) {
			die( json_encode( [] ) );
		}
		// Validate the request...
		$nonce = $_REQUEST['nonce'];
		if ( !wp_verify_nonce( $nonce, 'import_courses_nonce' ) ) {
			die( json_encode( [] ) );
		}

		$msg = array( 'success' => 1 );

		$course_ids = $_REQUEST['course_id'];
		if ( is_numeric( $course_ids ) && !is_array( $course_ids ) ) {
			$course_ids = array( $course_ids );
		}

		$api     = new Coursesource_Api();
		$vendors = $api->getVendors();

		$attribute_names = array(
				'HoursOfTraining' => Coursesource_Woocommerce_Settings::getAttributeNameTrainingDuration(),
				'Publisher'       => Coursesource_Woocommerce_Settings::getAttributeNamePublisher(),
		);

		foreach ( $course_ids as $CourseID ) {
			if ( !is_numeric( $CourseID ) ) {
				continue;
			}

			$catalogueData = $api->getCatalogueCourse( $CourseID );
			if ( isset( $catalogueData->CourseInfo->CourseID ) ) {
				// Why no checking to see if the catalogueData is even halfway sensible?
				$durationData = $api->api_getDurations( $CourseID );

				$customSKUPrefix   = Coursesource_Woocommerce_Settings::getProductSkuPrefix();
				$sku               = $customSKUPrefix . $catalogueData->CourseInfo->CourseID;
				$product_title     = $catalogueData->CourseInfo->Course_Title;
				$product_image     = $catalogueData->CourseInfo->Course_Image;
				$hours_of_training = $catalogueData->CourseInfo->Hours_of_Training;
				$vendor_name       = $vendors[$catalogueData->CourseInfo->VendorID];
				$product_price     = $catalogueData->BuyPrice;

                // As of 2024-02-16 Live & Dev API endpoints for getCatalogueCourse endpoint are returning different nodes from the Outline node
                // Make sure we can get a Product Description from either Introduction or HTML nodes
                $product_desc      = $catalogueData->Outline->Introduction;
                if( Coursesource_Woocommerce_Settings::isAPIModeDev() ){
                    $product_desc      = $catalogueData->Outline->HTML;
                }

                $import_data = [
						'sku'                   => $sku,
						'title'                 => $product_title,
						'image'                 => $product_image,
						'desc'                  => $product_desc,
						'price'                 => $product_price,
						'attributes'            => array(
								$attribute_names['HoursOfTraining'] => $hours_of_training
						),
						'hidden_attributes'     => array(
								'CourseID'   => $catalogueData->CourseInfo->CourseID,
								'DurationID' => $durationData[0]->DurationID,
						),
						'filterable_attributes' => array(
								$attribute_names['Publisher'] => $vendor_name
						)
				];

				$product_id = Coursesource_Woocommerce_Product::add_product_from_course( $import_data );

				if ( empty( $product['error'] ) ) {
					$msg = $product_id;
				}
				else {
					$msg = "Problem loading Course data for this product";
				}
			}

		}
		header( 'Content-Type: application/json' );
		die( json_encode( $msg ) );
	}


	/**
	 * AJAX request to load Course details via Coursesource API
	 * @return void
	 */
	public static function get_course_library_ajax() {
		if ( !isset( $_REQUEST['nonce'] ) || !isset( $_REQUEST['get_course_library'] ) ) {
			die( json_encode( [] ) );
		}
		// Validate the request...
		$nonce = $_REQUEST['nonce'];
		if ( !wp_verify_nonce( $nonce, 'import_courses_nonce' ) ) {
			die( json_encode( [] ) );
		}
		$api     = new Coursesource_Api();
		$courses = $api->getCourseLibrary();
		header( 'Content-Type: application/json' );
		die( json_encode( [ 'courses' => $courses ] ) );
	}

}

Coursesource_Admin_Ajax::init();
