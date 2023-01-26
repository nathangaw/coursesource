<?php

function cs_my_courses_table() {
	global $wp;  
	// get enrol details
	$api = new cs_api_connection();
	$vendors = $api->api_getVendors();
	$userID = get_current_user_id();

	if ( !$userID ) {
		echo '<p class="error">You must be logged in to use this page. Please <a href="'.wp_login_url( get_permalink() ).'">login here</a></p>';
		return;
	}
	$MyCourses = $api->getMyCourses($userID);
	#echodump($MyCourses);
	$current_url = home_url(add_query_arg(array(),$wp->request));
	$join = strpos($current_url, '?') ? '?' : '&';
	/*
	 * url for iframe:
	 //content-cdn.course-source.net/cls_scorm_wrapper/cls_scorm1p2_wrapper_20171121.html?enrollID=4189784&tutorialID=5350001&updateKey=321596&tutorialURL=%2Fcbt%2F100pceffective%2F5S%2F001-5S-Game%2Findex_lms.html&dataURLPrefix=%2F%2Flearner.course-source.net%2Fcs%2Flearning&hasExitButton=0&useOverlaying=0&windowTitle=5S+-+Transform+Your+Workplace+in+5+Steps+%2F+5S+Game&returnURL=https://www.course-source.com/MyCourses/4189784/redirect
	*/
	echo '<table class="csTable myCoursesTable">
		<thead><tr>
		   <td>Title</td>
		   <td>Publisher</td>
		   <td>Expires</td>
		   <td>Last Access</td>
		   <td>Completion Status</td>
		</tr></thead><tbody>';

	foreach ( $MyCourses as $Course ) {
		#$CourseInfo = $api->getLibraryCourse($Course->CourseID);
		$CourseInfo = $api->getCourseInfo($Course->CourseID);
		$VendorID = $CourseInfo->VendorID;
		$VendorName = $vendors[$VendorID];
		echo '<tr class="enrolmentRow">';
			echo '<td><a href="#'.$Course->EnrollID.'" class="openEnrolment">'.$Course->CourseTitle.'</a></td>';
			echo '<td>'.$VendorName.'</td>';
			echo '<td>'.$Course->EndDate.'</td>';
			echo '<td>'.$Course->LastAccessedDate.'</td>';
			echo '<td>'. ($Course->CompleteStatus ? 'Complete' : 'Incomplete').'</td>';
		echo '</tr>';
	}

	echo '</tbody></table><span class="button" id="addEnrolmentKey">+ Use Enrolment Key</span>';
	echo '<div class="myCourseDataContainer"></div>';
	echo '<div id="cs_overlay"></div>';

}
add_shortcode('cs_my_courses_table', 'cs_my_courses_table');

function cs_footer() {
	echo '<div class="hystmodal" id="modalWindow" aria-hidden="true">
		<div class="hystmodal__wrap">
			<div class="hystmodal__window" role="dialog" aria-modal="true">
				<button data-hystclose class="hystmodal__close">Close</button>
				<div id="modalWindowContents"></div>
			</div>
		</div>
	</div>';
}
add_action('wp_footer', 'cs_footer');
add_action('admin_footer', 'cs_footer');

function getMyCourseData() {
	$userID = get_current_user_id();
	
	if (isset($_REQUEST['coursesource_action']) && $_REQUEST['coursesource_action']=='enrol' && $_REQUEST['enrolmentKey']!='')
	{
		$api = new cs_api_connection();
		$enrol_result = $api->api_createEnrolmentFromKey($userID, $_REQUEST['enrolmentKey']);
		
		if (count($enrol_result->Errors)>0)
			die(json_encode(['result'=>false,'error'=>implode(', ',$enrol_result->Errors)]));
		else
			die(json_encode(['result'=>true]));
		
	}

	if ( !isset($_REQUEST['coursesource_action']) || $_REQUEST['coursesource_action'] != 'getMyCourseData' || !is_numeric($_REQUEST['enrolID']) ) {
		return;
	}
	
	$enrolID = $_REQUEST['enrolID'];
	
	$api = new cs_api_connection();
	$myCourseData = $api->getMyCourseData($userID, $enrolID);
	
	$tutorials = $myCourseData->CourseLessons;
	
	$Lessons = array();
	foreach($tutorials as $lesson) {
		if ( !empty($lesson->LessonTitle) ) { 
			echo '<h3>'.$myCourseData->CourseTitle.' - '.$lesson->LessonTitle.'</h3>';
		} else {
			echo '<h3>'.$myCourseData->CourseTitle.'</h3>';
		}

		echo '<table class="csTable openEnrolmentTable">';
		echo '<thead><tr>';
		echo '<td>Module</td>';
		echo '<td>Last Access</td>';
		echo '<td>Completion Status</td>';
		echo '</tr></thead>';
		echo '<tbody>';
		foreach ($lesson->LessonTutorials as $tutorial) {
			$tutURL = empty($tutorial->HighBandwidthURL) ? $tutorial->LowBandwidthURL : $tutorial->HighBandwidthURL;

			echo '<tr>';
			echo '<td><a data-attr_url="'.$tutURL.'" class="tutorialOpen">'.$tutorial->TutorialTitle.'</a></td>';
			echo '<td>'.($tutorial->TimesAccessed > 0 ? $tutorial->LastAccessed : '').'</td>';
			echo '<td>'.($tutorial->CompleteStatus ? 'Complete' : 'Incomplete').'</td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
	}
	
	die();
} add_action('init', 'getMyCourseData');


function closeCourseWindow() {
	if ( !isset($_REQUEST['coursesource_action']) || $_REQUEST['coursesource_action'] != 'closeCourseWindow' ) {
		return;
	}
	
	echo '<html><head>';
	echo '<script>';
		echo 'top.location.reload();';
	echo '</script>';
	echo '</head></html>';
	die();
}add_action('init', 'closeCourseWindow');
?>