<?php
/**
 * Load + init smoke test (no WordPress required).
 *
 * Drives the REAL plugin load path with stubbed WP functions to catch
 * runtime-at-load fatals that `php -l` cannot see — e.g. files required in the
 * wrong order, undefined constants/classes, or wrong argument counts.
 *
 * Run both contexts:
 *   php tests/load-smoke-test.php          # front-end request
 *   php tests/load-smoke-test.php admin    # wp-admin request
 *
 * Exit: 0 = clean load, 1 = a fatal was caught.
 *
 * Guards against regressions like the v1.1.2 WSOD, where the module loader
 * required a module's class file before its bootstrap defined its constants.
 *
 * @package Amazing_Offer
 */

error_reporting( E_ALL );
define( 'ABSPATH', __DIR__ . '/' );

$GLOBALS['ao_is_admin'] = ( isset( $argv[1] ) && 'admin' === $argv[1] );
$context                = $GLOBALS['ao_is_admin'] ? 'admin' : 'front-end';
$plugin                 = dirname( __DIR__ ) . '/amazing-offer/amazing-offer.php';

// --- hook + option registries -------------------------------------------------
$GLOBALS['ao_actions'] = array();
$GLOBALS['ao_filters'] = array();
$GLOBALS['ao_options'] = array();

// --- WP stubs (only what the load/init path touches) --------------------------
function add_action( $h, $cb, $p = 10, $a = 1 ) { $GLOBALS['ao_actions'][ $h ][] = $cb; return true; }
function add_filter( $h, $cb, $p = 10, $a = 1 ) { $GLOBALS['ao_filters'][ $h ][] = $cb; return true; }
function do_action( $h ) { $a = func_get_args(); array_shift( $a ); if ( ! empty( $GLOBALS['ao_actions'][ $h ] ) ) { foreach ( $GLOBALS['ao_actions'][ $h ] as $cb ) { call_user_func_array( $cb, $a ); } } }
function apply_filters( $h, $v ) { if ( ! empty( $GLOBALS['ao_filters'][ $h ] ) ) { foreach ( $GLOBALS['ao_filters'][ $h ] as $cb ) { $v = call_user_func( $cb, $v ); } } return $v; }
function add_shortcode( $t, $cb ) { return true; }
function register_activation_hook( $f, $cb ) { return true; }
function register_deactivation_hook( $f, $cb ) { return true; }
function plugin_dir_path( $f ) { return rtrim( str_replace( '\\', '/', dirname( $f ) ), '/' ) . '/'; }
function plugin_dir_url( $f ) { return 'http://example.test/wp-content/plugins/' . basename( dirname( $f ) ) . '/'; }
function plugin_basename( $f ) { return basename( dirname( $f ) ) . '/' . basename( $f ); }
function load_plugin_textdomain( $d, $a = false, $p = false ) { return true; }
function is_admin() { return ! empty( $GLOBALS['ao_is_admin'] ); }
function is_multisite() { return false; }
function current_user_can( $c, $id = null ) { return true; }
function __( $t, $d = null ) { return $t; }
function _e( $t, $d = null ) { echo $t; }
function _x( $t, $c, $d = null ) { return $t; }
function esc_html__( $t, $d = null ) { return $t; }
function esc_html_e( $t, $d = null ) { echo $t; }
function esc_attr__( $t, $d = null ) { return $t; }
function esc_attr_e( $t, $d = null ) { echo $t; }
function get_option( $k, $d = false ) { return array_key_exists( $k, $GLOBALS['ao_options'] ) ? $GLOBALS['ao_options'][ $k ] : $d; }
function add_option( $k, $v ) { $GLOBALS['ao_options'][ $k ] = $v; return true; }
function update_option( $k, $v ) { $GLOBALS['ao_options'][ $k ] = $v; return true; }
function delete_option( $k ) { unset( $GLOBALS['ao_options'][ $k ] ); return true; }
function get_transient( $k ) { return false; }
function set_transient( $k, $v, $e = 0 ) { return true; }
function delete_transient( $k ) { return true; }
function register_post_type( $t, $a = array() ) { return (object) array( 'name' => $t ); }
function register_block_type( $n, $a = array() ) { return true; }
function register_rest_route( $ns, $r, $a = array() ) { return true; }
function wp_register_script( $h, $s = '', $d = array(), $v = false, $f = false ) { return true; }
function wp_register_style( $h, $s = '', $d = array(), $v = false, $m = 'all' ) { return true; }
function wp_enqueue_script( $h ) {}
function wp_enqueue_style( $h ) {}
function wp_localize_script( $h, $o, $d ) { return true; }
function wp_enqueue_media() {}
function admin_url( $p = '' ) { return 'http://example.test/wp-admin/' . $p; }
function add_menu_page() { return 'toplevel_page_amazing-offer'; }
function add_submenu_page() { return 'amazing-offer_page_amazing-offer-special'; }
function wp_create_nonce( $a = -1 ) { return 'nonce'; }
function get_bloginfo( $k ) { return '6.5'; }
function did_action( $h ) { return 0; }
function wp_rand( $a = 0, $b = 0 ) { return 5; }

// WooCommerce active, Elementor inactive.
class WooCommerce {}
function WC() { return null; }

$err = null;
try {
	require $plugin;
	if ( function_exists( 'amazing_offer_run' ) ) {
		amazing_offer_run();
	}
	foreach ( array( 'init', 'rest_api_init' ) as $hook ) {
		if ( ! empty( $GLOBALS['ao_actions'][ $hook ] ) ) {
			foreach ( $GLOBALS['ao_actions'][ $hook ] as $cb ) {
				call_user_func( $cb );
			}
		}
	}
} catch ( \Throwable $e ) {
	$err = get_class( $e ) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine();
}

if ( null === $err ) {
	echo "PASS load+init ($context)\nRESULT: ALL PASS\n";
	exit( 0 );
}
echo "FAIL load+init ($context): $err\nRESULT: FAILED\n";
exit( 1 );
