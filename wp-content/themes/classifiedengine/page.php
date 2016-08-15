<?php
    get_header();

    global $post;

    if(have_posts()) { the_post();

        $current_page = $post;
        $current_id  = $post->ID;

        if( $post->post_parent == 0 )
            $post_id = $current_id;
        else
            $post_id = $post->post_parent;

            $args = array(
                'child_of'=>    $post_id,
                'parent'  =>    $post_id
            );

            $page_childs = get_pages($args);

    ?>
        <div class="title-page">
            <div class="main-center container">
        	<h1 class="text single-title"><?php the_title();?></h1>
            </div>
        </div>
        <div class="container main-center main-content">
            <div class="row">
                <?php if($page_childs) : ?>
                <div class="col-md-3">
                    <div class="well sidebar-nav">
                        <ul class="nav nav-list menu-left-page">
                            <li <?php if( $post_id == $current_id ) echo 'class ="active"';?> > <a href="<?php echo get_permalink($post_id);?>">
                                <?php echo apply_filters('the_title', get_the_title($post_id)) ;?></a> </li>
                            <?php $class='';
                                foreach($page_childs as $page){
                                    $class = ($page->ID == $current_id ) ? 'class ="active"' : '';
                                    echo '<li '.$class.'><a href="'.get_permalink($page->ID).'">'.$page->post_title,'</a></li>';
                                }
                            ?>
                        </ul>
                        <?php
                            wp_reset_query();
                            // get_sidebar();
                        ?>
                    </div><!--/.well -->
                </div><!--/span-->
                <?php endif;?>
                <div class="col-md-9 contenttext paddingTop34">
               		<?php the_content(); ?>
                </div>
            </div><!--/.main center-->
        </div><!--/.fluid-container-->
    <?php
    }

get_footer();
