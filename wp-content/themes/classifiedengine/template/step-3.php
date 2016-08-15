<?php
global $disable_payment,$ad_currency, $ad, $ce_config, $useCaptcha, $step;

?>
<div id="step-ad" class="post-ad-step step"  >
    <div class="head-step clearfix border-top">
    	<?php if(!$disable_payment){ ?>
      		<div class="number-step">
        	<?php echo array_shift($step); ?>
      		</div>
   	 	<?php }?>
      <div class="name-step">
        <?php _e("Enter your listing details", ET_DOMAIN); ?>
      </div>
      <span class="status-step"><i class="fa fa-arrow-right"></i><i class="fa fa-arrow-down"></i></span>
    </div>

    <div class="content-step" style="<?php if(!$disable_payment || $disable_payment && !is_user_logged_in() ) echo 'display:none'; ?> ">
      	<div class="post-step3 form-post" >
            <form class="form" id="ad_form">
              	<div class="form-group clearfix">
                    <label class="control-label customize_text" for="title"><?php _e("Title", ET_DOMAIN); ?><br><span class="sub-title customize_text"><?php _e("Keep it short &amp; clear", ET_DOMAIN); ?></span></label>
                    <div class="controls">
                      	<input type="text" id="post_title" name="post_title" placeholder="" class="" >
                      	<!-- <span class="icon icon-error" data-icon="!"></span> -->
                    </div>
              	</div>
              	<?php do_action( 'ce_ad_post_form_after_title' , $ad );	?>
              	<div class="form-group clearfix">
                    <label class="control-label customize_text" for="title"><?php printf(__("Price (%s) ", ET_DOMAIN) , $ad_currency ); ?><br>
                    	<span class="sub-title customize_text"><?php _e("Your product's price", ET_DOMAIN); ?></span></label>
                    <div class="controls">
                      	<input type="text" id="<?php echo CE_ET_PRICE; ?>" name="<?php echo CE_ET_PRICE; ?>" placeholder="" class="" >
                      	<!-- <span class="icon icon-error" data-icon="!"></span> -->
                    </div>
              	</div>
              	<?php do_action( 'ce_ad_post_form_after_price' , $ad ); ?>

              	<div class="form-group clearfix">
                    <label class="control-label customize_text" for="ad_location"><?php _e("Location", ET_DOMAIN); ?>
                    	<br>
                    	<span class="sub-title customize_text"><?php _e("Select your area", ET_DOMAIN); ?></span>
                    </label>
                    <div class="controls select-style">
                    <?php
                        $ad_location_id = '';
                        $et_full_location = '';

                        if($ad){
                           if(isset($ad->location) )
                              $ad_location_id   = $ad->location[0]->term_id;
                              $et_full_location  = $ad->et_full_location;
                        }

                        if(is_user_logged_in()){
                            global $current_user;
                            $seller             = ET_Seller::convert($current_user);

                            if (empty($ad_location_id))
                              $ad_location_id   = $seller->user_location_id;

                            if (empty($et_full_location))
                              $et_full_location   = $seller->et_address;
                        }
                        $locations     = ce_get_locations();
                        $space         = '';
                        $parent       = 0;

                        ?>
                      	<select name="ad_location" id="ad_location">
                          <option value=""><?php _e("Select your location", ET_DOMAIN); ?></option>
                      		<?php
                          echo ce_walk_tax_dropdown_tree($locations, 0, array(
                                'show_option_all' => '', 'show_option_none' => '',
                                'orderby' => 'id', 'order' => 'ASC',
                                'show_count' => 0,
                                'hide_empty' => 1, 'child_of' => 0,
                                'exclude' => '', 'echo' => 1,
                                'selected' => $ad_location_id, 'hierarchical' => 0,
                                'name' => 'ad_location', 'id' => '',
                                'class' => 'postform', 'depth' => 0,
                                'tab_index' => 0, 'taxonomy' => 'ad_location',
                                'hide_if_empty' => false, 'option_none_value' => -1
                              ));
                          ?>
                      	</select>
                    </div>
              	</div>
              	<?php do_action( 'ce_ad_post_form_after_location' , $ad ); ?>

              	<div class="form-group clearfix">
                    <label class="control-label customize_text" for="et_full_location"><?php _e("Address", ET_DOMAIN); ?><br>
                    	<span class="sub-title"><?php _e("Enter street or city address", ET_DOMAIN); ?></span></label>
                    <div class="controls">
                      	<input type="text" id="et_full_location" placeholder="" <?php if(!isset($_GET['id'])){ echo 'value ="'.$et_full_location.'"'; }?> name="et_full_location" class="">
                      	<input type="hidden" id="et_location_lat" placeholder="" name="et_location_lat" class="">
                      	<input type="hidden" id="et_location_lng" placeholder="" name="et_location_lng" class="">
                    </div>
              	</div>

              	 <?php do_action('ce_ad_post_form_after_address', $ad); ?>

                <div class="form-group clearfix">
                    <label class="control-label customize_text" for="category"><?php _e("Category", ET_DOMAIN); ?><br><span class="sub-title customize_text">
                    	<?php _e("Select the best one(s)", ET_DOMAIN); ?> <?php if($ce_config['max_cat']) printf(__("Max is %d", ET_DOMAIN), $ce_config['max_cat']); ?></span></label>
                    <div class="controls search-category">
                      	<p class="icon" data-icon="s"></p>
                      	<input type="text" id="category" placeholder="<?php _e("Search for a category...", ET_DOMAIN); ?>" name="category">
                      	<div class="category-all">

                           	<div class="overview" id="auto-complete-list">
                        	<!-- auto complete categories here -->
                            </div>

                      	</div>
                    </div>
                </div>
				<!-- add hook for add custom fields -->
                <?php do_action( 'ce_ad_post_form_fields' , $ad ); ?>

              	<div class="form-group clearfix" id="gallery_container">
                	<label class="control-label label-uploadfile customize_text" for="photos"><?php _e("Photos", ET_DOMAIN); ?><br>
                		<span class="sub-title customize_text"><?php printf(__(" Upload up to %d images", ET_DOMAIN), $ce_config['number_of_carousel'] ); ?></span>
                	</label>
               		<div class="controls carousel-list">
                      	<ul class="input-file clearfix" id="image-list">
                        	<!-- upload image list here -->
                        	<li id="carousel_container">
                            	<span class="filename" id="carousel_browse_button" style="position:absolute;z-index:1" >+</span>
                            </li>
                      		<span class="et_ajaxnonce" id="<?php echo wp_create_nonce( 'ad_carousels_et_uploader' ); ?>"></span>
                      	</ul>
                    </div>
              	</div>
              	<div class="form-group clearfix textarea-description">
                    <label class="control-label customize_text" for="post_content"><?php _e("Description", ET_DOMAIN); ?><br>
                    	<span class="sub-title customize_text"><?php _e("Ideally 3 short paragraphs", ET_DOMAIN); ?></span>
                    </label>
                    <div class="controls mce-tinymce-controls">
                    	<?php wp_editor( '', 'post_content', ce_ad_editor_settings()); ?>

                    </div>
              	</div>
              	<?php
              	if($useCaptcha && !current_user_can( 'manage_options' )) { ?>
				<div class="form-group clearfix">
					 <div class="controls">
                        <?php do_action("et_insert_captcha_post_ad_loged") ;?>
	   				</div>
	   			</div>
                <?php
            	}
				?>
              	<div class="form-group clearfix continue">
                	<label class="control-label customize_text"></label>
                    <div class="controls">
                      	<button type="submit" class="btn  btn-primary customize_text" data-submit="<?php _e("Submit", ET_DOMAIN); ?>" data-continue="<?php _e("Continue", ET_DOMAIN); ?>">

                      		<?php
                      		if(!$disable_payment)
                      		 	_e("Continue", ET_DOMAIN);
                      		 else
                      		 	_e("Submit", ET_DOMAIN);
                      		 ?>
                      	</button>
                    </div>
              	</div>
            </form>
      	</div>
    </div>
</div><!--/.step3 -->