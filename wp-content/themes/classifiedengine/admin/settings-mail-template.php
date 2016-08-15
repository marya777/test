<?php 
	$mailt_opt			=	new ET_CEMailTemplate ();	
	
	$register_mail 	 	= 	$mailt_opt->get_register_mail ();
	$forgot_pass_mail   = 	$mailt_opt->get_forgot_pass_mail ();
	$reset_pass_mail	=	$mailt_opt->get_reset_pass_mail ();
	//$apply_mail			=	$mailt_opt->get_apply_mail ();
	//$remind_mail		=	$mailt_opt->get_remind_mail ();
	$approve_mail		=	$mailt_opt->get_approve_mail ();
	$reject_mail		=	$mailt_opt->get_reject_mail ();
	$archive_mail		=	$mailt_opt->get_archive_mail ();
	$cash_mail			=	$mailt_opt->get_cash_notification_mail ();
	$message_to_seller  =	$mailt_opt->get_message_to_seller_mail();
	$receipt_mail  		=	$mailt_opt->get_receipt_mail();

	$cash_notice	    = 	$mailt_opt->get_cash_notification_mail();

	$editor_email 	= array(
		'quicktags'  	=> false,
		'media_buttons' => false,
		'wpautop'		=> true,
		'editor_class'	=> 'ce-mail',
		'tinymce'   	=> array(
			'content_css'	=> get_template_directory_uri() . '/js/lib/tiny_mce/content.css',
			'height'   	=> 200,
			'autoresize_min_height'		=> 200,  
			'autoresize_max_height'		=> 350,
			'theme_advanced_buttons1' 	=> 'bold,italic,|,link,unlink,bullist,numlist',
			'theme_advanced_buttons2' 	=> '',
			'theme_advanced_buttons3' 	=> '',
			'theme_advanced_statusbar_location' => 'none',
			'setup' =>  "function(ed){
				ed.onChange.add(function(ed, l) {
					var content	= ed.getContent();
					if(ed.isDirty() || content === '' ){
						ed.save();
						jQuery(ed.getElement()).blur(); // trigger change event for textarea
					}

				});

				// We set a tabindex value to the iframe instead of the initial textarea
				ed.onInit.add(function() {
					var editorId = ed.editorId,
						textarea = jQuery('#'+editorId);
					jQuery('#'+editorId+'_ifr').attr('tabindex', textarea.attr('tabindex'));
					textarea.attr('tabindex', null);
				});
			}"
		)
	);
?>
<style type="text/css">
	.email-template .form {display: none;}
</style>
<div class="et-main-main clearfix inner-content" id="setting-mail-template"  <?php if ($sub_section != 'mail-template') echo 'style="display:none"' ?> >

	<div id="email_template_point" class="title font-quicksand mail-template-title" id="auth-mail-template-title">
		<?php _e("Authentication Mail Template",ET_DOMAIN);?>
		
	</div>
	<div class="desc " id="authentication-mail-template">
		<div class="item">
			<?php _e("Email templates for authentication process. You can use placeholders to include some specific content.",ET_DOMAIN);?> 
			<a class="icon btn-template-help payment" data-icon="?" href="#" title="<?php  _e("View more details",ET_DOMAIN) ?>"></a>
			
			<!-- <a class="find-out font-quicksand" href="#">
				Find out more <span class="icon" data-icon="i"></span>
			</a> -->
			
			<div class="cont-template-help payment-setting">
				[user_login],[display_name],[user_email] : <?php _e("user's details you want to send mail", ET_DOMAIN) ?><br />
				[dashboard] : <?php _e("seller dashboard url ", ET_DOMAIN) ?><br />
				[title], [link], [excerpt],[desc] : <?php _e("ad infomation and detail", ET_DOMAIN) ?> <br />
				[activate_url] : <?php _e("activate link is require for user to renew their pass", ET_DOMAIN) ?> <br />
				[reason] : <?php _e(" reject ad reason ", ET_DOMAIN) ?> <br />
				[cash_message] :<?php _e(" cash message when user pay success ", ET_DOMAIN) ?> <br />
				[site_url],[blogname],[admin_email] :<?php _e(" site info, admin email", ET_DOMAIN) ?>
			</div>
		</div>
		<div class="inner email-template" >
			<div class="item">
				<div class="payment">
					<?php _e("Register Mail Template",ET_DOMAIN);?>
				</div>	    
				<div class="form payment-setting">
					<div class="form-item">
						<div class="mail-template">
							<div class="mail-input">
								<?php wp_editor( $register_mail ,'register_mail' , $editor_email ); ?>
								<!-- <textarea name="register_mail" id="register-mail" style="width:100%;"><?php echo $register_mail ?></textarea> -->							
								<span class="icon <?php if(empty($register_mail) ) echo 'color-error';?>" data-icon="<?php data_icon($register_mail, 'text') ?>"> </span>
							</div>							
							<div class="mail-control-btn">
								<a href="#" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
							</div>
						</div>
						
					</div>
				</div>    						
			</div>
			
			<div class="item">
				<div class="payment">
					<?php _e("Forgot Password Mail Template",ET_DOMAIN);?>
				</div>	    
				<div class="form payment-setting">
					<div class="form-item">
						<div class="mail-template">
							<div class="mail-input">
								<?php wp_editor( $forgot_pass_mail ,'forgot_pass_mail' , $editor_email ); ?>
								<!-- <textarea name="forgot_pass_mail" id="forgot-pass-mail" style="width:100%;"><?php echo $forgot_pass_mail ?></textarea> -->
								<span class="icon <?php if(empty($forgot_pass_mail) ) echo 'color-error';?>" data-icon="<?php data_icon($forgot_pass_mail, 'text') ?>"> </span>
							</div>
							<div class="mail-control-btn">
								<div>(*)[activate_url] : activate url is require for user to renew their pass, you must have it in your mail </div>
								<a href="#" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
							</div>
						</div>						
					</div>
				</div>    						
			</div>
			
			<div class="item">
				<div class="payment">
					<?php _e("Reset Password Mail Template",ET_DOMAIN);?>
				</div>	    
				<div class="form payment-setting">
					<div class="form-item">
						<div class="mail-template">
							<div class="mail-input">
								<?php wp_editor( $reset_pass_mail ,'reset_pass_mail' , $editor_email ); ?>
								<!-- <textarea name="reset_pass_mail" id="reset-pass-mail" style="width:100%;"><?php echo $reset_pass_mail ?></textarea> -->
								<span class="icon <?php if(empty($reset_pass_mail) ) echo 'color-error';?>" data-icon="<?php data_icon($reset_pass_mail, 'text') ?>"> </span>
							</div>
							<div class="mail-control-btn">
								<a href="#" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
							</div>
						</div>
					</div>
				</div>    						
			</div>

			

		</div>
	</div>
	
	<div class="title font-quicksand mail-template-title" id="ad-mail-template-title">
		<?php _e("Ad-related Email Templates",ET_DOMAIN);?>
	</div>
	<div class="desc" id="ab-mail-template">
		<?php _e("Email templates used for ad-related event. You can use placeholders to include some specific content.",ET_DOMAIN);?> 
		<!-- <a class="find-out font-quicksand" href="#">
			Find out more <span class="icon" data-icon="i"></span>
		</a> -->
		<?php 
	
	
		$auto_email = ET_CEMailTemplate::get_auto_emails();	
		
		?>
		<div class="inner email-template" >
			
			
			<div class="item">
				<div class="payment">
					<?php _e("Sent to sellers to notify his ad has been published",ET_DOMAIN);?>
					<div class="button-enable font-quicksand enable-email">
						<a href="#" rel="mail_approve" title="Disable" class="deactive <?php echo $auto_email['approve'] == 0 ? 'selected' : ''?>">
							<span>Disable</span>
						</a>
						<a href="#" rel="mail_approve" title="Enable" class="active <?php echo $auto_email['approve'] == 1 ? 'selected' : ''?>">
							<span>Enable</span>
						</a>
					</div>
				</div>	    
				<div class="form payment-setting">
					<div class="form-item">
						<div class="mail-template">
							<div class="mail-input">
								
								<?php wp_editor( $approve_mail ,'approve_mail' , $editor_email ); ?>								
								<!-- <textarea name="approve_mail" id="approve-mail" style="width:100%;"><?php echo $approve_mail ?></textarea> -->
								<span class="icon <?php if(empty($approve_mail) ) echo 'color-error';?>" data-icon="<?php data_icon($approve_mail, 'text') ?>"> </span>
							</div>
							<div class="mail-control-btn">
								<a href="#" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
							</div>
						</div>
					</div>
				</div>    						
			</div>
			
			<div class="item">
				<div class="payment">
					<?php _e("Archive ad mail template",ET_DOMAIN);?>
					<div class="button-enable font-quicksand enable-email">
						<a href="#" rel="mail_archive" title="Disable" class="deactive <?php echo $auto_email['archive'] == 0 ? 'selected' : ''?>">
							<span>Disable</span>
						</a>
						<a href="#" rel="mail_archive" title="Enable" class="active <?php echo $auto_email['archive'] == 1 ? 'selected' : ''?>">
							<span>Enable</span>
						</a>
					</div>
				</div>	    
				<div class="form payment-setting">
					<div class="form-item">
						<div class="mail-template">
							<div class="mail-input">
								<?php wp_editor( $archive_mail ,'archive_mail' , $editor_email ); ?>
								<!-- <textarea name="archive_mail" id="archive-mail" style="width:100%;"><?php echo $archive_mail ?></textarea> -->
								<span class="icon <?php if(empty($archive_mail) ) echo 'color-error';?>" data-icon="<?php data_icon($archive_mail, 'text') ?>"> </span>
							</div>
							<div class="mail-control-btn">
								<a href="#" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
							</div>
						</div>
					</div>
				</div>    						
			</div>
			
			<div class="item">
				<div class="payment">
					<?php _e("Reject ad mail template",ET_DOMAIN);?>
					<div class="button-enable font-quicksand enable-email">
						<a href="#" rel="mail_reject" title="Disable" class="deactive <?php echo $auto_email['reject'] == 0 ? 'selected' : ''?>">
							<span>Disable</span>
						</a>
						<a href="#" rel="mail_reject" title="Enable" class="active <?php echo $auto_email['reject'] == 1 ? 'selected' : ''?>">
							<span>Enable</span>
						</a>
					</div>
				</div>	    
				<div class="form payment-setting">
					<div class="form-item">
						<div class="mail-template">
							<div class="mail-input">
								
								<?php wp_editor( $reject_mail ,'reject_mail' , $editor_email ); ?>
								<!-- <textarea name="reject_mail" id="reject-mail" style="width:100%;"><?php echo $reject_mail ?></textarea> -->
								<span class="icon <?php if(empty($reject_mail) ) echo 'color-error';?>" data-icon="<?php data_icon($reject_mail, 'text') ?>"> </span>
							</div>
							<div class="mail-control-btn">
								<div>(*)[reason] : <?php _e("reason when you reject an ad ", ET_DOMAIN) ?></div>
								<a href="#" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
							</div>
						</div>
					</div>
				</div>    						
			</div>
			<!-- payment success receipt mail -->
			<div class="item">
				<div class="payment">
					<?php _e("Sent to sellers for successful payment when they post an ad",ET_DOMAIN);?>
					<div class="button-enable font-quicksand enable-email">
						<a href="#" rel="mail_receipt" title="Disable" class="deactive <?php echo $auto_email['receipt'] == 0 ? 'selected' : ''?>">
							<span>Disable</span>
						</a>
						<a href="#" rel="mail_receipt" title="Enable" class="active <?php echo $auto_email['receipt'] == 1 ? 'selected' : ''?>">
							<span>Enable</span>
						</a>
					</div>
				</div>	    
				<div class="form payment-setting">
					<div class="form-item">
						<div class="mail-template">
							<div class="mail-input">
								<?php wp_editor( $receipt_mail ,'receipt_mail' , $editor_email ); ?>
								<!-- <textarea name="cash_notification_mail" id="cash_notification_mail" style="width:100%;"><?php echo $cash_mail ?></textarea> -->
								<span class="icon <?php if(empty($receipt_mail) ) echo 'color-error';?>" data-icon="<?php data_icon($receipt_mail, 'text') ?>"> </span>
							</div>
							<div class="mail-control-btn">
								<a href="#" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
							</div>
						</div>
					</div>
				</div>    						
			</div>

			<!-- cash notice mail setting -->
			<div class="item">
				<div class="payment">
					<?php _e("Sent to sellers when they post an ad and pay by cash",ET_DOMAIN);?>
					<div class="button-enable font-quicksand enable-email">
						<a href="#" rel="mail_cash_notice" title="Disable" class="deactive <?php echo $auto_email['cash_notice'] == 0 ? 'selected' : ''?>">
							<span>Disable</span>
						</a>
						<a href="#" rel="mail_cash_notice" title="Enable" class="active <?php echo $auto_email['cash_notice'] == 1 ? 'selected' : ''?>">
							<span>Enable</span>
						</a>
					</div>
				</div>	    
				<div class="form payment-setting">
					<div class="form-item">
						<div class="mail-template">
							<div class="mail-input">
								<?php wp_editor( $cash_notice ,'cash_notification_mail' , $editor_email ); ?>
								<!-- <textarea name="cash_notification_mail" id="cash_notification_mail" style="width:100%;"><?php echo $cash_mail ?></textarea> -->
								<span class="icon <?php if(empty($cash_notice) ) echo 'color-error';?>" data-icon="<?php data_icon($cash_notice, 'text') ?>"> </span>
							</div>
							<div class="mail-control-btn">
								<a href="#" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
							</div>
						</div>
					</div>
				</div>    						
			</div>

			<div class="item">
				<div class="payment">
					<?php _e(" Sent to seller when a user sends him/her a message",ET_DOMAIN);?>
				</div>	    
				<div class="form payment-setting">
					<div class="form-item">
						<div class="mail-template">
							<div class="mail-input">
								<?php wp_editor( $message_to_seller ,'message_to_seller_mail' , $editor_email ); ?>
								<!-- <textarea name="reset_pass_mail" id="reset-pass-mail" style="width:100%;"><?php echo $reset_pass_mail ?></textarea> -->
								<span class="icon <?php if(empty($message_to_seller) ) echo 'color-error';?>" data-icon="<?php data_icon($message_to_seller, 'text') ?>"> </span>
							</div>
							<div class="mail-control-btn">
								<a href="#" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
							</div>
						</div>
					</div>
				</div>    						
			</div>
			

		</div>
	</div>
	
</div>

