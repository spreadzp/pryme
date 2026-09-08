<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activity log writer.
 */
class Prime_Activity {

	public static function log( $client_id, $action, $entity_type = '', $entity_id = 0, $meta = [] ) {
		global $wpdb;

		$user_id = get_current_user_id();

		$wpdb->insert(
			$wpdb->prefix . 'prime_activity_log',
			[
				'client_id'   => intval( $client_id ),
				'user_id'     => intval( $user_id ),
				'action'      => sanitize_key( $action ),
				'entity_type' => sanitize_key( $entity_type ),
				'entity_id'   => intval( $entity_id ),
				'meta'        => wp_json_encode( $meta ),
			]
		);

		return $wpdb->insert_id;
	}
}
