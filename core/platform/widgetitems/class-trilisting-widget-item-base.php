<?php

namespace TRILISTING;

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgetitems/class-trilisting-acf-fileds.php';

/**
 * Abstract class render widgets.
 */
abstract class Trilisting_Widgets_Item_Base {
	var $post;
	var $post_title;
	var $post_link;

	protected $current_post_type;
	protected $post_meta     = [];
	protected $post_thumb    = null;
	protected $post_thumb_id = null;
	protected $platform      = null;
	protected $options       = [];
	protected $render_params = [];
	protected $acf_fields    = [];

	/**
	 * Trilisting_Widgets_Item_Base constructor.
	 * 
	 * @since 1.0.0
	 * @param $post
	 * @param array $options
	 * @param array $render_params
	 * @throws ErrorException
	 */
	public function __construct( $post, $options = [], $render_params = [] ) {
		if ( 'object' != gettype( $post ) or 'WP_Post' != get_class( $post ) ) {
			throw new ErrorException( 'ac_widget_item_base: ' . get_Class( $this ) . '($post): $post is not WP_Post' );
		}
				
		$this->platform   = \Trilisting_Widgets_Platform::instance();
		$this->post       = $post;
		$this->post_title = get_the_title( $post->ID );
		$this->post_link  = esc_url( get_permalink( $post->ID ) );

		if ( function_exists( 'get_field_objects' ) ) {
			$this->acf_fields = get_field_objects( $post->ID );
		}

		if ( has_post_thumbnail( $this->post->ID ) ) {
			$tmp = get_post_thumbnail_id( $this->post->ID );
			if ( ! empty( $tmp ) ) {
				$this->post_thumb_id = $tmp;
			}
		}

		$this->options           = $options;
		$this->current_post_type = get_post_type( $this->post->ID );

		// default render params
		$this->render_params = [
			'item_type'  => 'a',
			'img_layout' => '',
		];
		$this->render_params = array_merge( $this->render_params, $render_params );
	}

	/**
	 * @since 1.0.0
	 * @param $file
	 * @param $context
	 * @param bool $output
	 * @return string
	 */
	protected function render_template($file, $context, $output = true ) {
		if ( $output ) {
			ob_start();
		}

		$template_file = locate_template( "trilisting-templates/listings/listing-{$file}.php" );

		if ( ! file_exists( $template_file ) ) {
			$template_file = plugin_dir_path( dirname( __FILE__ ) ) . "widgetitems/listings/listing-{$file}.php";
		}

		if ( ! $template_file || ! file_exists( $template_file ) ) {
			trigger_error( sprintf( 'Error locating %s for inclusion', $file ), E_USER_ERROR );
		}

		extract( $context, EXTR_SKIP );
		require( $template_file );
		if ( $output ) {
			return ob_get_clean();
		}
	}

	/**
	 * Render widgets.
	 * 
	 * @since 1.0.0
	 * @return string
	 * @throws ErrorException
	 */
	public function render() {
		if ( empty( $this->render_params ) ) {
			throw new ErrorException( 'Error: no rendering params for item "' . get_class( $this ) . '"' );
		}

		return $this->render_template( $this->render_params['item_type'], $this->render_params );
	}

	public function render_fields() {
		$output = '';

		if ( ! empty( $this->acf_fields ) ) {
			foreach ( $this->acf_fields as $key => $field ) {
				$method_field = $field['type'] . '_field';
				if (
					1 == $this->get_inner_option( 'meta_' . $this->current_post_type . '.' . $field['name'] ) &&
					(
					  'text'   == $field['type'] ||
					  'number' == $field['type'] ||
					  'email'  == $field['type']
					)
				) {
					if ( method_exists( 'TRILISTING\Trilisting_Acf_Fields', $method_field ) ) {
						$output .= '<div class="trilisting-acf-field trilisting-acf-field-' . esc_attr( $key ) . '">';
						$output .= Trilisting_Acf_Fields::$method_field( $field );
						$output .= '</div>';
					}
				} elseif ( ! is_single() && ( 'google_map' == $field['type'] ) ) {
					$output .= '<div class="trilisting-acf-field trilisting-acf-field-' . esc_attr( $key ) . '">';
					$output .= Trilisting_Acf_Fields::google_map_field( $field, $this->post->ID );
					$output .= '</div>';
				}
			}
		} // End if

		return $output;
	}

	/**
	 * @param $name
	 * @param string $default
	 * @return mixed|string
	 */
	protected function get_inner_option( $name, $default = '' ) {
		$result = $default;
		if ( isset( $this->options[ $name ] ) ) {
			$result = $this->options[ $name ];
		}

		return $result;
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function render_author() {
		return apply_filters( 'trilisting/item/author' , '<a class="trilisting-meta-author" href="' . esc_url( get_author_posts_url( $this->post->post_author ) ) . '">' . esc_html( get_the_author_meta( 'display_name', $this->post->post_author ) ) . '</a>', $this->post );
	}

	/**
	 * @since 1.0.0
	 * @param bool $show_icons
	 * @return string
	 */
	public function render_date() {
		return '<time class="trilisting-meta-date" datetime="' . esc_attr( date( DATE_W3C, get_the_time( 'U', $this->post->ID ) ) ) . '">' . esc_html( get_the_time( get_option( 'date_format' ), $this->post->ID ) ) . '</time>';
	}

	public function is_sticky_class() {
		if ( is_sticky( $this->post->ID ) ) {
			return 'trilisting-sticky-listing';
		}

		return;
	}

	public function is_sticky() {
		if ( is_sticky( $this->post->ID ) ) {
			return apply_filters( 'trilisting/widget/sticky_icon', '<i class="fas fa-certificate"></i>' );
		}

		return;
	}

	/**
	 * @since 1.0.0
	 * @param bool $show_text
	 * @param bool $show_icons
	 * @return string
	 */
	public function render_comments( $show_text = true, $icons = '' ) {
		$output = '';
		$comments_number = '';

		$comments_number = get_comments_number( $this->post->ID );
		if ( $show_text ) {
			if ( trilisting_check_insert_rating( $this->current_post_type ) ) {
				$comments_number = esc_html( $comments_number . _n( ' review', ' reviews', $comments_number, 'trilisting' ) );
			} else {
				$comments_number = esc_html( $comments_number . _n( ' comment', ' comments', $comments_number, 'trilisting' ) );
			}
		}

		$output .= '<a class="trilisting-meta-comments" href="' . esc_url( get_comments_link( $this->post->ID ) ) . '">';
		$output .= $icons . esc_html( $comments_number );
		$output .= '</a>';

		return $output;
	}

	/**
	 * Get first image for gallery post.
	 * 
	 * @since 1.0.0
	 * @param $img_layout
	 * @return string
	 */
	public function get_image_post_gallery( $img_layout ) {
		$output = '';
		$images = get_post_meta( $this->post->ID, '_format_gallery_images', true );

		if ( $images ) {
			$the_image = wp_get_attachment_image_src( $images[0], $img_layout );
			$output .= '<div class="trilisting-item-bg-img" data-url="' . esc_url( $the_image[0] ) . '" style="background-image: url(' . esc_url( $the_image[0] ) . ')"></div>';
		}

		return $output;
	}

	/**
	 * @since 1.0.0
	 * @param $img_layout
	 * @param $is_link
	 * @param $data_url
	 * @return string
	 */
	public function render_image( $img_layout, $is_link, $data_url ) {
		$output = $output_data_url = $post_img = '';
		$post_type        = get_post_format( $this->post->ID );
		$post_gallery_img = $this->get_image_post_gallery( $img_layout );

		if ( 'none' != $img_layout ) {
			if ( $data_url ) {
				$output_data_url = 'data-url="' . esc_url( $this->platform->get_image( $this->post->ID, $img_layout ) ) . '"'; 
			}

			if ( $is_link ) {
				$output .= '<a class="trilisting-item-link" href="' . esc_url( $this->post_link ) . '" aria-label="Listing-link">';
			}

			if ( 'gallery' == $post_type && ! empty( $post_gallery_img ) ) {
				$post_img .= $post_gallery_img;
			} elseif ( ! empty( $this->post_thumb_id ) ) {
				$post_img .= '<div class="trilisting-item-bg-img" ' . $output_data_url . ' style="background-image: url(' . esc_url( $this->platform->get_image( $this->post->ID, $img_layout ) ) . ')"></div>';
			}  else {
				$post_img .= $this->platform->get_default_placeholder( $img_layout, $this->post_title, $data_url );
			}

			if ( empty( $post_img ) ) {
				return;
			}

			$output .= $post_img;
			if ( $is_link ) {
				$output .= '</a>';
			}

			if ( is_sticky( $this->post->ID ) ) {
				$output .= do_action( 'trilisting/widgets/sticky_icons_html' );
			}
		} // End if

		return $output;
	}

	/**
	 * @since 1.0.0
	 * @param string $length
	 * @return string
	 */
	public function render_title( $length = '' ) {
		$output = '';
		$output .= '<h3 class="trilisting-item-title"><a class="trilisting-item-title-link" href="' . esc_url( $this->post_link ) . '" aria-label="Listing-link">';

		$title = $this->post_title;
		if ( empty( $title ) ) {
			$title = $this->render_date();
		}

		if ( '' != $length ) {
			$output .= $this->platform->excerpt( $title, $length );
		} else {
			$length = $this->get_inner_option( 'title_length' );
			$output .= $this->platform->excerpt( $title, $length );
		}

		$output .= '</a></h3>';

		return $output;
	}

	/**
	 * @since 1.0.0
	 * @param bool $filter_content
	 * @param string $custom_limit
	 * @return mixed|string
	 */
	public function render_post_content( $custom_limit = '' ) {
		$output = '';

		$content = $this->post->post_content;
		if ( '' != $this->post->post_excerpt ) {
			$content = $this->post->post_excerpt;
		}

		if ( '' != $custom_limit ) {
			$output .= $this->platform->excerpt( $content, $custom_limit );
		} else {
			$limit = $this->get_inner_option( 'excerpt_length' );
			if ( 0 < intval( $limit ) ) {
				$output .= $this->platform->excerpt( $content, $limit );
			} else {
				$output .= $content;
			}
		}

		$output = str_replace(']]>', ']]>', $output);

		return $output;
	}

	/**
	 * @since 1.0.0
	 * @param int $count_category
	 * @param string $tax_name
	 * @param string $field_name
	 * @return string
	 */
	public function render_category( $count_category = 1, $tax_name = '', $field_name = '' ) {
		$output = $cat = $cid = $cat_name = $custom_taxonomy = '';

		if ( ! is_numeric( $count_category ) ) {
			$count_category = 1;
		}

		$post_type = $this->current_post_type;
		$default_post_types = get_post_types( [ '_builtin' => true, ] );

		if ( array_key_exists( $post_type, $default_post_types ) ) {
			$categories = get_the_category( $this->post->ID );
			$output .= '<div class="trilisting-term trilisting-category">';

			if ( -1 === $count_category || 1 < $count_category ) {
				$i = 0;
				foreach( $categories as $cat_id => $cat ) {
					$output .= '<a href="' . esc_url( get_category_link( $cat->term_id ) ) . '">' . esc_html( $cat->cat_name ) . '</a>';
					
					$i++;
					if( $i >= $count_category ) {
						break;
					}
				}
			} elseif ( 1 == $count_category ) {
				foreach ( $categories as $category ) {
					if ( $category->term_id == get_query_var( 'cat' ) ) {
						$cat = $category;
						break;
					}
				}

				if ( empty( $cat ) and ! empty( $categories[0] ) ) {
					$cat = $categories[0];
				}

				if ( ! empty( $cat ) ) {
					$cid = $cat->cat_ID;
					$cat_name = $cat->name;
				}

				if ( ! empty( $cid ) && ! empty( $cat_name ) ) {
					$output .= '<a href="' . esc_url( get_category_link( $cid ) ) . '">' . esc_html( $cat_name ) . '</a>';
				}
			}

			$output .= '</div>';
		} else {
			// custom post type
			$taxs_name = get_object_taxonomies( $post_type, 'objects' );

			if ( ! empty( $taxs_name ) ) {
				foreach ( $taxs_name as $obj ) {
					$terms = get_the_terms( $this->post->ID, $obj->name );

					if ( ! empty( $terms ) ) {
						if ( ! empty( $tax_name ) && ( $tax_name === $obj->name ) ) {
							$output	.= '<div class="trilisting-term ' . esc_attr( $obj->name ) . '">';
							$output .= Trilisting_Helpers::do_action( 'trilisting/taxonomy/' . $obj->name . '/before_html' );
							foreach ( $terms as $term ) {
								if ( ! empty( $term->term_id ) && ! empty( $term->name ) ) {
									$custom_field_val = '';

									if ( ! empty( $field_name ) && function_exists( 'get_field_objects' ) ) {
										$fields_obj = get_field_objects( $term->taxonomy . '_' . $term->term_id );
										if ( ! empty( $fields_obj ) && isset( $fields_obj[ $field_name ] ) ) {
											$field_value = $fields_obj[ $field_name ];

											switch ( $field_value['type'] ) {
												case 'font-awesome' :
													$custom_field_val = \TRILISTING\Trilisting_Acf_Fields::font_awesome_field( $field_value );
													break;
											}
										}
									}

									$output .= '<a class="trilisting-term-link" href="' . esc_url( get_category_link( $term->term_id ) ) . '">' . $custom_field_val . esc_html( $term->name ) . '</a>';
								}
							}
							
							$output .= '</div>';
							break;
						} // End if
						
						if ( empty( $tax_name ) ) {
							$output	.= '<div class="trilisting-term ' . esc_attr( $obj->name ) . '">';
						
							foreach ( $terms as $term ) {
								if ( ! empty( $term->term_id ) && ! empty( $term->name ) ) {
									$output .= '<a class="trilisting-term-link" href="' . esc_url( get_category_link( $term->term_id ) ) . '">' . esc_html( $term->name ) . '</a>';
								}
							}
							$output .= '</div>';
						}
					} // End if
				} // End foreach
			} // End if
		} // End if

		return $output;
	}

} // abstract class
