<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Trilisting_Menu_Walker' ) ) {
	/**
	 * Walker class menu
	 *
	 * @since  1.0.0
	 */
	class Trilisting_Menu_Walker extends Walker_Nav_menu {
		/**
		 * @param $output
		 * @param int $depth
		 * @param array $args
		 */
		function start_lvl( &$output, $depth = 0, $args = [] ) {
			$sub = $depth + 1;
			$output .= "\n<ul class='sub-menu-$sub'>\n";
		}

		/**
		 * @param $output
		 * @param $item
		 * @param int $depth
		 * @param array $args
		 * @param int $id
		 */
		function start_el( &$output, $item, $depth = 0, $args = [], $id = 0 ) {
			$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

			$class_names = $value = '';
			$classes     = empty( $item->classes ) ? [] : (array) $item->classes;
			$parent      = false;
			$all_classes = array_flip( $classes );
			if ( key_exists( 'menu-item-has-children', $all_classes ) ) {
				$classes[0] = 'parent';
				$parent     = true;
			}

			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
			$class_names = ' class="' . esc_attr( $class_names ) . '"';
	
			$output .= $indent . '<li' . $value . $class_names . '>';
	
			$attributes = ! empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) . '"' : '';
			$attributes .= ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
			$attributes .= ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : '';
			$attributes .= ! empty( $item->url ) ? ' href="' . esc_attr( $item->url ) . '"' : '';

			$append = $prepend = "";

			$item_output = $args->before;
			$item_output .= '<a class="trilisting-menu-link"' . $attributes . '>';
			$item_output .= $args->link_before . $prepend . apply_filters( 'the_title', $item->title, $item->ID ) . $append;

			$item_output .= '</a>';
			$item_output .= $args->after;

			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
	}
} // End if
