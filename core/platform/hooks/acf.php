<?php
/**
 * ACF hooks 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_filter( 'ACFFA_api_endpoint', 'trilisting_ACFFA_api_endpoint' );
if ( ! function_exists( 'trilisting_ACFFA_api_endpoint' ) ) {
	/**
	 * @since 1.0.0
	 * @return string
	 */
	function trilisting_ACFFA_api_endpoint( $api_endpoint ) {
		return 'https://data.jsdelivr.com/v1/package/resolve/gh/FortAwesome/Font-Awesome@5';
	}
}

add_filter( 'ACFFA_cdn_baseurl', 'trilisting_ACFFA_cdn_baseurl' );
if ( ! function_exists( 'trilisting_ACFFA_cdn_baseurl' ) ) {
	/**
	 * @since 1.0.0
	 * @return string
	 */
	function trilisting_ACFFA_cdn_baseurl( $cdn_baseurl ) {
		return TRILISTING_ASSETS_URL . 'libs/font-awesome5/css/all.min.css';
	}
}

add_filter( 'ACFFA_cdn_filepath', 'trilisting_ACFFA_cdn_filepath' );
if ( ! function_exists( 'trilisting_ACFFA_cdn_filepath' ) ) {
	/**
	 * Filepatch.
	 * 
	 * @since 1.0.0
	 * @return string
	 */
	function trilisting_ACFFA_cdn_filepath( $cdn_filepath ) {
		return '';
	}
}

add_filter( 'ACFFA_override_version', 'trilisting_ACFFA_override_version' );
if ( ! function_exists( 'trilisting_ACFFA_override_version' ) ) {
	/**
	 * Ovveride version.
	 * 
	 * @since 1.0.0
	 * @return bool
	 */
	function trilisting_ACFFA_override_version( $bool ) {
		return true;
	}
}

if ( ! function_exists( 'trilisting_ACF_PRO_google_map_key' ) && class_exists( 'acf' ) ) {
	function trilisting_ACF_PRO_google_map_key() {
		$google_maps_api_key = get_trilisting_option( 'google_maps_api_key' );

		if ( ! empty( $google_maps_api_key ) ) {
			acf_update_setting( 'google_api_key', $google_maps_api_key );
		}
	}

	add_action('acf/init', 'trilisting_ACF_PRO_google_map_key');
}

// acf settings field
if ( function_exists( 'acf_render_field_setting' ) ) {
	add_action('acf/render_field_settings', 'trilisting_render_field_settings');
	if ( ! function_exists( 'trilisting_render_field_settings' ) ) {
		function trilisting_render_field_settings( $field ) {
			acf_render_field_setting( $field, [
				'label'        => esc_html__( 'Hide label on front?', 'trilisting' ),
				'instructions' => '',
				'name'         => 'tril_hidden_label',
				'type'         => 'true_false',
				'ui'           => 1,
			], true );
		}
	}
}
