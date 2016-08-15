<?php 
/**
 * Template Name: Seller's Profile
*/
get_header();
global $user_ID;
$orders			=	ET_Seller::get_current_order($user_ID);
$package_data	=	ET_Seller::get_package_data($user_ID);

$section	=	isset( $_REQUEST['section'])  ? $_REQUEST['section'] : false ;
?>
     <div class="title-page">
		<div class="main-center container">
			<span class="customize_heading text fontsize30"><?php _e("Transactions", ET_DOMAIN); ?></span>
			<!-- <div class="account logout">
				<a href="<?php echo wp_logout_url(home_url()); ?>" /><?php _e('Logout',ET_DOMAIN);?> <span class="icon" data-icon="Q"></span></a>
			</div>   -->
		</div>
	</div><!--/.title page-->

	<div class="tabs-acount">
		<div class="main-center container">
			<ul class="nav nav-tabs">
				<li>
				  <a title="<?php _e("Views all your ads", ET_DOMAIN); ?>" href="<?php echo et_get_page_link('account-listing')?>" ><?php _e("Your Listings", ET_DOMAIN); ?></a>
				</li>
				<li><a title="<?php _e("Views all your profile", ET_DOMAIN); ?>"  href="<?php echo et_get_page_link('account-profile'); ?>"><?php _e("Seller Profile", ET_DOMAIN); ?></a></li>
				<li><a title="<?php _e("Change password", ET_DOMAIN); ?>" href="<?php echo et_get_page_link('account-profile', array('section' => 'password')); ?>"><?php _e("Password", ET_DOMAIN); ?></a></li>
				<li><a title="<?php _e("Favourites Ads", ET_DOMAIN); ?>" href="<?php echo et_get_page_link('account-profile', array('section' => 'favourites')); ?>"><?php _e("Favourites Ads", ET_DOMAIN); ?></a></li>
				<?php do_action('page_account_nav_tab', $section) ?>
			</ul>
		</div>
	</div><!--/.title page-->

	<div class="page-account-profile main-content woocommerce" id="seller_profile">
		<div class="container main-center accout-profile">
			<div class="row row-fluid">
				<div class="col-md-8 span8">
                    <div class="jobs_container">
                        <form method="post" class=" transaction-select-form">
                            <label for="month"><?php _e("Start date", ET_DOMAIN) ?> : </label>
                            <input type="text" name="start_date" id="start_date" data-date-format="yyyy-mm-dd" value="<?php echo isset($_POST['start_date'])?$_POST['start_date']:'' ?>"/>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <label for="month"><?php _e("End date", ET_DOMAIN) ?> : </label>
                            <input type="text" name="end_date" id="end_date" data-date-format="yyyy-mm-dd" value="<?php echo isset($_POST['end_date'])?$_POST['end_date']:'' ?>""/>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="submit" value="<?php _e("View", ET_DOMAIN) ?>"/>
                        </form>
                        <div class="transaction_list">
                            <table class="shop_table cart" style="width: 100%">
                                <thead>
                                    <th><?php _e("Date", ET_DOMAIN) ?></th>
                                    <th><?php _e("Sale", ET_DOMAIN) ?></th>
                                    <th><?php _e("Earning", ET_DOMAIN) ?></th>
                                </thead>
                                <tbody>
                                <?php et_render_transaction_report(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
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

<?php get_footer(); ?>