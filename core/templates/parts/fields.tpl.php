<?php
/**
 * The fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$acf_fields = $options_fields = [];
if ( function_exists( 'get_field_objects' ) ) {
	$acf_fields     = get_field_objects( $post->ID );
	$options_fields = Trilisting_Widgets_Platform::get_trilisting_option( get_post_type() . '_meta' );
}

if ( ! empty( $acf_fields ) ) {
	foreach ( $acf_fields as $key_field => $field ) {
		$method_field = $field['type'] . '_field';
		if (
			method_exists( 'TRILISTING\Trilisting_Acf_Fields', $method_field ) &&
			isset( $options_fields[ $field['name'] ] ) &&
			1 != $options_fields[ $field['name'] ]
		) {
			echo TRILISTING\Trilisting_Acf_Fields::$method_field( $field );
		}
	}
}
