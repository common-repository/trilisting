<?php
/**
 * Dashboard shortcode content if user is not logged in.
 * 
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div id="trilisting-dashboard" class="trilisting-dashboard-wrap">
	<?php do_shortcode( '[trilisting_login_register]' ); ?>
</div>
