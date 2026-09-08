<?php
/**
 * Query helper and access check functions.
 *
 * @package prime-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the current client ID based on the logged-in user's role.
 *
 * For client_user: returns their assigned client_id from user meta.
 * For employee/admin: returns client_id from URL query var (prime_client_slug).
 *
 * @return int|null Client ID or null if not determined.
 */
function prime_get_current_client_id() {
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return null;
	}

	// For client user: get their assigned client_id.
	if ( user_can( $user_id, 'prime_view_portal' ) && ! user_can( $user_id, 'manage_options' ) && ! user_can( $user_id, 'prime_view_all_clients' ) ) {
		$client_id = get_user_meta( $user_id, '_prime_client_id', true );
		return $client_id ? (int) $client_id : null;
	}

	// For employee/admin: get client_id from URL.
	$client_slug = get_query_var( 'prime_client_slug' );
	if ( $client_slug ) {
		return prime_get_client_id_by_slug( $client_slug );
	}

	return null;
}

/**
 * Get client post ID by slug.
 *
 * @param string $slug Client slug (post_name of CPT 'client').
 * @return int|null Client post ID or null if not found.
 */
function prime_get_client_id_by_slug( $slug ) {
	$client = get_page_by_path( $slug, OBJECT, 'client' );
	return $client ? $client->ID : null;
}

/**
 * Check if a user has access to a specific client.
 *
 * @param int $user_id   User ID.
 * @param int $client_id Client post ID.
 * @return bool True if user has access.
 */
function prime_user_has_client_access( $user_id, $client_id ) {
	if ( ! $user_id || ! $client_id ) {
		return false;
	}

	// Admins and owner have access to all clients.
	if ( user_can( $user_id, 'manage_options' ) || user_can( $user_id, 'prime_view_all_clients' ) ) {
		return true;
	}

	// Check client_team table.
	global $wpdb;
	$table = $wpdb->prefix . 'prime_client_team';

	return (bool) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table}
			 WHERE client_id = %d AND user_id = %d AND deleted_at IS NULL",
			$client_id,
			$user_id
		)
	);
}

/**
 * Check if a user can perform a specific action on a client.
 *
 * @param int    $user_id   User ID.
 * @param int    $client_id Client post ID.
 * @param string $action    Action key (e.g. 'tasks.create', 'files.delete').
 * @return bool True if allowed.
 */
function prime_user_can( $user_id, $client_id, $action ) {
	if ( ! $user_id || ! $client_id ) {
		return false;
	}

	// Admins and owner have full access.
	if ( user_can( $user_id, 'manage_options' ) || user_can( $user_id, 'prime_manage_clients' ) ) {
		return true;
	}

	global $wpdb;
	$table = $wpdb->prefix . 'prime_client_team';

	$team = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT role, permissions FROM {$table}
			 WHERE client_id = %d AND user_id = %d AND deleted_at IS NULL",
			$client_id,
			$user_id
		)
	);

	if ( ! $team ) {
		return false;
	}

	// Lead role has full CRUD on everything.
	if ( 'lead' === $team->role ) {
		return true;
	}

	// Check granular permissions.
	$permissions = json_decode( $team->permissions, true );
	if ( ! is_array( $permissions ) ) {
		$permissions = array();
	}

	// Split the action key on the dot to check nested permissions.
	$parts = explode( '.', $action );
	if ( count( $parts ) !== 2 ) {
		return false;
	}

	return isset( $permissions[ $parts[0] ][ $parts[1] ] )
		? (bool) $permissions[ $parts[0] ][ $parts[1] ]
		: false;
}

/**
 * Get client tasks with pagination.
 *
 * @param int   $client_id Client post ID.
 * @param array $args      Optional query arguments.
 *     @type int    $per_page    Items per page (default 20).
 *     @type int    $page        Page number (default 1).
 *     @type string $status      Filter by status (default null = all).
 *     @type int    $assigned_to Filter by assignee (default null = all).
 * }
 * @return array Array of task objects.
 */
function prime_get_client_tasks( $client_id, $args = array() ) {
	global $wpdb;

	$defaults = array(
		'per_page'    => 20,
		'page'        => 1,
		'status'      => null,
		'assigned_to' => null,
	);
	$args     = wp_parse_args( $args, $defaults );

	$table    = $wpdb->prefix . 'prime_client_tasks';
	$per_page = (int) $args['per_page'];
	$offset   = ( (int) $args['page'] - 1 ) * $per_page;

	$where  = 'WHERE client_id = %d AND deleted_at IS NULL';
	$params = array( $client_id );

	if ( $args['status'] ) {
		$where   .= ' AND status = %s';
		$params[] = $args['status'];
	}

	if ( $args['assigned_to'] ) {
		$where   .= ' AND assigned_to = %d';
		$params[] = (int) $args['assigned_to'];
	}

	$params[] = $per_page;
	$params[] = $offset;

	return $wpdb->get_results(
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders -- placeholders in $where are dynamic, count varies.
		$wpdb->prepare(
			"SELECT * FROM {$table} {$where}
			 ORDER BY position ASC, created_at DESC
			 LIMIT %d OFFSET %d",
			$params
		)
	);
}

/**
 * Get enabled modules for a client.
 *
 * @param int $client_id Client post ID.
 * @return array Array of objects with module_key, is_enabled, settings.
 */
function prime_get_client_modules( $client_id ) {
	global $wpdb;
	$table = $wpdb->prefix . 'prime_client_modules';

	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT module_key, is_enabled, settings
			 FROM {$table}
			 WHERE client_id = %d AND is_enabled = 1",
			$client_id
		)
	);
}

/**
 * Check if a specific module is enabled for a client.
 *
 * @param int    $client_id  Client post ID.
 * @param string $module_key Module key (gantt, invoices, chat, knowledge_base, time_tracking, reports).
 * @return bool True if enabled.
 */
function prime_is_module_enabled( $client_id, $module_key ) {
	global $wpdb;
	$table = $wpdb->prefix . 'prime_client_modules';

	return (bool) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table}
			 WHERE client_id = %d AND module_key = %s AND is_enabled = 1",
			$client_id,
			$module_key
		)
	);
}

/**
 * Get team members for a client.
 *
 * @param int $client_id Client post ID.
 * @return array Array of objects with user_id, role, permissions.
 */
function prime_get_client_team( $client_id ) {
	global $wpdb;
	$table = $wpdb->prefix . 'prime_client_team';

	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT user_id, role, permissions
			 FROM {$table}
			 WHERE client_id = %d AND deleted_at IS NULL
			 ORDER BY role ASC, created_at ASC",
			$client_id
		)
	);
}

/**
 * Get Kanban columns for a client's board.
 *
 * @param int $client_id Client post ID.
 * @return array Array of column objects ordered by position.
 */
function prime_get_kanban_columns( $client_id ) {
	global $wpdb;
	$table = $wpdb->prefix . 'prime_kanban_columns';

	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$table}
			 WHERE client_id = %d
			 ORDER BY position ASC",
			$client_id
		)
	);
}

/**
 * Log an activity to the audit trail.
 *
 * @param int      $client_id   Client post ID (null for non-client actions).
 * @param string   $action      Action key (e.g. 'task.created').
 * @param string   $entity_type Entity type (e.g. 'task', 'file').
 * @param int|null $entity_id   Entity ID.
 * @param array    $meta        Additional metadata (stored as JSON).
 * @return int Inserted row ID.
 */
function prime_log_activity( $client_id, $action, $entity_type, $entity_id = null, $meta = array() ) {
	global $wpdb;
	$table = $wpdb->prefix . 'prime_activity_log';

	$ip_address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : null;

	$wpdb->insert(
		$table,
		array(
			'client_id'   => $client_id,
			'user_id'     => get_current_user_id(),
			'action'      => $action,
			'entity_type' => $entity_type,
			'entity_id'   => $entity_id,
			'meta'        => wp_json_encode( $meta ),
			'ip_address'  => $ip_address,
		),
		array( '%d', '%d', '%s', '%s', '%d', '%s', '%s' )
	);

	return (int) $wpdb->insert_id;
}
