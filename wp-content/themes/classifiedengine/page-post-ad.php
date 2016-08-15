<?php
/**
 * Template Name: Post Classified
*/

global $user_ID, $ce_config, $current_user, $ad;

if(isset($_REQUEST['id'])) {
	$ad	=	CE_Ads::convert (get_post($_REQUEST['id']));
	if( $user_ID != $ad->post_author )
		wp_redirect( et_get_page_link('post-ad') );
}

get_header();
global $step, $useCaptcha, $term_of_use, $disable_payment, $ad_currency, $plans;
$step				=	array ('1' , '2', '3' , '4');
$currency			=	ET_Payment::get_currency();
$plans				=	et_get_payment_plans ();
$package_data		=	ET_Seller::get_package_data($user_ID);

$option 			= new CE_Options;
$ad_currency 		= $option->get_option('et_currency_sign');
$disable_payment 	= et_get_payment_disable();
$useCaptcha 		= $option->use_captcha();
$term_of_use		= et_get_page_link('terms-of-use' , array () , false);
?>

<div class="title-page">
  	<div class="main-center container">
    	<span class="customize_heading text fontsize30"><?php _e("Post an Ad", ET_DOMAIN); ?></span>
    </div>
</div><!--/.title page-->

<div class="post-page main-content" id="post-classified">
    <div class="container main-center accout-profile">
      	<div class="row">
	        <div class="col-md-8">
	        	<?php
	        		if(empty($plans) && current_user_can( 'manage_options' ) && !$disable_payment) {
	        			echo "<p style='background: #F5F5F0;padding: 5px;margin-bottom: 5px;color: #E18972;border-radius: 2px;font-size: 10px; '>" ;
						printf(__("This function is disabled because there are no payment plans to display. Please go to <a href='%s'>Settings</a> to create payment plans.", ET_DOMAIN), admin_url( 'admin.php?page=et-settings#section/setting-payment' ));
						echo "</p>";
	 				}
	 			?>
	          	<div class="post-to-classifieds">
		            <div class="post-step">
						<?php
							if( !empty($plans) && !$disable_payment ) // load list plan
								get_template_part('template/step','1');

							if(!is_user_logged_in() ) // load form login
								get_template_part('template/step','2');

							// Post Ad form.
							get_template_part('template/step','3');

							// load list payment gateway
							get_template_part('template/step','4');
						?>

		            </div>
	          	</div>
	          	<?php if($term_of_use) {?>
              		<div class="term-of-use">
							<?php printf( __("By posting your ad, you agree to our <a target='_blank' href='%s' > Terms of use </a> ", ET_DOMAIN) , $term_of_use ) ; ?>
							(*)
  					</div>
				<?php } ?>
        	</div>
	       		 <div class="col-md-4" id="static-text-sidebar">
					<?php
						ce_seller_packages_data();
						get_sidebar();
					?>
				</div>
			</div>
    </div><!--/.main center-->
</div><!--/.fluid-container categories items-->
<script type="application/json" id="ad_data">
	<?php echo json_encode($ad);?>
</script>
<?php get_footer(); ?>