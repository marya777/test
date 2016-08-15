<?php
    et_get_mobile_header();
 ?>

<div data-role="content" style="margin-top: 91px;">

    <?php 
    if ( is_active_sidebar( 'mobile_header' ) ) :
        echo '<div class="mobile-sidebar header-sidebar">';              
        dynamic_sidebar( 'mobile_header' );
        echo '</div>';
       
    endif;
    ?>

    <ul data-role="listview" class="latest-list">
    <?php 
    if( have_posts() ) {
        while(have_posts()) { the_post(); 
            global $flag ;
            get_template_part('mobile/ad', 'mobile') ;
        }
    }else { ?>
        	<li class="no-result">
        		<h2>
        			<?php _e( 'There is currently no ads.', 'ET_DOMAIN' ); ?> <br/>
        			<a data-ajax="false" href="<?php echo et_get_page_link('post-ad'); ?>" > <?php _e("Submit a new ad now!", ET_DOMAIN); ?> </a>
        		</h2>
        	</li>
        	
        <?php } ?> 
    </ul>

    <div class="inview" ></div>
     <?php 
    if ( is_active_sidebar( 'mobile_footer' ) ) :
        echo '<div class="mobile-sidebar footer-sidebar">';              
        dynamic_sidebar( 'mobile_footer' );
        echo '</div>';
       
    endif;
    ?>
</div><!-- /content -->
<?php
et_get_mobile_footer();