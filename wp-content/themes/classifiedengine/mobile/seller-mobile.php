<?php 
	global $ce_seller;
	$user_id = $ce_seller->ID;
    $local  = $ce_seller->user_location;
?>
<li data-icon="false">
    <div class="intro-profile">
    	<a href="<?php echo get_author_posts_url( $ce_seller->ID) ?>" title="<?php printf(__("View all ads by %s", ET_DOMAIN), $ce_seller->display_name) ?>" >
        	<?php echo get_avatar( $ce_seller->ID, 60 ) ?>
            <div class="seller-detail">
                <span class="seller-name"><?php echo $ce_seller->display_name ?></span>
                <span class="sembold colorgray"><?php if($local != '') echo '</br>'.$local; ?></span>
                <span class="date-join"><?php echo $ce_seller->joined_date; ?></span>
            </div>
         </a>
    </div>
    <!-- <div class="list-product-seller">
    	<ul>
        	<li><a href="#"><img src="http://placehold.it/25x25/42bdc2/FFFFFF"></a></li>
            <li><a href="#"><img src="http://placehold.it/25x25/42bdc2/FFFFFF"></a></li>
            <li><a href="#"><img src="http://placehold.it/25x25/42bdc2/FFFFFF"></a></li>
            <li><a href="#"><img src="http://placehold.it/25x25/42bdc2/FFFFFF"></a></li>
            <li><a href="#"><img src="http://placehold.it/25x25/42bdc2/FFFFFF"></a></li>
            <li><a href="#"><img src="http://placehold.it/25x25/42bdc2/FFFFFF"></a></li>
            <li><a href="#"><img src="http://placehold.it/25x25/42bdc2/FFFFFF"></a></li>
            <li><a href="#"><img src="http://placehold.it/25x25/42bdc2/FFFFFF"></a></li>
            <li><a href="#"><img src="http://placehold.it/25x25/42bdc2/FFFFFF"></a></li>
        </ul>
    </div> -->
</li>
