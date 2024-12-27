<?php
/***
  Plugin Name: Wp360 Invoice
  Description: The WP360 Invoice Plugin provides an intuitive solution to manage and create invoices seamlessly for woocommerce websites checking everywhere. 
  Requires at least: 5.2.0
  License:GPL2
  Tested up to: 6.7.1
  Author: wp360
  Author URI: https://wp360.in/
  Version: 1.0.1
  Text Domain: wp360-invoice
 ***/

if (!defined('ABSPATH') ) {
    exit; // Exit if accessed directly
}

if (!defined('WP360_VERSION')) {
	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 */
	define('WP360_VERSION', '1.0.0');
}

define('WP360_SLUG', 'wp360-invoice');

require_once 'suite/index.php';
require_once 'inc/functions.php';
require_once 'admin/tabs.php';
require_once 'front/myaccount_invoice_tab.php';
require_once 'front/view_invoice.php';
require_once 'admin/wp360_invoice_extra_fields.php';

register_activation_hook(__FILE__, 'wp360invoice_admin_notice_activation_hook');
function wp360invoice_admin_notice_activation_hook() {
  
}
add_action('admin_notices', 'wp360invoice_admin_notice_notice');
function wp360invoice_admin_notice_notice(){
    if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) and current_user_can( 'activate_plugins' ) ) {
        deactivate_plugins(plugin_basename(__FILE__));
        set_transient( 'wp360-admin-notice-error', true, 5 );
        wp360invoice_remove_activation_message();
    }
    if( get_transient( 'wp360-admin-notice-error' ) ){
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php esc_attr_e( 'wp360 Invoice plugin requires Woocommerce plugin to be install.', 'wp360-invoice' ); ?></p>
        </div>
        <?php
        delete_transient( 'wp360-admin-notice-error' );
    }
}
function wp360invoice_remove_activation_message() {
    if ( isset($_GET['activate']) ) {
        unset($_GET['activate']);
    }
}

add_action( 'wp_enqueue_scripts', 'wp360invoice_pluginFrontScripts');
function wp360invoice_pluginFrontScripts(){    
    if (is_account_page()) {
        wp_enqueue_style(WP360_SLUG.'_front_style', plugin_dir_url(__FILE__).'front/assets/css/front_style.css','',WP360_VERSION);
        wp_enqueue_script(WP360_SLUG.'_front_jspdf', plugin_dir_url(__FILE__).'front/assets/js/front-jspdf.js',array('jquery'),WP360_VERSION,true);
        wp_localize_script(WP360_SLUG.'_front_jspdf', 'wp360_pdf_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp360_generate_pdf_nonce'),
        ));
    }
}

add_action('admin_enqueue_scripts', 'wp360invoice_pluginAdminScripts');
function wp360invoice_pluginAdminScripts() {    
    wp_enqueue_media(); 
    wp_enqueue_style(WP360_SLUG.'_admin_style', plugin_dir_url(__FILE__).'admin/css/admin_style.css', array(), WP360_VERSION);
    wp_enqueue_style(WP360_SLUG.'_suite_style', plugin_dir_url(__FILE__).'suite/suite.css', array(), WP360_VERSION);
    wp_enqueue_script('jquery', false, array(), true, true); // Load jQuery in the footer
    wp_enqueue_script(WP360_SLUG.'_admin_js', plugin_dir_url(__FILE__).'admin/js/admin_script.js?v='.time().'', array('jquery'), WP360_VERSION,true); 
    wp_localize_script(WP360_SLUG.'_admin_js', 'wp360_pdf_ajax_admin', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wp360_generate_pdf_nonce'),
    ));
}


function wp360invoice_plugin_activation() {
    $plugin_version = wp360invoice_get_plugin_version();
    if (!get_option('wp360invoice_plugin_version')) {
        update_option('wp360invoice_plugin_version', $plugin_version);
    }
}
function wp360invoice_get_plugin_version() {
    $plugin_data = get_plugin_data(plugin_dir_path(__FILE__) . 'wp360-invoice.php');
    return $plugin_data['Version'];
}
