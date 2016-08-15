<?php

$plans			=	et_get_payment_plans ();
$package_data	=	ET_Seller::get_package_data($user_ID);

$one_plan		=	0;
if(count($plans) == 1 ) {
	$one_plan = 1;
}

if(!empty($plans)) { ?>
  	<div data-role="fieldcontain" class="post-new-classified">
  		<label class="ui-input-text"><?php _e("Select a pricing plan", ET_DOMAIN); ?><span class="subtitle"><?php _e("We offer different types of pricing plan.", ET_DOMAIN); ?></span></label>
	    <?php foreach ($plans as $key => $plan) { ?>
		    <label for="package-<?php echo $plan['ID']; ?>">
		    	<?php echo $plan['post_title']; ?> - <?php echo et_get_price_format ($plan[CE_ET_PRICE] , 'sup'); ?>
			    <span class="subtitle">
					<?php 
						$number_of_post	=	$plan['et_number_posts'];
						$mark			=	0;
						/**
						 * default mark if only have one plan
						*/
						if($one_plan) $mark	=	1;

						if($plan['et_number_posts'] > 1 ) {
							if( isset($package_data[$key] ) && $package_data[$key]['qty'] > 0 ) {
              					/**
              					 * print text when company has job left in package
              					*/
              					// $k  	= 1;
              					$mark 	=  1;
              					$number_of_post	=	$package_data[$key]['qty'];
              					echo '<span style="color:#0F5ED5">';
              					if($number_of_post > 1 ) {

              						printf(__("You purchased and have %d ads left in this plan.", ET_DOMAIN) , $number_of_post);
              					}
              					else  {
              						printf(__("You purchased and have %d ad left in this plan.", ET_DOMAIN) , $number_of_post);
              					}
              					echo '</span>';

              				}else {
              					/**
              					 * print normal text if company dont have job left in this package
              					*/
              					printf(__("This plan includes %d ads.", ET_DOMAIN) , $number_of_post);		
              				}
							echo '<br/>';
						} 

						echo $plan['post_content']; 
					?>
              	</span>
            </label>
			
		    <input type="radio" <?php checked( 1, $mark ); ?> name="et_payment_package" id="package-<?php echo $plan['ID']; ?>" value="<?php echo $plan['ID']; ?>">
	   	<?php } ?>
  	</div>
<?php } ?>