<div class="et-main-main clearfix inner-content" id="setting-general" <?php if ($sub_section != '' && $sub_section != 'general') echo 'style="display:none"' ?>>
<?php 
	$option = new CE_Options;	
	$site_demon = $option->get_option('et_site_demon');
	$site_title = $option->get_site_title();//('et_site_title');
	$copyright 	= $option->get_option('et_copyright');
	$twitter 	= $option->get_option('et_twitter_account');
	$facebook 	= $option->get_option('et_facebook_link');
	$site_desc 	= $option->get_site_desc();//('et_site_desc');
	$google_analytics = $option->get_option('et_google_analytics');
	$google 	= $option->get_option('et_google_plus');

	$validator	=	new ET_Validator();
?>
<style type="text/css">
	body.mceContentBody { 
   background: #fff; 
   color:#000;
}
	
</style>
	<div class="title font-quicksand"><?php _e("Website Title",ET_DOMAIN);?></div>
	<div class="desc">
		<?php _e("Enter your website title ",ET_DOMAIN);?>
		<div class="form no-margin no-padding no-background">
			<div class="form-item">
				<input class="option-item bg-grey-input <?php if($site_title == '') echo 'color-error' ?>" type="text" value="<?php echo $site_title?>" id="site_title" name="site_title" />
				<span class="icon  <?php if($site_title == '') echo 'color-error' ?>" data-icon="<?php data_icon($site_title) ?>"></span>
			</div>
		</div>
	</div>
	<div class="title font-quicksand"><?php _e("Website Description",ET_DOMAIN);?></div>
	<div class="desc">
		<?php _e("This description will appear next to your website logo in the header.",ET_DOMAIN);?>
		<div class="form no-margin no-padding no-background">
			<div class="form-item">
				<input class="option-item bg-grey-input <?php if($site_desc == '') echo 'color-error' ?>" type="text" value="<?php echo $site_desc?>" id="site_desc" name="site_desc" />
				<span class="icon  <?php if($site_desc == '') echo 'color-error' ?>" data-icon="<?php  data_icon($site_desc) ?>"></span>
			</div>
		</div>
	</div>
	
    <div class="title font-quicksand"><?php _e("Copyright Information",ET_DOMAIN);?></div>
	<div class="desc">
		<?php _e("This copyright information will appear in the footer.",ET_DOMAIN);?>
		<div class="form no-margin no-padding no-background">
			<div class="form-item">
				<input class="option-item bg-grey-input <?php if($copyright == '') echo 'color-error' ?>" type="text" value="<?php  echo htmlentities($copyright) ?>" id="copyright" name="copyright" />
				<span class="icon  <?php if($copyright == '') echo 'color-error' ?>" data-icon="<?php data_icon($copyright) ?>"></span>
			</div>
		</div>
	</div>
	

	<div class="title font-quicksand"><?php _e("Google Analytics Script",ET_DOMAIN);?></div>
	<div class="desc">
		<?php _e("Google analytics is a service offered by Google that generates detailed statistics about the visits to a website.",ET_DOMAIN);?>
		<div class="form no-margin no-padding no-background">
    		<div class="form-item">
        		<textarea class="autosize option-item" row="4" style="height: auto;overflow: visible;" id="google_analytics" name="google_analytics" ><?php echo stripslashes($google_analytics); ?></textarea>
        		<span class="icon <?php if ($google_analytics == '') echo 'color-error' ?>" data-icon="<?php data_icon($google_analytics, 'text') ?>"></span>
        	</div>
        </div>
	</div>
	<?php 
		$google_captcha	=	ET_GoogleCaptcha::get_api();
	?>
	<div class="title font-quicksand"><?php _e("Use google captcha",ET_DOMAIN);?></div>
	<div class="desc">
	 	<?php _e("Enabling this will make every one submit form have to fill in google captcha.",ET_DOMAIN);?>			
		<?php /*<!-- <a class="find-out font-quicksand" href="#">
	 		<?php _e("Find out more",ET_DOMAIN);?> <span class="icon" data-icon="i"></span>
	 	</a> --> */ ?>	 
		<div class="inner no-border btn-left">
			<div class="payment">
				<div class="button-enable font-quicksand">
					<?php et_display_enable_disable_button('use_captcha', __("Use captcha",ET_DOMAIN), 'use_captcha'); ?>
				</div>
			</div>
		</div>
			<div class="form no-margin no-padding no-background">
				<div class="form-item">
					<input class="option-item bg-grey-input google-captcha" type="text" value="<?php echo $google_captcha['private_key'] ?>" id="private_key" name="private_key" placeholder="<?php _e("Private key", ET_DOMAIN) ?>" />
				</div>
			</div>
			<div class="form no-margin no-padding no-background">
				<div class="form-item">
					<input class="option-item bg-grey-input google-captcha " type="text" value="<?php echo $google_captcha['public_key'] ?>" id="public_key" name="public_key" placeholder="<?php _e("Public key", ET_DOMAIN) ?>" />
				</div>
			</div>	        				
	</div>
</div>

