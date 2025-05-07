<?php

namespace Coursesource\Woocommerce\Wp;

use Coursesource\Woocommerce\Coursesource\Api;

class User
{

    public static function init()
    {
        self::add_actions();
        self::add_filters();
    }

    public static function add_actions()
    {
        \add_action( 'user_register',  __CLASS__ . '::cs_user_register', 10, 1 );
        \add_action( 'profile_update',  __CLASS__ . '::cs_user_profile_update', 10, 1 );
        \add_action( 'password_reset',  __CLASS__ . '::cs_user_reset_update', 10, 2 );
        \add_action( 'check_passwords',  __CLASS__ . '::cs_user_password_update', 10, 3 );
    }

    public static function add_filters()
    {}


    /**
     * @param $user_id
     * @return void
     */
    public static function cs_user_register( $user_id  )
    {
        $registration_pass = ( $_POST['pass1'] ) ?? null;
        if( !$registration_pass ){
            $registration_pass = ( $_POST['account_password'] ) ?? null;
        }
        if( $registration_pass ) {
            $user = get_user_by('id', $user_id);
            $api = new Api();
            $exist = $api->checkUser( $user );
            if( !$exist ){
                $fname = get_user_meta($user->ID, 'first_name', true );
                $lname = get_user_meta($user->ID, 'last_name', true );
                $api->addUser( $user->ID, $fname, $lname, $user->user_email , $api->defaultGroup, $registration_pass );
            }
        }
    }

    /**
     * @param $user_id
     * @return void
     */
    public static function cs_user_profile_update( $user_id  )
    {
        $new_pass = ($_POST['password_1'] ) ?? null;
        if( $new_pass ) {
            $user = get_user_by('id', $user_id);
            self::updateUserPassword( $user, $new_pass );
        }
    }

    /**
     * @param WP_User $user
     * @param string $new_pass
     * @return void
     */
    public static function cs_user_reset_update( \WP_User $user, $new_pass  )
    {
        self::updateUserPassword( $user, $new_pass );
    }

    /**
     * @param $user_name
     * @param $new_pass1
     * @param $new_pass2
     * @return void
     */
    public static function cs_user_password_update( $user_name, $new_pass, $new_pass2 )
    {
        $user = get_user_by( 'login', $user_name );
        self::updateUserPassword( $user, $new_pass );
    }


    public static function get_user_groups( $user_id )
    {
        $user = get_user_by( 'ID', $user_id );
        $api = new Api();
        $exist = $api->checkUser( $user );
        $user_groups = $api->getUserGroups( $user->ID );
    }


    public static function updateUserPassword( \WP_User $user, $new_pass )
    {
        $fname = get_user_meta($user->ID, 'first_name', true );
        $lname = get_user_meta($user->ID, 'last_name', true );
        $api = new Api();
        $exist = $api->checkUser( $user );
        if( !$exist ){
            $api->addUser( $user->ID, $fname,  $lname, $user->user_email, $api->defaultGroup, $new_pass );
        }else{
            //Find the Groups this user is already a manager for
            $userData = $api->getUser( $user->ID );
            if( $userData ){
                $api->updateUser( $user->ID, $fname,  $lname, $user->user_email , $userData->GroupName, $new_pass );
            }
        }
    }

}
