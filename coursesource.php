<?php
/**
 * Plugin Name: Course Source
 * Description: This plugin is created by Course Source to facilitate interfacing with the Course Source LMS
 * Version: 1.0
 * Text Domain: coursesource
 * Author: CourseSource
 * Author URI: https://www.course-source.com
 */

if(!class_exists('WP_List_Table')){ require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ); }
require_once('classes/cs_api.php');
require_once('classes/courses.php');
require_once('cs_wp_functions.php');
require_once('cs_mycourses.php');

function coursesource_enqueue_admin_scripts() {   
	wp_enqueue_script( 'cs_js', plugins_url('js/cs_js.js', __FILE__), array('jquery'), '1.0' );
	wp_enqueue_script( 'hystmodal.min', plugins_url('js/hystmodal.min.js', __FILE__), array('jquery'), '1.0' );
	wp_enqueue_style( 'cs-admin-css', plugins_url('/css/cs-admin-css.css', __FILE__));
	wp_enqueue_style( 'hystmodal.min', plugins_url('/css/hystmodal.min.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'coursesource_enqueue_admin_scripts');

function coursesource_enqueue_scripts() {
	wp_enqueue_script( 'cs_js_student', plugins_url('js/cs_js_student.js', __FILE__), array('jquery'), '1.0' );
	wp_enqueue_script( 'hystmodal.min', plugins_url('js/hystmodal.min.js', __FILE__), array('jquery'), '1.0' );
	wp_enqueue_style( 'cs-frontend-css', plugins_url('/css/cs-frontend-css.css', __FILE__));
	wp_enqueue_style( 'hystmodal.min', plugins_url('/css/hystmodal.min.css', __FILE__));
}add_action( 'wp_enqueue_scripts', 'coursesource_enqueue_scripts' );

function register_coursesource_submenu_page() {
	add_submenu_page( 'woocommerce', 'Course Source', 'Course Source Settings', 'manage_options', 'course-source', 'coursesource_page_callback' ); 
	add_submenu_page( 'edit.php?post_type=product', 'Course Source', 'Import Products from Course Source', 'manage_woocommerce', 'course-source-import', 'coursesource_import_page_callback' ); 
}
//if ( ! class_exists( 'WP_List_Table' ) ) { require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ); }


function coursesource_page_callback() {
	$attributeArray = array(
		'cs_attribute_name_HoursOfTraining' => 'Training Duration',
		'cs_attribute_name_Publisher'	   => 'Publisher'
	);
	if ( isset($_REQUEST['cs_api_settings']) ) {
		update_option('cs_api_key', sanitize_text_field($_REQUEST['cs_api_key']));
		update_option('cs_api_endpoint', $_REQUEST['cs_api_endpoint']);
		update_option('cs_siteid', $_REQUEST['cs_siteid']);
		update_option('cs_sku_prefix', $_REQUEST['cs_sku_prefix']);
		
		foreach ( $attributeArray as $attrName => $defaultLabel ) {
			$newLabel = empty($_REQUEST[$attrName]) ? $defaultLabel : sanitize_text_field($_REQUEST[$attrName]);
			update_option($attrName, $newLabel);
		}
	}
	
	if ( isset($_REQUEST['cs_sync_definitions']) ) {
		$connection = new cs_api_connection();
		$library = $connection->syncLibraryDefinitions();
		?>
<script>
	jQuery(document).on('ready', function() {
		var library = <?php echo json_encode($library); ?>;
		var libraryCount = library.length;

		jQuery('.libraryImportProgress').html('Processed: 0 / ' + libraryCount);

		function importCourseToWooCommerce(i) {
			courseInfo = library[i];
console.log(courseInfo.CourseID);
			jQuery.ajax({
				url: '',
				method: 'POST',
				data: { coursesource_action: 'importCourseToLibrary', import_id: courseInfo.CourseID, DOING_AJAX:true }
			}).done(function() {
				if ( i < library.length ) {
					i++;
					jQuery('.libraryImportProgress').html('Processed: ' + i + ' / ' + libraryCount);
					importCourseToWooCommerce(i);
				} else {
					jQuery('.libraryImportProgress').html('Processed: ' + i + ' / ' + libraryCount + '. Import Complete.');
				}
			});
		}
		
		importCourseToWooCommerce(0);
	});
</script>
<?php
	}
	
	echo '<h3>Course Source Settings</h3>';
	
	echo '<div class="cs_wrapper">';
		//api settings
		$api_key = get_option('cs_api_key', '');
		$api_endpoint = get_option('cs_api_endpoint', '');
		$cs_siteID = get_option('cs_siteid', '');
		$cs_sku_prefix = get_option('cs_sku_prefix', '');
		if ($cs_sku_prefix=='') $cs_sku_prefix = 'CS';
		?>
		<form action="" method="POST" name="cs_api_settings">
			<h3>General Settings</h3>
			<input type="hidden" name="cs_api_settings" value="1"/>
			<label for="cs_api_key">API Key: <input type="text" name="cs_api_key" id="cs_api_key" value="<?php echo $api_key; ?>" /></label><br />
			<label for="cs_api_endpoint">API Endpoint: <input type="text" name="cs_api_endpoint" id="cs_api_endpoint" value="<?php echo $api_endpoint; ?>" /></label><br />
			<label for="cs_siteid">CourseSource SiteID: <input type="text" name="cs_siteid" id="cs_siteid" value="<?php echo $cs_siteID; ?>" /></label><br /><br /><br />
			
			<p>Warning! Any imported products may no longer be recognised as course source courses. It's best to only change this before any products are imported.</p>
			<label for="cs_sku_prefix">Custom SKU Prefix (Defaults to 'CS'): <input type="text" name="cs_sku_prefix" id="cs_sku_prefix" value="<?php echo $cs_sku_prefix; ?>" /></label><br />
			
			<h3>Attribute Settings</h3>
			Note: Changing these settings will not affect existing products - they must be re-imported to update the attribute names.</p>
			<?php 
			foreach ( $attributeArray as $attrName => $defaultLabel ) {
				echo '<label>"'.$defaultLabel.'" attribute label: <input type="text" name="'.$attrName.'" value="'.get_option($attrName, $defaultLabel).'" /></label><br />';
			}
			?>
			<input type="submit" class="btn" />
		</form>
		<?php
	echo '</div>';
}

function coursesource_import_page_callback() {
	$nonce = wp_create_nonce("my_user_vote_nonce");
	echo '<div class="wrap">';
		echo '<h1>Course Source Courses</h1>';
		echo '<style>';
		echo '.widefat .check-column { width:30px!important; }';
		echo '</style>';
		$course_table = new CourseSource_List();
		$course_table->prepare_items();
		echo '<hr class="wp-header-end">';
		
		echo '<form method="get">';
		echo '<input type="hidden" name="post_type" value="product" />';
		echo '<input type="hidden" name="page" value="'.$_REQUEST['page'].'" />';
		echo $course_table->get_dropdown();
		echo $course_table->search_box( __( 'Search Courses' ), 'course_name' );
		$course_table->display();
		echo '</form>';
		
		// Import visible
		echo '<a href="#" class="button sync_selected">Import/Re-Import Selected</a> ';
		
		// Sync All Button
		echo '<a href="#" class="button import_entire_library">Import/Re-Import Entire Library</a><span class="sync_status"></span>';
		echo '<div style="display:none;">';
			echo '<form class="hidden import_entire_library_form" name="import_entire_library_form" method="POST" action="">';
			wp_nonce_field('cs_import_library', 'cs_import_library_nonce');
			echo '<input type="hidden" name="import_entire_library" value="1" />';
			echo '</form>';
		echo '</div>';
	echo '</div>';
    echo '<a class="user_vote" data-nonce="' . $nonce . '" data-post_id="' . $post->ID . '" href="' . $link . '">vote for this article</a>';
	?>
	<script>
		var myModal;

		jQuery(document).ready(function(){
			myModal = new HystModal();

			jQuery('.coursetitle ').click(function(){
				myModal.open("#modalWindow");
				jQuery('#modalWindowContents').html('<div class="loading-notification">Loading course information...</div>');
				jQuery.get('/wp-admin/admin-ajax.php?action=course_details&nonce=<?=$nonce;?>&course_id='+jQuery(this).data('course-id'),function(response){
					jQuery('#modalWindowContents').html('<table class="csTable horizontal">\
							<tr><th>Title</th><td>'+response.CourseInfo.Course_Title+'</td></tr>\
							'+(response.CourseInfo.Hours_of_Training!=undefined?'<tr><th>Length</th><td>'+response.CourseInfo.Hours_of_Training+'</td></tr>':'')+'\
							<tr><th>Price</th><td>'+response.BuyPriceCurrency+''+response.BuyPrice+'</td></tr>\
							<tr><td colspan="2">'+response.Outline.HTML+'</td></tr>\
						</table>');
				});
			});
			//jQuery('#modalWindowContents').load('');
		});
	</script>

	<?PHP
	
	if ( isset($_REQUEST['import_entire_library']) && check_admin_referer( 'cs_import_library', 'cs_import_library_nonce' ) ) {
		$connection = new cs_api_connection();
		$library = $connection->getLibrary();
		?>
		<script>
			jQuery(document).on('ready', function() {
				var library = <?php echo json_encode($library['results']); ?>;
				var libraryCount = library.length;

				jQuery('.sync_status').html('Processed: 0 / ' + libraryCount);

				function importCourseToWooCommerce(i) {
					courseInfo = library[i];
					jQuery.ajax({
						url: '',
						method: 'POST',
						data: { coursesource_action: 'import_course', import_id: courseInfo.CourseID, DOING_AJAX:true }
					}).done(function() {
						if ( i < library.length ) {
							i++;
							jQuery('.sync_status').html('Processed: ' + i + ' / ' + libraryCount);
							importCourseToWooCommerce(i);
						} else {
							jQuery('.sync_status').html('Processed: ' + i + ' / ' + libraryCount + '. Import Complete.');
						}
					});
				}
				importCourseToWooCommerce(0);
			});
		</script>
	<?php
	}
}
add_action('admin_menu', 'register_coursesource_submenu_page',99);


function importCourseToLibrary() {
	if ( !isset($_REQUEST['coursesource_action']) || $_REQUEST['coursesource_action'] != 'importCourseToLibrary' || !is_numeric($_REQUEST['import_id']) ) {
		return;
	}
	
	$api = new cs_api_connection();
	
	$ImportID = $_REQUEST['import_id'];
	
	$api->loadCourseToLibrary($ImportID);
	
	die();
} add_action('init', 'importCourseToLibrary');



function register_my_session() {
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}
}
add_action('init', 'register_my_session');

function echodump($variable,$do_die=false)
{
	echo '<pre>'.print_r($variable,true).'</pre>';
	if ($do_die) die();
}