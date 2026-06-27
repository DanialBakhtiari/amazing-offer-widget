<?php
/**
 * Fired during plugin activation.
 *
 * @package Amazing_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activator class.
 */
class Amazing_Offer_Activator {

	/**
	 * Seed default settings on activation.
	 *
	 * @return void
	 */
	public static function activate() {
		require_once AMAZING_OFFER_PLUGIN_DIR . 'includes/class-amazing-offer-settings.php';

		if ( false === get_option( 'amazing_offer_settings', false ) ) {
			add_option( 'amazing_offer_settings', Amazing_Offer_Settings::get_defaults() );
		}

		if ( false === get_option( 'amazing_offer_products', false ) ) {
			add_option( 'amazing_offer_products', array() );
		}

		add_option( 'amazing_offer_version', AMAZING_OFFER_VERSION );

		flush_rewrite_rules();
	}
}
