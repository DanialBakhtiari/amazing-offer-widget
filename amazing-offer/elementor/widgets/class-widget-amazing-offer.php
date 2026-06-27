<?php
/**
 * Elementor widget: Amazing Offer slider.
 *
 * @package Amazing_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

/**
 * Amazing Offer Elementor widget.
 */
class Widget_Amazing_Offer extends Widget_Base {

	/**
	 * Settings manager (optional injection).
	 *
	 * @var Amazing_Offer_Settings|null
	 */
	protected $settings = null;

	/**
	 * Products manager (optional injection).
	 *
	 * @var Amazing_Offer_Products|null
	 */
	protected $products = null;

	/**
	 * Inject dependencies.
	 *
	 * @param Amazing_Offer_Settings $settings Settings.
	 * @param Amazing_Offer_Products $products Products.
	 * @return void
	 */
	public function set_dependencies( $settings, $products ) {
		$this->settings = $settings;
		$this->products = $products;
	}

	/**
	 * Lazily resolve the settings manager.
	 *
	 * @return Amazing_Offer_Settings
	 */
	protected function settings() {
		if ( null === $this->settings ) {
			$this->settings = new Amazing_Offer_Settings();
		}
		return $this->settings;
	}

	/**
	 * Lazily resolve the products manager.
	 *
	 * @return Amazing_Offer_Products
	 */
	protected function products() {
		if ( null === $this->products ) {
			$this->products = new Amazing_Offer_Products();
		}
		return $this->products;
	}

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'amazing-offer-widget';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'پیشنهاد شگفت‌انگیز', 'amazing-offer' );
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
		return array( 'offer', 'sale', 'woocommerce', 'slider', 'discount', 'تخفیف', 'پیشنهاد' );
	}

	/**
	 * Register widget controls.
	 *
	 * @return void
	 */
	protected function register_controls() {

		/* ---------- Content: source ---------- */
		$this->start_controls_section(
			'section_content',
			array(
				'label' => __( 'محتوا', 'amazing-offer' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'source',
			array(
				'label'   => __( 'منبع محصولات', 'amazing-offer' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'saved',
				'options' => array(
					'saved'    => __( 'از داشبورد', 'amazing-offer' ),
					'sale'     => __( 'همه محصولات تخفیف‌دار', 'amazing-offer' ),
					'category' => __( 'دسته خاص', 'amazing-offer' ),
				),
			)
		);

		$this->add_control(
			'category',
			array(
				'label'     => __( 'دسته محصول', 'amazing-offer' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => $this->get_category_options(),
				'condition' => array( 'source' => 'category' ),
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'   => __( 'تعداد محصولات', 'amazing-offer' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 20,
				'default' => 8,
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'عنوان ویجت', 'amazing-offer' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'خالی = استفاده از تنظیمات داشبورد', 'amazing-offer' ),
			)
		);

		$this->add_control(
			'show_timer',
			array(
				'label'        => __( 'نمایش تایمر', 'amazing-offer' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'بله', 'amazing-offer' ),
				'label_off'    => __( 'خیر', 'amazing-offer' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'timer_duration',
			array(
				'label'     => __( 'مدت تایمر (ساعت)', 'amazing-offer' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 720,
				'default'   => 24,
				'condition' => array( 'show_timer' => 'yes' ),
			)
		);

		$this->end_controls_section();

		/* ---------- Slider ---------- */
		$this->start_controls_section(
			'section_slider',
			array(
				'label' => __( 'اسلایدر', 'amazing-offer' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'slider_mode',
			array(
				'label'   => __( 'حالت نمایش', 'amazing-offer' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => array(
					'auto'   => __( 'اسلایدر خودکار', 'amazing-offer' ),
					'manual' => __( 'اسلایدر دستی', 'amazing-offer' ),
					'grid'   => __( 'گرید ثابت', 'amazing-offer' ),
				),
			)
		);

		$this->add_control(
			'slider_speed',
			array(
				'label'     => __( 'سرعت (میلی‌ثانیه)', 'amazing-offer' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 500, 'max' => 5000, 'step' => 100 ) ),
				'default'   => array( 'size' => 3000 ),
				'condition' => array( 'slider_mode' => 'auto' ),
			)
		);

		$this->add_responsive_control(
			'cards_per_view',
			array(
				'label'           => __( 'تعداد کارت در نمایش', 'amazing-offer' ),
				'type'            => Controls_Manager::NUMBER,
				'min'             => 1,
				'max'             => 6,
				'default'         => 3,
				'tablet_default'  => 2,
				'mobile_default'  => 1,
			)
		);

		$this->add_control(
			'slider_loop',
			array(
				'label'        => __( 'لوپ', 'amazing-offer' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_nav',
			array(
				'label'        => __( 'دکمه‌های ناوبری', 'amazing-offer' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_dots',
			array(
				'label'        => __( 'نقاط', 'amazing-offer' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();

		/* ---------- Style: card ---------- */
		$this->start_controls_section(
			'section_style_card',
			array(
				'label' => __( 'استایل کارت', 'amazing-offer' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'card_bg',
			array(
				'label'     => __( 'رنگ پس‌زمینه کارت', 'amazing-offer' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .amazing-offer-card' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'card_border_color',
			array(
				'label'     => __( 'رنگ حاشیه', 'amazing-offer' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .amazing-offer-card' => 'border-color: {{VALUE}}; border-style: solid;' ),
			)
		);

		$this->add_control(
			'card_radius',
			array(
				'label'      => __( 'گردی گوشه', 'amazing-offer' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'selectors'  => array( '{{WRAPPER}} .amazing-offer-card' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => __( 'فاصله داخلی', 'amazing-offer' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .amazing-offer-card-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		/* ---------- Style: price ---------- */
		$this->start_controls_section(
			'section_style_price',
			array(
				'label' => __( 'استایل قیمت', 'amazing-offer' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'sale_price_color',
			array(
				'label'     => __( 'رنگ قیمت تخفیفی', 'amazing-offer' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .amazing-offer-sale-price' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'regular_price_color',
			array(
				'label'     => __( 'رنگ قیمت اصلی', 'amazing-offer' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .amazing-offer-regular-price' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'price_typography',
				'selector' => '{{WRAPPER}} .amazing-offer-sale-price',
			)
		);

		$this->end_controls_section();

		/* ---------- Style: button ---------- */
		$this->start_controls_section(
			'section_style_button',
			array(
				'label' => __( 'استایل دکمه', 'amazing-offer' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'button_color',
			array(
				'label'     => __( 'رنگ دکمه', 'amazing-offer' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .amazing-offer-add-to-cart' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'button_hover_color',
			array(
				'label'     => __( 'رنگ هاور دکمه', 'amazing-offer' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .amazing-offer-add-to-cart:hover' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'button_radius',
			array(
				'label'     => __( 'گردی گوشه دکمه', 'amazing-offer' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'selectors' => array( '{{WRAPPER}} .amazing-offer-add-to-cart' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'button_padding',
			array(
				'label'      => __( 'فاصله داخلی دکمه', 'amazing-offer' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .amazing-offer-add-to-cart' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Build category options for the control.
	 *
	 * @return array
	 */
	protected function get_category_options() {
		$options = array( '' => __( '— انتخاب دسته —', 'amazing-offer' ) );

		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return $options;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'number'     => 100,
			)
		);

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->term_id ] = $term->name;
			}
		}

		return $options;
	}

	/**
	 * Render widget output.
	 *
	 * @return void
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		$args = array(
			'source'       => isset( $s['source'] ) ? $s['source'] : 'saved',
			'limit'        => isset( $s['limit'] ) ? absint( $s['limit'] ) : 8,
			'category'     => isset( $s['category'] ) ? absint( $s['category'] ) : 0,
			'slider_mode'  => isset( $s['slider_mode'] ) ? $s['slider_mode'] : 'auto',
			'show_timer'   => ( isset( $s['show_timer'] ) && 'yes' === $s['show_timer'] ),
			'slider_loop'  => ( isset( $s['slider_loop'] ) && 'yes' === $s['slider_loop'] ),
			'show_nav'     => ( isset( $s['show_nav'] ) && 'yes' === $s['show_nav'] ),
			'show_dots'    => ( isset( $s['show_dots'] ) && 'yes' === $s['show_dots'] ),
		);

		if ( ! empty( $s['title'] ) ) {
			$args['title'] = $s['title'];
		}
		if ( ! empty( $s['slider_speed']['size'] ) ) {
			$args['slider_speed'] = absint( $s['slider_speed']['size'] );
		}
		if ( ! empty( $s['timer_duration'] ) ) {
			$args['timer_duration'] = absint( $s['timer_duration'] );
			$args['timer_type']     = 'duration';
		}
		if ( ! empty( $s['cards_per_view'] ) ) {
			$args['slider_cards_desktop'] = absint( $s['cards_per_view'] );
		}
		if ( ! empty( $s['cards_per_view_tablet'] ) ) {
			$args['slider_cards_tablet'] = absint( $s['cards_per_view_tablet'] );
		}
		if ( ! empty( $s['cards_per_view_mobile'] ) ) {
			$args['slider_cards_mobile'] = absint( $s['cards_per_view_mobile'] );
		}

		echo Amazing_Offer_Render::render( $args, $this->settings(), $this->products() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped within renderer.
	}
}
