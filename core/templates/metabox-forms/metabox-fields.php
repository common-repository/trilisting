<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$fields = [
	'search'     => esc_html__( 'Search', 'trilisting' ),
	'location'   => esc_html__( 'Location', 'trilisting' ),
	'post_types' => esc_html__( 'Post types', 'trilisting' ),
];
$fields = array_merge( $fields, \TRILISTING\Trilisting_Helpers::get_wordpress_data( 'taxonomies' ) );
?>

<div class="tril-search-form tril-search-form-fields">

	<!-- Header -->
	<ul class="tril-head">
		<li class="tril-order"><?php esc_html_e( 'Sort', 'trilisting' ) ?></li>
		<li class="tril-search-by-col"><?php esc_html_e( 'Search by', 'trilisting' ) ?></li>
		<li class="tril-field-type-col"><?php esc_html_e( 'Field Type', 'trilisting' ) ?></li>
		<li class="tril-field-heading-col"><?php esc_html_e( 'Heading', 'trilisting' ) ?></li>
	</ul>

	<?php while ( $mb->have_fields_and_multi( 'field' ) ) : ?>
		<?php $mb->the_group_open(); ?>

		<div class="tril-fields-list-wrap">

			<!-- Field -->
			<div class="tril-handle">
				<ul class="tril-handle-list">
					<li class="tril-order">
						<span class="tril-order-count dashicons-before dashicons-move"></span>
					</li>
					<li class="tril-search-by-col">
						<a class="tril-search-by-field-col" href="#" title="Edit"><?php echo ! empty( $mb->get_the_value( 'search_by' ) ) ? esc_attr( $mb->get_the_value( 'search_by' ) ) : esc_html__( '(No value)', 'trilisting' ); ?></a>
						<div class="tril-row-options">
							<a href="#" class="tril-edit-fields" title="Edit"><?php esc_html_e( 'Edit', 'trilisting' ) ?></a>
							<a href="#" tilte="Delete" class="dodelete tril-delete-fields"><?php esc_html_e( 'Delete', 'trilisting' ); ?></a>
						</div>
					</li>
					<li class="tril-field-type-col"><?php echo esc_attr( $mb->get_the_value( 'field_type' ) ); ?></li>
					<li class="tril-field-heading-col"><?php echo esc_attr( $mb->get_the_value( 'field_heading' ) ); ?></li>
				</ul>
			</div>

			<div class="tril-fields-settings">
				<table class="tril-table">
					<tbody class="tril-fields-wrap">

						<!-- Search by -->
						<tr class="tril-search-form-field">
							<td class="tril-label">
								<label><?php esc_html_e( 'Search by', 'trilisting' ); ?></label>
							</td>
							<td class="tril-input">
								<div class="tril-input-wrap">
									<?php $mb->the_field( 'search_by' ); ?>
									<select class="tril-search-by-field" name="<?php $mb->the_name(); ?>">
										<option value=""><?php esc_html_e( 'Select value', 'trilisting' ); ?></option>
										<?php foreach ( $fields as $key => $field ) : ?>
											<option value="<?php echo $key; ?>"<?php $mb->the_select_state( $key ); ?>><?php echo $field; ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</td>
						</tr>

						<!-- Heading -->
						<tr class="tril-search-form-field">
							<td class="tril-label">
								<label><?php esc_html_e( 'Field Heading', 'trilisting' ); ?></label>
							</td>
							<td class="tril-input">
								<div class="tril-input-wrap">
									<?php $mb->the_field( 'field_heading' ); ?>
									<input class="tril-input-field-heading" type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/>
								</div>
							</td>
						</tr>

						<!-- Field Type -->
						<tr class="tril-search-form-field tril-field-group-tax-js">
							<td class="tril-label">
								<label><?php esc_html_e( 'Field Type', 'trilisting' ); ?></label>
							</td>
							<td class="tril-input">
								<div class="tril-input-wrap">
									<?php $mb->the_field( 'field_type' ); ?>
									<select class="tril-select-field-type" name="<?php $mb->the_name(); ?>">
										<option value=""><?php esc_html_e( 'Select value', 'trilisting' ); ?></option>
										<option value="select"<?php $mb->the_select_state( 'select' ); ?>><?php esc_html_e( 'Select', 'trilisting' ); ?></option>
										<option value="checkbox"<?php $mb->the_select_state( 'page' ); ?>><?php esc_html_e( 'Checkbox', 'trilisting' ); ?></option>
										<option value="radio"<?php $mb->the_select_state( 'radio' ); ?>><?php esc_html_e( 'Radio Button', 'trilisting' ); ?></option>
									</select>
								</div>
							</td>
						</tr>

						<!-- Hierarchical -->
						<tr class="tril-search-form-field tril-field-group-tax-js">
							<td class="tril-label">
								<label><?php esc_html_e( 'Hierarchical', 'trilisting' ); ?></label>
							</td>
							<td class="tril-input">
								<div class="tril-input-wrap">
									<?php $mb->the_field( 'hierarchical' ); ?>
									<input class="tril-field-hierarchical tril-switch-input" type="checkbox" name="<?php $mb->the_name(); ?>" value="1"<?php $mb->the_checkbox_state( '1' ); ?>/>
									<?php
									$hierarchical_check = '';
									if ( ! empty( $mb->get_the_value( 'hierarchical' ) ) ) {
										$hierarchical_check = ' -on';
									}
									?>
									<div class="tril-switch<?php echo $hierarchical_check; ?>">
										<span class="tril-switch-on" style="min-width: 18px;"><?php esc_html_e( 'Yes', 'trilisting' ); ?></span>
										<span class="tril-switch-off" style="min-width: 18px;"><?php esc_html_e( 'No', 'trilisting' ); ?></span>
										<div class="tril-switch-slider"></div>
									</div>
								</div>
							</td>
						</tr>

						<!-- Show Count -->
						<tr class="tril-search-form-field tril-field-group-tax-js">
							<td class="tril-label">
								<label><?php esc_html_e( 'Show Count', 'trilisting' ); ?></label>
							</td>
							<td class="tril-input">
								<div class="tril-input-wrap">
									<?php $mb->the_field( 'show_count' ); ?>
									<input class="tril-field-show-count tril-switch-input" type="checkbox" name="<?php $mb->the_name(); ?>" value="1"<?php $mb->the_checkbox_state( '1' ); ?>/>
									<?php
									$show_count_check = '';
									if ( ! empty( $mb->get_the_value( 'show_count' ) ) ) {
										$show_count_check = ' -on';
									}
									?>
									<div class="tril-switch<?php echo $show_count_check; ?>">
										<span class="tril-switch-on" style="min-width: 18px;"><?php esc_html_e( 'Yes', 'trilisting' ); ?></span>
										<span class="tril-switch-off" style="min-width: 18px;"><?php esc_html_e( 'No', 'trilisting' ); ?></span>
										<div class="tril-switch-slider"></div>
									</div>
								</div>
							</td>
						</tr>
						
						<!-- Placeholder -->
						<tr class="tril-search-form-field tril-field-group-input-js hidden">
							<td class="tril-label">
								<label><?php esc_html_e( 'Placeholder', 'trilisting' ); ?></label>
							</td>
							<td class="tril-input">
								<div class="tril-input-wrap">
									<?php $mb->the_field( 'placeholder' ); ?>
									<input class="tril-input-field-placeholder" type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/>
								</div>
							</td>
						</tr>

					</tbody>
				</table>
			</div>
		</div>

		<?php $mb->the_group_close(); ?>
	<?php endwhile; ?>

	<!-- Footer -->
	<ul class="tril-footer">
		<li class="tril-add-field"> <a href="#" class="docopy-field button button-primary button-large tril-add-field-js"><?php esc_html_e( '+ Add Field', 'trilisting' ); ?></a></li>
		<li class="tril-remove-all"><a href="#" class="dodelete-field button button-delete button-large"><?php esc_html_e( 'Remove All', 'trilisting' ); ?></a></li>
	</ul>

</div>
