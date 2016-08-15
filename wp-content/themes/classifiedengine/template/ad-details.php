<?php
    /**
     * ad details template in single ad
    */
    global $ad, $post;

    $user   =   get_userdata( $post->post_author );
    $seller   = ET_Seller::convert( $user );
?>
<?php do_action('ce_single_before_detail') ?>
<div class="row">
    <div class="col-md-4 col-sm-4 mobile-desktop">
        <?php do_action('display_right_ad',get_the_ID());?>
        <?php ce_seller_bar($seller); ?>

    </div>

    <div class="col-md-8 col-sm-8 fix-responsive">
        <?php
            get_template_part('template/single' , 'slider');
        ?>
        <div class="row">
            <div class="col-md-8 listing-content" <?php echo Schema::Product("description") ?>>
                <?php the_content(); ?>
                <?php do_action('ce_detail_ad_more',get_the_ID()); ?>
            </div>
            <div class="col-md-4 addthis_toolbox addthis_default_style" addthis:title="<?php the_title();?>"
                        addthis:description="<?php the_excerpt()?>">
                <?php do_action('ce_single_after_description') ?>
                <ul class="share-social">
                    <!-- AddThis Button BEGIN -->
                    <li>
                        <a class="addthis_button_facebook" >
                        <span class="icon-fb"><i class="fa fa-facebook"></i></span><?php _e("Share on Facebook", ET_DOMAIN); ?></a>
                    </li>
                    <li>
                        <a class="addthis_button_twitter"><span class="icon-tw"><i class="fa fa-twitter"></i></span><?php _e("Share on Twitter", ET_DOMAIN); ?></a>
                    </li>
                    <li>
                        <a class="addthis_button_email"><span class="icon-mail"><i class="fa fa-envelope"></i></span><?php _e("Send to a friend", ET_DOMAIN); ?></a>
                    </li>






                     <?php
                        if($ad->post_status == 'publish' ) {
                            if(!$featured) { ?>
                                <li class="tooltips flag toggleFeature"><a href="#" data-toggle="tooltip" title="<?php _e("Set as featured", ET_DOMAIN); ?>" data-original-title="<?php _e("Set as featured", ET_DOMAIN); ?>" ><span class="icon" data-icon="^"></span></a></li>
                            <?php } else { ?>
                                <li class="tooltips flag toggleFeature featured"><a href="#" data-toggle="tooltip" title="<?php _e("Remove featured", ET_DOMAIN); ?>" data-original-title="<?php _e("Remove featured", ET_DOMAIN); ?>" ><span class="icon color-yellow" data-icon="^"></span></a></li>
                            <?php }
                        } else { ?>
                            <li class="tooltips remove approve"><a data-toggle="tooltip" title="<?php _e("Approve", ET_DOMAIN); ?>" data-original-title="<?php _e("Approve", ET_DOMAIN); ?>" href="#"><span class="icon color-green" data-icon="3"></span></a></li>
                        <?php } ?>









                    <!-- AddThis Button END -->
                    <!-- Add favorite !-->
                    <?php
                    global $user_ID;
                    $favorites  =  (array) get_user_meta($user_ID,'ads_favourites',true);
                   $status = 0;
                    $class = 'active';
                    if(in_array(get_the_ID(),$favorites)){
                        $status = 1;
                        $class ='';
                    }

                    ?>
                    <!-- End Favorite !-->
                    <?php if( is_user_logged_in() ){ ?>
                    <li><a id="add_fovorite" class="<?php echo $class;?> ad-favorite <?php if($user_ID) echo 'logged';?>" rel="<?php the_ID();?>" href="#"><span class="icon icon-mail"> <i class="fa fa-eye"></i> </span> <span class='status-add'><?php  _e('Add to favourites',ET_DOMAIN);?> </span><span class="status-added"><?php  _e('Unfavourite ad',ET_DOMAIN);?>  </span></a></li>
                    <?php } ?>
                </ul>

            </div>

        </div>
    </div>
</div>

