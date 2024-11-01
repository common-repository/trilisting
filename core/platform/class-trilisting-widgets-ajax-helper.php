<?php

/* Check if Class Exists. */
if ( ! class_exists( 'Trilisting_Widgets_Ajax_Helper' ) ) {
	/**
	 * Ajax Helper.
	 */
	class Trilisting_Widgets_Ajax_Helper {
		/**
		 * @since 1.0.0
		 * @param string $ajax_parameters
		 * @return string
		 */
		public static function on_ac_ajax_widget( $ajax_parameters = '' ) {
			$is_ajax = false;
			if ( empty( $ajax_parameters ) ) {
				$is_ajax = true;
				$ajax_parameters = [
					'atts'          => '',	// original block atts
					'sort_posts'    => '',  // sort by_date, by_title etc.
					'column_number' => 0,	// should not be 0 (1 - 2 - 3)
					'current_page'  => '',	// the current page of the block
					'widget_id'     => '',	// widget uid
					'widget_type'   => '',	// the type of the widget / widget class
					'filter_value'  => '',	// the id for this specific filter type. The filter type is in the atts
				];
				
				if ( ! empty( $_POST['atts'] ) ) {
					$ajax_parameters['atts'] = json_decode( stripslashes( $_POST['atts'] ), true ); //current widget args
				}
				if ( ! empty( $_POST['sortby_posts'] ) ) {
					$ajax_parameters['atts']['sortby'] = $_POST['sortby_posts'];
				}
				if ( ! empty( $_POST['column_number'] ) ) {
					$ajax_parameters['column_number'] = $_POST['column_number']; //the block is on x columns
				}
				if ( ! empty( $_POST['current_page'] ) ) {
					$ajax_parameters['current_page'] = $_POST['current_page'];
				}
				if ( ! empty( $_POST['widget_id'] ) ) {
					$ajax_parameters['widget_id'] = $_POST['widget_id'];
				}
				if ( ! empty( $_POST['widget_type'] ) ) {
					$ajax_parameters['widget_type'] = $_POST['widget_type'];
				}
				if ( ! empty( $_POST['filter_value'] ) ) {
					$ajax_parameters['filter_value'] = $_POST['filter_value']; //the new id filter
				}
				if ( ! empty( $_POST['taxonomy_name'] ) ) {
					$ajax_parameters['atts']['category_ids'] = $_POST['taxonomy_name']; //the new id filter
				}
			} // End if

			if ( isset( $ajax_parameters['atts']['ac_ajax_filter_type'] ) ) {
				// changed filter
				switch ( $ajax_parameters['atts']['ac_ajax_filter_type'] ) {
					case 'ac_category_by_ids_filter':
						// filter by category
						if ( ! empty( $ajax_parameters['filter_value'] ) ) {
							$ajax_parameters['atts']['category_ids'] = $ajax_parameters['filter_value'];
							unset( $ajax_parameters['atts']['category_id'] );
						}
						break;
					case 'ac_author_by_ids_filter':
						if ( ! empty( $ajax_parameters['filter_value'] ) ) {
							$ajax_parameters['atts']['author_ids'] = $ajax_parameters['filter_value'];
						}
						break;
					case 'ac_tag_by_ids_filter':
						if ( ! empty( $ajax_parameters['filter_value'] ) ) {
							$ajax_parameters['atts']['tag_ids'] = $ajax_parameters['filter_value'];
						}
						break;
				}
			} // End if

			// get posts
			$query  = &Trilisting_Widgets_Manager::get_wp_query( $ajax_parameters['atts'], $ajax_parameters['current_page'] );
			$widget = Trilisting_Widgets_Platform::instance()->get_widget_manager()->get_widget_instance( $ajax_parameters['widget_type'] );
			$widget->set_widget_atts( $ajax_parameters['atts'] );
			$output = $widget->widget_content( $query->posts, $ajax_parameters['column_number'] );

			//pagination
			$hide_prev = false;
			$hide_next = false;
			if ( 1 == $ajax_parameters['current_page'] ) {
				$hide_prev = true;
			}

			if ( ! empty( $ajax_parameters['atts']['offset'] ) && ! empty( $ajax_parameters['atts']['post_limit'] ) && ( 0 != $ajax_parameters['atts']['post_limit'] ) ) {
				if ( $ajax_parameters['current_page'] >= ceil( ( $query->found_posts - $ajax_parameters['atts']['offset'] ) / $ajax_parameters['atts']['post_limit'] ) ) {
					$hide_next = true; //hide link on last page
				}
			} elseif ( $ajax_parameters['current_page'] >= $query->max_num_pages ) {
				$hide_next = true; //hide link on last page
			}

			$result = [
				'data'      => $output,
				'widget_id' => $ajax_parameters['widget_id'],
				'hide_prev' => $hide_prev,
				'hide_next' => $hide_next,
			];

			if ( true === $is_ajax ) {
				die( json_encode( $result ) );
			} else {
				return json_encode( $result );
			}
		}
	}
} // End if
