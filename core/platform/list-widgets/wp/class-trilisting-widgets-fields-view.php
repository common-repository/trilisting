<?php
/**
 * Widget API: Trilisting_Fields_View class
 */

add_action( 'widgets_init', 'trilisting_widget_fields_view' );
if ( ! function_exists( 'trilisting_widget_fields_view' ) ) {
	function trilisting_widget_fields_view() {
		return register_widget( 'Trilisting_Fields_View' );
	}
}

if ( ! class_exists( 'Trilisting_Fields_View' ) ) {
	/**
	 * Core class used to implement a Fields View widget.
	 *
	 * @since 3.0.0
	 *
	 * @see WP_Widget
	 */
	class Trilisting_Fields_View extends WP_Widget {
		/**
		 * Sets up acf fields.
		 */
		public $acf_reg_fields = [];
		/**
		 * Sets up a new Fields View widget instance.
		 *
		 * @since 3.0.0
		 */
		public function __construct() {
			$widget_ops = [
				'classname'                   => 'trilisting_fields_view',
				'description'                 => esc_html__( 'A list or dropdown of fields view.' ),
				'customize_selective_refresh' => true,
			];
			parent::__construct( 'trilisting_fields_view', $name = '[triListing] Custom Fields View', $widget_ops );
			$this->get_acf_fields();
		}

		/**
		 * Get ACF fields.
		 *
		 * @since 1.0.0
		 */
		public function get_acf_fields() {
			$this->acf_reg_fields = trilisting_get_group_post_fields();

			return $this->acf_reg_fields;
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
			$title = $_custom_class = $hide_label = '';
			if ( array_key_exists( 'title', $instance ) ) {
				$title = apply_filters( 'widget_title', $instance['title'] );
			}

			if ( array_key_exists( 'hide_label', $instance ) ) {
				$hide_label = $instance['hide_label'];
			}

			if ( is_single() && isset( $instance['post_types_choice'] ) && ( $instance['post_types_choice'] == get_post_type() ) ) {
				$output       = '';
				$output_field = '';

				$output = $before_widget;
				if ( ! empty( $instance['custom_class'] ) ) {
					$_custom_class = str_replace( ',', ' ', preg_replace( '/\s+/', '', $instance['custom_class'] ) );
				}
				$output .= '<div class="trilisting-list-fields-view-widget ' . esc_attr( $_custom_class ) . '">';

				if ( $title ) {
					$output .= $before_title . $title . $after_title;
				}

				$hide_class = '';
				if ( $hide_label ) {
					$hide_class = ' trilisting-hide-label';
				}
				
				$output .= '<div class="trilisting-fields-view-inner' . $hide_class . '">';

				if ( isset( $instance['post_fields'] ) && ! empty( $instance['post_fields'] ) ) {
					foreach ( $instance['post_fields'] as $field_slug ) {
						$output_field .= TRILISTING\Trilisting_Acf_Fields::get_field( $field_slug );
					}
				}

				// Fields
				$output .= $output_field;

				$output .= '</div>';
				$output .= '</div>';
				$output .= $after_widget;

				if ( ! empty( $output_field ) ) {
					echo $output;
				}

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

			$instance['title']             = strip_tags( $new_instance['title'] );
			$instance['post_types_choice'] = strip_tags( $new_instance['post_types_choice'] );
			$instance['custom_class']      = $new_instance['custom_class'];
			$instance['hide_label']        = ! empty( $new_instance['hide_label'] ) ? 1 : 0;
			$instance['post_fields']       = isset( $new_instance['post_fields'] ) ? $new_instance['post_fields'] : [];

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
					'title'             => '',
					'custom_class'      => '',
					'post_types_choice' => '',
					'post_fields'       => [],
					'hide_label'        => false,
				]
			);

			extract( $instance );
			$title       = sanitize_text_field( $instance['title'] );
			$post_fields = isset( $instance['post_fields'] ) ? $instance['post_fields'] : [];
			$hide_label = isset( $instance['hide_label'] ) ? (bool) $instance['hide_label'] : false;
			?>
			<div class="trilisting-fields-view-admin">
				<p>
					<span><?php esc_html_e( 'Displays fields only on a single template', 'trilisting' ); ?></span>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'trilisting' ); ?></label>
					<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" class="widefat" />
				</p>
				<p>
					<label for="<?php echo $this->get_field_id( 'post_types_choice' ); ?>"><?php esc_html_e( 'Select Post Type:', 'trilisting' ); ?></label>
					<select name="<?php echo $this->get_field_name( 'post_types_choice' ); ?>" id="<?php echo $this->get_field_id( 'post_types_choice' ); ?>" class="widefat trilisting-fv-select-post-type">
						<?php
						//post types
						$post_types = TRILISTING\Trilisting_Helpers::get_wordpress_data( 'post_types', [ '_builtin' => false ] );

						foreach ( $post_types as $key => $type ) : ?>
							<option value="<?php echo $key; ?>"<?php if ( $key == $post_types_choice ) { echo 'selected="selected"'; } ?>><?php echo esc_attr( $type ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<p>
					<?php
					//acf fields
					foreach ( $this->acf_reg_fields as $key_post_type => $fields_value ) :
						?>
						<label for="<?php echo $this->get_field_id( 'post_fields' ); ?>"><?php esc_html_e( 'Select Fields:', 'trilisting' ); ?></label>
						<select class="widefat trilisting-fields-group post_fields_<?php echo esc_attr( $key_post_type ); ?>" name="<?php echo $this->get_field_name( 'post_fields' ) . '[]'; ?>" id="<?php echo $this->get_field_id( 'post_fields' ); ?>" multiple>

						<?php
						foreach ( $fields_value as $field ) {
							?>
							<option value="<?php echo $field['name']; ?>"<?php echo in_array( $field['name'], $post_fields ) ? 'selected="selected"' : ''; ?>><?php echo esc_attr( $field['label'] ); ?></option>
							<?php
						}
						?>

						</select>
						<?php
					endforeach;
					?>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id( 'custom_class' ); ?>"><?php esc_html_e( 'Custom class:', 'trilisting' ); ?></label>
					<input id="<?php echo $this->get_field_id( 'custom_class' ); ?>" name="<?php echo $this->get_field_name( 'custom_class' ); ?>" type="text" value="<?php echo esc_attr( $custom_class ); ?>" class="widefat" />
					<div class="ac-widget-info"><?php esc_html_e( 'Enter here the custom class separated by commas (ex: container,row)', 'trilisting' ); ?></div>
				</p>
				<p>
					<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'hide_label' ); ?>" name="<?php echo $this->get_field_name( 'hide_label' ); ?>"<?php checked( $hide_label ); ?> />
					<label for="<?php echo $this->get_field_id( 'hide_label' ); ?>"><?php esc_html_e( 'Hide fields label', 'trilisting' ); ?></label>
				</p>
				<script>
					//fields view select
					(function($){
						$(document).ready(function () {
							function select_post_type() {
								$('.trilisting-fields-view-admin .trilisting-fields-group').hide();
								var id_post_type = "<?php echo $this->get_field_id( 'post_types_choice' ); ?>",
									selector_val =  $('.trilisting-fields-view-admin #' + id_post_type);
								if (selector_val) {
									var select_name = '.post_fields_' + selector_val.val();
									$('.trilisting-fields-view-admin').find(select_name).toggle()
								}
							}
							select_post_type();
							$('.trilisting-fields-view-admin .trilisting-fv-select-post-type').change(function () {
								$('.trilisting-fields-view-admin .trilisting-fields-group').hide();
								if ($(this).val()) {
									var select_name = '.post_fields_' + $(this).val();
									$('.trilisting-fields-view-admin').find(select_name).toggle();
								}
							});
						});
					})(jQuery);
				</script>
			</div>
		<?php
		}
	}
} // End if
