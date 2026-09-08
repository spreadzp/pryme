<?php
/**
 * Uninstall handler — drops tables, removes roles, cleans options.
 *
 * @package prime-core
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-prime-db.php';
Prime_DB::instance()->drop_tables();

// Remove roles.
remove_role( 'prime_client' );
remove_role( 'prime_employee' );

// Remove options.
delete_option( 'prime_db_version' );
