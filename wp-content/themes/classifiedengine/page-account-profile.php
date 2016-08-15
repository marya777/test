<?php 
/**
 * Template Name: Seller's Profile
*/
get_header();
global $user_ID;
$orders			=	ET_Seller::get_current_order($user_ID);
$package_data	=	ET_Seller::get_package_data($user_ID);

$section	=	isset( $_REQUEST['section'])  ? $_REQUEST['section'] : false ;
?>
     <div class="title-page">
		<div class="main-center container">
			<span class="customize_heading text fontsize30"><?php _e("Account", ET_DOMAIN); ?></span>
			<!-- <div class="account logout">
				<a href="<?php echo wp_logout_url(home_url()); ?>" /><?php _e('Logout',ET_DOMAIN);?> <span class="icon" data-icon="Q"></span></a>
			</div>   -->
		</div>
	</div><!--/.title page-->

	<div class="tabs-acount">
		<div class="main-center container">
			<ul class="nav nav-tabs">
				<li>
				  <a title="<?php _e("Views all your ads", ET_DOMAIN); ?>" href="<?php echo et_get_page_link('account-listing')?>" ><?php _e("Your Listings", ET_DOMAIN); ?></a>
				</li>
				<li <?php if(!$section) echo 'class="active"' ?>><a title="<?php _e("Views all your profile", ET_DOMAIN); ?>"  href="<?php echo et_get_page_link('account-profile'); ?>"><?php _e("Seller Profile", ET_DOMAIN); ?></a></li>
				<li <?php if($section == 'password') echo 'class="active"' ?>><a title="<?php _e("Change password", ET_DOMAIN); ?>" href="<?php echo et_get_page_link('account-profile', array('section' => 'password')); ?>"><?php _e("Password", ET_DOMAIN); ?></a></li>
				<li <?php if($section == 'favourites') echo 'class="active"' ?>><a title="<?php _e("Favourites Ads", ET_DOMAIN); ?>" href="<?php echo et_get_page_link('account-profile', array('section' => 'favourites')); ?>"><?php _e("Favourites Ads", ET_DOMAIN); ?></a></li>
				<?php do_action('page_account_nav_tab', $section) ?>
			</ul>
		</div>
	</div><!--/.title page-->

	<div class="page-account-profile main-content" id="seller_profile">
		<div class="container main-center accout-profile">
			<div class="row row-fluid">
				<div class="col-md-8 span8">
	    			<div class="tab-content">
	    			 	<div id="change_password" class="tab-pane active" <?php if( $section != 'password' )  echo 'style ="display:none"'; ?> >
							<form class="change-password " action="#" method="post">
								<div class="form-group control-group">
									<label class="control-label" for="old_password"><?php _e('Old Password',ET_DOMAIN);?></label>
									<div class="controls">
										<input type="password" name="old_password" id="old_password" placeholder="" class="input-block-level required" /> <span class="help-block error"></span>
									</div>
								</div>
								<div class="form-group control-group">
								  <label class="control-label" for="new_password"><?php _e('New Password',ET_DOMAIN);?></label>
								  <div class="controls">
									<input type="password" id="user_pass" name="user_pass" placeholder="" class="input-block-level required" > <span class="help-block error"></span>
								  </div>
								</div>
								<div class="form-group control-group">
								  <label class="control-label" for="retype_new_password"><?php _e('Retype New Password',ET_DOMAIN);?></label>
								  <div class="controls">
									<input type="password" id="renew_password" name="renew_password" placeholder="" class="input-block-level required"> <span class="help-block error"></span>
								  </div>
								</div>
								<div class="divider"></div>
								<div class="form-group control-group">
								  <div class="controls">
									<button type="submit" class="btn btn-primary"><?php _e('Update Changes',ET_DOMAIN);?></button>
								  </div>
								</div>
							</form>
						</div> <!-- end tab change password !-->

						<div id="change_profile" class="tab-pane active"  <?php if( $section )  echo 'style ="display:none"';?>>
							<?php 
								global $current_user,$user_ID;
								$seller 	= ET_Seller::convert($current_user);
							?>

							<form class="form update-profile" id="update_profile" method="post">
					            <div class="control-group form-group ">
					              	<label for="full_name" class="control-label"><?php _e('Your full name',ET_DOMAIN);?></label>
					              	<div class="controls">
					                	<input type="text"  name="display_name" class="input-xlarge" value="<?php echo $current_user->display_name;?>" placeholder="" id="display_name">
					              	</div>
					            </div>
					            <div class="control-group form-group ">
					              	<label for="email" class="control-label"><?php _e('Email',ET_DOMAIN);?></label>
					              	<div class="controls">
					                	<input type="text" required class="input-xlarge" name="user_email" value= "<?php echo $current_user->user_email; ?>" placeholder="" id="user_email">
					              	</div>
					            </div>
					            <div class="control-group form-group ">
					              	<label for="email" class="control-label"><?php _e('Phone',ET_DOMAIN);?></label>
					              	<div class="controls">
					                	<input type="text" class="input-xlarge" name="et_phone" value= "<?php echo $seller->et_phone; ?>" placeholder="" id="et_phone">
					              	</div>
					            </div>
					            <div class="control-group form-group ">
					              	<label for="email" class="control-label"><?php _e('Location',ET_DOMAIN);?></label>
					              	<div class="controls">
					              		<?php
					              		$locations	=	ce_get_locations();
					              		if(!empty($locations)) {
					              			echo "<select name='user_location_id' id='user_location_id'>";
					              			$space  = '';
											$parent	= 0;
					              			foreach ($locations as $key => $value) {
												$tem_parent	=	$value->parent;
									          	if( $tem_parent != $parent )  $space .= '&nbsp;&nbsp;';
									          	if( $tem_parent == 0 ) $space	=	'';
									          	$parent	=	$tem_parent;
					              			 ?>
					              				<option <?php selected( $seller->user_location_id, $value->term_id, true ); ?>  value="<?php echo $value->term_id ?>">
					              					<?php echo $space; echo $value->name; ?>
					              				</option>
					              			<?php }
					              			echo "</select>";
					              		}

					              		?>
					                	<!-- <input type="text" required="" class="input-xlarge" name="user_location" value= "<?php //echo $seller->user_location;;?>" placeholder="Location" id="user_location">  -->
					              	</div>
					              	<!-- <input type="hidden" value="<?php //echo $seller->user_location; ?>" id="user_location" name="user_location" /> -->
					            </div>

					            <?php do_action('et_add_meta_seller_profile',$seller); ?>

					            <div class="control-group form-group ">
					              	<label for="address" class="control-label"><?php _e('Address',ET_DOMAIN)?></label>
					             	<div class="controls">
					                	<input type="text" class="input-xlarge" name="et_address" value="<?php echo $seller->et_address; ?>">
					              	</div>
					            </div>

					            <div class="control-group form-group " style="overflow:hidden">
					              	<label class="control-label" for="profile_picture"><?php _e('Profile Picture',ET_DOMAIN);?></label>
					              	<div class="controls" id="profile_thumb_container">
					              		<div id="profile_thumb_thumbnail" class="image avatar-thumbs">
											<?php echo get_avatar( $current_user->ID , 150 );?>
										</div>
										<div class="input-file upload-profile">
											<div class="left clearfix" style="clear:both; float:left;">
												<span id="<?php echo wp_create_nonce( 'user_avatar_et_uploader' ); ?>" class="et_ajaxnonce"></span>
												<span id="profile_thumb_browse_button" class="bg-grey-button btn-button" style="z-index: 0;">
													<?php _e("Browse files...", ET_DOMAIN); ?>	<span data-icon="o" class="icon"></span>
												</span>
											</div>
										</div>
										<input type="hidden" name="profile_id" id="profile_id" value="<?php echo $user_ID;?>" />
					              </div>
					            </div>

                                <div class="divider"></div>

					            <div class="control-group">
					              <div class="controls">
					                <button class="btn btn-primary" type="submit"><?php _e('Update Changes',ET_DOMAIN);?></button>
					              </div>
					            </div>
			          		</form>
			          	</div><!-- end prifile tab !-->


			          	<!-- Favourites list !-->
			          	<div id="list_favorites" class="row tab-pane active" <?php if( $section != 'favourites' )  echo 'style ="display:none"'; ?> >
							<?php

                   			$favorites  =  (array) get_user_meta($user_ID,'ads_favourites',true);
                   			$paged 		= get_query_var('paged' );
                   			$ads  		=  new WP_Query(array('paged' => max(1,$paged), 'post__in' => $favorites,'post_status' => 'publish', 'post_type'=>CE_AD_POSTTYPE, 'suppress_filters' => FALSE ));

                   			if($ads->have_posts()){
                   				while($ads->have_posts()) :
                   					$ads->the_post();
			    					get_template_part( 'template/ad', 'favourite' );

                   				endwhile;
                   			} else {
                   				get_template_part('template/ad', 'notfound' );
                   			}
	                   		?>

						</div> <!-- end tab change password !-->
						<div class="col-md-12 pagination-page">
						<?php ce_pagination( $ads ); ?>
						</div>

			          	<!-- End Favourites !-->

			         </div>

				</div>
				<div class="col-md-4" id="static-text-sidebar">
				<?php
					ce_seller_packages_data();
					get_sidebar();
					?>
				</div>
			</div>
		</div><!--/.main center-->
	</div><!--/.fluid-container categories items-->
<?php get_footer(); ?>