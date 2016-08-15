<?php
class CE_AjaxSeller extends CE_Ajax {
	static $ce_event = array (
		'et_seller_sync' , 
		'et_login',
		'et_logout',
		'ce-search-seller',
		'ce-load-more-seller',
		'ce-send-seller-message',
		'ce-load-more-favorites',
	);

	static $ce_priv_event = array (
		'et-change-password'
	);

	function send ($response) {
		wp_send_json ($response);
	}

	function __construct () {
		$ajax_event	=	self::$ce_event;
		foreach ($ajax_event as $key => $value) {
			$function 	=	str_replace('et-', '', $value);
			$function 	=	str_replace('-', '_', $function);
			$this->add_ajax ($value , $function );
		}

		$private_event	=	self::$ce_priv_event;
		foreach ($private_event as $key => $value) {
			$function 	=	str_replace('et-', '', $value);
			$function 	=	str_replace('-', '_', $function);
			$this->add_ajax ($value , $function , true , false );
		}

	}

	/**
	 * ajax sync seller
	 * create
	 * update
	*/
	function et_seller_sync () {

		$request	=	$_POST['content'];

		switch ($_REQUEST['method']) {

			case 'create':

				$ce_option = new CE_Options();
				$useCaptcha	=	$ce_option->use_captcha();
				if($useCaptcha && !current_user_can('manage_options') ){

					$image 		= 	isset( $request['recaptcha_challenge_field']) ?  $request['recaptcha_challenge_field'] : '';
					$input 		= 	isset( $request['recaptcha_response_field']) ?  $request['recaptcha_response_field'] : 	'';

					$captcha	=	ET_GoogleCaptcha::getInstance();

					if( !$captcha->checkCaptcha( $image , $input) || empty($image) ) {
						wp_send_json(array('success' => false , 'msg' => __("You enter an invalid captcha!", ET_DOMAIN)) );
					}
				}

				if( is_user_logged_in() )
						wp_send_json(  array('success' => false ,'msg' => __("You have logged in.", ET_DOMAIN) ) );
				$user_ID	=	ET_Seller::insert( $request );

				if(!is_wp_error( $user_ID )) {

					$result	=	ET_Seller::convert(get_userdata($user_ID));
					CE_Mailing::register_seller($user_ID);

				}else {

					if(isset($user_ID->errors)){

						if(isset($user_ID->errors['existing_user_login']) )
							$user_ID= new WP_Error('user_exists',__('Sorry, that username already exists.',ET_DOMAIN));

						if(isset( $user_ID->errors['existing_user_email']) )
							$user_ID= new WP_Error('existing_user_email',__('Sorry, that email address already exists.',ET_DOMAIN) );

						if(isset($user->errors['username_invalid']) )
							$user_ID= new WP_Error('user_exists',__('Sorry,Username only lowercase letters (a-z) and numbers are allowed.',ET_DOMAIN) );

					}


					$result	=	$user_ID;

				}

				break;

			case 'update':
				global $user_ID;
				/**
				 * check permission
				*/
				if( !current_user_can('manage_options') ) {

					if ( $request['ID'] != $user_ID ) {
						wp_send_json(array('success' => false , 'msg' => __("Permission Denied!", ET_DOMAIN)) );
					}
				}

				$result	=	ET_Seller::update($request);

				break;

			default:
				wp_send_json (array('success' => false , 'msg' => __("Undefined Method!", ET_DOMAIN)) );
				break;
		}

		if(!is_wp_error($result) ) {
			/**
			 * generate ajaxnonce
			*/
			$result->ajaxnonce		=	 ae_create_nonce( 'ad_carousels_et_uploader' );
			$result->logoajaxnonce	=	 wp_create_nonce( 'user_avatar_et_uploader' );

			if($_REQUEST['method'] == 'create') {
				wp_send_json ( array('success' => true , 'data' => $result , 'msg' => __("You were signed up successfully.", ET_DOMAIN) ));
			}else {
				if(isset($request['old_password'])) {
					wp_send_json ( array('success' => true , 'data' => $result , 'msg' => __("Your password has been updated.", ET_DOMAIN) ));
				}else {
					wp_send_json ( array('success' => true , 'data' => $result , 'msg' => __("Your profile has been updated.", ET_DOMAIN) ));
				}

			}

		} else {
			wp_send_json ( array('success' => false , 'msg' => $result->get_error_message() ) );
		}

	}

	/**
	* User login
	**/
	function et_login(){

		$response = array();
		$request  = $_POST;
		$username = isset($request['user_email']) ? trim($request['user_email']) : '';
		$password = isset($request['user_pass']) ? $request['user_pass'] : '';

		// check login

		if( !empty($username ) && !empty( $password ) ){
			$user 	= et_login($username, $password, true);
			if( is_wp_error($user) ) {
				// apply login by email here
				$user 	= et_login_by_email($username, $password, true);
			}
			if ( !is_wp_error($user) ){
				$data	  = ET_Seller::convert($user);
				//$data->ajaxnonce		=	ae_create_nonce( 'ad_carousels_et_uploader' );
				/*
				* change create nonce from 2.9.0 co compatible with WP 4.0
				*/

				$data->ajaxnonce		=	ae_create_nonce( 'ad_carousels_et_uploader' );

				$data->logoajaxnonce	=	wp_create_nonce( 'user_avatar_et_uploader' );

				$response = array(
					'status'  => true,
					'success' => true,
					'code' => 200,
					'msg' => __('You have logged in successfully', ET_DOMAIN),
					'data' => $data,
					'ajaxnonce' 	=> 	ae_create_nonce( 'ad_carousels_et_uploader' ),
					'logoajaxnonce'	=>	wp_create_nonce( 'user_avatar_et_uploader' )
				);

				// $response['data']	=	array_merge($response['data'], $_POST);
			}	else  {

				$response = array(
					'status' => false,
					'code' => 401,
					'msg' => __('Your login information was incorrect. Please try again.', ET_DOMAIN),
				);
			}
			/**
			* @since 1.8.4
			*/
			do_action("after_login",$user);
		} else {
			$response = array(
				'status' => false,
				'code' => 401,
				'msg' => __('Your login information are empty. Please fill in all required.', ET_DOMAIN),
			);
		}

		wp_send_json($response);

	}

	// logout
	function et_logout(){
		wp_logout();
		$return = array(
			'status' => 200,
			'msg' => __('You were logged out successfully.', ET_DOMAIN)
			);
		wp_send_json( $return );
	}


	function search_seller ( $local, $name, $args ) {
		$url 		= 	array();
		if( !empty($name) ){
			$args['search'] = $name;
			$url['s'] 		= $name;
		}

		$number	=	$args['number'];

		if(!empty($local)) {
			$args['meta_query']	=	array();
			$term		=	get_term_by( 'slug', $local, 'ad_location');

			if($term && !is_wp_error($term) ) {
				$meta_value	=	array($local);

				$children		=	get_terms( 'ad_location' , array ( 'hide_empty' => false, 'parent' => $term->term_id)  );

				foreach ($children as $key => $value) {
					$meta_value[]	=	$value->name;
				}

				$args['meta_query'] = array (array(
					'key'		=> 'user_location',
					'compare'	=> 'IN',
					'value'		=> $meta_value
				));

			}else {
				$args['meta_key'] 		= 'user_location';
				$args['meta_value'] 	= $local;
				$args['meta_compare'] 	= 'Like';
			}
			$url['l'] 			 	= $local;
		}

		$total_args	=	$args;
		$total_args['number']	=	'';
		//get  user no limit
		$total		=   ET_Seller::list_sellers($total_args);

		$list 		= 	ET_Seller::list_sellers($args);

		if(!empty($list)) {
			$sellers	=	array();
			foreach ($list as $key => $value) {

				$seller		=	ET_Seller::convert($value);
				$ads 		= 	$ads = CE_Ads::query(array('author'=>$value->ID,'showposts'=>-1 , 'meta_key' => ET_FEATURED,'post_type' => CE_AD_POSTTYPE, 'post_status' => 'publish'));
				$ad_html	=	'';

				if($ads->have_posts()){

					$i = 0;
					if($ads->found_posts > 4 ) {
							$ad_html.= '<div class="span1 bg-img text ">
										<a title="'.sprintf(__("View more ads by %s", ET_DOMAIN), $seller->display_name ) .'" href="'.get_author_posts_url( $seller->ID).'" >'.($ads->found_posts - 4).'+ </a>
										</div>';
					}

					while($ads->have_posts()){
						$ads->the_post();
						$ad_html	.=	'<div class="span1 bg-img"><a class="ad-of-user" title ="'.get_the_title().'" href="'.get_permalink().'">';
						$ad_html	.=	get_the_post_thumbnail(get_the_ID() , 'thumbnail' );
						$ad_html	.= '</a> </div>';
						$i++;
						if($i==4)
							break;
					}
				}

				$seller->ads_html		=	$ad_html;
				$seller->avatar			=	get_avatar( $seller->ID, 60 );
				$seller->ads_link		=	get_author_posts_url( $seller->ID);
				$seller->ads_link_title =   sprintf(__("View all ads by %s", ET_DOMAIN), $seller->display_name);

				$sellers[]	=	$seller;
			}

			$total_pages	=	ceil(count($total) / $number) ;
			$big = 9999999;

			$pages = paginate_links( array(
			    //'base' 		=> $base, // the base URL, including query arg
			    'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			    //'format' 	=> $number, // this defines the query parameter that will be used, in this case 
			    'format' => '?paged=%#%',
			    'prev_text'    	=> '<i class="fa fa-arrow-left"></i>',
				'next_text'    	=> '<i class="fa fa-arrow-right"></i>',
			    'total' 	=> $total_pages, // the total number of pages we have
			    'current' 	=> 1, // the current page
			    'end_size' 	=> 1,
			    'mid_size' 	=> 5,
			    'type'		=> 'array',

			));

			$pagination	=	'';
			if(is_array($pages) ){
				global $wp_rewrite;
				$pagination	=	'';
				$page_link	=	et_get_page_link('sellers', $url );
				$admin_ajax	=	admin_url( 'admin-ajax.php' );
				foreach ($pages as $key => $value) {
					$value	=	str_replace( $admin_ajax, $page_link,  $value);
					if( !empty($name) || !empty($local) || !$wp_rewrite->using_permalinks() )

						$value	=	str_replace( '?paged', '&paged',  $value);

					$pagination	.= '<li>'.$value. '</li>'	;
				}
			}

			$number_of_sellers	=	count($list);

			$response 	= 	array(
								'status'=> true,
								'msg' => sprintf(__('%s sellers',ET_DOMAIN), count($total) ),
								'header_msg' => sprintf(__('%s sellers',ET_DOMAIN), count($total) ),
								'count_user' => $number_of_sellers,
								'data' => $sellers ,
								'paginate' => $pagination
							);
		} else {
			$msg 	='<p class="intro-product" >';
			$msg 	.= __('No sellers found for your query.',ET_DOMAIN);
			$msg 	.='</p>';
			$response 	= 	array('status'=> false ,'msg' =>$msg , 'header_msg' => __("0 seller", ET_DOMAIN) );
		}
		return $response;
	}
	/**
	* action ce-search-seller
	* auto serach in page seller-list/
	* Listing seller
	*/
	function ce_search_seller(){

		$request 	= 	$_POST['content'];
		$name 		= 	isset($request['name']) ? $request['name'] : '';
		$local 		= 	isset($request['local']) ? $request['local'] : '';
		$number		=	get_option('posts_per_page');
		$args 		= 	array('number' => $number);

		$response	=	$this->search_seller ( $local, $name, $args );

		wp_send_json($response);

	}

	/**
	 * ajax load more seller
	*/
	function ce_load_more_seller() {

		$request 	= 	$_REQUEST['content'];
		$name 		= 	isset($request['name']) ? $request['name'] : '';
		$local 		= 	isset($request['local']) ? $request['local'] : '';
		$number		=	get_option('posts_per_page');
		$args 			= 	array('number' => $number);
		$args['offset']	=	$number * $request['paged'];

		$response	=	$this->search_seller ( $local, $name, $args );

		wp_send_json($response);
	}

	/**
	 * ajax load more favorites
	*/
	function ce_load_more_favorites() {

		$request 		= 	$_REQUEST['content'];
		$user 			= 	isset($request['user_id']) ? $request['user_id'] : '';

		$number			=	get_option('posts_per_page');
		$args 			= 	array('number' => $number);

		$favorites  	=  (array) get_user_meta($user,'ads_favourites',true);
        $ads  		=  new WP_Query(array('post__in' => $favorites,'post_status' => 'publish', 'post_type'=>CE_AD_POSTTYPE));
        $return 		= array();
       	if($ads->have_posts()){
       		while($ads->have_posts()):
				global $post;
				$ads->the_post();
				$p = CE_Ads::convert(get_the_ID());
				if(isset($p->location[0]) )
					$p->user_location = $p->location[0]->name;
       			$return[] = $p;
       		endwhile;
       	}
       	$response = array('status' => true,'msg' => __('Load ads favorites finish',ET_DOMAIN), 'data' => $return, 'total_pages' => $ads->max_num_pages,'suppress_filters' =>0  ) ;
		wp_send_json($response);
	}
	/*
	* ce-send-seller-message
	*/

	function ce_send_seller_message(){
		$request 		= $_POST;
		$response 		= array('status' => false,'msg' => __('Message not sent!',ET_DOMAIN));
		$seller_id 		= isset($request['seller_id']) ? $request['seller_id'] : '';
		$ad_id 			= isset($request['ad_id']) ? $request['ad_id'] : '';
		$email_user 	= isset($request['email_user']) ? $request['email_user'] : '';
		$phone 			= isset($request['phone']) ? $request['phone'] : '';
		$message 		= isset($request['message']) ? $request['message'] : '';

		do_action("pre_send_message",$_POST);

		$validator		=	new ET_Validator ();

		if( !$validator->validate('email', $email_user)   ) {
			$response 	= array('success' => false,'msg'=> __('You have entered an invalid email!',ET_DOMAIN));
			wp_send_json($response);

		}

		if( isset($request['recaptcha_challenge']) ){
			$captcha	=	ET_GoogleCaptcha::getInstance();
			if( !$captcha->checkCaptcha( $request['recaptcha_challenge'] , $request['recaptcha_response']  ) ) {
				wp_send_json(array('success' => false , 'msg' => __("You enter an invalid captcha!", ET_DOMAIN)) );
			}
		}

		if(!empty($request['last_name']) && !empty($message ) ){

			$send 		= CE_Mailing::contact_seller($request);
			if($send){
				$detector = new ET_MobileDetect();
				if( $detector->isMobile() ) {
					setcookie('contactor_email',$email_user,time() + 3*24*3600,'/');
					setcookie('contactor_fname',$request['first_name'],time() + 3*24*3600,'/');
					setcookie('contactor_lname',$request['last_name'],time() + 3*24*3600,'/');
					setcookie('contactor_phone',$phone,time() + 3*24*3600,'/');
				}

				$response = array('status' => true,'msg' => __('Message sent!',ET_DOMAIN));

			}else {

				$response = array('status' => false ,'msg' => __('Message not sent!',ET_DOMAIN));
			}

			do_action("after_send_message",$_POST, $send);

		} else {
			$response = array('status' => false,'msg' => __('Please fill out empty fields.',ET_DOMAIN));
		}
		wp_send_json($response);
	}

	/**
	 * user change password
	*/
	function change_password(){
		$response = array('success'=>false,'msg' => __('Current password is not exact.',ET_DOMAIN) );

		 if ( is_user_logged_in()) {

		 	global $current_user;

		 	get_currentuserinfo();
			$request = $_POST['content'];
			$old_pass = $request['oldpass'];
			$new_pass = $request['newpass'];
	     	$username = $current_user->user_login;
			$aut = wp_authenticate_username_password(NULL,$username,$request['oldpass']);

			if(!is_wp_error($aut)){
				//wp_set_password( $password, $user_id );
				$user = wp_update_user(array('ID'=>$current_user->ID,'user_pass'=>$new_pass));
				if($user){
					wp_clear_auth_cookie();
					$response = array('success'=>true,'msg' => __('Changed password successfull',ET_DOMAIN) );
				}

			} else {
				//$response = array('success' => false, 'msg'=>strip_tags($aut->get_error_message()));
				$response = array('success' => false, 'msg'=>__('The password you entered is incorrect.',ET_DOMAIN));
			}
		} else {
			$response = array('success'=>false,'msg' => __('User has not login.',ET_DOMAIN) );
		}
		wp_send_json($response);
	}


}