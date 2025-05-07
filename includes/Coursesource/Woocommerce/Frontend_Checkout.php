<?php

namespace Coursesource\Woocommerce;

use Coursesource\Woocommerce\Coursesource\Api;
use Coursesource\Woocommerce\Woo\Checkout;
use Coursesource\Woocommerce\Woo\Order;
use stdClass;

class Frontend_Checkout
{

    public static function init()
    {
        self::add_actions();
    }

    public static function add_actions()
    {
        \add_action('wp_enqueue_scripts', __CLASS__ . "::checkout_scripts", 9999);
        \add_action('woocommerce_after_checkout_form', __CLASS__ . "::checkout_modal", 20);

        $ajax_actions = [
            'find_groups_by_email',
            'link_manager_to_group',
            'manager_exists',
            'group_exists',
            'group_similar',
            'group_managers',
            'create_group',
        ];
        foreach ($ajax_actions as $ajax_action) {
            \add_action("wp_ajax_{$ajax_action}", __CLASS__ . "::{$ajax_action}", 10, 1);
            \add_action("wp_ajax_nopriv_{$ajax_action}", __CLASS__ . "::{$ajax_action}", 10, 1);
        }
    }

    /**
     * Add scripts to checkout
     * @return void
     */
    public static function checkout_scripts()
    {
        global $wp;
        if (\is_checkout() && empty($wp->query_vars['order-pay']) && !isset($wp->query_vars['order-received'])) {
            if (Checkout::does_cart_contain_coursesource_products()) {
                $assets = [
                    'js' => [
                        'cs-checkout' => [],
                    ],
                    'css' => [
                        'cs-checkout' => [],
                    ]
                ];
                Common::register_scripts_and_styles($assets);
                $args = [
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('cs-frontend-nonce'),
                    'checkout_enrolment_group' => Order::ORDER_ENROLLMENT_KEYS_GROUP,
                    'checkout_enrolment_manager' => Order::ORDER_ENROLLMENT_MANAGER,
                    'checkout_enrolment_keys_required' => Checkout::does_cart_require_coursesource_keys(),
                    'checkout_enrolment_logged_in' => is_user_logged_in(),
                ];
                \wp_localize_script('cs-checkout-js', COURSESOURCE_JS_OBJECT_NAME, $args);
            }
        }
    }


    /**
     * Add scripts to checkout
     * @return void
     */
    public static function checkout_modal()
    {
        print '<div id="cs-modal-checkout-groups" class="cs-modal cs-modal__checkout-groups" style="display: none;"></div>';
    }

    /**
     * @return void
     */
    public static function find_groups_by_email()
    {
        if (!isset($_REQUEST['nonce']) || !isset($_REQUEST['email'])) {
            die(json_encode([]));
        }
        // Validate the request...
        $nonce = $_REQUEST['nonce'];
        if (!\wp_verify_nonce($nonce, 'cs-frontend-nonce')) {
            die(json_encode([]));
        }
        $response = null;
        $email = sanitize_email($_REQUEST['email']);
        $user = get_user_by('email', $email);
        if ($user) {
            $api = new Api();
            $apiResponse = $api->getUserGroups($user);
            if ($api) {
                $response = ['groups' => $apiResponse];
            }
        }
        print wp_json_encode($response);
        wp_die();
    }

    /**
     * @param $blah
     * @return void
     */
    public static function link_manager_to_group($blah)
    {
        if (!isset($_REQUEST['nonce']) || !isset($_REQUEST['email']) || !isset($_REQUEST['group_id'])) {
            die(json_encode([]));
        }
        // Validate the request...
        $nonce = $_REQUEST['nonce'];
        if (!\wp_verify_nonce($nonce, 'cs-frontend-nonce')) {
            die(json_encode([]));
        }

        $email = $_REQUEST['email'];
        $group_id = $_REQUEST['group_id'];
        $api = new Api();
        $apiResponse = $api->linkManagerToGroup($email, $group_id);
        return $apiResponse;
    }


    public static function manager_exists()
    {
        if (!isset($_REQUEST['nonce']) || !isset($_REQUEST['group_name'])) {
            die(json_encode([]));
        }
        // Validate the request...
        $nonce = $_REQUEST['nonce'];
        if (!\wp_verify_nonce($nonce, 'cs-frontend-nonce')) {
            die(json_encode([]));
        }

        $group_name = sanitize_text_field($_REQUEST['group_name']);
        $api = new Api();
        $groupExists = $api->checkGroup($group_name);
        $response = [
            'result' => true,
        ];
        // Group does not exist...
        if (in_array($groupExists, [0, -3])) {
            $response['result'] = false;
        }
        print wp_json_encode($response);
        wp_die();
    }


    /**
     * @param $blah
     * @return void
     */
    public static function group_exists()
    {
        if (!isset($_REQUEST['nonce']) || !isset($_REQUEST['group_name'])) {
            die(json_encode([]));
        }
        // Validate the request...
        $nonce = $_REQUEST['nonce'];
        if (!\wp_verify_nonce($nonce, 'cs-frontend-nonce')) {
            die(json_encode([]));
        }

        $group_name = sanitize_text_field($_REQUEST['group_name']);

        $api = new Api();
        $groupExists = $api->checkGroup($group_name);
        $response = new stdClass();
        $response->result = true;
        $response->groups = [$group_name];
        // Group does not exist...
        if (in_array($groupExists, [0, -3])) {
            $response->result = false;
            $similarGroups = array_values( self::group_similar() );
            $response->groups = $similarGroups;
        }
        print wp_json_encode($response);
        wp_die();
    }


    /**
     * @return array|void|null
     */
    public static function group_similar()
    {
        if (!isset($_REQUEST['nonce']) || !isset($_REQUEST['group_name'])) {
            die(json_encode([]));
        }
        // Validate the request...
        $nonce = $_REQUEST['nonce'];
        if (!\wp_verify_nonce($nonce, 'cs-frontend-nonce')) {
            die(json_encode([]));
        }

        $group_name = sanitize_text_field($_REQUEST['group_name']);
        $api = new Api();
        $groups = $api->getGroups();
        $similarGroups = array_filter($groups, function ($group) use ($group_name) {
            return self::is_name_similiar($group_name, $group);
        });
        return $similarGroups;
    }

    /**
     * @param $blah
     * @return void
     */
    public static function group_managers()
    {
        if (!isset($_REQUEST['nonce']) || !isset($_REQUEST['group_name'])) {
            die(json_encode([]));
        }
        // Validate the request...
        $nonce = $_REQUEST['nonce'];
        if (!\wp_verify_nonce($nonce, 'cs-frontend-nonce')) {
            die(json_encode([]));
        }

        $group_name = sanitize_text_field($_REQUEST['group_name']);
        $user_email = sanitize_text_field($_REQUEST['user_email']);
        $api = new Api();
        $groupExists = $api->checkGroup($group_name);

        $result = false;
        $managers = [];
        if (!in_array($groupExists, [0, -3])) {
            $result = true;
            $users = self::get_managers_from_same_domain($user_email);
            if (count($users) >= 1) {
                foreach ($users as $user) {
                    //Check if these people are managers of the domain...
                    $groupExistsWithThisManager = $api->checkGroup($group_name, $user);
                    if ($groupExistsWithThisManager === 1) {
                        $managers[] = $user;
                    }
                }
            }
        }
        $response = [
            'result' => $result,
            'managers' => $managers,
        ];
        print wp_json_encode($response);
        wp_die();
    }


    public static function create_group()
    {
        if (!isset($_REQUEST['nonce']) || !isset($_REQUEST['group_name'])) {
            die(json_encode([]));
        }
        // Validate the request...
        $nonce = $_REQUEST['nonce'];
        if (!\wp_verify_nonce($nonce, 'cs-frontend-nonce')) {
            die(json_encode([]));
        }

        $group_name = $_REQUEST['group_name'];
        $api = new Api();
        $groupExists = $api->checkGroup($group_name);
        // Group does not exist...
        if (in_array($groupExists, [0, -3])) {
            $api->addNewGroup($group_name);
        }
        $apiResponse = $api->createGroup($group_name);
        return $apiResponse;
    }


    /**
     * @param $email
     * @return array
     */
    public static function get_managers_from_same_domain($email)
    {
        //Get domain name
        $email_domain = explode('@', sanitize_email($email))[1];
        $args = [
            'search' => '*' . esc_attr($email_domain) . '*',
            'search_columns' => [
                'user_email',
            ],
        ];
        $query = new \WP_User_Query($args);
        return $query->get_results();
    }


    /**
     * @param $name
     * @param $similar_name
     * @param $replacements
     * @param $suffixes
     * @return bool
     */
    public static function is_name_similiar($name, $similar_name, $replacements = [], $suffixes = [])
    {
        $normalised_name = strtolower(trim($name));
        $normalised_similar_name = strtolower(trim($similar_name));
        if (empty($replacements)) {
            $replacements = [' ', '-', '_', ',', '.'];
        }
        if (empty($suffixes)) {
            $suffixes = ['ltd', 'limited', 'inc', 'plc', 'pvt'];
        }

        if ($normalised_name === $normalised_similar_name) {
            return true;
        }

        foreach ($suffixes as $suffix) {
            $nameSuffixPosition = strpos($normalised_name, $suffix);
            $normalised_name = $nameSuffixPosition ? substr($normalised_name, 0, $nameSuffixPosition) : $normalised_name;
            $nameSimilarSuffixPosition = strpos($normalised_similar_name, $suffix);
            $normalised_similar_name = $nameSimilarSuffixPosition ? substr($normalised_similar_name, 0, $nameSimilarSuffixPosition) : $normalised_similar_name;
            if ($normalised_name === $normalised_similar_name) {
                return true;
            }

            foreach ($replacements as $replacement) {
                //Remove any common characters...
                $normalised_name = str_replace($replacement, '', $normalised_name);
                $normalised_similar_name = str_replace($replacement, '', $normalised_similar_name);

                if ($normalised_name === $normalised_similar_name) {
                    return true;
                }
                // If we've got a 1/2 sensible length name to compare against
                if (($normalised_name >= 6)) {
                    // would be nicer to use str_starts_with() but requires PHP8+
                    if (strlen($normalised_name) === 0 || strpos($normalised_similar_name, $normalised_name) === 0) {
                        return true;
                    }
                }
            }
        }

        return false;

    }


}
