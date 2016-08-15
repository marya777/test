<?php 
/**
 * template ad published
*/
global $post,$publish_list;

$ad = CE_Ads::convert ($post);
$ad->id = $ad->ID;
$publish_list[] = $ad;
$et_featured = ET_FEATURED;
$template   =   apply_filters( 'ce_template_publish_ad' , false , $ad );
if($template) echo $template;
else {
?>
<div class="item-product col-md-4" <?php echo Schema::Product(); ?>>
    <?php if($ad->$et_featured) { ?>
        <span class="icon-featured"><i class="fa fa-bookmark"></i> <?php _e("Featured", ET_DOMAIN) ?></span>   
    <?php } ?>
    <!-- ad thumnail image -->
    <p class="img">
  	
        <a href="<?php the_permalink() ?>">
            <?php if($ad->$et_featured) { ?>
        	   <span class="shadown-img"><img src="<?php echo TEMPLATEURL ?>/img/shadown-black.png"></span>
            <?php } ?>
            <?php 
                echo $ad->the_post_thumbnail;
           ?>
        </a>
    </p>
  <!-- //ad thumnail image -->  

   <!-- ad details -->
    <div class="intro-product">
        <a href="<?php the_permalink() ?>" title="<?php printf(__("Views %s", ET_DOMAIN), get_the_title()); ?>" >
            <span class="title" <?php echo Schema::Product("name") ?>><?php the_title() ?></span>
            <p>
                <?php if(isset($ad->location[0])) { ?>
                    <span class="name"><?php echo $ad->location[0]->name; ?></span>
                <?php } ?>
                <span class="price"><?php echo $ad->price; ?></span>
            </p>
        </a>
        <?php do_action('ce_place_item_after_price') ?>
        <div class="description" <?php echo Schema::Product("description") ?>>
            <?php the_excerpt(); ?>
        </div>
    </div>             
</div><!--/span-->
<?php }