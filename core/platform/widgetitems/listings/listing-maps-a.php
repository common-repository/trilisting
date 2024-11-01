<?php
/**
 * View templates maps a.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<article class="trilisting-marker-maps-listing">
	<div class="trilisting-item<?php echo esc_attr( ( '' != $item_type ? ' trilisting-item-' . $item_type : '' ) );  ?>">
		<div class="trilisting-wrap-badge-img">
			<?php
			if ( 'none' !== $img_layout && '' !== $img_layout ) {
				echo $this->render_image( $img_layout, true, $data = '' );
			}

			if ( 1 == $this->get_inner_option( 'meta_' . $this->current_post_type . '.category' ) ) {
				echo $this->render_category();
			}
			?>
		</div>

		<div class="trilisting-item-details">

			<div class="trilisting-item-tile-wrap">
				<?php
				echo $this->is_sticky();
				echo $this->render_title();
				?>
			</div>

			<?php
			// reviews
			if (
				trilisting_check_insert_rating( $this->current_post_type )
				&& 1 == $this->get_inner_option( 'meta_' . $this->current_post_type . '.reviews' )
			) {
				echo trilisting_display_average_rating( $this->post->ID );
			}
			?>

			<div class="trilisting-item-map-fields-wrap">
				<?php echo $this->render_fields(); ?>
				
				<?php
				if ( 1 == $this->get_inner_option( 'meta_' . $this->current_post_type . '.date' ) ) {
					echo $this->render_date();
				}
				
				if ( 1 == $this->get_inner_option( 'meta_' . $this->current_post_type . '.author' ) ) {
					echo $this->render_author();
				}
				?>
				
				<?php if ( 1 == $this->get_inner_option( 'meta_' . $this->current_post_type . '.comments' ) ) : ?>
					<div class="trilisting-meta">
						<?php echo $this->render_comments( true, '<i class="fa fa-comment-o" aria-hidden="true"></i>' ); ?>
					</div>
				<?php endif; ?>
			</div>


		</div>
	</div> <!-- .trilisting-item -->
</article>
