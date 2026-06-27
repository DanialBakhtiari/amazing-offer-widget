<?php
/**
 * Plugin settings handling via the WordPress Options API.
 *
 * @package Amazing_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings manager.
 */
class Amazing_Offer_Settings {

	/**
	 * Option key used to store all settings.
	 *
	 * @var string
	 */
	const OPTION_KEY = 'amazing_offer_settings';

	/**
	 * Default values for every setting.
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return array(
			// Title.
			'title'                => 'پیشنهاد شگفت‌انگیز',
			'show_icon'            => true,
			'subtitle'             => 'فقط تا پایان امروز!',

			// Timer.
			'show_timer'           => true,
			'timer_type'           => 'midnight', // midnight | duration | fixed_date.
			'timer_duration'       => 24, // hours.
			'timer_end_date'       => '',

			// Slider.
			'slider_mode'          => 'auto', // auto | manual | grid.
			'slider_speed'         => 3000,
			'slider_cards_mobile'  => 1,
			'slider_cards_tablet'  => 2,
			'slider_cards_desktop' => 3,
			'slider_loop'          => true,
			'show_nav'             => true,
			'show_dots'            => true,
			'pause_on_hover'       => true,

			// Card.
			'show_original_price'  => true,
			'show_discount_badge'  => true,
			'badge_color'          => '#e04a1f',
			'show_add_to_cart'     => true,
			'cart_button_text'     => 'افزودن به سبد',
			'button_color'         => '#1a1a2e',
			'button_hover_color'   => '#e04a1f',

			// Extra modules.
			'show_countdown_days'  => false,
			'show_stock'           => false,
			'show_buyers_count'    => false,
			'buyers_count_base'    => 50,
		);
	}

	/**
	 * Retrieve a single setting value.
	 *
	 * @param string $key Setting key.
	 * @return mixed Setting value or null when undefined.
	 */
	public function get( $key ) {
		$all = $this->get_all();
		return isset( $all[ $key ] ) ? $all[ $key ] : null;
	}

	/**
	 * Retrieve all settings merged over defaults.
	 *
	 * @return array
	 */
	public function get_all() {
		$saved = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		return wp_parse_args( $saved, self::get_defaults() );
	}

	/**
	 * Update settings after sanitizing each field.
	 *
	 * @param array $settings Raw settings input.
	 * @return bool
	 */
	public function update( array $settings ) {
		$clean = $this->sanitize( $settings );
		$merged = wp_parse_args( $clean, $this->get_all() );
		return update_option( self::OPTION_KEY, $merged );
	}

	/**
	 * Reset all settings to defaults.
	 *
	 * @return bool
	 */
	public function reset() {
		return update_option( self::OPTION_KEY, self::get_defaults() );
	}

	/**
	 * Sanitize a raw settings array against the known schema.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	public function sanitize( array $input ) {
		$defaults = self::get_defaults();
		$clean    = array();

		$text_keys = array( 'title', 'subtitle', 'cart_button_text' );
		foreach ( $text_keys as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$clean[ $key ] = sanitize_text_field( $input[ $key ] );
			}
		}

		$bool_keys = array(
			'show_icon',
			'show_timer',
			'slider_loop',
			'show_nav',
			'show_dots',
			'pause_on_hover',
			'show_original_price',
			'show_discount_badge',
			'show_add_to_cart',
			'show_countdown_days',
			'show_stock',
			'show_buyers_count',
		);
		foreach ( $bool_keys as $key ) {
			$clean[ $key ] = ! empty( $input[ $key ] ) && 'false' !== $input[ $key ];
		}

		$color_keys = array( 'badge_color', 'button_color', 'button_hover_color' );
		foreach ( $color_keys as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$color         = sanitize_hex_color( $input[ $key ] );
				$clean[ $key ] = $color ? $color : $defaults[ $key ];
			}
		}

		$int_keys = array(
			'timer_duration',
			'slider_speed',
			'slider_cards_mobile',
			'slider_cards_tablet',
			'slider_cards_desktop',
			'buyers_count_base',
		);
		foreach ( $int_keys as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$clean[ $key ] = absint( $input[ $key ] );
			}
		}

		// Constrained enums.
		if ( isset( $input['timer_type'] ) ) {
			$allowed             = array( 'midnight', 'duration', 'fixed_date' );
			$clean['timer_type'] = in_array( $input['timer_type'], $allowed, true ) ? $input['timer_type'] : 'midnight';
		}
		if ( isset( $input['slider_mode'] ) ) {
			$allowed              = array( 'auto', 'manual', 'grid' );
			$clean['slider_mode'] = in_array( $input['slider_mode'], $allowed, true ) ? $input['slider_mode'] : 'auto';
		}
		if ( isset( $input['timer_end_date'] ) ) {
			$clean['timer_end_date'] = sanitize_text_field( $input['timer_end_date'] );
		}

		// Clamp card counts to sane ranges.
		if ( isset( $clean['slider_cards_mobile'] ) ) {
			$clean['slider_cards_mobile'] = max( 1, min( 2, $clean['slider_cards_mobile'] ) );
		}
		if ( isset( $clean['slider_cards_tablet'] ) ) {
			$clean['slider_cards_tablet'] = max( 2, min( 3, $clean['slider_cards_tablet'] ) );
		}
		if ( isset( $clean['slider_cards_desktop'] ) ) {
			$clean['slider_cards_desktop'] = max( 3, min( 6, $clean['slider_cards_desktop'] ) );
		}
		if ( isset( $clean['slider_speed'] ) ) {
			$clean['slider_speed'] = max( 500, min( 5000, $clean['slider_speed'] ) );
		}

		return $clean;
	}
}
