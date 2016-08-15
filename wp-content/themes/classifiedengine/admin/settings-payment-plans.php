	<style type="text/css">
		.payment-plan label.error {float : left; color : #FF9E78; margin-top : 5px; width: 70%;}
	</style>
	<div class="title font-quicksand"><?php _e("Payment Plans",ET_DOMAIN);?></div>
	<div id="payment_plans_point" class="desc">
		<?php _e("Set payment plans your sellers can choose from when posting new ads.",ET_DOMAIN);?> 
		<!-- <a class="find-out font-quicksand" href="#">
			<?php _e("Find out more",ET_DOMAIN);?><span class="icon" data-icon="i"></span>
		</a> -->
		<div class="inner">
			<div id="payment_lists">
			<?php 
				$plans = self::$payment_package->get_all_plans();
				$json_plans	=	array();
				if ( is_array($plans) ){
					echo '<ul class="pay-plans-list sortable">';
					foreach ($plans  as $key =>  $plan) {

						$plan['id']		=	$plan['ID'];
						$json_plans[]	=	$plan;

						$tooltip = array(
							'delete' => __('Delete', ET_DOMAIN),
							'edit' => __('Edit', ET_DOMAIN),
							);
						$feature = $plan[ET_FEATURED] == 1 ? '<em class="icon-text">^</em>' : '';
						?>
						<li class="item" id="payment_<?php echo $plan['ID'] ?>" data="<?php echo $plan['ID'] ?>">
							<div class="sort-handle"></div>
							<span><?php echo $plan['post_title'] . ' ' . $feature?></span>  
							<?php printf( __('%s for %s days', ET_DOMAIN), et_get_price_format($plan[CE_ET_PRICE]), $plan['et_duration'] ) ?>
							<div class="actions">
								<a href="#" title="<?php _e('Edit', ET_DOMAIN) ?>" class="icon act-edit" rel="<?php echo $plan['ID'] ?>" data-icon="p"></a>
								<a href="#" title="<?php _e('Delete', ET_DOMAIN) ?>" class="icon act-del" rel="<?php echo $plan['ID'] ?>" data-icon="D"></a>
							</div>
						</li>
						<?php
					}
					echo '</ul>';
				}
				else {
					echo '<p>' . __('There is no added plan yet' ,ET_DOMAIN) . '</p>';
				}
				?>
			</div>
			<script type="application/json" id="payment_plans_data">
				<?php echo json_encode( $json_plans )  ?>
			</script>
		
			<div class="item">
				<form id="payment_plans_form" action="" class="engine-payment-form">
					<input type="hidden" name="action" value="et-save-payment">
					<input type="hidden" name="et_action" value="et-save-payment">
					<div class="form payment-plan">
						<div class="form-item">
							<div class="label"><?php _e("Enter a name for your plan",ET_DOMAIN);?></div>
							<input class="bg-grey-input not-empty required" name="payment_name" type="text" />
						</div>
						<div class="form-item f-left-all clearfix">
							<div class="width33p">
								<div class="label"><?php _e("Price",ET_DOMAIN);?></div>
								<input class="bg-grey-input width50p not-empty is-number required number" name="payment_price" type="text" /> 
								<?php echo $selected_currency['label']; ?>
							</div>
							<div class="width33p">
								<div class="label"><?php _e("Availability",ET_DOMAIN);?></div>
								<input class="bg-grey-input width50p not-empty is-number required number" type="text" name="payment_duration" /> 
								<?php _e("days",ET_DOMAIN);?>
							</div>
							<div class="width33p">
								<div class="label"><?php _e("Number of ads",ET_DOMAIN);?></div>
								<input class="bg-grey-input width50p not-empty is-number required number" type="text" name="payment_quantity" /> 
								<?php _e("posts",ET_DOMAIN);?>
							</div>
						</div>
						<div class="form-item">
							<div class="label"><?php _e("Short description about this plan",ET_DOMAIN);?></div>
							<input class="bg-grey-input not-empty" name="payment_desc" type="text" />
						</div>
						<div class="form-item">
							<div class="label"><?php _e("Featured Ad",ET_DOMAIN);?></div>
							<input type="hidden" name="payment_featured" value="0">
							<input type="checkbox" name="payment_featured" value="1"/> <?php _e("Ads posted under this plan will be featured.",ET_DOMAIN);?>
						</div>
						<div class="submit">
							<button class="btn-button engine-submit-btn add_payment_plan">
								<span><?php _e("Save Plan",ET_DOMAIN);?></span><span class="icon" data-icon="+"></span>
							</button>
						</div>
					</div>
				</form>
			</div>

			<script type="text/template" id="template_edit_form">
				<form action="" class="edit-plan engine-payment-form">
					<input type="hidden" name="action" value="et-save-payment">
					<input type="hidden" name="et_action" value="et-save-payment">
					<input type="hidden" name="id" value="{{ id }}">
					<div class="form payment-plan">
						<div class="form-item">
							<div class="label"><?php _e("Enter a name for your plan",ET_DOMAIN);?></div>
							<input class="bg-grey-input not-empty required" name="title" type="text" value="<?php echo '{{ post_title }}' ?>" />
						</div>
						<div class="form-item f-left-all clearfix">
							<div class="width33p">
								<div class="label"><?php _e("Price",ET_DOMAIN);?></div>
								<input class="bg-grey-input width50p not-empty is-number required number" name="price" type="text" value="<?php echo '{{ '.CE_ET_PRICE.' }}' ?>"/>
								<?php et_display_currency($selected_currency['label'], $selected_currency,' ','')?>
							</div>
							<div class="width33p">
								<div class="label"><?php _e("Availability",ET_DOMAIN);?></div>
								<input class="bg-grey-input width50p not-empty is-number required number" type="text" name="duration" value="<?php echo '{{ et_duration }}'?>"/> 
								days
							</div>
							<div class="width33p">
								<div class="label"><?php _e("Ads",ET_DOMAIN);?></div>
								<input class="bg-grey-input width50p not-empty is-number required number" type="text" name="quantity" value="<?php echo '{{ et_number_posts }}' ?>" /> 
								<?php _e("ads",ET_DOMAIN);?>
							</div>
						</div>
						<div class="form-item">
							<div class="label"><?php _e("Short description about this plan",ET_DOMAIN);?></div>
							<input class="bg-grey-input not-empty " name="payment_desc" type="text" value="<?php echo '{{ post_content }}' ?>">
						</div>
						<div class="form-item">
							<div class="label"><?php _e("Featured Ad",ET_DOMAIN);?></div>
							<input type="checkbox" name="featured" value="1" <# if ( <?php echo ET_FEATURED; ?> == 1 ) { #> checked="checked" <# } #>/> <?php _e("Ads posted under this plan will be featured.",ET_DOMAIN);?>
						</div>
						<div class="submit">
							<button  class="btn-button engine-submit-btn add_payment_plan">
								<span><?php _e("Save Plan",ET_DOMAIN);?></span><span class="icon" data-icon="+"></span>
							</button>
							or <a href="#" class="cancel-edit"><?php _e("Cancel", ET_DOMAIN); ?></a>
						</div>
					</div>
				</form>
			</script>
		</div>
	</div>

	