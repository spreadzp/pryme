<?php
/**
 * Magic Login integration — passwordless auth for client users.
 *
 * @package prime-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Prime_Magic_Login {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_init', array( $this, 'configure_magic_login' ) );
		add_filter( 'magic_login_allowed_user', array( $this, 'restrict_to_clients' ), 10, 2 );
		add_filter( 'magic_login_redirect_url', array( $this, 'redirect_after_login' ), 10, 2 );
		add_filter( 'magic_login_email_subject', array( $this, 'email_subject' ) );
		add_filter( 'magic_login_email_body', array( $this, 'email_body' ), 10, 3 );

		add_action( 'wp_ajax_prime_send_magic_link', array( $this, 'ajax_send_magic_link' ) );
		add_action( 'wp_ajax_nopriv_prime_send_magic_link', array( $this, 'ajax_send_magic_link' ) );
	}

	/**
	 * Set Magic Login plugin settings on first run.
	 */
	public function configure_magic_login() {
		if ( ! function_exists( 'magic_login_get_settings' ) ) {
			return;
		}

		$settings = magic_login_get_settings();

		if ( isset( $settings['is_configured'] ) && $settings['is_configured'] ) {
			return;
		}

		$settings['is_configured']   = true;
		$settings['token_ttl']       = 900;
		$settings['login_code_ttl']  = 900;
		$settings['ip_check']        = false;
		$settings['revoke_on_login'] = true;
		$settings['auto_login']      = true;
		$settings['show_login_form'] = false;

		update_option( 'magic_login_settings', $settings );
	}

	/**
	 * Only allow magic link login for client users.
	 */
	public function restrict_to_clients( $allowed, $user ) {
		if ( ! $user || ! $user->ID ) {
			return false;
		}

		return user_can( $user->ID, 'prime_view_client_portal' )
			&& ! user_can( $user->ID, 'prime_view_admin' )
			&& ! user_can( $user->ID, 'prime_view_workspace' );
	}

	/**
	 * Redirect client to their portal after magic link login.
	 */
	public function redirect_after_login( $redirect_url, $user ) {
		if ( ! $user || ! $user->ID ) {
			return $redirect_url;
		}

		$client_id = get_user_meta( $user->ID, '_prime_client_id', true );
		$client    = get_post( $client_id );

		if ( $client && 'client' === $client->post_type ) {
			return home_url( "/app/client/{$client->post_name}/" );
		}

		return home_url( '/app/' );
	}

	/**
	 * Customize email subject.
	 */
	public function email_subject( $subject ) {
		return sprintf(
			'[%s] %s',
			get_bloginfo( 'name' ),
			__( 'Your login link', 'prime-core' )
		);
	}

	/**
	 * Customize email body.
	 */
	public function email_body( $body, $login_url, $user ) {
		return sprintf(
			"<p>%s,</p>\n<p>%s</p>\n<p><a href=\"%s\" style=\"display:inline-block;padding:12px 32px;background:#4f46e5;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;\">%s</a></p>\n<p style=\"color:#9ca3af;font-size:13px;\">%s</p>",
			esc_html( $user->display_name ),
			esc_html__( 'Click the button below to log in to your client portal.', 'prime-core' ),
			esc_url( $login_url ),
			esc_html__( 'Log in to PRIME', 'prime-core' ),
			esc_html__( 'This link expires in 15 minutes and can only be used once.', 'prime-core' )
		);
	}

	/**
	 * AJAX handler for magic link request from login page.
	 */
	public function ajax_send_magic_link() {
		check_ajax_referer( 'prime_magic_link', 'prime_magic_nonce' );

		$email = sanitize_email( wp_unslash( $_POST['magic_email'] ?? '' ) );
		if ( ! $email ) {
			wp_send_json_error(
				array( 'message' => __( 'Please enter a valid email.', 'prime-core' ) ),
				400
			);
		}

		$user = get_user_by( 'email', $email );
		if ( ! $user ) {
			wp_send_json_success(
				array( 'message' => __( 'If an account exists, a login link has been sent.', 'prime-core' ) )
			);
		}

		if ( ! $this->restrict_to_clients( true, $user ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Magic links are for client accounts only.', 'prime-core' ) ),
				403
			);
		}

		if ( function_exists( 'magic_login_send_login_link' ) ) {
			$result = magic_login_send_login_link( $user );
			if ( is_wp_error( $result ) ) {
				wp_send_json_error(
					array( 'message' => $result->get_error_message() ),
					500
				);
			}
			wp_send_json_success(
				array( 'message' => __( 'Login link sent! Check your email.', 'prime-core' ) )
			);
		} else {
			wp_send_json_error(
				array( 'message' => __( 'Magic Login plugin not active.', 'prime-core' ) ),
				500
			);
		}
	}
}
