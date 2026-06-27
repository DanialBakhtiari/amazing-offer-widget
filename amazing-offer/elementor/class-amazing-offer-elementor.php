<?php
/**
 * Elementor integration: category + widget registration.
 *
 * @package Amazing_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor controller.
 */
class Amazing_Offer_Elementor {

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
	 *
	 * @param Amazing_Offer_Settings $settings Settings.
	 * @param Amazing_Offer_Products $products Products.
	 */
	public function __construct( Amazing_Offer_Settings $settings, Amazing_Offer_Products $products ) {
		$this->settings = $settings;
		$this->products = $products;
	}

	/**
	 * Register Elementor hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
	}

	/**
	 * Register a dedicated widget category.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Manager.
	 * @return void
	 */
	public function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'amazing-offer',
			array(
				'title' => __( 'Amazing Offer', 'amazing-offer' ),
				'icon'  => 'eicon-price-list',
			)
		);
	}

	/**
	 * Register the widget.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Manager.
	 * @return void
	 */
	public function register_widgets( $widgets_manager ) {
		require_once AMAZING_OFFER_PLUGIN_DIR . 'elementor/widgets/class-widget-amazing-offer.php';

		$widget = new Widget_Amazing_Offer();
		// Inject dependencies for render.
		$widget->set_dependencies( $this->settings, $this->products );

		$widgets_manager->register( $widget );
	}
}
