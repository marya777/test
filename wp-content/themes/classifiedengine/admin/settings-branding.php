<div id="customize-branding" <?php if ( $sub_section != 'branding') echo 'style="display: none"' ?> class="et-main-main inner-content branding clearfix subcontent-branding">
	<div class="title font-quicksand"><?php _e('Upload Logo', ET_DOMAIN );?></div>
	<div class="desc">
		<?php _e('Your logo should be in PNG, GIF or JPG format, within <strong>150x50px</strong>  and less than <strong>1500Kb</strong>.', ET_DOMAIN);?>
		<div class="customization-info">
			<?php $uploaderID = 'website_logo';?>
			<div class="input-file upload-logo" id="<?php echo $uploaderID;?>_container">
			<?php 
				$ce_option 		= new CE_Options;
				$website_logo 	= $ce_option->get_website_logo();
				
				
			?>
					<div class="left clearfix">
						<div class="image" id="<?php echo $uploaderID;?>_thumbnail">
						<?php if ($website_logo){ ?>
							<img src="<?php echo $website_logo[0];?>"/>
						<?php } ?>
						</div>
					</div>
				
				<span class="et_ajaxnonce" id="<?php echo wp_create_nonce( $uploaderID . '_et_uploader' ); ?>"></span>
				<span class="bg-grey-button button btn-button" id="<?php echo $uploaderID;?>_browse_button">
					<?php _e('Browse', ET_DOMAIN);?>
					<span class="icon" data-icon="o"></span>
				</span>

			</div>
		</div>
		<div style="clear:left"></div>
	</div>

	<div class="title font-quicksand margin-top30"><?php _e('Upload Mobile Icon', ET_DOMAIN);?></div>
	<div class="desc">
		<?php _e('This icon will be used as a launcher icon for iPhone and Android smartphones and also as the website favicon. The image dimensions should be <strong>57x57px</strong>.', ET_DOMAIN);?>
		<div class="customization-info">
			<?php $uploaderID = 'mobile_icon';?>
			<div class="input-file  mobile-logo" id="<?php echo $uploaderID;?>_container">
				<?php 
				$mobile_icon = $ce_option->get_mobile_icon();
				
					?>
					<div class="left clearfix">
						<div class="image" id="<?php echo $uploaderID;?>_thumbnail">
						<?php if ($mobile_icon){ ?>
							<img src="<?php echo $mobile_icon[0];?>"/>
						<?php } ?>
						</div>
					</div>
				
				<span class="et_ajaxnonce" id="<?php echo wp_create_nonce( $uploaderID . '_et_uploader' ); ?>"></span>
				<span class="bg-grey-button button btn-button" id="<?php echo $uploaderID;?>_browse_button">
					<?php _e('Browse', ET_DOMAIN);?>
					<span class="icon" data-icon="o"></span>
				</span>
			</div>
		</div>
		<div style="clear:left"></div>
	</div>


</div>