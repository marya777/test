<!-- step 2 -->
<?php
global $step,$useCaptcha, $term_of_use,$disable_payment;

?>
<div id="step-auth" class="post-ad-step step"  >
    <div class="head-step clearfix border-top">
      	<div class="number-step">
        	<?php echo array_shift($step); ?>
      	</div>
      	<div class="name-step">
        	<?php _e("Login or register", ET_DOMAIN); ?>
      	</div>
      	<span class="status-step"><i class="fa fa-arrow-right"></i><i class="fa fa-arrow-down"></i></span>
    </div>
    <div class="content-step" style="<?php if(!$disable_payment) echo 'display:none';?>">
      	<div class="post-step2 form-post" >
            <div class="login">
              	<?php _e("Already have an account?", ET_DOMAIN); ?> <a href=""><?php _e("Log in", ET_DOMAIN) ?> <i class="fa fa-arrow-right"></i></a> 
            </div>
            <form class="form">
              	<div class="form-group clearfix">
                	<label class="control-label customize_text" for="full_name"><?php _e("Full Name", ET_DOMAIN); ?><br /><span class="sub-title customize_text"><?php _e("First and last name", ET_DOMAIN); ?></span></label>
                    <div class="controls">
                      	<input type="text" id="full_name" name="full_name" placeholder=""  class="">
                    </div>
              	</div>
              	<div class="form-group clearfix">
                	<label class="control-label customize_text" for="user_login"><?php _e("Username", ET_DOMAIN); ?> (*)<br /><span class="sub-title customize_text"><?php _e("Enter a username", ET_DOMAIN); ?></span></label>
                    <div class="controls">
                      	<input type="text" id="user_login" name="user_login" placeholder=""  class=" user_name">
                    </div>
              	</div>
              	<div class="form-group clearfix">
                	<label class="control-label customize_text" for="email"><?php _e("Email Address", ET_DOMAIN); ?> (*)<br /><span class="sub-title customize_text"><?php _e("Enter a valid email", ET_DOMAIN); ?></span></label>
                    <div class="controls">
                      	<input type="text" id="user_email" name="user_email" placeholder=""  class=" email">
                    </div>
              	</div>
              	<div class="form-group clearfix">
                	<label class="control-label customize_text" for="phone_number"><?php _e("Phone Number", ET_DOMAIN); ?><br /><span class="sub-title customize_text"><?php _e(" Your phone number with area code", ET_DOMAIN); ?></span></label>
                	<div class="controls">
                  		<input type="text" id="phone_number" placeholder="" >
                	</div>
              	</div>

                <?php do_action('ce_after_register_form'); ?>

              	<div class="form-group clearfix" id="user_avatar_container">
                	<label class="control-label customize_text" for="profile_picture"><?php _e('Profile Picture', ET_DOMAIN) ?><br /><span class="sub-title customize_text"><?php printf(__("Image size must not be more than %s", ET_DOMAIN),apply_filters('ce_max_file_size_upload', '3mb')); ?></span></label>
                	<div class="controls">
                      	<div class="input-file">
                      		<div id="user_avatar_thumbnail">
                    			<?php echo get_avatar('demo@enginethemes.com', 150); ?>
                    		</div>
                        	<span class="bg-button-file button" id="user_avatar_browse_button">
                          		<?php _e("Upload...", ET_DOMAIN); ?>
                          		<span class="icon" data-icon="o"></span>
                        	</span>

                      	</div>
                      	<span class="et_ajaxnonce" id="<?php echo wp_create_nonce( 'user_avatar_et_uploader' ); ?>"></span>
                      	<input type="hidden" name="et_avatar" id="et_avatar" />
                	</div>
              	</div>

              	<div class="form-group clearfix">
                	<label class="control-label customize_text" for="password"><?php _e("Password", ET_DOMAIN); ?> (*)<br /><span class="sub-title customize_text"><?php _e("Enter a password", ET_DOMAIN); ?></span></label>
                    <div class="controls">
                      	<input type="password" id="password" name="password" placeholder=""  class="" >
                    </div>
              	</div>
              	<div class="form-group clearfix">
                	<label class="control-label customize_text" for="repeat_password"><?php _e("Repeat Password", ET_DOMAIN); ?> (*)<br /><span class="sub-title customize_text"><?php _e("Repeat your password", ET_DOMAIN); ?></span></label>
                    <div class="controls">
                      	<input type="password" id="repeat_password" name="repeat_password" placeholder=""  class="" >
                    </div>
              	</div>

				  <?php if($term_of_use) { ?>
                  	<div class="form-group clearfix">

                  		<label class="control-label customize_text" for="">
                  		</label>
                  		<div class="controls">
                        	<input type="checkbox" name="loginkeeping" id="loginkeeping" value="loginkeeping" required />

      						<label for="loginkeeping" style="width : 200px;">
      							<?php printf( __("I agree with <a target='_blank' href='%s' > Terms of use </a> ", ET_DOMAIN) , $term_of_use ) ; ?>
      							(*)
      						</label>
      					</div>

                  	</div>
				<?php } ?>
				<?php if($useCaptcha) { ?>
    				<div class="form-group clearfix">
    					 <div class="controls">
                            <?php do_action("et_insert_captcha_post_ad") ;?>
    	   				</div>
    	   			</div>
                <?php 	} ?>

              	<div class="form-group continue clearfix">
                    <label class="control-label customize_text"></label>
                    <div class="controls">
                      	<button type="submit" class="btn  btn-primary submit-auth customize_text"><?php _e("Continue", ET_DOMAIN); ?></button>
                  	</div>
              	</div>
            </form>
      	</div>
    </div>
</div><!--/.step2 -->