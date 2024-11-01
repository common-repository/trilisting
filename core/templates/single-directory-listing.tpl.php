<?php
/**
 * The template for displaying single page.
 * 
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="trilisting-listings">

	<?php
	if ( is_active_sidebar( 'trilisting-single-top' ) ) {
		dynamic_sidebar( 'trilisting-single-top' );
	}

	// Button to change the current listing
	echo trilisting_link_edit_listing( get_the_ID() );

	// Saved button
	if ( 1 == get_trilisting_option( 'enable_saved_listing' ) ) {
		echo trilisting_get_saved_html( get_the_ID() );
	}

	// Rating
	if ( trilisting_check_insert_rating() ) {
		echo trilisting_display_average_rating( get_the_ID() );
	}

	// The taxonomy of the current listing
	echo TRILISTING\Trilisting_Helpers::render_taxonomy();

	/* translators: %s: Name of current post */
	the_content( sprintf(
		__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'trilisting' ),
		get_the_title()
	) );

	TRILISTING\Trilisting_Helpers::get_template_part( 'parts/fields' );

	if ( is_active_sidebar( 'trilisting-single-bottom' ) ) {
		dynamic_sidebar( 'trilisting-single-bottom' );
	}
	?>

</div><!-- .trilisting-listings -->
