<?php
/**
 * Plugin Name: PRIME Core
 * Description: Roles, login routing, client portal gating, custom tables, CPTs for the PRIME platform.
 * Version:     0.2.0
 * Author:      PRIME
 * License:     GPL-2.0-or-later
 * Text Domain: prime-core
 * Requires PHP: 8.1
 * Requires at least: 6.0
 *
 * @package prime-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PRIME_CORE_VERSION', '0.2.0' );
define( 'PRIME_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'PRIME_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'PRIME_CORE_FILE', __FILE__ );
define( 'PRIME_PORTAL_SLUG', 'portal' );

require_once PRIME_CORE_PATH . 'includes/class-prime-roles.php';
require_once PRIME_CORE_PATH . 'includes/class-prime-auth.php';
require_once PRIME_CORE_PATH . 'includes/class-prime-portal.php';
require_once PRIME_CORE_PATH . 'includes/class-prime-db.php';
require_once PRIME_CORE_PATH . 'includes/class-prime-cpt.php';
require_once PRIME_CORE_PATH . 'includes/class-prime-acf.php';
require_once PRIME_CORE_PATH . 'includes/class-prime-routing.php';
require_once PRIME_CORE_PATH . 'includes/class-prime-magic-login.php';
require_once PRIME_CORE_PATH . 'includes/functions.php';

// Initialize singletons.
Prime_Portal::instance();
Prime_CPT::instance();
Prime_ACF::instance();
Prime_Routing::instance();
Prime_Magic_Login::instance();

// Activation hook — create tables, register roles, flush rewrites.
register_activation_hook(
	__FILE__,
	function () {
		Prime_DB::instance()->create_tables();
		Prime_Roles::instance()->register_roles();
		flush_rewrite_rules();
	}
);

// Deactivation hook — flush rewrites only.
register_deactivation_hook(
	__FILE__,
	function () {
		flush_rewrite_rules();
	}
);

// Enqueue app layout CSS.
add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style(
			'prime-app-layout',
			PRIME_CORE_URL . 'templates/css/app-layout.css',
			array(),
			PRIME_CORE_VERSION
		);
	}
);
