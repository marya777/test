<?php
global $disable_payment,$step;
$payment_gateways	=	et_get_enable_gateways();
if( !empty( $payment_gateways ) && ! $disable_payment ) { ?>
<div id="step-payment" class="post-ad-step step" >
    <div class="head-step clearfix border-top">
      	<div class="number-step">
       		<?php echo array_shift($step); ?>
      	</div>
      	<div class="name-step">
        	<?php _e("Select your payment method", ET_DOMAIN); ?>
      	</div>
      	<span class="status-step"><i class="fa fa-arrow-right"></i><i class="fa fa-arrow-down"></i></span>
    </div>
    <div class="content-step" style="display:none">
    	<form method="post" action="" id="checkout_form">
			<div class="payment_info"> </div>
			<div style="position:absolute; left : -7777px; " >
				<input type="submit" id="payment_submit" />
			</div>
		</form>
      	<ul class="post-step4" >
			<?php
			do_action ('before_ce_payment_button', $payment_gateways);
			$ce_default_payment = array('paypal', 'cash', '2checkout');

			//TODO : Need for improve

			if (class_exists('WooCommerce')) {
				$available_payment_gateways = WooCommerce::instance()->payment_gateways()->get_available_payment_gateways();
				$available_payment_gateways = array_keys($available_payment_gateways);
				$ce_default_payment = array_merge($ce_default_payment, $available_payment_gateways);
			}

			foreach ($payment_gateways as $key => $payment) {
				if (!isset($payment['active']) || $payment['active'] == -1 || !in_array($key, $ce_default_payment))
					continue;
			?>

        	<li class="clearfix payment-button" data-type="<?php echo $key?>">
              	<span class="name"><span class="fontsize17"><?php echo $payment['label']?></span><br>
              	<?php echo $payment['description'] ?></span>
              	<button class="btn btn-primary" type="button"><?php _e("Select", ET_DOMAIN); ?></button>
            </li>
            <?php }
			do_action('after_ce_payment_button', $payment_gateways);
            ?>
      	</ul>
	</div>
</div><!--/.step4 -->
<?php } ?>