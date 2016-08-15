<?php 
global $post, $flag; 
    $ad =   CE_Ads::convert($post);
    $et_featured = ET_FEATURED;
    if(!is_author()) {
         if( $ad->$et_featured && $flag != 'f' ) {
            $flag   =   'f';
            echo '<li class="list-divider featured-divider"><h2>'.__("Featured Ads", ET_DOMAIN).'</h2></li>';
        }

        if( !$ad->$et_featured && $flag != 'l') {
            $flag   =   'l';
            echo '<li class="list-divider latest-divider"><h2>'.__("Latest Ads", ET_DOMAIN).'</h2></li>';
        }
    }
   
?>
<li data-icon="false">
    <a href="<?php the_permalink(); ?>" data-ajax="false">    
        <span class="product-img">
            <?php echo $ad->the_post_thumbnail; ?>
            <?php if($ad->$et_featured) echo '<span class="flag"><i class="fa fa-bookmark"></i></span>'; ?>
        </span>
        <span class="product-text">
            <span class="text clearfix"><?php the_title(); ?></span>
            <span class="price clearfix"><?php echo $ad->price; ?></span>
            <?php if(isset($ad->location[0])) { ?>
                <span class="address"><?php echo $ad->location[0]->name; ?></span><br/>
            <?php } ?>
            <!-- <span class="address"> 
                <?php echo $ad->et_full_location ?>
            </span> -->
        </span>      
    </a>
</li> 