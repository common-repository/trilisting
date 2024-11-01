<?php

add_action( 'widgets_init', 'trilisting_widget_user_actions' );
if ( ! function_exists( 'trilisting_widget_user_actions' ) ) {
	function trilisting_widget_user_actions() {
		return register_widget( 'Trilisting_Widgets_User_Actions' );
	}
}

if ( ! class_exists( 'Trilisting_Widgets_User_Actions' ) ) {
	/**
	 * Core class used to implement a Fields View widget.
	 *
	 * @since 3.0.0
	 *
	 * @see WP_Widget
	 */
	class Trilisting_Widgets_User_Actions extends WP_Widget {
		/**
		 * Sets up a new Fields View widget instance.
		 *
		 * @since 3.0.0
		 */
		public function __construct() {
			$widget_ops = [
				'classname'                   => 'trilisting_widgets_user_actions',
				'description'                 => esc_html__( 'Edit listing and save listing buttons.' ),
				'customize_selective_refresh' => true,
			];
			parent::__construct( 'trilisting_widgets_user_actions', $name = '[triListing] User action links', $widget_ops );
		}

		/**
		 * Outputs the content for the current Fields View widget instance.
		 *
		 * @since 3.0.0
		 *
		 * @staticvar bool $first_dropdown
		 *
		 * @param array $args     Display arguments including 'before_title', 'after_title',
		 *                       'before_widget', and 'after_widget'.
		 * @param array $instance Settings for the current Fields View widget instance.
		 */
		public function widget( $args, $instance ) {
			extract( $args) ;

			// Widget options
			$title = $save = $edit = '';
			if ( array_key_exists( 'title', $instance ) ) {
				$title = apply_filters( 'widget_title', $instance['title'] );
			}
			if ( array_key_exists( 'save', $instance ) ) {
				$save = (bool) $instance['save'];
			}
			if ( array_key_exists( 'edit', $instance ) ) {
				$edit = (bool) $instance['edit'];
			}

			if ( is_single() && ( $edit || $save ) ) {

				echo $before_widget;
				echo '<div class="trilisting-actions-widget trilisting-user-actions">';

					if ( $title ) {
						echo $before_title . $title . $after_title;
					}

					echo '<div class="trilisting-actions-inner-widgets">';

					// Edit button
					if ( $edit ) {
						echo trilisting_link_edit_listing();
					}

					// Saved button
					if ( $save && 1 == get_trilisting_option( 'enable_saved_listing' ) ) {
						echo trilisting_get_saved_html( get_the_ID() );
					}

					echo '</div>';

				echo '</div>';
				echo $after_widget;

			} // End if
		}

		/**
		 * Handles updating settings for the current Fields View widget instance.
		 *
		 * @since 3.0.0
		 *
		 * @param array $new_instance New settings for this instance as input by the user via WP_Widget::form().
		 * @param array $old_instance Old settings for this instance.
		 * @return array Updated settings to save.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			$instance['title'] = strip_tags( $new_instance['title'] );
			$instance['save']  = ! empty( $new_instance['save'] ) ? 1 : 0;
			$instance['edit']  = ! empty( $new_instance['edit'] ) ? 1 : 0;

			return $instance;
		}
		
		/**
		 * Outputs the settings form for the Fields View widget.
		 *
		 * @since 3.0.0
		 *
		 * @param array $instance Current settings.
		 */
		public function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, [
					'title' => '',
					'save'  => true,
					'edit'  => true,
				]
			);

			extract( $instance );
			$title = sanitize_text_field( $instance['title'] );
			$save  = isset( $instance['save'] ) ? (bool) $instance['save'] : true;
			$edit  = isset( $instance['edit'] ) ? (bool) $instance['edit'] : true;
			?>
			<div class="trilisting-fields-view-admin">
				<!-- Title -->
				<p>
					<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'trilisting' ); ?></label>
					<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" class="widefat" />
				</p>
				<!-- Edit -->
				<p>
					<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'edit' ); ?>" name="<?php echo $this->get_field_name( 'edit' ); ?>"<?php checked( $edit ); ?> />
					<label for="<?php echo $this->get_field_id( 'edit' ); ?>"><?php esc_html_e( 'Show edit', 'trilisting' ); ?></label><br/>
				</p>
				<!-- Save -->
				<p>
					<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'save' ); ?>" name="<?php echo $this->get_field_name( 'save' ); ?>"<?php checked( $save ); ?> />
					<label for="<?php echo $this->get_field_id( 'save' ); ?>"><?php esc_html_e( 'Show save', 'trilisting' ); ?></label><br/>
				</p>
			</div>
		<?php
		}
	}
} // End if
