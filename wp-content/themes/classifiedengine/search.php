<?php
get_header();

global $ce_categories, $wp_query;
$location_list    = ce_get_locations();
$ce_categories    = ce_get_categories();

get_template_part( 'template-search' );
?>

<!-- end search form heaer !-->
<!-- remove slider here -->
<div class="title-page search-page-template">
	<div class="main-center container">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-3">
          		<span class="customize_heading text fontsize30"><?php global $cat_name; echo $cat_name; ?></span>
            </div>
            <div class="col-md-9">
                <!-- order search list -->

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
<div class="main-center container main-content">
	<div class="row">
    <div class="col-md-3 area-f-left">
        <?php
            get_sidebar();
        ?>

    </div><!--/span-->
    <div class="col-md-9 product paddingTop45 area-f-right">

        <h1 class="title-product"><?php printf(__("Search Results for: %s", ET_DOMAIN),get_query_var('s')); ?></h1>

        <div class="row" id="publish_list">
            <?php
            $publish_list   = array();
            $search_query   = CE_Ads::query();

            if($search_query->have_posts()) :
                while ($search_query->have_posts()) { $search_query->the_post();
                    global $post;

                    $item = CE_Ads::convert($post);
                    $item->id   =   $item->ID;
                    $publish_list[]  =   $item;
                    get_template_part( 'template/ad', 'publish' );
                }
            else :

                get_template_part('template/ad', 'notfound' );

            endif;
        ?>
        </div><!--/row-->
        <div class="col-md-12 pagination-page">
        <?php
            ce_pagination($search_query);
        ?>
        </div>
    </div><!--/.Latest product-->
    </div><!--/.main center-->
</div><!--/.fluid-container-->
<script type="application/json" id="publish_ads">
<?php
    echo json_encode($publish_list);
?>
</script>
<script type="application/json" id="pending_ads">
<?php
    $pending_ads = array();
    echo json_encode($pending_ads);
?>
</script>

<?php
get_footer();
?>