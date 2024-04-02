<?php
/**
 * Plugin Name: Course Source
 * Description: This plugin is created by Course Source to facilitate interfacing with the Course Source LMS
 * Version: 2.2.10
 * Text Domain: coursesource
 * Author: CourseSource
 * Author URI: https://www.course-source.com
 * Requires PHP: 7.0
 */

define( 'COURSESOURCE_PLUGIN_VERSION', '2.2.10' );
define( 'COURSESOURCE_PLUGIN_BASE', plugin_dir_path(__FILE__) );
define( 'COURSESOURCE_PLUGIN_BASE_URL', plugin_dir_url(__FILE__) );
define( 'COURSESOURCE_TEMPLATES', COURSESOURCE_PLUGIN_BASE . 'templates/' ) ;
define( 'COURSESOURCE_OPTION_NAME_PREFIX', "cs_" );
define( 'COURSESOURCE_COMPANY_NAME', "Course-Source" );

require_once( COURSESOURCE_PLUGIN_BASE . 'classes/Coursesource_Api.php' );
require_once( COURSESOURCE_PLUGIN_BASE . 'classes/Coursesource_Template.php' );
require_once( COURSESOURCE_PLUGIN_BASE .'classes/Coursesource_Woocommerce_Settings.php' );
require_once( COURSESOURCE_PLUGIN_BASE .'classes/Coursesource_Woocommerce_Product.php' );
require_once( COURSESOURCE_PLUGIN_BASE .'classes/Coursesource_Woocommerce_Checkout.php' );
require_once( COURSESOURCE_PLUGIN_BASE .'classes/Coursesource_Woocommerce_Order.php' );
require_once( COURSESOURCE_PLUGIN_BASE .'classes/Coursesource_Woocommerce_Email.php' );
require_once( COURSESOURCE_PLUGIN_BASE . 'classes/Coursesource_Setup.php' );
require_once( COURSESOURCE_PLUGIN_BASE .'classes/Coursesource_Shortcodes.php' );
require_once( COURSESOURCE_PLUGIN_BASE .'classes/Coursesource_List_Table.php' );
require_once( COURSESOURCE_PLUGIN_BASE .'classes/Coursesource_Admin_Ajax.php' );
require_once( COURSESOURCE_PLUGIN_BASE .'classes/Coursesource_Frontend_Ajax.php' );
