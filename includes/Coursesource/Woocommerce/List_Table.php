<?php

namespace Coursesource\Woocommerce;

use Coursesource\Woocommerce\Coursesource\Api;

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class List_Table extends \WP_List_Table
{

    public $total = 0;

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     */
    function __construct()
    {
        parent::__construct([
            'singular' => 'course', //Singular label
            'plural' => 'courses', //plural label, also this well be one of the table css class
            'ajax' => false //We won't support Ajax for this table
        ]);
    }

    public static function init()
    {
        \add_action('admin_enqueue_scripts', __CLASS__ . '::localize_scripts');
        \add_action('admin_menu', __CLASS__ . '::add_product_submenu_page', 99);
        \add_action('init', __CLASS__ . '::init_thickbox');
    }


    public static function localize_scripts()
    {
        if (is_admin()) {

            $assets = [
                'js' => [
                    'cs-admin' => [],
                ],
                'css' => [
                    'cs-admin' => [],
                ]
            ];
            Common::register_scripts_and_styles( $assets );

            $params = [
                'adminurl' => admin_url(),
                'ajaxurl' => admin_url('admin-ajax.php'),
                'thickbox_id' => 'coursesource-thickbox-content',
                'import_courses_nonce' => \wp_create_nonce('import_courses_nonce'),
                'import_course' => \wp_create_nonce('coursesource_import_course'),
                'import_courses' => \wp_create_nonce('coursesource_import_courses'),
                'import_library' => \wp_create_nonce('coursesource_import_library'),
            ];
            \wp_localize_script('cs-admin-js', 'CoursesourceListTable', $params);
        }
    }

    public static function get_table_url()
    {
        return \menu_page_url('course-source-import', false);
    }


    public static function init_thickbox()
    {
        \add_thickbox();
    }

    /**
     * Add Course Source submenu to Admin -> Products
     * @return void
     */
    public static function add_product_submenu_page()
    {
        \add_submenu_page('edit.php?post_type=product', 'Course Source', 'Import Products from Course Source', 'manage_woocommerce', 'course-source-import', __CLASS__ . '::course_import_page_callback');
    }

    public static function course_import_page_callback()
    {
        echo '<div class="wrap">';
        echo '<h1>Course Source Courses</h1>';
        echo '<style>';
        echo '.widefat .check-column { width:30px!important; }';
        echo '</style>';
        $course_table = new self();
        $course_table->prepare_items();
        echo '<hr class="wp-header-end">';

        $form_url = self::get_table_url();
        echo '<form method="get" action="' . $form_url . '">';
        echo '<input type="hidden" name="post_type" value="product" />';
        echo '<input type="hidden" name="page" value="' . esc_attr__($_REQUEST['page']) . '" />';
        echo $course_table->get_dropdown();
        echo $course_table->search_box(__('Search Courses'), 'course_name');
        $course_table->display();
        echo '</form>';

        echo "<p class='notice notice-error' style='margin: 15px 0; padding: 10px;'>";
        echo __('Before importing or resynchronising data, please check that you have the plugin\'s import settings configured as you require. You can check the plugin settings','coursesource');
        echo " <a href='" . \admin_url('admin.php?page=wc-settings&tab=coursesource_settings_tab') . "'>" . __('here', 'coursesource') . "</a>.";
        echo "</p>";

        // Sync/Import Selected Courses
        echo '<a href="#" id="cs-courses-table-sync__selected" class="button cs-courses-table-sync__selected">' . __('Import / Link / Resynchronise Selected','coursesource' ) . '</a> ';
        // Sync Entire Library
        echo '<a href="#" id="cs-courses-table-sync__entire_library" class="button cs-courses-table-sync__entire_library">' . __('Import / Link / Resynchronise Entire Library','coursesource' ) . '</a></span>';
        echo '<span id="cs-courses-table-sync-spinner"></span>';
        echo '</div>';
    }


    /**
     * Add extra markup in the toolbars before or after the list
     *
     * @param string $which , helps you decide if you add the markup after (bottom) or before (top) the list
     */
    function extra_tablenav($which)
    {
        if ($which == "top") {
            //The code that goes before the table is here
            echo "";
        }
        if ($which == "bottom") {
            //The code that goes after the table is there
            echo "";
        }
    }

    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    function get_columns()
    {
        $columns = [
            'cb' => '<input type="checkbox" />',
            'courseid' => __('Course ID'),
            'coursetitle' => __('Course Name'),
            'price' => __('Price'),
            'vendorname' => __('Vendor'),
            'imported' => __('Import Status')
        ];
        return $columns;
    }

    /**
     * Decide which columns to activate the sorting functionality on
     * @return array $sortable, the array of columns that can be sorted by the user
     */
    public function get_sortable_columns()
    {
        return [
            'courseid' => ['courseid', false],
            'coursetitle' => ['coursetitle', false],
            'price' => ['price', false],
            'imported' => ['imported', false],
        ];
    }

    public function get_hidden_columns()
    {
        return [];
    }


    public function column_default($item, $column_name)
    {
        return $item[$column_name];
    }


    /**
     * Get the sort name and direction
     * @return array
     */
    private function get_sort_parameters() {
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'courseid'; // If no sort set, default to courseid
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc'; // If no order, default to asc
        return [
            'name' => $orderby,
            'direction' => $order,
        ];
    }


    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];

        /* -- Pagination parameters -- */
        $perpage = 100;
        $paged = 1;
        if (isset($_REQUEST['paged'])) {
            $paged = (int)$_REQUEST['paged'];
        }

        $vendor_id = "";
        if (isset($_REQUEST['VendorID'])) {
            $vendor_id = \sanitize_text_field($_REQUEST['VendorID']);
        }

        $search_term = '';
        if (isset($_REQUEST['s'])) {
            $search_term = \sanitize_text_field($_REQUEST['s']);
        }

        $items = $this->getAllCourses($paged, $perpage, $vendor_id, $search_term, $this->get_sort_parameters() );

        $totalitems = $this->total;
        $totalpages = (int) ceil($totalitems / $perpage);

        $this->set_pagination_args([
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page" => $perpage,
        ]);

        $this->items = $items;
    }


    function display_rows()
    {
        //Get the records registered in the prepare_items method
        $records = $this->items;

        //Get the columns registered in the get_columns and get_sortable_columns methods
        $columns = $this->get_columns();
        //list( $columns, $hidden ) = $this->get_column_info();
        $paged = 1;
        if (isset($_REQUEST['paged']) && is_numeric($_REQUEST['paged'])) {
            $paged = $_REQUEST['paged'];
        }

        //Loop for each record
        if (!empty($records)) {
            foreach ($records as $record) {
                //Open the line
                echo '<tr id="record_' . stripslashes($record['CourseID']) . '">';
                $checkbox = '<label class="screen-reader-text" for="record_' . stripslashes($record['CourseID']) . '">' . sprintf(__('Select %s'), $record['CourseTitle']) . '</label>'
                    . "<input type='checkbox' name='courses[]' id='course_" . stripslashes($record['CourseID']) . "' value='" . stripslashes($record['CourseID']) . "' />";

                foreach ($columns as $column_name => $column_display_name) {

                    //Style attributes for each col
                    $class = "class='$column_name column-$column_name'";
                    $style = "";
                    #if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
                    $attributes = $class . $style;

                    //Display the cell
                    if ($column_name == 'cb') {
                        echo '<th scope="row" class="check-column">' . $checkbox . '</th>';
                    } elseif ($column_name == 'courseid') {
                        echo '<td ' . $attributes . '>' . stripslashes($record['CourseID']) . '</td>';
                    } elseif ($column_name == 'coursetitle') {
                        echo '<td ' . $attributes . ' data-course-id="' . $record['CourseID'] . '">' . stripslashes($record['CourseTitle']) . '</td>';
                    } elseif ($column_name == 'vendorname') {
                        echo '<td ' . $attributes . '>' . stripslashes($record['VendorName']) . '</td>';
                    } elseif ($column_name == 'price') {
                        echo '<td ' . $attributes . '>' . stripslashes($record['Price']) . '</td>';
                    } elseif ($column_name == 'imported') {
                        echo '<td ' . $attributes . '>';
                        $import_status = $record['importstatus'];

                        if ($record['imported'] === false) {
                            $button_label =  $record['importable'] ? 'Link to Related Product' : 'Import as New Product';
//                            if( $record['importable'] ) {
//                                echo '<a href="' . \admin_url('/post.php?post=' . $record['importable_product_id'] . '&action=edit') . '" class="action_view view_product button" data-course_id="' . stripslashes($record['CourseID']) . '" target="_blank" title="Opens in new window">Show Product</a>';
//                            }
                            echo '<a id="cs-course-button-import-' . $record['CourseID'] . '" href="#" class="action_import import_course button import_course_status_' . $record['importstatus'] .'" data-course_id="' . stripslashes($record['CourseID']) . '">'. $button_label . '</a>';
                        } else {
                            echo '<a href="' . \admin_url('/post.php?post=' . $record['product_id'] . '&action=edit') . '" class="action_view view_product button" data-course_id="' . stripslashes($record['CourseID']) . '" target="_blank" title="Opens in new window">Edit Product</a>';
                            echo ' <a id="cs-course-button-import-' . $record['CourseID'] . '" href="#" class="action_import import_course button import_course_status_' . $record['importstatus'] .'" data-course_id="' . stripslashes($record['CourseID']) . '" data-course-imported="1">Resynchronise</a>';
                        }
                        echo '</td>';
                    } else {
                        echo '<td>default</td>';
                    }
                }

                //Close the line
                echo '</tr>';
            }
        }
    }

    function get_dropdown()
    {
        $api = new Api();
        $course_vendors = $api->getVendors();

        $request_vendor_id = "";
        if (isset($_REQUEST['VendorID'])) {
            $request_vendor_id = \sanitize_text_field($_REQUEST['VendorID']);
        }

        $publisher_select = '<label for="select_vendor">Choose a vendor: </label>';
        $publisher_select .= '<select id="select_vendor" name="VendorID">';
        $class = '';
        if (!empty($request_vendor_id)) {
            $class = "selected='selected'";
        }
        $publisher_select .= "<option value='' $class>All</option>";
        foreach ($course_vendors as $vender_id => $vendor_name) {
            $class = ($request_vendor_id == $vender_id) ? "selected='selected'" : '';
            $publisher_select .= "<option value='{$vender_id}' {$class}>{$vendor_name}</option>";
        }
        $publisher_select .= '</select>';

        return $publisher_select;
    }

    protected function get_default_primary_column_name()
    {
        return 'courseid';
    }

    /**
     * @param int $page
     * @param int $perpage
     * @param string $VendorID
     * @param string $search
     * @param $sort
     *
     * @return array
     */
    function getAllCourses($page = 1, $perpage = 50, $VendorID = '', $search = '', $sort = null)
    {
        if ($page <= 0) {
            $page = 1;
        }
        $offset = (($page - 1) * $perpage) + ($page - 1);

        // Why is a paginated response not being returned from the API?
        $api = new Api();
        $api_courses = $api->api_GetCoursesPaginated($offset, $perpage, $VendorID, $search, $sort);
        $this->total = $api->total;

        $courses = [];
        foreach ($api_courses as $api_course) {
            $courses[] = [
                'CourseID' => $api_course->CourseID,
                'CourseTitle' => $api_course->CourseTitle,
                'Price' => $api_course->Price,
                'VendorName' => $api_course->VendorName,
                'product_id' => $api_course->product_id,
                'imported' => $api_course->imported,
                'importable' => $api_course->importable,
                'importstatus' => $api_course->importstatus,
            ];
        }
        return $courses;
    }

}
