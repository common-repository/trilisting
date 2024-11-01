<?php
/**
 * Grid templates - grid 1.
 *
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$top_options	= $this->get_global_options_array( 'tp' );
$bottom_options = $this->get_global_options_array( 'bt' );

$render_item_params = [
	'item_type'   => $view1,
	'img_layout'  => 'trilisting-widgets-default',
];
?>

<div class="row ac-posts-wrapper">
	<div class="article-wrapper">
		<div class="col-xs-12 col-sm-12 col-md-12">
			<div class="row">
				<div class="col-sm-12 trilisting-blog-1">
					<?php
					$post = $posts[0];
					$item = new TRILISTING\Trilisting_Widgets_Item_1( $post, $top_options, $render_item_params );
					echo $item->render();
					?>
				</div>
				<div class="col-sm-12">

					<?php
					if ( isset( $posts[1] ) ) {
						$render_item_params['item_type']  = $view2;
						$render_item_params['img_layout'] = 'trilisting-widgets-default';

						for ( $i = 1; $i < count( $posts ); $i++ ) {
							$post = $posts[ $i ];
							$item = new TRILISTING\Trilisting_Widgets_Item_1( $post, $bottom_options, $render_item_params );
							echo $item->render();
						}
					}
					?>

				</div>
			</div>
		</div>
	</div>
</div> <!-- .ac-posts-wrapper -->
