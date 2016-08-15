<?php
    et_get_mobile_header();
 ?>
 <div class="search headroom" >

	    <div class="search-text">
	        <input type="text" name="search" id="txt_search" class="txt_search" placeholder="<?php _e("Search classifieds...", ET_DOMAIN); ?>" >
	        <span class="icon" data-icon="s"></span>
	    </div>
	    <a href="#" class="icon ui-btn-s search-btn  category-btn" data-icon="y"></a>
	    <div class="menu-filter" style="display: none;">
	        <div class="menu-filter-inner">
	            <div class="icon-header">
	                <a class="icon" data-icon="y"></a>
	            </div>
	            <div data-role="collapsible-set" data-theme="c" data-content-theme="d">
	                <?php
	                    ce_mobile_taxonomy(array('taxonomy' => CE_AD_CAT ));
	                    ce_mobile_taxonomy(array('taxonomy' => 'ad_location' , 'show_option_none' => __('Location list is empty.',ET_DOMAIN) )  );
	                ?>

	            </div>
	            <a href="#" class="ui-btn-s btn-blue filter-search-btn btn-wide width90 search-button" > <?php _e("Search", ET_DOMAIN); ?> </a>
	        </div>
	    </div>

</div>
<div class="page404" >
	<div class="text-page-left">
		<h2><?php _e("Oops...", ET_DOMAIN); ?></h2>
	    <p><?php _e("This page cannot be found.", ET_DOMAIN); ?><br>
		<?php printf(__("Go back to %s", ET_DOMAIN), '<a href="'.home_url().'">'.__("Homepage", ET_DOMAIN).'</a>') ?></p>
	</div>
</div>
<ul data-role="listview" class="latest-list">
</ul>
<div class="inview"></div>
<?php
et_get_mobile_footer();