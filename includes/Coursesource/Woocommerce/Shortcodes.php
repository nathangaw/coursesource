<?php

namespace Coursesource\Woocommerce;

use Coursesource\Woocommerce\Coursesource\Api;

class Shortcodes {

	public static function init() {
		self::add_shortcodes();
		\add_action( 'init', __CLASS__ . '::init_thickbox' );
	}


	public static function add_shortcodes() {
		\add_shortcode( 'cs_my_courses_table', __CLASS__ . '::my_courses' );
        \add_shortcode( 'cs_my_courses_key', __CLASS__ . '::my_courses_key' );
	}

	public static function init_thickbox() {
		\add_thickbox();
	}

	public static function my_courses() {
		$user = \wp_get_current_user();
		if ( $user->ID == 0 ) {
			return '<p class="error">You must be logged in to use this page. Please <a href="' . \wp_login_url( \get_permalink() ) . '">login here</a></p>';
		}

		$api     = new Coursesource\Api();
		$courses = $api->getMyCourses( $user );
		if ( count( $courses ) ) {
			$vendors = $api->getVendors();
			/*
			 * url for iframe:
			 //content-cdn.course-source.net/cls_scorm_wrapper/cls_scorm1p2_wrapper_20171121.html?enrollID=4189784&tutorialID=5350001&updateKey=321596&tutorialURL=%2Fcbt%2F100pceffective%2F5S%2F001-5S-Game%2Findex_lms.html&dataURLPrefix=%2F%2Flearner.course-source.net%2Fcs%2Flearning&hasExitButton=0&useOverlaying=0&windowTitle=5S+-+Transform+Your+Workplace+in+5+Steps+%2F+5S+Game&returnURL=https://www.course-source.com/MyCourses/4189784/redirect
			*/
			return Template::get_template( 'shortcodes/my_courses_table', [
					'courses' => $courses,
					'vendors' => $vendors,
					'api'     => $api
			] );
		}
	}

    public static function my_courses_key() {
        $user = \wp_get_current_user();
        if ( $user->ID == 0 ) {
            return '<p class="error">You must be logged in to use this page. Please <a href="' . \wp_login_url( get_permalink() ) . '">login here</a></p>';
        }

        return Template::get_template( 'shortcodes/my_courses_key', [] );
    }


}
