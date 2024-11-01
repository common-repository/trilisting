<?php
/**
 * ACF helpers functions.
 */

if ( ! function_exists( 'trilisting_fix_line_breaks' ) ) {
	/**
	 *  This function will loop through all array pieces and correct double line breaks from DB to XML
	 *
	 *  @param    $v (mixed)
	 *  @return   $v (mixed)
	 */
	function trilisting_fix_line_breaks( $v ) {
		if ( is_array( $v ) ) {
			foreach ( array_keys( $v ) as $k ) {
				$v[ $k ] = trilisting_fix_line_breaks( $v[ $k ] );
			}
		} elseif ( is_string( $v ) ) {
			$v = str_replace( "\r\n", "\r", $v );
		}
		return $v;
	}
}

if ( ! function_exists( 'trilisting_cdata' ) ) {
	/**
	 * Wrap given string in XML CDATA tag.
	 *
	 * @param string $str String to wrap in XML CDATA tag.
	 */
	function trilisting_cdata( $str ) {
		if ( false == seems_utf8( $str ) ) {
			$str = utf8_encode( $str );
		}

		$str = "<![CDATA[$str" . ( ( substr( $str, -1 ) == ']') ? ' ' : '' ) . ']]>';

		return $str;
	}
}

if ( ! function_exists( 'trilisting_site_url' ) ) {
	/**
	 * Return the URL of the site
	 *
	 * @return string Site URL.
	 */
	function trilisting_site_url() {
		if ( is_multisite() ) {
			return network_home_url();
		}

		return get_site_url();
	}
}

if ( ! function_exists( 'trilisting_tag_description' ) ) {
	/**
	 * Output a tag_description XML tag from a given tag object
	 *
	 * @param object $tag Tag Object
	 */
	function trilisting_tag_description( $tag ) {
		if ( empty( $tag->description ) ) {
			return;
		}

		echo '<wp:tag_description>' . trilisting_cdata( $tag->description ) . '</wp:tag_description>';
	}
}

if ( ! function_exists( 'trilisting_term_name' ) ) {
	/**
	 * Output a term_name XML tag from a given term object
	 *
	 * @param object $term Term Object
	 */
	function trilisting_term_name( $term ) {
		if ( empty( $term->name ) ) {
			return;
		}

		echo '<wp:term_name>' . trilisting_cdata( $term->name ) . '</wp:term_name>';
	}
}

if ( ! function_exists( 'trilisting_term_description' ) ) {
	/**
	 * Output a term_description XML tag from a given term object
	 *
	 * @param object $term Term Object
	 */
	function trilisting_term_description( $term ) {
		if ( empty( $term->description ) ) {
			return;
		}

		echo '<wp:term_description>' . trilisting_cdata( $term->description ) . '</wp:term_description>';
	}
}

if ( ! function_exists( 'trilisting_authors_list' ) ) {
	/**
	 * Output list of authors with posts
	 */
	function trilisting_authors_list() {
		global $wpdb;

		$authors = [];
		$results = $wpdb->get_results( "SELECT DISTINCT post_author FROM $wpdb->posts" );
		foreach ( (array) $results as $result ) {
			$authors[] = get_userdata( $result->post_author );
		}

		$authors = array_filter( $authors );

		foreach ( $authors as $author ) {
			echo "\t<wp:author>";
			echo '<wp:author_id>' . $author->ID . '</wp:author_id>';
			echo '<wp:author_login>' . $author->user_login . '</wp:author_login>';
			echo '<wp:author_email>' . $author->user_email . '</wp:author_email>';
			echo '<wp:author_display_name>' . trilisting_cdata( $author->display_name ) . '</wp:author_display_name>';
			echo '<wp:author_first_name>' . trilisting_cdata( $author->user_firstname ) . '</wp:author_first_name>';
			echo '<wp:author_last_name>' . trilisting_cdata( $author->user_lastname ) . '</wp:author_last_name>';
			echo "</wp:author>\n";
		}
	}
}
