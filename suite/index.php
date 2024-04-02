<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
if (!class_exists('Wp360_Suite')) {
    class Wp360_Suite {
        public function __construct() {
            // Hook to add the admin menu
            add_action('admin_menu', array($this, 'add_admin_menu'));
        }
        public function add_admin_menu() {
            // Add the top-level menu item
            add_menu_page(
                'Wp360',           // Page title
                'Wp360',           // Menu title
                'manage_options',  // Capability required to access the menu
                'wp360_menu',      // Menu slug
                array($this, 'wp360_page'), // Callback function to display the page
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
                'wp360-invoice/admin/all_invoices.php',
                ''
            );
               // Add submenu page
            add_submenu_page(
                'wp360_menu',
                __('Settings', 'wp360-invoice'),
                __('Settings', 'wp360-invoice'),
                'manage_options',
                'wp360-invoice/admin/wp360_settings.php',
            );

        }
        public function wp360_page() {

            $wp360_plugins = [
                [
                    'title' => 'WP360 Stripe',
                    'description' => 'A tailored plugin for WooCommerce, facilitating seamless integration with the Stripe payment gateway. Enhance your online transactions with a secure and efficient payment experience.',
                    'url' => '#',
                    'status'=>'inactive',
                ],
                [
                    'title' => 'WP360 Tickets',
                    'description' => 'Elevate your event management with WP360 Tickets, a customized WooCommerce plugin designed for seamless ticketing solutions, making your events a breeze to organize and attend.',
                    'url' => '#',
                    'status'=>'inactive',
                ],
                [
                    'title' => 'WP360 OptiPixel',
                    'description' => 'OptiPixel is a powerful image optimization plugin designed to enhance website performance by compressing and optimizing ',
                    'url' => '#',
                    'status'=>'inactive',
                ],
                [
                    'title' => 'WP360 Subscription',
                    'description' => 'Empower your subscription-based business model with WP360 Subscription, a WooCommerce plugin crafted for managing recurring payments and providing a hassle-free subscription experience. ',
                    'url' => '#',
                    'status'=>'inactive',
                ],
                [
                    'title' => 'WP360 Direct Pay Link',
                    'description' => 'Simplify payments with WP360 Direct Pay Link, a WooCommerce plugin that creates direct payment links, offering a quick and secure way for customers to complete transactions without navigating through multiple pages',
                    'url' => '#',
                    'status'=>'inactive',
                ],
                [
                    'title' => 'wp360 invoice',
                    'description' => 'The WP360 Invoice Plugin provides an intuitive solution to manage and create invoices seamlessly for woocommerce websites. ',
                    'url' => '#',
                    'status'=>'active',
                ],
                // Add more plugins as needed
            ];
            echo '<div class="wp360_main_dashboard">
                <div class="wp360_banner">
                     <div class="wp360_logo">
                        <img src="'.esc_url(plugin_dir_url(__FILE__)).'images/logo.svg">
                     </div>
                </div>
                <div class="wp360_content">
                    <div class="head">
                        <h2>'.esc_html__('Welcome to wp360', 'wp360-invoice').'</h2>
                        <p><strong>'.esc_html__('Upcoming Plugin','wp360-invoice' ).'</strong></p>
                    </div>
                    <div class="list">';

                    foreach ($wp360_plugins as $wp360_plugin) {
                        echo '<div class="inner_list ' . esc_html($wp360_plugin['status']) . '">';
                        echo '<div class="icons"><span></span></div>';
                        echo '<div class="wrapitem">';
                        echo '<div class="content">';
                        echo '<h3>' . esc_html($wp360_plugin['title']) . '</h3>';
                        echo '<p>' . esc_html($wp360_plugin['description']) . '</p>';
                        echo '</div>';
                        echo '<div class="link">';
                        
                        // Only display "coming soon" link if the status is not 'active'
                        if ($wp360_plugin['status'] !== 'active') {
                            echo '<a href="' . esc_url($wp360_plugin['url']) . '">' . esc_html__('coming soon', 'wp360-invoice') . '</a>';
                        }
                        
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }

                    echo '</div>
                </div>  
            <div>';
        }
    }
    // Instantiate the class
    $wp360_suite = new Wp360_Suite();
}
