<?php

class Coursesource_Frontend_Ajax {

	public static function init() {
		self::add_actions();
	}

	public static function add_actions() {
		add_action( "wp_ajax_cs_course_table_details", __CLASS__ . "::course_table_details_ajax" );
		add_action( "wp_ajax_course_enrole_from_key", __CLASS__ . "::course_enrole_from_key_ajax" );
	}

	/**
	 * AJAX request to load Course details via Coursesource API
	 * @return void
	 */
	public static function course_enrole_from_key_ajax() {
		if ( !isset( $_REQUEST['nonce'] ) || !isset( $_REQUEST['key'] ) ) {
			die( json_encode( [] ) );
		}
		// Validate the request...
		$nonce = $_REQUEST['nonce'];
		if ( !wp_verify_nonce( $nonce, 'course_table_details' ) ) {
			die( json_encode( [] ) );
		}

		$user_id   = get_current_user_id();
		$key       = $_REQUEST['key'];
		$api       = new Coursesource_Api();
		$enrolment = $api->createEnrolmentFromKey( $user_id, $key );
		$result    = [];
		if ( !empty( $enrolment->Success ) ) {
			$result['success'] = $enrolment->Success;
		}
		if ( !empty( $enrolment->Errors ) ) {
			$result['error'] = $enrolment->Errors;
		}
		header( 'Content-Type: application/json' );
		die( json_encode( $result ) );
	}

	/**
	 * AJAX request to load Course details via Coursesource API
	 * @return void
	 */
	public static function course_table_details_ajax() {

		if ( !isset( $_REQUEST['nonce'] ) || !isset( $_REQUEST['enrolment_id'] ) || (int) $_REQUEST['enrolment_id'] == 0 ) {
			die( json_encode( [] ) );
		}
		// Validate the request...
		$nonce = $_REQUEST['nonce'];
		if ( !wp_verify_nonce( $nonce, 'course_table_details' ) ) {
			die( json_encode( [] ) );
		}

		$user_id      = get_current_user_id();
		$enrolment_id = $_REQUEST['enrolment_id'];
		$api          = new Coursesource_Api();
		$course       = $api->getMyCourseData( $user_id, $enrolment_id );
		$lessons      = $course->CourseLessons;

		$response = Coursesource_Template::get_template( 'shortcodes/my_courses_course_details', [
				'course'  => $course,
				'lessons' => $lessons,
		] );
		header( 'Content-Type: text/html; charset=utf-8' );
		die( $response );
	}


}

Coursesource_Frontend_Ajax::init();
