<?php

namespace TRILISTING\MediaButtons;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Support media buttons.
 */
class Trilisting_Media_Buttons {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 */
	public static $instance;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 * 
	 * @static
	 * 
	 * @since 1.0.0
	 * @return self Main instance.
	 */
	public static function init() {
		if ( self::$instance == NULL ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
	}

	/**
	 * Add hooks.
	 * 
	 * @since 1.0.0
	 */
	public function add_hooks() {
		global $pagenow;
		if ( current_user_can( 'edit_theme_options' ) && in_array( $pagenow, [ 'edit.php', 'post.php', 'post-new.php', 'widgets.php' ] ) ) {
			add_action( 'admin_footer', [ $this, 'render' ], 12 );
			add_filter( 'media_buttons', [ $this, 'register_button' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		}
	}

	public function register_button() {
		echo '<a class="button trilisting-media-button trilisting-insert-shortcode-button hidden" href="javascript:void(0)">' . esc_html__( 'Add triListing Shortcodes', 'trilisting' ) . '</a>';
	}

	/**
	 * Enqueue scripts for post admin pages.
	 * 
	 * @since   1.0.0
	 */
	public function enqueue_scripts() {
		$plugin_version	= \TRILISTING\Trilisting_Info::VERSION;

		wp_enqueue_style(
			TRILISTING_PREFIX . 'buttons',
			TRILISTING_URL . 'core/media-buttons/assets/css/media-buttons.min.css',
			[],
			$plugin_version,
			'all'
		);

		wp_enqueue_style(
			'chosen',
			TRILISTING_URL . 'core/media-buttons/assets/libs/chosen/chosen.min.css'
		);

		wp_enqueue_style(
			'magnific-popup',
			TRILISTING_URL . 'core/media-buttons/assets/libs/magnific-popup/magnific-popup.min.css'
		);

		wp_enqueue_script( 'chosen' );

		wp_enqueue_script(
			'magnific-popup',
			TRILISTING_URL . 'core/media-buttons/assets/libs/magnific-popup/magnific-popup.min.js',
			[ 'jquery' ],
			$plugin_version,
			true
		);

		wp_enqueue_script(
			TRILISTING_PREFIX . 'buttons',
			TRILISTING_URL . 'core/media-buttons/assets/js/buttons.min.js',
			[ 'jquery' ],
			$plugin_version,
			true
		);
	}

	/**
	 * Get fields.
	 * 
	 * @param $name
	 * @param $attr
	 * @return null|string
	 */
	private function get_fields( $name, $attr ) {
		$output  = null;
		$desc    = ( isset( $attr['desc'] ) && ! empty( $attr['desc'] ) ) ? '<p class="des">' .  $attr['desc'] . '</p>' : '';
		$default = isset( $attr['default'] ) ? ( 'data-default-value="' . $attr['default'] . '"' ) : '';

		$output .= '<div class="option-item-wrap ' . esc_attr( $name ) . '">';
		switch ( $attr['type'] ) {
			case 'checkbox':
				$output .= '<div class="label"><label for="' . $name . '"><strong>' . esc_attr( $attr['title'] ) . ': </strong></label></div>';
				$output .= '<div class="content"> <input name="' . $name . '" type="checkbox" class="' . $name . '" id="' . $name . '" ' . $default . ' />' . $desc . '</div> ';
				break;
			case 'select':
				$output .= '<div class="label"><label for="' . $name . '"><strong>' . esc_attr( $attr['title'] ) . ': </strong></label></div>';
				$output .= '<div class="content"><select class="trilisting-select-input" id="' . $name . '" name="' . $name . '"' . $default . '>';
				$values = $attr['values'];
				foreach ( $values as $key => $value ) {
					$output .= '<option value="' . $key . '">' . esc_attr( $value ) . '</option>';
				}
				$output .= '</select>' . $desc . '</div>';
				break;
			case 'multiselect':
				$output .= '<div class="label"><label for="' . $name . '"><strong>' . esc_attr( $attr['title'] ) . ': </strong></label></div>';
				$output .= '<div class="content"><select class="trilisting-multiselect-input" multiple="multiple" id="' . $name . '" name="' . $name . '">';
				$values = $attr['values'];
				foreach ( $values as $k => $v ) {
					$output .= '<option value="' . $k . '">' . $v . '</option>';
				}
				$output .= '</select>' . $desc . '</div>';
				break;
			case 'textarea':
				$output .= '<div class="label"><label for="' . $name . '"><strong>' . esc_attr( $attr['title'] ) . ': </strong></label></div>';
				$output .= '<div class="content"><textarea id="' . $name . '" name="' . $name . '"></textarea> ' . $desc . '</div>';
				break;
			case 'text':
			default:
				$output .= '<div class="label"><label for="' . $name . '"><strong>' . esc_attr( $attr['title'] ) . ': </strong></label></div>';
				$output .= '<div class="content"><input ' . $default . ' id="' . $name . '" type="text" name="' . $name . '" value="" />' . $desc . '</div>';
				break;
		}
		$output .= '</div>';
		return $output;
	}

	public function render() {
		$search_forms = \TRILISTING\Trilisting_Helpers::get_wordpress_data( 'posts', [
			'numberposts' => -1,
			'orderby'     => 'date',
			'order'       => 'DESC',
			'post_type'   => 'trilisting-search',
		] );

		$image_sizes = trilisting_get_image_sizes();

		$list_shortcodes['trilisting_login_register'] = [
			'type'  => 'custom',
			'title' => esc_html__( 'Login/Register', 'trilisting' ),
		];

		$list_shortcodes['trilisting_dashboard'] = [
			'type'  => 'custom',
			'title' => esc_html__( 'Dashboard', 'trilisting' ),
		];

		$list_shortcodes['trilisting_gallery'] = [
			'type'  => 'custom',
			'title' => esc_html__( 'Gallery', 'trilisting' ),
			'attr'  => [
				'size' => [
					'type'   => 'select',
					'title'  => esc_html__( 'Image size', 'trilisting' ),
					'values' => $image_sizes,
				],
				'style' => [
					'type'   => 'select',
					'title'  => esc_html__( 'Style', 'trilisting' ),
					'values' => [
						'img'        => esc_html__( 'HTML tag <img>', 'trilisting' ),
						'background' => esc_html__( 'Css background-image', 'trilisting' ),
					],
					'default' => 'img',
				],
				'class' => [
					'type'  => 'text',
					'title' => esc_html__( 'Custom Class', 'trilisting' ),
				],
			],
		];

		$list_shortcodes['trilisting_saved'] = [
			'type'  => 'custom',
			'title' => esc_html__( 'Saved', 'trilisting' ),
		];

		$list_shortcodes['trilisting_search_form'] = [
			'type'  => 'custom',
			'title' => esc_html__( 'Search', 'trilisting' ),
			'attr'  => [
				'id' => [
					'type'   => 'select',
					'title'  => esc_html__( 'Form', 'trilisting' ),
					'values' => $search_forms,
				],
			],
		];

		$list_shortcodes['trilisting_submit_form'] = [
			'type'  => 'custom',
			'title' => esc_html__( 'Submit Form', 'trilisting' ),
			'attr'  => [
				'post_types' => [
					'type'   => 'select',
					'title'  => esc_html__( 'Post type', 'trilisting' ),
					'values' => \TRILISTING\Trilisting_Helpers::get_wordpress_data( 'post_types', [ '_builtin' => false ] ),
				],
			]
		];

		$list_shortcodes['trilisting_user_form'] = [
			'type'  => 'custom',
			'title' => esc_html__( 'User Profile', 'trilisting' ),
		];
		$list_shortcodes = apply_filters( 'trilisting/media_buttons/list_shortcodes', $list_shortcodes );

		$html_options = null;

		$output = '<div id="trilisting-input-shortcode" class="mfp-hide mfp-with-anim"><div class="shortcode-content"><div id="trilisting-media-btn-header">';
		$output .= '<div class="label"><strong>' . esc_html__( 'triListing Shortcodes', 'trilisting' ) . '</strong></div><div class="content">';
		$output .= '<select id="trilisting-shortcodes" data-placeholder="' . esc_html__( 'Choose a shortcode', 'trilisting' ) . '"><option></option>';

		foreach ( $list_shortcodes as $shortcode => $options ) {
			if ( false !== strpos( $shortcode, 'header' ) ) {
				$output .= '<optgroup label="' . esc_attr( $options['title'] ) . '">';
			} else {
				$output .= '<option value="' . $shortcode . '">' . esc_attr( $options['title'] ) . '</option>';
				$html_options .= '<div class="shortcode-options" id="trilisting-options-' . $shortcode . '" data-name="' . $shortcode . '" data-type="' . $options['type'] . '">';

				if ( ! empty( $options['attr'] ) ) {
					$index = 0;
					foreach ( $options['attr'] as $name => $attr ) {
						if ( 0 == $index % 2 ) {
							$html_options .= '<div class="two-option-wrap">';
						}

						$html_options .= $this->get_fields( $name, $attr );
						$index++;

						if ( 0 == $index % 2 || $index >= count( $options['attr'] ) ) {
							$html_options .= '</div>';
							$html_options .= '<div class="clearfix"></div>';
						}
					}
				}
				$html_options .= '</div>';
			} // End if
		} // End foreach

		$output .= '</select></div><div class="clearfix"></div></div>';
		echo $output . $html_options;
		$output_docs = '<div class="trilisting-shortcode-docs"><i class="fas fa-info-circle"></i>' . esc_html__( 'For additional information please ', 'trilisting' ) . '<a href="https://trilisting.com/trilisting-plugin-documentation/#trilisting-docs-title-e4b29bc" target="_blank">' . esc_html__( 'read the documentation.', 'trilisting' ) . '</a></div>';
		echo '<a class="btn" id="trilisting-insert-shortcode">' . esc_html__( 'Insert', 'trilisting' ) . '</a>' . $output_docs . '</div></div>';
	}
}
