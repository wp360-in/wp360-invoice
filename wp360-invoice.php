<?php
/***
  Plugin Name: Wp360 Invoice
  Description: The WP360 Invoice Plugin provides an intuitive solution to manage and create invoices seamlessly for woocommerce websites checking everywhere. 
  Requires at least: 5.2.0
  License:GPL2
  Tested up to: 6.7.1
  Author: wp360
  Author URI: https://wp360.in/
  Version: 1.0.3
  Text Domain: wp360-invoice
 ***/

if (!defined('ABSPATH') ) {
    exit; // Exit if accessed directly
}

if (!defined('WP360INVOICE_VERSION')) {
	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 */
	define('WP360INVOICE_VERSION', '1.0.3');
}

define('WP360_SLUG', 'wp360-invoice');

require_once 'suite/index.php';
require_once 'inc/functions.php';
require_once 'admin/tabs.php';
// require_once 'front/myaccount_invoice_tab.php';
require_once 'admin/wp360_invoice_extra_fields.php';

add_action('admin_notices', 'wp360invoice_admin_notice_notice');
function wp360invoice_admin_notice_notice(){
    if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) and current_user_can( 'activate_plugins' ) ) {
        // deactivate_plugins(plugin_basename(__FILE__));
        // set_transient( 'wp360-admin-notice-error', true, 5 );
        // wp360invoice_remove_activation_message();
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
    if(get_option('wp360_invoices_page_id') && is_page(get_option('wp360_invoices_page_id'))){
        wp_enqueue_style(WP360_SLUG.'_front_style', plugin_dir_url(__FILE__).'front/assets/css/front_style.css','',WP360INVOICE_VERSION);
        wp_enqueue_script(WP360_SLUG.'_front_jspdf', plugin_dir_url(__FILE__).'front/assets/js/front-jspdf.js',array('jquery'),WP360INVOICE_VERSION,true);
        wp_localize_script(WP360_SLUG.'_front_jspdf', 'wp360_pdf_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp360_generate_pdf_nonce'),
        ));
    }
    
}

add_action('admin_enqueue_scripts', 'wp360invoice_pluginAdminScripts');
function wp360invoice_pluginAdminScripts() {    
    wp_enqueue_media(); 
    wp_enqueue_style(WP360_SLUG.'_admin_style', plugin_dir_url(__FILE__).'admin/css/admin_style.css', array(), WP360INVOICE_VERSION);
        wp_enqueue_style(WP360_SLUG.'_front_style', plugin_dir_url(__FILE__).'front/assets/css/front_style.css','',WP360INVOICE_VERSION);
    wp_enqueue_style(WP360_SLUG.'_suite_style', plugin_dir_url(__FILE__).'suite/suite.css', array(), WP360INVOICE_VERSION);
    wp_enqueue_script('jquery', false, array(), true, true); // Load jQuery in the footer
    wp_enqueue_script(WP360_SLUG.'_admin_js', plugin_dir_url(__FILE__).'admin/js/admin_script.js?v='.time().'', array('jquery'), WP360INVOICE_VERSION,true); 
    wp_localize_script(WP360_SLUG.'_admin_js', 'wp360_pdf_ajax_admin', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wp360_generate_pdf_nonce'),
    ));
}

// Hook into plugin activation
register_activation_hook( __FILE__, 'wp360invoice_plugin_activation_hook' );

function wp360invoice_plugin_activation_hook() {
    update_option('wp360invoice_plugin_activated', true);
    wp360invoice_create_invoices_page();
    update_option('wp360invoice_plugin_version', WP360INVOICE_VERSION);
    wp360invoice_invoice_endpoint();
}

add_action('admin_init', 'wp360invoice_check_for_update');

function wp360invoice_check_for_update() {
    $stored_version = get_option('wp360invoice_plugin_version');    
    // If no version is stored or the version has changed, run the update logic
    if (!$stored_version || version_compare($stored_version, WP360INVOICE_VERSION, '<')) {
        wp360invoice_create_invoices_page(); // Ensure the page exists
        update_option('wp360invoice_plugin_version', WP360INVOICE_VERSION); // Update the stored version
        wp360invoice_invoice_endpoint();
    }
}
function wp360invoice_invoice_endpoint() {
    if (get_option('wp360invoice_plugin_activated')) {
        add_rewrite_endpoint('wp360_invoice', EP_ROOT | EP_PAGES);
        flush_rewrite_rules();
        delete_option('wp360invoice_plugin_activated');
    }
    add_rewrite_endpoint( 'wp360_invoice', EP_ROOT | EP_PAGES );
}

function wp360invoice_create_invoices_page() {
    $existing_page_id = get_option('wp360_invoices_page_id');
    if ($existing_page_id && get_post_status($existing_page_id)) {
        return;
    }
    $slug = 'invoices';

    $existing_page = get_page_by_path($slug);
    $admin_user = get_users([
        'role'    => 'administrator',
        'orderby' => 'ID',
        'order'   => 'ASC',
        'number'  => 1,
    ]);
    
    $admin_id = !empty($admin_user) ? $admin_user[0]->ID : 1;
    if (!$existing_page) {
        $page_id = wp_insert_post([
            'post_title'     => 'Invoices',
            'post_name'      => $slug,
            'post_content'   => '[wp360invoice__pagecontent]', // Add your shortcode or content
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'post_author'    => $admin_id,
        ]);
    } else {
        $unique_slug = wp_unique_post_slug($slug, $existing_page->ID, $existing_page->post_status, $existing_page->post_type, $existing_page->post_parent);

        $page_id = wp_insert_post([
            'post_title'     => 'Invoices',
            'post_name'      => $unique_slug,
            'post_content'   => '[wp360invoice__pagecontent]',
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'post_author'    => $admin_id,
        ]);
    }

    if ($page_id) {        
        update_option('wp360_invoices_page_id', $page_id);        
    }
}

add_shortcode('wp360invoice__pagecontent', function () {
    if (!is_user_logged_in()) {
        return '<h2>Please <a href="'.wp_login_url().'">log in</a> to view your invoices.</h2>';
    }
    ob_start();
    require_once 'wp360-invoice-frontpage.php';
    return ob_get_clean();
});

register_activation_hook( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php', 'wp360invoice_invoice_endpoint' );

// Add Invoice Tab to My Account Page
function wp360invoice_add_invoice_tab_to_my_account( $menu_links ) {
    $invoice_page_id = get_option('wp360_invoices_page_id');
    if ( $invoice_page_id ) {
        $endpoint_slug = get_post_field( 'post_name', $invoice_page_id );
        $menu_links = array_slice( $menu_links, 0, 3, true ) 
            + array( $endpoint_slug => get_the_title( $invoice_page_id ) )
            + array_slice( $menu_links, 3, NULL, true );
    }
    return $menu_links;
}
add_filter( 'woocommerce_account_menu_items', 'wp360invoice_add_invoice_tab_to_my_account', 20 );

add_action( 'wp_enqueue_scripts', function() {
    if ( is_page( get_option( 'wp360_invoices_page_id' ) ) ) {
        wp_enqueue_style( 'dashicons' );
    }
});




add_action('admin_post_wp360invoice_mark_invoice_as_paidAdmin', 'wp360invoice_mark_invoice_as_paid');
function wp360invoice_mark_invoice_as_paid() {
    wp360invoice_handle_mark_paid(array(
        'nonce_field'   => 'wp360invoice_mark_invoice_as_paidAdmin_nonce',
        'nonce_action'  => 'wp360invoice_mark_invoice_as_paidAdmin_action',
        'is_admin'      => true,
    ));
}

add_action('admin_post_wp360invoice_mark_invoice_as_paidUser', 'wp360invoice_mark_invoice_as_paid_user');
function wp360invoice_mark_invoice_as_paid_user() {
    wp360invoice_handle_mark_paid(array(
        'nonce_field'   => 'wp360invoice_mark_invoice_paid_nonce',
        'nonce_action'  => 'wp360invoice_mark_invoice_paid',
        'is_admin'      => false,
    ));
}

/**
 * Common handler for marking an invoice as paid (admin + user contexts).
 */
function wp360invoice_handle_mark_paid($args) {
    $nonce_field  = $args['nonce_field'];
    $nonce_action = $args['nonce_action'];
    $is_admin     = !empty($args['is_admin']);

    if (
        !isset($_POST[$nonce_field]) ||
        !wp_verify_nonce($_POST[$nonce_field], $nonce_action)
    ) {
        wp_die('Nonce verification failed');
    }

    $invoice_id = isset($_POST['invoiceID']) ? intval($_POST['invoiceID']) : 0;

    if (!function_exists('wp_handle_upload')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    // Save notes (common to both)
    $notes = isset($_POST['paymentNotes']) ? sanitize_textarea_field($_POST['paymentNotes']) : '';
    if (!empty($notes)) {
        update_post_meta($invoice_id, 'invoice_paymentnotes', $notes);
    }

    // Handle file upload (only admin uses custom upload dir + restricted mimes)
    if (isset($_FILES['paymentReceipt']) && !empty($_FILES['paymentReceipt']['name'])) {
        $file = $_FILES['paymentReceipt'];
        $file['name'] = time() . '-' . sanitize_file_name($file['name']);

        $upload_overrides = array('test_form' => false);

        if ($is_admin) {
            $allowed_types = array(
                'pdf'  => 'application/pdf',
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png'  => 'image/png',
            );
            $upload_overrides['mimes'] = $allowed_types;
            add_filter('upload_dir', 'wp360invoice_custom_upload_dir');
        }

        $movefile = wp_handle_upload($file, $upload_overrides);

        if ($is_admin) {
            remove_filter('upload_dir', 'wp360invoice_custom_upload_dir');
        }

        if ($movefile && !isset($movefile['error'])) {
            update_post_meta($invoice_id, 'payment_receipt', $movefile['url']);
            update_post_meta($invoice_id, 'invoice_status', 'paid');
        } elseif (!$is_admin) {
            wp_die('Upload error: ' . $movefile['error']);
        }
    } elseif ($is_admin) {
        // Admin flow previously marked paid even without a successful upload check;
        // keep that behavior for backward compatibility.
        update_post_meta($invoice_id, 'invoice_status', 'paid');
    }

    // Redirect back
    if ($is_admin) {
        $referer = wp_get_referer();
        if (!$referer) {
            $referer = admin_url('admin.php?page=wp360_invoice');
        }
        $parsed_url = parse_url($referer);
        $path  = isset($parsed_url['path']) ? basename($parsed_url['path']) : 'admin.php';
        $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $final_url = add_query_arg('uploaded', '1', admin_url($path . $query));
        wp_redirect($final_url);
    } else {
        wp_safe_redirect(add_query_arg('uploaded', '1', wp_get_referer()));
    }
    exit;
}

/**
 * Custom upload directory for admin receipt uploads.
 */
function wp360invoice_custom_upload_dir($dirs) {
    $custom_dir = WP_PLUGIN_DIR . '/wp360-invoice-krishna-wp360/receipt';
    if (!file_exists($custom_dir)) {
        wp_mkdir_p($custom_dir);
    }
    $dirs['path']   = $custom_dir;
    $dirs['url']    = plugins_url('wp360-invoice-krishna-wp360/receipt');
    $dirs['subdir'] = '/receipt';
    return $dirs;
}