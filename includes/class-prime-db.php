<?php
/**
 * Database class — table creation, schema versioning, table name helper.
 *
 * @package prime-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database class — table creation, schema versioning, table name helper.
 *
 * @package prime-core
 */
class Prime_DB {

	/**
	 * Singleton instance.
	 *
	 * @var Prime_DB|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Prime_DB
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor.
	 */
	private function __construct() {}

	/**
	 * Get a table name with the prime_ prefix.
	 *
	 * @param string $table Short name without prefix (e.g. 'client_tasks').
	 * @return string      Full table name (e.g. 'wp_prime_client_tasks').
	 */
	public function table( $table ) {
		global $wpdb;
		return $wpdb->prefix . 'prime_' . $table;
	}

	/**
	 * Create all custom tables on plugin activation.
	 * Uses dbDelta() for safe, idempotent table creation/modification.
	 *
	 * Tables are registered via the `prime_db_tables` filter
	 * by subsequent slices (1-2, 1-3, 1-4).
	 */
	public function create_tables() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$tables = array();
		$tables = apply_filters( 'prime_db_tables', $tables, $charset_collate );

		foreach ( $tables as $sql ) {
			dbDelta( $sql );
		}

		update_option( 'prime_db_version', PRIME_CORE_VERSION );
	}

	/**
	 * Drop all custom tables on uninstall.
	 *
	 * Table names are registered via the `prime_db_table_names` filter.
	 */
	public function drop_tables() {
		global $wpdb;

		$table_names = array();
		$table_names = apply_filters( 'prime_db_table_names', $table_names );

		foreach ( $table_names as $table ) {
			$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %s', $table ) );
		}

		delete_option( 'prime_db_version' );
	}
}
