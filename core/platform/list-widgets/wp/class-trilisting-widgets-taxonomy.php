<?php
/**
 * Widget API: Trilisting_Taxonomies class
 */

require_once TRILISTING_PATH_TEMPLATES . 'view-taxonomy/default.tpl.php';

add_action( 'widgets_init', 'trilisting_widget_taxonomies' );
if ( ! function_exists( 'trilisting_widget_taxonomies' ) ) {
	function trilisting_widget_taxonomies() {
		return register_widget( 'Trilisting_Taxonomies' );
	}
}

if ( ! class_exists( 'Trilisting_Taxonomies' ) ) {
	/**
	 * Core class used to implement a Taxonomies widget.
	 *
	 * @since 3.0.0
	 *
	 * @see WP_Widget
	 */
	class Trilisting_Taxonomies extends WP_Widget {
		/**
		 * Sets up a new Taxonomy widget instance.
		 *
		 * @since 3.0.0
		 */
		public function __construct() {
			$widget_ops = [
				'classname'                   => 'trilisting_taxonomies',
				'description'                 => esc_html__( 'A list or dropdown of taxonomies.' ),
				'customize_selective_refresh' => true,
			];
			parent::__construct( 'trilisting_taxonomies', $name = '[triListing] Custom Taxonomies', $widget_ops );
		}

		/**
		 * Outputs the content for the current Taxonomy widget instance.
		 *
		 * @since 3.0.0
		 *
		 * @staticvar bool $first_dropdown
		 *
		 * @param array $args     Display arguments including 'before_title', 'after_title',
		 *                       'before_widget', and 'after_widget'.
		 * @param array $instance Settings for the current Taxonomy widget instance.
		 */
		public function widget( $args, $instance ) {
			extract( $args );

			// Widget options
			$title         = '';
			$this_taxonomy = '';
			if ( array_key_exists( 'title', $instance ) ) {
				$title = apply_filters( 'widget_title', $instance['title'] );
			}

			if ( array_key_exists( 'taxonomy', $instance ) ) {
				$this_taxonomy = $instance['taxonomy'];
			}

			$hierarchical = ! empty( $instance['hierarchical'] ) ? '1' : '0';
			$inv_empty    = ! empty( $instance['empty'] ) ? '0' : '1'; // invert to go from UI's "show empty" to WP's "hide empty"
			$showcount    = ! empty( $instance['count'] ) ? '1' : '0';

			if ( array_key_exists( 'orderby' ,$instance ) ){
				$orderby = $instance['orderby'];
			} else {
				$orderby = 'count';
			}
			if ( array_key_exists( 'view_template' ,$instance ) ){
				$view_template = $instance['view_template'];
			} else {
				$view_template = 'default';
			}
			if ( array_key_exists( 'ascdsc', $instance ) ){
				$ascdsc = $instance['ascdsc'];
			} else {
				$ascdsc = 'desc';
			}
			if ( array_key_exists( 'exclude', $instance ) ){
				$exclude = $instance['exclude'];
			} else {
				$exclude = '';
			}
			if ( array_key_exists( 'include', $instance ) ){
				$include = $instance['include'];
			} else {
				$include = '';
			}
			if ( array_key_exists( 'childof', $instance ) ){
				$childof = $instance['childof'];
			} else {
				$childof = '';
			}
			if ( array_key_exists( 'dropdown', $instance ) ){
				$dropdown = $instance['dropdown'];
			} else {
				$dropdown = false;
			}
			if ( array_key_exists( 'show_option_none', $instance ) ) {
				$show_option_none = $instance['show_option_none'];
			}
			if ( array_key_exists( 'depth', $instance ) ) {
				$depth = $instance['depth'];
			} else {
				$depth = 0;
			}
			if ( array_key_exists( 'number', $instance ) ) {
				$number = $instance['number'];

				if ( 0 > $number ) {
					$number = null;
				}
			} else {
				$number = null;
			}

			$tax = $this_taxonomy;
			echo $before_widget;
			echo '<div class="trilisting-list-taxonomy-widget trilisting-widget-taxonomy-template-' . esc_attr( $view_template ) . ' trilisting-list-taxonomy-widget-' . esc_attr( $tax ) . '">';

			do_action( 'trilisting/action/widgets/taxonomy_before' );

			if ( $title ) {
				echo '<h4 class="trilisting-title trilisting-widget-title">'. $title . '</h4>';
			}

			if ( ! is_archive() ) {
				$hierarchical = 0;
			}

			$args_tax = [
				'echo'            => 1,
				'depth'           => $depth,
				'show_option_all' => false,
				'taxonomy'        => $tax,
				'order'           => $ascdsc,
				'exclude'         => $exclude,
				'include'         => $include,
				'child_of'        => $childof,
				'show_count'      => $showcount,
				'hide_empty'      => $inv_empty,
				'hierarchical'    => $hierarchical,
			];
			$args_tax = apply_filters( 'trilisting/widgets/taxonomy', $args_tax );

			if ( $dropdown ) {
				$taxonomy_object = get_taxonomy( $tax );
				$walker = new \TRILISTING\Walker\Trilisting_Taxonomy_Dropdown_Walker();

				$args = wp_parse_args( [
						'id'               => 'trilisting-widget-' . esc_attr( $tax ),
						'show_option_none' => '',
						'orderby'          => 'RANDOM()',
						'hide_if_empty'    => true,
						'walker'           => $walker,
						'name'             => $taxonomy_object->query_var,
					],
					$args_tax
				);

				echo '<form action="' . get_bloginfo( 'url' ) . '" method="get">';
				wp_dropdown_categories( $args );
				echo '<input type="submit" value="ok" /></form>';
			} else {
				if ( 'default' !== $view_template ) {
					\TRILISTING\Trilisting_Helpers::get_manager_template( 'view-taxonomy/' . $view_template . '.tpl.php', '', true );
				}

				$walker_class_tpml = 'Trilisting_Category_Walker_' . $view_template;
				if ( class_exists( $walker_class_tpml ) ) {
					$walker_list = new $walker_class_tpml;
				} else {
					$walker_list = new Trilisting_Category_Walker_default();
				}

				$args = wp_parse_args( [
						'orderby'            => $orderby,
						'style'              => 'list',
						'use_desc_for_title' => 1,
						'title_li'           => '',
						'show_option_none'   => ( isset( $show_option_none ) ) ? '' : esc_html__( 'No Categories', 'trilisting' ),
						'number'             => $number,
						'walker'             => $walker_list,
					],
					$args_tax
				);

				$custom_class = apply_filters( 'trilisting/widgets/taxonomy/custom_class/' . $view_template, '' );

				echo '<ul class="trilisting-taxonomies-list trilisting-widget-' . esc_attr( $tax ) . ' ' . esc_attr( $custom_class ) . '">';
				wp_list_categories( $args );
				echo '</ul>';
			} // End if

			do_action( 'trilisting/action/widgets/taxonomy_after' );

			echo '</div>';
			echo $after_widget;
		}

		/**
		 * Handles updating settings for the current Taxonomies widget instance.
		 *
		 * @since 3.0.0
		 *
		 * @param array $new_instance New settings for this instance as input by the user via
		 *							WP_Widget::form().
		 * @param array $old_instance Old settings for this instance.
		 * @return array Updated settings to save.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			$instance['title']         = strip_tags( $new_instance['title'] );
			$instance['taxonomy']      = strip_tags( $new_instance['taxonomy'] );
			$instance['number']        = $new_instance['number'];
			$instance['orderby']       = $new_instance['orderby'];
			$instance['view_template'] = $new_instance['view_template'];
			$instance['ascdsc']        = $new_instance['ascdsc'];
			$instance['exclude']       = $new_instance['exclude'];
			$instance['include']       = isset( $new_instance['include'] ) ? $new_instance['include'] : '';
			$instance['childof']       = $new_instance['childof'];
			$instance['hierarchical']  = ! empty( $new_instance['hierarchical'] ) ? 1 : 0;
			$instance['empty']         = ! empty( $new_instance['empty'] ) ? 1 : 0;
			$instance['count']         = ! empty( $new_instance['count'] ) ? 1 : 0;
			$instance['dropdown']      = ! empty( $new_instance['dropdown'] ) ? 1 : 0;

			return $instance;
		}

		/**
		 * Outputs the settings form for the Taxonomies widget.
		 *
		 * @since 3.0.0
		 *
		 * @param array $instance Current settings.
		 */
		public function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, [
					'title'         => '',
					'taxonomy'      => 'category',
					'number'        => null,
					'orderby'       => 'name',
					'ascdsc'        => 'desc',
					'exclude'       => '',
					'include'       => '',
					'childof'       => '',
					'hierarchical'  => true,
					'count'         => true,
					'empty'         => false,
					'dropdown'      => false,
					'view_template' => 'default',
				]
			);

			extract( $instance );
			$title        = sanitize_text_field( $instance['title'] );
			$showcount    = isset( $instance['count'] ) ? (bool) $instance['count'] : false;
			$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
			$empty        = isset( $instance['empty'] ) ? (bool) $instance['empty'] : false;
			$dropdown     = isset( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
			?>

			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'trilisting' ); ?></label>
				<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" class="widefat" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php esc_html_e( 'Select Taxonomy:', 'trilisting' ); ?></label>
				<select name="<?php echo $this->get_field_name( 'taxonomy' ); ?>" id="<?php echo $this->get_field_id('taxonomy'); ?>" class="widefat">
					<?php
					$args = [
						'public'   => true,
						'_builtin' => false,
					];

					$taxonomies = [ 'category', 'post_tag', 'post_format' ];
					$taxonomies = array_merge( $taxonomies, get_taxonomies( $args, 'names' ,'and' ) );

					foreach ( $taxonomies as $taxonomy_name ) : ?>
						<option value="<?php echo $taxonomy_name; ?>"<?php if ( $taxonomy_name == $taxonomy ) { echo 'selected="selected"'; } ?>><?php echo esc_attr( $taxonomy_name ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<div class="trilisting-taxonomies-widget-options">	
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>"<?php checked( $showcount ); ?> />
				<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php esc_html_e( 'Show Post Counts', 'trilisting' ); ?></label><br />

				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name( 'hierarchical' ); ?>"<?php checked( $hierarchical ); ?> />
				<label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php esc_html_e( 'Show Hierarchy', 'trilisting' ); ?></label><br/>

				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'empty' ); ?>" name="<?php echo $this->get_field_name( 'empty' ); ?>"<?php checked( $empty ); ?> />
				<label for="<?php echo $this->get_field_id('empty'); ?>"><?php esc_html_e( 'Show Empty Terms', 'trilisting' ); ?></label><br/>

				<!-- Template -->
				<p>
					<label for="<?php echo $this->get_field_id( 'view_template' ); ?>"><?php echo esc_html__( 'View template:' ); ?></label>
					<select name="<?php echo $this->get_field_name( 'view_template' ); ?>" id="<?php echo $this->get_field_id( 'view_template' ); ?>" class="widefat" >
						<?php
						$output_template_select = [];
						$select_default         = ( 'default' == $view_template ) ? 'selected="selected"' : '';
						$output_template_select['default'] = '<option value="default"' . $select_default . '>' . esc_html__( 'Default', 'trilisting' ) . '</option>';

						$output_template_select = apply_filters( 'trilisting/filter/widgets/admin/taxonomy_templates', $output_template_select, $view_template );

						if ( ! empty( $output_template_select ) ) {
							foreach ( $output_template_select as $key => $select_value ) {
								echo $select_value;
							}
						}
						?>
					</select>
				</p>

				<!-- Order By: -->
				<p>
					<label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php echo esc_html__( 'Order By:' ); ?></label>
					<select name="<?php echo $this->get_field_name( 'orderby' ); ?>" id="<?php echo $this->get_field_id( 'orderby' ); ?>" class="widefat" >
						<option value="ID" <?php if ( 'ID' == $orderby ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'ID', 'trilisting' ); ?></option>
						<option value="name" <?php if ( 'name' == $orderby ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Name', 'trilisting' ); ?></option>
						<option value="slug" <?php if ( 'slug' == $orderby ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Slug', 'trilisting' ); ?></option>
						<option value="count" <?php if ( 'count' == $orderby ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Count of listings', 'trilisting' ); ?></option>
						<option value="term_group" <?php if ( 'term_group' == $orderby ) { echo 'selected="selected"'; } ?>><?php esc_html_e( 'Term Group', 'trilisting' ); ?></option>
					</select>
				</p>

				<!-- asc/desc -->
				<p>
					<label><input type="radio" name="<?php echo $this->get_field_name( 'ascdsc' ); ?>" value="asc" <?php if ( 'asc' == $ascdsc ) { echo 'checked'; } ?>/><?php esc_html_e( 'Ascending', 'trilisting' ); ?></label><br/>
					<label><input type="radio" name="<?php echo $this->get_field_name( 'ascdsc' ); ?>" value="desc" <?php if ( 'desc' == $ascdsc ) { echo 'checked'; } ?>/><?php esc_html_e( 'Descending', 'trilisting' ); ?></label>
				</p>

				<!-- Include (comma-separated list of ids to include) -->
				<p>
					<label for="<?php echo $this->get_field_id( 'include' ); ?>"><?php esc_html_e( 'Include (comma-separated list of ids to include' ); ?></label><br/>
					<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'include' ); ?>" value="<?php echo $include; ?>" />
				</p>

				<!-- Exclude (comma-separated list of ids to exclude) -->
				<p>
					<label for="<?php echo $this->get_field_id( 'exclude' ); ?>"><?php esc_html_e( 'Exclude (comma-separated list of ids to exclude)', 'trilisting' ); ?></label><br/>
					<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'exclude' ); ?>" value="<?php echo $exclude; ?>" />
				</p>

				<!-- Number (comma-separated list of ids to number) -->
				<p>
					<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php esc_html_e( 'Count category', 'trilisting' ); ?></label><br/>
					<input type="number" class="widefat" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo $number; ?>" />
				</p>

				<!-- Only Show Children of (category id) -->
				<p>
					<label for="<?php echo $this->get_field_id( 'childof' ); ?>"><?php esc_html_e( 'Only Show Children of (category id)', 'trilisting' ); ?></label><br/>
					<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'childof' ); ?>" value="<?php echo $childof; ?>" />
				</p>
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'dropdown' ); ?>" name="<?php echo $this->get_field_name( 'dropdown' ); ?>"<?php checked( $dropdown ); ?> />
				<label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php esc_html_e( 'Display as Dropdown', 'trilisting' ); ?></label>
			</div>
	<?php 
		}

	} // class Trilisting_Taxonomies
} // End if
