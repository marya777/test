<?php
global $wp_query;
et_get_mobile_header();
$author =   $wp_query->queried_object;
$seller =   ET_Seller::convert ($author);


$args       =   array ( 'meta_key' => 'attitude' ,  'comment_type' => 'review' , 'post_author' => $seller->ID );
        
$pos_args   =   $args;
$pos_args['meta_value'] =   'pos';


$neg_args   =   $args;
$neg_args['meta_value'] =   'neg';

// $seller      =   ET_Seller::convert ($author);
$neg_reviews    =   get_comments( $neg_args );
$pos_reviews    =   get_comments( $pos_args );

$post_author_url    =   get_author_posts_url( $seller->ID); 

 ?>  
    <div data-role="content" class="author-archive" <?php echo Schema::Organization() ?>>
        <div class="profile-seller">
            <!-- <img class="img-circle" src="img/seller.jpg"> -->
            <?php echo get_avatar( $seller->ID ); ?>
            <div class="text-profile" style="text-align: initial;">
                <h1 <?php echo Schema::Organization("legalName") ?>><?php echo $seller->display_name; ?></h1>
                <span class="sub-intro" <?php echo Schema::Organization("address"); ?>><?php echo $seller->user_location; ?></span>
                
            </div>

        </div>
        <div class="text-vote">               
            <span class="link-plus">
                <a data-ajax="false" href="<?php echo $post_author_url.'/review/pos'; ?>">
                    <span class="icon-vote">+</span>&nbsp;&nbsp;<?php printf(__("Positive: %d", ET_DOMAIN) , count($pos_reviews) ) ?>         
                </a>
            </span> 
            <span class="link-minus">
                <a data-ajax="false" href="<?php echo $post_author_url.'/review/neg'; ?>">
                    <span class="icon-minus">-</span>&nbsp;&nbsp;<?php printf(__("Negative: %d", ET_DOMAIN) , count($neg_reviews) ) ?>            
                </a>
            </span>
               <!--  <a href="#" class="link-submit-review submit-review" data-id="3" data-name="aasdasd asdasdasd">
                    <i class="fa fa-comment"></i>&nbsp;&nbsp;<?php _e("Post your review", ET_DOMAIN); ?>
                </a> -->
        </div>
        <h2>
            <?php 
            if( $wp_query->post_count > 1 )  
                printf(__("%d Active Ads", ET_DOMAIN), $wp_query->found_posts , $seller->display_name);
            else 
                printf(__("%d Active Ad", ET_DOMAIN), $wp_query->found_posts , $seller->display_name);
             ?>
        </h2>
        
        <ul data-role="listview" class="latest-list">
        <?php if(have_posts()) { 
            while (have_posts()) { the_post(); 
                get_template_part('mobile/ad', 'mobile') ;
        ?>
            
        <?php } ?>
            
        <?php } else { // print no ad message
            echo '<li class="no-result">'.__("No Active Ad", ET_DOMAIN).'</li>';
        } ?>
        </ul>
        <?php 
        if(have_posts()) { ?>
            <div id="author-inview" data-author="<?php echo $seller->ID ?>"></div> 
        <?php }
        ?>  
    </div><!-- /content -->
<?php
et_get_mobile_footer();
