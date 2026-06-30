<?php
/**
 * Core plugin loader.
 *
 * Wires together admin, public, shortcode, and Elementor components.
 *
 * @package Amazing_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 */
class Amazing_Offer {

	/**
	 * Settings manager.
	 *
	 * @var Amazing_Offer_Settings
	 */
	protected $settings;

	/**
	 * Products manager.
	 *
	 * @var Amazing_Offer_Products
	 */
	protected $products;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->settings = new Amazing_Offer_Settings();
		$this->products = new Amazing_Offer_Products();
	}

	/**
	 * Register all hooks and run the plugin.
	 *
	 * @return void
	 */
	public function run() {
		// Shared AJAX handlers.
		$this->products->register_hooks();

		// Admin.
		if ( is_admin() ) {
			$admin = new Amazing_Offer_Admin( $this->settings, $this->products );
			$admin->register_hooks();
		}

		// Public front-end.
		$public = new Amazing_Offer_Public( $this->settings, $this->products );
		$public->register_hooks();

		// Shortcode.
		add_shortcode( 'amazing_offer', array( $this, 'render_shortcode' ) );

		// Elementor.
		if ( amazing_offer_is_elementor_active() ) {
			add_action( 'elementor/init', array( $this, 'init_elementor' ) );
		}

		// Additive modules (no-op when none registered).
		$this->load_modules();
	}

	/**
	 * Discover and boot self-contained modules.
	 *
	 * Modules live at modules/<name>/<name>.php and self-register onto the
	 * documented `amazing_offer_modules` filter. The class autoloader does not
	 * scan modules/, so each module bootstrap is required explicitly here; the
	 * module is then responsible for requiring its own classes. With no module
	 * registered the loop is a no-op and behavior is identical to core.
	 *
	 * @return void
	 */
	private function load_modules() {
		// Load ONLY each module's entry point: modules/<name>/<name>.php.
		// (Globbing every *.php would require sibling class files before their
		// bootstrap defines their constants — order-dependent fatal.)
		$module_dirs = glob( AMAZING_OFFER_PLUGIN_DIR . 'modules/*', GLOB_ONLYDIR );
		if ( is_array( $module_dirs ) ) {
			foreach ( $module_dirs as $dir ) {
				$bootstrap = $dir . '/' . basename( $dir ) . '.php';
				if ( file_exists( $bootstrap ) ) {
					require_once $bootstrap;
				}
			}
		}

		/**
		 * Filter the additive modules to boot.
		 *
		 * Each entry is an array with keys: 'label', 'class', 'file', and an
		 * optional 'enabled' flag. A missing 'enabled' key means enabled, to
		 * stay compatible with the documented label/class/file contract.
		 *
		 * @param array $modules Registered modules keyed by slug.
		 */
		$modules = apply_filters( 'amazing_offer_modules', array() );
		if ( ! is_array( $modules ) ) {
			return;
		}

		foreach ( $modules as $module ) {
			if ( ! is_array( $module ) ) {
				continue;
			}
			if ( array_key_exists( 'enabled', $module ) && ! $module['enabled'] ) {
				continue;
			}
			if ( ! empty( $module['file'] ) && file_exists( $module['file'] ) ) {
				require_once $module['file'];
			}
			if ( empty( $module['class'] ) || ! class_exists( $module['class'] ) ) {
				continue;
			}
			$class    = $module['class'];
			$instance = new $class( $this->settings, $this->products );
			if ( method_exists( $instance, 'register_hooks' ) ) {
				$instance->register_hooks();
			}
		}
	}

	/**
	 * Initialize the Elementor integration.
	 *
	 * @return void
	 */
	public function init_elementor() {
		$elementor = new Amazing_Offer_Elementor( $this->settings, $this->products );
		$elementor->register_hooks();
	}

	/**
	 * Shortcode callback.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Rendered HTML.
	 */
	public function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'limit'    => '',
				'title'    => '',
				'source'   => 'saved', // sale | saved | category.
				'category' => '',
				'mode'     => '',
			),
			$atts,
			'amazing_offer'
		);

		$args = array(
			'source'   => sanitize_text_field( $atts['source'] ),
			'category' => absint( $atts['category'] ),
		);

		if ( '' !== $atts['limit'] ) {
			$args['limit'] = absint( $atts['limit'] );
		}
		if ( '' !== $atts['title'] ) {
			$args['title'] = sanitize_text_field( $atts['title'] );
		}
		if ( '' !== $atts['mode'] ) {
			$args['slider_mode'] = sanitize_text_field( $atts['mode'] );
		}

		return Amazing_Offer_Render::render( $args, $this->settings, $this->products );
	}
}
