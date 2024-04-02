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
delete_option('wp360_company_address');
delete_option('wp360_thankyoumsg');
delete_option('wp360_invoicestartnumber');

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

