<?php


if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define a secret key to authenticate requests from Git webhook
$secret_key = 'wp360';

// Verify request is coming from Git webhook

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payload']) && !empty($_POST['payload'])) {
    error_log("custom ERROR PAYLOAD" .  json_encode(  $_POST['payload'] ));
    $headers = getallheaders();
    $signature = $headers['X-Hub-Signature'] ?? '';
    // Verify the signature
    if ($signature !== 'sha1=' . hash_hmac('sha1', file_get_contents('php://input'), $secret_key)) {
        header('HTTP/1.0 403 Forbidden');
        die('Forbidden');
    }
    $payload = json_decode($_POST['payload'], true);
    $repo_name = $payload['repository']['full_name'] ?? '';
    $pusher_name = $payload['pusher']['name'] ?? '';
    $commits = $payload['commits'] ?? [];
    $fileDirectotry = plugin_dir_path(__FILE__);
    foreach ($commits as $commit) {
        error_log('CUSTOM ERROR MESSAGE: 2' .$commit['message']);
       
        $plugin_version  = '1.0.1';
        update_option('wp360_plugin_version', $plugin_version);
        $output = shell_exec('cd ' . $fileDirectory . ' && git pull https://github.com/KrishnaBtist/wp360-invoice.git master');
        if ($output === false) {
            // Handle error if git pull failed
            error_log('Git pull failed');
            // Optionally, send a notification or log the error
        } else {
            // Handle successful pull
            error_log('Git pull successful');
            // Optionally, send a notification or log the success
        }
    }
} else {
}


add_action('wp_head', function(){
    $version = get_option('wp360_plugin_version');
    echo '<pre>',var_dump( $version ); echo '</pre>';

    $plugin_data = get_plugin_data(plugin_dir_path(__FILE__) . 'wp360-invoice.php');

   // echo '<pre>',var_dump( $plugin_data ); echo '</pre>';
    echo '<pre>',var_dump(  $plugin_data['Version']  ); echo '</pre>';

    echo  $fileDirectotry = plugin_dir_path(__FILE__);

});


add_action('admin_notices', 'custom_plugin_display_status');

function custom_plugin_display_status() {
    $current_version    = get_option('wp360_plugin_version');
    $plugin_version     = get_plugin_version();

    if ($current_version === $plugin_version) {
        $status = 'Plugin is up to date';
    } else {
        $status = 'Plugin update available';
    }

    echo '<div class="notice notice-info"><p>' . $status . '</p></div>';
}


add_action('after_plugin_row', 'custom_plugin_update_notice', 10, 2);

function custom_plugin_update_notice($plugin_file, $plugin_data) {
    // Check if the plugin file matches your custom plugin
  
    if ("wp360-invoice/wp360-invoice.php" === $plugin_file) {
       // echo "*****".$plugin_file;
        $updated_version = get_option('wp360_plugin_version'); // Replace 'wp360_plugin_version' with your actual option key
       // echo '<pre>',var_dump(  $current_version  ); echo '</pre>';
        $plugin_data    = get_plugin_data(plugin_dir_path(__FILE__) . 'wp360-invoice.php');
        $latest_version = $plugin_data['Version'];
        // Check if an update is available
       // echo '<pre>',var_dump(  $latest_version  ); echo '</pre>';
        if ($latest_version != $updated_version ) {
            // Display the update notice
            echo '<tr class="plugin-update-tr"><td colspan="3" class="plugin-update colspanchange"><div class="update-message notice inline notice-warning notice-alt"><p>Update available of Wp360 Invoice. <a href="#">Update now</a></p></div></td></tr>';
        }
    }
}

//GIT CLONE


// Function to clone or update a Git repository
function git_clone_or_update($repository_url, $destination_path, $git) {
 
    if (!is_dir($destination_path)) {
        mkdir($destination_path, 0755, true);
        $git->cloneRepository($repository_url, $destination_path);
        return "Repository cloned successfully.";
    } else {
        // echo "checking file exist";
        // die();
        $git->open($destination_path)->pull('origin');
        return "Repository updated successfully.";
    }
}

add_action('wp_head', function() {
    $plugin_dir = plugin_dir_path(__FILE__);
    // Include Composer's autoloader
    require_once $plugin_dir . 'vendor/autoload.php';
    $git = new \CzProject\GitPhp\Git();


   // $git = new Git();
    // Example usage: Replace these values with your Git repository URL and destination path
    $repository_url = 'https://github.com/KrishnaBtist/wp360-invoice.git';
    $destination_path = plugin_dir_path(__FILE__) . 'wp360-invoice/';
    $output = git_clone_or_update($repository_url, $destination_path, $git);
    echo $output; // Output any result or error messages
});
