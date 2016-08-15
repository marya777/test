<?php
global $review, $wp_query ;

$author	=	$wp_query->queried_object;
$seller	=	ET_Seller::convert ($author);

$type		=	get_query_var( 'review' );
/**
 * explore query var review to get paged
*/
$type_arr	=	explode( '/', $type);

/**
 * check url
*/
if(!empty( $type_arr )) {
	if($type_arr[0] != 'page') {
		$type	=	$type_arr[0];
	}else {
		$type	=	'all';
		$paged =  $type_arr[1] ;
		set_query_var( 'paged' , $type_arr[1] );
	}

	if( isset($type_arr[2] ) )  {
		$paged =  $type_arr[2] ;
		set_query_var( 'paged' ,  $type_arr[2] ) ;
	}
}

$request 	=	CE_Review::get_reviews ($seller, $type , $paged);
extract($request);

/**
 * filter review
*/
$filter	=	array ('all' => __("View all", ET_DOMAIN) , 'pos' =>  __("Positive", ET_DOMAIN) , 'neg' => __("Negative", ET_DOMAIN));
$link	=	get_author_posts_url( $seller->ID);

/**
 * get header
*/
get_header();
?>

<div class="title-page">
	<div class="main-center container">
		<span class="customize_heading text fontsize30">
		<?php
			printf(__("Reviews of %s", ET_DOMAIN) , $seller->display_name )	;
		?>
		</span>
	</div>
</div><!--/.title page-->

<div class="profile-page">
	<div class="container main-center accout-profile">
		<div class="row">
            <div class="col-md-8 product review-list">
				<div class="btn-group dropdown-search" style="margin-top:0px; margin-bottom: 40px">

		            <button class="btn dropdown-toggle featured-home" data-toggle="dropdown">
		                <span class="select"><?php echo $filter[$type]; ?></span>
		                <i class="fa fa-arrow-down" style="margin-left: 20px;"></i>
		            </button>
		            <!-- <a href="#" title="Sort Descending"><i class="fa fa-arrow-circle-o-down icon-arrow-2"></i></a> -->
		            <ul class="dropdown-menu">
		            <?php
		            unset($filter[$type]);
		            $i = 0;
		            global $wp_rewrite;
		            foreach ($filter as $key => $value) {
		                    $i++;
		                    $htp = '';
		                    if ( $wp_rewrite->using_permalinks() ){
		                    	$htp = $link.'review/'.$key ;
		                    } else {
		                    	$htp 		= add_query_arg( array('review' => $key),$link );
		                    }
		                ?>
		                    <li><a href="<?php echo $htp;?>"><?php echo $value ?></a></li>
		                    <?php if($i < 2 ) echo '<li class="divider"></li>';
		                } ?>
		            </ul>
		        </div>

				<div class="row">
				    <?php
				    $i	=	0;
				    foreach ($reviews as $key => $review) {
				    	$i ++ ;
				        get_template_part( 'template/review' , 'item' );
				        if( $i % 2 == 0) echo '<div style="clear:both"></div>';
				    } ?>
				</div>
				<div class="pagination-page">
                    <?php
                       ce_review_pagination( $total_page );
                    ?>
                </div>
            </div><!--/.Latest review-->

			<div class="col-md-4 mobile-desktop">
			  	<?php ce_seller_bar ($seller); ?>
			</div>

		</div>
	</div><!--/.main center-->
</div><!--/.fluid-container categories items-->

<?php get_footer(); ?>