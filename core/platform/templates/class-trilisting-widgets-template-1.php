<?php

namespace TRILISTING;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Widget template.
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'trilisting_widgets_template_1' ) ) {

	class trilisting_widgets_template_1 {
		public $data = '';

		public function __construct( $args ) {
			$this->data = $args;
		}

		/**
		 * Ajax filter.
		 * 
		 * @return string
		 */
		public function get_ajax_filter() {
			$output    = '';
			$dropdown  = '';
			$mode      = $this->data['drop_down_mode'];
			if ( ! empty( $this->data['drop_down_items'] ) ) {
				//generate unique id for this pull down filter control
				$item_idx   = 0;
				$hide_class = '';
				$wrapper_id = "ac_ajax_filter_" . $this->data['widget_uid'];
				$line_css   = ' hidden';

				if ( 'full' == $mode ) {
					$line_css   = ' hidden-xs hidden-md visible-lg';
					$hide_class = ' hidden-lg';
				}

				$output .= '<div id="' . esc_attr( $wrapper_id ) . '" class="trilisting-title-tabs' . esc_attr( $line_css ) . '"><ul class="menu trilisting-title-line-filter" id="' . esc_attr( $wrapper_id ) . '_list">';
				foreach ( $this->data['drop_down_items'] as $item ) {
					$output   .= '<li class="trilisting-title-tab ac-ajax-filter-item-wrapper' . esc_attr( ( $item_idx == 0 ? ' active' : '' ) ) . '"><a id="' . esc_attr( \Trilisting_Widgets_Manager::generate_guid() ) . '" data-ac_filter_value="' . esc_attr( $item['id'] ) . '" data-ac_widget_id="' . esc_attr( $this->data['widget_uid'] ) . '" href="#" class="ac-ajax-filter-item">' . esc_html( $item['name'] ) . '</a></li>';
					$dropdown .= '<li class="trilisting-dropdown-filter-item ac-ajax-filter-item-wrapper' . esc_attr( ( $item_idx == 0 ? ' active' : '' ) ) . '"><a id="' . esc_attr( \Trilisting_Widgets_Manager::generate_guid() ) . '" data-ac_filter_value="' . esc_attr( $item['id'] ) . '" data-ac_widget_id="' . esc_attr( $this->data['widget_uid'] ) . '" href="#" class="ac-ajax-filter-item">' . esc_html( $item['name'] ) . '</a></li>';
					$item_idx++;
				}
				$output .= '</ul></div>';

				// generate dropdown filter for small screens/spaces
				$output .= '<div class="trilisting-title-dropdown-filter visible-xs' . esc_attr( $hide_class ) . '">';
				$output .= '<div class="an-dropdown-selected-filter" id="ac-filter__' . esc_attr( \Trilisting_Widgets_Manager::generate_guid() ) . '" data-ac_filter_value_selected="' . esc_attr( $this->data['drop_down_items'][0]['id'] ) . '" >' . esc_html( $this->data['drop_down_items'][0]['name'] ) . '</div>';
				$output .= '<i class="fas fa-angle-down"></i>';
				$output .= '<ul class="trilisting-title-dropdown-filter-list">';
				$output .= $dropdown;
				$output .= '</ul></div>';
			} // End if

			return $output;
		}

		public function get_sortby_filter() {
			$output = '';
			
			$list_sort = [
				esc_html__( 'By publish date', 'trilisting' )              => 'by_date',
				esc_html__( 'By modify date', 'trilisting' )               => 'by_mod_date',
				esc_html__( 'By post title', 'trilisting' )                => 'by_title',
				esc_html__( 'By post slug', 'trilisting' )                 => 'by_slug',
				esc_html__( 'By post views count', 'trilisting' )          => 'by_view_count',
				esc_html__( 'By comments count', 'trilisting' )            => 'by_comment_count',
				esc_html__( 'Random Posts', 'trilisting' )                 => 'by_random',
				esc_html__( 'Random posts Today', 'trilisting' )           => 'random_today',
				esc_html__( 'Random posts from last 7 Day', 'trilisting' ) => 'random_7_day',
			];

			$output .= '<div class="trilisting-sort-wrap"><select name="trilisting_search_sort" class="trilisting-posts-sort" autocomplete="off">';
			$output .= '<option value="" selected="selected">' . esc_html__( 'Sort by', 'trilisting' ) . '</option>';
			foreach( $list_sort as $sort_value => $key ) {
				$output .= '<option value="' . esc_attr( $key ) . '">' . esc_attr( $sort_value ) . '</option>';
			}
			$output .= '</select></div>';

			return $output;
		}

		/**
		 * Title widgets.
		 * 
		 * @return string
		 */
		public function get_title() {
			$custom_title = $this->data['atts']['custom_title'];
			$custom_url   = $this->data['atts']['custom_url'];

			extract( $this->data['atts'] );

			if ( empty( $custom_title ) ) {
				if ( empty( $this->data['drop_down_items'] ) ) {
					return '';
				}
				$custom_title = 'widget title';
			}
			// custom title
			$output = '';
			$ajax   = $this->get_ajax_filter();
			$aclass = '';

			if ( '' != $ajax ) {
				$aclass = ' trilisting-title-relative';
			}

			$output .= '<div class="trilisting-block-title">';
			$output .= '<h4 class="trilisting-title' . esc_attr( $aclass ) . '">';
			
			if ( ! empty( $custom_url ) ) {
				$output .= '<a href="' . esc_url( $custom_url ) . '">' . esc_html( $custom_title ) . '</a>';
			} else {
				$output .= esc_html( $custom_title );
			}

			$output .= '</h4>';
			$output .= '<div class="trilisting-title-ajax-filter trilisting-title-relative">';
			$output .= $ajax;
			$output .= '</div>';
			$output .= '</div>';

			return $output;
		}

		/**
		 * @param $key
		 * @return string
		 */
		protected function get_att_value( $key ) {
			if ( isset( $this->data['atts'][ $key ] ) ) {
				return $this->data['atts'][ $key ];
			} else {
				return '';
			}
		}

	}

} // End if
