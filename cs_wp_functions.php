<?php

/* 
 * This file contains the functions that are used by the cs plugin to integrate with wordpress
 */



function cs_import_product($data) {
	global $wpdb;
	$user_id = get_current_user(); // this has NO SENSE AT ALL, because wp_insert_post uses current user as default value
	#print_r($data); die();

	$customSKUPrefix = get_option('cs_sku_prefix', 'CS');
	
	$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'postmeta WHERE meta_key = "_sku" AND meta_value = %s',$data['sku']);
	$results = $wpdb->get_results($sql);
	if ( count($results) > 0 ) {
		$post_id = $results[0]->post_id;
		$post_id = wp_update_post( array(
			'ID'	=> $post_id,
			'post_author' => $user_id,
			'post_title' => $data['title'],
			'post_content' => (string)$data['desc'],
			'post_status' => 'publish',
			'post_type' => "product",
		) );
	} else {
		$post_id = wp_insert_post( array(
			'post_author' => $user_id,
			'post_title' => $data['title'],
			'post_content' => (string)$data['desc'],
			'post_status' => 'publish',
			'post_type' => "product",
		) );
	}
	
	$product_attributes = array();
	// Add attributes
	if ( !empty($data['attributes']) ) { foreach($data['attributes'] as $name => $value ) {
		$product_attributes[] = array(
			'name' => $name, // set attribute name
			'value' => $value, // set attribute value
			'position' => 1,
			'is_visible' => 1,
			'is_variation' => 0,
			'is_taxonomy' => 0
		);
	}}
	if ( !empty($data['hidden_attributes']) ) { foreach($data['hidden_attributes'] as $name => $value ) {
		$product_attributes[] = array(
			'name' => $name, // set attribute name
			'value' => $value, // set attribute value
			'position' => 1,
			'is_visible' => 0,
			'is_variation' => 0,
			'is_taxonomy' => 0
		);
	}}
	
	if ( !empty($data['filterable_attributes']) ) { foreach( $data['filterable_attributes'] as $name => $value ) {
		$tax = wc_attribute_taxonomy_name($name);
		$sanName = wc_sanitize_taxonomy_name($name);
		
		if( empty(taxonomy_exists( $tax )) ) { save_product_attribute_from_name( $sanName, $name ); }
		
		if( empty(term_exists( $value, $tax )) ) { 
			$insert = wp_insert_term( $value, $tax, array('slug' => sanitize_title($value) ) );
		}
		
		wp_set_post_terms( $post_id, $value, $tax, true );
		$product_attributes[] = array(
			'name' => $tax, // set attribute name
			'value' => '', // set attribute value
			'position' => 1,
			'is_visible' => 1,
			'is_variation' => 0,
			'is_taxonomy' => 1
		);
	} }
	
	
	wp_set_object_terms( $post_id, 'simple', 'product_type' );
	update_post_meta( $post_id, '_visibility', 'visible' );
	update_post_meta( $post_id, '_stock_status', 'instock');
	update_post_meta( $post_id, 'total_sales', '0' );
	update_post_meta( $post_id, '_downloadable', 'no' );
	update_post_meta( $post_id, '_virtual', 'yes' );
	update_post_meta( $post_id, '_regular_price', $data['price'] );
	update_post_meta( $post_id, '_sale_price', '' );
	update_post_meta( $post_id, '_purchase_note', '' );
	update_post_meta( $post_id, '_featured', 'no' );
	update_post_meta( $post_id, '_weight', '' );
	update_post_meta( $post_id, '_length', '' );
	update_post_meta( $post_id, '_width', '' );
	update_post_meta( $post_id, '_height', '' );
	update_post_meta( $post_id, '_sku', $data['sku'] );
	update_post_meta( $post_id, '_product_attributes', $product_attributes );
	update_post_meta( $post_id, '_sale_price_dates_from', '' );
	update_post_meta( $post_id, '_sale_price_dates_to', '' );
	update_post_meta( $post_id, '_price', $data['price'] );
	update_post_meta( $post_id, '_sold_individually', false );
	update_post_meta( $post_id, '_manage_stock', 'no' );
	update_post_meta( $post_id, '_backorders', 'no' );
	update_post_meta( $post_id, '_stock', '' );
	update_post_meta( $post_id, '_is_coursesource', true );
	return array('success' => 1);
}


function process_import() {
	if ( !isset($_REQUEST['coursesource_action']) || $_REQUEST['coursesource_action'] != 'import_course' ) {
		return;
	}
	
	$CourseIDs = $_REQUEST['import_id'];
	if ( is_numeric($CourseIDs) && !is_array($CourseIDs) ) { $CourseIDs = array($CourseIDs); }
	
	$msg = array('success' => 1);
	
	$connection = new cs_api_connection();
	
	$attribute_names = array(
		'HoursOfTraining'   => get_option('cs_attribute_name_HoursOfTraining', 'Training Duration'),
		'Publisher'		 => get_option('cs_attribute_name_Publisher'	  , 'Publisher')
	);
	
	foreach ( $CourseIDs as $CourseID ) {
		if ( !is_numeric($CourseID) ) { continue; }

		$catalogueData = $connection->getCatalogueCourse($CourseID);
		$durationData = $connection->api_getDurations($CourseID);
		#print_r($durationData); print_r($catalogueData); die();
		$customSKUPrefix = get_option('cs_sku_prefix', 'CS');

		$sku = $customSKUPrefix.$catalogueData->CourseInfo->CourseID;
		$product_title = $catalogueData->CourseInfo->Course_Title;
		$product_image = $catalogueData->CourseInfo->Course_Image;
		$product_desc = $catalogueData->Outline->HTML;
		$hours_of_training = $catalogueData->CourseInfo->Hours_of_Training;
		#$vendor_name = $connection->vendorList[$catalogueData->CourseInfo->VendorID];
		$vendor_name = $_SESSION['coursesource']['vendors'][$catalogueData->CourseInfo->VendorID];
		$product_price = $catalogueData->BuyPrice;
		
		$import_data = [
			'sku'			   => $sku,
			'title'			 => $product_title,
			'image'			 => $product_image,
			'desc'			  => $product_desc,
			'price'			 => $product_price,
			'attributes'		=> array(
				$attribute_names['HoursOfTraining'] => $hours_of_training
			),
			'hidden_attributes' => array(
				'CourseID'	  => $catalogueData->CourseInfo->CourseID,
				'DurationID'  => $durationData[0]->DurationID,
			),
			'filterable_attributes' => array(
				$attribute_names['Publisher']  => $vendor_name
			)
		];
		#die(print_r($import_data,true));

		$product = cs_import_product($import_data);
		

		if ( !empty($product['error']) ) {
			$msg = $product;
		}
	}
	header('Content-Type: application/json');
	die(json_encode($msg));
}
add_action('init', 'process_import');

function course_details() {
	if ( !isset($_REQUEST['course_id']) || (int)$_REQUEST['course_id']==0)
		die(json_encode([]));

	$connection = new cs_api_connection();
	$catalogueData = $connection->getCatalogueCourse($_REQUEST['course_id']);
	
	header('Content-Type: application/json');
	die(json_encode($catalogueData));
}
add_action("wp_ajax_course_details", "course_details");




/* Don't allow guest checkout for courses */
add_filter( 'pre_option_woocommerce_enable_guest_checkout', 'block_guest_checkout_for_cs_products' );
function block_guest_checkout_for_cs_products( $value ) {
  $restrict_ids = getAllCSWPProductIDs(); // Replace with product ids which cannot use guest checkout
  
  if ( WC()->cart ) {
	$cart = WC()->cart->get_cart();
	foreach ( $cart as $item ) {
	  if ( in_array( $item['product_id'], $restrict_ids ) ) {
		$value = "no";
		break;
	  }
	}
  }
  
  return $value;
}


function coursesource_checkout_fields( $fields ) {
	$coursesource_ids = getAllCSWPProductIDs();
	$cart = WC()->cart->get_cart();

	$force_someone_else = false;
	foreach ($cart as $item)
		if (in_array($item['product_id'],$coursesource_ids) && $item['quantity']>1)
			$force_someone_else = true;

	if ($force_someone_else)
		$fields['order']['someone_else']  = [
			'type' => 'hidden',
			'default' => '1',
		];
	else
		$fields['order']['someone_else']  = [
			'type' => 'checkbox',
			'value' => '1',
			'label' => "I am buying all or some of these courses for someone else",
		];

	return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'coursesource_checkout_fields' );


function coursesource_checkout_field_update_order_meta( $order_id ) {
	if (!empty( $_POST['someone_else'] ) ) {
		update_post_meta( $order_id, 'someone_else', (int)$_POST['someone_else'] );
	}
	update_post_meta( $order_id, 'enrolment_keys_json', '' );
}
add_action( 'woocommerce_checkout_update_order_meta', 'coursesource_checkout_field_update_order_meta' );


function coursesource_order_meta_keys( $keys ) {
	$keys[] = 'Enrolment Keys';
	return $keys;
}
add_filter('woocommerce_email_order_meta_keys', 'coursesource_order_meta_keys');


/* get all cs products that have been imported into WP.
 */
function getAllCSWPProductIDs() {
	$IDs = array();
	
	global $wpdb;
	$results = $wpdb->get_results('SELECT post_id FROM '.$wpdb->prefix.'postmeta WHERE meta_key="_is_coursesource" AND meta_value=1');
	foreach ( $results as $row ) {
		$IDs[] = $row->post_id;
	}
	
	return $IDs;
}



/* Order Completion - Process on CS */
function CS_completed_order($order_id) {
	// process $order_id
	$order = new WC_Order($order_id);
	
	$cs_processed_order = $order->get_meta('enrolments_complete');
	$someone_else = $order->get_meta('someone_else');
	if ( !empty($cs_processed_order) ) { return; }
	
	
	// first, let's check if any of the products in the basket are CS products:
	$course_array = array();
	$orderProducts = $order->get_data();
	foreach ($orderProducts['line_items'] as $prod) {
		$product = $prod->get_product();
		
		$prod['CourseID'] = $product->get_attribute('CourseID');
		$prod['DurationID'] = $product->get_attribute('DurationID');
		$prod['product_id'] = $prod->get_product_id();
		if ( empty($prod['CourseID']) ) { continue; }
		$course_array[] = $prod;
	}
	
	// if no CS products in basket, we don't need to do anything
	if ( empty($course_array) ) {
		return;
	}
	
	
	// get user - loginID == $user->name (from getLoginIDFromUserID)
	$user = $order->get_user();
	if ( !$user ) { error_log('no user found for order'); return; }
	
	// connect
	$api = new cs_api_connection();
	
	// check user exists
	$userExists = $api->checkUser($user);
	
	// if user doesn't exist, create
	if ( !$userExists ) {
		$api->addUser(
			$user->ID,
			get_user_meta($user->ID, 'first_name', true),
			get_user_meta($user->ID, 'last_name', true), 
			$user->data->user_email,
			$api->defaultGroup,
			1
		);
		error_log('CS - creating user');
	}
	
	
	
	// check user again, if still doesn't exist, something has gone wrong, send email
	$userExists = $api->checkUser($user->ID);
	//error_log(print_r($userExists));die();
	if ( !$userExists ) {
		error_log('CS api didn\'t create user');
		$api->emailError('Automated message - API error', 'An error has occurred on '.home_url().' whilst trying to add a user via the API.<br /><br />Site: '.$api->SiteID.'<br />Site Key: '.$api->api_key.'<br />LoginID: '.$user->name);
		return;
	}
	
	// enrol user
	$enrolment_keys = [];
	foreach ($course_array as $course) {

		if ($someone_else)
		{
			$enrolment_result = $api->api_createEnrolmentKey($user->ID, date("Y-m-d"),date("Y-m-d",strtotime("+1 year")), $course['CourseID'], $course['DurationID'], $course['quantity'], $order_id, $course['product_id']);

			if (count($enrolment_result->Errors)>0 || count($enrolment_result->Warnings)>0)
				$api->emailError('Automated message - API error', 'An error has occurred on '.home_url().' whilst trying to create enrolment keys via the API.<br /><br />Site: '.$api->SiteID.'<br />Site Key: '.$api->api_key.'<br />LoginID: '.$user->name.'<br />Enrolment result: <pre>'.print_r($enrolment_result,true).'</pre>');
			
			$enrolment_keys[] = $enrolment_result;
		}
		else
			$api->enrolUser($user->ID, $course['CourseID']);
	}

	$enrolment_keys_display = '';
	foreach ($enrolment_keys as $keys)
		$enrolment_keys_display.='<p><strong>'.implode(', ',$keys->CoursesInKey).'</strong><br>'.implode('<br>',$keys->Keys).'</p>';

	update_post_meta($order_id, 'enrolment_keys_json', json_encode($enrolment_keys));
	update_post_meta($order_id, 'Enrolment Keys', $enrolment_keys_display);
	update_post_meta($order_id, 'enrolments_complete', '1');
}
add_action( 'woocommerce_order_status_processing', 'CS_completed_order', 10, 1);
add_action( 'woocommerce_order_status_completed', 'CS_completed_order', 10, 1);


/**
 * Get the product attribute ID from the name.
 *
 */
function get_attribute_id_from_name( $name ){
	global $wpdb;
	$attribute_id = $wpdb->get_col("SELECT attribute_id
	FROM {$wpdb->prefix}woocommerce_attribute_taxonomies
	WHERE attribute_name LIKE '$name'");
	return reset($attribute_id);
}


function save_product_attribute_from_name( $name, $label='', $set=true ){
	if( ! function_exists ('get_attribute_id_from_name') ) return;

	global $wpdb;

	$label = $label == '' ? ucfirst($name) : $label;
	$attribute_id = get_attribute_id_from_name( $name );

	if( empty($attribute_id) ){
		$attribute_id = NULL;
	} else {
		$set = false;
	}
	$args = array(
		'attribute_id'	  => $attribute_id,
		'attribute_name'	=> $name,
		'attribute_label'   => $label,
		'attribute_type'	=> 'select',
		'attribute_orderby' => 'menu_order',
		'attribute_public'  => 0,
	);

	if( empty($attribute_id) )
		$wpdb->insert(  "{$wpdb->prefix}woocommerce_attribute_taxonomies", $args );

	if( $set ){
		$attributes = wc_get_attribute_taxonomies();
		$args['attribute_id'] = get_attribute_id_from_name( $name );
		$attributes[] = (object) $args;
		//print_pr($attributes);
		set_transient( 'wc_attribute_taxonomies', $attributes );
	} else {
		return;
	}
}