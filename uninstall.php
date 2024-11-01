<?php

// If uninstall not called from WordPress, then exit.
if ( ! defined('WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;
$key          = '%' . $wpdb->esc_like( '_trilisting_pages_opt_plugin_' ) . '%';
$delate_pages = $wpdb->get_results( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key LIKE %s" , $key ), ARRAY_A );
if ( ! empty( $delate_pages ) ) {
	foreach ( $delate_pages as $page ) {
		wp_delete_post( $page['post_id'], true );
	}
}

// Remove custom role
remove_role( 'trilisting_author' );

// Delete options
delete_option( 'trilisting_widgets_redux_option' );
delete_option( 'trilisting_post_type_places' );

// Delete options in Multisite
delete_site_option( 'trilisting_widgets_redux_option' );
