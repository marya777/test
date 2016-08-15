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
<div class="item-product col-md-4" <?php echo Schema::Product() ?>>
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
  
  <?php 
  /**
   * front-end control button 
  */
  if( current_user_can( 'manage_options' ) ) { ?>
    <ul class="button-event">
        <li class="tooltips update edit">
            <a href="#" data-toggle="tooltip" title="<?php _e("Edit", ET_DOMAIN); ?>" data-original-title="<?php _e("Edit", ET_DOMAIN)?>">
                <span  class="icon" data-icon="p"></span>
            </a>
        </li>
        <li class="tooltips flag toggle-feature">
            <?php if($ad->$et_featured) { ?>
            <a href="#" data-toggle="tooltip" title="<?php _e("Remove featured", ET_DOMAIN); ?>" data-original-title="<?php _e("Set as featured", ET_DOMAIN) ?>" >
                <span class="icon color-yellow" data-icon="^"></span>
            </a>
            <?php } else { ?>
            <a href="#" data-toggle="tooltip" title="<?php _e("Set as featured", ET_DOMAIN); ?>" data-original-title="<?php _e("Set as featured", ET_DOMAIN) ?>" >
                <span class="icon" data-icon="^"></span>
            </a>
            <?php } ?>
        </li>
        <li class="tooltips remove archive">
            <a href="#" data-toggle="tooltip" title="<?php _e("Delete", ET_DOMAIN); ?>" data-original-title="<?php _e("Delete", ET_DOMAIN)?>" >
                <span  class="icon" data-icon="#"></span>
            </a>
        </li>
    </ul> 
  <?php } ?>    

   <!-- ad details -->
    <div class="intro-product woocommerce">
        
            <h5 class="title" <?php echo Schema::Product("name") ?>>  
                <a href="<?php the_permalink() ?>" title="<?php printf(__("Views %s", ET_DOMAIN), get_the_title()); ?>" >
                    <?php the_title() ?> 
                </a>
            </h5>
            <!-- <a href="<?php the_permalink() ?>" title="<?php printf(__("Views %s", ET_DOMAIN), get_the_title()); ?>" > -->
                <p>
                    <?php if(isset($ad->location[0])) { ?>
                        <span class="name"><?php echo $ad->location[0]->name; ?></span>
                    <?php } ?>
                    <span <?php echo Schema::Offer(); ?>>
                        <span class="price"><?php echo $ad->price;?></span>
                    </span>
                </p>
           <!--  </a> -->
        <?php do_action('ce_place_item_after_price') ?>
        <div class="description " <?php echo Schema::Product("description") ?>>
            <?php the_excerpt(); ?>
        </div>
    </div>             
</div><!--/span-->
<?php }