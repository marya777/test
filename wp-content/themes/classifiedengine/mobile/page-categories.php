<?php
    et_get_mobile_header();
 ?>

<div data-role="content" style="padding-top: 20px;" class="list-categories">
	<h1 class="title-categories"><?php _e("All categories", ET_DOMAIN); ?></h1>
    <div class="content-category ad_category ">
    <?php
   	ce_mobile_taxonomy_list(array('taxonomy' => CE_AD_CAT));
    ?>
    </div>
</div><!-- /content -->
<?php
et_get_mobile_footer();