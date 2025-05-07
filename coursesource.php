<?php
/**
 * Plugin Name: Course Source
 * Description: This plugin is created by Course Source to facilitate interfacing with the Course Source LMS
 * Version: 2.3.5
 * Text Domain: coursesource
 * Author: CourseSource
 * Author URI: https://www.course-source.com
 * Requires PHP: 7.4
 */

use Coursesource\Woocommerce\Admin_Ajax;
use Coursesource\Woocommerce\Common;
use Coursesource\Woocommerce\Frontend_Ajax;
use Coursesource\Woocommerce\Frontend_Checkout;
use Coursesource\Woocommerce\List_Table;
use Coursesource\Woocommerce\Settings;
use Coursesource\Woocommerce\Setup;
use Coursesource\Woocommerce\Shortcodes;
use Coursesource\Woocommerce\Woo\Checkout;
use Coursesource\Woocommerce\Woo\Email;
use Coursesource\Woocommerce\Woo\Myaccount;
use Coursesource\Woocommerce\Woo\Order;
use Coursesource\Woocommerce\Woo\Order\Cron as Order_Cron;
//use Coursesource\Woocommerce\Woo\Product\Cron as Product_Cron;
use Coursesource\Woocommerce\Wp\User;

define( 'COURSESOURCE_PLUGIN_VERSION', '2.3.5' );
define( 'COURSESOURCE_PLUGIN_FILE', __FILE__ ) ;
define( 'COURSESOURCE_PLUGIN_BASE', plugin_dir_path(__FILE__) );
define( 'COURSESOURCE_PLUGIN_BASE_URL', plugin_dir_url(__FILE__) );
define( 'COURSESOURCE_PLUGIN_ASSETS', COURSESOURCE_PLUGIN_BASE . 'assets/' );
define( 'COURSESOURCE_PLUGIN_ASSETS_URL', COURSESOURCE_PLUGIN_BASE_URL . 'assets/' );
define( 'COURSESOURCE_TEMPLATES', COURSESOURCE_PLUGIN_BASE . 'templates/' ) ;
define( 'COURSESOURCE_OPTION_NAME_PREFIX', "cs_" );
define( 'COURSESOURCE_COMPANY_NAME', "Course-Source" );
define( 'COURSESOURCE_JS_OBJECT_NAME', "Coursesource" );

require COURSESOURCE_PLUGIN_BASE . 'vendor/autoload.php';
//Common::init();
Settings::init();
Setup::init();
User::init();
new Order_Cron();
//new Product_Cron();
Checkout::init();
Order::init();
Email::init();
Myaccount::init();
Shortcodes::init();
List_Table::init();
Admin_Ajax::init();
Frontend_Checkout::init();
Frontend_Ajax::init();

//add_action( 'init', function() {
//    $blah = new Cron();
//    $blah->update_products();
//} );
