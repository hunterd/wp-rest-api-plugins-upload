<?php
/*
Plugin Name: WP REST API Plugins Upload
Description: Adds an endpoint to the WordPress REST API to securely upload a plugin.
Version: 1.0
Author: David Mussard
Author URI: https://davidmussard.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-rest-api-plugins-upload
Domain Path: /languages
*/

// languages
define('WP_REST_API_PLUGINS_UPLOAD_TEXTDOMAIN', 'wp-rest-api-plugins-upload');

function wp_rest_api_plugins_upload_load_textdomain() {
    load_plugin_textdomain("wp-rest-api-plugins-upload", false, basename(dirname(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'wp_rest_api_plugins_upload_load_textdomain');
// end languages


add_action('rest_api_init', function () {
    register_rest_route('api-rest-plugin-upload/v1', '/upload/', array(
        'methods' => 'POST',
        'callback' => 'upload_install_plugin',
        'permission_callback' => function () {
            return current_user_can('install_plugins');
        }
    ));
});

function find_plugin_path($installed_plugins, $plugin_name) {
    $plugin_base = basename($plugin_name, '.zip');
    foreach ($installed_plugins as $path => $details) {
        if (strpos($path, $plugin_base) === 0) {
            return $path;
        }
    }
    return null;
}

function upload_install_plugin(WP_REST_Request $request) {
    $files = $request->get_file_params();

    if (empty($files['pluginfile'])) {
        return new WP_Error('no_plugin_file', __('No file submitted', "wp-rest-api-plugins-upload"), array('status' => 400));
    }

    $file = $files['pluginfile'];

    if ('zip' !== pathinfo($file['name'], PATHINFO_EXTENSION)) {
        return new WP_Error('invalid_file_type', __('Must be a zip file', "wp-rest-api-plugins-upload"), array('status' => 400));
    }

    $temporary_file = wp_upload_bits($file['name'], null, file_get_contents($file['tmp_name']));
    if ($temporary_file['error']) {
        return new WP_Error('upload_error', __('Error uploading file: ', "wp-rest-api-plugins-upload") . $temporary_file['error'], array('status' => 500));
    }

    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    WP_Filesystem();

    $unzip_result = unzip_file($temporary_file['file'], WP_PLUGIN_DIR . '/' . basename($file['name'], '.zip') );
    
    if (is_wp_error($unzip_result)) {
        @unlink($temporary_file['file']);
        return $unzip_result;
    }

    $plugin_slug = basename($file['name'], '.zip');

    @unlink($temporary_file['file']);
    return new WP_REST_Response(['message' => __('Plugin uploaded successfully', "wp-rest-api-plugins-upload"), 'slug' => $plugin_slug], 200);
}

function add_paypal_me_button_in_plugins_list( $links ) {
    $links[] = '<a target="_blank" style="font-weight:bold" href="' . esc_url( "paypal.me/davidmussard" ) . '">' . __("Donate", "wp-rest-api-plugins-upload") . '</a>';
    return $links;
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'add_paypal_me_button_in_plugins_list' );

?>