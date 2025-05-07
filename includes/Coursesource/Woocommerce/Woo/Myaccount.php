<?php

namespace Coursesource\Woocommerce\Woo;
use Coursesource\Woocommerce\Settings;

class Myaccount
{

    public static function init()
    {
        self::add_actions();
        self::add_filters();
    }

    public static function add_actions()
    {
    }

    public static function add_filters()
    {
        \add_filter('woocommerce_account_menu_items', __CLASS__ . '::my_courses_link', 99, 1);
        \add_filter('woocommerce_get_endpoint_url', __CLASS__ . '::my_courses_link_url', 10, 4);
    }


    /**
     * Add a new link to the Account Links sidebar
     * @param $items
     * @return array
     */
    public static function my_courses_link($items)
    {
        $new_items = [
            'coursesource_mycourses' => __('My Courses', 'coursesource'),
        ];

        $new_items = array_slice($items, 0, 2, true) +
            $new_items +
            array_slice($items, 2, count($items), true);
        return $new_items;
    }

    /**
     * Add the url to the page that contains the My Courses shortcodes
     * @param $url
     * @param $endpoint
     * @param $value
     * @param $permalink
     * @return mixed|string
     */
    public static function my_courses_link_url($url, $endpoint, $value, $permalink)
    {
        if ($endpoint === 'coursesource_mycourses') {
            $url = Settings::getMyCoursesUrl();
        }
        return $url;
    }
}