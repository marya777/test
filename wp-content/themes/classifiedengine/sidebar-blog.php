<?php 
/**
 * The Content Sidebar
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */

if ( ! is_active_sidebar( 'sidebar-blog' ) ) {
	return;
}
?>
<div id="sidebar-blog" class="well sidebar-nav main-sidebar sortable ui-sortable" <?php echo Schema::WPSideBar() ?>>
	<?php
dynamic_sidebar( 'sidebar-blog' );
?>
</div>

