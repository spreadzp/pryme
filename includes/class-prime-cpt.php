<?php
/**
 * Custom Post Type registration.
 *
 * @package prime-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Post Type registration.
 *
 * @package prime-core
 */
class Prime_CPT {

	/**
	 * Singleton instance.
	 *
	 * @var Prime_CPT|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Prime_CPT
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor — hooks registered in slice 1-5.
	 */
	private function __construct() {
		// CPT registration hooks added in slice 1-5.
	}

	/**
	 * Register all custom post types.
	 * Implemented in slice 1-5.
	 */
	public function register_cpts() {
		// TODO: slice 1-5.
	}
}
