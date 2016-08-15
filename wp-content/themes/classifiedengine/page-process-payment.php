<?php 
/**
 *	Template Name: Process Payment
 */

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
		do_action('ce_payment_process', $payment_return, $order, $payment_type, $session);
	}

	$ad_id		=	$session['ad_id'];

?>

<!DOCTYPE html>
<!--[if IE 6]>
<html id="ie6" <?php language_attributes(); ?> >
<![endif]-->
<!--[if IE 7]>
<html id="ie7" <?php language_attributes(); ?> >
<![endif]-->
<!--[if IE 8]>
<html id="ie8" <?php language_attributes(); ?> >
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?> >
<!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<!-- Use the .htaccess and remove these lines to avoid edge case issues.
			 More info: h5bp.com/i/378 -->
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<!-- <meta name="viewport" content="width=device-width" /> -->
<meta name="description" content="<?php echo bloginfo('description')?>" />
<meta name="keywords" content="" />
<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged, $user_ID;

	wp_title( '|', true, 'right' );

	?></title>
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />


<!--[if lte IE 8]> <link rel="stylesheet" type="text/css" href="css/lib/ie.css" charset="utf-8" /> <![endif]-->
<?php wp_head(); ?>

</head>
<body class="redirect">
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

	<script type="text/javascript">
	  	jQuery(document).ready (function () {
	  		var $count_down	=	jQuery('.count_down');
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
	</body>
</html>