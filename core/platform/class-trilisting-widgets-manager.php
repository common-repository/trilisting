<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Trilisting_Widgets_Manager' ) ) {
	/**
	 * Widget manager.
	 */
	class Trilisting_Widgets_Manager {
		protected static $widgets = [];
		protected static $widgets_instances = [];

		public function __construct() {
			$this->init();
		}

		private static $unique_counter = 0;

		/**
		 * Generate unique_ids.
		 * 
		 * @since 1.0.0
		 * @return string
		 */
		public static function generate_guid() {
			self::$unique_counter++;
			return 'uuid_ac_' . self::$unique_counter . '__' . uniqid();
		}

		/**
		 * @since 1.0.0
		 * @param string $atts
		 * @param string $paged
		 * @return array
		 */
		public static function extract_query_args( $atts = '', $paged = '' ) {
			extract( shortcode_atts(
					[
						's'                           => '',
						'post_ids'                    => '',
						'category_ids'                => '',
						'category_id'                 => '',
						'tag_sid'                     => '',
						'tag_ids'                     => '',
						'sortby'                      => '',
						'sortorder'                   => '',
						'sticky_posts'                => '',
						'post_limit'                  => '',
						'author_ids'                  => '',
						'wp_custom_post_types'        => '',
						'wp_custom_taxonomy_types'    => '',
						'wp_custom_taxonomy_category' => '',
						'posts_per_page'              => '',
						'offset'                      => '',
						'ac_common_inherit'           => 'true',
						'tax_query'                   => '',
						'meta_query'                  => '',
						'post_status'                 => '',
					],
					$atts
				)
			);
			$wp = [
				'ignore_sticky_posts' => empty( $sticky_posts ) ? 1 : 0,
				'post_status'         => 'publish',
			];
			if (  ! empty( $post_status ) ) {
				if ( ! is_array( $post_status ) ) {
					$post_status = explode( ',', $post_status );
				}
				$wp['post_status'] = $post_status;
			}
			if ( ! empty( $tag_sid ) and empty( $tag_ids ) ) {
				$tag_ids = $tag_sid;
			}
			if ( ! empty( $tag_ids ) ) {
				if ( ! is_array( $tag_ids ) ) {
					$tag_ids = explode( ',', $tag_ids );
				}
				$wp['tag__in'] = $tag_ids;
			}
			if ( ! empty( $author_ids ) ) {
				$wp['author__in'] = $author_ids;
			}
			if ( ! empty( $tax_query ) ) {
				$wp['tax_query'] = $tax_query;
			}
			if ( ! empty( $meta_query ) ) {
				$wp['meta_query'] = $meta_query;
			}
			if ( ! empty( $category_id ) and empty( $category_ids ) ) {
				$wp['category_name'] = $category_id;
			}
			if ( ! empty( $category_ids ) ) {
				$wp['cat'] = $category_ids;
			}
			if ( ! empty( $wp_custom_taxonomy_types ) && empty( $tax_query ) ) {
				if ( ! empty( $wp_custom_taxonomy_category ) ) {
					$custom_tax = explode( ',', $wp_custom_taxonomy_category );
					$wp['tax_query'] = [
						[
							'taxonomy' => trim( $wp_custom_taxonomy_types ),
							'field'    => 'slug',
							'terms'    => $custom_tax,
						]
					];
				} else {
					$wp['tax_query'] = [
						[
							'taxonomy' => trim( $wp_custom_taxonomy_types ),
							'field'    => 'slug',
							'terms'    => '',
							'operator' => 'EXISTS',
						]
					];
				}
			}
			if ( ! empty( $s ) ) {
				$wp['s'] = $s;
			}
			if ( empty ( $sortorder ) ) {
				$sortorder = 'DESC';
			}
			$wp['order'] = $sortorder;
			switch ( $sortby ) {
				case 'by_date':
					$wp['orderby'] = 'date';
					break;
				case 'by_mod_date':
					$wp['orderby'] = 'modified';
					break;
				case 'by_title':
					$wp['orderby'] = 'title';
					break;
				case 'by_slug':
					$wp['orderby'] = 'name';
					break;
				case 'by_view_count':
					$wp['meta_query'] = [
						[
							'relation'    => 'OR',
							'view_exists' => [
								'key'     => '_tril_post_views_count',
								'compare' => 'EXISTS',
							],
							'view_not_exists' => [
								'key'         => '_tril_post_views_count',
								'compare'     => 'NOT EXISTS',
							],
					 	]
					];
					$wp['orderby'] = 'meta_value_num';
					break;
				case 'by_comment_count':
					$wp['orderby'] = 'comment_count';
					break;
				case 'by_random':
					$wp[ 'orderby' ] = 'rand';
					break;
				case 'random_today':
					$wp['orderby']  = 'rand';
					$wp['year']	    = date( 'Y' );
					$wp['monthnum'] = date( 'n' );
					$wp['day']      = date( 'j' );
					break;
				case 'random_7_day':
					$wp['orderby']    = 'rand';
					$wp['date_query'] = [
						'column' => 'post_date_gmt',
						'after'  => '1 week ago',
					];
					break;
			} // End switch.
			//add post_type to query
			if ( ! empty( $wp_custom_post_types ) ) {
				$post_types = [];
				$exploded = explode( ',', $wp_custom_post_types );

				foreach ( $exploded as $v ) {
					if ( '' != trim( $v ) ) {
						$post_types[] = trim( $v );
					}
				}

				$wp['post_type'] = $post_types;
			}

			// post by ids filter
			if ( ! empty( $post_ids ) ) {

				//split posts id string
				$pids   = explode( ',', $post_ids );
				$in     = [];
				$not_in = [];

				foreach ( $pids as $id ) {
					$id = trim( $id );
					if ( is_numeric( $id ) ) {
						if ( intval( $id ) < 0 ) {
							$not_in [] = str_replace( '-', '', $id );
						} else {
							$in [] = $id;
						}
					}
				}

				if ( ! empty( $in ) ) {
					$wp['post__in'] = $in;
				}

				if ( ! empty( $not_in ) ) {
					if ( ! empty( $wp['post__not_in'] ) ) {
						$wp['post__not_in'] = array_merge( $wp['post__not_in'], $not_in );
					} else {
						$wp['post__not_in'] = $not_in;
					}
				}
			} // End if

			//custom pagination
			if ( empty( $post_limit ) ) {
				$post_limit = get_option( 'posts_per_page' );
			}

			$wp['posts_per_page'] = $post_limit;
			if ( ! empty( $paged ) ) {
				$wp['paged'] = $paged;
			} else {
				$wp['paged'] = 1;
			}
			if ( ! empty( $offset ) and $paged > 1 ) {
				$wp['offset'] = $offset + ( ( $paged - 1 ) * $post_limit );
			} else {
				$wp['offset'] = $offset;
			}

			return $wp;
		}

		/**
		 * @since 1.0.0
		 * @param string $atts
		 * @param string $paged
		 * @return WP_Query
		 */
		public static function &get_wp_query( $atts = '', $paged = '' ) {
			$args  = self::extract_query_args( $atts, $paged );
			$query = new WP_Query( $args );

			return $query;
		}

		/**
		 * @since 1.0.0
		 * @param $mid
		 * @return mixed
		 */
		public static function get_widget_instance( $mid ) {
			if ( key_exists( $mid, self::$widgets_instances ) ) {
				return self::$widgets_instances[ $mid ];
			}
			self::$widgets_instances[ $mid ] = new $mid;

			return self::$widgets_instances[ $mid ];
		}

		/**
		 * @since 1.0.0
		 * @param $mid
		 * @return array
		 */
		public static function get_widget_data( $mid ) {
			if ( isset( self::$widgets[ $mid ] ) ) {
				return self::$widgets[ $mid ];
			} else {
				return [];
			}
		}

		/**
		 * @since 1.0.0
		 * @param $atts
		 * @param $content
		 * @param $tag
		 * @return string
		 */
		public static function init_widget_function( $atts, $content, $tag ) {
			$instance = self::get_widget_instance( $tag );
			if ( ! empty( $instance ) ) {
				return $instance->render( $atts, $content );
			}

			return '';
		}

		/**
		 * @since 1.0.0
		 * @return array
		 */
		protected static function add_default_params() {
			return [
				[
					"param_name"  => "custom_title",
					"type"        => "textfield",
					"value"       => "",
					"heading"     => esc_html__( 'Custom title for this block:', 'trilisting' ),
					"description" => esc_html__( "Optional - a title for this block, if you leave it blank the block will not have a title", 'trilisting' ),
					"holder"      => "div",
					"class"       => '',
				],
				[
					"param_name"  => "custom_url",
					"type"        => "textfield",
					"value"       => "",
					"heading"     => esc_html__( 'Title url:', 'trilisting' ),
					"description" => esc_html__( "Optional - a custom url when the block title is clicked", 'trilisting' ),
					"holder"      => "div",
					"class"       => '',
				],
			];
		}

		/**
		 * @since 1.0.0
		 * @return array
		 */
		protected static function add_default_columns() {
			return [
				[
					"param_name" => "column_number",
					"type"       => "dropdown",
					"value"      => [
						'1' => '1',
						'2' => '2',
						'3' => '3',
						'4' => '4',
					],
					"heading"    => esc_html__( 'Columns:', 'trilisting' ),
					"holder"     => "div",
					"class"      => '',
				],
			];
		}

		/**
		 * @since 1.0.0
		 * @param string $group
		 * @return array
		 */
		protected static function add_default_filter( $group = 'Filter' ) {
			return [
				[
					"param_name"  => "wp_custom_post_types",
					"type"        => "textfield",
					"value"       => 'trilisting_places',
					"heading"     => esc_html__( 'Post Type:', 'trilisting' ),
					"description" => esc_html__( "Filter by post types. Usage: post, page, trilisting_places - Write 1 or more post types delimited by commas", 'trilisting' ),
					"holder"      => "div",
					"class"       => '',
					'group'       => $group,
				],
				[
					"param_name"  => "wp_custom_taxonomy_types",
					"type"        => "textfield",
					"value"       => '',
					"heading"     => esc_html__( 'Taxonomy Type:', 'trilisting' ),
					"description" => esc_html__( "Filter by post taxonomy. Usage: trilisting_category, trilisting_location, trilisting_features.", 'trilisting' ),
					"holder"      => "div",
					"class"       => "",
					'group'       => $group,
				],
				[
					"param_name"  => "wp_custom_taxonomy_category",
					"type"        => "textfield",
					"value"       => '',
					"heading"     => esc_html__( 'Taxonomy slug (It depends on taxonomy type set):', 'trilisting' ),
					"description" => esc_html__( "Filter by taxonomy categories. Usage: slug taxonomy - Write 1 or more post types delimited by commas.", 'trilisting' ),
					"holder"      => "div",
					"class"       => "",
					'group'       => $group,
				],
				[
					"param_name"  => "post_ids",
					"type"        => "textfield",
					"value"       => '',
					"heading"     => esc_html__( 'Post ID filter:', 'trilisting' ),
					"description" => esc_html__( "Filter multiple posts by ID. Enter here the post IDs separated by commas (ex: 1,2,3). To exclude posts from this widget add them with '-' (ex: -7, -211)", 'trilisting' ),
					"holder"      => "div",
					"class"       => '',
					'group'       => $group,
				],
				[
					"param_name"  => "author_ids",
					"type"        => "textfield",
					"value"       => '',
					"heading"     => esc_html__( "Multiple authors filter:", 'trilisting' ),
					"description" => esc_html__( "Filter multiple authors by ID. Enter here the author IDs separated by commas (ex: 11,12,15).", 'trilisting' ),
					"holder"      => "div",
					"class"       => '',
					'group'       => $group,
				],
				[
					"param_name" => "sortby",
					"type"       => "dropdown",
					"value"      => [
						esc_html__( 'By publish date', 'trilisting' )           => 'by_date',
						esc_html__( 'By modify date', 'trilisting' )            => 'by_mod_date',
						esc_html__( 'By title', 'trilisting' )                  => 'by_title',
						esc_html__( 'By slug', 'trilisting' )                   => 'by_slug',
						esc_html__( 'By views count', 'trilisting' )            => 'by_view_count',
						esc_html__( 'By comments/reviews count', 'trilisting' ) => 'by_comment_count',
						esc_html__( 'Random', 'trilisting' )                    => 'by_random',
						esc_html__( 'Random Today', 'trilisting' )              => 'random_today',
						esc_html__( 'Random from last 7 Day', 'trilisting' )    => 'random_7_day',
					],
					"heading"     => esc_html__( 'Sort by:', 'trilisting' ),
					"description" => esc_html__( "How to sort the listing.", 'trilisting' ),
					"holder"      => "div",
					"class"       => '',
					'group'       => $group,
				],
				[
					"param_name" => "sortorder",
					"type"       => "dropdown",
					"value"      => [
						esc_html__( 'Descending', 'trilisting' ) => 'DESC',
						esc_html__( 'Ascending', 'trilisting' )  => 'ASC',
					],
					"heading"     => esc_html__( 'Sort order:', 'trilisting' ),
					"description" => esc_html__( "Sort direction.", 'trilisting' ),
					"holder"      => "div",
					"class"       => '',
					'group'       => $group,
				],
				[
					"param_name" => "sticky_posts",
					"type"       => "dropdown",
					"value"      => [
						esc_html__( 'Hide', 'trilisting' ) => '',
						esc_html__( 'Show', 'trilisting' ) => 'show',
					],
					"heading"     => esc_html__( "Sticky posts:", 'trilisting' ),
					"description" => esc_html__( "Shows sticky posts at the top of other posts.", 'trilisting' ),
					"holder"      => "div",
					"class"       => '',
					'group'       => $group,
				],

				// this are added to the main group
				[
					'param_name'  => 'el_class',
					'type'        => 'textfield',
					'value'       => '',
					'heading'     => esc_html__( 'Extra class', 'trilisting' ),
					'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS', 'trilisting' ),
					'class'       => 'ac-textfield-extrabig',
					'group'       => '',
				],
			];
		}

		/**
		 * @since 1.0.0
		 * @return array
		 */
		protected static function add_default_pagination() {
			return [
				[
					"param_name" => "ajax_pagination",
					"type"       => "dropdown",
					"value"      => [
						esc_html__( 'No pagination', 'trilisting' )        => '',
						esc_html__( 'Next and Prev ajax', 'trilisting' )   => 'next_prev',
						esc_html__( 'Numeric ajax', 'trilisting' )         => 'numeric',
						esc_html__( 'Load More button', 'trilisting' )     => 'load_more',
					],
					"heading"     => esc_html__( 'Pagination:', 'trilisting' ),
					"description" => esc_html__( "Select pagination type for widget.", 'trilisting' ),
					"holder"      => "div",
					"class"       => "ac-dropdown-big",
					'group'       => esc_html__( 'Pagination', 'trilisting' ),
				],
				[
					'param_name' => 'css',
					'value'      => '',
					'type'       => 'css_editor',
					'heading'    => esc_html__( 'Css', 'trilisting' ),
					'group'      => esc_html__( 'Design options', 'trilisting' ),
				],
			];
		}

		/**
		 * @since 1.0.0
		 * @return array
		 */
		protected static function add_default_ajax_filter(){
			return [
				[
					"param_name" => "ac_ajax_filter_type", //this is used to build the filter list (for example a list of categories from the id-s bellow)
					"type"       => "dropdown",
					"value"      => [
						esc_html__( 'No ajax filter', 'trilisting' )       => '',
						esc_html__( 'Filter by authors', 'trilisting' )    => 'ac_author_by_ids_filter',
					],
					"heading"     => esc_html__( 'Ajax dropdown - filter type:', 'trilisting' ),
					"description" => esc_html__( "Show the ajax drop down filter. The ajax filters require an additional parameter. If no ids are provided in the input below, the filter will show all the available items (ex: all authors, all categories etc..)", 'trilisting' ),
					"holder"      => "div",
					"class"       => '',
					"group"       => esc_html__( "Ajax filter", 'trilisting' ),
				],
				//filter by ids
				[
					"param_name"  => "ac_ajax_filter_ids", //the ids that we will show in the list
					"type"        => "textfield",
					"value"       => '',
					"heading"     => esc_html__( 'Ajax dropdown - show the following IDs:', 'trilisting' ),
					"description" => esc_html__( "The ajax drop down shows only the (author ids, categories ids OR tag IDs) that you enter here separated by comas", 'trilisting' ),
					"holder"      => "div",
					"class"       => '',
					"group"       => esc_html__( "Ajax filter", 'trilisting' ),
				],
				//default pull down text
				[
					"param_name"  => "ac_filter_default_txt",
					"type"        => "textfield",
					"value"       => 'All',
					"heading"     => esc_html__( 'Ajax dropdown - Filter default text:', 'trilisting' ),
					"description" => esc_html__( "The default text for the first item from the drop down. The first item shows the default block settings (the settings from the Filter tab)", 'trilisting' ),
					"holder"      => "div",
					"class"       => '',
					"group"       => esc_html__( "Ajax filter", 'trilisting' ),
				],
				//default pull down text
				[
					"param_name" => "ac_sortby_filter",
					"type"       => "dropdown",
					"value"      => [
						esc_html__( 'No', 'trilisting' )  => 'no_sortby',
						esc_html__( 'Yes', 'trilisting' ) => 'yes_sortby',
					],
					"heading" => esc_html__( 'Ajax sort by filter', 'trilisting' ),
					"holder"  => "div",
					"class"   => '',
					"group"   => esc_html__( "Ajax filter", 'trilisting' ),
				],
			];
		}

		/**
		 * @since 1.0.0
		 * @return array
		 */
		protected static function add_default_post_settings() {
			return [
				[
					"param_name"  => "post_limit",
					"type"        => "textfield",
					"value"       => '5',
					"heading"     => esc_html__( 'Limit post number:', 'trilisting' ),
					"description" => esc_html__( "If the field is empty the limit post number will be the number from Wordpress settings -> Reading", 'trilisting' ),
					"holder"      => "div",
					"class"       => "ac-textfield-small",
				],
				[
					"param_name"  => "offset",
					"type"        => "textfield",
					"value"       => '',
					"heading"     => esc_html__( 'Offset posts:', 'trilisting' ),
					"description" => esc_html__( "Start the count with an offset. If you have a block that shows 5 posts before this one, you can make this one start from the 6'th post (by using offset 5)", 'trilisting' ),
					"holder"      => "div",
					"class"       => "ac-textfield-small",
				],
			];
		}

		/**
		 * @since 1.0.0
		 * @param $id
		 * @param $data
		 */
		public static function add_widget( $id, $data ) {
			if ( is_array( self::$widgets ) && ! key_exists( $id, self::$widgets ) ) {
				if ( isset( $data['icon_auto'] ) && true == $data['icon_auto'] ) {
					$data['options']['icon'] = 'at-icon-pagebuilder-' . $id;
				}

				if ( isset( $data['default_params'] ) && true == $data['default_params'] ) {
					// default params for widget
					$data[ 'options' ][ 'params' ] = array_merge(
						self::add_default_params(),
						$data['options']['params']
					);
				}

				if ( isset( $data['post_settings'] ) && true == $data['post_settings'] ) {
					// default post settings for widget
					$data['options']['params'] = array_merge(
						$data['options']['params'],
						self::add_default_post_settings()
					);
				}

				if ( isset( $data['default_filter'] ) && true == $data['default_filter'] ) {
					// default filter for widget
					$data['options']['params'] = array_merge(
						$data['options']['params'],
						self::add_default_filter()
					);
				}

				if ( isset( $data['default_columns'] ) && true == $data['default_columns'] ) {
					// default columns for widget
					$data['options']['params'] = array_merge(
						$data['options']['params'],
						self::add_default_columns()
					);
				}

				if ( isset( $data['default_ajax_filter'] ) && true == $data['default_ajax_filter'] ) {
					// default ajax filter for widget
					$data['options']['params'] = array_merge(
						$data['options']['params'],
						self::add_default_ajax_filter()
					);
				}

				if ( isset( $data['default_pagination'] ) && true == $data['default_pagination'] ) {
					// default pagination for widget
					$data['options']['params'] = array_merge(
						$data['options']['params'],
						self::add_default_pagination()
					);
				}

				self::$widgets[ $id ] = $data;
				$file_path = plugin_dir_path( dirname( __FILE__ ) ) . '/' . $data['file'];
				
				if ( file_exists( $file_path ) ) {
					require_once $file_path;
				}
			}
		}

		/**
		 * @since 1.0.0
		 * @return mixed
		 */
		public static function get_all_widgets() {
			return self::$widgets;
		}

		/**
		 * Initilization all widgets.
		 * 
		 * @since 1.0.0
		 */
		protected function init() {
			require_once TRILISTING_DIR_PATCH . 'core/platform/templates/class-trilisting-widgets-template-1.php';
			require_once TRILISTING_DIR_PATCH . 'core/platform/widgetitems/class-trilisting-widget-item-1.php';
			// widgets wp
			require_once TRILISTING_DIR_PATCH . 'core/platform/list-widgets/wp/class-trilisting-widgets-user-actions.php';
			require_once TRILISTING_DIR_PATCH . 'core/platform/list-widgets/wp/class-trilisting-widgets-taxonomy.php';
			require_once TRILISTING_DIR_PATCH . 'core/platform/list-widgets/wp/class-trilisting-widgets-fields-view.php';
			require_once TRILISTING_DIR_PATCH . 'core/platform/list-widgets/class-trilisting-widget-base-default.php';
			require_once TRILISTING_DIR_PATCH . 'core/platform/list-widgets/class-trilisting-widget-base-maps.php';
			require_once TRILISTING_DIR_PATCH . 'core/platform/list-widgets/class-trilisting-widget-base-special.php';
		}
	}

} // End if
