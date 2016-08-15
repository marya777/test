<?php
global $step, $plans;

?>
<!-- step 1 -->
<div id="step-plan" class="post-ad-step step">
    <div class="head-step clearfix active">
      	<div class="number-step">
       		<?php echo array_shift($step); ?>
      	</div>
      	<div class="name-step step-plan-label">
       		<?php _e(" Select your pricing plan", ET_DOMAIN); ?>
      	</div>
      	<span class="status-step"><i class="fa fa-arrow-right"></i><i class="fa fa-arrow-down"></i></span>
    </div>
    <div class="content-step">
          	<ul class="post-step1">
          	<?php 
          	$k  	= 0;
			$mark 	= 0;
          	$only_free = false;
			/**
			 * check if only one package force user select it
			*/
			if(count($plans) == 1 ) {
				$temp	=	$plans;
				$p		=	array_pop($temp);
				if( $p[CE_ET_PRICE] == 0 )  $only_free = true;
			}

          	foreach( $plans as $key => $plan ) {
          		?>
                <li class="clearfix">
                  	<span class="price"><?php echo et_get_price_format ($plan[CE_ET_PRICE] , 'sup'); ?></span>
                  	<span class="name"><span class="fontsize17"><?php echo $plan['post_title']; ?></span>
					<?php
						$number_of_post	=	$plan['et_number_posts'];
						if($plan['et_number_posts'] > 1 ) {
							if( isset($package_data[$key] ) && $package_data[$key]['qty'] > 0 ) {
              					/**
              					 * print text when company has job left in package
              					*/
              					// $k  	= 1;
              					$mark 	=  1;
              					$number_of_post	=	$package_data[$key]['qty'];
              					if($number_of_post > 1 ) {
              						echo ' - ';printf(__("You have %d ads in this plan.", ET_DOMAIN) , $number_of_post);
              					}
              					else  {
              						echo ' - ';printf(__("You have %d ad in this plan.", ET_DOMAIN) , $number_of_post);
              					}


              				}else {
              					/**
              					 * print normal text if company dont have job left in this package
              					*/
              					echo ' - ';printf(__("This plan includes %d ads.", ET_DOMAIN) , $number_of_post);
              				}

						}

					?>
                  	<br />
                  	<p><?php echo $plan['post_content']; ?></p></span>
                  	<button class="btn btn-primary select-plan <?php if( ($mark == 1 && $k == 0) || $only_free ) { echo 'mark-step' ; $k = 1;} ?>" type="button"
                  			data-package="<?php echo $plan['ID'] ?>"
                  			data-price="<?php echo $plan[CE_ET_PRICE];?>"
							<?php if( $plan[CE_ET_PRICE] > 0 ) { ?>
								data-label="<?php printf(__("You have selected: %s", ET_DOMAIN) , $plan['post_title'] ); ?>"
							<?php } else { ?>
								data-label="<?php _e("You are currently using the 'Free' plan", ET_DOMAIN); ?>"
							<?php } ?>
					 	>
                  		<?php _e("Select", ET_DOMAIN); ?>
                  	</button>
                </li>
            <?php } ?>
          	</ul> <!-- End step1 !-->
    </div>
</div>

<script id="package_plans" type="text/data">
	<?php echo json_encode($plans); ?>
</script>
<!--/.step1 -->