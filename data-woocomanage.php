<?php

/**
 * Plugin Name: Data Woocomanage
 * Description: Data client in Woocomanage
 * Author: Woocomanage LTD
 * Author URI: https://github.com/JosefElishayov/data-woocomanage
 * Version: 0.0.9
 */

if (!defined("ABSPATH")) {
    exit;
}

// require 'plugin-update-checker/plugin-update-checker.php';
// use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
// $myUpdateChecker = PucFactory::buildUpdateChecker(
// 	'http://localhost/wordpress/wp-content/uploads/2024/02/data-woocomanage.zip',  
// 	__FILE__, //Full path to the main plugin file or functions.php.
// 	'data-woocomanage'
// );

require 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/JosefElishayov/data-woocomanage',
	__FILE__,
	'data-woocomanage'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('main');

//Optional: If you're using a private repository, specify the access token like this:
$myUpdateChecker->setAuthentication('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');

class DataWoocomanage
{
    public function __construct()
    {
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_plugin_update'));
        add_action('admin_menu', array($this, 'create_custom_data_woocomanage'));
        add_action('rest_api_init', array($this, 'woocomanage_register_rest_route'));
        // add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_plugin_update'));

        // register_activation_hook(__FILE__,  array($this, 'activate_plugin'));
        // add_action('plugins_loaded',  array($this, 'woocomanage_load_textdomain'));
        // register_deactivation_hook(__FILE__,  array($this, 'deactivate_plugin'));
    }


    function check_for_plugin_update($transient)
    {
        $transient->checked = true;

        if (empty($transient->checked)) {

            return $transient;
        }

        $update = $this->check_plugin_version();

        if ($update) {
            // print_r($transient);
            $plugin_path = plugin_basename(__FILE__);            
            $transient->response[$plugin_path] = $update;
        }

        return $transient;
    }

    function check_plugin_version()
    {
        $plugin_data = get_plugin_data(__FILE__);
        $current_version = $plugin_data['Version'];

        // Make a request to your update endpoint
        $response = wp_remote_get("https://woocomanage.com/wp-json/woocomanage/v1/version/");

        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $latest_version = json_decode($body, true);
            if (version_compare($current_version, $latest_version, '<')) {

                // New version available, return update info
                return array(
                    'slug' => "data-woocomanage",
                    'new_version' =>  $latest_version,
                    'package' => 'http://localhost/wordpress/wp-content/uploads/2024/02/data-woocomanage.zip' // Replace with your actual update URL
                );
            }
        }

        return false;
    }




    public function create_custom_data_woocomanage()
    {
        add_menu_page(
            'Data',
            'נתוני משתמשים', // Translated to Hebrew
            'manage_options',
            'data_woocomanage',
            array($this, 'data_woocomanage'),
            'dashicons-database'
        );
    }

    public function data_woocomanage()
    {
        echo "Work";
        $string = ltrim("0.0.5'", "'"); // "0.0.5"
        echo $string;
        $response = wp_remote_get("https://woocomanage.com/wp-json/woocomanage/v1/version/");
        $body = wp_remote_retrieve_body($response);

        $latest_version = trim($body);
        echo  $latest_version;
    }
    public function woocomanage_register_rest_route()
    {
        register_rest_route('woocomanage/v1', '/version/', array(
            'methods'  => 'POST,GET',
            'callback' =>  array($this, 'rest_api_data_woocomanage_version'),
        ));
        register_rest_route('woocomanage/v1', '/pro/', array(
            'methods'  => 'POST,GET',
            'callback' =>  array($this, 'rest_api_data_woocomanage_pro'),
        ));
    }

    public function rest_api_data_woocomanage_version($request)
    {
        $newVersion = '0.0.5';
        if ($request->get_method() === 'GET') {
            return rest_ensure_response($newVersion);
        }
    }

    public function rest_api_data_woocomanage_pro($request)
    {
        $msgCode = "woocomanage_open";
        $newRequest = $request->get_json_params();
        if ($request->get_method() === 'POST') {
            if ($newRequest['code'] === $msgCode) {
                return true;
            }
            return false;
        }
    }
}


new DataWoocomanage();
