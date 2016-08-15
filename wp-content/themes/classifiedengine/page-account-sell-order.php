<?php
/**
 * Template Name: Seller's order
 */
global $user_ID;
get_header();
?>
	<div class="title-page">
		<div class="main-center container">
			<span class="customize_heading text fontsize30"><?php _e("Sale Order", ET_DOMAIN); ?></span>
			<!-- <div class="account logout">
				<a href="#"><?php _e("Logout", ET_DOMAIN); ?><span class="icon" data-icon="Q"></span></a>
			</div>   -->
		</div>
	</div><!--/.title page-->

	<div class="tabs-acount">
		<div class="main-center container">
			<ul class="nav nav-tabs">
				<li><a title="<?php _e("Views all your ads", ET_DOMAIN); ?>" href="<?php echo et_get_page_link('account-listing') ?>" ><?php _e("Your Listings", ET_DOMAIN); ?></a></li>
				<li><a title="<?php _e("Views all your profile", ET_DOMAIN); ?>"  href="<?php echo et_get_page_link('account-profile'); ?>"><?php _e("Seller Profile", ET_DOMAIN); ?></a></li>
				<li><a title="<?php _e("Change password", ET_DOMAIN); ?>" href="<?php echo et_get_page_link('account-profile', array('section' => 'password')); ?>"><?php _e("Password", ET_DOMAIN); ?></a></li>
				<li><a title="<?php _e("Favourites Ads", ET_DOMAIN); ?>" href="<?php echo et_get_page_link('account-profile', array('section' => 'favourites')); ?>"><?php _e("Favourites Ads", ET_DOMAIN); ?></a></li>
				<?php do_action('page_account_nav_tab') ?>
			</ul>
		</div>
	</div><!--/.title page-->

	<div class="account-page main-content woocommerce" id="seller_listing">

        <?php
        global $wpdb, $user_ID;
        $need_orders = et_get_order_product_by_seller();

        ?>

		<div class="container main-center accout-profile">
		  	<div class="row">
				<div class="col-md-8" id="latest_ads_container">
					<div class="jobs_container">
					    <ul class="list profile-listing order-list" id="profile_listing" >
                            <?php
                            global $user_ID;
                            if ($need_orders) {
                                echo '<ul class="list profile-listing" id="profile_listing" >';
                                foreach ($need_orders as $seller_orders) {
                                    get_template_part('template/profile', 'order');
                                }
                                echo '</ul>';
                            } else {
                                _e('You have no order', ET_DOMAIN);
                            }
                            ?>
                        </ul>
				    </div>
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