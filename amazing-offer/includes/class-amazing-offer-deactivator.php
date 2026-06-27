<?php
/**
 * Fired during plugin deactivation.
 *
 * @package Amazing_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deactivator class.
 */
class Amazing_Offer_Deactivator {

	/**
	 * Clean up transient state on deactivation.
	 *
	 * Settings and saved products are preserved; full removal happens in
	 * uninstall.php.
	 *
	 * @return void
	 */
	public static function deactivate() {
		delete_transient( 'amazing_offer_sale_products_cache' );
		flush_rewrite_rules();
	}
}
