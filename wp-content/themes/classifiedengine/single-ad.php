<?php
ob_start();
get_header();
if(have_posts()) { the_post();
    global $post, $ad;

    $ad       = CE_Ads::convert($post);
    $et_featured = ET_FEATURED;
    $featured = $ad->$et_featured ;

?>
<?php if(current_user_can( 'manage_options' )) { ?>
    <div class="heading-message message" style="display : none;">

    </div>
<?php } ?>
<div <?php echo Schema::Product() ?>>
    <div class="single-ad-title">
        <div class="main-center container">
            <div class="row">
                <div class="col-md-8">
                    <div class="name-product">
                        <h1 class="ad-title"><span <?php echo Schema::Product("name") ?>><?php the_title() ?></span>
                            <div class="price-product" <?php echo Schema::Product("offers") ?>>
                                <div class="price-arrow"><i class="fa fa-backward"></i></div>
                                <span><?php echo $ad->price ?></span>
                            </div>
                        </h1>
                    </div>

                    <p class="clearfix text-intro">
                        <?php if(isset($ad->location[0]))  echo  $ad->location[0]->name ?> <span class="date-upload"><?php echo $ad->date_ago ?></span>
                    </p>
                </div>
                <div class="col-md-4">
                    <?php if( current_user_can( 'manage_options' )) { ?>
                    <ul class="button-event event-listing">
                        <li class="tooltips update edit"><a data-toggle="tooltip" title="<?php _e("Edit", ET_DOMAIN); ?>" data-original-title="<?php _e("Edit", ET_DOMAIN); ?>" href="#"><span class="icon" data-icon="p"></span></a></li>

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

                        <?php if( $ad->post_status == 'pending') { ?>

                            <li class="tooltips remove reject"><a data-toggle="tooltip" title="<?php _e("Reject", ET_DOMAIN); ?>" data-original-title="<?php _e("Reject", ET_DOMAIN); ?>" href="#"><span class="icon color-purple" data-icon="*"></span></a></li>
                        <?php }else { ?>
                            <li class="tooltips remove archive"><a data-toggle="tooltip" title="<?php _e("Archive", ET_DOMAIN); ?>" data-original-title="<?php _e("Archive", ET_DOMAIN); ?>" href="#"><span class="icon" data-icon="#"></span></a></li>
                        <?php } ?>
                    </ul>
                <?php } ?>
                </div>
            </div>



        </div>
    </div><!--/.title page-->

    <div class="single-page">
        <div class="container main-center listing-page main-content">
            <?php
                get_template_part('template/ad', 'details' );
             ?>
             <?php
            $option = new CE_Options;
            $cm_ad  = $option->get_option('et_comment_ad');
            $status = get_post_status();
            if( $cm_ad && 'publish' == $status ){ ?>
                <div class="comments" id="ad_comment">
                    <h3 class="title"><?php comments_number (__('0 Comment on this Product', ET_DOMAIN), __('1 Comment on this Product', ET_DOMAIN), __('% Comments on this Article', ET_DOMAIN))?> </h3>
                    <?php comments_template('/comments.php', true)?>
                </div> <?php
            }
            ?>

        </div><!--/.main center-->


    </div><!--/.fluid-container categories items-->
    <div class="listing-related" id="listing-related">
        <?php
        $terms      = get_the_terms( get_the_ID(), CE_AD_CAT );
        $cats       = array();
        if(is_array($terms) && !empty($terms)){
            foreach($terms as $term){
                $cats [] = $term->term_id;
            }
        }

        $args = array(
            'post_type'         => CE_AD_POSTTYPE,
            'posts_per_page'    => apply_filters( 'ce_filters_number_ad_related', 16 ),
            'post_status'       => 'publish',
            'post__not_in'      => array(get_the_ID()),
            // 'meta_query'         => array(
            //     array(
            //         'key'      =>'ce_ebay_item_id' ,
            //         'value'  => '',
            //         'compare'       => 'NOT EXISTS'
            //         )
            //     )
        );
        $count  =   0;
        if(count($cats) > 0){
            $args_relevant = wp_parse_args(array
                ('tax_query' => array(
                    array(
                        'taxonomy'  => CE_AD_CAT,
                        'field'     => 'id',
                        'terms'     => (array) $cats,
                        'operator'  => 'IN'
                    )
                ) ), $args);


            global $post,$wp_query;
            $ad_query   = CE_Ads::query($args_relevant);
            $count      = $ad_query->found_posts;
        }

        if($count ==  0)
        $ad_query = CE_Ads::query($args);
        ?>

        <div class="container main-center">
            <div class="btn-group dropdown-left">
                <button class="btn dropdown-toggle selectdrop" data-toggle="dropdown"><span class="select"><?php if($count >  0) _e("Relevant", ET_DOMAIN); else _e("Latest", ET_DOMAIN);?> </span><span class="icon-arrow"><i class="fa fa-arrow-down"></i></span></button>
                <ul class="dropdown-menu">
                <?php if( $count > 0 ) { // remove relevant selector if does not have related post  ?>
                    <li class="relevent <?php if($count >  0) echo 'selected';?>"; rel="relevant" id="<?php the_ID();?>"><a ><?php _e("Relevant", ET_DOMAIN); ?></a></li>
                <?php } ?>
                    <li class="latest <?php if($count ==  0) echo 'selected';?>"  rel="latest" id="<?php the_ID();?>" ><a ><?php _e("Latest", ET_DOMAIN); ?></a></li>
                    <li class ="popular" rel="popular" id="<?php the_ID();?>"><a ><?php _e("Popular", ET_DOMAIN); ?></a></li>

                </ul>
            </div>
            <div class="btn-group contronl-listing">
                <button class="btn  prev-related disabled  " id="1" data = "<?php the_ID();?>" ><span class="arrow-left"><i class="fa fa-arrow-left"></i></span></button>
                <button class="btn next-related" id="2" data='<?php the_ID();?>'><span class="arrow-next"><i class="fa fa-arrow-right"></i></span></button>
            </div>
            <span class="clearfix"></span>
            <div class="col-md-12" id="listing_container">
                <!-- strat carousFredSel !-->
                <div class="row row-carousel">
                    <div class="image_carousel">
                        <div id="ad_carousel">
                        <?php
                            while( $ad_query->have_posts() ) {

                                $ad_query->the_post ();
                                global $post,$publish_list;
                                  $listing          = CE_Ads::convert ($post);
                                  $listing->id      = $listing->ID;
                                  $publish_list[]   = $listing;
                                  get_template_part( 'template/ad', 'carousel' );

                            }
                        ?>
                        </div>
                    </div>
                </div>
                <!-- End carouFredSel !-->
            </div>
        </div><!--/.main center-->
    </div>
</div>
<script type="application/json" id="single_ad_data">
  <?php $ad->id = $ad->ID; echo json_encode($ad); ?>
</script>
<?php 
}
get_footer();
?>
