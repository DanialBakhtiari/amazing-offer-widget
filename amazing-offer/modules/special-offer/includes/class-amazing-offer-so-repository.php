<?php
/**
 * Template repository: create, read, duplicate, delete, reorder, toggle.
 *
 * Maps the "unlimited templates" requirement onto native CPT primitives:
 * menu_order = drag-drop order, post_status publish/draft = active toggle.
 *
 * @package Amazing_Offer\Special_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Template repository.
 */
class Amazing_Offer_SO_Repository {

	/**
	 * Fetch all templates ordered by menu_order.
	 *
	 * @param array $args Extra WP_Query args.
	 * @return WP_Post[]
	 */
	public function all( $args = array() ) {
		$query = new WP_Query(
			wp_parse_args(
				$args,
				array(
					'post_type'        => Amazing_Offer_SO_CPT::POST_TYPE,
					'post_status'      => array( 'publish', 'draft' ),
					'posts_per_page'   => -1,
					'orderby'          => 'menu_order',
					'order'            => 'ASC',
					'no_found_rows'    => true,
					'suppress_filters' => false,
				)
			)
		);
		return $query->posts;
	}

	/**
	 * Get a single template post if it is of the right type.
	 *
	 * @param int $post_id Template id.
	 * @return WP_Post|null
	 */
	public function get( $post_id ) {
		$post = get_post( $post_id );
		if ( $post && Amazing_Offer_SO_CPT::POST_TYPE === $post->post_type ) {
			return $post;
		}
		return null;
	}

	/**
	 * Whether a template is active (published).
	 *
	 * @param int $post_id Template id.
	 * @return bool
	 */
	public function is_active( $post_id ) {
		$post = $this->get( $post_id );
		return $post && 'publish' === $post->post_status;
	}

	/**
	 * Create a new template.
	 *
	 * @param string $title  Internal name.
	 * @param array  $config Optional initial config.
	 * @return int|WP_Error New post id or error.
	 */
	public function create( $title, $config = array() ) {
		$post_id = wp_insert_post(
			array(
				'post_type'   => Amazing_Offer_SO_CPT::POST_TYPE,
				'post_status' => 'publish',
				'post_title'  => sanitize_text_field( $title ),
				'menu_order'  => $this->next_order(),
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		// Start a blank template from the full defaults so toggles (nav/dots/
		// timer) are sensibly ON, not zeroed by presence-based sanitization.
		$config = wp_parse_args( is_array( $config ) ? $config : array(), Amazing_Offer_SO_Schema::get_defaults() );
		Amazing_Offer_SO_Schema::save( $post_id, $config );
		return $post_id;
	}

	/**
	 * Duplicate an existing template as a draft copy.
	 *
	 * @param int $post_id Source template id.
	 * @return int|WP_Error
	 */
	public function duplicate( $post_id ) {
		$source = $this->get( $post_id );
		if ( ! $source ) {
			return new WP_Error( 'ao_so_not_found', __( 'طرح یافت نشد.', 'amazing-offer' ) );
		}

		$config = Amazing_Offer_SO_Schema::load( $post_id );

		$new_id = wp_insert_post(
			array(
				'post_type'   => Amazing_Offer_SO_CPT::POST_TYPE,
				'post_status' => 'draft',
				/* translators: %s: original template name. */
				'post_title'  => sprintf( __( '%s (کپی)', 'amazing-offer' ), $source->post_title ),
				'menu_order'  => $this->next_order(),
			),
			true
		);

		if ( is_wp_error( $new_id ) ) {
			return $new_id;
		}

		Amazing_Offer_SO_Schema::save( $new_id, $config );
		return $new_id;
	}

	/**
	 * Permanently delete a template (and its meta).
	 *
	 * @param int $post_id Template id.
	 * @return bool
	 */
	public function delete( $post_id ) {
		if ( ! $this->get( $post_id ) ) {
			return false;
		}
		return (bool) wp_delete_post( $post_id, true );
	}

	/**
	 * Toggle a template's active state.
	 *
	 * @param int  $post_id Template id.
	 * @param bool $active  Desired state.
	 * @return bool
	 */
	public function set_status( $post_id, $active ) {
		if ( ! $this->get( $post_id ) ) {
			return false;
		}
		$result = wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => $active ? 'publish' : 'draft',
			),
			true
		);
		return ! is_wp_error( $result );
	}

	/**
	 * Persist a new template order.
	 *
	 * @param int[] $ordered_ids Template ids in display order.
	 * @return void
	 */
	public function reorder( array $ordered_ids ) {
		$order = 0;
		foreach ( $ordered_ids as $id ) {
			$id = absint( $id );
			if ( $this->get( $id ) ) {
				wp_update_post(
					array(
						'ID'         => $id,
						'menu_order' => $order,
					)
				);
				++$order;
			}
		}
	}

	/**
	 * Next menu_order value (max + 1).
	 *
	 * @return int
	 */
	private function next_order() {
		$posts = $this->all( array( 'orderby' => 'menu_order', 'order' => 'DESC', 'posts_per_page' => 1 ) );
		if ( empty( $posts ) ) {
			return 0;
		}
		return (int) $posts[0]->menu_order + 1;
	}
}
