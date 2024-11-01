<?php
/**
 * View templates c.
 *
 * @package Plugin Name
 * @version 1.0.0
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
		<a class="trilisting-item-link" href="<?php echo esc_url( $this->post_link ); ?>"></a>

		<?php
		if ( 'none' != $img_layout && '' != $img_layout ) {
			$output .= $this->render_image( $img_layout, true, $data_url = '' );
		}
		?>

		<div class="trilisting-item-details">
			<?php echo $this->render_category(); ?>

			<div class="trilisting-item-tile-wrap">
				<?php
				echo $this->is_sticky();
				echo $this->render_title();
				?>
			</div>

			<?php
			if ( 'post' !== $this->current_post_type && 1 == $this->platform->get_option( 'enable_saved_listing' ) ) {
				echo trilisting_get_saved_html( $this->post->ID );
			}
			?>

			<div class="trilisting-block-meta">
				<?php echo $this->render_date(); ?>
			</div>
		</div> <!-- .trilisting-item-details -->
	</div>

</article>
