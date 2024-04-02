<?php

class Coursesource_Setup {

	public static function init() {
		add_action( 'wp_enqueue_scripts', __CLASS__ . '::enqueue_scripts' );
		add_action( 'wp_footer', __CLASS__ . '::footer' );
		add_action( 'admin_footer', __CLASS__ . '::footer' );
		add_action( 'init', __CLASS__ . '::init_thickbox' );
		add_action( 'init', __CLASS__ . '::close_close_window' );
	}


	public static function enqueue_scripts() {

		wp_enqueue_style( 'hystmodal.min', COURSESOURCE_PLUGIN_BASE_URL . '/css/hystmodal.min.css', null, COURSESOURCE_PLUGIN_VERSION );
		wp_enqueue_script( 'hystmodal.min', COURSESOURCE_PLUGIN_BASE_URL . '/js/hystmodal.min.js', array( 'jquery' ), COURSESOURCE_PLUGIN_VERSION );

		wp_enqueue_style( 'cs-frontend-css', COURSESOURCE_PLUGIN_BASE_URL . '/css/cs-frontend.css', null, COURSESOURCE_PLUGIN_VERSION );
		wp_register_script( 'cs-js-student', COURSESOURCE_PLUGIN_BASE_URL . 'js/cs-student.js', array( 'jquery' ), COURSESOURCE_PLUGIN_VERSION );
		wp_enqueue_script( 'cs-js-student' );

		$params = [
				'ajaxurl'     => admin_url( 'admin-ajax.php' ),
				'thickbox_id' => 'coursesource-thickbox-content',
		];
		wp_localize_script( 'cs-js-student', 'CoursesourceFrontend', $params );
	}


	public static function footer() {
		?>
		<div class="hystmodal" id="modalWindow" aria-hidden="true">
			<div class="hystmodal__wrap">
				<div class="hystmodal__window" role="dialog" aria-modal="true">
					<button data-hystclose class="hystmodal__close">Close</button>
					<div id="modalWindowContents"></div>
				</div>
			</div>
		</div>
		<!-- Add Thickbox content div -->
		<div id="coursesource-thickbox-content" class="coursesource-thickbox-content" style="display: none;"></div>
		<!-- Layer to load Course iframes into -->
		<div id="cs_overlay" class="coursesource-iframe-container"></div>
		<?php
	}

	public static function close_close_window() {
		if ( !isset($_REQUEST['coursesource_action']) || $_REQUEST['coursesource_action'] != 'closeCourseWindow' ) {
			return;
		}
		echo '<html><head><script>top.location.reload();</script></head></html>';
		die();
	}


	public static function init_thickbox() {
		add_thickbox();
	}

}

Coursesource_Setup::init();
