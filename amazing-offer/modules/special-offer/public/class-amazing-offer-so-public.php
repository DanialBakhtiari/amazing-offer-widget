<?php
/**
 * Special Offer public controller: registers + lazily enqueues module assets.
 *
 * Uses its OWN enqueue action (amazing_offer_so_render) and module-prefixed
 * handles so Swiper never loads for the legacy [amazing_offer] output.
 *
 * @package Amazing_Offer\Special_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Public controller.
 */
class Amazing_Offer_SO_Public {

	/**
	 * Core products manager (for add-to-cart reuse context).
	 *
	 * @var Amazing_Offer_Products
	 */
	protected $products;

	/**
	 * Whether assets were already enqueued this request.
	 *
	 * @var bool
	 */
	protected $enqueued = false;

	/**
	 * Constructor.
	 *
	 * @param Amazing_Offer_Products $products Core products manager.
	 */
	public function __construct( $products ) {
		$this->products = $products;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'amazing_offer_so_render', array( $this, 'enqueue_assets' ) );
		add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register (not enqueue) module assets.
	 *
	 * @return void
	 */
	public function register_assets() {
		wp_register_style(
			'ao-so-swiper',
			AMAZING_OFFER_SO_URL . 'public/vendor/swiper/swiper-bundle.min.css',
			array(),
			'11.2.10'
		);
		wp_register_style(
			'ao-so-public',
			AMAZING_OFFER_SO_URL . 'public/css/ao-so-public.css',
			array( 'ao-so-swiper', 'dashicons' ),
			AMAZING_OFFER_SO_VERSION
		);

		wp_register_script(
			'ao-so-swiper',
			AMAZING_OFFER_SO_URL . 'public/vendor/swiper/swiper-bundle.min.js',
			array(),
			'11.2.10',
			true
		);
		wp_register_script(
			'ao-so-public',
			AMAZING_OFFER_SO_URL . 'public/js/ao-so-public.js',
			array( 'ao-so-swiper' ),
			AMAZING_OFFER_SO_VERSION,
			true
		);

		wp_localize_script(
			'ao-so-public',
			'amazingOfferSOData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'amazing_offer_public' ),
				'i18n'    => array(
					'added'   => __( 'به سبد اضافه شد ✓', 'amazing-offer' ),
					'error'   => __( 'خطا رخ داد', 'amazing-offer' ),
					'prev'    => __( 'قبلی', 'amazing-offer' ),
					'next'    => __( 'بعدی', 'amazing-offer' ),
				),
			)
		);
	}

	/**
	 * Enqueue assets on demand (once per request).
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( $this->enqueued ) {
			return;
		}
		wp_enqueue_style( 'ao-so-swiper' );
		wp_enqueue_style( 'ao-so-public' );
		wp_enqueue_script( 'ao-so-swiper' );
		wp_enqueue_script( 'ao-so-public' );
		$this->enqueued = true;
	}
}
