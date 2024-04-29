<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
require_once('custom_plugin_update_count.php');

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
    // Send request to GitHub API to get repository contents
    $response = $client->request('GET', $apiUrl, [
        'headers' => $headers,
    ]);
   // echo '<pre>', var_dump( $response ); echo '</pre>';
    $files = json_decode($response->getBody(), true);
  //  echo '<pre>', var_dump( $files ); echo '</pre>';

    // Iterate through each file in the repository
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



// Add action hook to wp_head
// add_action('wp_head', function() {
//     $plugin_dir = plugin_dir_path(__FILE__);
//     require_once $plugin_dir . 'vendor/autoload.php';
//     $repoOwner = 'KrishnaBtist';
//     $repoName = 'wp360-invoice-btist';
//     $branch = 'main'; 
//     $token = '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'; // Replace this with your actual personal access token
//     // GitHub API endpoint to get the repository contents
//     $apiUrl = "https://api.github.com/repos/{$repoOwner}/{$repoName}/contents";
//     // Path where you want to clone the repository
//     $clonePath = plugin_dir_path(__FILE__);
//     // Initialize GuzzleHttp client
//     $client = new GuzzleHttp\Client();
//     // Function to fetch files from a directory
//     function fetchFilesFromDirectory($client, $apiUrl, $localDirectory, $token) {
//         $headers = [
//             'Authorization' => 'token ' . $token,
//             'Accept' => 'application/vnd.github.v3+json',
//         ];
//         // Send request to GitHub API to get repository contents
//         $response = $client->request('GET', $apiUrl, [
//             'headers' => $headers,
//         ]);
//         $files = json_decode($response->getBody(), true);
//         // Iterate through each file in the repository
//         foreach ($files as $file) {
//             if ($file['type'] === 'file') {
//                 $fileContent = file_get_contents($file['download_url']);
//                 $localFilePath = $localDirectory . '/' . $file['name'];
//                 if (file_exists($localFilePath)) {
//                     file_put_contents($localFilePath, $fileContent);
//                     error_log(' File '{$file['name']}' updated locally 1.');
//                 } else {
//                     file_put_contents($localFilePath, $fileContent);
//                     error_log('File '{$file['name']}' saved locally 1 <br>')
//                 }
//             } elseif ($file['type'] === 'dir') {
//                 $subDirectoryUrl = $file['url'];
//                 $subDirectoryName = $file['name'];
//                 $subLocalDirectory = $localDirectory . '/' . $subDirectoryName;
//                 if (!file_exists($subLocalDirectory)) {
//                     mkdir($subLocalDirectory, 0777, true);
//                 }
//                 fetchFilesFromDirectory($client, $subDirectoryUrl, $subLocalDirectory, $token);
//             }
//         }
//     }
//     fetchFilesFromDirectory($client, $apiUrl, $clonePath, $token);
// });


