<?php
/**
 * Uninstall cleanup.
 *
 * Removes all plugin options, the Special Offer module's templates/meta, and
 * its options when the user deletes the plugin. Multisite-aware.
 *
 * @package Amazing_Offer
 */

// Only run from the WordPress uninstall lifecycle.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Options removed on every site.
 *
 * @var string[]
 */
$amazing_offer_uninstall_options = array(
	// Core (legacy) options — unchanged.
	'amazing_offer_settings',
	'amazing_offer_products',
	'amazing_offer_version',
	// Special Offer module options.
	'amazing_offer_so_enabled',
	'amazing_offer_so_db_version',
);

/**
 * Remove all plugin data for the current site.
 *
 * The Special Offer CPT is not registered during uninstall, so its posts are
 * queried by the literal post-type slug and deleted with their meta.
 *
 * @param string[] $options Option keys to delete.
 * @return void
 */
function amazing_offer_uninstall_site( $options ) {
	foreach ( $options as $option ) {
		delete_option( $option );
	}

	$template_ids = get_posts(
		array(
			'post_type'        => 'ao_special_offer',
			'post_status'      => 'any',
			'numberposts'      => -1,
			'fields'           => 'ids',
			'suppress_filters' => true,
		)
	);
	foreach ( $template_ids as $template_id ) {
		wp_delete_post( (int) $template_id, true );
	}

	delete_transient( 'amazing_offer_sale_products_cache' );
}

if ( is_multisite() ) {
	$sites = get_sites( array( 'fields' => 'ids' ) );
	foreach ( $sites as $blog_id ) {
		switch_to_blog( $blog_id );
		amazing_offer_uninstall_site( $amazing_offer_uninstall_options );
		restore_current_blog();
	}
} else {
	amazing_offer_uninstall_site( $amazing_offer_uninstall_options );
}
