<?php if( is_home()  || is_tax() ||  is_search() || is_post_type_archive(CE_AD_POSTTYPE) ) { ?>
	<div id="sidebar-main-left" class="well sidebar-nav main-sidebar sortable ui-sortable" <?php echo Schema::WPSideBar() ?>>
	<?php
	    if(is_active_sidebar('sidebar-main-left'))
	        dynamic_sidebar( 'sidebar-main-left' );
	    else {
	    	 echo '<div class="widget-title widget-title-sample">';
	        _e('Categories',ET_DOMAIN);
	        echo '</div>';
	        ce_list_categories();
	        echo '<br /><div class="widget-title widget-title-sample">';
	        _e('Locations',ET_DOMAIN);
	        echo '</div>';
	        ce_list_locations();
	    }

	?>
	</div><!--/.well -->
<?php
}else {

 	if (is_page_template( 'page-sellers.php' ) ) {
	?>
	<div id="sidebar-main-left" class="well sidebar-nav main-sidebar sortable ui-sortable  ce-list-locations" <?php echo Schema::WPSideBar() ?>>
		<?php

		if(is_active_sidebar('sidebar-list-seller'))
	        dynamic_sidebar( 'sidebar-list-seller' );
	    else
	    	ce_list_locations(); ?>
	</div>

	<?php
	} else {

		if(is_page_template( 'page-account-profile.php' ) || is_page_template( 'page-account-listing.php' ) ) {  ?>

		  	<div id="sidebar-seller-profile" class="sidebar sortable ui-sortable" <?php echo Schema::WPSideBar() ?>>
		  		<?php dynamic_sidebar( 'sidebar-seller-profile' ); ?>
		  	</div>
		<?php
	   	} elseif( is_page_template( 'page-post-ad.php') ) { ?>

		  	<div id="sidebar-post-ad" class="sidebar sortable ui-sortable" <?php echo Schema::WPSideBar() ?>>
		  		<?php dynamic_sidebar( 'sidebar-post-ad' ); ?>
		  	</div>
		<?php
	   	}elseif ( is_page() ) {
	   	?>
	   		<div id="sidebar-page" class="sidebar sortable ui-sortable" <?php echo Schema::WPSideBar() ?>>
		  		<?php dynamic_sidebar( 'sidebar-page' ); ?>
		  	</div>
		<?php
	   	}

	   	$ajax_nonce = wp_create_nonce("save-sidebar-widgets");
   		if(current_user_can( 'manage_options')) { ?>
			<input id="savewidgets" name="savewidgets" type="hidden" value="<?php echo $ajax_nonce ?>" />
	  		<a href="#" class="btn style-button button-right-bar add-more" ><?php _e("Add a text widget", ET_DOMAIN); ?>  <span class="sembold">+</span></a>
	  	<?php
		}
   	}
 }
 ?>
