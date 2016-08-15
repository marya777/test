<div class="et-main-main" id="setting-ad" style="display: none">
<?php 
	$option 		= new CE_Options;	
	$currency_sign 	= $option->get_option('et_currency_sign');
	$currency_align = $option->get_option('et_currency_align');
	$currency_format= $option->get_option('et_currency_format');

	$location		=	new ET_AdLocation();
	$category		=	new ET_AdCatergory();	
		
?>
<div id="classified_content" >
	<?php if (!class_exists("WooCommerce")): ?>
	<div class="title font-quicksand"><?php _e("Currency Sign",ET_DOMAIN);?></div>
	<div class="desc">
		<?php _e("Enter currency sign for ads on your site",ET_DOMAIN);?>
		<div class="form no-margin no-padding no-background">
			<div class="form-item">
				<input class="option-item bg-grey-input <?php if($currency_sign == '') echo 'color-error' ?>" type="text" value="<?php echo $currency_sign ?>" id="currency_sign" name="currency_sign" />
				<span class="icon  <?php if($currency_sign == '') echo 'color-error' ?>" data-icon="<?php data_icon($currency_sign) ?>"></span>
			</div>
		</div>
		<div class="inner no-border btn-left currency-align">
			<div class="payment">	
				<div class="button-enable font-quicksand">
					<a href="#" rel="currency_align" title="Currency Align" class="toggle-button deactive <?php if ($currency_align == 'right') echo 'selected' ?>">
						<span><?php _e('Right', ET_DOMAIN) ?></span>
					</a>
					<a href="#" rel="currency_align" title="Currency Align" class="toggle-button active <?php if ($currency_align != 'right') echo 'selected' ?>">
						<span><?php _e('Left', ET_DOMAIN) ?></span>
					</a>
				</div>
			</div>
		</div>
	</div>

	<div class="title font-quicksand" style="padding-bottom:10px;"><?php _e("Currency Format",ET_DOMAIN);?></div>
	<div class="desc" style="padding-top:0; margin-top:0;">
		<div class="inner no-border btn-left currency-format">
			<div class="payment">	
				<div class="button-enable font-quicksand">
					<a href="#" rel="currency_format" title="<?php _e('Format money',ET_DOMAIN);?> 1.234,5" class="toggle-button deactive  <?php if ($currency_format  == 2) echo 'selected' ?>">
						<span><?php _e('Custom', ET_DOMAIN) ?></span>
					</a>
					<a href="#" rel="currency_format" title="<?php _e('Format money',ET_DOMAIN);?> 1,234.5" class="toggle-button  active <?php if ($currency_format != 2) echo 'selected' ?>">
						<span><?php _e('Default', ET_DOMAIN) ?></span>
					</a>
					
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>
	<div id="ads_categoy_point" class="title font-quicksand">
	<?php 
		
		//$title_position  = $category->get_title();		
		$title_position	=	 __("CATEGORY", ET_DOMAIN);
	?>
		<div class="title-main" title="<?php //_e("Double click to edit", ET_DOMAIN); ?>">
	 		<?php echo ($title_position) ? $title_position : __("Oops, empty title! Double click to change", ET_DOMAIN);?>
	 	</div>

	</div>
	<div class="desc">
		<?php _e("Create a list to categorize ads the way you want.",ET_DOMAIN);?> 
		<div class="cat-list-container" id="ad-category" >
			
			<?php $category->print_backend_terms(); ?>
			
		</div>
		<?php $category->print_confirm_list(); ?>
	</div>

	<div id="ads_location_point" class="title font-quicksand">
	<?php
		//$title_available  = $location->get_title();		
		$title_available	=	 __("LOCATION", ET_DOMAIN);
	?>	<div class="title-main" title="">
		<?php echo ($title_available) ? $title_available : __("Oops, empty title! Double click to change", ET_DOMAIN); ?>
		</div>
		
	</div>

		

	<div class="desc">
		<?php _e("Create a list of Locations (e.g., Vietnam, USA, Australia) that buyers can use to filter ad posts.",ET_DOMAIN);?> 
		<div class="types-list-container" id="ad-location">
			<!-- <ul class="list-job-input list-tax jobtype-sortable tax-sortable"> -->
			<?php 
				$location->print_backend_terms();
				$location->print_confirm_list();
				$option 		= new CE_Options;
				$ad 			= $option->get_option('et_pending_ad');
				$cm_ad  		= $option->get_option('et_comment_ad');
				$cm_ad_captcha  = $option->get_option('et_comment_ad_captcha');
			?>
		</div>
	</div>
	<?php do_action('ce_settings_show_more_taxs');?>

	<div class="title font-quicksand"><?php _e('Pending ads', ET_DOMAIN) ?></div>
	<div class="desc">
		<?php _e('Enabling this will make every new ad post pending until you review and approve it manually.',ET_DOMAIN);?>
		<div class="inner no-border btn-left">
			<div class="payment">	
				<div id="pending_ads_point" class="button-enable font-quicksand">
					<a href="#" rel="pending_ad" title="Pending Ad Status" class="toggle-button deactive <?php if ($ad == 0) echo 'selected' ?>">
						<span><?php _e('Disable', ET_DOMAIN) ?></span>
					</a>
					<a href="#" rel="pending_ad" title="Pending Ad Status" class="toggle-button active <?php if ($ad == 1) echo 'selected' ?>">
						<span><?php _e('Enable', ET_DOMAIN) ?></span>
					</a>
				</div>
			</div>
		</div>
	</div>	

</div>
	<div class="title font-quicksand"><?php _e('Comment ads', ET_DOMAIN) ?></div>
	<div id="comment_ads_point" class="desc">
		<?php _e('Enabling this will show comment in ad detail .',ET_DOMAIN);?>
		<div class="inner no-border btn-left">
			<div class="payment">	
				<div class="button-enable font-quicksand">
					<a href="#" rel="comment_ad" title="Comment Ad Status" class="toggle-button deactive <?php if ($cm_ad == 0) echo 'selected' ?>">
						<span><?php _e('Disable', ET_DOMAIN) ?></span>
					</a>
					<a href="#" rel="comment_ad" title="Comment Ad Status" class="toggle-button active <?php if ($cm_ad == 1) echo 'selected' ?>">
						<span><?php _e('Enable', ET_DOMAIN) ?></span>
					</a>
				</div>
			</div>
		</div>
	</div>

	<div class="title font-quicksand"><?php _e('Use google captcha for comment form', ET_DOMAIN) ?></div>
	<div class="desc">
		<?php _e('Enabling captcha google in comment form .',ET_DOMAIN);?>
		<div class="inner no-border btn-left">
			<div class="payment">	
				<div class="button-enable font-quicksand">
					<a href="#" rel="comment_ad_captcha" title="Comment Ad Status" class="toggle-button deactive <?php if ($cm_ad_captcha == 0) echo 'selected' ?>">
						<span><?php _e('Disable', ET_DOMAIN) ?></span>
					</a>
					<a href="#" rel="comment_ad_captcha" title="Comment Ad Status" class="toggle-button active <?php if ($cm_ad_captcha == 1) echo 'selected' ?>">
						<span><?php _e('Enable', ET_DOMAIN) ?></span>
					</a>
				</div>
			</div>
		</div>
	</div>	

</div>

