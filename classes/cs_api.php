<?php

/**
 * Get API Connection details
 */
class cs_api_connection {
	public $api_key;
	public $api_endpoint;
	public $SiteID;
	public $total;
	public $vendorList;
	public $errorEmailAddress;
	
	public $libraryExpiration = 7 * (24 * 60 * 60);
	public $defaultGroup = 'Students';
	
	function __construct() {
		global $wpdb;

		$this->api_key = get_option('cs_api_key', '');
		$this->api_endpoint = get_option('cs_api_endpoint', '');
		$this->SiteID = get_option('cs_siteid', '');
		$this->total = 0;
		$this->errorEmailAddress = 'mark@rubixmedia.co.uk';

		//Fetch on creation so that we always have the data to hand
		$this->api_GetCoursesPaginated();
	}
	
	
	/* error handling */
	public function emailError($subject, $body) {
		die($subject.' - '.$body);
		wp_mail($this->errorEmailAddress, $subject, $body);
	}
	
	function getAllCourses($paged = 1, $perpage = 50, $VendorID=0, $search='') {
		global $wpdb;

		if ($paged<=0) $paged = 1;
		$courseList = $this->api_GetCoursesPaginated((($paged - 1)*$perpage)+($paged - 1), $perpage, $VendorID, $search);
		$offset = (($paged - 1)*$perpage)+($paged - 1);
		#echo '<pre>'.print_r($courseList,true).'</pre>'; die();

		$custom_sku_prefix = get_option('cs_sku_prefix', 'CS');
		$current_skus_sql = "SELECT post_id,meta_value FROM ".$wpdb->prefix."postmeta WHERE meta_key='_sku'";

		$current_skus = [];
		foreach ($wpdb->get_results($current_skus_sql) as $sku_row)
			$current_skus[$sku_row->meta_value] = $sku_row->post_id;

		$this->total = count($courseList);

		$courseList_paged = array_slice($courseList, $offset, $perpage);

		#echo '<pre>'.print_r($current_skus,true).'</pre>'; die();

		$return = [];
		
		foreach ( $courseList_paged as $course ) {
			$return[] = array(
					'CourseID' => $course->CourseID,
					'CourseTitle' => $course->CourseTitle,
					'Price' => $course->Price,
					'VendorName' => $course->VendorName,
					'imported'  => (isset($current_skus[$custom_sku_prefix.$course->CourseID])?$current_skus[$custom_sku_prefix.$course->CourseID]:false)
				);
		}
		return $return;
	}
	
	
	function getCourseInfo($CourseID = null) {
		if ( empty($CourseID) || !is_numeric($CourseID) ) {
			return false;
		}
		$method = 'getCourseInfo';
		$params = array($this->SiteID, $this->api_key, $CourseID, null, null, null, $_SERVER['HTTP_HOST']);
		return $this->do_jsonRpc($method, $params)->result;
	}
	
	function getCourseData($CourseID = null) {
		if ( empty($CourseID) || !is_numeric($CourseID) ) {
			return false;
		}
		$method = 'getCourseData';
		$params = array($this->SiteID, $this->api_key, $CourseID, null, null, null, $_SERVER['HTTP_HOST']);
		return $this->do_jsonRpc($method, $params)->result;
	}
	
	function getCatalogueCourse($CourseID = null) {
		if ( empty($CourseID) || !is_numeric($CourseID) ) {
			return false;
		}
		$method = 'getCatalogueCourse';
		$params = array($this->SiteID, $this->api_key, $CourseID, null, null, null, $_SERVER['HTTP_HOST']);
		return $this->do_jsonRpc($method, $params)->result;
	}
	
	/* checkUser */
	function checkUser($userID) {
		$loginID = $this->getLoginIDFromUserID($userID);
		
		$method = 'checkUser';
		$params = array($this->SiteID, $this->api_key, $loginID);
		$resp = $this->do_jsonRpc($method, $params)->result;
		
		return (empty($resp) || $resp == -1 ? false : true);
	}
	
	
	/* addUser */
	function addUser($userID, $fname, $lname, $email, $group) {
		$loginID = $this->getLoginIDFromUserID($userID);
		
		$method = 'addUser';
		$params = array($this->SiteID, $this->api_key, $loginID, $fname, $lname, $email, $group, 1);
		$resp = $this->do_jsonRpc($method, $params)->result;
	}
	
	public function getLoginIDFromUserID($userID) {
		// KW 7/12/18 switched to user_login instead of userID
		$loginID = wp_get_current_user()->user_login;

		// for wordpress integrations we use the userID as loginID, so it must be numeric
		//if ( !is_numeric($userID) ) { return; }
		
		// pad the number out to a minimum of 6 characters
		// $loginID = str_pad($userID, 6, '0', STR_PAD_LEFT);
		
		return $loginID;
	}
	
	public function enrolUser($userID, $courseID) {
		$loginID = $this->getLoginIDFromUserID($userID);
		
		$method = 'enrolUser';
		$params = array($this->SiteID, $this->api_key, $loginID, $courseID);
		$resp = $this->do_jsonRpc($method, $params)->result;
		
		return $resp;
	}
	
	
	public function getMyCourses($userID) {
		$loginID = $this->getLoginIDFromUserID($userID);
		
		$method = 'getMyCourses';
		$params = array($this->SiteID, $this->api_key, $loginID);
		$resp = $this->do_jsonRpc($method, $params)->result;
		
		return $resp;
	}
	
	public function getMyCourseData($userID, $enrolID) {
		$loginID = $this->getLoginIDFromUserID($userID);
		
		$method = 'getMyCourseData';
		$params = array($this->SiteID, $this->api_key, $loginID, $enrolID);
		$resp = $this->do_jsonRpc($method, $params)->result;
		
		return $resp;
	}

	public function api_getDurations($courseID) {
		$method = 'getDurations';
		$params = array($this->SiteID, $this->api_key, $courseID);
		$resp = $this->do_jsonRpc($method, $params)->result;
		
		return $resp;
	}
	
	public function api_createEnrolmentKey($userID, $ValidFrom, $ValidUntil, $CourseIDs, $DurationIDs, $Quantity, $BasketID, $BasketItemIDs) {
		$loginID = $this->getLoginIDFromUserID($userID);
		$method = 'createEnrolmentKey';
		$params = array($this->SiteID, $this->api_key, $loginID, $ValidFrom, $ValidUntil, $BasketID, [(int)$CourseIDs], [(int)$DurationIDs], $Quantity, null, true, $BasketID, [(int)$BasketItemIDs]);
		$resp = $this->do_jsonRpc($method, $params)->result;
		
		return $resp;
	}
	
	public function api_createEnrolmentFromKey($userID, $enrolmentKey) {
		$loginID = $this->getLoginIDFromUserID($userID);
		
		$method = 'createEnrolmentFromKey';
		$params = array($this->SiteID, $this->api_key, $loginID, $enrolmentKey);
		$resp = $this->do_jsonRpc($method, $params)->result;
		
		return $resp;
	}
	
	public function api_GetCoursesPaginated($offset = 0, $perpage = 50, $VendorID=0, $search='') {

		if (!isset($_SESSION['coursesource']) || time()>$_SESSION['coursesource']['courses_expiry'])
		{
			$method = 'getCoursesPaginated';
			$params = array($this->SiteID, $this->api_key);
			$resp = $this->do_jsonRpc($method, $params)->result;
			#echodump($resp,1);

			$_SESSION['coursesource']['courses'] = $resp;
			$_SESSION['coursesource']['courses_expiry'] = time()+3600; //Only fetch every hour

			$_SESSION['coursesource']['vendors'] = [];
			foreach ($resp->Vendors as $v)
				$_SESSION['coursesource']['vendors'][$v->VendorID] = $v->VendorName;

		}

		$return_courses = [];
		$return_courses = $_SESSION['coursesource']['courses']->Courses;

		if ($search!='')
			foreach ($return_courses as $k=>$c)
				if (strpos(strtolower($c->CourseTitle), strtolower($search))===false)
					unset($return_courses[$k]);

		if ($VendorID)
			foreach ($return_courses as $k=>$c)
				if (strtolower($c->VendorID)!=strtolower($VendorID))
					unset($return_courses[$k]);

		return $return_courses;
	}
	
	public function api_getAllCourses($offset = 0, $perpage = 50, $VendorID = false, $search='') {

		if (!isset($_SESSION['coursesource']) || time()>$_SESSION['coursesource']['courses_expiry'])
		{
			$method = 'getAllCourses';
			$params = array($this->SiteID, $this->api_key, $SubSiteID);
			$resp = $this->do_jsonRpc($method, $params)->result;

			$_SESSION['coursesource']['courses'] = $resp;
			$_SESSION['coursesource']['courses_expiry'] = time()+3600; //Only fetch every hour

		}

		$return_courses = [];
		$return_courses = $_SESSION['coursesource']['courses'];

		if ($search!='')
			foreach ($return_courses as $k=>$c)
				if (strpos(strtolower($c->CourseTitle), strtolower($search))===false)
					unset($return_courses[$k]);

		return $return_courses;
	}
	
	public function api_getVendors() {

		if (isset($_SESSION['coursesource']) && $_SESSION['coursesource']['vendors_expiry']>time())
			return $_SESSION['coursesource']['vendors'];

		$method = 'getVendorsPairs';
		$params = array($this->SiteID, $this->api_key);
		$resp = $this->do_jsonRpc($method, $params)->result;

		$_SESSION['coursesource']['vendors'] = [];
		foreach ($resp as $r)
			$_SESSION['coursesource']['vendors'][$r->VendorID] = $r->VendorName;
		
		$_SESSION['coursesource']['vendors_expiry'] = time()+3600; //Only fetch every hour

		return $_SESSION['coursesource']['vendors'];
	}


	protected function do_jsonRpc($method, $params) {
		if ( !is_array($params) ) { die('Error: Could not connect to API.'); return false; }
		
		$message = array();
		$message['jsonrpc'] = '2.0';
		$message['method'] = $method;
		$message['params'] = $params;
		$message['id'] ='1';
		$data = json_encode($message);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->api_endpoint);
		curl_setopt($ch, CURLOPT_HEADER, false); 
		#curl_setopt($ch, CURLOPT_GET, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);   
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

		$resp = curl_exec($ch);
		$resp = json_decode($resp);
		#echo "\n\n".$data."\n".print_r($resp,true)."\n----------\n";

		curl_close($ch);

		if ($resp===false){
			die('Error: Could not connect to API.');
			return false;
		}
		
		if ( !empty($resp->ErrorMessage) ) {
			die('CS API Error - '.$resp->ErrorMessage);
			return null;
		}
		
		return $resp;
	}
}