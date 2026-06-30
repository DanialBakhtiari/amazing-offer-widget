<?php
/**
 * Special Offer module controller.
 *
 * Wires the module's data layer and (in later phases) its public renderer,
 * admin manager, shortcode, block, and Elementor widget. Everything here is
 * additive and only runs while the module is enabled.
 *
 * @package Amazing_Offer\Special_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Module classes use the Amazing_Offer_SO_* prefix. The core autoloader matches
// the Amazing_Offer_ prefix but only probes the root includes/admin/public/
// elementor dirs, so module classes must be required explicitly here.
require_once AMAZING_OFFER_SO_DIR . 'includes/class-amazing-offer-so-schema.php';
require_once AMAZING_OFFER_SO_DIR . 'includes/class-amazing-offer-so-migrator.php';
require_once AMAZING_OFFER_SO_DIR . 'includes/class-amazing-offer-so-cpt.php';
require_once AMAZING_OFFER_SO_DIR . 'includes/class-amazing-offer-so-repository.php';
require_once AMAZING_OFFER_SO_DIR . 'includes/class-amazing-offer-so-render.php';
require_once AMAZING_OFFER_SO_DIR . 'public/class-amazing-offer-so-public.php';

require_once AMAZING_OFFER_SO_DIR . 'includes/class-amazing-offer-so-export.php';

if ( is_admin() ) {
	require_once AMAZING_OFFER_SO_DIR . 'admin/class-amazing-offer-so-admin.php';
}

/**
 * Module controller.
 */
class Amazing_Offer_SO_Module {

	/**
	 * Core settings manager (legacy defaults source).
	 *
	 * @var Amazing_Offer_Settings
	 */
	protected $settings;

	/**
	 * Core products manager (reused for product data).
	 *
	 * @var Amazing_Offer_Products
	 */
	protected $products;

	/**
	 * Per-template repository.
	 *
	 * @var Amazing_Offer_SO_Repository
	 */
	protected $repository;

	/**
	 * Public controller.
	 *
	 * @var Amazing_Offer_SO_Public
	 */
	protected $public;

	/**
	 * Constructor.
	 *
	 * @param Amazing_Offer_Settings $settings Core settings manager.
	 * @param Amazing_Offer_Products $products Core products manager.
	 */
	public function __construct( $settings, $products ) {
		$this->settings   = $settings;
		$this->products   = $products;
		$this->repository = new Amazing_Offer_SO_Repository();
	}

	/**
	 * Register module hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		// Data layer: register the UI-less CPT early on init.
		add_action( 'init', array( 'Amazing_Offer_SO_CPT', 'register' ) );

		// Idempotent option/version seeding (runs from core init, not a
		// module activation hook, to avoid host-plugin activation timing gaps).
		add_action( 'init', array( $this, 'maybe_upgrade' ), 20 );

		// Public assets + front-end output.
		$this->public = new Amazing_Offer_SO_Public( $this->products );
		$this->public->register_hooks();

		add_shortcode( 'special_offer', array( $this, 'render_shortcode' ) );

		// Admin manager + editor.
		if ( is_admin() && class_exists( 'Amazing_Offer_SO_Admin' ) ) {
			$admin = new Amazing_Offer_SO_Admin( $this->settings, $this->products, $this->repository );
			$admin->register_hooks();
		}
	}

	/**
	 * Shortcode handler: [special_offer id="123"] or [special_offer slug="..."].
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'   => 0,
				'slug' => '',
			),
			$atts,
			'special_offer'
		);

		$post_id = absint( $atts['id'] );

		if ( ! $post_id && ! empty( $atts['slug'] ) ) {
			$found = get_page_by_path( sanitize_title( $atts['slug'] ), OBJECT, Amazing_Offer_SO_CPT::POST_TYPE );
			if ( $found ) {
				$post_id = (int) $found->ID;
			}
		}

		if ( ! $post_id ) {
			return '';
		}

		return Amazing_Offer_SO_Render::render( $post_id, $this->settings, $this->products );
	}

	/**
	 * Seed module options and stamp the DB version on first run / upgrade.
	 *
	 * @return void
	 */
	public function maybe_upgrade() {
		// Master switch defaults ON (visible immediately, renders nothing until
		// a template exists).
		if ( false === get_option( 'amazing_offer_so_enabled', false ) ) {
			add_option( 'amazing_offer_so_enabled', true );
		}

		$stored = get_option( 'amazing_offer_so_db_version', '0' );
		if ( version_compare( $stored, AMAZING_OFFER_SO_VERSION, '<' ) ) {
			update_option( 'amazing_offer_so_db_version', AMAZING_OFFER_SO_VERSION );
		}
	}

	/**
	 * Expose the repository (used by later-phase controllers).
	 *
	 * @return Amazing_Offer_SO_Repository
	 */
	public function repository() {
		return $this->repository;
	}
}
