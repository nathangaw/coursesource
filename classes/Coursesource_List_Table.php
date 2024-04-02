<?php

if ( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CourseSource_List_Table extends WP_List_Table {

	public $total = 0;

	/**
	 * Constructor, we override the parent to pass our own arguments
	 * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	 */
	function __construct() {
		parent::__construct( array(
				'singular' => 'course', //Singular label
				'plural'   => 'courses', //plural label, also this well be one of the table css class
				'ajax'     => false //We won't support Ajax for this table
		) );
	}

	public static function init() {
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::localize_scripts' );
		add_action( 'admin_menu', __CLASS__ . '::add_product_submenu_page', 99 );
		add_action( 'init', __CLASS__ . '::init_thickbox' );
	}


	public static function localize_scripts() {
		if ( is_admin() ) {
			wp_register_script( 'cs-admin', COURSESOURCE_PLUGIN_BASE_URL . 'js/cs-admin.js', array( 'jquery' ), COURSESOURCE_PLUGIN_VERSION );
			wp_enqueue_script( 'cs-admin' );
			wp_enqueue_style( 'cs-admin', COURSESOURCE_PLUGIN_BASE_URL . '/css/cs-admin.css', null, COURSESOURCE_PLUGIN_VERSION );

			$params = [
					'ajaxurl'              => admin_url( 'admin-ajax.php' ),
					'thickbox_id'          => 'coursesource-thickbox-content',
					'import_courses_nonce' => wp_create_nonce( 'import_courses_nonce' ),
					'import_course'        => wp_create_nonce( 'coursesource_import_course' ),
					'import_courses'       => wp_create_nonce( 'coursesource_import_courses' ),
					'import_library'       => wp_create_nonce( 'coursesource_import_library' ),
			];
			wp_localize_script( 'cs-admin', 'CoursesourceListTable', $params );
		}
	}

    public static function get_table_url()
    {
        return menu_page_url('course-source-import', false);
    }


	public static function init_thickbox() {
		add_thickbox();
	}

	/**
	 * Add Course Source submenu to Admin -> Products
	 * @return void
	 */
	public static function add_product_submenu_page() {
		add_submenu_page( 'edit.php?post_type=product', 'Course Source', 'Import Products from Course Source', 'manage_woocommerce', 'course-source-import', __CLASS__ . '::course_import_page_callback' );
	}

	public static function course_import_page_callback() {
		echo '<div class="wrap">';
		echo '<h1>Course Source Courses</h1>';
		echo '<style>';
		echo '.widefat .check-column { width:30px!important; }';
		echo '</style>';
		$course_table = new CourseSource_List_Table();
		$course_table->prepare_items();
		echo '<hr class="wp-header-end">';

        $form_url = self::get_table_url();
		echo '<form method="get" action="' . $form_url . '">';
		echo '<input type="hidden" name="post_type" value="product" />';
		echo '<input type="hidden" name="page" value="' . esc_attr__( $_REQUEST['page'] ) . '" />';
		echo $course_table->get_dropdown();
		echo $course_table->search_box( __( 'Search Courses' ), 'course_name' );
		$course_table->display();
		echo '</form>';

		// Sync/Import Selected Courses
		echo '<a href="#" id="cs-courses-table-sync__selected" class="button cs-courses-table-sync__selected">Import/Re-Import Selected</a> ';
		// Sync Entire Library
		echo '<a href="#" id="cs-courses-table-sync__entire_library" class="button cs-courses-table-sync__entire_library">Import/Re-Import Entire Library</a></span>';
		echo '<span id="cs-courses-table-sync-spinner"></span>';
		echo '</div>';
	}


	/**
	 * Add extra markup in the toolbars before or after the list
	 *
	 * @param string $which , helps you decide if you add the markup after (bottom) or before (top) the list
	 */
	function extra_tablenav( $which ) {
		if ( $which == "top" ) {
			//The code that goes before the table is here
			echo "";
		}
		if ( $which == "bottom" ) {
			//The code that goes after the table is there
			echo "";
		}
	}

	/**
	 * Define the columns that are going to be used in the table
	 * @return array $columns, the array of columns to use with the table
	 */
	function get_columns() {
		$columns = array(
				'cb'          => '<input type="checkbox" />',
				'courseid'    => __( 'Course ID' ),
				'coursetitle' => __( 'Course Name' ),
				'price'       => __( 'Price' ),
				'vendorname'  => __( 'Vendor' ),
				'imported'    => __( 'Imported' )
		);
		return $columns;
	}

	/**
	 * Decide which columns to activate the sorting functionality on
	 * @return array $sortable, the array of columns that can be sorted by the user
	 */
	public function get_sortable_columns() {
		return $sortable = array(
			//'courseid'=>'Course ID',
			//'coursetitle'=>'Course Title',
			//'imported'=>'Imported'
		);
	}

	public function get_hidden_columns() {
		return array();
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
			case 'title':
			case 'description':
			case 'year':
			case 'director':
			case 'rating':
				return $item[$column_name];
			default:
				return $item[$column_name];
		}
	}

	/**
	 * Prepare the table with different parameters, pagination, columns and table elements
	 */
	function prepare_items() {
		global $_wp_column_headers;
		$screen = get_current_screen();

		$columns = $this->get_columns();
		//$_wp_column_headers[$screen->id]=$columns;
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		/* -- Ordering parameters -- */
		//Parameters that are going to be used to order the result
		//$orderby = !empty($_GET["orderby"]) ? mysql_real_escape_string($_GET["orderby"]) : 'ASC';
		//$order = !empty($_GET["order"]) ? mysql_real_escape_string($_GET["order"]) : '';

		/* -- Pagination parameters -- */
		//Number of elements in your table?
		//How many to display per page?
		$perpage = 100;
		//Which page is this?
		$paged = 1;
		if ( isset( $_REQUEST['paged'] ) ) {
			$paged = (int) $_REQUEST['paged'];
		}

		$vendor_id = "";
		if ( isset( $_REQUEST['VendorID'] ) ) {
			$vendor_id = sanitize_text_field( $_REQUEST['VendorID'] );
		}

		$search_term = '';
		if ( isset( $_REQUEST['s'] ) ) {
			$search_term = sanitize_text_field( $_REQUEST['s'] );
		}

		$items = $this->getAllCourses( $paged, $perpage, $vendor_id, $search_term );

		$totalitems = $this->total;
		$totalpages = (int) ceil( $totalitems / $perpage );


		$this->set_pagination_args( array(
				"total_items" => $totalitems,
				"total_pages" => $totalpages,
				"per_page"    => $perpage,
		) );
		//The pagination links are automatically built according to those parameters

		/* -- Register the Columns -- */

		/* -- Fetch the items -- */
		$this->items = $items;
	}


	function display_rows() {

		//Get the records registered in the prepare_items method
		$records = $this->items;

		//Get the columns registered in the get_columns and get_sortable_columns methods
		$columns = $this->get_columns();
		//list( $columns, $hidden ) = $this->get_column_info();
		$paged = 1;
		if ( isset( $_REQUEST['paged'] ) && is_numeric( $_REQUEST['paged'] ) ) {
			$paged = $_REQUEST['paged'];
		}

		//Loop for each record
		if ( !empty( $records ) ) {
			foreach ( $records as $rec ) {
				//Open the line
				echo '<tr id="record_' . stripslashes( $rec['CourseID'] ) . '">';
				$checkbox = '<label class="screen-reader-text" for="record_' . stripslashes( $rec['CourseID'] ) . '">' . sprintf( __( 'Select %s' ), $rec['CourseTitle'] ) . '</label>'
						. "<input type='checkbox' name='courses[]' id='course_" . stripslashes( $rec['CourseID'] ) . "' value='" . stripslashes( $rec['CourseID'] ) . "' />";

				foreach ( $columns as $column_name => $column_display_name ) {

					//Style attributes for each col
					$class = "class='$column_name column-$column_name'";
					$style = "";
					#if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
					$attributes = $class . $style;

					//Display the cell
					if ( $column_name == 'cb' ) {
						echo '<th scope="row" class="check-column">' . $checkbox . '</th>';
					}
					elseif ( $column_name == 'courseid' ) {
						echo '<td ' . $attributes . '>' . stripslashes( $rec['CourseID'] ) . '</td>';
					}
					elseif ( $column_name == 'coursetitle' ) {
						echo '<td ' . $attributes . ' data-course-id="' . $rec['CourseID'] . '">' . stripslashes( $rec['CourseTitle'] ) . '</td>';
					}
					elseif ( $column_name == 'vendorname' ) {
						echo '<td ' . $attributes . '>' . stripslashes( $rec['VendorName'] ) . '</td>';
					}
					elseif ( $column_name == 'price' ) {
						echo '<td ' . $attributes . '>' . stripslashes( $rec['Price'] ) . '</td>';
					}
					elseif ( $column_name == 'imported' ) {
						echo '<td ' . $attributes . '>';
						if ( $rec['imported'] == 0 || !is_numeric( $rec['imported'] ) ) {
							echo '<a id="cs-course-button-import-' . $rec['CourseID'] . '" href="#" class="action_import import_course button" data-course_id="' . stripslashes( $rec['CourseID'] ) . '">Import Now</a>';
						}
						else {
							echo '<a href="' . admin_url( '/post.php?post=' . $rec['imported'] . '&action=edit' ) . '" class="action_view view_course button" data-course_id="' . stripslashes( $rec['CourseID'] ) . '">Edit Product</a>';
							echo ' <a id="cs-course-button-import-' . $rec['CourseID'] . '" href="#" class="action_import import_course button" data-course_id="' . stripslashes( $rec['CourseID'] ) . '" data-course-imported="1">Re-Import</a>';
						}
						echo '</td>';
					}
					else {
						echo '<td>default</td>';
					}
				}

				//Close the line
				echo '</tr>';
			}
		}
	}

	function get_dropdown() {

		$api            = new Coursesource_Api();
		$course_vendors = $api->getVendors();

		$request_vendor_id = "";
		if ( isset( $_REQUEST['VendorID'] ) ) {
			$request_vendor_id = sanitize_text_field( $_REQUEST['VendorID'] );
		}

		$publisher_select = '<label for="select_vendor">Choose a vendor: </label>';
        $publisher_select .= '<select id="select_vendor" name="VendorID">';
		$class            = '';
		if ( !empty( $request_vendor_id ) ) {
			$class = "selected='selected'";
		}
		$publisher_select .= "<option value='' $class>All</option>";
		foreach ( $course_vendors as $vender_id => $vendor_name ) {
			$class            = ( $request_vendor_id == $vender_id ) ? "selected='selected'" : '';
			$publisher_select .= "<option value='{$vender_id}' {$class}>{$vendor_name}</option>";
		}
		$publisher_select .= '</select>';

		return $publisher_select;
	}

	protected function get_default_primary_column_name() {
		return 'courseid';
	}

	/**
	 * @param $paged
	 * @param $perpage
	 * @param $VendorID
	 * @param $search
	 *
	 * @return array
	 */
	function getAllCourses( $paged = 1, $perpage = 50, $VendorID = 0, $search = '' ) {
		global $wpdb;

		//Get the SKUs of the currently imported Course
		$current_skus      = [];
		$custom_sku_prefix = Coursesource_Woocommerce_Settings::getProductSkuPrefix();
		$current_skus_sql  = "SELECT post_id, meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key='_sku'";
		foreach ( $wpdb->get_results( $current_skus_sql ) as $sku_row ) {
			$current_skus[$sku_row->meta_value] = $sku_row->post_id;
		}

		if ( $paged <= 0 ) {
			$paged = 1;
		}

		$offset = ( ( $paged - 1 ) * $perpage ) + ( $paged - 1 );
		// Why is a paginated response not being returned from the API?
		$api         = new Coursesource_Api();
		$api_courses = $api->api_GetCoursesPaginated( $offset, $perpage, $VendorID, $search );
		$this->total = $api->total;

		$courses = [];
		foreach ( $api_courses as $api_course ) {
			$is_imported = isset( $current_skus[$custom_sku_prefix . $api_course->CourseID] ) ? $current_skus[$custom_sku_prefix . $api_course->CourseID] : false;
			$courses[]   = array(
					'CourseID'    => $api_course->CourseID,
					'CourseTitle' => $api_course->CourseTitle,
					'Price'       => $api_course->Price,
					'VendorName'  => $api_course->VendorName,
					'imported'    => $is_imported
			);
		}
		return $courses;
	}

}

CourseSource_List_Table::init();
