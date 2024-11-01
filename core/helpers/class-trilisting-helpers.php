<?php

namespace TRILISTING;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Trilisting_Helpers {
	/**
	 * Get wordpress data.
	 * 
	 * @static
	 * 
	 * @since 1.0.0
	 * @param bool $type
	 * @param array $args
	 * @return array|string
	 */
	public static function get_wordpress_data( $type = false, $args = [] ) {
		$data = "";
	
		if ( empty ( $data ) && ! empty ( $type ) ) {
			/**
			 * Use data from Wordpress to populate options array
			 * */
			if ( ! empty ( $type ) && empty ( $data ) ) {
				if ( empty ( $args ) ) {
					$args = [];
				}
	
				$data = [];
				$args = wp_parse_args( $args, [] );
	
				if ( "categories" == $type  || "category" == $type ) {
					$cats = get_categories( $args );
					if ( ! empty ( $cats ) ) {
						foreach ( $cats as $cat ) {
							$data[ $cat->term_id ] = $cat->name;
						}
						// End foreach
					} // End if
				} elseif ( "menus" == $type || "menu" == $type ) {
					$menus = wp_get_nav_menus( $args );
					if ( ! empty ( $menus ) ) {
						foreach ( $menus as $item ) {
							$data[ $item->term_id ] = $item->name;
						}
						// End foreach
					}
					// End if
				} elseif ( "pages" == $type|| "page" == $type ) {
					if ( ! isset ( $args['posts_per_page'] ) ) {
						$args['posts_per_page'] = 20;
					}
					$pages = get_pages( $args );
					if ( ! empty ( $pages ) ) {
						foreach ( $pages as $page ) {
							$data[ $page->ID ] = $page->post_title;
						}
						// End foreach
					}
					// End if
				} elseif ( "terms" == $type || "term" == $type ) {
					$taxonomies = $args['taxonomies'];
					unset ( $args['taxonomies'] );
					$terms = get_terms( $taxonomies, $args ); // this will get nothing
					if ( ! empty ( $terms ) && ! is_a( $terms, 'WP_Error' ) ) {
						foreach ( $terms as $term ) {
							$data[ $term->term_id ] = $term->name;
						}
						// End foreach
					} // End if
				} elseif ( "taxonomy" == $type || "taxonomies" == $type ) {
					$taxonomies = get_taxonomies( $args );
					if ( ! empty ( $taxonomies ) ) {
						foreach ( $taxonomies as $key => $taxonomy ) {
							$data[ $key ] = $taxonomy;
						}
						// End foreach
					} // End if
				} elseif ( "posts" == $type || "post" == $type ) {
					$posts = get_posts( $args );
					if ( ! empty ( $posts ) ) {
						foreach ( $posts as $post ) {
							$data[ $post->ID ] = $post->post_title;
						}
						// End foreach
					}
					// End if
				} elseif ( "post_type" == $type || "post_types" == $type ) {
					global $wp_post_types;
	
					$defaults = [
						'public'              => true,
						'exclude_from_search' => false,
					];
					$args       = wp_parse_args( $args, $defaults );
					$output     = 'names';
					$operator   = 'and';
					$post_types = get_post_types( $args, $output, $operator );
	
					ksort( $post_types );
	
					foreach ( $post_types as $name => $title ) {
						if ( isset ( $wp_post_types[ $name ]->labels->menu_name ) ) {
							$data[ $name ] = $wp_post_types[ $name ]->labels->menu_name;
						} else {
							$data[ $name ] = ucfirst( $name );
						}
					}
				} elseif ( "tags" == $type || "tag" == $type ) {
					$tags = get_tags( $args );
					if ( ! empty ( $tags ) ) {
						foreach ( $tags as $tag ) {
							$data[ $tag->term_id ] = $tag->name;
						} // End foreach
					}
					// End if
				} elseif ( "menu_location" == $type || "menu_locations" == $type ) {
					global $_wp_registered_nav_menus;
	
					foreach ( $_wp_registered_nav_menus as $k => $v ) {
						$data[ $k ] = $v;
					}
				} else if ( "image_size" == $type || "image_sizes" == $type ) {
					global $_wp_additional_image_sizes;
	
					foreach ( $_wp_additional_image_sizes as $size_name => $size_attrs ) {
						$data[ $size_name ] = $size_name . ' - ' . $size_attrs['width'] . ' x ' . $size_attrs['height'];
					}
				} elseif ( "roles" == $type ) {
					/** @global WP_Roles $wp_roles */
					global $wp_roles;
	
					$data = $wp_roles->get_names();
				} elseif ( "sidebars" == $type || "sidebar" == $type ) {
					/** @global array $wp_registered_sidebars */
					global $wp_registered_sidebars;
	
					foreach ( $wp_registered_sidebars as $key => $value ) {
						$data[ $key ] = $value['name'];
					}
				} elseif ( "capabilities" == $type ) {
					/** @global WP_Roles $wp_roles */
					global $wp_roles;
	
					foreach ( $wp_roles->roles as $argsole ) {
						foreach ( $argsole['capabilities'] as $key => $cap ) {
							$data[ $key ] = ucwords( str_replace( '_', ' ', $key ) );
						}
					}
				} elseif ( "callback" == $type ) {
					if ( ! is_array( $args ) ) {
						$args = [ $args ];
					}
					$data = call_user_func( $args[0] );
				} elseif ( "users" == $type || "users" == $type ) {
					$users = get_users( $args );
					if ( ! empty ( $users ) ) {
						foreach ( $users as $user ) {
							$data[ $user->ID ] = $user->display_name;
						} // End foreach
					} // End if
				} // End if
			} // End if
		} // End if

		return $data;
	}

	/**
	 * Gets and includes template files.
	 *
	 * @static
	 * 
	 * @since 1.0.0
	 * @param mixed  $template_name
	 * @param array  $args (default: array())
	 */
	public static function get_manager_template( $template_name, $args = [], $include_theme = false ) {
		if ( $args && is_array( $args ) ) {
			extract( $args );
		}

		if ( true === $include_theme ) {
			$template_name = self::locate_template( $template_name );
		}

		if ( ! file_exists( $template_name ) ) {
			return;
		}

		include( $template_name );
	}

	/**
	 * Locates a template and return the path for inclusion.
	 *
	 * This is the load order:
	 *
	 *		yourtheme		/	$template_path	/	$template_name
	*		yourtheme		/	$template_name
	*		$default_path	/	$template_name
	*
	* @static
	* 
	* @since 1.0.0
	* @param string      $template_name
	* @param string      $template_path (default: 'trilisting-templates')
	* @param string|bool $default_path (default: '') False to not load a default
	* @return string
	*/
	public static function locate_template( $template_name, $template_path = 'trilisting-templates', $default_path = '' ) {
		// Look within passed path within the theme - this is priority
		$template = locate_template(
			[
				trailingslashit( $template_path ) . $template_name,
				$template_name
			]
		);

		// Get default template
		if ( ! $template && false !== $default_path ) {
			$default_path = $default_path ? $default_path : TRILISTING_PATH_TEMPLATES . '/';
			if ( file_exists( trailingslashit( $default_path ) . $template_name ) ) {
				$template = trailingslashit( $default_path ) . $template_name;
			}
		}

		// Return what we found
		return apply_filters( 'trilisting_locate_template', $template, $template_name, $template_path );
	}

	/**
	 * Gets template part (for templates in loops).
	 *
	 * @static
	 * 
	 * @since 1.0.0
	 * @param string      $slug
	 * @param string      $name (default: '')
	 * @param string      $template_path (default: 'trilisting-templates')
	 * @param string|bool $default_path (default: '') False to not load a default
	 */
	public static function get_template_part( $slug, $name = '', $template_path = 'trilisting-templates', $default_path = '' ) {
		$template = '';

		if ( $name ) {
			$template = self::locate_template( "{$slug}-{$name}.tpl.php", $template_path, $default_path );
		}

		// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/trilisting-templates/slug.php
		if ( ! $template ) {
			$template = self::locate_template( "{$slug}.tpl.php", $template_path, $default_path );
		}

		if ( $template ) {
			load_template( $template, false );
		}
	}

	/**
	 * Wrapper do_action.
	 * 
	 * @static
	 * 
	 * @since 1.0.0
	 * @param $action_name
	 * @return string
	 */
	public static function do_action( $action_name, $atts = '' ) {
		ob_start();
		do_action( $action_name, $atts );
		return ob_get_clean();
	}

	/**
	 * Get a list of custom taxonomies.
	 * 
	 * @static
	 * 
	 * @since 1.0.0
	 * @param string $terms_name
	 * @return string
	 */
	public static function render_taxonomy( $terms_name = '', $post_id = '', $field_name = '' ) {
		$output = '';

		if ( empty( $post_id ) ) {
			global $post;
			$post_id = $post->ID;
		}

		$html = '';
		$custom_field_val = '';
		if ( empty( $field_name ) && is_single() ) {
			$html = apply_filters( 'trilisting/single/taxonomy/html_before', '' );
		}
		$post_type = get_post_type( $post_id );
		$default_post_types = get_post_types(
			[ '_builtin' => true, ]
		);
		$default_post_types = apply_filters( 'trilisting/helpers/taxonomy/args', $default_post_types );

		if ( ! empty( $terms_name ) ) {
			$terms = wp_get_post_terms( $post_id, $terms_name );
			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					if ( ! empty( $term->term_id ) && ! empty( $term->name ) ) {
						$custom_fields = '';
						if ( ! empty( $field_name ) && function_exists( 'get_field_objects' ) ) {

							$fields_obj = get_field_objects( $term->taxonomy . '_' . $term->term_id );
							if ( ! empty( $fields_obj ) && isset( $fields_obj[ $field_name ] ) ) {
								$field_value = $fields_obj[ $field_name ];

								switch ( $field_value['type'] ) {
									case 'image' :
										$custom_field_val = \TRILISTING\Trilisting_Acf_Fields::image_field( $field_value, 'img' );
										break;
									case 'font-awesome' :
										$custom_field_val = \TRILISTING\Trilisting_Acf_Fields::font_awesome_field( $field_value );
										break;
								}

								if ( ! empty( $custom_field_val ) ) {
									$custom_fields .= '<div class="trilisting-tax-img-wrap">';
									$custom_fields .= $custom_field_val;
									$custom_fields .= '</div>';
								}
							}
						}

						$output .= '<a class="trilisting-term-link" href="' . esc_url( get_category_link( $term->term_id ) ) . '">' . $custom_fields . $html . esc_html( $term->name ) . '</a>';
					}
				} // End foreach
			}
		} else {
			if ( array_key_exists( $post_type, $default_post_types ) ) {
				$categories = get_the_category( $post_id );
				$output .= '<div class="trilisting-term">';

				foreach ( $categories as $cat_id => $cat ) {
					$output .= '<a href="' . esc_url( get_category_link( $cat->term_id ) ) . '">' . $html . esc_html( $cat->cat_name ) . '</a>';
				}

				$output .= '</div>';
			} else {
				// custom post type
				global $wp_query;
				$query_obj = $wp_query->get_queried_object();
				$taxs_name = get_object_taxonomies( $post_type, 'objects' );

				if ( ! empty( $taxs_name ) ) {
					foreach ( $taxs_name as $obj ) {
						$terms = get_the_terms( $query_obj->ID, $obj->name );
						if ( ! empty( $terms ) ) {
							$output	.= '<div class="trilisting-term ' . esc_attr( $obj->name ) . '">';
							$output .= '<span class="trilisting-term-title">' . esc_attr( $obj->label ) . '</span>';
						
							foreach ( $terms as $term ) {
								if ( ! empty( $term->term_id ) && ! empty( $term->name ) ) {

									if ( function_exists( 'get_field_objects' ) ) {
										$custom_field_val = '';
										$fields_obj = get_field_objects( $term->taxonomy . '_' . $term->term_id );
										if ( ! empty( $fields_obj ) ) {
											foreach ( $fields_obj as $field_value ) {
												switch ( $field_value['type'] ) {
													case 'font-awesome' :
														$custom_field_val = \TRILISTING\Trilisting_Acf_Fields::font_awesome_field( $field_value );
														break;
												}
											}
										}
									}

									$output .= '<a class="trilisting-term-link" href="' . esc_url( get_category_link( $term->term_id ) ) . '">' . $custom_field_val . $html . esc_html( $term->name ) . '</a>';
								}
							}
							
							$output .= '</div>';
						}
					} // End foreach
				}
			}
		} // End if

		return $output;
	}
}
