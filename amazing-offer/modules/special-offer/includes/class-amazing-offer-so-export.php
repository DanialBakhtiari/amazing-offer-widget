<?php
/**
 * Per-template export / import as portable JSON.
 *
 * Only WooCommerce product IDs travel inside the config; nothing else is
 * machine-specific except banner.image_id (which degrades gracefully to the
 * stored image URL on the target site).
 *
 * @package Amazing_Offer\Special_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Export / import.
 */
class Amazing_Offer_SO_Export {

	/**
	 * Envelope format identifier.
	 *
	 * @var string
	 */
	const FORMAT = 'ao_special_offer';

	/**
	 * Build the export envelope for a template.
	 *
	 * @param int $post_id Template id.
	 * @return array
	 */
	public static function export_array( $post_id ) {
		$post   = get_post( $post_id );
		$config = Amazing_Offer_SO_Schema::load( $post_id );

		return array(
			'_format'         => self::FORMAT,
			'_schema'         => Amazing_Offer_SO_Schema::SCHEMA_VERSION,
			'_plugin_version' => defined( 'AMAZING_OFFER_SO_VERSION' ) ? AMAZING_OFFER_SO_VERSION : '',
			'title'           => $post ? $post->post_title : '',
			'config'          => $config,
		);
	}

	/**
	 * Pretty JSON for a template export.
	 *
	 * @param int $post_id Template id.
	 * @return string
	 */
	public static function to_json( $post_id ) {
		return wp_json_encode( self::export_array( $post_id ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	}

	/**
	 * Validate an import envelope.
	 *
	 * @param mixed $data Decoded JSON.
	 * @return bool
	 */
	public static function validate( $data ) {
		return is_array( $data )
			&& isset( $data['_format'] ) && self::FORMAT === $data['_format']
			&& isset( $data['config'] ) && is_array( $data['config'] );
	}

	/**
	 * Import an envelope into a new draft template.
	 *
	 * Runs the schema migrator (so older/newer exports are handled) and the
	 * sanitizer (via the repository) before persisting.
	 *
	 * @param array                       $data       Decoded envelope.
	 * @param Amazing_Offer_SO_Repository $repository Repository.
	 * @return int|WP_Error New template id or error.
	 */
	public static function import( array $data, Amazing_Offer_SO_Repository $repository ) {
		if ( ! self::validate( $data ) ) {
			return new WP_Error( 'ao_so_bad_format', __( 'فایل واردشده معتبر نیست.', 'amazing-offer' ) );
		}

		$config = $data['config'];

		// Stamp the source schema so the migrator can run if needed.
		if ( ! isset( $config['_schema'] ) && isset( $data['_schema'] ) ) {
			$config['_schema'] = (int) $data['_schema'];
		}
		$config = Amazing_Offer_SO_Migrator::migrate( $config );

		$title = isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : __( 'طرح', 'amazing-offer' );
		/* translators: %s: imported template name. */
		$title = sprintf( __( '%s (وارد شده)', 'amazing-offer' ), $title );

		$id = $repository->create( $title, $config );
		if ( is_wp_error( $id ) ) {
			return $id;
		}

		// Imported templates start inactive so they are reviewed before going live.
		$repository->set_status( $id, false );

		return $id;
	}
}
