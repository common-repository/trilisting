<?php

namespace TRILISTING\Walker;
use Walker_Category;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Category API: Trilisting_Search_Walker class
 */
class Trilisting_Search_Walker extends Walker_Category {

	private $type                 = '';
	private $defaults             = [];

	//multiselect
	private $multidepth           = 0;
	private $multilastid          = 0;
	private $multilastdepthchange = 0;

	public function __construct( $type = 'checkbox', $defaults = [] ) {
		$this->type = $type;
		$this->defaults = $defaults;
	}

	public function display_element( $element, &$children_elements, $max_depth, $depth = 0, $args, &$output ) {
		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}

	/**
	 * Starts the element output.
	 *
	 * @since 1.0.0
	 *
	 * @see Walker::start_el()
	 *
	 * @param string $output   Used to append additional content (passed by reference).
	 * @param object $category Category data object.
	 * @param int    $depth    Optional. Depth of category in reference to parents. Default 0.
	 * @param array  $args     Optional. An array of arguments. See wp_list_categories(). Default empty array.
	 * @param int    $id       Optional. ID of the current category. Default 0.
	 */
	public function start_el( &$output, $category, $depth = 0, $args = [], $id = 0 ) {
		if ( 'list' == $this->type ) {
			extract( $args );

			$cat_name	= esc_attr( $prefix_name );
			$cat_name	= apply_filters( 'list_cats', $cat_name, $category );
			$link		= '<a href="' . esc_url( get_term_link( $category ) ) . '" ';

			if ( 0 == $use_desc_for_title || empty( $category->description ) ) {
				$link .= 'title="' . esc_attr( sprintf( __( 'View all posts filed under %s' ), $cat_name ) ) . '"';
			} else {
				$link .= 'title="' . esc_attr( strip_tags( apply_filters( 'category_description', $category->description, $category ) ) ) . '"';
			}

			$link .= '>';
			$link .= $cat_name . '</a>';

			if ( ! empty( $feed_image ) || ! empty( $feed ) ) {
				$link .= ' ';

				if ( empty( $feed_image ) ) {
					$link .= '( ';
				}

				$link .= '<a class="trilisting-cat" href="' . esc_url( get_term_feed_link( $category->term_id, $category->taxonomy, $feed_type ) ) . '"';

				if ( empty( $feed ) ) {
					$alt = ' alt="' . sprintf( __( 'Feed for all posts filed under %s' ), $cat_name ) . '"';
				} else {
					$title = ' title="' . $feed . '"';
					$alt = ' alt="' . $feed . '"';
					$name = $feed;
					$link .= $title;
				}

				$link .= '>';

				if ( empty( $feed_image ) ) {
					$link .= $name;
				} else {
					$link .= "<img src='$feed_image'$alt$title" . ' />';
				}

				$link .= '</a>';

				if ( empty( $feed_image ) ) {
					$link .= ' )';
				}

			} // End if

			if ( ! empty( $show_count ) ) {
				$link .= ' ( ' . intval( $category->count ) . ' )';
			}

			if ( 'list' == $args['style'] ) {
				$output .= "\t<li";
				$class = 'cat-item cat-item-' . $category->term_id;
				if ( ! empty( $current_category ) ) {
					$_current_category = get_term( $current_category, $category->taxonomy );
					if ( $category->term_id == $current_category ) {
						$class .= ' current-cat';
					} elseif ( $category->term_id == $_current_category->parent ) {
						$class .= ' current-cat-parent';
					}

				}
				$output .= ' class="' . $class . '"';
				$output .= ">$link\n";
			} else {
				$output .= "\t$link<br />\n";
			}
		} else if ( ( "checkbox" == $this->type ) || ( "radio" == $this->type ) ) {
			extract( $args );

			$cat_name = esc_attr( $prefix_name );
			$cat_name = esc_attr( $category->name );
			$cat_id   = esc_attr( $category->term_id );
			$cat_name = apply_filters( 'list_cats', $cat_name, $category );

			//check a default has been set
			$checked = "";

			if ( $defaults ) {
				if ( ! empty( $this->defaults ) && ( is_array( $defaults ) ) ) {
					foreach ( $defaults as $defaultid ) {
						if ( ( $defaultid == $cat_id ) ) {
							$checked = ' checked="checked"';
						}
					}
				}
			}

			if ( isset( $_GET[ $prefix_name ] ) ) {
				$count_select = '';
				$select_field = $_GET[ $prefix_name ];
				if ( is_array( $select_field ) ) {
					$count_select = count( $select_field );
				}

				if ( ( $count_select > 0 ) && ( is_array( $select_field ) ) ) {
					foreach ( $select_field as $select_tid ) {
						if ( ( $select_tid == $cat_id ) ) {
							$checked = ' checked="checked"';
						}
					}
				}
			}

			$link = "<label class='trilisting-label'><input class='trilisting-checkbox' type='" . $this->type . "' name='" . $prefix_name . "[]' value='" . $cat_id . "'" . $checked . " /> " . $cat_name;

			if ( ! empty( $show_count ) ) {
				$link .= ' (' . intval($category->count) . ')';
			}

			$link .= "</label>";

			if ( 'list' == $args['style'] ) {
				$output .= "\t<li";
				$class = 'cat-item cat-item-' . $category->term_id;
				if ( ! empty($current_category) ) {
					$_current_category = get_term( $current_category, $category->taxonomy );
					if ( $category->term_id == $current_category ) {
						$class .=  ' current-cat';
					} elseif ( $category->term_id == $_current_category->parent ) {
						$class .=  ' current-cat-parent';
					}
				}
				$output .=  ' class="' . $class . '"';
				$output .= ">$link\n";
			} else {
				$output .= "\t$link<br />\n";
			}
		} // End if
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @since 1.0.0
	 *
	 * @see Walker::end_el()
	 *
	 * @param string $output Used to append additional content (passed by reference).
	 * @param object $page   Not used.
	 * @param int    $depth  Optional. Depth of category. Not used.
	 * @param array  $args   Optional. An array of arguments. Only uses 'list' for whether should append
	 *                       to output. See wp_list_categories(). Default empty array.
	 */
	public function end_el( &$output, $page, $depth = 0, $args = [] ) {
		if ( 'list' == $this->type ) {
			if ( 'list' != $args['style'] ) {
				return;
			}

			$output .= "</li>\n";
		} elseif (  ( "checkbox" == $this->type ) || ( "radio" == $this->type ) ) {
			if ( 'list' != $args['style'] ) {
				return;
			}

			$output .= "</li>\n";
		}
	}

	/**
	 * Starts the list before the elements are added.
	 *
	 * @since 1.0.0
	 *
	 * @see Walker::start_lvl()
	 *
	 * @param string $output Used to append additional content. Passed by reference.
	 * @param int    $depth  Optional. Depth of category. Used for tab indentation. Default 0.
	 * @param array  $args   Optional. An array of arguments. Will only append content if style argument
	 *                       value is 'list'. See wp_list_categories(). Default empty array.
	 */
	public function start_lvl( &$output, $depth = 0, $args = [] ) {
		if ( 'list' == $this->type ) {
			if ( 'list' != $args['style'] ) {
				return;
			}

			$indent = str_repeat( "\t", $depth );
			$output .= "$indent<ul class='children'>\n";
		} elseif (  ( "checkbox" == $this->type ) || ( "radio" == $this->type ) ) {
			if ( 'list' != $args['style'] ) {
				return;
			}

			$indent = str_repeat("\t", $depth);
			$output .= "$indent<ul class='children'>\n";
		}
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @since 1.0.0
	 *
	 * @see Walker::end_lvl()
	 *
	 * @param string $output Used to append additional content. Passed by reference.
	 * @param int    $depth  Optional. Depth of category. Used for tab indentation. Default 0.
	 * @param array  $args   Optional. An array of arguments. Will only append content if style argument
	 *                       value is 'list'. See wp_list_categories(). Default empty array.
	 */
	public function end_lvl( &$output, $depth = 0, $args = [] ) {
		if ( 'list' == $this->type ) {
			if ( 'list' != $args['style'] ) {
				return;
			}

			$indent = str_repeat( "\t", $depth );
			$output .= "$indent</ul>\n";
		} elseif ( ( "checkbox" == $this->type ) || ( "radio" == $this->type ) ) {
			if ( 'list' != $args['style'] ) {
				return;
			}

			$indent = str_repeat("\t", $depth);
			$output .= "$indent</ul>\n";
		}
	}
}
