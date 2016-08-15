<?php
global $wp_query, $publish_list , $pending_list;
$author	=	$wp_query->queried_object;
$seller	=	ET_Seller::convert ($author);

$publish_list	=	array ();
$pending_list	=	array ();

get_header();

?>

<div class="title-page">
	<div class="main-center container">
		<span class="customize_heading text fontsize30">
			<?php
			if( $wp_query->post_count > 1 )
				printf(__("%d items by <strong>%s</strong>", ET_DOMAIN), $wp_query->found_posts , $seller->display_name);
			else
				printf(__("%d item by <strong>%s</strong>", ET_DOMAIN), $wp_query->found_posts , $seller->display_name);
			 ?>
		</span>
	</div>
</div><!--/.title page-->

<div class="profile-page">
	<div class="container main-center accout-profile">
		<div class="row">
			<div class="col-md-8 product classified-list">

				<div class="row " id="publish_list">
    				<?php
    					if(have_posts()) :
    						while(have_posts()) { the_post();
    							get_template_part( 'template/ad', 'publish' );
    						}
    					else :
    						get_template_part('template/ad', 'notfound' );
    					endif;
    				?>
				</div><!--/row-->
                <div class="pagination-page">
                    <?php
                        ce_pagination($wp_query);
                    ?>
                </div>

			</div><!--/.Latest product-->

			<div class="col-md-4 mobile-desktop">

			  	<?php ce_seller_bar($seller); ?>

			</div>

		</div>
	</div><!--/.main center-->
</div><!--/.fluid-container categories items-->
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

<?php
get_footer();