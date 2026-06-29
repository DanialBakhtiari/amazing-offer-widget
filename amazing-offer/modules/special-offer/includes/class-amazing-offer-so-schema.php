<?php
/**
 * Per-template configuration schema, defaults, sanitization, and storage.
 *
 * Each template (طرح) is a single ao_special_offer post; its full config lives
 * in one post meta blob. Defaults layer OVER the core plugin defaults so the
 * module inherits and never drifts from core behavior.
 *
 * @package Amazing_Offer\Special_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Template config schema.
 */
class Amazing_Offer_SO_Schema {

	/**
	 * Post meta key holding the full config blob.
	 *
	 * @var string
	 */
	const META_KEY = '_ao_so_config';

	/**
	 * Current schema version (bump only on structural changes).
	 *
	 * @var int
	 */
	const SCHEMA_VERSION = 1;

	/**
	 * Allowed slider effects.
	 *
	 * @return string[]
	 */
	public static function effects() {
		return array( 'slide', 'fade', 'coverflow', 'cards', 'grid' );
	}

	/**
	 * Allowed product source types.
	 *
	 * @return string[]
	 */
	public static function source_types() {
		return array( 'saved', 'sale', 'category', 'manual' );
	}

	/**
	 * Default config for a template, layered over core plugin defaults.
	 *
	 * @return array
	 */
	public static function get_defaults() {
		$core = Amazing_Offer_Settings::get_defaults();

		$extra = array(
			'_schema'    => self::SCHEMA_VERSION,
			'effect'     => 'slide',
			'style'      => array(
				'radius'  => 12,
				'gap'     => 16,
				'card_bg' => '#ffffff',
				'section_bg' => '',
			),
			'banner'     => array(
				'image'    => '',
				'image_id' => 0,
				'link'     => '',
				'position' => 'hidden', // right | left | top | hidden.
				'alt'      => '',
			),
			'source'     => array(
				'type'        => 'saved', // saved | sale | category | manual.
				'category'    => 0,
				'limit'       => 12,
				'product_ids' => array(),
				'auto_sync'   => false,
			),
			'responsive' => array(
				'mobile' => array(), // sparse overrides.
				'tablet' => array(),
			),
		);

		return array_merge( $core, $extra );
	}

	/**
	 * Sanitize a raw config array against the schema.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	public static function sanitize( array $input ) {
		// Reuse the core field-for-field sanitizer for shared flat keys.
		$core_sanitizer = new Amazing_Offer_Settings();
		$clean          = $core_sanitizer->sanitize( $input );

		// Effect enum.
		$clean['effect'] = ( isset( $input['effect'] ) && in_array( $input['effect'], self::effects(), true ) )
			? $input['effect']
			: 'slide';

		// Style block.
		$style          = ( isset( $input['style'] ) && is_array( $input['style'] ) ) ? $input['style'] : array();
		$clean['style'] = array(
			'radius'     => isset( $style['radius'] ) ? max( 0, min( 60, absint( $style['radius'] ) ) ) : 12,
			'gap'        => isset( $style['gap'] ) ? max( 0, min( 80, absint( $style['gap'] ) ) ) : 16,
			'card_bg'    => isset( $style['card_bg'] ) ? ( sanitize_hex_color( $style['card_bg'] ) ? sanitize_hex_color( $style['card_bg'] ) : '#ffffff' ) : '#ffffff',
			'section_bg' => isset( $style['section_bg'] ) && '' !== $style['section_bg'] ? ( sanitize_hex_color( $style['section_bg'] ) ? sanitize_hex_color( $style['section_bg'] ) : '' ) : '',
		);

		// Banner block.
		$banner          = ( isset( $input['banner'] ) && is_array( $input['banner'] ) ) ? $input['banner'] : array();
		$positions       = array( 'right', 'left', 'top', 'hidden' );
		$clean['banner'] = array(
			'image'    => isset( $banner['image'] ) ? esc_url_raw( $banner['image'] ) : '',
			'image_id' => isset( $banner['image_id'] ) ? absint( $banner['image_id'] ) : 0,
			'link'     => isset( $banner['link'] ) ? esc_url_raw( $banner['link'] ) : '',
			'position' => ( isset( $banner['position'] ) && in_array( $banner['position'], $positions, true ) ) ? $banner['position'] : 'hidden',
			'alt'      => isset( $banner['alt'] ) ? sanitize_text_field( $banner['alt'] ) : '',
		);

		// Source block.
		$src             = ( isset( $input['source'] ) && is_array( $input['source'] ) ) ? $input['source'] : array();
		$clean['source'] = array(
			'type'        => ( isset( $src['type'] ) && in_array( $src['type'], self::source_types(), true ) ) ? $src['type'] : 'saved',
			'category'    => isset( $src['category'] ) ? absint( $src['category'] ) : 0,
			'limit'       => isset( $src['limit'] ) ? max( 1, min( 50, absint( $src['limit'] ) ) ) : 12,
			'product_ids' => array(),
			'auto_sync'   => ! empty( $src['auto_sync'] ) && 'false' !== $src['auto_sync'],
		);
		if ( isset( $src['product_ids'] ) && is_array( $src['product_ids'] ) ) {
			$clean['source']['product_ids'] = array_values( array_unique( array_filter( array_map( 'absint', $src['product_ids'] ) ) ) );
		}

		// Responsive sparse overrides.
		$resp                = ( isset( $input['responsive'] ) && is_array( $input['responsive'] ) ) ? $input['responsive'] : array();
		$clean['responsive'] = array(
			'mobile' => self::sanitize_breakpoint( isset( $resp['mobile'] ) ? $resp['mobile'] : array() ),
			'tablet' => self::sanitize_breakpoint( isset( $resp['tablet'] ) ? $resp['tablet'] : array() ),
		);

		$clean['_schema'] = self::SCHEMA_VERSION;

		return $clean;
	}

	/**
	 * Sanitize a single responsive breakpoint's sparse overrides.
	 *
	 * Only keys explicitly present are kept, so unset keys fall back to the
	 * base config at render time.
	 *
	 * @param mixed $bp Raw breakpoint overrides.
	 * @return array
	 */
	private static function sanitize_breakpoint( $bp ) {
		if ( ! is_array( $bp ) ) {
			return array();
		}
		$out = array();
		if ( isset( $bp['cards'] ) ) {
			$out['cards'] = max( 1, min( 6, absint( $bp['cards'] ) ) );
		}
		if ( isset( $bp['gap'] ) ) {
			$out['gap'] = max( 0, min( 80, absint( $bp['gap'] ) ) );
		}
		foreach ( array( 'hide_timer', 'hide_nav', 'hide_dots', 'hide_banner' ) as $k ) {
			if ( isset( $bp[ $k ] ) ) {
				$out[ $k ] = ! empty( $bp[ $k ] ) && 'false' !== $bp[ $k ];
			}
		}
		return $out;
	}

	/**
	 * Load and normalize a template's config (migrate + merge defaults).
	 *
	 * @param int $post_id Template post id.
	 * @return array
	 */
	public static function load( $post_id ) {
		$raw = get_post_meta( $post_id, self::META_KEY, true );
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}
		$raw = Amazing_Offer_SO_Migrator::migrate( $raw );
		return self::merge_defaults( $raw );
	}

	/**
	 * Sanitize and persist a template's config.
	 *
	 * @param int   $post_id Template post id.
	 * @param array $config  Raw config.
	 * @return bool
	 */
	public static function save( $post_id, array $config ) {
		$clean = self::sanitize( $config );
		return (bool) update_post_meta( $post_id, self::META_KEY, $clean );
	}

	/**
	 * Merge a (migrated) config over defaults.
	 *
	 * wp_parse_args is shallow, so nested blocks are merged explicitly to
	 * backfill any newly-added nested keys on old rows.
	 *
	 * @param array $config Migrated config.
	 * @return array
	 */
	public static function merge_defaults( array $config ) {
		$defaults = self::get_defaults();
		$merged   = wp_parse_args( $config, $defaults );

		foreach ( array( 'style', 'banner', 'source' ) as $block ) {
			$sub             = ( isset( $config[ $block ] ) && is_array( $config[ $block ] ) ) ? $config[ $block ] : array();
			$merged[ $block ] = wp_parse_args( $sub, $defaults[ $block ] );
		}

		$merged['responsive'] = array(
			'mobile' => ( isset( $config['responsive']['mobile'] ) && is_array( $config['responsive']['mobile'] ) ) ? $config['responsive']['mobile'] : array(),
			'tablet' => ( isset( $config['responsive']['tablet'] ) && is_array( $config['responsive']['tablet'] ) ) ? $config['responsive']['tablet'] : array(),
		);

		return $merged;
	}
}
