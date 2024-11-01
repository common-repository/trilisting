<?php
/**
 * View templates a.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$post_classes = get_post_class( '', $this->post->ID );
$sticky_class = $this->is_sticky_class();
array_push( $post_classes, 'article-wrapper', $sticky_class );
$post_classes = implode( ' ', $post_classes );
?>

<article class="<?php echo esc_attr( $post_classes ); ?>">
	<div class="trilisting-item<?php echo esc_attr( ( '' != $item_type ? ' trilisting-item-' . $item_type : '' ) );  ?>" data-post-id="<?php echo $this->post->ID; ?>">
		<div class="trilisting-wrap-badge-img">
			<?php
			if ( 'none' !== $img_layout && '' !== $img_layout ) {
				echo $this->render_image( $img_layout, true, $data = '' );
			}
			?>
		</div>

		<div class="trilisting-item-details">
			
			<div class="trilisting-block-main">
				<?php
				if ( 1 == $this->get_inner_option( 'meta_' . $this->current_post_type . '.category' ) ) {
					echo $this->render_category();
				}
				?>
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

				// Saved button
				if ( 'post' !== $this->current_post_type && 1 == $this->platform->get_option( 'enable_saved_listing' ) ) {
					echo trilisting_get_saved_html( $this->post->ID );
				}
				?>

				<div class="trilisting-item-description">
					<?php echo $this->render_post_content(); ?>
				</div>
			</div>
			
			<?php echo $this->render_fields(); ?>
			<div class="trilisting-block-meta trilisting-meta">
				<?php
				if ( 1 == $this->get_inner_option( 'meta_' . $this->current_post_type . '.author' ) ) {
					echo $this->render_author();
				}

				if ( 1 == $this->get_inner_option( 'meta_' . $this->current_post_type . '.date' ) ) {
					echo $this->render_date();
				}

				if ( 1 == $this->get_inner_option( 'meta_' . $this->current_post_type . '.comments' ) ) {
					echo $this->render_comments( true, '<i class="fa fa-comment-o" aria-hidden="true"></i>' );
				}
				?>
			</div>

		</div>
	</div> <!-- .trilisting-item -->
</article>
