<?php
global $review;
$user_id    =   (isset($review->user_id)) ? $review->user_id : 0;
$userdata   =   get_userdata ($user_id);

$attitude   =   get_comment_meta( $review->comment_ID, 'attitude' , true );

?>
<div class="list-reviewer-wrapper col-md-6">
    <div class="reviewer">
        <div class="avatar">
            <a href="<?php echo get_author_posts_url( $user_id ); ?>" >
                <?php echo get_avatar( $user_id ); ?>
            </a>
        </div>
        <div class="info-review">
            <h2 class="name">
                <!-- <a href="<?php echo get_author_posts_url( $user_id ); ?>" > -->
                <?php 
                    echo $userdata->display_name;
                 ?>
                <!-- </a> -->
            </h2>
            <?php if($attitude == 'pos') { ?>
                <span class="link-plus" title="<?php _e("Positive", ET_DOMAIN); ?>">
                    <span class="icon-vote">+</span>&nbsp;&nbsp;<?php echo CE_Ads::process_post_date($review->comment_date_gmt);  ?>
                </span>
            <?php }else { ?>
                <span class="link-minus" title="<?php _e("Negative", ET_DOMAIN); ?>">
                    <span class="icon-minus">-</span>&nbsp;&nbsp;<?php echo CE_Ads::process_post_date($review->comment_date_gmt); ?>
                </span>
            <?php } ?>

            <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremq laudantium, totam rem aperiam.</p>
            <!-- <a href="<?php echo get_permalink( $review->comment_post_ID ); ?>"><?php echo  get_the_title( $review->comment_post_ID ); ?></a> -->
        </div>
    </div>
</div>