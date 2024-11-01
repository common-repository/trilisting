<?php

namespace TRILISTING\Walker;
use Walker;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Core class used to create an HTML dropdown list of Categories.
 *
 * @since 1.0.0
 *
 * @see Walker
 */
class Trilisting_Taxonomy_Dropdown_Walker extends Walker {
	/**
	 * What the class handles.
	 *
	 * @since 1.0.0
	 * @var string
	 *
	 * @see Walker::$tree_type
	 */
	public $tree_type = 'category';
	/**
	 * Database fields to use.
	 *
	 * @since 1.0.0
	 * @todo Decouple this
	 * @var array
	 *
	 * @see Walker::$db_fields
	 */
	public $db_fields = [
		'parent' => 'parent',
		'id'     => 'term_id',
	];
	/**
	 * Starts the element output.
	 *
	 * @since 1.0.0
	 *
	 * @see Walker::start_el()
	 *
	 * @param string $output   Used to append additional content (passed by reference).
	 * @param object $category Category data object.
	 * @param int    $depth    Depth of category. Used for padding.
	 * @param array  $args     Uses 'selected', 'show_count', and 'value_field' keys, if they exist.
	 *                         See wp_dropdown_categories().
	 * @param int    $id       Optional. ID of the current category. Default 0 (unused).
	 */
	public function start_el( &$output, $category, $depth = 0, $args = [], $id = 0 ) {
		$pad = str_repeat( '&nbsp;', $depth * 3 );
		/** This filter is documented in wp-includes/category-template.php */
		$cat_name    = apply_filters( 'list_cats', $category->name, $category );
		$value_field = 'slug';

		$output .= "\t<option class=\"level-$depth\" value=\"" . esc_attr( $category->{$value_field} ) . '"';
		// Type-juggling causes false matches, so we force everything to a string.
		if ( (string) $category->{$value_field} === (string) $args['selected'] ) {
			$output .= ' selected="selected"';
		}

		$output .= '>';
		$output .= $pad . $cat_name;
		if ( $args['show_count'] ) {
			$output .= '&nbsp;&nbsp;(' . number_format_i18n( $category->count ) . ')';
		}

		$output .= "</option>\n";
	}
	
}
