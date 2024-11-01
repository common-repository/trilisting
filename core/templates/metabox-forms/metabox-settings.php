<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="tril-search-form tril-search-form-settings">

	<!-- Search Type -->
	<div class="tril-search-form-field tril-search-form-search-type">
		<div class="tril-label">
			<label for="tril_search_from-search-type"><?php esc_html_e( 'Type', 'trilisting' ); ?></label>
		</div>

		<div class="tril-input">
			<?php $mb->the_field( 'search_type' ); ?>
			<select id="tril_search_from-search-type" name="<?php $mb->the_name(); ?>">
				<option value="box"<?php $mb->the_select_state( 'box' ); ?>><?php esc_html_e( 'Search Box', 'trilisting' ); ?></option>
				<option value="page"<?php $mb->the_select_state( 'page' ); ?>><?php esc_html_e( 'Search Page', 'trilisting' ); ?></option>
			</select>
		</div>
	</div>

	<!-- Submit Button Text -->
	<div class="tril-search-form-field tril-search-form-submit">
		<div class="tril-label">
			<label for="tril_search_form-submit-btn-text"><?php esc_html_e( '"Submit" button text', 'trilisting' ); ?></label>
		</div>

		<div class="tril-input">
			<input id="tril_search_form-submit-btn-text" type="text" name="<?php $metabox->the_name( 'submit_btn_text' ); ?>" value="<?php $metabox->the_value( 'submit_btn_text' ); ?>"/>
		</div>
	</div>

	<!-- Custom Class -->
	<div class="tril-search-form-field tril-search-form-custom-class">
		<div class="tril-label">
			<label for="tril_search_form-custom-class"><?php esc_html_e( 'Custom Class', 'trilisting' ); ?></label><br/>
		</div>

		<div class="tril-input">
			<input id="tril_search_form-custom-class" type="text" name="<?php $metabox->the_name( 'custom_class' ); ?>" value="<?php $metabox->the_value( 'custom_class' ); ?>"/>
		</div>
	</div>

</div>
