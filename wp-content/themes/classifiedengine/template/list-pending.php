<?php

    global $pending_list;
    $pending_list = array();
    

    $args_pendding = array(
            'post_type'   => CE_AD_POSTTYPE,
            'post_status' => 'pending',
            'showposts'   => -1,
            'meta_key'    =>  'et_paid',
            'orderby'     =>  'meta_value post_date'
    );               
    $pending_ad = new WP_Query ($args_pendding);
    
    if($pending_ad->have_posts()) { 
    ?>
		<h1 class="title-product pending-title"><?php _e("Pending Classifieds", ET_DOMAIN); ?></h1>       
		<div class="row" id="pending_list">            
        <?php
            while ($pending_ad->have_posts()) { $pending_ad->the_post();
              get_template_part( 'template/ad', 'pending' );
            }
        ?>         
      </div><!--/row-->
    <?php }
    
?>