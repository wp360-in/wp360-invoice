<?php

// add_action('admin_init', 'clear_plugin_updates');
// function clear_plugin_updates() {
//     delete_site_transient('update_plugins');
// }
add_filter( 'site_transient_update_plugins', 'wp360_push_update' );
function wp360_push_update( $transient ){
    if ( empty( $transient->checked ) ) {
        return $transient;
    }
    $plugin_basename = plugin_basename(__FILE__); // Get the plugin's basename
    $custom_plugin_file = dirname($plugin_basename) . '/wp360-invoice.php'; // Combine with '/wp360-invoice.php'
    $available_version = get_option('wp360_plugin_available_version');
    $installed_version = get_plugin_data(WP_PLUGIN_DIR . '/' . $custom_plugin_file)['Version'];
    if (!empty($available_version) && version_compare($available_version, $installed_version, '>')) {
        $res = new stdClass();
        $res->slug = dirname($plugin_basename); // Extract the directory name as the slug
        $res->plugin = $custom_plugin_file;
        $res->new_version = $available_version;
        $res->tested = 'tester';
        $plugin_slug    = basename(dirname(__FILE__));
        $transient->response[$res->plugin] = $res;
    }
    return $transient;
}








