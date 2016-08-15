<?php
    et_get_mobile_header();
 ?>
 
<div data-role="content" style="margin-top: 91px;">
    <ul data-role="listview" class="latest-list">
    <?php
    if(have_posts()){ 
        while(have_posts()) { the_post(); 
            global $flag ;
            get_template_part('mobile/ad', 'mobile') ;
        } 
    } else { ?>
    	<li style="display:none" class="no-result list-divider"><h2><?php printf( __( 'Don\'t have ad in  %s category', 'ET_DOMAIN' ), single_cat_title( '', false ) ); ?></h2>h2></li> <?php 
    }?>             
    </ul>    
    <div class="inview" ></div>
</div><!-- /content -->
<?php
et_get_mobile_footer();