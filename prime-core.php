<?php
/**
 * Plugin Name: PRIME Core
 * Plugin URI: https://alexpryymak.com
 * Description: Client Portal SaaS — tasks, files, chat, access management.
 * Version: 1.0.0
 * Author: Alex Pryymak
 * License: proprietary
 * Text Domain: prime-core
 * Requires PHP: 8.2
 * Requires WP: 6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PRIME_VERSION', '1.0.0' );
define( 'PRIME_PATH', plugin_dir_path( __FILE__ ) );
define( 'PRIME_URL', plugin_dir_url( __FILE__ ) );
define( 'PRIME_FILE', __FILE__ );
define( 'PRIME_FROM_EMAIL', 'support@alexpryymak.com' );

require_once PRIME_PATH . 'includes/class-prime-activity.php';

/**
 * Main plugin class.
 */
class Prime_Core {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', [ $this, 'init' ] );
	}

	public function init() {
		do_action( 'prime_core_loaded' );
	}
}

Prime_Core::instance();
