<?php
/**
 * Widget base.
 */

namespace TRILISTING;

use TRILISTING\DB\Trilisting_DB_Query;

/* Check if Class Exists. */
if ( ! class_exists( 'trilisting_widgets_base' ) ) {

	class trilisting_widgets_base {
		public $widget_uid;
		public $query;

		protected $template_data;
		protected $post_index;
		protected $scope;
		protected $atts           = []; //the atts used for rendering the current widget
		protected $global_options = [];
		protected $acf_reg_fields = [];

		private $opt_enable_post_type;
		private $template_instance;
		private $is_loop = true;

		/**
		 * trilisting_widgets_base constructor.
		 * 
		 * @since 1.0.0
		 * @param string $scope
		 * @param int $post_index
		 */
		public function __construct( $scope = '', $post_index = 0 ) {
			$this->scope      = $scope;
			$this->post_index = $post_index;

			$this->opt_enable_post_type   = trilisting_enable_post_types();
			$this->opt_enable_post_type[] = 'post';
			
			$this->acf_reg_fields = Trilisting_DB_Query::get_acf_register_fields();
		}

		/**
		 * @since 1.0.0
		 * @return mixed|string
		 */
		public function get_widget_name() {
			$m = get_class( $this );
			$m = str_replace( 'trilisting_', '', $m );

			return $m;
		}

		/**
		 * @since 1.0.0
		 * @param $file
		 * @param $context
		 * @param bool $output
		 * @return string
		 */
		protected function render_template( $file, $context, $output = true ) {
			if ( $output ) {
				ob_start();
			}

			$template_file = locate_template( "trilisting-templates/grid-templates/grid-{$file}.php" );

			if ( ! file_exists( $template_file ) ) {
				$template_file = plugin_dir_path( dirname( __FILE__ ) ) . "grid-templates/grid-{$file}.php";
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
		 * Settings for widgets.
		 * 
		 * @since 1.0.0
		 * @return array
		 */
		protected function get_global_option_names() {
			if ( ! empty( $this->opt_enable_post_type ) ) {
				$global_opt = [
					'title_length'   => 40,
					'excerpt_length' => 100,
					'readmore'       => 1,
				];

				foreach ( $this->opt_enable_post_type as $post_type ) {
					$global_opt = array_merge( $global_opt, [
						'meta_' . $post_type . '.category' => 1,
						'meta_' . $post_type . '.date'     => 1,
						'meta_' . $post_type . '.comments' => 0,
						'meta_' . $post_type . '.reviews'  => 0,
						'meta_' . $post_type . '.author'   => 1,
					]);
				}

				return $global_opt;
			} else {
				return [
					'title_length'   => 40,
					'excerpt_length' => 100,
					'readmore'       => 1,
					'meta.category'  => 1,
					'meta.reviews'   => 1,
					'meta.date'      => 1,
					'meta.comments'  => 0,
					'meta.author'    => 1,
				];
			} // End if
		}

		/**
		 * @since 1.0.0
		 * @param array $custom
		 */
		protected function add_global_options( $custom = [] ) {
			$this->global_options = [];

			if ( ! empty( $this->opt_enable_post_type ) ) {
				foreach ( $this->opt_enable_post_type as $post_type ) {
					if ( ! empty( $this->acf_reg_fields ) ) {
						$widget = $this->get_widget_name();
						foreach ( $this->acf_reg_fields as $key => $field_name ) {
							if (
								'text'   == $field_name['post_content']['type']
								|| 'number' == $field_name['post_content']['type']
								|| 'email'  == $field_name['post_content']['type']
							) {
								$full_key = $widget . '_' . 'meta_' . $post_type . '.' . $field_name['post_excerpt'];
								$this->global_options[ 'meta_' . $post_type . '.' . $field_name['post_excerpt'] ] = \Trilisting_Widgets_Platform::instance()->get_option( $full_key );
							}
						}
					} else {
						continue;
					}
				} // End foreach
			} // End if

			if ( count( $custom ) > 0 ) {
				foreach ( $custom as $key => $default ) {
					$this->global_options[ $key ] = \Trilisting_Widgets_Platform::instance()->get_option( $key );
				}
			}

			$global = $this->get_global_option_names();
			if ( count( $global ) > 0 ) {
				$widget = $this->get_widget_name();
				foreach ( $global as $key => $default ) {
					$full_key = $widget . '_' . $key;
					$this->global_options[ $key ] = \Trilisting_Widgets_Platform::instance()->get_option( $full_key );
				}
			}
		}

		/**
		 * @since 1.0.0
		 * @return array
		 */
		public function get_global_options_array() {
			$result                   = [];
			$result['title_length']   = $this->get_global_option( 'title_length' );
			$result['excerpt_length'] = $this->get_global_option( 'excerpt_length' );

			if ( ! empty( $this->opt_enable_post_type ) ) {
				foreach ( $this->opt_enable_post_type as $post_type ) {
					$result['meta_' . $post_type . '.category'] = $this->get_global_option( 'meta_' . $post_type . '.category' );
					$result['meta_' . $post_type . '.date']     = $this->get_global_option( 'meta_' . $post_type . '.date' );
					$result['meta_' . $post_type . '.comments'] = $this->get_global_option( 'meta_' . $post_type . '.comments' );
					$result['meta_' . $post_type . '.reviews']  = $this->get_global_option( 'meta_' . $post_type . '.reviews' );
					$result['meta_' . $post_type . '.author']   = $this->get_global_option( 'meta_' . $post_type . '.author' );

					if ( ! empty( $this->acf_reg_fields ) ) {
						foreach ( $this->acf_reg_fields as $key => $field_name ) {
							if (
								'text'   == $field_name['post_content']['type']
								|| 'number' == $field_name['post_content']['type']
								|| 'email'  == $field_name['post_content']['type']
							) {
								$result['meta_' . $post_type . '.' . $field_name['post_excerpt'] ] = $this->get_global_option( 'meta_' . $post_type . '.' . $field_name['post_excerpt'] );
							}
						}
					}

				} // End foreach
			} // End if

			return $result;
		}

		/**
		 * @since 1.0.0
		 * @param $atts
		 */
		public function set_widget_atts( $atts ) {
			$this->atts = array_merge( $this->atts, $atts );
		}

		/**
		 * @since 1.0.0
		 * @param $name
		 * @return mixed
		 */
		public function get_global_option( $name ) {
			$inherited = $this->get_att( 'ac_common_inherit' );
			if ( true != $inherited && 'true' != $inherited ) {
				$name_att = str_replace( '.', '-', $name );
				$att_key  = 'ac_widget_' . $name_att;

				if ( isset( $this->atts[ $att_key ] ) ) {
					return $this->atts[ $att_key ];
				}
			}

			if ( isset( $this->global_options[ $name ] ) ) {
				return $this->global_options[ $name ];
			}
		}

		/**
		 * @since 1.0.0
		 * @param string $scope
		 * @return int
		 */
		public static function vc_get_columns( $scope = '' ) {
			if ( empty( $scope ) ) {
				$columns = 4;
			} else {
				if ( 'widget' == $scope ) {
					$columns = 3;
				} else {
					$columns = 12;
				}
			}

			//default
			return $columns;
		}

		/**
		 * @since 1.0.0
		 * @return array
		 */
		public function get_drop_down_items() {
			$items = [];
			if ( ! empty( $this->scope ) && 'widget' != $this->scope ) {
				return $items;
			}

			$ajax_filter_type   = $this->get_att( 'ac_ajax_filter_type' );
			$filter_default_txt = $this->get_att( 'ac_filter_default_txt' );
			$ajax_filter_ids    = $this->get_att( 'ac_ajax_filter_ids' );

			if ( ! empty( $ajax_filter_type ) ) {
				$items[0] = [
					'name' => $filter_default_txt,
					'id'   => '',
				];

				switch ( $ajax_filter_type ) {
					case 'ac_category_by_ids_filter':
						$categories = get_categories([
							'include' => $ajax_filter_ids,
							'exclude' => '1',
							'number'  => 100, // limit the output
						]);

						if ( ! empty( $ajax_filter_ids ) ) {
							$ajax_filter_ids = explode( ',', $ajax_filter_ids );

							// order the categories - match the order set in the block settings
							foreach ( $ajax_filter_ids as $category_id ) {
								$category_id = trim( $category_id );

								foreach ( $categories as $category ) {
									// retrieve the category
									if ( $category_id == $category->cat_ID ) {
										$items [] = [
											'name' => $category->name,
											'id'   => $category->cat_ID,
										];
										break;
									}
								}
							}
							// if no category ids are added
						} else {
							foreach ( $categories as $category ) {
								$items[] = [
									'name' => $category->name,
									'id'   => $category->cat_ID,
								];
							}
						}
						break;
					case 'ac_author_by_ids_filter':
						if ( ! empty( $ajax_filter_ids ) ) {
							$ajax_filter_ids = explode( ',', $ajax_filter_ids );
						}
						if ( is_array( $ajax_filter_ids ) && count( $ajax_filter_ids ) > 0 ) {
							$authors = get_users([
								'role__in' => [ 'author', 'trilisting_author' ],
								'include'  => $ajax_filter_ids,
							]);
						} else {
							$authors = get_users( [ 'role__in' => [ 'author', 'trilisting_author' ] ] );
						}

						foreach ( $authors as $author ) {
							$items[] = [
								'name' => $author->display_name,
								'id'   => $author->ID,
							];
						}
						break;
					case 'ac_tag_by_ids_filter':
						$tags = [];
						if ( ! empty( $ajax_filter_ids ) ) {
							$tags = get_tags([
								'include' => $ajax_filter_ids,
							]);
						} else {
							$tags = get_tags([
								'orderby' => 'name',
								'number'  => 100,
							]);
						}

						foreach ( $tags as $tag ) {
							$items[] = [
								'name' => $tag->name,
								'id'   => $tag->term_id,
							];
						}
						break;
				} // End switch
			} // End if

			return $items;
		}

		/**
		 * @since 1.0.0
		 * @param $atts
		 * @param null $content
		 * @return string
		 */
		public function render( $atts, $content = null ) {
			if ( empty( $this->scope ) || 'widget' == $this->scope ) {
				$this->atts = shortcode_atts(
					[
						's'                           => '',
						'post_limit'                  => 8,
						'sortby'                      => '',
						'post_status'                 => '',
						'column_number'               => '',
						'sortorder'                   => '',
						'tax_query'                   => '',
						'meta_query'                  => '',
						'sticky_posts'                => '',
						'post_ids'                    => '',
						'wp_custom_post_types'        => 'trilisting_places',
						'wp_custom_taxonomy_types'    => '',
						'wp_custom_taxonomy_category' => '',
						'tag_sid'                     => '',
						'tag_ids'                     => '',
						'author_ids'                  => '',
						'category_id'                 => '',
						'category_ids'                => '',
						'custom_title'                => '',
						'custom_url'                  => '',
						'ajax_pagination'             => '',
						'vc_columns_number'           => trilisting_widgets_base::vc_get_columns( $this->scope ),
						'ac_ajax_filter_type'         => '',
						'ac_ajax_filter_ids'          => '',
						'ac_sortby_filter'            => '',
						'ac_filter_default_txt'       => esc_html__( 'All', 'trilisting' ),
						'class'                       => '',
						'el_class'                    => '',
						'offset'                      => '', // the offset
						'css'                         => '', //custom css,
						'ac_widget_title_length'      => '15',
						'ac_widget_excerpt_length'    => '30',
						'ac_widget_category'          => '1',
						'ac_widget_meta-date'         => 'true',
						'ac_widget_meta-liked'        => 'true',
						'ac_widget_meta-author'       => 'false',
						'ac_widget_meta-comments'     => 'false',
						'ac_widget_meta-views'        => 'false',
						'ac_widget_meta-reviews'      => 'false',
						'ac_common_inherit'           => 'true',
					],
					$atts
				);
			} else {
				$this->atts = shortcode_atts(
					[
						's'                           => '',
						'post_limit'                  => get_option( 'posts_per_page' ),
						'sortby'                      => '',
						'post_status'                 => '',
						'sortorder'                   => '',
						'tax_query'                   => '',
						'meta_query'                  => '',
						'column_number'               => '',
						'sticky_posts'                => '',
						'wp_custom_post_types'        => 'trilisting_places',
						'wp_custom_taxonomy_types'    => '',
						'wp_custom_taxonomy_category' => '',
						'post_ids'                    => '',
						'tag_sid'                     => '',
						'tag_ids'                     => '',
						'author_ids'                  => '',
						'category_id'                 => '',
						'category_ids'                => '',
						'custom_title'                => '',
						'custom_url'                  => '',
						'ajax_pagination'             => '',
						'vc_columns_number'           => 12,
						'ac_ajax_filter_type'         => '',
						'ac_ajax_filter_ids'          => '',
						'ac_sortby_filter'            => '',
						'ac_filter_default_txt'       => '',
						'class'                       => '',
						'el_class'                    => '',
						'offset'                      => '', // the offset
						'css'                         => '', // custom css,
						'ac_widget_title_length'      => '',
						'ac_widget_excerpt_length'    => '',
						'ac_widget_category'          => '1',
						'ac_widget_meta-date'         => 'true',
						'ac_widget_meta-liked'        => 'true',
						'ac_widget_meta-author'       => 'false',
						'ac_widget_meta-reviews'      => 'false',
						'ac_common_inherit'           => 'true',
					], $atts );
			} // End if

			$this->widget_uid = \Trilisting_Widgets_Manager::generate_guid();
			$filter_list = [];
			if ( true === $this->is_loop() ) {
				// get the pull down items
				$drop_down_items = $this->get_drop_down_items();
				if ( empty( $this->scope ) || 'widget' == $this->scope ) {
					//by ref do the query
					$this->query = &\Trilisting_Widgets_Manager::get_wp_query( $this->atts );
				} else {
					if ( 'page' == $this->scope ) {
						$qr = 'paged';
						if ( is_front_page() ) {
							$qr = 'page';
						}
						$args = [
							'posts_per_page' => get_option( 'posts_per_page' ),
							'paged'          => get_query_var( $qr ),
						];
					} else {
						global $wp_query;
						$this->query = $wp_query;
					}
				}
			} // End if

			$template     = 'TRILISTING\trilisting_widgets_template_1';
			$unique_class = $this->widget_uid . '--widget_uclass ';

			$mode = 'full';
			if ( empty( $this->scope ) || 'widget' == $this->scope ) {
				$columns = static::vc_get_columns();
			} else {
				$columns = 12;
			}
			if ( 8 > $columns ) {
				$mode = 'small';
			}

			$drop_down_items = $this->get_drop_down_items();

			$this->template_data = [
				'atts'            => $this->atts,
				'widget_uid'      => $this->widget_uid,
				'unique_class'    => $unique_class,
				'drop_down_items' => $drop_down_items,
				'drop_down_mode'  => $mode,
			];
			$this->template_instance = new $template( $this->template_data );

			return '';
		}

		/**
		 * @since 1.0.0
		 * @return bool
		 */
		public function is_loop() {
			return $this->is_loop;
		}

		/**
		 * @since 1.0.0 
		 */
		public function disable_loop_mode() {
			$this->is_loop = false;
		}

		/**
		 * @since 1.0.0
		 * @param $att_name
		 * @return mixed
		 * @throws ErrorException
		 */
		protected function get_att( $att_name ) {
			if ( empty( $this->atts ) ) {
				throw new \ErrorException( 'Error: the atts are not set yet. Class: "' . get_class( $this ) . '", method: "get_att"' );
				die;
			}

			if ( ! isset( $this->atts[ $att_name ] ) ) {
				throw new \ErrorException( 'Error: trying to get an att that does not exists! Class: "' . get_class( $this ) . '",  Att name: "' . $att_name . '".' );
				die;
			}

			return $this->atts[ $att_name ];
		}

		/**
		 * gets the current template instance, if no instance it's found throws error
		 *
		 * @since 1.0.0
		 * @return mixed the template instance
		 * @throws ErrorException - no template instance found
		 */
		private function widget_template() {
			if ( isset( $this->template_instance ) ) {
				return $this->template_instance;
			} else {
				throw new \ErrorException( 'Error: no template instance. Class: "' . get_class( $this ) . '", method: "widget_template"' );
				die;
			}
		}

		/**
		 * @since 1.0.0
		 * @return string
		 */
		public function get_title() {
			$title = '';
			if ( empty( $this->scope ) || 'single_post_relate' == $this->scope || 'widget' == $this->scope || 'page' == $this->scope ) {
				$title = $this->widget_template()->get_title();
			}

			return $title;
		}

		/**
		 * @since 1.0.0
		 * @return string
		 */
		public function get_sortby_filter() {
			return $this->widget_template()->get_sortby_filter();
		}

		/**
		 * @since 1.0.0
		 * @return string
		 */
		public function get_pagination() {
			if ( ! empty( $this->scope ) && 'widget' != $this->scope ) {
				return '';
			}

			$offset = 0;

			if ( isset( $this->atts['offset'] ) ) {
				$offset = $this->atts['offset'];

				if ( empty( $offset ) ) {
					$offset = 0;
				}
			}

			$output	= '';
			$ajax_pagination = $this->get_att( 'ajax_pagination' );
			$limit = $this->get_att( 'post_limit' );

			$prev_text = esc_html__( 'Prev', 'trilisting' );
			$next_text = esc_html__( 'Next', 'trilisting' );
			$show_text = false;
			$position  = 'center';

			switch ( $ajax_pagination ) {

				case 'next_prev':
					$output .= '<div class="trilisting-paginator next-prev-wrap ac-position-' . esc_attr( $position ) . '">';
					$output .= '<a href="#" class="ac-list-page-link trilisting-prev-button btn-page-disabled" id="prev-page-' . $this->widget_uid . '" data-ac_block_id="' . $this->widget_uid . '"><i class="fa fa-angle-left"></i>' . ( $show_text == true ? $prev_text : '' ) . '</a>';

					if ( $this->query->found_posts - $offset <= $limit ) {
						//hide next page button
						$output .= '<a href="#"  class="ac-list-page-link trilisting-next-button btn-page-disabled" id="next-page-' . $this->widget_uid . '" data-ac_block_id="' . $this->widget_uid . '">' . ( $show_text == true ? $next_text : '' ) . '<i class="fa fa-angle-right"></i></a>';
					} else {
						$output .= '<a href="#"  class="ac-list-page-link trilisting-next-button" id="next-page-' . $this->widget_uid . '" data-ac_block_id="' . $this->widget_uid . '">' . ( $show_text == true ? $next_text : '' ) . '<i class="fa fa-angle-right"></i></a>';
					}

					$output .= '</div>';
					break;

				case 'numeric':
					if ( $this->query->found_posts - $offset <= $limit ) {
						$output .= '<div class=" trilisting-paginator trilisting-widgets-numeric-paginator numeric-disabled ac-position-' . esc_attr( $position ) . '">';
						$output .= '<a href="#" class="trilisting-list-page-link trilisting-prev-button btn-page-disabled" id="prev-page-' . $this->widget_uid . '" data-ac_block_id="' . $this->widget_uid . '">PREV' . ( $show_text == true ? $prev_text : '' ) . '</a>';	
						$output .= '<a href="#"  class="trilisting-list-page-link trilisting-next-button btn-page-disabled" id="next-page-' . $this->widget_uid . '" data-ac_block_id="' . $this->widget_uid . '">' . ( $show_text == true ? $next_text : '' ) . 'NEXT</a>';
					} else {
						$output .= '<div class=" trilisting-paginator trilisting-widgets-numeric-paginator ac-position-' . esc_attr( $position ) . '">';
						$output .= '<a href="#" class="trilisting-list-page-link trilisting-prev-button btn-page-disabled" id="prev-page-' . $this->widget_uid . '" data-ac_block_id="' . $this->widget_uid . '">PREV' . ( $show_text == true ? $prev_text : '' ) . '</a>';
	
						$count_page = ceil( ( $this->query->found_posts - $offset ) / $limit );
						for ( $num = 1; $num <= $count_page; $num++ ) {
							$active_page = ( 1 == $num ) ? ' active-page' : '';
							$output .= '<a href="#"  class="trilisting-list-page-link page-number-' . $num . $active_page . '" data-numeric-page="' . $num . '" data-ac_block_id="' . $this->widget_uid . '">' . $num . '</a>';
						}
						$output .= '<a href="#"  class="trilisting-list-page-link trilisting-next-button" id="next-page-' . $this->widget_uid . '" data-ac_block_id="' . $this->widget_uid . '">' . ( $show_text == true ? $next_text : '' ) . 'NEXT</a>';
					}

					$output .= '</div>';
					break;

				case 'load_more':
					$hidden = '';
					if ( ( $this->query->found_posts - $offset <= $limit ) || ( 1 == $this->query->max_num_pages ) ) {
						$hidden = ' hidden';
					}
					$output .= '<div class="ac-loadmore trilisting-paginator trilisting-pagination trilisting-loadmore-page">';
					$output .= '<a href="#" class="trilisting-btn' . esc_attr( $hidden ) . '" id="next-page-' . $this->widget_uid . '" data-ac_block_id="' . $this->widget_uid . '">' . esc_html__( 'Load more', 'trilisting' );
					$output .= '</a>';
					$output .= \TRILISTING\Trilisting_Helpers::do_action( 'trilisting/widgets/ajax_loader_html' );
					$output .= '</div>';

					break;
			} // End switch

			return $output;
		}

		/**
		 * @since 1.0.0
		 * @return string
		 */
		public function get_widget_js_settings() {
			if ( ! empty( $this->scope ) && 'widget' != $this->scope ) {
				return '';
			}

			do_action( 'ac_widget_base__render_widget_js_settings', [ &$this ] );

			// do not output the js if it's not a loop widget
			if ( false === $this->is_loop() ) {
				return '';
			}

			$output		= '';
			$pagination	= '';
			$ajax		= '';
			$ajax_pg	= $this->get_att( 'ajax_pagination' );
			$ajax_sort	= $this->get_att( 'ac_sortby_filter' );
			if ( ! empty( $ajax_pg ) || ! empty( $ajax_sort ) ) {
				$max_pages = 0;
				$per_page  = 0;
				if ( ! empty( $this->atts['offset'] ) ) {
					if ( 0 != $this->atts['post_limit'] ) {
						$per_page  = $this->atts['post_limit'];
						$max_pages = ceil( ( $this->query->found_posts - $this->atts['offset'] ) / $this->atts['post_limit'] );
					} else if ( 0 != get_option( 'posts_per_page' ) ) {
						$per_page  = get_option( 'posts_per_page' );
						$max_pages = ceil( ( $this->query->found_posts - $this->atts['offset'] ) / $per_page );
					}
				} else {
					$max_pages = $this->query->max_num_pages;
					if ( 0 != $this->atts['post_limit'] ) {
						$per_page = $this->atts['post_limit'];
					} else {
						$per_page = get_option( 'posts_per_page' );
					}
				}

				$pagination = [
					'total'      => $this->query->found_posts,
					'post_count' => $this->query->post_count,
					'perpage'    => $per_page,
					'current'    => 1,
					'pages'      => $max_pages,
				];
			} // End if

			$ajax_flt = $this->get_att( 'ac_ajax_filter_type' );
			if ( ! empty( $ajax_flt ) || '' != $pagination ) {
				$ajax = [
					'action'      => 'ac_ajax_widget',
					'widget_id'   => $this->widget_uid,
					'atts'        => str_replace( "'", "\u0027", json_encode( $this->atts ) ),
					'vc_columns'  => $this->atts['vc_columns_number'],
					'widget_name' => $this->get_widget_name(),
					'widget_type' => get_class( $this ),
				];
			}

			if ( '' != $ajax || '' != $pagination ) {
				$output .= '<div id="ajax-settings--' . $this->widget_uid . '" class="' . $this->widget_uid . ' ajax-settings"';
				if ( '' != $ajax ) {
					$output .= ' data-ajax-settings=\'' . json_encode( $ajax ) . '\'';
				}
				if ( '' != $pagination ) {
					$output .= ' data-ajax-pagination=\'' . json_encode( $pagination ) . '\'';
				}
				$output .= '></div>';
			}

			return $output;
		}

		/**
		 * @since 1.0.0
		 * @return string
		 */
		protected function get_widget_html_data() {
			return ' data-ac-widget-uid="' . $this->widget_uid . '" ';
		}

		/**
		 * Parses a design panel generated css string and get's the classes and the
		 *
		 * @since 1.0.0
		 * @return array|bool - array of results or false if no classes are available
		 */
		protected function parse_css_att( $user_css_att ) {
			if ( empty( $user_css_att ) ) {
				return false;
			}

			$matches        = [];
			$preg_match_ret = preg_match_all( '/\s*\.\s*([^\{]+)\s*\{\s*([^\}]+)\s*\}\s*/', $user_css_att, $matches );

			if ( 0 === $preg_match_ret || false === $preg_match_ret || empty( $matches[1] ) || empty( $matches[2] ) ) {
				return false;
			}

			return $matches[1];
		}

		/**
		 * @since 1.0.0
		 * @param array $classes
		 * @return string
		 */
		protected function get_html_classes( $classes = [] ) {
			$class           = $this->get_att( 'class' );
			$el_class        = $this->get_att( 'el_class' );
			$ajax_pagination = $this->get_att( 'ajax_pagination' );
			$css             = $this->get_att( 'css' );

			//add the block wrap and block id class
			$widget_classes = [
				'trilisting-block',
				$this->template_data[ 'unique_class' ],
				get_class( $this ),
			];

			// get the design tab css classes
			$css_design = $this->parse_css_att( $css );
			if ( false !== $css_design ) {
				$widget_classes = array_merge(
					$widget_classes,
					$css_design
				);
			}

			//add the classes that we receive via shortcode.
			if ( ! empty( $class ) ) {
				$class_array   = explode( ' ', $class );
				$block_classes = array_merge(
					$widget_classes,
					$class_array
				);
			}

			//marge the additional classes
			if ( ! empty( $classes ) ) {
				$block_classes = array_merge(
					$widget_classes,
					$classes
				);
			}

			if ( ! empty( $ajax_pagination ) ) {
				$widget_classes[] = 'ac_with_ajax_pagination';
			}

			// this is the field that all the shortcodes have (or at least should have)
			if ( ! empty( $el_class ) ) {
				$el_class_array = explode( ' ', $el_class );
				$widget_classes = array_merge(
					$widget_classes,
					$el_class_array
				);
			}

			//remove duplicates
			$widget_classes = array_unique( $widget_classes );

			return implode( ' ', $widget_classes );
		}
	}

} // End if
