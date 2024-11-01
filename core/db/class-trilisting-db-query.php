<?php

namespace TRILISTING\DB;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class for database queries.
 *
 * ACF V5
 * 
 * @since 1.0.0
 */
class Trilisting_DB_Query {
	/**
	 * Stores acf fields.
	 * 
	 * @static
	 * 
	 * @since  1.0.0
	 * @access public
	 * @var array
	 */
	public static $query_acf_reg_fields = [];

	/**
	 * List ACF v5 fields.
	 * 
	 * @static
	 * 
	 * @since  1.0.0
	 * @access public
	 * @return array ACF fields
	 */
	public static function get_query_acf_reg_fields() {
		$query = '';

		if ( class_exists( 'acf' ) ) {
			if ( empty( self::$query_acf_reg_fields ) ) {
				self::$query_acf_reg_fields = self::query_acf_register_fields();
			}
			$query = self::$query_acf_reg_fields;
		}

		return $query;
	}

	/**
	 * List user fields.
	 * 
	 * @static
	 * 
	 * @since  1.0.0
	 * @param  $user_id
	 * @access public
	 * @return array ACF fields
	 */
	public static function get_user_profile_fields( $user_id ) {
		$user_fields = [];
		$query_acf   = self::get_query_acf_reg_fields();
		$user_data   = get_userdata( $user_id );

		if ( ! empty( $query_acf ) && ! empty( $user_id ) ) {
			foreach ( $query_acf as $key => $field_name ) {
				if ( isset( $field_name['rule']['location'] ) ) {
					foreach ( $field_name['rule']['location'] as $rule_key => $post_rules ) {
						if (
							isset( $post_rules[0]['param'] ) &&
							'user_role' == $post_rules[0]['param'] &&
							'==' == $post_rules[0]['operator']
						) {
							if ( 'all' === $post_rules[0]['value'] ) {
								array_push( $user_fields, $field_name['post_parent'] );
							} else {
								foreach ( $user_data->roles as $role ) {
									if ( $role === $post_rules[0]['value'] ) {
										array_push( $user_fields, intval( $field_name['post_parent'] ) );
									}
								}
							}
						}
					} // End foreach
				} // End if
			} // End foreach
		} // End if

		return $user_fields;
	}

	/**
	 * Grouping of fields.
	 * 
	 * @static
	 * 
	 * @since  1.0.0
	 * @access public
	 * @return array ACF groups fields
	 */
	public static function get_group_fields() {
		$post_fields = [];
		$user_fields = [];
		$query_acf   = self::get_query_acf_reg_fields();

		if ( ! empty( $query_acf ) ) {
			foreach ( $query_acf as $key => $field_name ) {
				if ( isset( $field_name['rule']['location'] ) ) {
					foreach ( $field_name['rule']['location'] as $rule_key => $post_rules ) {
						if (
							isset( $post_rules[0]['param'] ) &&
							'post_type' == $post_rules[0]['param'] &&
							'==' == $post_rules[0]['operator']
						) {
							// user groups
							if ( ! isset( $user_fields[ $post_rules[0]['value'] ] ) ) {
								$user_fields[ $post_rules[0]['value'] ] = [];
							}
	
							$filed_id = intval( $field_name['post_parent'] );
							$user_fields[ $post_rules[0]['value'] ][ $filed_id ] = $filed_id;
	
							// fields
							$post_fields[ $post_rules[0]['value'] ][ $field_name['ID'] ]          = $field_name['post_content'];
							$post_fields[ $post_rules[0]['value'] ][ $field_name['ID'] ]['ID']    = $field_name['ID'];
							$post_fields[ $post_rules[0]['value'] ][ $field_name['ID'] ]['label'] = $field_name['post_title'];
							$post_fields[ $post_rules[0]['value'] ][ $field_name['ID'] ]['name']  = $field_name['post_excerpt'];
						}
					}
				} // End if
			} // End foreach
		} // End if

		return [
			'user' => $user_fields,
			'post' => $post_fields,
		];
	}

	/**
	 * ACF register fields.
	 * 
	 * @static
	 * 
	 * @since  1.0.0
	 * @access public
	 * @return array Register acf fields.
	 */
	public static function get_acf_register_fields() {
		return self::get_query_acf_reg_fields();
	}

	/**
	 * Get a list of all ACF v5 fields.
	 * 
	 * @static
	 * 
	 * @since 1.0.0
	 * @access public
	 * @return mixed
	 */
	public static function query_acf_register_fields() {
		global $wpdb;
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT ID,post_title,post_parent,post_excerpt,post_content FROM $wpdb->posts WHERE post_type=%s AND post_status=%s" , 'acf-field', 'publish' ), ARRAY_A );

		if ( ! empty( $result ) ) {
			$parent_id = '';
			$rules     = [];

			foreach ( $result as $key => $sub_array ) {
				foreach ( $sub_array as $sub_key => $field ) {
					if ( $parent_id === $result[ $key ]['post_parent'] ) {
					} else {
						$rules = $wpdb->get_results( $wpdb->prepare( "SELECT post_content FROM $wpdb->posts WHERE post_type=%s AND ID=%s" , 'acf-field-group', $result[ $key ]['post_parent'] ), ARRAY_A );
					}

					if ( isset( $rules[0] ) ) {
						$result[ $key ]['rule'] = maybe_unserialize( $rules[0]['post_content'] );
					}

					if ( 'post_content' === $sub_key ) {
						$result[ $key ]['post_content'] = maybe_unserialize( $field );
					}

					$parent_id = $result[ $key ]['post_parent'];
				}
			} // End foreach
		} // End if

		return $result;
	}
}
