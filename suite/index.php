<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
if (!class_exists('Wp360_Suite')) {
    class Wp360_Suite {
        public function __construct() {
            // Hook to add the admin menu
            add_action('admin_menu', array($this, 'wp360invoice_add_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'wp360invoice_enqueue_scripts_and_styles'));
            
            add_action('wp_ajax_generate_invoice_pdf', array($this, 'wp360_invoice_generate_pdf'));
            add_action('wp_ajax_nopriv_generate_invoice_pdf', array($this, 'wp360_invoice_generate_pdf'));
        }
        public function wp360_invoice_generate_pdf() {
            check_ajax_referer('wp360_generate_pdf_nonce', 'nonce');             
            require ABSPATH . 'wp-content/plugins/' . WP360_SLUG .'/wp360_invoice_pdf.php';
            wp_die();
        }
        public function wp360invoice_enqueue_scripts_and_styles() {    
            wp_enqueue_style('wp360_suite_style', plugin_dir_url(__FILE__) . 'css/suite.css', array(), '1.0.0');
        }

        public function wp360invoice_add_admin_menu() {
            // Add the top-level menu item
            add_menu_page(
                'Wp360',           // Page title
                'Wp360',           // Menu title
                'manage_options',  // Capability required to access the menu
                'wp360_menu',      // Menu slug
                array($this, 'wp360invoice_dashboard_page'), // Callback function to display the page
                //'wp360-invoice/suite/dashboard.php',
                'dashicons-admin-generic', // Dashicon for the menu
                20                 // Position in the menu
            );           
            // You can add more submenus as needed
            // add_submenu_page(...);
            add_submenu_page( 
                'wp360_menu',
                __('wp360 invoice', 'wp360-invoice'),
                __('wp360 invoice', 'wp360-invoice'),
                'manage_options',
                'wp360_invoice',
                array($this, 'wp360invoice_render_page'),
            );
        }
        public function wp360invoice_dashboard_page() {
            echo '<div class="wp360_main_dashboard">
                <div class="wp360_banner">
                     <div class="wp360_logo">
                        <img src="'.esc_url(plugin_dir_url(__FILE__)).'images/logo.svg">
                     </div>
                </div>
                <div class="wp360_content">
                    <div class="head">
                        <h2>'.esc_html__('What else do we have for you?', 'wp360-invoice').'</h2>
                    </div>
                    <div class="list">';
                    foreach ($this->wp360invoice_addons() as $addon) {
                        $status = 'coming_up';
                        $actionName = __('Coming Soon', 'wp360-invoice');
                        $action = 'javascript:;';
                        // if(isset($addon['repo_url'])){
                        //     $action = $addon['repo_url'];
                        // }
                        

                        $target = '';
                        $pathpluginurl = WP_PLUGIN_DIR .'/'. $addon['path'];
                        $isinstalled = file_exists( $pathpluginurl );
                        if ($isinstalled ) {
                            $status = 'installed';
                            $actionName = __('Activate', 'wp360-invoice');
                            $action = '#activateplugin';
                        }
                        if (is_plugin_active($addon['path'])) {
                            $status = 'activated';
                            $actionName = __('Activated', 'wp360-invoice');
                            $action = 'javascript:;';
                        } 
                        if(!isset($addon['repo_url']) || !$addon['repo_url']){
                            $status = 'coming_up';
                            $actionName = __('Coming Soon', 'wp360-invoice');
                            $action = 'javascript:;';
                        }
                        echo '
                        <div class="inner_list ' . esc_html($status) . '">
                            <div class="icons"><img src="'.esc_url(plugins_url('/', __FILE__)).'images/'.esc_html($addon['icon']).'"/></div>
                            <div class="wrapitem">
                                <div class="content">
                                    <h3>' . esc_html($addon['title']). '</h3>
                                    <p>' . esc_html($addon['description']) . '</p>
                                </div>
                                <div class="link">
                                <a href="' .esc_url($action) . '" class="' . esc_html($status) . '" '.((esc_html($actionName) == 'Get it'?'target="_blank"':'')).'>' . esc_html($actionName) . '</a>
                                </div>
                            </div>
                        </div>';
                    }

                    echo '</div>
                </div>  
            <div>';
        }

        public function wp360invoice_addons(){
            $addons = [
                [
                    'title' => 'WP360 Invoice',
                    'description' => 'The addon provides an intuitive solution to manage and create invoices seamlessly for woocommerce websites.',
                    'repo_url' => 'https://github.com/wp360-in/wp360-invoice/',
                    'path'=>'wp360-invoice/wp360-invoice.php',
                    'icon'=>'wp360-invoice-icon.png'
                ],
                [
                    'title' => 'WP360 Stripe',
                    'description' => 'A tailored plugin for WooCommerce, facilitating seamless integration with the Stripe payment gateway. Enhance your online transactions with a secure and efficient payment experience.',
                    'repo_url' => 'https://github.com/wp360-in/wp360-stripe/',
                    'path'=>'wp360-stripe/wp360-stripe.php',
                    'icon'=>'wp360-stripe-icon.png'
                ],
                [
                    'title' => 'WP360 Optipixel',
                    'description' => 'A tailored plugin for WooCommerce, facilitating seamless integration with the Stripe payment gateway. Enhance your online transactions with a secure and efficient payment experience.',
                    'repo_url' => 'https://github.com/wp360-in/wp360-optipixel/',
                    'path'=>'wp360-optipixel/wp360-optipixel.php',
                    'icon'=>'wp360-invoice-icon.png'
                ],
                [
                    'title' => 'WP360 Subscription',
                    'description' => 'The addon provides an intuitive solution to manage and create invoices seamlessly for woocommerce websites.',
                    'repo_url' => 'https://github.com/wp360-in/wp360-subscription/',
                    'path'=>'wp360-subscription/wp360-subscription.php',
                    'icon'=>'wp360-subscription-icon.png'
                ],
                [
                    'title' => 'WP360 Paypal',
                    'description' => 'A tailored plugin for WooCommerce, facilitating seamless integration with the Stripe payment gateway. Enhance your online transactions with a secure and efficient payment experience.',
                    //'repo_url' => 'https://github.com/wp360-in/wp360-paypal/',
                    'path'=>'wp360-paypal/wp360-paypal.php',
                    'icon'=>'wp360-paypal-icon.png'
                ],
                [
                    'title' => 'WP360 Tickets',
                    'description' => 'A tailored plugin for WooCommerce, facilitating seamless integration with the Stripe payment gateway. Enhance your online transactions with a secure and efficient payment experience.',
                    //'repo_url' => 'https://github.com/wp360-in/wp360-stripe/',
                    'path'=>'wp360-optipixel/wp360-optipixel.php',
                    'icon'=>'wp360-ticket-icon.png'
                ],
                // Add more plugins as needed
            ];
            return $addons;
        }
        public function wp360invoice_render_page(){
            if(isset($_GET['tab']) && $_GET['tab'] === 'settings'){
                require_once plugin_dir_path(__FILE__).'../admin/wp360_invoice_settings.php';
            }
            else{
                require_once plugin_dir_path(__FILE__).'../admin/all_invoices.php';
            }            
        }
    }
    // Instantiate the class
    $wp360_suite = new Wp360_Suite();
}
