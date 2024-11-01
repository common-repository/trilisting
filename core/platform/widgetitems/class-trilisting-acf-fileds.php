<?php

namespace TRILISTING;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class acf fields.
 *
 * @since   1.0.0
 */
class Trilisting_Acf_Fields {
	/**
	 * ACF text field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function text_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/text_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-text-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-text-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			$output .= Trilisting_Helpers::do_action( 'trilisting/fields/text/before_html', $atts );
			$output .= '<span class="trilisting-text-value trilisting-value">' . esc_attr( $atts['value'] ) . '</span>';
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * ACF textarea field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function textarea_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/textarea_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-textarea-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-textarea-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			$output .= '<p class="trilisting-textarea-value trilisting-value">' . wp_kses( $atts['value'], 'post' ) . '</p>';
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * ACF select field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function select_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/select_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-select-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-select-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			$output .= Trilisting_Helpers::do_action( 'trilisting/fields/select/before_html', $atts );
			$output .= '<span class="trilisting-select-value trilisting-value">' . esc_attr( $atts['value'] ) . '</span>';
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * ACF checkbox field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function checkbox_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/checkbox_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-checkbox-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-checkbox-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			$output .= Trilisting_Helpers::do_action( 'trilisting/fields/checkbox/before_html', $atts );

			if ( is_array( $atts['value'] ) && ! empty( $atts['value'] ) ) {
				foreach ( $atts['value'] as $value ) {
					if ( is_array( $value ) ) {
						$output .= '<span class="trilisting-checkbox-value trilisting-value">' . esc_attr( $value['label'] ) . '</span>';
					} else {
						$output .= '<span class="trilisting-checkbox-value trilisting-value">' . esc_attr( $value ) . '</span>';
					}
				}
			} else {
				$output .= '<span class="trilisting-checkbox-value trilisting-value">' . esc_attr( $atts['value'] ) . '</span>';
			}

			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * ACF radio field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function radio_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/radio_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-radio-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-radio-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			$output .= Trilisting_Helpers::do_action( 'trilisting/fields/radio/before_html', $atts );

			if ( is_array( $atts['value'] ) && ! empty( $atts['value'] ) ) {
				$output .= '<span class="trilisting-radio-value trilisting-value">' . esc_attr( $atts['value']['label'] ) . '</span>';
			} else {
				$output .= '<span class="trilisting-radio-value trilisting-value">' . esc_attr( $atts['value'] ) . '</span>';
			}

			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * ACF true_false field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function true_false_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/true_false_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-true_false-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-true_false-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			$output .= Trilisting_Helpers::do_action( 'trilisting/fields/true_false/before_html', $atts );
			$output .= '<span class="trilisting-true_false-value trilisting-value">' . esc_attr( $atts['value'] ) . '</span>';

			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * ACF number field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function number_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/number_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-number-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-number-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			$output .= Trilisting_Helpers::do_action( 'trilisting/fields/number/before_html', $atts );
			$output .= '<span class="trilisting-number-value trilisting-value">' . esc_attr( $atts['value'] ) . '</span>';
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * ACF email field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function email_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/email_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-email-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-email-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			$output .= '<span class="trilisting-email-value trilisting-value">' . esc_attr( $atts['value'] ) . '</span>';
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * ACF password field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function password_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/password_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-password-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-password-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			$output .= '<span class="trilisting-password-value trilisting-value">' . esc_attr( $atts['value'] ) . '</span>';
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * ACF password field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function font_awesome_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/font_awesome', $atts );

		if ( ! empty( $atts['value'] ) && ! is_object( $atts['value'] ) ) {
			$output .= '<span class="trilisting-font-awesome-value trilisting-value">' . $atts['value'] . '</span>';
		}

		return $output;
	}

	/**
	 * ACF range field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function range_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/range_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-range-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-range-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			$output .= '<span class="trilisting-range-value trilisting-value">' . esc_attr( $atts['value'] ) . '</span>';
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * ACF url field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function url_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/url_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-url-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-url-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			$output .= '<a href="' . esc_url( $atts['value'] ) . '" class="trilisting-url-value trilisting-value">' . esc_attr( $atts['label'] ) . '</a>';
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * ACF file field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function file_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/file_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-file-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';
			
			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-file-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			if ( is_array( $atts['value'] ) ) {
				$output .= '<a class="trilisting-file-value trilisting-value" href="' . esc_url( $atts['value']['url'] ) . '" target="_blank">';
				$output .= esc_attr( $atts['value']['title'] );
				$output .= '</a>';
			} elseif ( is_numeric( $atts['value'] ) ) {
				$attachment_file     = basename( get_attached_file( $atts['value'] ) );
				$attachment_file_url = wp_get_attachment_url( $atts['value'] );
				if ( ! empty( $attachment_file ) ) {
					$output .= '<a class="trilisting-file-value trilisting-value" href="' . esc_url( $attachment_file_url ) . '" target="_blank">';
					$output .= esc_attr( $attachment_file );
					$output .= '</a>';
				}
			} else {
				$output .= '<a class="trilisting-file-value trilisting-value" href="' . esc_url( $atts['value'] ) . '" target="_blank">';
				$output .= esc_attr( basename( $atts['value'] ) );
				$output .= '</a>';
			}


			$output .= '</div>';
		} // End if

		return $output;
	}

	/**
	 * ACF wysiwyg field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function wysiwyg_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/wysiwyg_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-wysiwyg-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-wysiwyg-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			$output .= wp_kses( $atts['value'], 'post' );
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * ACF oembed field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function oembed_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/oembed_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-oembed-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-oembed-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			$output .= '<div class="trilisting-oembed-value trilisting-value">' . wp_kses( $atts['value'], \Trilisting_Widgets_Platform::allowed_html( 'iframe' ) ) . '</div>';
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * ACF time picker field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function time_picker_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/time_picker_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-time-picker-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-time-picker-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			$output .= '<span class="trilisting-time-picker-value trilisting-value">' . esc_attr( $atts['value'] ) . '</span>';
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * ACF data picker field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function date_picker_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/date_picker_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-date-picker-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-date-picker-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			$output .= '<time class="trilisting-date-picker-value trilisting-value" datetime="' . esc_attr( $atts['value'] ) . '">' . esc_attr( $atts['value'] ) . '</time>';
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * ACF data time picker field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function date_time_picker_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/date_time_picker_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-date-time-picker-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-date-time-picker-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			$output .= '<time class="trilisting-date-time-picker-value trilisting-value" datetime="' . esc_attr( $atts['value'] ) . '">' . esc_attr( $atts['value'] ) . '</time>';
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * ACF user field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function user_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/user_field', $atts );

		if ( ! empty( $atts['value'] ) && is_array( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-user-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-user-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			$output .= '<a class="trilisting-user-field-name trilisting-user-field-link" href="' . esc_url( $atts['value']['user_url'] ) . '">';
			$output .= '<span class="trilisting-user-field-avatar">' . wp_kses( $atts['value']['user_avatar'], \Trilisting_Widgets_Platform::allowed_html( 'img' ) ) . '</span>';
			$output .= esc_attr( $atts['value']['display_name'] );
			$output .= '</a>';
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * ACF gallery field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function gallery_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/gallery_field', $atts );

		if ( ! empty( $atts['value'] ) && is_array( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-gallery-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-gallery-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			foreach ( $atts['value'] as $key => $image ) {
				$output .= self::image_field( $image );
			}
			$output .= '</div>';

		}

		return $output;
	}

	/**
	 * ACF page link field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function page_link_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/page_link_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-page-links-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-page-link-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			if ( is_array( $atts['value'] ) ) {
				foreach ( $atts['value'] as $link ) {
					$output .= '<a class="trilisting-page-link-field" href="' . esc_url( $link ) . '">' . esc_url( $link ) . '</a>';
				}
			} else {
				$output .= '<a href="' . esc_url( $atts['value'] ) . '" class="trilisting-page-link-field">' . esc_url( $atts['value'] ) . '</a>';
			}
			$output .= '</div>';
		} // End if

		return $output;
	}

	/**
	 * ACF link field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function link_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/link_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-link-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-link-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			if ( is_array( $atts['value'] ) ) {
				$atts_target = ( ! empty( $atts['value']['target'] ) ) ? 'target="' . esc_attr( $atts['value']['target'] ) . '"' : '';
				
				$output .= '<a class="trilisting-link-value trilisting-value" href="' . esc_url( $atts['value']['url'] ) . '" ' . $atts_target . '>';
				$output .= ! empty( $atts['value']['title'] ) ? esc_attr( $atts['value']['title'] ) : esc_url( $atts['value']['url'] );
				$output .= '</a>';
			} else {
				$output .= '<span class="trilisting-link-value trilisting-value">' . esc_attr( $atts['value'] ) . '</span>';
			}

			$output .= '</div>';
		} // End if

		return $output;
	}

    /**
	 * ACF image field.
	 * 
	 * @since 1.0.0
     * @param array $atts
     * @param string $tag
     * @param bool $background
     * @return string
     */
    public static function image_field( $atts = '', $tag = '', $background = false ) {
		$output = $image_size = '';
		$atts   = apply_filters( 'trilisting/filters/acf/image_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			if ( empty( $tag ) ) {
				$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-image-field-inner trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';
			}

			if ( is_array( $atts['value'] ) ) {
				if ( empty( $tag ) ) {
					$output .= '<figure class="trilisting-image-field">';
				}

				$image_size = apply_filters( 'trilisting/filters/acf/image_field/sizes', $image_size );
				if ( empty( $image_size ) ) {
					$image_size = 'medium_large';
				}

				if ( true === $background ) {
					$output .= '<div class="trilisting-image trilisting-image-background" style="background-image: url(' . esc_url( $atts['value']['sizes'][ $image_size ] ) . ')"></div>';
				} else {
					$output .= '<img class="trilisting-image" src="' . esc_url( $atts['value']['sizes'][ $image_size ] ) . '" width="' . esc_attr( $atts['value']['sizes'][ $image_size . '-width' ] ) . '" height="' . esc_attr( $atts['value']['sizes'][ $image_size . '-height' ] ) . '" alt="' . esc_attr( $atts['value']['alt'] ) . '" title="' . esc_attr( $atts['value']['title'] ) . '">';
				}

				if ( empty( $tag ) ) {
					if ( ! empty( $atts['value']['caption'] ) ) {
						$output .= '<figcaption class="trilisting-image-caption">' . esc_html( $atts['value']['caption']  ) . '</figcaption>';
					}
					$output .= '</figure>';
				}
			} elseif ( is_numeric( $atts['value'] ) ) {
				$atts_src = self::attachment_image_id( $atts['value'] );

				if ( $atts_src ) {
					$output .= '<img class="trilisting-image" src="' . esc_url( $atts_src ) . '" alt="">';
				}
			} else {
				$output .= '<img class="trilisting-image" src="' . esc_url( $atts['value'] ) . '" alt="">';
			} // End if

			if ( empty( $tag ) ) {
				$output .= '</div>';
			}
		} // End if

		return $output;
	}

	/**
	 * ACF google map field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @param int $post_id
	 * @return string
	 */
	public static function google_map_field( $atts = '', $post_id = '' ) {
		$output = '';

		if ( ! empty( $atts['value'] ) ) {
			$atts         = apply_filters( 'trilisting/filters/acf/google_map_field', $atts );
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );

			if ( is_single() ) {
				$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-map-wrapper' . $wrapper_atts['class'] . '">';
				$output .= '<a class="trilisting-map-direction" rel="noopener" href="https://maps.google.com/maps?q=' . str_replace( ' ', '+', urldecode( $atts['value']['address'] ) ) . '" target="_blank">' . apply_filters( 'trilisting/fields/maps/directions', '' ) . esc_html__( 'Get direction', 'trilisting' ) . '</a>';
				$output .= '<div class="trilisting-map trilisting-field-' . esc_attr( $atts['name'] ) . '">';
			}

			$post_id = ! empty( $post_id ) ? $post_id : get_the_ID();
			$data_maps_settings = [
				'post_id' =>  $post_id,
				'sticky'  =>  is_sticky( $post_id ),
				'name'    =>  $atts['name'],
				'lat'     =>  $atts['value']['lat'],
				'lng'     =>  $atts['value']['lng'],
			];

			$output .= '<div class="trilisting-marker" data-maps-settings=\'' . json_encode( $data_maps_settings ) . '\'></div>';

			if ( is_single() ) {
				$output .= '</div></div>';
			}
		} // End if

		return $output;
	}

	/**
	 * ACF group field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function group_field( $atts = '' ) {
		$output    = '';
		$has_empty = true;
		$atts      = apply_filters( 'trilisting/filters/acf/group_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );

			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-group-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			// a maximum of 2 levels of nesting
			foreach ( $atts['sub_fields'] as $sub_field ) {
				$output .= '<div class="trilisting-group-field-inner">';
				if ( isset( $sub_field['tril_hidden_label'] ) && ! $sub_field['tril_hidden_label'] ) {
					$output .= '<span class="trilisting-group-field-label trilisting-label">' . esc_attr( $sub_field['label'] ) . '</span>';
				}

				$output .= '<div class="trilisting-group-field-sub-wrap">';
				if ( 'group' === $sub_field['type'] && isset( $sub_field['sub_fields'] ) ) {
					foreach ( $sub_field['sub_fields'] as $sub_2_field ) {
						if ( ! isset( $sub_2_field['sub_fields'] ) ) {
							$sub_2_field['value'] = isset( $atts['value'][ $sub_field['name'] ][ $sub_2_field['name'] ] ) ? $atts['value'][ $sub_field['name'] ][ $sub_2_field['name'] ] : '';

							if ( ! empty( $sub_2_field['value'] ) ) {
								$has_empty = false;
								$method_field = $sub_2_field['type'] . '_field';
								if ( method_exists( 'TRILISTING\Trilisting_Acf_Fields', $method_field ) ) {
									$output .= Trilisting_Acf_Fields::$method_field( $sub_2_field );
								}

							}
						}
					} // End foreach
				} elseif ( ! isset( $sub_field['sub_fields'] ) ) {
					$has_empty    = false;
					$method_field = $sub_field['type'] . '_field';
					$sub_field['value'] = isset( $atts['value'][ $sub_field['name'] ] ) ? $atts['value'][ $sub_field['name'] ] : '';

					if ( method_exists( 'TRILISTING\Trilisting_Acf_Fields', $method_field ) ) {
						$output .= Trilisting_Acf_Fields::$method_field( $sub_field );
					}
				} // End if

				$output .= '</div>'; // End .trilisting-group-sub-wrap
				$output .= '</div>'; // End .trilisting-group-inner
			} // End foreach
			
			$output .= '</div>'; // End .trilisting-group-field
		}

		if ( ! $has_empty ) {
			return $output;
		}

		return '';
	}

	/**
	 * ACF taxonomy field.
	 * 
	 * @since 1.0.0
	 * @param array $atts
	 * @return string
	 */
	public static function taxonomy_field( $atts = '' ) {
		$output = '';
		$atts   = apply_filters( 'trilisting/filters/acf/taxonomy_field', $atts );

		if ( ! empty( $atts['value'] ) ) {
			$wrapper      = isset( $atts['wrapper'] ) ? $atts['wrapper'] : '';
			$wrapper_atts = self::get_field_wrapper( $wrapper );
			$output .= '<div ' . $wrapper_atts['id'] . 'class="trilisting-taxonomy-field trilisting-field' . $wrapper_atts['class'] . ' trilisting-field-' . esc_attr( $atts['name'] ) . '">';

			if (
				! empty( $atts['label'] )
				&& isset( $atts['tril_hidden_label'] )
				&& ! $atts['tril_hidden_label']
			) {
				$output .= '<span class="trilisting-taxonomy-label trilisting-label">' . esc_attr( $atts['label'] ) . '</span>';
			}

			if ( is_array( $atts['value'] ) ) {
				foreach ( $atts['value'] as $term ) {
					$term_object = get_term( $term );
					if ( ! empty( $term_object ) ) {
						$output .= '<a class="trilisting-taxonomy-field-link" href="' . esc_url( get_term_link( $term ) ) . '">' . esc_attr( $term_object->name ) . '</a>';
					}
				}
			} else {
				$term_object = get_term( $atts['value'] );
				if ( ! empty( $term_object ) ) {
					$output .= '<a class="trilisting-taxonomy-field-link" href="' . esc_url( get_term_link( $atts['value'] ) ) . '">' . esc_attr( $term_object->name ) . '</a>';
				}
			}
			$output .= '</div>';
		} // End if

		return $output;
	}

	/**
	 * ACF get field.
	 * 
	 * @since 1.0.0
	 * @param string $meta_key
	 * @param int $post_id
	 * @return string
	 */
	public static function get_field( $meta_key = '', $post_id = '' ) {
		if ( empty( $post_id ) && empty( $meta_key ) ) {
			return '';
		}

		$output     = '';
		$field_atts = [];
		if ( function_exists( 'get_field_object' ) ) {
			$field_atts = get_field_object( $meta_key, $post_id );
		}

		if ( ! empty( $field_atts ) ) {
			$method_field = $field_atts['type'] . '_field';
			if ( method_exists( 'TRILISTING\Trilisting_Acf_Fields', $method_field ) ) {
				$output .= Trilisting_Acf_Fields::$method_field( $field_atts );
			}
		}

		return $output;
	}

	/**
	 * ACF wrapper.
	 * 
	 * @since 1.0.0
	 * @param array $wrapper
	 * @return array
	 */
	protected static function get_field_wrapper( $wrapper ) {
		$wrapper_arr = [
			'id'	=> '',
			'class'	=> '',
		];

		if ( ! empty( $wrapper['id'] ) ) {
			$wrapper_arr['id'] = 'id="' . esc_attr( $wrapper['id'] ) . '" ';
		}
		if ( ! empty( $wrapper['class'] ) ) {
			$wrapper_arr['class'] = ' ' . esc_attr( $wrapper['class'] );
		}

		return $wrapper_arr;
	}

	/**
	 * ACF attachment image.
	 * 
	 * @since 1.0.0
	 * @param int $attachment_id
	 * @param string $size
	 * @return bool
	 */
	protected static function attachment_image_id( $attachment_id, $size = 'medium' ) {
		$image = wp_get_attachment_image_src( $attachment_id, $size, false );
		return isset( $image['0'] ) ? $image['0'] : false;
	}

}
