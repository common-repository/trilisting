<?php
/**
 * Helper functions for theme-options.php
 *
 * @since 1.0.0
 */

namespace TRILISTING;

/**
 * Widgets fields options.
 * 
 * @param array $default
 * @param bool $author
 * @param bool $comments
 * @param bool $liked
 * @param bool $date
 * @return array
 */
function trilisting_get_widget_meta_opts( $fields, $default = false, $post_type = '' ) {
	$options = [];

	if ( ! empty( $fields ) && isset( $fields[ $post_type ] ) ) {
		foreach ( $fields[ $post_type ] as $key => $field ) {
			if (
				'text'   == $field['type'] ||
				'number' == $field['type'] ||
				'email'  == $field['type']
			) {
				$options[ esc_attr( $field['name'] ) ] = esc_html( $field['label'] );
			}
		}
	} // End if

	$options['author']   = esc_html__( 'Author', 'trilisting' );
	$options['comments'] = esc_html__( 'Number Reviews/Comments', 'trilisting' );
	$options['category'] = esc_html__( 'Category', 'trilisting' );
	$options['date']     = esc_html__( 'Date', 'trilisting' );
	$options['reviews']  = esc_html__( 'Reviews', 'trilisting' );

	if ( $default ) {
		foreach ( $options as $key => $option ) {
			$options[ $key ] = 1;
		}
	}

	$options = apply_filters( 'trilisting/widgets/options/fields_meta', $options );

	return $options;
}


/**
 * Post fields options.
 * 
 * @param string $fields
 * @param bool $default
 * @param string $post_type
 * @return array
 */
function trilisting_get_post_options( $fields = '', $default = false, $post_type = '' ) {
	$options = [];

	if ( ! empty( $fields ) && ! empty( $post_type ) && isset( $fields[ $post_type ] ) ) {
		foreach ( $fields[ $post_type ] as $key => $field ) {
			$options[ esc_attr( $field['name'] ) ] = esc_html( $field['label'] );
		}
	}

	if ( empty( $options ) ) {
		$options['acf_none'] = esc_html__( 'None fields', 'trilisting' );
	}

	$options = apply_filters( 'trilisting/post/options/fields', $options );

	return $options;
}

/**
 * List widgets.
 * 
 * @param bool $inherit
 * @param bool $none
 * @return mixed
 */
function trilisting_get_widgets( $inherit = false, $none = false ) {
	if ( $inherit ) {
		$widgets['inherit'] = esc_html__( 'Inherit', 'trilisting' );
	}

	if ( $none ) {
		$widgets['none'] = esc_html__( 'None', 'trilisting' );
	}

	$widgets['widget_standard_1'] = esc_html__( 'Standard', 'trilisting' );
	$widgets['widget_blog_1']     = esc_html__( 'Blog', 'trilisting' );

	$widgets = apply_filters( 'trilisting/widgets/options/type', $widgets );

	return $widgets;
}

/**
 * Pagination.
 * 
 * @return mixed
 */
function trilisting_get_pagination_type_widgets() {
	$pagination['numeric']   = esc_html__( 'Numeric', 'trilisting' );
	$pagination['load_more'] = esc_html__( 'Load more', 'trilisting' );

	$pagination = apply_filters( 'trilisting/pagination/options/type', $pagination );

	return $pagination;
}
