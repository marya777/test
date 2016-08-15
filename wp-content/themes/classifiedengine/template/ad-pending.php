<?php 
/**
 * template for pending ad item
*/
global $post,$pending_list;

$ad	=	CE_Ads::convert($post);
$ad->id	=	$ad->ID;
$pending_list[]	=	$ad;
$et_featured = ET_FEATURED;
?>
<div class="col-md-4 item-product ">  
	<?php if($ad->$et_featured) { ?>
    	<span class="icon-featured"><i class="fa fa-bookmark"></i> <?php _e("Featured", ET_DOMAIN) ?></span>   
  	<?php } ?> 
	<p class="img"><a href="<?php the_permalink()?>" >
		<?php if($ad->$et_featured) { ?>
    	<span class="shadown-img"><img src="<?php echo TEMPLATEURL ?>/img/shadown-black.png"></span>
      	<?php } ?>
		<?php echo $ad->the_post_thumbnail; ?></a>
	</p>
	<?php if(current_user_can( 'manage_options' )) { ?>
	<!-- control button -->
	<ul class="button-event">
		<li class="tooltips check approve">
			<a href="#" data-toggle="tooltip" title="<?php _e("Approve", ET_DOMAIN); ?>" data-original-title="<?php _e("Approve", ET_DOMAIN)?>" >
				<span class="icon" data-icon="3"></span>
			</a>
		</li>
		<li class="tooltips delete reject">
			<a href="#" data-toggle="tooltip" title="<?php _e("Reject", ET_DOMAIN); ?>" data-original-title="<?php _e("Reject", ET_DOMAIN)?>" >
				<span class="icon icon-delete" data-icon="*"></span>
			</a>
		</li>
		<li class="tooltips update edit">
			<a href="#" data-toggle="tooltip" title="<?php _e("Edit", ET_DOMAIN); ?>" data-original-title="<?php _e("Edit", ET_DOMAIN)?>" >
				<span class="icon" data-icon="p"></span>
			</a>
		</li>
	</ul> 
	<?php } ?>

    <div class="intro-product">
    	<h5 class="title" <?php echo Schema::Product("name") ?>>  <a href="<?php the_permalink() ?>" title="<?php printf(__("Views %s", ET_DOMAIN), get_the_title()); ?>" ><?php the_title() ?> </a></h5>
        <a href="<?php the_permalink() ?>" title="<?php printf(__("Views %s", ET_DOMAIN), get_the_title()); ?>" >            
            <p>
                <?php if(isset($ad->location[0])) { ?>
                    <span class="name"><?php echo $ad->location[0]->name; ?></span>
                <?php } ?>
                <span class="price"><?php echo $ad->price; ?></span>
            </p>
        </a>
        <div class="description" >
            <?php the_excerpt(); ?>
        </div>
    </div>          
</div><!--/span-->