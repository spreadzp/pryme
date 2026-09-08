<?php
/**
 * ACF field registration for CPT 'client'.
 *
 * @package prime-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ACF field registration.
 *
 * @package prime-core
 */
class Prime_ACF {

	/**
	 * Singleton instance.
	 *
	 * @var Prime_ACF|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Prime_ACF
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor — register fields on acf/init.
	 */
	private function __construct() {
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_action( 'init', array( $this, 'register_fields' ), 20 );
	}

	/**
	 * Register ACF field group for CPT 'client'.
	 * Runs on 'acf/init' hook — only fires if ACF is active.
	 */
	public function register_fields() {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'                   => 'group_prime_client',
				'title'                 => __( 'Client Information', 'prime-core' ),
				'fields'                => array(
					array(
						'key'           => 'field_client_status',
						'label'         => __( 'Status', 'prime-core' ),
						'name'          => 'client_status',
						'type'          => 'select',
						'instructions'  => __( 'Current client status', 'prime-core' ),
						'required'      => 1,
						'choices'       => array(
							'active'   => __( 'Active', 'prime-core' ),
							'paused'   => __( 'Paused', 'prime-core' ),
							'archived' => __( 'Archived', 'prime-core' ),
							'lead'     => __( 'Lead', 'prime-core' ),
						),
						'default_value' => 'lead',
						'layout'        => 'horizontal',
						'return_format' => 'value',
					),
					array(
						'key'          => 'field_client_slug',
						'label'        => __( 'Client Slug', 'prime-core' ),
						'name'         => 'client_slug',
						'type'         => 'text',
						'instructions' => __( 'URL slug for this client (e.g. acme-corp → /client/acme-corp/)', 'prime-core' ),
						'required'     => 0,
						'placeholder'  => 'acme-corp',
					),
					array(
						'key'      => 'field_client_company_name',
						'label'    => __( 'Company Name', 'prime-core' ),
						'name'     => 'client_company_name',
						'type'     => 'text',
						'required' => 1,
					),
					array(
						'key'      => 'field_client_contact_email',
						'label'    => __( 'Contact Email', 'prime-core' ),
						'name'     => 'client_contact_email',
						'type'     => 'email',
						'required' => 1,
					),
					array(
						'key'      => 'field_client_contact_phone',
						'label'    => __( 'Contact Phone', 'prime-core' ),
						'name'     => 'client_contact_phone',
						'type'     => 'text',
						'required' => 0,
					),
					array(
						'key'           => 'field_client_logo',
						'label'         => __( 'Logo', 'prime-core' ),
						'name'          => 'client_logo',
						'type'          => 'image',
						'instructions'  => __( 'Client company logo', 'prime-core' ),
						'return_format' => 'array',
						'preview_size'  => 'thumbnail',
						'library'       => 'all',
						'required'      => 0,
					),
					array(
						'key'            => 'field_client_start_date',
						'label'          => __( 'Start Date', 'prime-core' ),
						'name'           => 'client_start_date',
						'type'           => 'date_picker',
						'display_format' => 'd/m/Y',
						'return_format'  => 'Y-m-d',
						'first_day'      => 1,
						'required'       => 0,
					),
					array(
						'key'            => 'field_client_end_date',
						'label'          => __( 'End Date', 'prime-core' ),
						'name'           => 'client_end_date',
						'type'           => 'date_picker',
						'display_format' => 'd/m/Y',
						'return_format'  => 'Y-m-d',
						'first_day'      => 1,
						'required'       => 0,
					),
					array(
						'key'           => 'field_client_tariff',
						'label'         => __( 'Tariff', 'prime-core' ),
						'name'          => 'client_tariff',
						'type'          => 'select',
						'choices'       => array(
							'basic'      => __( 'Basic', 'prime-core' ),
							'pro'        => __( 'Pro', 'prime-core' ),
							'enterprise' => __( 'Enterprise', 'prime-core' ),
						),
						'default_value' => 'basic',
						'layout'        => 'horizontal',
						'return_format' => 'value',
						'required'      => 0,
					),
					array(
						'key'      => 'field_client_notes',
						'label'    => __( 'Notes', 'prime-core' ),
						'name'     => 'client_notes',
						'type'     => 'textarea',
						'rows'     => 4,
						'required' => 0,
					),
				),
				'location'              => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'client',
						),
					),
				),
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen'        => '',
				'active'                => true,
				'description'           => __( 'Client information fields for the PRIME platform', 'prime-core' ),
			)
		);
	}
}
