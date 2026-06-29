<?php
/**
 * UI-less custom post type backing each Special Offer template.
 *
 * Registered with no public surface: no front-end URL, no admin UI, no REST.
 * Templates are managed entirely through the module's own admin screens.
 *
 * @package Amazing_Offer\Special_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Template CPT.
 */
class Amazing_Offer_SO_CPT {

	/**
	 * Post type slug.
	 *
	 * @var string
	 */
	const POST_TYPE = 'ao_special_offer';

	/**
	 * Register the post type.
	 *
	 * @return void
	 */
	public static function register() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'          => __( 'پیشنهادهای ویژه', 'amazing-offer' ),
					'singular_name' => __( 'پیشنهاد ویژه', 'amazing-offer' ),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'show_ui'             => false,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'has_archive'         => false,
				'hierarchical'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'can_export'          => true,
				'delete_with_user'    => false,
				'supports'            => array( 'title', 'page-attributes' ),
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
			)
		);
	}
}
