<?php
/**
 * Template blog content
*/
?>

<div class="post-wrapper" <?php echo Schema::BlogPosting() ?>>
    <div class="author-content">
        <span class="avatar"><?php echo get_avatar( get_the_author_meta('ID'), 80 ); ?></span>
        <span class="name" <?php echo Schema::BlogPosting("author") ?>>
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
        <h3 class="entry-title">
            <a title="Permanent link to <?php the_title_attribute(); ?>" rel="bookmark" href="<?php the_permalink(); ?>" <?php echo Schema::BlogPosting("headline") ?>>
                <?php the_title(); ?>
            </a>
        </h3>
        <div class="entry-content" <?php echo Schema::BlogPosting("articleBody") ?>>
		<?php
            if( is_singular( 'post' ) )  {
				the_content();
            } else {
                the_post_thumbnail( 'post-thumbnail' );
                the_excerpt();
            }
				wp_link_pages();
                the_tags();
            if( !is_singular( 'post' ) )  { ?>
                <a  class="read-post" href="<?php trackback_url(); ?>"><?php _e( 'Readmore',ET_DOMAIN ); ?>&nbsp;&nbsp;
                    <i class="fa fa-arrow-right"></i>
                </a>
                <?php 
            } 
            edit_post_link(); 
        ?>
            <div class="clearfix"></div>
        </div>        
    </div>
</div>
