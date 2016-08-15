<?php
global $wp_query, $publish_list;;

    get_header();

    $publish_list =array();
    $pending_list = array();

    get_template_part( 'template-search' );

    if(is_active_sidebar('sidebar-home-top')) { 
    ?>
        <div class="sidebar-top">
            <?php 
                dynamic_sidebar('sidebar-home-top');
            ?>
        </div>
    <?php
    }
?>
    <div class="title-page">
    	<div class="main-center container">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-3" >
              		<span class="customize_heading text ">
                        <?php if( $wp_query->found_posts == 1 ) printf(__('%s Active Classified',ET_DOMAIN),$wp_query->found_posts);   ?>
                        <?php if( $wp_query->found_posts > 1 ) printf(__('%s Active Classifieds',ET_DOMAIN),$wp_query->found_posts);   ?>
                    </span>
                    </div>
                    <div class="col-md-9">
                        <?php ce_ad_order_filter(); ?>

                  		<ul class="icon-view">
                  			<li class="grid"><span><i class="fa fa-th"></i></span></li>
                  			<li class="list"><span><i class="fa fa-align-justify"></i></span></li>
                  		</ul>
                    </div>
                </div>
            </div>
    	</div>
    </div>
    <div class="main-center container main-content" >
    	<div class="row">
            <div class="col-md-9 product paddingTop45 area-f-right">
                <?php if(have_posts()) : ?><h1 class="title-product"><?php printf(__("Latest Classifieds in %s", ET_DOMAIN),  single_tag_title( '', false )); ?></h1> <?php endif; ?>

                <div class="row"  id="publish_list">

                    <?php
                    if(have_posts()) :
                        while (have_posts()) { the_post();
                            get_template_part( 'template/ad', 'publish' );
                        }
                    else :
                        get_template_part('template/ad', 'notfound' );
                        endif;
                    ?>
                </div><!--/row-->
                <div class="col-md-12 pagination-page">
                    <?php ce_pagination(); ?>
                </div>
            </div><!--/.Latest product-->
            <div class="col-md-3 area-f-left">
                <?php
                    get_sidebar();
                ?>
            </div><!--/span-->
        </div><!--/.main center-->

    </div><!--/.fluid-container-->

        <?php
        if(is_active_sidebar('sidebar-home-bottom')) { ?>
            <div class="sidebar-bottom">
            <?php 
                dynamic_sidebar('sidebar-home-bottom');
            ?>
            </div>
        <?php } ?>

        <script type="application/json" id="publish_ads">
        <?php
            echo json_encode($publish_list);
        ?>
        </script>
        <script type="application/json" id="pending_ads">
        <?php
            echo json_encode($pending_list);
        ?>
        </script>
<?php  get_footer(); ?>