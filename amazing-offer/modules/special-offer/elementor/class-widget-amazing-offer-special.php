<?php
/**
 * Elementor widget for a Special Offer template.
 *
 * A NEW widget independent from the legacy Widget_Amazing_Offer. Renders a
 * chosen template by id through the shared SO renderer; config comes from the
 * template (NOT legacy global settings).
 *
 * @package Amazing_Offer\Special_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

/**
 * Special Offer Elementor widget.
 */
class Widget_Amazing_Offer_Special extends Widget_Base {

	/**
	 * Settings manager (optional injection).
	 *
	 * @var Amazing_Offer_Settings|null
	 */
	protected $so_settings = null;

	/**
	 * Products manager (optional injection).
	 *
	 * @var Amazing_Offer_Products|null
	 */
	protected $so_products = null;

	/**
	 * Repository (optional injection).
	 *
	 * @var Amazing_Offer_SO_Repository|null
	 */
	protected $so_repository = null;

	/**
	 * Inject dependencies.
	 *
	 * @param Amazing_Offer_Settings      $settings   Settings.
	 * @param Amazing_Offer_Products      $products   Products.
	 * @param Amazing_Offer_SO_Repository $repository Repository.
	 * @return void
	 */
	public function set_dependencies( $settings, $products, $repository ) {
		$this->so_settings   = $settings;
		$this->so_products   = $products;
		$this->so_repository = $repository;
	}

	/**
	 * Resolve settings lazily.
	 *
	 * @return Amazing_Offer_Settings
	 */
	protected function settings() {
		if ( null === $this->so_settings ) {
			$this->so_settings = new Amazing_Offer_Settings();
		}
		return $this->so_settings;
	}

	/**
	 * Resolve products lazily.
	 *
	 * @return Amazing_Offer_Products
	 */
	protected function products() {
		if ( null === $this->so_products ) {
			$this->so_products = new Amazing_Offer_Products();
		}
		return $this->so_products;
	}

	/**
	 * Resolve repository lazily.
	 *
	 * @return Amazing_Offer_SO_Repository
	 */
	protected function repository() {
		if ( null === $this->so_repository ) {
			$this->so_repository = new Amazing_Offer_SO_Repository();
		}
		return $this->so_repository;
	}

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'amazing-offer-special';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'پیشنهاد ویژه', 'amazing-offer' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-price-list';
	}

	/**
	 * Widget categories.
	 *
	 * @return string[]
	 */
	public function get_categories() {
		return array( 'amazing-offer', 'woocommerce-elements' );
	}

	/**
	 * Search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array( 'offer', 'special', 'sale', 'slider', 'woocommerce', 'پیشنهاد', 'تخفیف' );
	}

	/**
	 * Build template select options.
	 *
	 * @return array
	 */
	protected function template_options() {
		$options = array( 0 => __( '— انتخاب طرح —', 'amazing-offer' ) );
		foreach ( $this->repository()->all() as $tpl ) {
			$label = $tpl->post_title;
			if ( 'publish' !== $tpl->post_status ) {
				$label .= ' ' . __( '(غیرفعال)', 'amazing-offer' );
			}
			$options[ $tpl->ID ] = $label . ' (#' . $tpl->ID . ')';
		}
		return $options;
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_so',
			array(
				'label' => __( 'پیشنهاد ویژه', 'amazing-offer' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'template_id',
			array(
				'label'   => __( 'انتخاب طرح', 'amazing-offer' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 0,
				'options' => $this->template_options(),
			)
		);

		$this->add_control(
			'so_hint',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'طرح‌ها را از منوی «Amazing Offer ← پیشنهاد ویژه» بسازید و مدیریت کنید.', 'amazing-offer' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$id       = isset( $settings['template_id'] ) ? absint( $settings['template_id'] ) : 0;

		if ( ! $id ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="ao-so-empty">' . esc_html__( 'یک طرح انتخاب کنید.', 'amazing-offer' ) . '</div>';
			}
			return;
		}

		echo Amazing_Offer_SO_Render::render( $id, $this->settings(), $this->products() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped within renderer.
	}
}
