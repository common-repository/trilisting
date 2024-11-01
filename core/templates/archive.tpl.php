<?php
/**
 * The template for displaying single page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

TRILISTING\Trilisting_Helpers::get_template_part( 'header' );
?>

<div class="trilisting-archive-wrap">
	<div id="primary" class="content-area">
		<main id="main" class="site-main trilisting-listings" role="main">

			<?php echo trilisting_get_archive_shortcodes(); ?>

		</main>
	</div>
	<?php TRILISTING\Trilisting_Helpers::get_template_part( 'sidebar' ); ?>
</div><!-- .trilisting-archive-wrap -->

<?php
TRILISTING\Trilisting_Helpers::get_template_part( 'footer' );
