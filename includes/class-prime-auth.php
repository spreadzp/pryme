<?php
/**
 * Authentication routing and portal gating.
 *
 * @package prime-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Authentication routing and portal gating.
 *
 * @package prime-core
 */
class Prime_Auth {

	/**
	 * Singleton instance.
	 *
	 * @var Prime_Auth|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Prime_Auth
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor — register hooks.
	 */
	private function __construct() {
		add_filter( 'login_redirect', array( $this, 'login_redirect' ), 10, 3 );
		add_action( 'admin_init', array( $this, 'block_admin_access' ) );
		add_filter( 'show_admin_bar', array( $this, 'hide_admin_bar' ) );
		add_filter( 'logout_redirect', array( $this, 'logout_redirect' ), 10, 3 );
		add_action( 'template_redirect', array( $this, 'gate_portal' ) );
		add_action( 'wp_head', array( $this, 'portal_noindex' ) );
		add_filter( 'wp_sitemaps_posts_query_args', array( $this, 'exclude_portal_from_sitemap' ), 10, 2 );
	}

	/**
	 * Redirect users after login: admins → admin, clients → portal.
	 *
	 * @param string       $redirect_to Default redirect URL.
	 * @param string       $requested   Requested redirect URL.
	 * @param WP_User|null $user        Authenticated user.
	 * @return string
	 */
	public function login_redirect( $redirect_to, $requested, $user ) {
		if ( ! ( $user instanceof WP_User ) ) {
			return $redirect_to;
		}
		if ( user_can( $user, 'manage_options' ) ) {
			return $requested && false === strpos( $requested, 'wp-admin/profile.php' ) ? $redirect_to : admin_url();
		}
		if ( user_can( $user, 'prime_view_portal' ) ) {
			return Prime_Roles::instance()->portal_url();
		}
		return $redirect_to;
	}

	/**
	 * Block clients and employees from accessing wp-admin.
	 */
	public function block_admin_access() {
		if ( ! is_user_logged_in() || wp_doing_ajax() ) {
			return;
		}
		if ( current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( current_user_can( 'prime_view_portal' ) && is_admin() ) {
			wp_safe_redirect( Prime_Roles::instance()->portal_url() );
			exit;
		}
	}

	/**
	 * Hide admin bar for non-admins.
	 *
	 * @param bool $show Whether to show admin bar.
	 * @return bool
	 */
	public function hide_admin_bar( $show ) {
		return current_user_can( 'manage_options' ) ? $show : false;
	}

	/**
	 * Redirect non-admins to login page on logout.
	 *
	 * @param string       $redirect_to Default redirect URL.
	 * @param string       $requested   Requested redirect URL.
	 * @param WP_User|null $user        User logging out.
	 * @return string
	 */
	public function logout_redirect( $redirect_to, $requested, $user ) {
		if ( $user instanceof WP_User && ! user_can( $user, 'manage_options' ) ) {
			return wp_login_url();
		}
		return $redirect_to;
	}

	/**
	 * Gate the portal page — redirect to login if not authenticated.
	 */
	public function gate_portal() {
		if ( is_admin() || ! is_page( PRIME_PORTAL_SLUG ) ) {
			return;
		}
		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( wp_login_url( Prime_Roles::instance()->portal_url() ) );
			exit;
		}
		if ( ! current_user_can( 'prime_view_portal' ) ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}
	}

	/**
	 * Add noindex meta tag to portal page.
	 */
	public function portal_noindex() {
		if ( is_page( PRIME_PORTAL_SLUG ) ) {
			echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
		}
	}

	/**
	 * Exclude portal page from WordPress sitemaps.
	 *
	 * @param array  $args      Query args for sitemap.
	 * @param string $post_type Post type being queried.
	 * @return array
	 */
	public function exclude_portal_from_sitemap( $args, $post_type ) {
		if ( 'page' === $post_type ) {
			$page = get_page_by_path( PRIME_PORTAL_SLUG );
			if ( $page ) {
				$exclude              = isset( $args['post__not_in'] ) ? $args['post__not_in'] : array();
				$exclude[]            = $page->ID;
				$args['post__not_in'] = $exclude;
			}
		}
		return $args;
	}
}
