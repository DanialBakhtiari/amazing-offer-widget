<?php
/**
 * Lazy per-row schema migration.
 *
 * Runs on every config read. Additive top-level/nested keys need NO migration
 * (defaults + merge backfill them); only structural changes add a numbered
 * migrate step. Unknown higher versions are left untouched (forward-compat).
 *
 * @package Amazing_Offer\Special_Offer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Config migrator.
 */
class Amazing_Offer_SO_Migrator {

	/**
	 * Migrate a raw config blob up to the current schema version.
	 *
	 * @param array $config Raw stored config.
	 * @return array
	 */
	public static function migrate( array $config ) {
		if ( empty( $config ) ) {
			return $config;
		}

		$from = isset( $config['_schema'] ) ? (int) $config['_schema'] : 1;
		$to   = Amazing_Offer_SO_Schema::SCHEMA_VERSION;

		// Already current, or a newer-than-code row (forward-compat): leave as-is.
		if ( $from >= $to ) {
			return $config;
		}

		// Apply ordered steps. Each migrate_N_to_M mutates $config in place.
		while ( $from < $to ) {
			$method = 'migrate_' . $from . '_to_' . ( $from + 1 );
			if ( method_exists( __CLASS__, $method ) ) {
				$config = self::$method( $config );
			}
			++$from;
		}

		$config['_schema'] = $to;
		return $config;
	}

	// Future structural steps go here, e.g.:
	// private static function migrate_1_to_2( array $config ) { ...; return $config; }
}
