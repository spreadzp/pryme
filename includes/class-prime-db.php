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
		add_filter( 'prime_db_tables', array( $this, 'add_task_tables' ), 11, 2 );
		add_filter( 'prime_db_tables', array( $this, 'add_comm_tables' ), 12, 2 );
		add_filter( 'prime_db_table_names', array( $this, 'add_core_table_names' ) );
		add_filter( 'prime_db_table_names', array( $this, 'add_task_table_names' ) );
		add_filter( 'prime_db_table_names', array( $this, 'add_comm_table_names' ) );
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

	/**
	 * Add task/Kanban tables to the dbDelta queue.
	 *
	 * @param array  $tables  SQL strings from previous filters.
	 * @param string $charset Charset collate string.
	 * @return array
	 */
	public function add_task_tables( $tables, $charset ) {
		return array_merge( $tables, $this->task_tables( $charset ) );
	}

	/**
	 * Add task/Kanban table names to the drop queue.
	 *
	 * @param array $names Table names from previous filters.
	 * @return array
	 */
	public function add_task_table_names( $names ) {
		global $wpdb;
		$prefix = $wpdb->prefix . 'prime_';

		return array_merge(
			$names,
			array(
				$prefix . 'task_labels',
				$prefix . 'task_label_assignees',
				$prefix . 'task_subtasks',
				$prefix . 'task_comments',
				$prefix . 'task_attachments',
				$prefix . 'kanban_columns',
				$prefix . 'task_activity',
			)
		);
	}

	/**
	 * Returns SQL for 7 task/Kanban tables.
	 *
	 * @param string $charset Charset collate string.
	 * @return array Array of CREATE TABLE SQL strings.
	 */
	public function task_tables( $charset ) {
		global $wpdb;
		$prefix = $wpdb->prefix . 'prime_';

		return array(
			// 7. Task labels.
			"CREATE TABLE {$prefix}task_labels (
				id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				client_id       BIGINT(20) UNSIGNED NOT NULL,
				title           VARCHAR(100) NOT NULL,
				color           VARCHAR(20) NOT NULL DEFAULT '#6c757d',
				created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY idx_client (client_id)
			) ENGINE=InnoDB {$charset};",

			// 8. Task label assignees (many-to-many).
			"CREATE TABLE {$prefix}task_label_assignees (
				id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				task_id         BIGINT(20) UNSIGNED NOT NULL,
				label_id        BIGINT(20) UNSIGNED NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY uniq_task_label (task_id, label_id),
				KEY idx_label (label_id)
			) ENGINE=InnoDB {$charset};",

			// 9. Task subtasks.
			"CREATE TABLE {$prefix}task_subtasks (
				id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				task_id         BIGINT(20) UNSIGNED NOT NULL,
				title           VARCHAR(255) NOT NULL,
				is_completed    TINYINT(1) NOT NULL DEFAULT 0,
				position        INT(11) NOT NULL DEFAULT 0,
				assigned_to     BIGINT(20) UNSIGNED DEFAULT NULL,
				created_by      BIGINT(20) UNSIGNED NOT NULL,
				created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY idx_task_position (task_id, position),
				KEY idx_task_completed (task_id, is_completed)
			) ENGINE=InnoDB {$charset};",

			// 10. Task comments.
			"CREATE TABLE {$prefix}task_comments (
				id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				task_id         BIGINT(20) UNSIGNED NOT NULL,
				user_id         BIGINT(20) UNSIGNED NOT NULL,
				comment         TEXT NOT NULL,
				mentions        LONGTEXT,
				created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				deleted_at      DATETIME DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY idx_task_date (task_id, created_at),
				KEY idx_user (user_id)
			) ENGINE=InnoDB {$charset};",

			// 11. Task attachments.
			"CREATE TABLE {$prefix}task_attachments (
				id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				task_id         BIGINT(20) UNSIGNED NOT NULL,
				file_id         BIGINT(20) UNSIGNED DEFAULT NULL,
				external_url    VARCHAR(500) DEFAULT NULL,
				title           VARCHAR(255) DEFAULT NULL,
				attached_by     BIGINT(20) UNSIGNED NOT NULL,
				created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY idx_task (task_id),
				KEY idx_file (file_id)
			) ENGINE=InnoDB {$charset};",

			// 12. Kanban columns.
			"CREATE TABLE {$prefix}kanban_columns (
				id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				client_id       BIGINT(20) UNSIGNED NOT NULL,
				title           VARCHAR(100) NOT NULL,
				status_key      VARCHAR(30) NOT NULL,
				position        INT(11) NOT NULL DEFAULT 0,
				is_system       TINYINT(1) NOT NULL DEFAULT 0,
				wip_limit       INT(11) DEFAULT NULL,
				color           VARCHAR(20) DEFAULT NULL,
				created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY idx_client_position (client_id, position),
				UNIQUE KEY uniq_client_status (client_id, status_key)
			) ENGINE=InnoDB {$charset};",

			// 13. Task activity.
			"CREATE TABLE {$prefix}task_activity (
				id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				task_id         BIGINT(20) UNSIGNED NOT NULL,
				user_id         BIGINT(20) UNSIGNED NOT NULL,
				action          VARCHAR(50) NOT NULL,
				from_value      VARCHAR(255) DEFAULT NULL,
				to_value        VARCHAR(255) DEFAULT NULL,
				created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY idx_task_date (task_id, created_at),
				KEY idx_user (user_id)
			) ENGINE=InnoDB {$charset};",
		);
	}

	/**
	 * Add communication tables to the dbDelta queue.
	 *
	 * @param array  $tables  SQL strings from previous filters.
	 * @param string $charset Charset collate string.
	 * @return array
	 */
	public function add_comm_tables( $tables, $charset ) {
		return array_merge( $tables, $this->comm_tables( $charset ) );
	}

	/**
	 * Add communication table names to the drop queue.
	 *
	 * @param array $names Table names from previous filters.
	 * @return array
	 */
	public function add_comm_table_names( $names ) {
		global $wpdb;
		$prefix = $wpdb->prefix . 'prime_';

		return array_merge(
			$names,
			array(
				$prefix . 'activity_log',
				$prefix . 'chat_channels',
				$prefix . 'chat_messages',
				$prefix . 'notifications',
			)
		);
	}

	/**
	 * Returns SQL for 4 communication/logging tables.
	 *
	 * @param string $charset Charset collate string.
	 * @return array Array of CREATE TABLE SQL strings.
	 */
	public function comm_tables( $charset ) {
		global $wpdb;
		$prefix = $wpdb->prefix . 'prime_';

		return array(
			// 14. Activity log.
			"CREATE TABLE {$prefix}activity_log (
				id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				client_id       BIGINT(20) UNSIGNED DEFAULT NULL,
				user_id         BIGINT(20) UNSIGNED NOT NULL,
				action          VARCHAR(50) NOT NULL,
				entity_type     VARCHAR(30) NOT NULL,
				entity_id       BIGINT(20) UNSIGNED DEFAULT NULL,
				meta            LONGTEXT,
				ip_address      VARCHAR(45),
				created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY idx_client_date (client_id, created_at),
				KEY idx_user_date (user_id, created_at),
				KEY idx_entity (entity_type, entity_id)
			) ENGINE=InnoDB {$charset};",

			// 15. Chat channels.
			"CREATE TABLE {$prefix}chat_channels (
				id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				client_id       BIGINT(20) UNSIGNED NOT NULL,
				name            VARCHAR(100) NOT NULL,
				type            VARCHAR(20) NOT NULL DEFAULT 'public',
				created_by      BIGINT(20) UNSIGNED NOT NULL,
				created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				deleted_at      DATETIME DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY idx_client (client_id)
			) ENGINE=InnoDB {$charset};",

			// 16. Chat messages.
			"CREATE TABLE {$prefix}chat_messages (
				id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				client_id       BIGINT(20) UNSIGNED NOT NULL,
				channel_id      BIGINT(20) UNSIGNED NOT NULL,
				parent_id       BIGINT(20) UNSIGNED DEFAULT NULL,
				user_id         BIGINT(20) UNSIGNED NOT NULL,
				message         TEXT NOT NULL,
				attachments     LONGTEXT,
				created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				deleted_at      DATETIME DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY idx_channel_date (channel_id, created_at),
				KEY idx_client_channel (client_id, channel_id),
				KEY idx_parent (parent_id)
			) ENGINE=InnoDB {$charset};",

			// 17. Notifications.
			"CREATE TABLE {$prefix}notifications (
				id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				user_id         BIGINT(20) UNSIGNED NOT NULL,
				client_id       BIGINT(20) UNSIGNED DEFAULT NULL,
				type            VARCHAR(50) NOT NULL,
				title           VARCHAR(255) NOT NULL,
				message         TEXT,
				link            VARCHAR(500),
				is_read         TINYINT(1) NOT NULL DEFAULT 0,
				created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY idx_user_read (user_id, is_read, created_at),
				KEY idx_client (client_id)
			) ENGINE=InnoDB {$charset};",
		);
	}
}
