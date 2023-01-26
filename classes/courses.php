<?php
class CourseSource_List extends WP_List_Table {
	/**
	* Constructor, we override the parent to pass our own arguments
	* We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	*/
	function __construct() {
	   parent::__construct( array(
	  'singular'=> 'course', //Singular label
	  'plural' => 'courses', //plural label, also this well be one of the table css class
	  'ajax'   => false //We won't support Ajax for this table
	  ) );
	}
	
	/**
	* Add extra markup in the toolbars before or after the list
	* @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
	*/
   function extra_tablenav( $which ) {
	  if ( $which == "top" ){
		 //The code that goes before the table is here
		 echo"";
	  }
	  if ( $which == "bottom" ){
		 //The code that goes after the table is there
		 echo"";
	  }
   }
   
   /**
	* Define the columns that are going to be used in the table
	* @return array $columns, the array of columns to use with the table
	*/
	function get_columns() {
		$columns = array(
			'cb'			=> '<input type="checkbox" />',
			'courseid'	  =>__('Course ID'),
			'coursetitle'   =>__('Course Name'),
			'price'   =>__('Price'),
			'vendorname'   =>__('Vendor'),
			'imported'	  =>__('Imported')
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
	public function get_hidden_columns()
	{
		return array();
	}
   
	public function column_default( $item, $column_name )
	{
		switch( $column_name ) {
			case 'id':
			case 'title':
			case 'description':
			case 'year':
			case 'director':
			case 'rating':
				return $item[ $column_name ];
			default:
				return $item[ $column_name ];
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
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);

	  /* -- Ordering parameters -- */
		  //Parameters that are going to be used to order the result
		  //$orderby = !empty($_GET["orderby"]) ? mysql_real_escape_string($_GET["orderby"]) : 'ASC';
		  //$order = !empty($_GET["order"]) ? mysql_real_escape_string($_GET["order"]) : '';
		  
		/* -- Pagination parameters -- */
		//Number of elements in your table?
		//How many to display per page?
		$perpage = 100;
		//Which page is this?
		$paged = (int)$_REQUEST['paged'];

		$connection = new cs_api_connection();
		$items = $connection->getAllCourses($paged, $perpage, $_REQUEST['VendorID'], $_REQUEST['s']);
		
		
		$totalpages = ceil($connection->total / $perpage);
		$totalitems = $connection->total;
		
		$this->set_pagination_args( array(
			"total_items" => $totalitems,
			"total_pages" => $totalpages,
			"per_page" => $perpage,
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
		if ( isset($_REQUEST['paged']) && is_numeric($_REQUEST['paged']) ) { $paged = $_REQUEST['paged']; }

	   //Loop for each record
	   if(!empty($records)){foreach($records as $rec){
		   //Open the line
		   echo '<tr id="record_'.stripslashes($rec['CourseID']).'">';
		   $checkbox = '<label class="screen-reader-text" for="record_'.stripslashes($rec['CourseID']).'">' . sprintf( __( 'Select %s' ), $rec['CourseTitle'] ) . '</label>'
					   . "<input type='checkbox' name='courses[]' id='course_".stripslashes($rec['CourseID'])."' value='".stripslashes($rec['CourseID'])."' />";

		   foreach ( $columns as $column_name => $column_display_name ) {

			  //Style attributes for each col
			  $class = "class='$column_name column-$column_name'";
			  $style = "";
			  #if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
			  $attributes = $class . $style;

			  //edit link
			  //$editlink  = '/wp-admin/link.php?action=edit&link_id='.(int)$rec->link_id;
			  $editlink = '#';

			   //Display the cell
			   if ( $column_name == 'cb' ) {  echo '<th scope="row" class="check-column">'.$checkbox.'</th>'; }
			   elseif ( $column_name == 'courseid' ) { echo '<td '.$attributes.'>'.stripslashes($rec['CourseID']).'</td>'; }
			   elseif ( $column_name == 'coursetitle' ) { echo '<td '.$attributes.' data-course-id="'.$rec['CourseID'].'">'.stripslashes($rec['CourseTitle']).'</td>'; }
			   elseif ( $column_name == 'vendorname' ) { echo '<td '.$attributes.'>'.stripslashes($rec['VendorName']).'</td>'; }
			   elseif ( $column_name == 'price' ) { echo '<td '.$attributes.'>'.stripslashes($rec['Price']).'</td>'; }
			   elseif ( $column_name == 'imported' ) { 
				   echo '<td '.$attributes.'>';
				   if ( $rec['imported'] == 0 || !is_numeric($rec['imported']) ) {
					   echo '<a href="#" class="action_import import_course button" data-course_id="'.stripslashes($rec['CourseID']).'">Import Now</a>';
				   } else {
						echo '<a href="'.admin_url('/post.php?post='.$rec['imported'].'&action=edit').'" class="action_view view_course button" data-course_id="'.stripslashes($rec['CourseID']).'">Edit Product</a>';
						echo ' <a href="#" class="action_import import_course button" data-course_id="'.stripslashes($rec['CourseID']).'">Re-Import</a>';
				   }
				   echo '</td>';
			   } else {
				   echo '<td>default</td>';
			   }
		   }

		   //Close the line
		   echo'</tr>';
		}}
	}
	 
	function get_dropdown() {
		$connection = new cs_api_connection();
		$pubs = $connection->api_getVendors();
		$pub_links = array();
		
		$vars = explode('?', $_SERVER['REQUEST_URI']);
		$base_url = $vars[0];
		$vars = explode('&', $vars[1]);
		$gets = array();
		foreach ( $vars as $v ) {
			$vexplode = explode('=', $v);
			if ( $vexplode[0] != 'VendorID') { $gets[$vexplode[0]] = $vexplode[1]; }
		}
		$base_url .= '?';
		foreach ( $gets as $k => $g ) {
			$base_url .= $k.'='.$g.'&';
		}
		$url = $base_url;
		
		$pub_links = '<label for="select_vendor">Choose a vendor: </label><select id="select_vendor" name="select_vendor">';
		if ( !isset($_REQUEST['VendorID']) ) { $class="selected='selected'"; }
		$pub_links .= "<option value='".$base_url.'VendorID='."' $class>All</option>";
		
		foreach ( $pubs as $vid => $vname ) {
			$url = $base_url.'VendorID='.$vid;
			$_REQUEST['VendorID'] == $vid ? $class = "selected='selected'" : $class="";
			
			$pub_links .= "<option value='$url'$class>" . $vname . '</option>';
		}
		
		$pub_links .= '</select>';
		
		// Now add the search too
		//$pub_links .= ' <label for="search_courses"> Search for a Course: </label><input type="search" name="search_courses" id="search_courses" /><input type="submit" name="submit_search" id="submit_search" value="Search" />';
		
		return $pub_links;
	}
	
	protected function get_default_primary_column_name() {
			return 'courseid';
	}
}
