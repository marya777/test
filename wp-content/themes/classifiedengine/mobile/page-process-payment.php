<?php 
global $wp_rewrite;

$payment_return			= array ('ACK' => false);
$payment_type			= get_query_var( 'paymentType' );

// if( !session_id() ) { session_start(); }

$session	=	et_read_session ();

if( $payment_type ) {		
	if(isset ($session['order_id']))
		$order				=	new ET_AdOrder( $session['order_id']);
	else 
		$order				=	new ET_NOPAYOrder();
	
	$visitor				=	CE_Payment_Factory::createPaymentVisitor ( strtoupper($payment_type), $order);
	$payment_return			=	$order->accept ($visitor);

	$payment_return			=	apply_filters( 'et_payment_process', $payment_return, $order , $payment_type);

	do_action( 'ce_payment_process' , $payment_return, $order, $payment_type, $session );	

}

$ad_id		=	$session['ad_id'];

//exit;
et_get_mobile_header();

?>


<div data-role="content" class="post-content">
<?php 
	global $ad , $payment_return;

	$payment_return	=	wp_parse_args( $payment_return, array('ACK' => false, 'payment_status' => '' ));
	extract( $payment_return );
	if($session['ad_id'])
		$ad	=	get_post( $session['ad_id'] );
	else 
		$ad	=	false;

	

	if( ( isset($ACK) && $ACK ) || (isset($test_mode) && $test_mode) ) {
		$permalink	=	get_permalink( $ad->ID );
		/**
		 * template payment success
		*/
		get_template_part( 'template/payment' , 'success' );

	 } else {

		if($ad)
			$permalink	=	et_get_page_link('post-ad', array( 'id' => $ad->ID ));
		else 
			$permalink	=	et_get_page_link('post-ad');

		/**
		 * template payment fail
		*/
		get_template_part( 'template/payment' , 'fail' );

	}

	et_destroy_session();

	?>
	<script src="<?php echo TEMPLATEURL ?>/mobile/js/jquery.js"></script>
	<script type="text/javascript">
	  	$(document).ready (function () {
	  		var $count_down	=	$('.count_down');
			setTimeout (function () {
				window.location = '<?php echo $permalink ?>';
			}, 10000 );
			setInterval (function () { 
				if($count_down.length >  0) {
					var i	=	 $count_down.html();
					$count_down.html(parseInt(i) -1 );
				}					
			}, 1000 );
	  	});
	</script>
</div>
<?php

et_get_mobile_footer();