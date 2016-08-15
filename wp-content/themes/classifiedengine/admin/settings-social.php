<div class="et-main-main clearfix inner-content" id="setting-social" <?php if ($sub_section != 'social') echo 'style="display:none"' ?>>

</style>
	
	<div class="title font-quicksand"><?php _e("Accept facebook login",ET_DOMAIN);?></div>
	<div class="desc">
	 		
		<?php /*<!-- <a class="find-out font-quicksand" href="#">
	 		<?php _e("Find out more",ET_DOMAIN);?> <span class="icon" data-icon="i"></span>
	 	</a> --> */ ?>
	 	<?php 
			$app_id 	 	= ET_FaceAuth::get_app_id();

		?>
		<div class="inner no-border btn-left">
			<div class="payment">
				<div id="social_fb_point" class="button-enable font-quicksand">
					<?php et_display_enable_disable_button('facebook_login', __("Facebook login",ET_DOMAIN), 'facebook_login'); ?>
				</div>
			</div>
		</div>
			<div class="form no-margin no-padding no-background">

				<div class="form-item">
					<p class="no-padding-bottom no-margin-bottom"><?php _e('Facebook Applicatin ID',ET_DOMAIN);?></p>
					<input class="option-item bg-grey-input " type="text" value="<?php echo $app_id ?>" id="facebook_id" name="facebook_id" placeholder="<?php _e("Facebook Applicatin ID", ET_DOMAIN) ?>" />
				</div>
			</div>			       				
	</div>

	<div class="title font-quicksand"><?php _e("Accept twitter login",ET_DOMAIN);?></div>
	<div class="desc">	 			
		<?php /*<!-- <a class="find-out font-quicksand" href="#">
	 		<?php _e("Find out more",ET_DOMAIN);?> <span class="icon" data-icon="i"></span>
	 	</a> --> */ ?>
	 	<?php 
			$twitter_key	 	= ET_TwitterAuth::get_twitter_key();
			$twitter_secret	 	= ET_TwitterAuth::get_twitter_secret();
		?>
		<div class="inner no-border btn-left">
			<div class="payment">
				<div id="social_tt_point" class="button-enable font-quicksand">
					<?php et_display_enable_disable_button('twitter_login', __("Twitter login",ET_DOMAIN), 'twitter_login'); ?>
				</div>
			</div>
		</div>
			<div class="form no-margin no-padding no-background">
				<div class="form-item">
					<p class="no-padding-bottom no-margin-bottom"><?php _e('Twitter Consumer Key',ET_DOMAIN);?></p>
					<input class="option-item bg-grey-input " type="text" value="<?php echo $twitter_key ?>" id="private_key" name="twitter_key" placeholder="<?php _e("Twitter Consumer Key", ET_DOMAIN) ?>" />
				</div>
			</div>
			<div class="form no-margin no-padding no-background">
				<div class="form-item">
					<p class="no-padding-bottom no-margin-bottom"><?php _e('Twitter Consumer Secret',ET_DOMAIN);?></p>
					<input class="option-item bg-grey-input google-captcha " type="text" value="<?php echo $twitter_secret ?>" id="public_key" name="twitter_secret" placeholder="<?php _e("Twitter Consumer Secret", ET_DOMAIN) ?>" />
				</div>
			</div>	        				
	</div>
</div>

