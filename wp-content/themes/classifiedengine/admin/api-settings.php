<?php 
class ET_AdminAPI extends ET_AdminMenuItem {

	private $options;

	function __construct(){
		parent::__construct('et-api-setting',  array(
			'menu_title'	=> __('Advanced Settings', ET_DOMAIN),
			'page_title' 	=> __('ADVANCED SETTINGS', ET_DOMAIN),
			'callback'   	=> array($this, 'menu_view'),
			'slug'			=> 'et-api-setting',
			'page_subtitle'	=> __('ClassifiedEngine ADVANCED SETTINGS', ET_DOMAIN),
			'pos' 			=> 25,
			'icon_class'	=> 'icon-gear'
		));
		$this->add_action( 'wp_ajax_ce-save-setting', 'save_settings' );
		$this->add_action( 'wp_ajax_ce-disable-setting', 'save_settings' );
		$this->add_action( 'wp_ajax_ce-enable-setting', 'save_settings' );
	}

	function save_settings () {
		$response	=	array('success' => false );
		if(!current_user_can( 'manage_options' )) {
			wp_send_json( $response );
		}
		global $wp_rewrite;
		$response['success']	=	true;
		set_theme_mod( $_REQUEST['setting'] , trim($_REQUEST['value']) );

		flush_rewrite_rules();
		wp_send_json( $response );
	}

	public function menu_view ($args) {
	?>
		<div class="et-main-header">
			<div id="api_setting_point" class="title font-quicksand"><?php echo $args->menu_title ?></div>
			<div class="desc"><?php _e("Manage your site's URL structure and loading options", ET_DOMAIN); ?></div>
		</div>
		<div class="et-main-content" id="advanced_settings">
			<div class="et-main-main clearfix inner-content" style="margin-left : 0;">
				<div class="title font-quicksand"><?php _e("Ad Slug", ET_DOMAIN); ?></div>
				<div class="desc">
					<?php _e("Enter slug for your Single Ad page", ET_DOMAIN); ?>
					<div class="form no-margin no-padding no-background">
						<div class="form-item">
							<input class="option-item bg-grey-input " type="text" value="<?php echo get_theme_mod( 'ad_post', CE_AD_POSTTYPE ); ?>" id="post_slug" name="ad_post" />
						</div>
					</div>
				</div>

				<div class="title font-quicksand"><?php _e("Ad Archives Slug", ET_DOMAIN); ?></div>
				<div class="desc">
					<?php _e(" Enter slug for your Archived Ads page", ET_DOMAIN); ?>
					<div class="form no-margin no-padding no-background">
						<div class="form-item">
							<input class="option-item bg-grey-input " type="text" value="<?php echo get_theme_mod( 'ad_archive', 'ads' ); ?>" id="archive_slug" name="ad_archive" />
						</div>
					</div>
				</div>

				<div class="title font-quicksand"><?php _e("Ad Category Slug", ET_DOMAIN); ?></div>
				<div class="desc">
					<?php _e("Enter taxonomy ad category slug", ET_DOMAIN); ?>
					<div class="form no-margin no-padding no-background">
						<div class="form-item">
							<input class="option-item bg-grey-input " type="text" value="<?php echo get_theme_mod( 'ad_category' , 'ad_cat' ); ?>" id="category_slug" name="ad_category" />
						</div>
					</div>
				</div>

				<div class="title font-quicksand"><?php _e("Ad Location Slug", ET_DOMAIN); ?></div>
				<div class="desc">
					<?php _e("Enter taxonomy ad location slug", ET_DOMAIN); ?>
					<div class="form no-margin no-padding no-background">
						<div class="form-item">
							<input class="option-item bg-grey-input " type="text" value="<?php echo get_theme_mod( 'ad_location' , 'location' ); ?>" id="location_slug" name="ad_location" />
						</div>
					</div>
				</div>

				<div class="title font-quicksand"><?php _e("Infinite Scroll", ET_DOMAIN); ?></div>
				<div class="desc">
					<?php $use_infinite = get_theme_mod( 'ce_use_infinite_scroll', 0 ); ?>
					<?php _e("Enabling this will load ad listings infinitely when users scroll the page", ET_DOMAIN); ?>
					<div class="inner no-border btn-left">
						<div class="payment">
							<div id="infinite_scroll_point" class="button-enable font-quicksand">
								<a href="#" rel="ce_use_infinite_scroll" title="<?php _e("Disable Infinite Scroll", ET_DOMAIN); ?>" class="toggle-button deactive <?php if(!$use_infinite) echo 'selected'; ?> ">
									<span><?php _e("Disable", ET_DOMAIN); ?></span>
								</a>
								<a href="#" rel="ce_use_infinite_scroll" title="<?php _e("Enable Infinite Scroll", ET_DOMAIN); ?>" class="toggle-button active <?php if($use_infinite) echo 'selected'; ?>">
									<span><?php _e("Enable", ET_DOMAIN); ?></span>
								</a>
							</div>
						</div>
					</div>
				</div>

				<div class="title font-quicksand"><?php _e("Minify Script and CSS", ET_DOMAIN); ?></div>
				<div class="desc">
					<?php $use_infinite = get_theme_mod( 'ce_minify', 0 ); ?>
					<?php _e("Enabling this will minify script and CSS to reduce loading time", ET_DOMAIN); ?>
					<div class="inner no-border btn-left">
						<div class="payment">
							<div id="minify_point" class="button-enable font-quicksand">
								<a href="#" rel="ce_minify" title="<?php _e("Disable Infinite Scroll", ET_DOMAIN); ?>" class="toggle-button deactive <?php if(!$use_infinite) echo 'selected'; ?> ">
									<span><?php _e("Disable", ET_DOMAIN); ?></span>
								</a>
								<a href="#" rel="ce_minify" title="<?php _e("Enable Infinite Scroll", ET_DOMAIN); ?>" class="toggle-button active <?php if($use_infinite) echo 'selected'; ?>">
									<span><?php _e("Enable", ET_DOMAIN); ?></span>
								</a>
							</div>
						</div>
					</div>
				</div>
				<!--
				* set categories limit when post an Ad.
				*  @since 1.8.2
				*
				*/
				!-->
				<div class="title font-quicksand"><?php _e("Max number of ad categories", ET_DOMAIN); ?></div>
				<div class="desc">
					<?php _e("Set a maximum number of categories a seller can choose for an Ad", ET_DOMAIN); ?>
					<div class="form no-margin no-padding no-background">
						<div class="form-item">
							<input class="option-item bg-grey-input " type="text" value="<?php echo get_theme_mod( 'ce_number_of_category' , '' ); ?>" id="ce_number_of_category" name="ce_number_of_category" />
						</div>
					</div>
				</div>

				<div class="title font-quicksand"><?php _e("Max number of ad images", ET_DOMAIN); ?></div>
				<div class="desc">
					<?php _e("Set a maximum number of images a seller can upload for an Ad", ET_DOMAIN); ?>
					<div class="form no-margin no-padding no-background">
						<div class="form-item">
							<input class="option-item bg-grey-input " type="text" value="<?php echo get_theme_mod( 'ce_number_of_carousel' ,30 ); ?>" id="ce_number_of_carousel" name="ce_number_of_carousel" />
						</div>
					</div>
				</div>

				<div class="title font-quicksand"><?php _e("Expiration date for free post ads", ET_DOMAIN); ?></div>
				<div class="desc">
					<?php _e("Set expiration period for free post ads", ET_DOMAIN); ?>
					<div class="form no-margin no-padding no-background">
						<div class="form-item">
							<input class="option-item bg-grey-input " type="text" value="<?php echo get_theme_mod( 'ce_number_days_expiry' , '' ); ?>" id="ce_number_days_expiry" name="ce_number_days_expiry" />
						</div>
					</div>
				</div>

			</div>
		</div>

	<?php
	}

	public function on_add_scripts(){
		$this->add_existed_script( 'jquery' );
		$this->add_existed_script( 'underscore' );
		$this->add_existed_script( 'backbone' );
		$this->add_existed_script( 'ce' );
		$this->add_existed_script( 'jquery.validator' );
		$this->add_script('advanced' , TEMPLATEURL.'/js/admin/advance.js');
	}

	public function on_add_styles(){
		$this->add_existed_style('admin.css');
	}

}