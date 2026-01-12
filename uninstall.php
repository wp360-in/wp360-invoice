<?php

/**
 * Uninstall wp360.
 *
 * Remove:
 * - WP360  meta
 *
 * @since 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
delete_option('wp360_thankyoumsg');
delete_option('wp360_invoicestartnumber');
delete_option('wp360_invoice_addresses');
delete_option('wp360_invoice_banking');
delete_option('wp360_firm_details');
delete_option('wp360_invoices_page_id');

$wp360_posts = get_posts(
	[
		'post_type'   => [ 'wp360_invoice'],
		'post_status' => 'any',
		'numberposts' => - 1,
		'fields'      => 'ids',
	]
);


if ( $wp360_posts ) {
	foreach ( $wp360_posts as $wp360_post ) {
		wp_delete_post( $wp360_post, true );
	}
}
// Delete extra fields
$users = get_users();    
if($users){
	foreach ($users as $user) {
		delete_user_meta($user->ID, 'wp360_invoice_user_extra_fields');
	}
}