<?php
/**
 * Special Offer module bootstrap.
 *
 * Self-registers onto the `amazing_offer_modules` filter consumed by the core
 * loader. Loaded explicitly by Amazing_Offer::load_modules() because the class
 * autoloader does not scan modules/.
 *
 * @package Amazing_Offer\Special_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AMAZING_OFFER_SO_VERSION', '1.0.0' );
define( 'AMAZING_OFFER_SO_DIR', plugin_dir_path( __FILE__ ) );
define( 'AMAZING_OFFER_SO_URL', plugin_dir_url( __FILE__ ) );

/**
 * Register the Special Offer module on the core modules filter.
 *
 * @param array $modules Registered modules.
 * @return array
 */
function amazing_offer_so_register_module( $modules ) {
	$modules['special_offer'] = array(
		'label'   => __( 'پیشنهاد ویژه', 'amazing-offer' ),
		'class'   => 'Amazing_Offer_SO_Module',
		'file'    => AMAZING_OFFER_SO_DIR . 'class-amazing-offer-so-module.php',
		'enabled' => (bool) get_option( 'amazing_offer_so_enabled', true ),
	);
	return $modules;
}
add_filter( 'amazing_offer_modules', 'amazing_offer_so_register_module' );
