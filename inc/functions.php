<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
function wp360invoice_newInvoiceID(){
    $latest_invoice = get_posts(array(
        'post_type'      => 'wp360_invoice',
        'posts_per_page' => 1,
        'orderby'        => 'post_date',
        'order'          => 'DESC',
    ));
    if ($latest_invoice) {
        $latest_invoice_id = $latest_invoice[0]->ID;    
        $invoice_number = get_post_meta($latest_invoice_id, 'invoice_number', true);    
        if ($invoice_number) {
            return wp360invoice_increment_invoice_number($invoice_number);
        } 
    } else {
        $wp360_invoicestartnumber = get_option('wp360_invoicestartnumber', '');
        if($wp360_invoicestartnumber){
            return wp360invoice_increment_invoice_number($wp360_invoicestartnumber);
        }else{
            return 'WP360A95';
        }
    }
}
function wp360invoice_increment_invoice_number($invoice_number) {
    preg_match('/(\d+)$/', $invoice_number, $matches);
    if (!empty($matches[1])) {
        $new_numeric_part = intval($matches[1]) + 1;
        $new_invoice_number = preg_replace('/(\d+)$/', $new_numeric_part, $invoice_number);
        return $new_invoice_number;
    }
    else{
        preg_match('/^([A-Za-z]+)(\d*)$/', $invoice_number, $matcheschar);
        if (!empty($matcheschar[1])) {
            $character_part = $matcheschar[1];
            $numeric_part = $matcheschar[2];
            if (empty($numeric_part) || !is_numeric($numeric_part)) {
                $new_invoice_number = $character_part . '1';
            } 
        }
    }
    return $new_invoice_number;
}


function wp360invoice_wooCustomersList() {
   $customer_query = new WP_User_Query(
       array(
          'fields' => 'ID',
          'role__not_in' => array('administrator'), // Exclude 'administrator' and 'customer' roles
       )
    );
    return $customer_query->get_results();
}

function wp360invoice_register_custom_invoice_post_type() {
    $labels = array(
        'name'               => __('Invoices', 'wp360-invoice'),
        'singular_name'      => __('Invoice', 'wp360-invoice'),
        'menu_name'          => __('Invoices', 'wp360-invoice'),
        // Add more labels as needed
    );
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => false, // Set to true if you want to show in the menu
        'query_var'          => true,
        'rewrite'            => array('slug' => 'invoice'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
    );
    register_post_type('wp360_invoice', $args);
}
add_action('init', 'wp360invoice_register_custom_invoice_post_type');




