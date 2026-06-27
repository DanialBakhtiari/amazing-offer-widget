<?php
/**
 * Public-facing functionality.
 *
 * @package Amazing_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Public controller.
 */
class Amazing_Offer_Public {

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
	 * Whether assets have already been enqueued this request.
	 *
	 * @var bool
	 */
	protected $enqueued = false;

	/**
	 * Constructor.
	 *
	 * @param Amazing_Offer_Settings $settings Settings.
	 * @param Amazing_Offer_Products $products Products.
	 */
	public function __construct( Amazing_Offer_Settings $settings, Amazing_Offer_Products $products ) {
		$this->settings = $settings;
		$this->products = $products;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		// Register (not enqueue) assets early.
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );

		// Enqueue only when a shortcode/widget actually renders.
		add_action( 'amazing_offer_render', array( $this, 'enqueue_assets' ) );

		// Elementor preview always needs the assets.
		add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register public assets.
	 *
	 * @return void
	 */
	public function register_assets() {
		wp_register_style(
			'amazing-offer-public',
			AMAZING_OFFER_PLUGIN_URL . 'public/css/amazing-offer-public.css',
			array( 'dashicons' ),
			AMAZING_OFFER_VERSION
		);

		wp_register_script(
			'amazing-offer-public',
			AMAZING_OFFER_PLUGIN_URL . 'public/js/amazing-offer-public.js',
			array(),
			AMAZING_OFFER_VERSION,
			true
		);

		wp_localize_script(
			'amazing-offer-public',
			'amazingOfferData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'amazing_offer_public' ),
				'i18n'    => array(
					'added'   => __( 'به سبد اضافه شد ✓', 'amazing-offer' ),
					'error'   => __( 'خطا رخ داد', 'amazing-offer' ),
					'loading' => __( 'در حال افزودن...', 'amazing-offer' ),
				),
			)
		);
	}

	/**
	 * Enqueue assets on demand.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( $this->enqueued ) {
			return;
		}
		wp_enqueue_style( 'amazing-offer-public' );
		wp_enqueue_script( 'amazing-offer-public' );
		$this->enqueued = true;
	}
}
