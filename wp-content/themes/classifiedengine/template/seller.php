<?php 
    /**
     * template seller item in seller list
    */
	global $ce_seller;
	$user_id = $ce_seller->ID;
  $local  = $ce_seller->user_location;
  $adress = get_user_meta($user_id,'user_location',true);
?>
<div class="col-md-12 item-product">
    <div class="col-md-5 intro-profle">
    	<a href="<?php echo get_author_posts_url( $ce_seller->ID) ?>" title="<?php printf(__("View all ads by %s", ET_DOMAIN), $ce_seller->display_name) ?>" >
        	<?php echo get_avatar( $ce_seller->ID, 60 ) ?>
        	<span class="sembold" <?php if($local == '') echo 'style="margin-top:8px;"' ?>><span class="colorgray"><?php echo $ce_seller->display_name ?></span>
        	<?php
        	
        	
          if($adress != '')
        	echo '</br>'.$adress;

        	?></span>
          <span class="date-join"><?php echo $ce_seller->joined_date; ?> </span>
      </a>
    </div>
    <div class="col-md-7 content-img-right">
    <?php
  //   	$args = wp_parse_args($args,array('post_type'=>CE_AD_POSTTYPE,'post_status'=>'publish'));
		// return CE_Ads::query($args);

    $ads = CE_Ads::query(array('author'=>$ce_seller->ID,'showposts'=>-1 , 'meta_key' => ET_FEATURED,'post_type' => CE_AD_POSTTYPE, 'post_status' => 'publish'));
		if( $ads->have_posts() ) {
			$i = 0;
			if( $ads->found_posts > 4 ) {
				echo '<div class="span1 bg-img text 123">
						<a title="'.sprintf(__("View more ads by %s", ET_DOMAIN), $ce_seller->display_name ) .'" href="'.get_author_posts_url( $ce_seller->ID).'" >'.($ads->found_posts - 4).'+ </a>
						</div>';
			}				
		 	while($ads->have_posts()) : $ads->the_post();	$i++;											
				echo '<div class="span1 bg-img"><a class="ad-of-user" title ="'.get_the_title().'" href="'.get_permalink(get_the_ID()).'">';
					echo get_the_post_thumbnail( get_the_ID(), 'thumbnail' );									
				echo '</a> </div>';

				if($i == 4) break;
			endwhile;
		};
    ?>
    	
    </div>
</div>