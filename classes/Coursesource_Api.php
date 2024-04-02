<?php

/**
 * Get API Connection details
 */
class Coursesource_Api {

	public const TRANSIENT_CORESOURCE_COURSES = 'coresource_net_api_courses';
	public const TRANSIENT_CORESOURCE_VENDORS = 'coresource_net_api_vendors';
	public const TRANSIENT_CORESOURCE_LIFETIME = 3600;

	public $api_key;
	public $api_endpoint;

	public $site_id;

	/**
	 * @var int
	 */
	public $total = 0;


	/**
	 * @var string
	 */
	public $errorEmailAddress;


	public $defaultGroup = 'Students';

	function __construct() {
		$this->api_key           = Coursesource_Woocommerce_Settings::getApiKey();
		$this->api_endpoint      = Coursesource_Woocommerce_Settings::getApiEndpoint();
		$this->site_id           = Coursesource_Woocommerce_Settings::getSiteId();
		$this->errorEmailAddress = Coursesource_Woocommerce_Settings::getErrorEmailRecipient();
		$this->total             = 0;
	}


	/* error handling */
	public function emailError( $subject, $body ) {
		wp_mail( $this->errorEmailAddress, $subject, $body );
		//die( $subject . ' - ' . $body );
	}


	function getCourseInfo( $CourseID = null ) {
		if ( empty( $CourseID ) || !is_numeric( $CourseID ) ) {
			return false;
		}
		$method = 'getCourseInfo';
		$params = array( $CourseID, null, null, null );
		return $this->do_jsonRpc( $method, $params )->result;
	}

	function getCourseData( $CourseID = null ) {
		if ( empty( $CourseID ) || !is_numeric( $CourseID ) ) {
			return false;
		}
		$method = 'getCourseData';
		$params = array( $CourseID, null, null, null );
		return $this->do_jsonRpc( $method, $params )->result;
	}

	function getCatalogueCourse( $CourseID = null ) {
		if ( empty( $CourseID ) || !is_numeric( $CourseID ) ) {
			return false;
		}
		$method = 'getCatalogueCourse';
		$params = array( $CourseID, null, null, null );
		return $this->do_jsonRpc( $method, $params )->result;
	}

    function getCourseImages( $CourseID = null ) {
        if ( empty( $CourseID ) || !is_numeric( $CourseID ) ) {
            return false;
        }
        $method = 'getCourseImages';
        $params = array( $CourseID, null, null, null );
        return $this->do_jsonRpc( $method, $params )->result;
    }

	/**
	 * @param $user
	 *
	 * @return bool
	 */
	function checkUser( $user ) {
		$method  = 'checkUser';
		$loginID = $this->get_coursesource_login_id_from_user( $user );
		$params  = [ $loginID ];
		$resp    = $this->do_jsonRpc( $method, $params )->result;

		return ( empty( $resp ) || $resp == - 1 ? false : true );
	}


	/**
	 * @param $userID
	 * @param $fname
	 * @param $lname
	 * @param $email
	 * @param $group
	 *
	 * @return void
	 */
	function addUser( $userID, $fname, $lname, $email, $group ) {
		$loginID = $this->get_coursesource_login_id_from_user( $userID );

		$method = 'addUser';
		$params = array( $loginID, $fname, $lname, $email, $group, 1 );
		$resp   = $this->do_jsonRpc( $method, $params )->result;
	}

	/**
	 * Provide a simple interface to retrieve the expected login username to Coursesource
	 *
	 * @param int|string|WP_User $user
	 *
	 * @return string
	 */
	public function get_coursesource_login_id_from_user( $user ) {
		// Whatever is feed in, always return the Wordpress user's user_login field
		if ( is_a( $user, 'WP_User' ) ) {
			$user_login = $user->user_login;
		}
		elseif ( is_numeric( $user ) ) {
			$user = get_user_by( 'id', $user );
			if ( $user ) {
				$user_login = $user->user_login;
			}
		}
		elseif ( is_string( $user ) ) {
			$user = get_user_by( 'user_login', $user );
			if ( $user ) {
				$user_login = $user->user_login;
			}
		}

		return $user_login;
	}

	/**
	 * @param $user_login
	 *
	 * @return string
	 */
	public function getLoginIDFromUserID( $user_login = null ) {
		$method = 'checkUser';
		if ( is_null( $user_login ) ) {
			$user_login = wp_get_current_user()->user_login;
		}

//		$params = [ $user_login ];
//		$response = $this->do_jsonRpc($method, $params)->result;
//		if (!empty($response) && $response != -1) {
//			return $user_login;
//		}
//
//		// then check padded UID
//		$user_login = str_pad( $user_login, 6, '0', STR_PAD_LEFT);
//		$method = 'checkUser';
//		$params = array($this->SiteID, $this->api_key, $user_login);
//		$response = $this->do_jsonRpc($method, $params)->result;
//		if (!empty($response) && $response != -1) {return $user_login;}

		// if ( !is_numeric($userID) ) { return; }
		// don't need above because $userID is always numeric UID
		// so if loginID not user_login or padded UID return original

		return $user_login;
	}

	public function enrolUser( $userID, $courseID ) {
		$method  = 'enrolUser';
		$loginID = $this->get_coursesource_login_id_from_user( $userID );

		$params = [ $loginID, $courseID ];
		$resp   = $this->do_jsonRpc( $method, $params )->result;

		return $resp;
	}


	/**
	 * @param $user_id
	 *
	 * @return mixed
	 */
	public function getMyCourses( $user_id = null ) {
		$method          = 'getMyCourses';
		$coursesource_id = $this->get_coursesource_login_id_from_user( $user_id );
		$params          = [ $coursesource_id ];
		return $this->do_jsonRpc( $method, $params )->result;
	}

	public function getMyCourseData( $userID, $enrolID ) {
		$loginID = $this->get_coursesource_login_id_from_user( $userID );

		$method = 'getMyCourseData';
		$params = array( $loginID, $enrolID );
		$resp   = $this->do_jsonRpc( $method, $params )->result;

		return $resp;
	}

	public function api_getDurations( $courseID ) {
		$method = 'getDurations';
		$params = array( $courseID );
		$resp   = $this->do_jsonRpc( $method, $params )->result;

		return $resp;
	}

	public function createEnrolmentKey( $userID, $ValidFrom, $ValidUntil, $CourseIDs, $DurationIDs, $Quantity, $BasketID, $BasketItemIDs ) {
		$method  = 'createEnrolmentKey';
		$loginID = $this->get_coursesource_login_id_from_user( $userID );
		$params  = [
				$loginID,
				$ValidFrom,
				$ValidUntil,
				$BasketID,
				[ (int) $CourseIDs ],
				[ (int) $DurationIDs ],
				$Quantity,
				null,
				true,
				$BasketID,
				[ (int) $BasketItemIDs ],
		];
		$resp    = $this->do_jsonRpc( $method, $params )->result;
		return $resp;
	}


	/**
	 * @param $user_id
	 * @param $enrolment_key
	 *
	 * @return mixed
	 */
	public function createEnrolmentFromKey( $user, $enrolment_key ) {
		$login_id = $this->get_coursesource_login_id_from_user( $user );
		$method   = 'createEnrolmentFromKey';
		$params   = array( $login_id, $enrolment_key );
		return $this->do_jsonRpc( $method, $params )->result;
	}

	function getCourseLibrary() {
		$response = get_transient( self::TRANSIENT_CORESOURCE_COURSES );
		if ( !$response ) {
			$method   = 'getCoursesPaginated';
			$responsePaginated = $this->do_jsonRpc( $method );
			if( property_exists( $responsePaginated, 'result' ) ){
				$response = $this->do_jsonRpc( $method )->result;
                set_transient( self::TRANSIENT_CORESOURCE_COURSES, $response, self::TRANSIENT_CORESOURCE_LIFETIME );
            }
		}

		$courses = [];
		if( $response && property_exists( $response, 'Courses' ) ){
			$courses     = $response->Courses;
			$this->total = count( $courses );
		}
		return $courses;
	}

	/**
	 * @TODO this method is returning all the courses in a single request rather than paginating them. Is this sane?
	 *
	 * @param int    $offset
	 * @param int    $perpage
	 * @param string $vendor_id
	 * @param string $search_term
	 *
	 * @return mixed
	 */
	public function api_GetCoursesPaginated( $offset = 0, $perpage = 50, $vendor_id = 0, $search_term = '' ) {
		$courses = $this->getCourseLibrary();

		//Filter courses by search term
		$search_term = strtolower( trim( $search_term ) );
		if ( $search_term != '' ) {
			foreach ( $courses as $key => $course ) {
                $found = 0;
				$course_title = strtolower( trim( $course->CourseTitle ) );
				if ( strpos( $course_title, $search_term ) !== false ) {
                    $found++;
                }

                $course_id =  (int) $course->CourseID;
                if ( $course_id === (int) $search_term ) {
                    $found++;
                }

                if( $found === 0 ){
                    unset( $courses[$key] );
                }
			}
		}

		// Filter any courses not matching this vendor_id
		$vendor_id = strtolower( trim( $vendor_id ) );
		if ( $vendor_id ) {
			foreach ( $courses as $key => $course ) {
				$course_vendor_id = strtolower( trim( $course->VendorID ) );
				if ( $course_vendor_id != $vendor_id )
					unset( $courses[$key] );
			}
		}

		$this->total = count( $courses );
		//Now paginate the response
		return array_slice( $courses, $offset, $perpage );
	}

	public function getVendors() {
		$vendors = get_transient( self::TRANSIENT_CORESOURCE_VENDORS );
		if ( !$vendors ) {
			$vendors = [];
			$method   = 'getVendorsPairs';
			$params   = array( $this->site_id, $this->api_key );
			$response = $this->do_jsonRpc( $method, $params );
			if( $response && property_exists( $response, 'result' ) ) {
				$result = $response->result;
				foreach ( $result as $vendor ) {
					$vendors[$vendor->VendorID] = $vendor->VendorName;
				}
			}
			set_transient( self::TRANSIENT_CORESOURCE_VENDORS, $vendors, self::TRANSIENT_CORESOURCE_LIFETIME );
		}
		return $vendors;
	}


	/**
	 *
	 * @param $method
	 * @param $params
	 *
	 * @return mixed|void
	 */
	protected function do_jsonRpc( $method, $params = [] ) {
		if ( !is_array( $params ) ) {
			die( 'Error: Could not connect to API.' );
			return false;
		}

		$default_params = [
				$this->site_id,
				$this->api_key
		];
		$params         = array_merge( $default_params, $params );

		$message            = array();
		$message['jsonrpc'] = '2.0';
		$message['method']  = $method;
		$message['params']  = $params;
		$message['id']      = '1';
		$data               = json_encode( $message );

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $this->api_endpoint );
		curl_setopt( $ch, CURLOPT_HEADER, false );
		#curl_setopt($ch, CURLOPT_GET, true);
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( "Content-Type: application/json" ) );

		$resp = curl_exec( $ch );
		$resp = json_decode( $resp );

		curl_close( $ch );

		if ( $resp === false ) {
			die( 'Error: Could not connect to API.' );
			return false;
		}

		//We should probably be logging this...
		if ( !empty( $resp->ErrorMessage ) ) {
			die( 'CS API Error - ' . $resp->ErrorMessage );
			return false;
		}
		return $resp;
	}

}
