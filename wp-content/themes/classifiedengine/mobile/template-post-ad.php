<?php
global $user_ID, $current_user;
$currency		=	ET_Payment::get_currency();

$request		=	wp_parse_args( $_REQUEST,
									array(
										'post_title' 			=> '' ,
										'post_content'			=> '',
										CE_ET_PRICE				=> '',
										'et_full_location'		=> '',
										'ad_location'				=> '',
										CE_AD_CAT				=> ''
									)

								);
extract($request);
$ad_category = $request[CE_AD_CAT];

if( is_user_logged_in() ){

	$seller        = ET_Seller::convert($current_user);
	if (empty($ad_location))
		$ad_location = $seller->user_location_id;
	if(empty($et_full_location))
		$et_full_location = $seller->et_address;

}
$ad = array();
if(isset($_REQUEST['id'])) {
	$ad	=	CE_Ads::convert (get_post($_REQUEST['id']));

	if (!empty($ad->location))
		$ad_location = $ad->location[0]->term_id;

	$et_full_location = $ad->et_full_location;

	if( $user_ID != $ad->post_author && current_user_can('manage_options') )
		wp_redirect( et_get_page_link('post-ad') );
}



$term_of_use	=	et_get_page_link('terms-of-use' , array () , false);
?>
<div class="classified-form" <?php if( !is_user_logged_in() ) echo "style='display: none'"; ?> >
	<form class="post-classifed-form" data-ajax="false" method="post" enctype="multipart/form-data" novalidate >
		<input type="hidden" value="<?php echo $user_ID; ?>" class="post_author" />

		<!-- tittle -->
		<div data-role="fieldcontain" class="post-new-classified">
			<label for="post_title"><?php _e("Title", ET_DOMAIN); ?><span class="subtitle"><?php _e("Keep it short & clear", ET_DOMAIN); ?></span></label>
			<input type="text" class="ad_field" name="post_title" id="post_title" value="<?php echo $post_title;  ?>" placeholder="<?php _e("Title", ET_DOMAIN); ?>" required />
		</div>

		<div data-role="fieldcontain" class="post-new-classified">
			<label for="<?php echo CE_ET_PRICE; ?>">
				<?php printf(__("Price (%s) ", ET_DOMAIN) , $currency['icon'] ); ?>
				<span class="subtitle"><?php _e("Your product's price", ET_DOMAIN); ?></span>
			</label>
			<?php
			$require_fields	=	get_theme_mod( 'ad_require_fields' , array ( CE_ET_PRICE , 'ad_location' , CE_AD_CAT  ) );

			?>
			<input type="number" class="ad_field" name="<?php echo CE_ET_PRICE; ?>" id="<?php echo CE_ET_PRICE; ?>" value="<?php echo $_regular_price;  ?>" placeholder="<?php _e("Price", ET_DOMAIN); ?>" <?php if(in_array(CE_ET_PRICE, $require_fields)) {?>  required <?php } ?> />
		</div>


		<div data-role="fieldcontain" class="post-new-classified category" data-ad="ad_location">
	        <label for="day">
	        	<?php _e("Location", ET_DOMAIN); ?>
	        	<span class="subtitle"><?php _e("Select yout area", ET_DOMAIN); ?></span>
	        </label>
	        <?php
	        	ce_dropdown_tax (	'ad_location' ,
        							array( 'show_option_all' => __("Select your", ET_DOMAIN),
		        							'name' 		=> 'ad_location',
		        							'id' 		=> 'ad_location',
		        							'taxonomy' 	=> 'ad_location' ,
		        							'hierarchical' => true,
		        							'selected'	=> $ad_location,
		        							'attr' => array( 'data-native-menu' => 0 )
		        						)
        							);
			        		?>
	    </div>

	    <div data-role="fieldcontain" class="post-new-classified">
			<label for="et_full_location">
				<?php _e("Address", ET_DOMAIN); ?>
				<span class="subtitle"><?php _e("Enter street or city adress", ET_DOMAIN); ?></span>
			</label>
			<input type="text" class="ad_field" name="et_full_location" id="et_full_location" value="<?php echo $et_full_location;  ?>" placeholder="<?php _e("Street or city adress", ET_DOMAIN); ?>" required />
			<input type="hidden" name="et_location_lat" id="et_location_lat" />
			<input type="hidden" name="et_location_lng" id="et_location_lng" />
		</div>
		<?php do_action('ce_ad_post_form_after_address', $ad); ?>


	    <div data-role="fieldcontain" class="post-new-classified category" data-ad="<?php echo CE_AD_CAT; ?>">
	      	<label for="day"><?php _e("Category", ET_DOMAIN); ?><span class="subtitle"><?php _e("Select the best one(s)", ET_DOMAIN); ?></span></label>
	      	<?php 
	      		ce_dropdown_tax (	CE_AD_CAT ,
	      							array( 	'show_option_all' => __("Select your", ET_DOMAIN),
			      							'attr' => array( 'data-native-menu' => 0 ,
			      							'multiple' => 'multiple' ) ,
			      							'name' => 'ad_categories',
			      							'id' => 'ad_categories',
			      							'selected'	=> $ad_category,
			      							'taxonomy' => CE_AD_CAT ,
			      							'hierarchical' => true
			      						)
			      					);
			    ?>
			<input type="hidden" id="<?php echo CE_AD_CAT; ?>" name="<?php echo CE_AD_CAT; ?>" value="<?php echo $ad_category; ?>" />
	    </div>

	    <div data-role="fieldcontain" class="post-new-classified">
			<label for="et_full_location">
				<?php _e("Photos", ET_DOMAIN); ?>
				<span class="subtitle"><?php printf ( __("Upload up to %s images", ET_DOMAIN) ,get_theme_mod( 'ce_number_of_carousel', 15 ) ); ?></span>
			</label>
			 <input type="file" name="et_carousel[]" data-clear-btn="true" id="et_carousel" accept="image/*" multiple="multiple">
		</div>

		<div data-role="fieldcontain" class="post-new-classified">
			<label for="info">
				<?php _e("Description", ET_DOMAIN); ?>
				<span class="subtitle"><?php _e("Ideally 3 short paragraphs", ET_DOMAIN); ?></span>
			</label>
	        <textarea name="post_content" class="ad_field" id="post_content"><?php echo $post_content;  ?></textarea>
		</div>

		<?php
			do_action( 'ce_mobile_ad_post_form_fields' );
		?>

		<div class="ui-content plan-ad">
			<?php
			if(!et_get_payment_disable())
				get_template_part( 'mobile/template' , 'list-plans' );
			?>
		<div data-role="fieldcontain" class="post-new-classified">
			<input type="submit" value="<?php  _e("Submit", ET_DOMAIN); ?>" data-icon="check" data-iconpos="right" data-inline="true">
		</div>

		</div>
	</form>
</div>