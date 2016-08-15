<?php
/**
 * Template Name: Seller's Listing
*/
global $user_ID;
get_header();
$section	=	isset( $_REQUEST['section'])  ? $_REQUEST['section'] : false ;
?>

	<div class="title-page">
		<div class="main-center container">
			<span class="customize_heading text fontsize30"><?php _e("Account", ET_DOMAIN); ?></span>
		</div>
	</div><!--/.title page-->

	<div class="tabs-acount">
		<div class="main-center container">
			<ul class="nav nav-tabs">
				<li class="active">
				  <a title="<?php _e("Views all your ads", ET_DOMAIN); ?>" href="<?php echo et_get_page_link('account-listing')?>" ><?php _e("Your Listings", ET_DOMAIN); ?></a>
				</li>
				<li><a title="<?php _e("Views all your profile", ET_DOMAIN); ?>"  href="<?php echo et_get_page_link('account-profile'); ?>"><?php _e("Seller Profile", ET_DOMAIN); ?></a></li>
				<li><a title="<?php _e("Change password", ET_DOMAIN); ?>" href="<?php echo et_get_page_link('account-profile', array('section' => 'password')); ?>"><?php _e("Password", ET_DOMAIN); ?></a></li>
				<li><a title="<?php _e("Favourites Ads", ET_DOMAIN); ?>" href="<?php echo et_get_page_link('account-profile', array('section' => 'favourites')); ?>"><?php _e("Favourites Ads", ET_DOMAIN); ?></a></li>
				<?php do_action('page_account_nav_tab', $section) ?>
			</ul>
		</div>
	</div><!--/.title page-->

	<div class="account-page main-content" id="seller_listing">


		<div class="container main-center accout-profile">
		  	<div class="row">

				<div class="col-md-8" id="latest_ads_container">
					<div class="jobs_container">
					  	<ul class="list profile-listing" id="profile_listing" >
						<?php
							global $user_ID;
					  		$listing = array();
					  		//$item = array();

					  		$queries = array('reject', 'pending',  'publish', 'draft','archive');
					  		foreach ($queries as $key => $value) {

						  		$ad_query = CE_Ads::query( array('author' => $user_ID, 'post_status' => $value,'meta_key'   =>  'et_paid') );

						  		while( $ad_query->have_posts() ) {
						  			$ad_query->the_post ();
						  			global $post;
						  			get_template_part( 'template/ad', 'profile' );
						  			$item = CE_Ads::convert($post);
						  			$item->id	=	$item->ID;
						  			$listing[]	=	$item;

							  	}
							}
						?>
					  	</ul>
					  	<!-- pagination -->
						<div class="col-md-12 pagination-page">
						<?php

							ce_pagination( $ad_query );
						?>
						</div>

				  </div>

				<script type="application/json" id="listing_ads">
				<?php
				    echo json_encode($listing);
				?>
				</script>
				</div>


				<div class="col-md-4" id="static-text-sidebar">
					<?php
						ce_seller_packages_data();
						get_sidebar();
					?>
					</div>
				</div>
		  	</div>
		</div><!--/.main center-->
	</div><!--/.fluid-container categories items-->


<?php
get_footer();
?>