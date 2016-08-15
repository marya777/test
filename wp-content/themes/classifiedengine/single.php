<?php
get_header();
if(have_posts()) {
the_post();
?>
<div class="title-page">
    <div class="main-center container">
        <span class="text"><?php the_title();?></span>
    </div>
</div>

<div class="main-center container main-content">
	<div class="row">
        <div class="col-md-9 product paddingTop45 area-f-right">
            <?php
                get_template_part( 'template/content' , 'blog' );
        	?>
        		<div class="comments">
        			<h3 class="title"><?php comments_number (__('0 Comment on this Article', ET_DOMAIN), __('1 Comment on this Article', ET_DOMAIN), __('% Comments on this Article', ET_DOMAIN))?> </h3>
              	    <?php comments_template('', true)?>
              	</div>

		</div>
        <div class="col-md-3 area-f-left">
            <?php
                get_sidebar('blog');
            ?>
        </div><!--/span-->
	</div>
</div>

<?php
    } // If comments are open: delete this and the sky will fall on your head

get_footer();