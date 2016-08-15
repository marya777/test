<?php
    et_get_mobile_header();
 ?>


<div data-role="content" style="padding-top: 20px;" class="list-categories">
	<h1 class="title-categories"><?php _e("All locations", ET_DOMAIN); ?></h1>
    <div class="content-category ad_locations ">
    <?php
   	ce_mobile_taxonomy_list(array('taxonomy' => 'ad_location', 'show_option_none' => __('Location list is empty.',ET_DOMAIN) ));
    ?>
    </div>
    <div id="inview" ></div>

</div><!-- /content -->
<?php
et_get_mobile_footer();