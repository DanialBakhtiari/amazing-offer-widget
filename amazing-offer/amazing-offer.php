<?php
/**
 * Plugin Name: Amazing Offer Widget
 * Plugin URI: https://danialbakhtiari.com
 * Description: A powerful WooCommerce product offer slider with Elementor widget support.
 * Version: 1.0.1
 * Author: Danial Bakhtiari
 * Author URI: https://danialbakhtiari.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: amazing-offer
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package Amazing_Offer
 */

// Abort if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin constants.
 */
define( 'AMAZING_OFFER_VERSION', '1.0.1' );
define( 'AMAZING_OFFER_PLUGIN_FILE', __FILE__ );
define( 'AMAZING_OFFER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AMAZING_OFFER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AMAZING_OFFER_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * PSR-ish autoloader for plugin classes.
 *
 * Maps class names like `Amazing_Offer_Products` to file paths such as
 * `includes/class-amazing-offer-products.php`. Elementor widget classes are
 * resolved from the `elementor/` tree.
 *
 * @param string $class Fully qualified class name.
 * @return void
 */
function amazing_offer_autoloader( $class ) {
	// Only handle our own classes.
	if ( 0 !== strpos( $class, 'Amazing_Offer' ) && 0 !== strpos( $class, 'Widget_Amazing_Offer' ) ) {
		return;
	}

	$file_name = 'class-' . str_replace( '_', '-', strtolower( $class ) ) . '.php';

	$paths = array(
		AMAZING_OFFER_PLUGIN_DIR . 'includes/' . $file_name,
		AMAZING_OFFER_PLUGIN_DIR . 'admin/' . $file_name,
		AMAZING_OFFER_PLUGIN_DIR . 'public/' . $file_name,
		AMAZING_OFFER_PLUGIN_DIR . 'elementor/' . $file_name,
		AMAZING_OFFER_PLUGIN_DIR . 'elementor/widgets/' . $file_name,
	);

	foreach ( $paths as $path ) {
		if ( file_exists( $path ) ) {
			require_once $path;
			return;
		}
	}
}
spl_autoload_register( 'amazing_offer_autoloader' );

/**
 * Activation hook.
 *
 * @return void
 */
function amazing_offer_activate() {
	require_once AMAZING_OFFER_PLUGIN_DIR . 'includes/class-amazing-offer-activator.php';
	Amazing_Offer_Activator::activate();
}
register_activation_hook( __FILE__, 'amazing_offer_activate' );

/**
 * Deactivation hook.
 *
 * @return void
 */
function amazing_offer_deactivate() {
	require_once AMAZING_OFFER_PLUGIN_DIR . 'includes/class-amazing-offer-deactivator.php';
	Amazing_Offer_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'amazing_offer_deactivate' );

/**
 * Check whether WooCommerce is active.
 *
 * @return bool
 */
function amazing_offer_is_woocommerce_active() {
	return class_exists( 'WooCommerce' );
}

/**
 * Check whether Elementor is active.
 *
 * @return bool
 */
function amazing_offer_is_elementor_active() {
	return did_action( 'elementor/loaded' ) || defined( 'ELEMENTOR_VERSION' );
}

/**
 * Admin notice shown when WooCommerce is missing.
 *
 * @return void
 */
function amazing_offer_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<strong><?php esc_html_e( 'Amazing Offer Widget', 'amazing-offer' ); ?>:</strong>
			<?php esc_html_e( 'این پلاگین برای کارکرد به ووکامرس نیاز دارد. لطفاً ابتدا ووکامرس را نصب و فعال کنید.', 'amazing-offer' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Bootstrap the plugin once all plugins are loaded.
 *
 * @return void
 */
function amazing_offer_run() {
	load_plugin_textdomain( 'amazing-offer', false, dirname( AMAZING_OFFER_PLUGIN_BASENAME ) . '/languages' );

	if ( ! amazing_offer_is_woocommerce_active() ) {
		add_action( 'admin_notices', 'amazing_offer_woocommerce_missing_notice' );
		return;
	}

	$plugin = new Amazing_Offer();
	$plugin->run();
}
add_action( 'plugins_loaded', 'amazing_offer_run' );
