<?php
/**
 * Dashboard shortcode content.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$dashboard = TRILISTING\Frontend\Trilisting_Dashbord::instance();
?>
<div id="trilisting-dashboard" class="trilisting-dashboard-wrap">
	<?php $dashboard->edit_form(); ?>
</div>
