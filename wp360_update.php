<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
//require_once('custom_plugin_update_count.php');

// add_action('wp_head', function(){
//     $aviliable_version = get_option('wp360_plugin_available_version');
//     echo '<pre> Aviliable Version',var_dump( $aviliable_version ); echo '</pre>';
//     echo '<pre> Current Version',var_dump(  get_plugin_version()  ); echo '</pre>';
//    // remove_custom_transient();
//     echo $plugin_slug   = basename(dirname(__FILE__));
    
//   //  $license_file_path = plugin_dir_path( __FILE__ ) . 'token.txt';
//     $token = file_get_contents( $license_file_path );
//     $token           = trim( $token );

//    // echo plugin_basename(__FILE__);
//     echo  $token;
//     echo "****";
//     //die();
// });

add_action('admin_init', function() {
    // Your code to check for plugin updates and perform actions
    require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
    $client = new GuzzleHttp\Client();
    try {
        $repoOwner      = 'wp360-in';
        $repoName       = 'wp360-invoice';
        $response       = $client->request('GET', "https://api.github.com/repos/{$repoOwner}/{$repoName}/releases/latest");
        $releaseData    = json_decode($response->getBody(), true);
        if (isset($releaseData['tag_name'])) {
            $release_version = $releaseData['tag_name'];
        }
    } catch (Exception $e) {
        error_log('WP360 Invoice Error ' .$e->getMessage());
    }
    if (!empty($release_version) && version_compare(get_plugin_version(), $release_version, '<')) {
        error_log('Greater than current version');
        update_option('wp360_plugin_available_version', $release_version);
    }
   
});

add_action('after_plugin_row', 'custom_plugin_update_notice', 10, 2);
function custom_plugin_update_notice($plugin_file, $plugin_data) {

    if ($plugin_data['plugin'] === $plugin_file &&  $plugin_data['Name'] == "Wp360 Invoice") {
        $aviliable_version = get_option('wp360_plugin_available_version');
        if (get_plugin_version() !=  $aviliable_version) {
            ?>
            <tr class="plugin-update-tr active wp360_alert_message" id="">
                <td class="plugin-update colspanchange" colspan="4">
                    <div class="update-message inline notice notice-warning notice-alt"> 
                        <p>
                            <?php
                            printf(
                                __('There is a new version of %s available. <a href="#" class="%s" data-slug="%s">View version %s details</a> or <a href="javascript:void(0)" class="%s"> Update now.</a>', 'wp360-invoice'),
                                'WP360 Invoice', // Plugin name
                                'wp360-invoice-view-details thickbox open-plugin-details-modal', // View details link class
                                urlencode($plugin_file), // Plugin file
                                esc_html($aviliable_version), // Available version
                                'wp360-invoice-update-click' // Update now link class
                            );
                            ?>
                        </p>
                   </div>
                </td>
            </tr> 
            <?php
        }
    }
}

add_action('wp_ajax_update_wp360_invoice', 'update_wp360_invoice_callback');
function update_wp360_invoice_callback() {
    if(isset($_POST['action']) &&  $_POST['action'] == "update_wp360_invoice"){
        $aviliable_version = get_option('wp360_plugin_available_version');
        $plugin_dir     = plugin_dir_path(__FILE__);
        require_once $plugin_dir . 'vendor/autoload.php';
        $repoOwner      = 'wp360-in';
        $repoName       = 'wp360-invoice';
        $branch         = 'main'; 
        $license_file_path      = plugin_dir_path( __FILE__ ) . 'token.txt';
        $token                  = file_get_contents( $license_file_path );
        $token                  = trim( $token );
        $apiUrl         = "https://api.github.com/repos/{$repoOwner}/{$repoName}/contents";
        $clonePath      = plugin_dir_path(__FILE__);
        // Initialize GuzzleHttp client
        $client = new GuzzleHttp\Client();



        fetchFilesFromDirectory($client, $apiUrl, $clonePath, $token);
       
        echo json_encode(
            array(
                'success' => true,
                'aviliableVersion'=>$aviliable_version
            ),
        );

       //  delete_site_transient('update_plugins');
    }
}
function fetchFilesFromDirectory($client, $apiUrl, $localDirectory, $token) {
    $headers = [
       // 'Authorization' => 'token ' . $token,
        'Accept' => 'application/vnd.github.v3+json',
    ];
    $response = $client->request('GET', $apiUrl, [
        'headers' => $headers,
    ]);
    $files = json_decode($response->getBody(), true);
    foreach ($files as $file) {
        if ($file['type'] === 'file') {
            $fileContent = file_get_contents($file['download_url']);
            $localFilePath = $localDirectory . '/' . $file['name'];
            if (file_exists($localFilePath)) {
                file_put_contents($localFilePath, $fileContent);
                error_log(' File '.$file['name'].' updated locally 1. <br>');
            } else {
                file_put_contents($localFilePath, $fileContent);
                error_log('File '.$file['name'].' saved locally 1 <br>');
            }
        } elseif ($file['type'] === 'dir') {
            $subDirectoryUrl = $file['url'];
            $subDirectoryName = $file['name'];
            $subLocalDirectory = $localDirectory . '/' . $subDirectoryName;
            if (!file_exists($subLocalDirectory)) {
                mkdir($subLocalDirectory, 0777, true);
            }
            fetchFilesFromDirectory($client, $subDirectoryUrl, $subLocalDirectory, $token);
        }
    }
}


//SET TRANSIENT FOR NEW UPDATE
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



//view details modal

add_filter( 'plugins_api', 'wp360_plugin_info', 20, 3);

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