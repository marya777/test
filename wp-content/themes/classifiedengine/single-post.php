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

            <div class="post-wrapper">
                <div class="author-content">
                    <span class="avatar"><?php echo get_avatar( get_the_author_meta('ID'), 80 ); ?></span>
                    <span class="name">
                        <?php the_author_posts_link(); ?>
                        <br />
                        <abbr class="published-time" title="<?php echo get_the_date(); ?>">
                            <?php _e("Posted at ", ET_DOMAIN); echo get_the_date(); ?>
                        </abbr>
                    </span>
                </div>
                <div class="detail-post">
                    <?php the_category(); ?>
                    <span><i class="fa fa-comments"></i><?php comments_popup_link(0, 1 ,'%'); ?> </span>
                </div>
                <div class="clearfix"></div>
                <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <h3 class="entry-title"><?php the_title(); ?></h3>
                    <div class="entry-content">
                        <?php
                            the_content();
                            wp_link_pages();
                        ?>
                    </div>
                    <?php the_tags(); ?>
                    <?php edit_post_link(); ?>
                </div>
            </div>

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