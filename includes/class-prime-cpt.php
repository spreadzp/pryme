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
	 * Private constructor — register CPTs on init.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'register_cpts' ) );
	}

	/**
	 * Register all custom post types.
	 */
	public function register_cpts() {
		$this->register_client();
		$this->register_portfolio();
		$this->register_service();
		$this->register_team_member();
		$this->register_invoice();
	}

	/**
	 * CPT: client — Client card, basic info, logo, status.
	 * Queryable but no public archive. ACF fields added in slice 1-6.
	 */
	private function register_client() {
		register_post_type(
			'client',
			array(
				'labels'             => array(
					'name'               => __( 'Clients', 'prime-core' ),
					'singular_name'      => __( 'Client', 'prime-core' ),
					'add_new_item'       => __( 'Add New Client', 'prime-core' ),
					'edit_item'          => __( 'Edit Client', 'prime-core' ),
					'new_item'           => __( 'New Client', 'prime-core' ),
					'view_item'          => __( 'View Client', 'prime-core' ),
					'search_items'       => __( 'Search Clients', 'prime-core' ),
					'not_found'          => __( 'No clients found', 'prime-core' ),
					'not_found_in_trash' => __( 'No clients found in Trash', 'prime-core' ),
					'all_items'          => __( 'All Clients', 'prime-core' ),
					'menu_name'          => __( 'Clients', 'prime-core' ),
				),
				'public'             => false,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_rest'       => true,
				'menu_icon'          => 'dashicons-businessperson',
				'menu_position'      => 5,
				'supports'           => array( 'title', 'thumbnail', 'editor' ),
				'has_archive'        => false,
				'rewrite'            => array( 'slug' => 'client' ),
				'capability_type'    => 'post',
				'map_meta_cap'       => true,
			)
		);
	}

	/**
	 * CPT: portfolio — Public portfolio/cases for the marketing site.
	 */
	private function register_portfolio() {
		register_post_type(
			'portfolio',
			array(
				'labels'             => array(
					'name'               => __( 'Portfolio', 'prime-core' ),
					'singular_name'      => __( 'Portfolio Item', 'prime-core' ),
					'add_new_item'       => __( 'Add New Portfolio Item', 'prime-core' ),
					'edit_item'          => __( 'Edit Portfolio Item', 'prime-core' ),
					'new_item'           => __( 'New Portfolio Item', 'prime-core' ),
					'view_item'          => __( 'View Portfolio Item', 'prime-core' ),
					'search_items'       => __( 'Search Portfolio', 'prime-core' ),
					'not_found'          => __( 'No portfolio items found', 'prime-core' ),
					'not_found_in_trash' => __( 'No portfolio items found in Trash', 'prime-core' ),
					'all_items'          => __( 'All Portfolio Items', 'prime-core' ),
					'menu_name'          => __( 'Portfolio', 'prime-core' ),
				),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_rest'       => true,
				'menu_icon'          => 'dashicons-portfolio',
				'menu_position'      => 6,
				'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
				'has_archive'        => true,
				'rewrite'            => array( 'slug' => 'portfolio' ),
				'capability_type'    => 'post',
				'map_meta_cap'       => true,
			)
		);
	}

	/**
	 * CPT: service — Agency services shown on the public site.
	 */
	private function register_service() {
		register_post_type(
			'service',
			array(
				'labels'             => array(
					'name'               => __( 'Services', 'prime-core' ),
					'singular_name'      => __( 'Service', 'prime-core' ),
					'add_new_item'       => __( 'Add New Service', 'prime-core' ),
					'edit_item'          => __( 'Edit Service', 'prime-core' ),
					'new_item'           => __( 'New Service', 'prime-core' ),
					'view_item'          => __( 'View Service', 'prime-core' ),
					'search_items'       => __( 'Search Services', 'prime-core' ),
					'not_found'          => __( 'No services found', 'prime-core' ),
					'not_found_in_trash' => __( 'No services found in Trash', 'prime-core' ),
					'all_items'          => __( 'All Services', 'prime-core' ),
					'menu_name'          => __( 'Services', 'prime-core' ),
				),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_rest'       => true,
				'menu_icon'          => 'dashicons-products',
				'menu_position'      => 7,
				'supports'           => array( 'title', 'editor', 'thumbnail' ),
				'has_archive'        => false,
				'rewrite'            => array( 'slug' => 'services' ),
				'capability_type'    => 'post',
				'map_meta_cap'       => true,
			)
		);
	}

	/**
	 * CPT: team_member — Team members for the public team page.
	 */
	private function register_team_member() {
		register_post_type(
			'team_member',
			array(
				'labels'             => array(
					'name'               => __( 'Team', 'prime-core' ),
					'singular_name'      => __( 'Team Member', 'prime-core' ),
					'add_new_item'       => __( 'Add New Team Member', 'prime-core' ),
					'edit_item'          => __( 'Edit Team Member', 'prime-core' ),
					'new_item'           => __( 'New Team Member', 'prime-core' ),
					'view_item'          => __( 'View Team Member', 'prime-core' ),
					'search_items'       => __( 'Search Team', 'prime-core' ),
					'not_found'          => __( 'No team members found', 'prime-core' ),
					'not_found_in_trash' => __( 'No team members found in Trash', 'prime-core' ),
					'all_items'          => __( 'All Team Members', 'prime-core' ),
					'menu_name'          => __( 'Team', 'prime-core' ),
				),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_rest'       => true,
				'menu_icon'          => 'dashicons-groups',
				'menu_position'      => 8,
				'supports'           => array( 'title', 'thumbnail', 'editor' ),
				'has_archive'        => false,
				'rewrite'            => array( 'slug' => 'team' ),
				'capability_type'    => 'post',
				'map_meta_cap'       => true,
			)
		);
	}

	/**
	 * CPT: invoice — Invoices for accounting (not online payments). Private.
	 */
	private function register_invoice() {
		register_post_type(
			'invoice',
			array(
				'labels'             => array(
					'name'               => __( 'Invoices', 'prime-core' ),
					'singular_name'      => __( 'Invoice', 'prime-core' ),
					'add_new_item'       => __( 'Add New Invoice', 'prime-core' ),
					'edit_item'          => __( 'Edit Invoice', 'prime-core' ),
					'new_item'           => __( 'New Invoice', 'prime-core' ),
					'view_item'          => __( 'View Invoice', 'prime-core' ),
					'search_items'       => __( 'Search Invoices', 'prime-core' ),
					'not_found'          => __( 'No invoices found', 'prime-core' ),
					'not_found_in_trash' => __( 'No invoices found in Trash', 'prime-core' ),
					'all_items'          => __( 'All Invoices', 'prime-core' ),
					'menu_name'          => __( 'Invoices', 'prime-core' ),
				),
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_rest'       => false,
				'menu_icon'          => 'dashicons-media-spreadsheet',
				'menu_position'      => 9,
				'supports'           => array( 'title' ),
				'has_archive'        => false,
				'capability_type'    => 'post',
				'map_meta_cap'       => true,
			)
		);
	}
}
