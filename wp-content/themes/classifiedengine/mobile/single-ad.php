<?php
et_get_mobile_header();
if(have_posts()) { the_post();
    global $post;
    $ad     =   CE_Ads::convert($post);
    $seller =   ET_Seller::convert( get_userdata($ad->post_author));

    $thumnail   =   wp_get_attachment_url( get_post_thumbnail_id( ) );

    $attachment = get_children( array(
            'numberposts' => 15,
            'order' => 'ASC',
            'post_mime_type' => 'image',
            'post_parent' => $post->ID,
            'post_type' => 'attachment'
          ),OBJECT );

?>   
    <div data-role="content">
        <?php if(!empty($attachment) ) { ?>
        <div class="sileshow">
            <div class="carousel slide" id="myCarousel">
                <ol class="<?php if( count($attachment) == 1) echo 'one-carousel'; ?> carousel-indicators ">
                <?php
                $i=0;
                foreach ($attachment as $key => $att) {  ?>
                    <li class="<?php if($i== 0) echo 'active'; ?>" data-slide-to="<?php echo $i ?>" data-target="#myCarousel"></li>
                <?php $i++; } ?>
                </ol>
                <!-- Carousel items -->
                <div class="carousel-inner">
                    <?php
                    $i=0;
                    foreach ($attachment as $key => $att) {
                        $image    = wp_get_attachment_image_src( $att->ID, 'full' );
                    ?>
                    <div class="item <?php if($i == 0 ) echo 'active'; ?>"><img src="<?php echo $image[0] ?>"></div>
                    <?php $i++; } ?>
                </div>
                <?php if(count($attachment) > 1) { ?>
                <!-- Carousel nav -->
                <a data-slide="prev" href="#myCarousel" class="carousel-control left prev"></a>
                <a data-slide="next" href="#myCarousel" class="carousel-control right next"></a>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
        <!-- listing title -->
        <div class="title-listing">
            <span class="text"><?php the_title(); ?></span>
        </div>
        <!-- listing info -->
        <div class="price-address">
            <div class="price"><?php echo $ad->price ?></div>
            <div class="address">
                <i class="fa fa-map-marker"></i>
                <span class="text"><?php echo $ad->et_full_location ?></span>
            </div>
        </div>
        <!-- button -->
        <div class="content-listing">
            <div class="tabs clearfix">
                <a class="ui-tabs ui-corner-left ui-link tab-active"><i class="fa fa-info-circle"></i><span class="border-arrow"><span class="arrow"></span></span></a>
                <a class="ui-tabs ui-corner-left ui-link mail"><i class="fa fa-envelope-o"></i><span class="border-arrow"><span class="arrow"></span></span></a>
                <?php do_action('ce_display_tab_nav',get_the_ID() );?>
                <a class="ui-tabs ui-corner-left ui-link"><i class="fa fa-phone"></i><span class="border-arrow"><span class="arrow"></span></span></a>
                <a class="ui-tabs ui-corner-left ui-link"><i class="fa fa-user"></i><span class="border-arrow"><span class="arrow"></span></span></a>
                <a class="ui-tabs ui-corner-right ui-link"><i class="fa fa-reply-all "></i><span class="border-arrow"><span class="arrow"></span></span></a>
            </div>
            <div class="content-tabs">
                <!-- listing details -->
                <div class="tab-cont" style="display: block;">
                    <div class="date-update"><?php echo $ad->date_ago ?></div>
                    <?php the_content(); ?>
                    <p><?php do_action('ce_detail_ad_more',get_the_ID()); ?></p>
                    <?php
                        $option = new CE_Options;
                        $cm_ad  = $option->get_option('et_comment_ad');
                        $status = $post->post_status;
                        if( $cm_ad && 'publish' == $status ){ ?>
                            <div class="blog-info-cmt">
                                <h3 class="title"><?php comments_number (__('0 Comment on this Product', ET_DOMAIN), __('1 Comment on this Product', ET_DOMAIN), __('% Comments on this Product', ET_DOMAIN))?> </h3>
                            </div> 
                            <?php comments_template('/comments.php', true)?>
                            
                    <?php
                        }
                    ?>
                </div>
                <!-- contact seller form -->
                <div class="tab-cont mail-form" style="display: none;">
                    <h1><?php _e("Email this seller", ET_DOMAIN); ?></h1>
                    <?php
                    $user_lname = '';
                    $email      = '';
                    $phone      = '';

                    if ( is_user_logged_in() ) {
                        global $display_name , $user_email,$user_login;
                        get_currentuserinfo();
                        $user_lname = (!empty($current_user->display_name)) ? $current_user->display_name : $user_login;
                        $email      = $user_email;
                        //$seller     = ET_Seller::convert($current_user);
                        //$phone      = $seller->et_phone;
                        $phone      = '';

                    }
                    $user_lname = isset($_COOKIE['contactor_lname']) ? $_COOKIE['contactor_lname'] : $user_lname;
                    $email      = isset($_COOKIE['contactor_email']) ? $_COOKIE['contactor_email'] : $email;
                    $phone      = isset($_COOKIE['contactor_phone']) ? $_COOKIE['contactor_phone'] : $phone;

                    ?>
                    <form class="form-horizontal" id="contact-seller" data-ajax="false">
                        <input type="hidden" value="<?php echo $seller->ID; ?>" id="seller_id" name="seller_id">
                        <input type="hidden" value="<?php the_ID(); ?>" id="ad_id" name="ad_id">
                        <input type="hidden" value="ce-send-seller-message" id="email_seller" name="action">
                        <div class="control-group">
                            <div class="controls">
                                <input type="hidden" placeholder="<?php _e("Your name", ET_DOMAIN); ?>" id="first_name" name="first_name">
                                <input type="text" value="<?php echo $user_lname;?>"  placeholder="<?php _e("Your name", ET_DOMAIN); ?>" id="last_name" name="last_name">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="controls">
                                <input type="text" value="<?php echo $email;?>" placeholder="<?php _e("Your email", ET_DOMAIN); ?>" id="email_user" name="email_user">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="controls">
                                <input type="text" value="<?php echo $phone;?>" placeholder="<?php _e("Your phone number", ET_DOMAIN); ?>" id="phone" name="phone">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="controls">
                                <textarea placeholder="<?php _e("Your message", ET_DOMAIN); ?>" rows="3" id="message" name="message"></textarea>
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="controls">
                                <button class="btn" type="submit"><?php _e("Send", ET_DOMAIN); ?></button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php do_action('ce_display_tab_ad_content', get_the_ID() ); ?>

                <div class="tab-cont tab-numberphone" style="display: none;">
                    <?php if($seller->et_phone) { ?>
                        <p class='tab-numberphone-text'>
                            <?php printf( __("Call this seller.<br> His name is <span class='name-numberphone'> %s </span>.", ET_DOMAIN), $seller->display_name );?>
                        </p>
                        <p class="call-me">
                            <a href="tel:<?php echo $seller->et_phone  ?>">
                                <?php printf(__("Call <span>%s</span>", ET_DOMAIN), $seller->et_phone); ?>
                            </a>
                        </p>
                        <p class='tab-numberphone-text'><?php _e("or", ET_DOMAIN); ?> <a href="tel:<?php echo $seller->et_phone  ?>"><?php _e("copy  phone number", ET_DOMAIN); ?></a></p>
                    <?php } else { ?>
                        <p class='tab-numberphone-text'>
                            <?php  _e("This seller hasn't <br>included a phone number.", ET_DOMAIN); ?>
                        </p>
                        <?php
						  echo '<p style="text-align:center;"><img src="'.TEMPLATEURL.'/img/sad.png"></p>';
					   ?>
                        <p class='tab-numberphone-text open-mail'>
                            <a href='#'> <?php _e("Send to seller an e-mail instead.", ET_DOMAIN);?></a>
                        </p>
                    <?php } ?>
                </div>

                <!-- author profile -->
                <div class="tab-cont profile-listing"   style="display: none;">
                    <a data-ajax="false" href="<?php echo get_author_posts_url( $seller->ID); ?>" title="<?php printf(__("View more ad posted by %s", ET_DOMAIN), $seller->display_name) ?>">
                        <?php echo get_avatar( $seller->ID ); ?>
                    </a>
                    <div class="text-profile">
                        <h1><?php echo $seller->display_name ?></h1>
                        <span class="sub-intro"><?php echo $seller->et_address; ?></span>
                    </div>
                    <?php do_action("cm_after_seller_info",$seller);?>
                </div>
                <!-- social share -->
                <div class="tab-cont" style="display: none; text-align:center;">
                    <h1><?php _e("Share this Ad", ET_DOMAIN); ?></h1>
                    <div class="f-left">
                        <a href="http://www.facebook.com/sharer.php?u=<?php the_permalink();?>&t=<?php the_title(); ?>" class="ui-link bnt social">Facebook<i class="fa fa-facebook" style="color:#3b5998"></i></a>
                        <a href="http://twitter.com/home?status=<?php the_title(); ?> - <?php the_permalink(); ?>" class="ui-link bnt social">Twitter<i class="fa fa-twitter" style="color:#00acee"></i></a>
                    </div>
                    <div class="f-right">
                        <a href="https://plus.google.com/share?url=<?php the_permalink();?>" class="bnt social">Google+<i class="fa fa-google-plus" style="color:#ea978e"></i></a>
                        <a href="http://www.pinterest.com/pin/create/button/?url=<?php the_permalink();?>&media=<?php echo $thumnail ?>" class="bnt social">Pinterest<i class="fa fa-pinterest" style="color:#c8232c"></i></a>
                    </div>
                </div>

            </div>
        </div>
    </div><!-- /content -->
<?php }
    et_get_mobile_footer();
 ?>
