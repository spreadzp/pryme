<?php
/**
 * URL routing for the /app/ zone, /login/ page, and legacy /portal/ redirect.
 *
 * @package prime-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URL routing for the /app/ zone.
 *
 * Registers rewrite rules, custom query vars, and handles template_redirect
 * to route requests to the correct template files.
 *
 * @package prime-core
 */
class Prime_Routing {

	/**
	 * Singleton instance.
	 *
	 * @var Prime_Routing|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Prime_Routing
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
		add_action( 'init', array( $this, 'register_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'register_query_vars' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ), 5 );
	}

	/**
	 * Register all /app/ zone rewrite rules.
	 * Runs on 'init' hook.
	 */
	public function register_rewrite_rules() {
		// Login page.
		add_rewrite_rule( '^login/?$', 'index.php?prime_template=login', 'top' );

		// /app/ default redirect (role-based).
		add_rewrite_rule( '^app/?$', 'index.php?prime_template=app_redirect', 'top' );

		// Admin panel.
		add_rewrite_rule( '^app/admin/?$', 'index.php?prime_template=admin', 'top' );
		add_rewrite_rule( '^app/admin/clients/?$', 'index.php?prime_template=admin&prime_section=clients', 'top' );
		add_rewrite_rule( '^app/admin/clients/([^/]+)/?$', 'index.php?prime_template=admin&prime_section=client_card&prime_client_slug=$matches[1]', 'top' );
		add_rewrite_rule( '^app/admin/employees/?$', 'index.php?prime_template=admin&prime_section=employees', 'top' );
		add_rewrite_rule( '^app/admin/billing/?$', 'index.php?prime_template=admin&prime_section=billing', 'top' );

		// Workspace.
		add_rewrite_rule( '^app/workspace/?$', 'index.php?prime_template=workspace', 'top' );
		add_rewrite_rule( '^app/workspace/tasks/?$', 'index.php?prime_template=workspace&prime_section=tasks', 'top' );
		add_rewrite_rule( '^app/workspace/clients/?$', 'index.php?prime_template=workspace&prime_section=clients', 'top' );

		// Client portal — base + sections.
		add_rewrite_rule( '^app/client/([^/]+)/?$', 'index.php?prime_template=client&prime_client_slug=$matches[1]', 'top' );
		add_rewrite_rule( '^app/client/([^/]+)/tasks/?$', 'index.php?prime_template=client&prime_client_slug=$matches[1]&prime_section=tasks', 'top' );
		add_rewrite_rule( '^app/client/([^/]+)/files/?$', 'index.php?prime_template=client&prime_client_slug=$matches[1]&prime_section=files', 'top' );
		add_rewrite_rule( '^app/client/([^/]+)/access/?$', 'index.php?prime_template=client&prime_client_slug=$matches[1]&prime_section=access', 'top' );
		add_rewrite_rule( '^app/client/([^/]+)/team/?$', 'index.php?prime_template=client&prime_client_slug=$matches[1]&prime_section=team', 'top' );
		add_rewrite_rule( '^app/client/([^/]+)/chat/?$', 'index.php?prime_template=client&prime_client_slug=$matches[1]&prime_section=chat', 'top' );
		add_rewrite_rule( '^app/client/([^/]+)/gantt/?$', 'index.php?prime_template=client&prime_client_slug=$matches[1]&prime_section=gantt', 'top' );
		add_rewrite_rule( '^app/client/([^/]+)/invoices/?$', 'index.php?prime_template=client&prime_client_slug=$matches[1]&prime_section=invoices', 'top' );
		add_rewrite_rule( '^app/client/([^/]+)/knowledge-base/?$', 'index.php?prime_template=client&prime_client_slug=$matches[1]&prime_section=knowledge_base', 'top' );
		add_rewrite_rule( '^app/client/([^/]+)/time-tracking/?$', 'index.php?prime_template=client&prime_client_slug=$matches[1]&prime_section=time_tracking', 'top' );
		add_rewrite_rule( '^app/client/([^/]+)/reports/?$', 'index.php?prime_template=client&prime_client_slug=$matches[1]&prime_section=reports', 'top' );

		// Legacy /portal/ redirect.
		add_rewrite_rule( '^portal/?$', 'index.php?prime_template=portal_redirect', 'top' );
	}

	/**
	 * Register custom query vars.
	 *
	 * @param array $vars Existing query vars.
	 * @return array
	 */
	public function register_query_vars( $vars ) {
		$vars[] = 'prime_template';
		$vars[] = 'prime_section';
		$vars[] = 'prime_client_slug';
		return $vars;
	}

	/**
	 * Template redirect — routes to the correct template based on query vars.
	 * Priority 5 — runs before default template loading.
	 */
	public function template_redirect() {
		$template = get_query_var( 'prime_template' );

		if ( ! $template ) {
			return;
		}

		// Legacy /portal/ redirect.
		if ( 'portal_redirect' === $template ) {
			wp_redirect( home_url( '/app/' ) );
			exit;
		}

		// /app/ default redirect (role-based).
		if ( 'app_redirect' === $template ) {
			$this->handle_app_redirect();
			return;
		}

		// Login page — no auth required.
		if ( 'login' === $template ) {
			if ( is_user_logged_in() ) {
				wp_redirect( home_url( '/app/' ) );
				exit;
			}
			include PRIME_CORE_PATH . 'templates/template-login.php';
			exit;
		}

		// Auth check for all /app/ routes.
		if ( in_array( $template, array( 'admin', 'workspace', 'client' ), true ) ) {
			if ( ! is_user_logged_in() ) {
				wp_redirect( home_url( '/login/' ) );
				exit;
			}
		}

		$user = wp_get_current_user();

		switch ( $template ) {
			case 'admin':
				if ( ! user_can( $user, 'prime_view_admin' ) ) {
					wp_redirect( home_url( '/app/' ) );
					exit;
				}
				include PRIME_CORE_PATH . 'templates/template-admin.php';
				exit;

			case 'workspace':
				if ( ! user_can( $user, 'prime_view_workspace' ) ) {
					wp_redirect( home_url( '/app/' ) );
					exit;
				}
				include PRIME_CORE_PATH . 'templates/template-workspace.php';
				exit;

			case 'client':
				$client_slug = get_query_var( 'prime_client_slug' );
				$client     = get_page_by_path( $client_slug, OBJECT, 'client' );

				if ( ! $client ) {
					$this->show_404();
					exit;
				}

				if ( ! prime_user_has_client_access( $user->ID, $client->ID ) ) {
					wp_redirect( home_url( '/app/' ) );
					exit;
				}

				$section            = get_query_var( 'prime_section' );
				$optional_sections  = array( 'chat', 'gantt', 'invoices', 'knowledge_base', 'time_tracking', 'reports' );
				if ( in_array( $section, $optional_sections, true ) ) {
					if ( ! prime_is_module_enabled( $client->ID, $section ) ) {
						wp_redirect( home_url( "/app/client/{$client_slug}/" ) );
						exit;
					}
				}

				include PRIME_CORE_PATH . 'templates/template-client.php';
				exit;
		}
	}

	/**
	 * Handle /app/ default redirect — role-based.
	 */
	private function handle_app_redirect() {
		if ( ! is_user_logged_in() ) {
			wp_redirect( home_url( '/login/' ) );
			exit;
		}

		$user = wp_get_current_user();

		if ( user_can( $user, 'prime_view_admin' ) ) {
			wp_redirect( home_url( '/app/admin/' ) );
			exit;
		}

		if ( user_can( $user, 'prime_view_workspace' ) ) {
			wp_redirect( home_url( '/app/workspace/' ) );
			exit;
		}

		if ( user_can( $user, 'prime_view_client_portal' ) ) {
			$client_id = get_user_meta( $user->ID, '_prime_client_id', true );
			$client    = get_post( $client_id );
			if ( $client && 'client' === $client->post_type ) {
				wp_redirect( home_url( "/app/client/{$client->post_name}/" ) );
				exit;
			}
			wp_redirect( home_url( '/login/?error=no_client' ) );
			exit;
		}

		wp_redirect( home_url( '/login/' ) );
		exit;
	}

	/**
	 * Show 404 page.
	 */
	private function show_404() {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		get_template_part( 404 );
	}
}
