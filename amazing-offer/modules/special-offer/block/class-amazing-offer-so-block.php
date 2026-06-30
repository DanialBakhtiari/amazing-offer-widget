<?php
/**
 * Server-rendered Gutenberg block for a Special Offer template.
 *
 * No build step: the editor script is plain JS using wp.element.createElement
 * and ServerSideRender. Registered via PHP so render_callback delegates to the
 * shared renderer (SSR/SEO intact). Guarded on a WP version that supports
 * block.json-less dynamic blocks.
 *
 * @package Amazing_Offer\Special_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block controller.
 */
class Amazing_Offer_SO_Block {

	const BLOCK_NAME = 'amazing-offer/special-offer';

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
	 * Repository.
	 *
	 * @var Amazing_Offer_SO_Repository
	 */
	protected $repository;

	/**
	 * Constructor.
	 *
	 * @param Amazing_Offer_Settings      $settings   Settings.
	 * @param Amazing_Offer_Products      $products   Products.
	 * @param Amazing_Offer_SO_Repository $repository Repository.
	 */
	public function __construct( $settings, $products, $repository ) {
		$this->settings   = $settings;
		$this->products   = $products;
		$this->repository = $repository;
	}

	/**
	 * Whether the current WP supports the block API we use.
	 *
	 * @return bool
	 */
	public static function supported() {
		return function_exists( 'register_block_type' ) && version_compare( get_bloginfo( 'version' ), '5.8', '>=' );
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		if ( ! self::supported() ) {
			return;
		}
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest' ) );
	}

	/**
	 * Register the block + its editor script.
	 *
	 * @return void
	 */
	public function register_block() {
		wp_register_script(
			'ao-so-block',
			AMAZING_OFFER_SO_URL . 'block/editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-api-fetch', 'wp-i18n' ),
			AMAZING_OFFER_SO_VERSION,
			true
		);

		register_block_type(
			self::BLOCK_NAME,
			array(
				'api_version'     => 2,
				'editor_script'   => 'ao-so-block',
				'attributes'      => array(
					'templateId' => array(
						'type'    => 'number',
						'default' => 0,
					),
				),
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Render callback (front-end + editor SSR).
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render_block( $attributes ) {
		$id = isset( $attributes['templateId'] ) ? absint( $attributes['templateId'] ) : 0;
		if ( ! $id ) {
			return '';
		}
		return Amazing_Offer_SO_Render::render( $id, $this->settings, $this->products );
	}

	/**
	 * Register the read-only REST route used by the editor selector.
	 *
	 * @return void
	 */
	public function register_rest() {
		register_rest_route(
			'amazing-offer/v1',
			'/special-offers',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'rest_list' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	/**
	 * REST: list templates for the selector.
	 *
	 * @return array
	 */
	public function rest_list() {
		$out = array();
		foreach ( $this->repository->all() as $tpl ) {
			$out[] = array(
				'id'     => (int) $tpl->ID,
				'title'  => $tpl->post_title,
				'active' => ( 'publish' === $tpl->post_status ),
			);
		}
		return $out;
	}
}
