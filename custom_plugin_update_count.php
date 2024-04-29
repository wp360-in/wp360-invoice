<?php

// add_filter('pre_set_site_transient_update_plugins', 'custom_plugin_update_count');
// function custom_plugin_update_count($transient) {
//     // $custom_plugin_file = 'wp360-invoice/wp360-invoice.php';
//     // $available_version = get_option('wp360_plugin_available_version');
//     // $installed_version = get_plugin_data(WP_PLUGIN_DIR . '/' . $custom_plugin_file)['Version'];

//  $plugin_basename = plugin_basename(__FILE__); // Get the plugin's basename
//  $custom_plugin_file = dirname($plugin_basename) . '/wp360-invoice.php'; // Combine with '/wp360-invoice.php'
//  $available_version = get_option('wp360_plugin_available_version');
//     $installed_version = get_plugin_data(WP_PLUGIN_DIR . '/' . $custom_plugin_file)['Version'];
//     if (!empty($available_version) && version_compare($available_version, $installed_version, '>')) {
//         // Increment the update count

//      $res = new stdClass();
//      $res->slug = dirname($plugin_basename); // Extract the directory name as the slug
//      $res->plugin = $custom_plugin_file;
//      $res->new_version = $available_version;
//      $res->tested = 'tester';
//      $plugin_slug    = basename(dirname(__FILE__));
//         $res->package = 'https://github.com/KrishnaBtist/wp360-invoice-btist/archive/refs/tags/1.1.2.zip';
//      $transient->response[$res->plugin] = $res;




//         // if (!isset($transient->response[$custom_plugin_file])) {
//         //     $transient->response[$custom_plugin_file] = new stdClass();
//         // }
//         // $transient->response[$custom_plugin_file]->new_version = $available_version;


//     }
//     return $transient;
// }


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
      //  $res->package = 'https://github.com/';
        $transient->response[$res->plugin] = $res;
        //activate_plugin( $plugin_slug); // Reactivate
    }
    return $transient;
}

// add_action('activated_plugin', 'wp360_after_plugin_activation', 10, 2);
// function wp360_after_plugin_activation( $plugin, $network_activation ) {
//     if ( $plugin == plugin_basename(__FILE__) ) {
//         // Plugin is activated, perform any actions here
//         // For example, redirect the user to a specific page
//         wp_redirect('https://staging.wp360.in/wp-admin/plugins.php');
//         exit;
//     }
// }



add_filter( 'plugins_api', 'wp360_plugin_info', 20, 3);
/*
 * $res empty at this step
 * $action 'plugin_information'
 * $args stdClass Object ( [slug] => woocommerce [is_ssl] => [fields] => Array ( [banners] => 1 [reviews] => 1 [downloaded] => [active_installs] => 1 ) [per_page] => 24 [locale] => en_US )
 */
function wp360_plugin_info( $res, $action, $args ){
    // do nothing if this is not about getting plugin information
    // if( 'plugin_information' !== $action ) {
    //  return $res;
    // }
    // do nothing if it is not our plugin
    // if( plugin_basename( __DIR__ ) !== $args->slug ) {
    //  return $res;
    // }
    // info.json is the file with the actual plugin information on your server
    // $remote = wp_remote_get( 
    //  'https://rudrastyh.com/wp-content/uploads/updater/info.json', 
    //  array(
    //      'timeout' => 10,
    //      'headers' => array(
    //          'Accept' => 'application/json'
    //      ) 
    //  )
    // );
    // do nothing if we don't get the correct response from the server
    // if( 
    //  is_wp_error( $remote )
    //  || 200 !== wp_remote_retrieve_response_code( $remote )
    //  || empty( wp_remote_retrieve_body( $remote ) 
    // ) {
    //  return $res;    
    // }
    //$remote = json_decode( wp_remote_retrieve_body( $remote ) );
    $res = new stdClass();
    $res->name = 'wp360-invoice';
    $res->slug = 'wp360-invoice';
    $res->author = 'test';
    $res->author_profile = 'author_profile';
    $res->version = '1.1.5';
    $res->tested = '2.222';
    $res->requires = '9.3';
    $res->requires_php = '8.2';
    $res->download_link = 'google.com';
    $res->trunk = 'google.com';
    $res->last_updated = '9.2';
    $res->sections = array(
        'description' => 'test desc',
        'installation' => 'asdasd instal',
        'changelog' => 'change log'
        // you can add your custom sections (tabs) here
    );
    // in case you want the screenshots tab, use the following HTML format for its content:
    // <ol><li><a href="IMG_URL" target="_blank"><img src="IMG_URL" alt="CAPTION" /></a><p>CAPTION</p></li></ol>
    $res->sections[ 'screenshots' ] = 'https://raw.githubusercontent.com/KrishnaBtist/wp360-invoice-btist/main/screenshots/view_invoice.jpg';

    $res->banners = array(
        'low' => 'https://raw.githubusercontent.com/KrishnaBtist/wp360-invoice-btist/main/screenshots/view_invoice.jpg',
        'high' => 'https://raw.githubusercontent.com/KrishnaBtist/wp360-invoice-btist/main/screenshots/view_invoice.jpg'
    );
    
    return $res;

}





// function delete_custom_plugin_transient($upgrader_object, $options) {
//     if ($options['type'] === 'plugin' && isset($options['plugins']) && is_array($options['plugins'])) {
//         $custom_plugin_file = 'wp360-invoice/wp360-invoice.php';
//         if (in_array($custom_plugin_file, $options['plugins'])) {
//             delete_transient('custom_plugin_update_count');
//         }
//     }
// }






