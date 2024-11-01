<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Redux options
require_once TRILISTING_DIR_PATCH . 'core/platform/options/trilisting-widgets-options.php';
// Ajax pagination
require_once TRILISTING_DIR_PATCH . 'core/platform/class-trilisting-widgets-ajax-helper.php';
// Ajax maps
require_once TRILISTING_DIR_PATCH . 'core/maps/class-trilisting-ajax-maps.php';
// Generate widgets
require_once TRILISTING_DIR_PATCH . 'core/platform/widgets/class-trilisting-widgets-generate.php';
// Init widgets options
require_once TRILISTING_DIR_PATCH . 'core/platform/trilisting-widgets-manager.php';

if ( ! class_exists( 'Trilisting_Widgets_Platform' ) ) {
	/**
	 * Class platform.
	 */
	class Trilisting_Widgets_Platform {
		protected static $instance;
		protected $widget_manager;
		protected $image_sizes;
		protected $js_vars;

		/**
		 * @since 1.0.0
		 * @return Trilisting_Widgets_Platform
		 */
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = static::init();
			}

			return self::$instance;
		}

		/**
		 * @since 1.0.0 
		 */
		protected function init_js_vars() {
			$this->js_vars = [];

			// google map markers
			$markers = $this->get_map_marker();

			$protocol  = is_ssl() ? 'https://' : 'http://';
			$this->js_vars['ajax_url']       = admin_url( 'admin-ajax.php', $protocol );
			$this->js_vars['rtl_mode']       = 'false';
			$this->js_vars['rtl_mode']       = 'false';
			$this->js_vars['default_marker'] = $markers['default'];
			$this->js_vars['sticky_marker']  = $markers['sticky'];
		}

		/**
		 * Enqueue scripts for all admin pages.
		 * 
		 * @since 1.0.0
		 */
		public function load_theme_js() {
			$this->init_js_vars();
			$plugin_slug	= TRILISTING\Trilisting_Info::SLUG;
			$plugin_version	= TRILISTING\Trilisting_Info::VERSION;

			if ( function_exists( 'acf_form_head' ) ) {
				acf_form_head();
			}

			//css
			wp_deregister_style( 'font-awesome' );
			wp_enqueue_style(
				'font-awesome',
				TRILISTING_ASSETS_URL . 'libs/font-awesome5/css/all.min.css',
				[],
				'5.2.0'
			);

			wp_enqueue_style(
				'select2',
				TRILISTING_ASSETS_URL . 'libs/select2/css/select2.min.css',
				[],
				$plugin_version
			);

			wp_enqueue_style(
				$plugin_slug,
				TRILISTING_ASSETS_URL . 'css/frontend.css',
				[],
				$plugin_version
			);

			wp_enqueue_style( 'dashicons' );

			//js
			wp_enqueue_media();

			wp_enqueue_script(
				'imagesloaded',
				TRILISTING_ASSETS_URL . 'js/imagesloaded.js',
				[ 'jquery' ],
				$plugin_version,
				true
			);

			global $post;
			if (
				is_single()
				|| is_archive()
				|| (
					is_object( $post )
					&& ( has_shortcode( $post->post_content, 'trilisting_search' )
					|| has_shortcode( $post->post_content, 'trilisting_search_form' ) )
				)
			) {
				wp_enqueue_script(
					'google-markerclusterer',
					'https://cdnjs.cloudflare.com/ajax/libs/js-marker-clusterer/1.0.0/markerclusterer.js',
					[],
					$plugin_version,
					true
				);

				$google_maps_api_key = ! empty( $this->get_option( 'google_maps_api_key' ) ) ? '?key=' . $this->get_option( 'google_maps_api_key' ) . '&libraries=places' : '?libraries=places';
				wp_enqueue_script(
					'google-maps',
					'https://maps.googleapis.com/maps/api/js' . esc_attr( $google_maps_api_key ),
					[],
					$plugin_version,
					true
				);
			}

			wp_enqueue_script(
				'select2',
				TRILISTING_ASSETS_URL . 'libs/select2/js/select2.full.min.js',
				[ 'jquery' ],
				$plugin_version,
				true
			);

			wp_enqueue_script(
				$plugin_slug . '-frontend',
				TRILISTING_ASSETS_URL . 'js/frontend.js',
				[ 'jquery' ],
				$plugin_version,
				true
			);

			wp_enqueue_script(
				$plugin_slug,
				TRILISTING_ASSETS_URL . 'js/ajax-frontend.js',
				[ 'jquery' ],
				$plugin_version,
				true
			);

			wp_register_script(
				'jquery-validate',
				TRILISTING_ASSETS_URL . 'libs/validate/jquery.validate.min.js',
				[ 'jquery',  ],
				$plugin_version,
				true
			);

			wp_register_script(
				$plugin_slug . '-user-form',
				TRILISTING_ASSETS_URL . 'js/user-form.js',
				[ 'jquery',  ],
				$plugin_version,
				true
			);

			wp_register_script(
				$plugin_slug . '-account',
				TRILISTING_ASSETS_URL . 'js/account.js',
				[ 'jquery', 'jquery-validate' ],
				$plugin_version,
				true
			);

			// google reCAPTCHA
			if (
				true == get_trilisting_option( 'enable_recaptcha' )
				|| true == get_trilisting_option( 'enable_login_recaptcha' )
				|| true == get_trilisting_option( 'enable_recaptcha_reset_password' )
			) {
				$recaptcha_src = esc_url_raw( add_query_arg( [
					'render' => 'explicit',
					'onload' => 'trilisting_recaptcha_onload_callback',
				], 'https://www.google.com/recaptcha/api.js' ) );

				wp_register_script(
					'trilisting-google-recaptcha',
					$recaptcha_src,
					[],
					$plugin_version,
					true
				);
			}

			wp_localize_script(
				$plugin_slug . '-frontend',
				'trilisting_frontend_data',
				[ 'confirmDelete' => esc_html__( 'Are you sure you want to delete this post?', 'trilisting' ), ]
			);

			wp_localize_script(
				$plugin_slug . '-account',
				'trilisting_account_data',
				[
					'ajax_url' => admin_url( 'admin-ajax.php', 'relative' ),
					'loading'  => esc_html__( 'Sending user info, please wait...', 'trilisting' ),
				]
			);

			wp_localize_script(
				$plugin_slug,
				'ac_js_settings',
				array_merge( $this->js_vars, [
						'action_maps'      => 'ac_ajax_maps',
						'action_load_form' => 'trilisting_form_load',
						'nonce'            => wp_create_nonce( 'ajax-nonce' ),
					]
				)
			);
		}

		public function __construct() {
			//add image sizes
			$image_sizes = $this->get_image_sizes();

			if ( ! empty( $image_sizes ) ) {
				foreach ( $image_sizes as $id => $size ) {
					add_image_size( $id, $size['w'], $size['h'], $size['crop'] );
				}
			}

			//add ajax handlers
			add_action( 'wp_ajax_nopriv_ac_ajax_widget', [ 'Trilisting_Widgets_Ajax_Helper', 'on_ac_ajax_widget' ] );
			add_action( 'wp_ajax_ac_ajax_widget', [ 'Trilisting_Widgets_Ajax_Helper', 'on_ac_ajax_widget' ] );
			//add ajax handlers maps
			add_action( 'wp_ajax_nopriv_ac_ajax_maps', [ 'Trilisting_Ajax_Maps', 'markers_maps_content' ] );
			add_action( 'wp_ajax_ac_ajax_maps', [ 'Trilisting_Ajax_Maps', 'markers_maps_content' ] );
			//add ajax load form
			add_action( 'wp_ajax_nopriv_trilisting_form_load', [ 'Trilisting_Ajax_Load_Form', 'load_form' ] );
			add_action( 'wp_ajax_trilisting_form_load', [ 'Trilisting_Ajax_Load_Form', 'load_form' ] );
			//enqueue js
			add_action( 'wp_enqueue_scripts', [ $this, 'load_theme_js' ] );
		}

		/**
		 * @since 1.0.0
		 * @return mixed
		 */
		public function get_js_vars() {
			return $this->js_vars;
		}

		/**
		 * @since 1.0.0
		 */
		protected function register_shortcodes() {
			$widgets = $this->widget_manager->get_all_widgets();
			foreach ( $widgets as $id => $widget ) {
				call_user_func( 'add' . '_' . 'shortcode', $id, [ $this->widget_manager, 'init_widget_function' ] );
			}
		}

		/**
		 * @since 1.0.0
		 * @return mixed
		 */
		public function get_widget_manager() {
			return $this->widget_manager;
		}

		/**
		 * @since 1.0.0
		 * @return static
		 */
		protected static function init() {
			$instance = new static();

			require_once TRILISTING_DIR_PATCH . 'core/platform/class-trilisting-widgets-manager.php';
			$instance->widget_manager = new Trilisting_Widgets_Manager();

			require_once TRILISTING_DIR_PATCH . 'core/platform/list-widgets/widgets-init.php';
			$instance->register_shortcodes();

			return $instance;
		}

		/**
		 * @since 1.0.0
		 * @param $items
		 * @param $name
		 * @param null $default
		 * @param string $separator
		 * @return mixed|null
		 */
		protected function nested_val( $items, $name, $default = null, $separator = '.' ) {
			$path = explode( $separator, $name );
			$current = $items;
			foreach ( $path as $field ) {
				if ( is_object( $current ) && isset( $current->{$field} ) ) {
					$current = $current->{$field};
				} elseif ( is_array( $current ) && isset( $current[ $field ] ) ) {
					$current = $current[ $field ];
				} else {
					return $default;
				}
			}

			return $current;
		}

		/**
		 * @since 1.0.0
		 * @return array
		 */
		public static function allowed_html( $key ) {
			if ( 'iframe' == $key ) {
				return [
					'iframe' => [
						'src'             => true,
						'width'           => true,
						'height'          => true,
						'allow'           => true,
						'frameborder'     => true,
						'scrolling'       => true,
						'allowfullscreen' => true,
					],
				];
			} elseif ( 'img' == $key ) {
				return [
					'img' => [
						'src'    => true,
						'width'  => true,
						'height' => true,
						'id'     => true,
						'class'  => true,
						'sizes'  => true,
						'srcset' => true,
						'alt'    => true,
					],
				];
			}

			return [];
		}

		/**
		 * Generate image sizes.
		 * 
		 * @since 1.0.0
		 * @return array
		 */
		protected function get_image_sizes() {
			if ( is_array( $this->image_sizes ) && count( $this->image_sizes ) > 0 ) {
				return $this->image_sizes;
			}

			$this->image_sizes = [];

			$this->image_sizes['trilisting-widgets-thumb']['w']         = 168;
			$this->image_sizes['trilisting-widgets-thumb']['h']         = 124;
			$this->image_sizes['trilisting-widgets-thumb']['crop']      = true;

			$this->image_sizes['trilisting-widgets-default']['w']       = 440;
			$this->image_sizes['trilisting-widgets-default']['h']       = 352;
			$this->image_sizes['trilisting-widgets-default']['crop']    = true;

			$this->image_sizes['trilisting-widgets-featured-1']['w']    = 370;
			$this->image_sizes['trilisting-widgets-featured-1']['h']    = 245;
			$this->image_sizes['trilisting-widgets-featured-1']['crop'] = true;

			$this->image_sizes['trilisting-gallery-preview']['w']       = 170;
			$this->image_sizes['trilisting-gallery-preview']['h']       = 170;
			$this->image_sizes['trilisting-gallery-preview']['crop']    = true;

			$this->image_sizes['trilisting-map-1']['w']                 = 43;
			$this->image_sizes['trilisting-map-1']['h']                 = 43;
			$this->image_sizes['trilisting-map-1']['crop']              = true;

			return $this->image_sizes;
		}

		/**
		 * @since 1.0.0
		 * @param $key
		 * @param string $default
		 * @return mixed|null|string
		 */
		public function get_option( $key, $default = '' ) {
			global $trilisting_widgets_redux_option;
			if ( empty( $trilisting_widgets_redux_option ) ) {
				$trilisting_widgets_redux_option = get_option( TRILISTING\Trilisting_Info::OPTION_NAME );
			}
			$result = $default;
			if ( 0 < mb_strpos( $key, '.' ) ) {
				$result = $this->nested_val( $trilisting_widgets_redux_option, $key, $default );
			} else {
				$result = isset( $trilisting_widgets_redux_option[ $key ] ) ? $trilisting_widgets_redux_option[ $key ] : $default;
			}

			return $result;
		}

		/**
		 * @since 1.0.0
		 * @param $key
		 * @param string $default
		 * @return mixed|null|string
		 */
		public static function get_trilisting_option( $key, $default = '' ) {
			global $trilisting_widgets_redux_option;
			if ( empty( $trilisting_widgets_redux_option ) ) {
				$trilisting_widgets_redux_option = get_option( TRILISTING\Trilisting_Info::OPTION_NAME );
			}
			$result = isset( $trilisting_widgets_redux_option[ $key ] ) ? $trilisting_widgets_redux_option[ $key ] : $default;

			return $result;
		}

		/**
		 * @since 1.0.0
		 * @param $id
		 * @param $size
		 * @return bool|string
		 */
		public function get_image( $id, $size ) {
			if ( ! is_numeric( $id ) && 'http' == mb_strtolower( substr( $id, 0, 4 ) ) ) {
				global $wpdb;
				$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid='%s';", $id ) );
				if ( isset( $attachment[0] ) ) {
					$imgid = $attachment[0];

					return wp_get_attachment_image( $imgid, $size );
				}
			} else {
				$post_thumbnail_id = get_post_thumbnail_id( $id );
				if ( ! $post_thumbnail_id ) {
					return '';
				}
				$image = wp_get_attachment_image_src( $post_thumbnail_id, $size, false );

				return isset( $image['0'] ) ? $image['0'] : false;
			}
		}

		/**
		 * @since 1.0.0
		 * @param $layout
		 * @param string $alt
		 * @return string
		 */
		public function get_default_placeholder( $layout, $alt = '', $data_url = false ) {
			$result    = '';
			$def_thumb = $this->get_option( 'def_thumb_img' );

			if ( ! empty( $def_thumb['url'] ) ) {
				$def_thumb_url = esc_url( $def_thumb['url'] );
				$data_url	= $data_url ? 'data-url="' . esc_url( $def_thumb_url ) . '"' : '';

				$result = '<div class="trilisting-item-bg-img" ' . esc_url( $data_url ) . ' style="background-image: url(' . esc_url( $def_thumb_url ) . ');"></div>';
			}

			return $result;
		}

		/**
		 * @since 1.0.0
		 * @param $id
		 * @return string
		 */
		public function get_first_img_post( $id ) {
			$result       = '';
			$content_post = get_post( $id );
			$img          = [ 'src' => '' ];

			if ( isset( $content_post->post_content ) ) {
				if ( preg_match( "'<\s*img\s.*?src\s*=\s*
						([\"\'])?
						(?(1) (.*?)\\1 | ([^\s\>]+))'isx", $content_post->post_content, $matched ) ) {

					$img['src'] = esc_url( $matched[2] );
					$result = '<div class="trilisting-item-bg-img" data-url="' . esc_url( $img['src'] ) . '" style="background-image: url(' . esc_url( $img['src'] ) . ');"></div>';
				}

				return $result;
			}

			return $result;
		}

		/**
		 * @since 1.0.0
		 * @param $post_content
		 * @param $limit
		 * @return mixed|string
		 */
		public function excerpt( $post_content, $limit ) {
			$result		  = '';
			$post_content = preg_replace( '/\[caption(.*)\[\/caption\]/i', '', $post_content );
			$post_content = preg_replace( '/\[[^\]]*\]/', '', $post_content );
			$post_content = wp_kses( $post_content, 'strip' );

			if ( 0 < $limit ) {
				$result = $this->content_limit_words( $post_content, $limit, '&hellip;' );
			} else {
				$result = $post_content;
			}

			$result = str_replace( '&nbsp;', ' ', $result );

			return $result;
		}

		/**
		 * @since 1.0.0
		 * @param array $args
		 * @return mixed
		 */
		public static function get_categories( $args = [] ) {
			$default = [
				'type'         => 'post',
				'orderby'      => 'name',
				'order'        => 'ASC',
				'hide_empty'   => 0,
				'hierarchical' => 1,
				'taxonomy'     => 'category',
				'pad_counts'   => 1,
			];

			$args = wp_parse_args( apply_filters( 'trilisting/form/field_select_categories_args', $args ), $default );

			$categories		= get_categories( $args );
			$new_categories = [];
			$new_categories[ esc_html__( 'All categories', 'trilisting' ) ] = 0;

			foreach ( $categories as $cat ) {
				$new_categories[ $cat->name . ' [id: ' . $cat->cat_ID . ']' ] = $cat->slug;
			}

			return apply_filters( 'trilisting/form/field_select_categories', $new_categories );
		}

		/**
		 * @since 1.0.0
		 * @param array $args
		 * @return mixed
		 */
		public static function get_tags( $args = [] ) {
			$default = [
				'orderby'    => 'name',
				'order'      => 'ASC',
				'hide_empty' => 0,
				'pad_counts' => 1,
			];

			$args = wp_parse_args( apply_filters( 'trilisting/form/field_select_tags_args', $args ), $default );

			$tags     = get_tags( $args );
			$new_tags = [];
			$new_tags[ esc_html__( 'All tags', 'trilisting' ) ] = 0;
			foreach ( $tags as $tag ) {
				$new_tags[ $tag->name . ' [slug: ' . $tag->slug . ']' ] = $tag->slug;
			}

			return apply_filters( 'trilisting/form/field_select_tags', $new_tags );
		}

		/**
		 * @since 1.0.0
		 * @param $scope
		 * @param null $q
		 * @return string
		 */
		public function get_page_pagination( $scope, $q = null ) {
			global $wp_query;

			$options = $this->init_pagination_options();
			$query   = null;

			if ( $q != null ) {
				$request = $q->request;
				$query   = $q;
			} else {
				$request = $wp_query->request;
				$query   = $wp_query;
			}

			$posts_per_page	= intval( get_query_var( 'posts_per_page' ) );
			$paged          = intval( get_query_var( 'paged' ) );
			$numposts       = $query->found_posts;
			$max_page       = $query->max_num_pages;

			if ( empty( $paged ) || 0 == $paged ) {
				$paged = 1;
			}

			$params = [
				'pagination_opt'  => $options,
				'request'         => $request,
				'posts_per_page'  => $posts_per_page,
				'paged'           => $paged,
				'num_posts'       => $numposts,
				'max_page'        => $max_page,
			];

			return $this->get_numeric_pagination( $params, false, $query );
		}

		/**
		 * @since 1.0.0
		 * @return array
		 */
		protected function init_pagination_options() {
			$options = [];

			$options['pages_caption']     = esc_html__( 'Page %CURRENT_PAGE% of %TOTAL_PAGES%', 'trilisting' );
			$options['current_page_text'] = '%PAGE_NUMBER%';
			$options['page_text']         = '%PAGE_NUMBER%';
			$options['first_page_text']   = esc_html__( '1', 'trilisting' );
			$options['last_page_text']    = esc_html__( '%TOTAL_PAGES%', 'trilisting' );
			$options['next_page_text']    = '<i class="fa fa-angle-right"></i>';
			$options['prev_page_text']    = '<i class="fa fa-angle-left"></i>';
			$options['dotright_text']     = esc_html__( '...', 'trilisting' );
			$options['dotleft_text']      = esc_html__( '...', 'trilisting' );

			$options['pages_num']                    = 3;
			$options['always_show']                  = 0;
			$options['larger_page_numbers_num']      = 3;
			$options['larger_page_numbers_multiple'] = 1000;

			return $options;
		}

		/**
		 * @since 1.0.0
		 * @param $options
		 * @param bool $only
		 * @param null $qr
		 * @return string
		 */
		protected function get_numeric_pagination( $options, $only = false, $qr = null ) {
			global $wp_query, $wp_rewrite;

			$query = null;
			if ( null != $qr ) {
				$query = $qr;
			} else {
				$query = $wp_query;
			}

			$hide_class = '';
			$prev = $options['pagination_opt']['prev_page_text'];
			$next = $options['pagination_opt']['next_page_text'];
			if ( true == $only ) {
				$hide_class = ' hide-prev-next';
			}

			$query->query_vars['paged'] > 1 ? $current = $query->query_vars[ 'paged' ] : $current = 1;
			$pagination = [
				'base'      => add_query_arg( 'paged', '%#%' ),
				'format'    => '',
				'total'     => $query->max_num_pages,
				'current'   => $current,
				'prev_next' => true,
				'mid_size'  => 2,
				'end_size'  => 2,
				'next_text' => wp_kses( __( '<i class="fas fa-long-arrow-alt-right"></i>', 'trilisting' ), [
					'i' => [
						'class' => [],
					],
			 	] ),
				'prev_text' => wp_kses( __( '<i class="fas fa-long-arrow-alt-left"></i>', 'trilisting' ), [
					'i' => [
						'class' => [],
					],
				] ),
				'type'     => 'plain',
			];
			if ( $wp_rewrite->using_permalinks() ) {
				$pagination['base'] = user_trailingslashit( trailingslashit( remove_query_arg( 's', get_pagenum_link( 1 ) ) ) . 'page/%#%/', 'paged' );
			}

			if ( ! empty( $query->query_vars['s'] ) ) {
				$pagination['add_args'] = [ 's' => str_replace( ' ', '+', get_query_var( 's' ) ) ];
			}

			$links  = paginate_links( $pagination );
			$output = '<nav class="solocode-pagination solocode-numeric' . esc_attr( $hide_class ) . '">' . $links . '</nav>';

			return empty( $links ) ? '' : $output;
		}

		/**
		 * Crop text.
		 * 
		 * @since 1.0.0
		 * @param $string
		 * @param int $width
		 * @param string $append
		 * @return string
		 */
		public function limit_words( $string, $width = 100, $append = '&hellip;' ) {
			if ( 1 > $width ) {
				return $string;
			}

			if ( strlen( $string ) <= $width ) {
				return $string;
			}

			$parts      = preg_split( '/([\s\n\r]+)/U', $string, NULL, PREG_SPLIT_DELIM_CAPTURE );
			$word_count = count( $parts );
			$length     = 0;
			$last_word  = 0;
			for ( ; $last_word < $word_count; ++ $last_word ) {
				$length += mb_strlen( $parts[ $last_word ] );

				if ( $length > $width ) {
					break;
				}
			}

			if ( $length > $width ) {
				return trim( implode( array_slice( $parts, 0, $last_word ) ) ) . $append;
			} else {
				return implode( array_slice( $parts, 0, $last_word ) );
			}
		}

		/**
		 * @param $html
		 * @param int $width
		 * @param string $append
		 * @return mixed|string
		 */
		public function content_limit_words( $html, $width = 100, $append = '&hellip;' ) {
			if ( 1 > $width ) {
				return $html;
			}

			$html = preg_replace( '/\s+/', ' ', $html );

			if ( ( preg_match_all( '/( [^\<]* ) (<)? (?(2)	 (\/?) ([^\>]+ ) > )/isx', $html, $match ) ) && array_filter( $match[2] ) ) {
				if ( strlen( $html ) <= $width ) {
					return $html;
				}

				$break  = FALSE;
				$texts  = &$match[1];
				$tags   = &$match[4];
				$length = 0;
				$result = '';
				$open_tags_list = [];

				foreach ( $texts as $index => $text ) {
					$slice_size = $width - $length;
					if ( 1 > $slice_size ) {
						$break = TRUE;
						break;
					}

					$sc_text = $this->limit_words( $text, $slice_size, '' );
					$length += mb_strlen( $text );
					$result .= $sc_text;

					if ( $sc_text !== $text ) {
						$break = TRUE;
						break;
					}

					$tag_data = $tags[ $index ];
					$tag_data = explode( ' ', $tag_data, 2 );
					$tag      = &$tag_data[0];
					$atts     = isset( $tag_data[1] ) ? ' ' . $tag_data[1] : '';
					$tag_open = empty( $match[3][ $index ] );

					if ( $tag_open ) {
						$open_tags_list[] = $tag;
						if ( $tag ) {
							$result .= '<' . $tag . $atts . '>';
						}
					} else {
						do {
							$last_tag_open = array_pop( $open_tags_list );
							$result .= '</' . $last_tag_open . '>';
						} while( $last_tag_open && $last_tag_open !== $tag );
					}
				} // End foreach

				do {
					if ( $last_tag_open = array_pop( $open_tags_list ) ) {
						$result .= '</' . $last_tag_open . '>';
					}
				} while( $last_tag_open );

				if ( $break ) {
					$result .= $append;
				}

				return $result;
			} else {
				return $this->limit_words( $html, $width, $append );
			} // End if
		}

		protected function get_attachment_image_src( $attach_id, $size = 'trilisting-map-1' ) {
			if ( ! empty( $attach_id ) ) {
				$imsge_src = wp_get_attachment_image_src(
					$attach_id,
					apply_filters( 'trilisting/maps/marker/attachment_size', $size )
				);
				return isset( $imsge_src[0] ) ? $imsge_src[0] : '';
			}

			return;
		}

		protected function get_map_marker() {
			// google maps markers
			$custom_default_marker = $this->get_trilisting_option( 'custom_default_marker_image' );
			if ( isset( $custom_default_marker['id'] ) && ! empty( $custom_default_marker['id'] ) ) {
				$default_marker = $this->get_attachment_image_src( $custom_default_marker['id'] );
			} else {
				$default_marker = $this->get_trilisting_option( 'maps_default_presets_markers' );

				switch ( $default_marker ) {
					case '1' :
						$default_marker = TRILISTING_ASSETS_URL . 'img/markers/marker-default-1.png';
						break;
					case '2' :
						$default_marker = TRILISTING_ASSETS_URL . 'img/markers/marker-default-2.png';
						break;
					default:
						$sticky_marker = '';
						break;
				}
			}

			$sticky_custom_marker = $this->get_trilisting_option( 'custom_sticky_marker_image' );
			if ( isset( $sticky_custom_marker['id'] ) && ! empty( $sticky_custom_marker['id'] ) ) {
				$sticky_marker = $this->get_attachment_image_src( $sticky_custom_marker['id'] );
			} else {
				$sticky_marker  = $this->get_trilisting_option( 'maps_sticky_presets_markers' );

				switch ( $sticky_marker ) {
					case '1' :
						$sticky_marker = TRILISTING_ASSETS_URL . 'img//markers/marker-featured-1.png';
						break;
					case '2' :
						$sticky_marker = TRILISTING_ASSETS_URL . 'img//markers/marker-featured-2.png';
						break;
					default:
						$sticky_marker = '';
						break;
				}
			}

			return [
				'default' => $default_marker,
				'sticky'  => $sticky_marker,
			];
		}
	}

} // End if
