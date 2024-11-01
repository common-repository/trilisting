<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/* Check if Class Exists. */
if ( ! class_exists( 'Trilisting_Widgets_Generate' ) ) {
	/**
	 * Class td_block_widget - used to create widgets from our blocks.
	 * AUTOLOAD STATUS: cannot be autoloaded because WordPress needs to know at all times what widgets are registered
	 */
	class Trilisting_Widgets_Generate extends WP_Widget {
		public $widget_id = '';

		protected $platform;
		protected $widget_man;

		private $data_array;
		private $default_params_array;

		/**
		 * overwrite the default WordPress constructor.
		 * 
		 * @since 1.0.0
		 */
		public function __construct() {

			$this->platform   = Trilisting_Widgets_Platform::instance();
			$this->widget_man = $this->platform->get_widget_manager();
			$this->data_array = $this->widget_man->get_widget_data( $this->widget_id );

			$widget_ops = [
				'classname'     => 'trilisting_widget',
				'description'   => '[triListing] ' . $this->data_array['options']['name'],
			];

			/**
			 * overwrite the widget settings, we emulate the WordPress settings. Before WP 4.3 we called the old php4 constructor again :(
			 * @see \WP_Widget::__construct
			 */
			$id_base         = $this->data_array['options']['base'] . '_widget';
			$name            = '[triListing] ' . $this->data_array['options']['name'];
			$widget_options  = $widget_ops;
			$control_options = [];

			$this->id_base     = strtolower($id_base);
			$this->name        = $name;
			$this->option_name = 'widget_' . $this->id_base;
			
			$this->widget_options = wp_parse_args( $widget_options, [
					'classname' => $this->option_name,
				]
			);
			$this->control_options = wp_parse_args( $control_options, [
					'id_base' => $this->id_base,
				]
			);

			$this->default_params_array = $this->build_default_values();
		}

		/**
		 * build the default values array.
		 * 
		 * @since 1.0.0
		 * @return array
		 */
		private function build_default_values() {
			$temp_array = [];
			if ( ! empty( $this->data_array['options']['params'] ) ) {
				foreach ( $this->data_array['options']['params'] as $param ) {
					if ( 'dropdown' == $param['type'] ) {
						$temp_array[ $param['param_name'] ] = '';
					} else {
						$temp_array[ $param['param_name'] ] = $param['value'];
					}
				}
			}
			return $temp_array;
		}

		/**
		 * @since 1.0.0
		 * @param $instance
		 */
		public function form($instance ) {
			$instance = wp_parse_args( (array) $instance, $this->default_params_array );

			if ( ! empty( $this->data_array['options']['params'] ) ) {
				foreach ( $this->data_array['options']['params'] as $param ) {
					switch ( $param['type'] ) {

						case 'textarea_html':
							?>
							<p>
								<label for="<?php echo esc_attr( $this->get_field_id( $param['param_name'] ) ); ?>"><?php echo esc_html( $param['heading'] ); ?></label>
								<textarea  class="widefat" name="<?php echo esc_attr( $this->get_field_name( $param['param_name'] ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( $param['param_name'] ) ); ?>" cols="30" rows="10"><?php echo esc_textarea( $instance[$param['param_name']]); ?></textarea>

								<div class="td-wpa-info">
									<?php echo esc_attr( $param['description'] ); ?>
								</div>
							</p>
							<?php
							break;

						case 'textfield':
							if ( 'custom_title' == $param['param_name'] ) {
								$field_id = $this->get_field_id('custom-title');
							} else {
								$field_id = $this->get_field_id( $param['param_name'] );
							}

							?>
							<p>
								<label for="<?php echo esc_attr( $this->get_field_id( $param['param_name'] ) ); ?>"><?php echo esc_html( $param['heading'] ); ?></label>
								<input class="widefat" id="<?php echo esc_attr( $field_id ); ?>"
									name="<?php echo esc_attr( $this->get_field_name( $param['param_name'] ) ); ?>" type="text"
									value="<?php echo esc_attr( $instance[ $param['param_name'] ] ); ?>" />

								<div class="ac-widget-info">
									<?php echo esc_html( $param['description'] ); ?>
								</div>
							</p>
							<?php
							break;

						case 'dropdown':
							?>
							<p>
								<label for="<?php echo esc_attr( $this->get_field_id( $param['param_name'] ) ); ?>"><?php echo esc_html( $param['heading'] ); ?></label>
								<select name="<?php echo esc_attr( $this->get_field_name( $param['param_name'] ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( $param['param_name'] ) ); ?>" class="widefat">
									<?php
										foreach ( $param['value'] as $param_name => $param_value ) {
											?>
											<option value="<?php echo esc_attr( $param_value ); ?>"<?php selected( $instance[ $param['param_name'] ], $param_value ); ?>><?php echo esc_html( $param_name ); ?></option>
											<?php
										}
									?>
								</select>

							<div class="ac-widget-info">
								<?php
								if ( isset( $param['description'] ) ) {
									echo esc_html( $param['description'] );
								}
								?>
							</div>
							</p>
							<?php
							break;
					} // End switch
				} // End foreach
			} // End if
		}

		/**
		 * Update the settings of the widget.
		 * 
		 * @since 1.0.0
		 * @param array $new_instance
		 * @param array $old_instance
		 * @return array
		 */
		public function update( $new_instance, $old_instance) {
			$instance = $old_instance;
			foreach ( $this->default_params_array as $param_name => $param_value ) {
				if ( isset( $new_instance[ $param_name ] ) ) {
					$instance[ $param_name ] = $new_instance[ $param_name ];
				}
			}
			return $instance;
		}

		/**
		 * render the widget.
		 * 
		 * @since 1.0.0
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance) {
			/**
			 * add the Trilisting_Widgets_Generate class to the block via the short code atts
			 */
			if ( ! empty( $instance['class'] ) ) {
				$instance['class'] =  $instance['class'] . ' Trilisting_Widgets_Generate';
			} else {
				$instance['class'] = 'Trilisting_Widgets_Generate';
			}

			$widget_class = $this->widget_id;
			$widget	= new $widget_class('widget');

			if ( ! empty( $instance['content'] ) ) {
				echo $widget->render($instance, $instance['content']);
			} else {
				echo $widget->render($instance);
			}
		}
	}
} // End if
