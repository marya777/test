<?php 
    /**
     * carousel template in single ad
    */
    global $post,$publish_list;

    $ad = CE_Ads::convert ($post);
    $ad->id = $ad->ID;
    $publish_list[] = $ad;

    $et_featured = ET_FEATURED;

?>

<div class="item-product ad-carousel related-classified" <?php echo Schema::Product(); ?>>
    <?php if($ad->$et_featured) { ?>
        <span class="icon-featured"><?php __("Featured", ET_DOMAIN) ?></span>   
    <?php } ?>
    <!-- ad thumnail image -->
    <p class="img">
  	
        <a href="<?php the_permalink() ?>">
        	<span class="shadown-img"><img src="<?php echo TEMPLATEURL ?>/img/shadown-black.png"></span>
            <?php 
                echo $ad->the_post_thumbnail;
            ?>
        </a>
    </p>
  
  
    <!-- ad details -->

    <div class="intro-product woocommerce">
        <h5 class="title" <?php echo Schema::Product("name"); ?>><?php the_title();?></h5>
        <a href="<?php the_permalink() ?>" title="<?php printf(__("Views %s", ET_DOMAIN), get_the_title()); ?>">
          
            <?php if(isset($ad->location[0])) { ?>
                <span class="name"><?php echo $ad->location[0]->name; ?></span><br/>
            <?php } ?>
            <span class="price" <?php echo Schema::Offer() ?> ><?php echo $ad->price; ?></span>
        </a>
        <?php do_action('ce_place_item_after_price') ?>
    </div>             
</div><!--/span-->
