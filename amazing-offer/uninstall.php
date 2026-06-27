<?php
/**
 * Uninstall cleanup.
 *
 * Removes all plugin options when the user deletes the plugin.
 *
 * @package Amazing_Offer
 */

// Only run from the WordPress uninstall lifecycle.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$options = array(
	'amazing_offer_settings',
	'amazing_offer_products',
	'amazing_offer_version',
);

foreach ( $options as $option ) {
	delete_option( $option );
}

// Multisite: clean each site.
if ( is_multisite() ) {
	$sites = get_sites( array( 'fields' => 'ids' ) );
	foreach ( $sites as $blog_id ) {
		switch_to_blog( $blog_id );
		foreach ( $options as $option ) {
			delete_option( $option );
		}
		restore_current_blog();
	}
}

delete_transient( 'amazing_offer_sale_products_cache' );
