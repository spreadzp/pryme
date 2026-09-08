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
	private function __construct() {
		add_filter( 'prime_db_tables', array( $this, 'add_core_tables' ), 10, 2 );
		add_filter( 'prime_db_table_names', array( $this, 'add_core_table_names' ) );
	}

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

	/**
	 * Add core client tables to the dbDelta queue.
	 *
	 * @param array  $tables  SQL strings from previous filters.
	 * @param string $charset Charset collate string.
	 * @return array
	 */
	public function add_core_tables( $tables, $charset ) {
		return array_merge( $tables, $this->core_tables( $charset ) );
	}

	/**
	 * Add core client table names to the drop queue.
	 *
	 * @param array $names Table names from previous filters.
	 * @return array
	 */
	public function add_core_table_names( $names ) {
		global $wpdb;
		$prefix = $wpdb->prefix . 'prime_';

		return array_merge(
			$names,
			array(
				$prefix . 'client_tasks',
				$prefix . 'client_files',
				$prefix . 'client_folders',
				$prefix . 'client_access_refs',
				$prefix . 'client_modules',
				$prefix . 'client_team',
			)
		);
	}

	/**
	 * Returns SQL for 6 core client tables.
	 *
	 * @param string $charset Charset collate string.
	 * @return array Array of CREATE TABLE SQL strings.
	 */
	public function core_tables( $charset ) {
		global $wpdb;
		$prefix = $wpdb->prefix . 'prime_';

		return array(
			// 1. Client tasks.
			"CREATE TABLE {$prefix}client_tasks (
				id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				client_id       BIGINT(20) UNSIGNED NOT NULL,
				project_id      BIGINT(20) UNSIGNED DEFAULT NULL,
				title           VARCHAR(255) NOT NULL,
				description     TEXT,
				status          VARCHAR(20) NOT NULL DEFAULT 'todo',
				priority        VARCHAR(10) NOT NULL DEFAULT 'medium',
				assigned_to     BIGINT(20) UNSIGNED DEFAULT NULL,
				created_by      BIGINT(20) UNSIGNED NOT NULL,
				position        INT(11) NOT NULL DEFAULT 0,
				due_date        DATE DEFAULT NULL,
				completed_at    DATETIME DEFAULT NULL,
				created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				deleted_at      DATETIME DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY idx_client_status (client_id, status),
				KEY idx_client_assigned (client_id, assigned_to, status),
				KEY idx_client_due (client_id, due_date),
				KEY idx_project (project_id, status)
			) ENGINE=InnoDB {$charset};",

			// 2. Client files.
			"CREATE TABLE {$prefix}client_files (
				id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				client_id       BIGINT(20) UNSIGNED NOT NULL,
				folder_id       BIGINT(20) UNSIGNED DEFAULT NULL,
				filename        VARCHAR(255) NOT NULL,
				filepath        VARCHAR(500) NOT NULL,
				filetype        VARCHAR(50) NOT NULL,
				filesize        BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
				uploaded_by     BIGINT(20) UNSIGNED NOT NULL,
				task_id         BIGINT(20) UNSIGNED DEFAULT NULL,
				description     TEXT,
				created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				deleted_at      DATETIME DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY idx_client_folder (client_id, folder_id),
				KEY idx_client_task (client_id, task_id),
				KEY idx_uploaded_by (uploaded_by)
			) ENGINE=InnoDB {$charset};",

			// 3. Client folders.
			"CREATE TABLE {$prefix}client_folders (
				id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				client_id       BIGINT(20) UNSIGNED NOT NULL,
				parent_id       BIGINT(20) UNSIGNED DEFAULT NULL,
				name            VARCHAR(255) NOT NULL,
				created_by      BIGINT(20) UNSIGNED NOT NULL,
				created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				deleted_at      DATETIME DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY idx_client_parent (client_id, parent_id)
			) ENGINE=InnoDB {$charset};",

			// 4. Client access refs.
			"CREATE TABLE {$prefix}client_access_refs (
				id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				client_id       BIGINT(20) UNSIGNED NOT NULL,
				type            VARCHAR(20) NOT NULL,
				label           VARCHAR(255) NOT NULL,
				external_ref    VARCHAR(255) NOT NULL,
				masked_value    VARCHAR(50) NOT NULL,
				url             VARCHAR(500) DEFAULT NULL,
				notes           TEXT,
				created_by      BIGINT(20) UNSIGNED NOT NULL,
				created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				deleted_at      DATETIME DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY idx_client_type (client_id, type)
			) ENGINE=InnoDB {$charset};",

			// 5. Client modules.
			"CREATE TABLE {$prefix}client_modules (
				id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				client_id       BIGINT(20) UNSIGNED NOT NULL,
				module_key      VARCHAR(50) NOT NULL,
				is_enabled      TINYINT(1) NOT NULL DEFAULT 0,
				settings        LONGTEXT,
				enabled_by      BIGINT(20) UNSIGNED NOT NULL,
				enabled_at      DATETIME DEFAULT NULL,
				disabled_at     DATETIME DEFAULT NULL,
				created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				UNIQUE KEY uniq_client_module (client_id, module_key)
			) ENGINE=InnoDB {$charset};",

			// 6. Client team.
			"CREATE TABLE {$prefix}client_team (
				id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				client_id       BIGINT(20) UNSIGNED NOT NULL,
				user_id         BIGINT(20) UNSIGNED NOT NULL,
				role            VARCHAR(20) NOT NULL DEFAULT 'member',
				permissions     LONGTEXT,
				added_by        BIGINT(20) UNSIGNED NOT NULL,
				created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				deleted_at      DATETIME DEFAULT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY uniq_client_user (client_id, user_id),
				KEY idx_user (user_id)
			) ENGINE=InnoDB {$charset};",
		);
	}
}
