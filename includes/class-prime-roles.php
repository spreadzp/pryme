<?php
/**
 * Roles and capabilities for the PRIME platform.
 *
 * @package prime-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Roles and capabilities for the PRIME platform.
 *
 * @package prime-core
 */
class Prime_Roles {

	/**
	 * Singleton instance.
	 *
	 * @var Prime_Roles|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Prime_Roles
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor — hooks are registered lazily.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'register_roles' ) );
	}

	/**
	 * Register custom roles and assign capabilities.
	 * Idempotent — safe to call on every init and on activation.
	 */
	public function register_roles() {
		if ( ! get_role( 'prime_client' ) ) {
			add_role( 'prime_client', 'Client', array( 'read' => true ) );
		}
		if ( ! get_role( 'prime_employee' ) ) {
			add_role(
				'prime_employee',
				'Employee',
				array(
					'read'         => true,
					'upload_files' => true,
				)
			);
		}

		$this->cleanup_legacy_roles();

		$employee = get_role( 'prime_employee' );
		if ( $employee ) {
			$caps = array(
				'prime_view_portal',
				'prime_view_all_clients',
				'prime_manage_tasks',
				'prime_view_workspace',
				'prime_view_client_portal',
			);
			foreach ( $caps as $cap ) {
				$employee->add_cap( $cap );
			}
		}

		$client = get_role( 'prime_client' );
		if ( $client ) {
			$client->add_cap( 'prime_view_portal' );
			$client->add_cap( 'prime_view_client_portal' );
		}

		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$caps = array(
				'prime_view_portal',
				'prime_view_all_clients',
				'prime_manage_tasks',
				'prime_manage_clients',
				'prime_view_admin',
				'prime_view_workspace',
				'prime_view_client_portal',
			);
			foreach ( $caps as $cap ) {
				$admin->add_cap( $cap );
			}
		}
	}

	/**
	 * Remove legacy pryme_* roles left from older plugin versions.
	 * Reassign users to the new prime_* roles before removing.
	 */
	private function cleanup_legacy_roles() {
		$legacy_map = array(
			'pryme_employee' => 'prime_employee',
			'pryme_client'   => 'prime_client',
		);

		foreach ( $legacy_map as $old => $new ) {
			if ( get_role( $old ) ) {
				$users = get_users( array( 'role' => $old ) );
				foreach ( $users as $user ) {
					$user->set_role( $new );
				}
				remove_role( $old );
			}
		}
	}

	/**
	 * Get the portal page URL.
	 *
	 * @return string
	 */
	public function portal_url() {
		$page = get_page_by_path( PRIME_PORTAL_SLUG );
		return $page ? get_permalink( $page ) : home_url( '/' . PRIME_PORTAL_SLUG . '/' );
	}

	/**
	 * Check if a user is staff (admin or employee with view_all_clients).
	 *
	 * @param WP_User|null $user User to check. Defaults to current user.
	 * @return bool
	 */
	public function is_staff( $user = null ) {
		if ( ! $user ) {
			$user = wp_get_current_user();
		}
		if ( ! $user instanceof WP_User ) {
			return false;
		}
		return user_can( $user, 'manage_options' ) || user_can( $user, 'prime_view_all_clients' );
	}
}
