<?php
/**
 * Pagination - Show numbered pagination for catalog pages.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( 1 >= $max_num_pages ) {
	return;
}
?>
<nav class="trilisting-dashbord-pagination">
	<?php
		echo paginate_links( apply_filters( 'trilisting/dashboard/pagination_args', [
			'base'      => esc_url_raw( str_replace( 999999999, '%#%', get_pagenum_link( 999999999, false ) ) ),
			'format'    => '',
			'current'   => max( 1, get_query_var('paged') ),
			'total'     => $max_num_pages,
			'prev_text' => esc_html__( 'Prev', 'trilisting' ),
			'next_text' => esc_html__( 'Next', 'trilisting' ),
			'type'      => 'list',
			'mid_size'  => 3,
			'end_size'  => 3,
		] ) );
	?>
</nav>
