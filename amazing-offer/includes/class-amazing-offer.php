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
