<?php
global $wp_query;
et_get_mobile_header();
$author =   $wp_query->queried_object;
$seller =   ET_Seller::convert ($author);

$type       =   get_query_var( 'review' );
/**
 * explore query var review to get paged
*/
$type_arr   =   explode( '/', $type);

/**
 * check url
*/
if(!empty( $type_arr )) {
    if($type_arr[0] != 'page') {
        $type   =   $type_arr[0];
    }else {
        $type   =   'all';
        $paged =  $type_arr[1] ;  
        set_query_var( 'paged' , $type_arr[1] );
    }       

    if( isset($type_arr[2] ) )  { 
        $paged =  $type_arr[2] ;  
        set_query_var( 'paged' ,  $type_arr[2] ) ;
    }
}

$request    =   CE_Review::get_reviews ($seller,$type , $paged);
extract($request);

/**
 * filter review 
*/
$filter =   array ('all' => __("View all", ET_DOMAIN) , 'pos' =>  __("Positive", ET_DOMAIN) , 'neg' => __("Negative", ET_DOMAIN));
$link   =   get_author_posts_url( $seller->ID); 


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
    <div data-role="content">
        <div class="profile-seller">
            <!-- <img class="img-circle" src="img/seller.jpg"> -->
            <?php echo get_avatar( $seller->ID ); ?>
            <div class="text-profile" style="text-align: initial;">
                <h1><?php echo $seller->display_name; ?></h1>
                <span class="sub-intro"><?php echo $seller->user_location; ?></span>
            </div>
        </div>
        
        <!-- Author review -->
        <div class="text-vote">               
            <span class="link-plus">
                <a href="<?php echo $post_author_url.'/review/pos'; ?>">
                    <span class="icon-vote">+</span>&nbsp;&nbsp;<?php printf(__("Positive: %d", ET_DOMAIN) , count($pos_reviews) ) ?>         
                </a>
            </span> 
            <span class="link-minus">
                <a href="<?php echo $post_author_url.'/review/neg'; ?>">
                    <span class="icon-minus">-</span>&nbsp;&nbsp;<?php printf(__("Negative: %d", ET_DOMAIN) , count($neg_reviews) ) ?>            
                </a>
            </span>
               <!--  <a href="#" class="link-submit-review submit-review" data-id="3" data-name="aasdasd asdasdasd">
                    <i class="fa fa-comment"></i>&nbsp;&nbsp;<?php _e("Post your review", ET_DOMAIN); ?>
                </a> -->
        </div>

        <!-- End / Author review -->
        <div class="option-review-wrapper">
            <div data-role="main" class="ui-content">
                <div class="review-filter" data-role="collapsible" data-collapsed-icon="arrow-d" data-expanded-icon="arrow-u" data-iconpos="right">
                    <h1><?php if($type == '' || $type == 'all') {
                            _e("View All Reviews", ET_DOMAIN);
                        }else {
                            if($type == 'pos') {
                                _e("Positive Reviews", ET_DOMAIN); 
                            }else { 
                                _e("Negative Reviews", ET_DOMAIN) ;
                            }
                        }

                     ?>

                    </h1>
                    <p class="all">
                        <a data-ajax="false" <?php if($type == '' || $type == 'all') echo 'class="active"'; ?> data-review="all" href="<?php echo $link.'/review/all'; ?>">
                            <span class="icon-vote dotted">.</span>&nbsp;&nbsp;<?php _e("All", ET_DOMAIN); ?>
                        </a>
                    </p>
                    <p>
                        <a data-ajax="false" <?php if($type == 'pos' ) echo 'class="active"'; ?> data-review="pos" href="<?php echo $link.'/review/pos'; ?>">
                            <span class="icon-vote">+</span>&nbsp;&nbsp;<?php _e("Positive", ET_DOMAIN); ?>
                        </a>
                    </p>
                    <p>
                        <a data-ajax="false" <?php if($type == 'neg' ) echo 'class="active"'; ?> data-review="neg" href="<?php echo $link.'/review/neg'; ?>">
                            <span class="icon-minus">-</span>&nbsp;&nbsp;<?php _e("Negative", ET_DOMAIN); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
        <div class="list-reviews" >
            <?php 
                $i  =   0;
                foreach ($reviews as $key => $review) {
                    $i ++ ;
                    get_template_part( 'template/review' , 'item' );
                    if( $i % 2 == 0) echo '<div style="clear:both"></div>';
                } ?> 
        </div>
        <?php 
        if( $request['total_page'] > 1 ) { ?>
            <div id="review-inview" data-author="<?php echo $seller->user_login ?>" data-review="<?php echo $type; ?>"></div> 
        <?php }
        ?>  
    </div><!-- /content -->
<?php
et_get_mobile_footer();
