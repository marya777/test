<?php
class CE_Ajax extends ET_Base {

	static $ce_event = array (
		'et-avatar-upload',
		'ce_set_cookie'
	);

	static $ce_priv_event = array (

		'ce_request_thumb' ,
		'et_payment_process' ,
		'et-carousel-upload' ,
		'ce_remove_carousel',

		'ce-add-text-widget',
		'ce-request-widgetid',
		'ce-sort-sidebar-widget',

		'ce_get_sidebar'
	);

	function send ($response) {
		wp_send_json ($response);
	}

	function __construct ( $ajax_event = '' , $private_event = '' ) {

		if($ajax_event == '') $ajax_event	=	self::$ce_event;

		foreach ($ajax_event as $key => $value) {
			$function 	=	str_replace('et-', '', $value);
			$function 	=	str_replace('-', '_', $function);
			$this->add_ajax ($value , $function );
		}

		if($private_event == '') $private_event	=	self::$ce_priv_event;
		foreach ($private_event as $key => $value) {
			$function 	=	str_replace('et-', '', $value);
			$function 	=	str_replace('-', '_', $function);
			$this->add_ajax ($value , $function , true , false );
		}

		// $this->add_ajax('et_register' , 'register', false, true);
		// $this->add_ajax('register' , 'login', false, true);
	}

	/**
	 * user upload avatar
	*/
	function avatar_upload () {
		global $user_ID;

		$res	=	$this->process_upload_image ();
		$profile_id = isset($_REQUEST['profile_id']) ? $_REQUEST['profile_id'] : 0;

		if ( !current_user_can( 'edit_user', $profile_id ) ){
			return false;
		}
		if($res['success'])
			ET_Seller::set_avatar($profile_id,$res['attach_id']);

		wp_send_json ($res);
	}

	/**
	 * process et-carousel-upload ajax action
	*/
	function carousel_upload () {
		$res	= array(
			'success'	=> false,
			'msg'		=> __('There is an error occurred', ET_DOMAIN ),
			'code'		=> 400,
		);

		global $user_ID;
		if(isset($_REQUEST['author'])) {
			$author	=	$_REQUEST['author'];
		}else {
			$author		=	$user_ID;
		}

		$res	=	$this->process_upload_image ($author);
		wp_send_json($res);
	}

	function ce_remove_carousel () {
		if(!current_user_can( 'manage_options' )) {
			global $user_ID;
			$post	=	get_post($_REQUEST['id']);
			if($user_ID != $post->post_author) wp_send_json( array ('success' => false, 'msg' => __("Not owned this image!", ET_DOMAIN)) );
		}
		wp_delete_post( $_REQUEST['id'], true );
		wp_send_json( array( 'success' => true ) );
	}
	function ce_request_widgetid () {
		if(current_user_can( 'manage_options' )) {
			$text		=	new WP_Widget_Text();
			$setting	=	$text->get_settings();
			$i =	0;
			foreach ($setting as $key => $value) {
				if($i < $key) $i = $key;
			}
			$i ++;
			et_sidebar_widget ( $_REQUEST['sidebar'] , 'text-'.$i);
			wp_send_json( array('i' => $i ) );
		}
	}
	/*
	* ce-sort-sidebar-widget
	*/
	function ce_sort_sidebar_widget () {
		header( 'HTTP/1.0 200 OK' );
		header( 'Content-type: application/json' );
		if(!current_user_can('manage_options')) {
			echo json_encode(array('success' => false, 'msg' => __("You have no permission to perform this action", ET_DOMAIN ) )) ;
			exit;
		}

		$sidebar	=	isset($_POST['sidebar'] ) ? $_POST['sidebar'] : '';
		$widget		=	isset($_POST['widget']) ? $_POST['widget'] : '' ;

		global $sidebars_widgets ;
		if( $sidebar == '' || $widget == '' || !isset($sidebars_widgets[$sidebar]) ) {
			echo json_encode(array('success' => false) );
			exit;
		}

		$sidebars_widgets[$sidebar]	=	$widget;
		wp_set_sidebars_widgets($sidebars_widgets);

		echo json_encode(array('success' => true ) );

		exit;

	}

	function ce_get_sidebar () {
		dynamic_sidebar( $_REQUEST['sidebar'] );
		exit;
	}

	/**
	 * process image upload
	*/
	function process_upload_image ( $author = 0 ) {
		$res	= array(
			'success'	=> false,
			'msg'		=> __('There is an error occurred', ET_DOMAIN ),
			'code'		=> 400,
		);
		// check fileID
		if(!isset($_POST['fileID']) || empty($_POST['fileID']) || !isset($_POST['imgType']) || empty($_POST['imgType']) ){
			$res['msg']	= __('Missing image ID', ET_DOMAIN );
		}
		else {
			$fileID		= $_POST["fileID"];
			$imgType	= $_POST['imgType'];

			// check ajax nonce
			if ( !ae_check_ajax_referer($imgType . '_et_uploader', false, false) &&  !check_ajax_referer( $imgType . '_et_uploader', '_ajax_nonce', false ) ){
				$res['msg']	= __('Security error!', ET_DOMAIN );
			}
			elseif(isset($_FILES[$fileID])){

				// handle file upload
				$attach_id	=	et_process_file_upload( $_FILES[$fileID], $author, 0, array(
									'jpg|jpeg|jpe'	=> 'image/jpeg',
									'gif'			=> 'image/gif',
									'png'			=> 'image/png',
									'bmp'			=> 'image/bmp',
									'tif|tiff'		=> 'image/tiff'
									)
								);

				if ( !is_wp_error($attach_id) ){
					try {
						$attach_data	= et_get_attachment_data($attach_id);
						$res	= array(
							'attach_id'	=> $attach_id,
							'success'	=> true,
							'msg'		=> __('Image upload success!', ET_DOMAIN ),
							'data'		=> $attach_data
						);
					}
					catch (Exception $e) {
						$res['msg']	= __( 'Error when updating settings.', ET_DOMAIN );
					}
				}
				else{
					$res['msg']	= $attach_id->get_error_message();
				}
			}
			else {
				$res['msg']	= __('Uploaded file not found', ET_DOMAIN);
			}
		}
		return $res;
	}
	/**
	 * set cookie for list view
	*/
	function ce_set_cookie () {
		setcookie( trim($_REQUEST['name']), trim($_REQUEST['value']) , time() + 3600*24*30 , "/" );
		//setcookie( trim($_REQUEST['name']), trim($_REQUEST['value']) , time() + 3600*24*30 , "/page/" );
		if($_REQUEST['name'] == 'et_location')
			wp_send_json ( array(
					'success' => true ,
					'url' => add_query_arg (array(ET_AdLocation::slug() => $_REQUEST['value'] ) , home_url()) 
				)
		) ;

		wp_send_json (array ('success' => true )) ;
	}

	/**
	 * request carousel thumbnail image for edit ad form
	*/
	function ce_request_thumb () {
		$items	=	isset($_REQUEST['item']) ? $_REQUEST['item'] : array() ; 
		$return =	array ();
		if(!empty($items)) {
			foreach ($items as $key => $value) {
				$return[]	=	et_get_attachment_data ($value , array ('thumbnail'));
			}
			wp_send_json (array ('success' => true, 'data' => $return ));
		}else {
			wp_send_json (array ('success' => false));
		}
	}

	/**
	 * request a payment process
	*/
	function et_payment_process () {
		global $user_ID;
		// remember to check isset or empty here
		$adID			= isset($_POST['ID']) ? $_POST['ID'] : '';
		$author			= isset($_POST['author']) ? $_POST['author'] : $user_ID;
		$packageID		= isset($_POST['packageID']) ? $_POST['packageID'] : '';
		$paymentType	= isset($_POST['paymentType']) ? $_POST['paymentType'] : '';
		$job_error		=	'';
		$author_error	=	'';
		$package_error	=	'';
		$errors			=	array ();
		// job id invalid
		if ( $adID == '' || get_post_type($adID) != CE_AD_POSTTYPE ) {
			$job_error	=	__("Invalid job ID!",ET_DOMAIN);
			$errors[]	=	$job_error;
		} else {
			// author does not authorize job
			$job	=	get_post($adID);
			if( $author != $job->post_author && !current_user_can('manage_options' )) {
				$author_error	=	__("Job author information is incorrect!",ET_DOMAIN);
				$errors[]	=	$author_error;
			}
		}
		// package data invalid
		if( $packageID == '' || get_post_type ( $packageID ) != 'payment_package' ) {
			$package_error	=	__("Invalid job package ID!",ET_DOMAIN);
			$errors[] =	$package_error;
		}
		// input data error
		if( !empty( $errors )) {
			header( 'HTTP/1.0 200 OK' );
			header( 'Content-type: application/json' );
			$response	=	array(
				'success'	=>  false,
				'errors'		=>	$errors
			);
			echo json_encode($response);
			exit;
		}

		////////////////////////////////////////////////
		////////////// process payment//////////////////
		////////////////////////////////////////////////
		$order_data		=	array (
			'payer'				=>	 $author,
			//'currency'			=>	 trim(ET_Payment::get_currency()),
			'total'				=>	 '',
			'status'			=>	 'draft',
			'payment'			=>	 $paymentType,
			'paid_date'			=>	 '',
			'payment_plan' 		=> 	$packageID ,
			'post_parent'		=> 	$adID
		) ;
		/**
		 * filter order data
		*/
		$order_data	=	apply_filters( 'et_payment_setup_order_data', $order_data );

		$plans		=	et_get_payment_plans();
		foreach ($plans as $key => $value) {
			if($value['ID'] == $packageID) {
				$plan		=	$value;
				break;
			}
		}
		$plan['ID']	=	$adID;
		//wp_update_post(array ('ID' => $adID, 'post_status' => 'pending'));
		$company_location	=	et_get_user_field ($user_ID,'recent_job_location');
		$ship	=	array( 'street_address' => isset($company_location['full_location']) ? $company_location['full_location'] : __("No location", ET_DOMAIN));

		// insert order into database
		$order		=	new ET_AdOrder( $order_data , $ship );

		$order->add_product ($plan);

		$order_data		=	$order->generate_data_to_pay ();

		et_write_session ('order_id', $order_data['ID']);
		et_write_session ('ad_id', $adID);

		$arg	=	array (
			'return' => et_get_page_link('process-payment'),
			'cancel' => et_get_page_link('process-payment')
		);
		/**
		 * process payment
		*/
		$paymentType	=	strtoupper( $paymentType );
		/**
		 * factory create payment visitor
		*/
		$visitor		=	CE_Payment_Factory::createPaymentVisitor( $paymentType, $order );

		$visitor->set_settings ($arg);
		$nvp	=	$order->accept( $visitor );
		if($nvp['ACK']) {
			$response	= array(
				'success'		=>	$nvp['ACK'],
				'data'			=>  $nvp,
				'paymentType'	=>	$paymentType
			);
		} else {
			$response	= array(
				'success'		=>	false,
				'paymentType'	=>	$paymentType,
				'msg'			=> __("Invalid payment gateway",ET_DOMAIN)
			);
		}

		$response	=	apply_filters('et_payment_setup', $response, $paymentType, $order );

		wp_send_json( $response );

	}

	/*
	* Update profile user.
	*/
	function update_profile(){
		if( is_user_logged_in() ){
			$request = $_POST['content'];
			global $current_user;
			get_currentuserinfo();
			$display_name 	= trim($request['full_name']);
			$email 			= trim($request['your_email']);
			$exist_email = false;

			if($email != $current_user->user_email)
				$exist_email = email_exists($email);
			if(!$exist_email){
				$aut = wp_update_user(array('ID'=> $current_user->ID, 'display_name' => $display_name, 'user_email' => $email));

				if(!is_wp_error($aut))
					update_user_meta($current_user->ID,'description',$request['description']);
					$response = array('success' => true, 'msg' => ' Update profile successfull!');
			}  else {
				// email has exists in db.
				$response = array('success' => false, 'msg' => 'The email you entered  is exists!');
			}
		} else
			$response = array('success' =>  false, 'msg' => 'User do not login');

		wp_send_json($response);

	}

}
