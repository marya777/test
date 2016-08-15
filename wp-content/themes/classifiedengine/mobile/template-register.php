
<div class="authentication-form">

	<form class="post-classifed-form register-form register" data-ajax="false" method="post" >
		<div data-role="fieldcontain" class="post-new-classified">
			<label for="username"><?php _e("Full name", ET_DOMAIN); ?><span class="subtitle"><?php _e("Enter your display name", ET_DOMAIN); ?></span></label>
			<input type="text" name="display_name"  value="" placeholder="<?php _e("Full name", ET_DOMAIN); ?>" required />
		</div>
		<div data-role="fieldcontain" class="post-new-classified">
			<label for="username"><?php _e("Username", ET_DOMAIN); ?><span class="subtitle"><?php _e("Enter your username", ET_DOMAIN); ?></span></label>
			<input type="text" name="user_login"  value="" placeholder="<?php _e("Username", ET_DOMAIN); ?>" required />
		</div>
		<div data-role="fieldcontain" class="post-new-classified">
			<label for="email"><?php _e("Phone Number", ET_DOMAIN); ?><span class="subtitle"><?php _e("Your phone number with area code", ET_DOMAIN); ?></span></label>
			<input type="text" name="et_phone" value="" placeholder="<?php _e("Phone number", ET_DOMAIN); ?>" required />
		</div>
		<div data-role="fieldcontain" class="post-new-classified">
			<label for="email"><?php _e("Email address", ET_DOMAIN); ?><span class="subtitle"><?php _e("Buyer will contact you via this", ET_DOMAIN); ?></span></label>
			<input type="email" name="user_email" value="" placeholder="<?php _e("Email address", ET_DOMAIN); ?>" required />
		</div>
		 <?php do_action('ce_after_register_form'); ?>
		<div data-role="fieldcontain" class="post-new-classified">
			<label for="username"><?php _e("Password", ET_DOMAIN); ?><span class="subtitle"><?php _e("Enter words keep your account private", ET_DOMAIN) ?></span></label>
			<input type="password" name="user_pass"  value="" placeholder="<?php _e("Password", ET_DOMAIN); ?>" required /> <br/>
			<input type="password" name="password_again" value="" placeholder="<?php _e("Retype Password", ET_DOMAIN); ?>" required />
		</div>

		<?php do_action('ce_mobile_after_reg_form'); ?>

		<?php
		$term_of_use	=	et_get_page_link('terms-of-use' , array () , false);
		if($term_of_use) { ?>
			<div data-role="fieldcontain" class="post-new-classified">
              	<input data-enhance="false" data-role="none" type="checkbox" name="loginkeeping" id="loginkeeping" value="loginkeeping" required />
              	<span><label data-enhance="false" data-role="none" for="loginkeeping" ><?php printf( __("I agree with the <a data-ajax='false' href='%s' > Terms of use </a> ", ET_DOMAIN) , $term_of_use ) ; ?></label> </span>
            </div>
		<?php
		}
		?>

		<div data-role="fieldcontain" class="post-new-classified">
			<input type="submit" value="<?php _e('Submit',ET_DOMAIN);?>" data-icon="check" data-iconpos="right" data-inline="true">
			<span style="margin-top:10px; display:block;">
			<?php _e("Already have an account?", ET_DOMAIN); ?> <a href="#" class="open-login" ><?php _e("Login here", ET_DOMAIN); ?></a>
			</span>
		</div>

	</form>

	<form class="post-classifed-form register-form login" data-ajax="false" method="post" style="display:none;" >
		<div data-role="fieldcontain" class="post-new-classified">
			<label for="username"><?php _e("Username", ET_DOMAIN); ?><span class="subtitle"><?php _e("Enter your username or email", ET_DOMAIN); ?></span></label>
			<input type="text" name="user_login"  value="" placeholder="<?php _e("Username", ET_DOMAIN); ?>" required />
		</div>
		<div data-role="fieldcontain" class="post-new-classified">
			<label for="username"><?php _e("Password", ET_DOMAIN); ?><span class="subtitle"><?php _e("Enter your password", ET_DOMAIN); ?></span></label>
			<input type="password" name="user_pass" value="" placeholder="<?php _e("Password", ET_DOMAIN); ?>" required /> <br/>
		</div>

		<div data-role="fieldcontain" class="post-new-classified">
			<input type="submit" value="<?php _e('Login',ET_DOMAIN);?>" data-icon="check" data-iconpos="right" data-inline="true">
			<span style="margin-top:10px; display:block;">
			<?php _e("Forgot your password?", ET_DOMAIN); ?> <a href="#" class="forgot-password" ><?php _e("Click here", ET_DOMAIN); ?></a>
			</span>
		</div>
		<?php
		$ce_options = new CE_Options();
        if( $ce_options->use_facebook() || $ce_options->use_twitter() ) { ?>

			<div data-role="fieldcontain" class="post-new-classified">
			<?php if($ce_options->use_facebook()){?>
				<a id="facebook_auth_btn" style="background:#49639E;" class="btn-fb-login"><i class="fa fa-facebook"></i><span class="right-btn-fb"><?php _e("Sign in with Facebook",ET_DOMAIN);?> </span> </a>
				<?php }?>
			<?php if($ce_options->use_twitter()){?>
				<a href="<?php echo home_url('?action=twitterauth');?>" id="tw_auth_btn" class=" btn-tw-login" ><i class="fa fa-twitter"></i><span class="right-btn-fb"><?php _e("Sign in with Twitter",ET_DOMAIN);?> </span></a>
			<?php }?>
			</div>
		<?php }?>
	</form>
	<form class="post-classifed-form forgot-password" data-ajax="false" method="post" style="display:none;" >
		<div data-role="fieldcontain" class="post-new-classified">
			<label for="username"><span class="subtitle"><?php _e("Enter your email", ET_DOMAIN); ?></span></label> <br />
			<input type="email" name="user_login"  class="required email" placeholder="<?php _e("Your Email", ET_DOMAIN); ?>" required />
		</div>

		<div data-role="fieldcontain" class="post-new-classified">
			<input type="submit" value="<?php _e('Reset Password',ET_DOMAIN);?>" data-icon="check" data-iconpos="right" data-inline="true">
		</div>
	</form>
</div>