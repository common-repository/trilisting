<?php
/**
 * View templates t.
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
	<div class="row">
		<div class="col-sm-12">
			<div class="trilisting-item<?php echo esc_attr( ( '' != $item_type ? ' trilisting-item-' . $item_type : '' ) );  ?>">
				<div class="row">
					<div class="col-xs-12 col-sm-6 col-md-6">
						<div class="wrap-trilisting-item-img-meta">
							<?php
							if ( 'none' != $img_layout && '' != $img_layout ) {
								echo $this->render_image( $img_layout, true, $data_url = '' );
							}
							?>
						</div>
					</div>

					<div class="'col-xs-12 col-sm-6 col-md-6">
						<div class="wrap-details">
							<div class="trilisting-item-details">

								<?php
								if ( 1 == $this->get_inner_option( 'meta_' . $this->current_post_type . '.category' ) ) {
									echo $this->render_category(); 
								}

								if ( 'post' !== $this->current_post_type && 1 == $this->platform->get_option( 'enable_saved_listing' ) ) {
									echo trilisting_get_saved_html( $this->post->ID );
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
								?>

								<?php echo $this->render_fields(); ?>

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
					</div>
				</div>
			</div>
		</div> <!-- .col-sm-12 -->
	</div>
</article>
