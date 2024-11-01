<?php
/**
 * Grid templates - grid featured 1.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$pix = $this->post_index;
$render_item_params = [
	'item_type'  => $view,
	'img_layout' => 'trilisting-widgets-default',
];
?>

<div class="col-sm-12 col-md-12">
	<div class="trilisting-item-c-list">
		<?php
		$post = $posts[ $pix ];
		$item = new TRILISTING\Trilisting_Widgets_Item_1( $post, $this->get_global_options_array(), $render_item_params );
		echo $item->render();
		?>
	</div>
	<div class="row">
		<div class="trilisting-bottom-block">
			<?php
			$pix++;
			if ( isset( $posts[ $pix ] ) ) {
				$post = $posts[ $pix ];
				$item = new TRILISTING\Trilisting_Widgets_Item_1( $post, $this->get_global_options_array(), $render_item_params );

				$render_item_params['item_type']  = $view;
				$render_item_params['img_layout'] = 'trilisting-widgets-featured-1';

				$count_post = 0;
				for ( $i = $pix; $i < count( $posts ); $i++ ) {
				?>
					<div class="trilisting-col-3">
						<?php
						$post = $posts[ $i ];
						$item = new TRILISTING\Trilisting_Widgets_Item_1( $post, $this->get_global_options_array(), $render_item_params );
						echo $item->render();
						?>
					</div>
					<?php
					$count_post++;

					if ( 3 == $count_post ) {
						break;
					}
				}
			}
			?>
		</div>
	</div>
</div> 
