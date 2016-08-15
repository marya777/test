<?php
class CE_AjaxAd extends CE_Ajax {

	static $ce_event = array (
		'ce-load-more-ad',
		'ce-load-more-post',
		'et-listing-related',
		'paging-listing-related',
		'et-mobile-fetch-ad' ,
		'et-update-favorite'

	);

	static $ce_priv_event = array (
		'et-product-sync' ,
		'et_fetch_ad_by_seller'
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

	function ce_load_more_ad () {

		$_REQUEST['paged'] ++;

		$query	=	CE_Ads::query( $_REQUEST );
		$response	=	array ('success' => false, 'data' => array() , 'max_page' => true);
		/**
		 * generate ad data response
		*/
		if( $query->have_posts() ) {
			$response['success']	=	true;
			while ( $query->have_posts() ) { $query->the_post();
				global $post;
				$ad	=	CE_Ads::convert($post);
				$response['data'][]	=	$ad;
			}

			if( $_REQUEST['paged'] > ( $query->max_num_pages-1) ) {
				$response['max_page']	= false;
			}

			$response['max_num_pages']	=	$query->max_num_pages;
			$response['paged']			=	$_REQUEST['paged'];
		}

		wp_send_json( $response );

	}


	/**
	 * load more post
	*/
	function ce_load_more_post () {

		$_REQUEST['paged'] ++;

		$query	=	new WP_Query ( $_REQUEST );
		$response	=	array ('success' => false, 'data' => array() , 'max_page' => true);
		/**
		 * generate ad data response
		*/
		if( $query->have_posts() ) {
			$response['success']	=	true;
			while ( $query->have_posts() ) { $query->the_post();
				global $post;
				$ad	=	($post);
				$cat	=	get_the_category();
				if(isset($cat[0])) {
					$cat			=	$cat[0];
					$ad->category	=	array (
						'name' => $cat->name , 
						'cat_link' => get_term_link( $cat, 'category' ) , 
						'title_link' => sprintf( __("View all posts in %s", ET_DOMAIN) , $cat->name )
					);
				}
				$ad->track_url		=	get_trackback_url();
				$ad->post_title		=	get_the_title();
				$ad->post_excerpt	=	apply_filters( 'the_excerpt' , $post->post_excerpt );
				$ad->author_link	=	get_author_posts_url( $post->post_author );

				$response['data'][]	=	$ad;
			}

			if( $_REQUEST['paged'] > ( $query->max_num_pages-1) ) {
				$response['max_page']	= false;
			}

			$response['max_num_pages']	=	$query->max_num_pages;
			$response['paged']			=	$_REQUEST['paged'];
		}

		wp_send_json( $response );
	}
	/**
	 * et-listing-related
	 * List product item in single product.
	*/
	function listing_related(){

		$request 	= $_POST['content'];
		$type 		= $_POST['content']['type'];
		$request 	= $_POST['content'];
		$paged 		= isset($request['paged']) ? $request['paged'] : 1;
		$terms 		= array();

		if($request['type'] == 'relevant')
			$terms 		= get_the_terms( $request['id'], CE_AD_CAT );

		$response 	= self::switch_response($type,$request['id'],$paged,$terms);

		wp_send_json($response);

	}

	function switch_response($type,$ad_id,$paged,$terms){
		$cats 		= array();
		if(is_array($terms) && !empty($terms)){
			foreach($terms as $term){
				$cats [] = $term->term_id;
			}
			//$cats = implode(",", $cats);
		}

		$args = array(
				'paged' 		=> $paged,
				'post_type'		=> CE_AD_POSTTYPE,
				'post_status'	=> 'publish',
				'posts_per_page'=> apply_filters( 'ce_filters_number_ad_related', 16 ),
				'post__not_in'	=> array($ad_id) ,
				//'meta_key'		=> ET_FEATURED
			);

		if($type  == 'popular')
			$args = wp_parse_args(array('meta_key' => 'et_post_views', 'orderby' => 'meta_value_num'), $args);
		else if( $type == 'relevant' && count($cats) > 0 )
			$args = wp_parse_args(array
				('tax_query' => array(
					//'relation' => 'AND',
					array(
						'taxonomy'  => CE_AD_CAT,
						'field' 	=> 'id',
						'terms' 	=> (array) $cats,
						'operator' 	=> 'IN'
					)
				) ), $args);

  		global $post;

  		$ad_query 	= CE_Ads::query($args);
  		$html 		= '';
  		$data		=	array();
  		while( $ad_query->have_posts() ) {

  			$ad_query->the_post ();
  			$ad 		= CE_Ads::convert ($post);
  			$data[]		= $ad;
		}

		if( empty( $data ) )
			$html = __('No ads found.',ET_DOMAIN);

		$response = array('success' => true,  'totalPages' => $ad_query->max_num_pages,'paged' => $paged, 'found_posts'=> $ad_query->found_posts, 'data' => $data);
		return $response;
	}


	function mobile_fetch_ad () {
		$request	=	$_REQUEST;
		unset($request['action']);

		$args	=	array(	'post_status' 	=> 'publish',
							'paged' 		=> $request['paged'] + 1,
							// 'meta_key'		=> ET_FEATURED,
							// 'orderby'		=> 'meta_value date',
						);
		$i = 0;
		if(isset($request[CE_AD_CAT])) {
			$args['tax_query']	=	array(
										array(
											'taxonomy'	=> CE_AD_CAT,
											'field'		=> 'slug',
											'terms'		=> $request[CE_AD_CAT]
										)
									);
			$i++;
		}
		/**
		 * build ad location query
		*/
		if(isset($request['ad_location'])) {
			if($i == 1) { // if isset query cat
				$args['tax_query'] = array(
							'relation' => 'AND',
							array(
								'taxonomy'	=> CE_AD_CAT,
								'field'		=> 'slug',
								'terms'		=> $request[CE_AD_CAT]
							),
							array(
								'taxonomy'	=> 'ad_location',
								'field'		=> 'slug',
								'terms'		=> $request['ad_location']

							)
						);
			} else {
				$args['tax_query']	=	array(
								array(
									'taxonomy'	=> 'ad_location',
									'field'		=> 'slug',
									'terms'		=> $request['ad_location'],
									'operator'	=> 'IN'
								)
							);
			}

		}

		/**
		 * add search keyword
		*/
		if(isset($request['s']) && $request['s'] != '' ) {
			$args['s']	=	$request['s'];
		}

		/**
		 * add search keyword
		*/
		if(isset($request['author']) && $request['author'] != '' ) {
			$args['author']	=	$request['author'];
		}

		$new	=	CE_Ads::query($args);
		if( $new->max_num_pages == ($request['paged'] + 1)) {
			$response['the_last']	=	true;
		}else {
			$response['the_last']	=	false;
		}

		$response	=	array ('success' => false, 'data' => array());
		/**
		 * generate ad data response
		*/
		while($new->have_posts()) { $new->the_post();
			global $post;
			$ad	=	CE_Ads::convert($post);
			$response['data'][]	=	$ad;
		}

		if(!empty($response['data'])) {
			$response['success']	= true;
		}else {
			$response['msg']	= __("No results found for your query.", ET_DOMAIN);
		}

		$response['featured']	=	__("Featured Ads", ET_DOMAIN);
		$response['latest']		=	__("Latest Ads", ET_DOMAIN);

		wp_send_json($response);
	}

	function update_favorite(){
		global $user_ID;
		$resp 	= array('success' => true, 'msg' => __('Add to favourites fail',ET_DOMAIN));
		if($user_ID){
			$ad_id 		= (int)$_REQUEST['id'];
			$action 	=  isset($_REQUEST['type']) ? $_REQUEST['type'] : 'add';

			$favorites 	= (array)get_user_meta($user_ID,'ads_favourites',true);

			if($action == 'remove'){
				$favorites =  array_diff($favorites, array($ad_id) );

			}else if(!in_array($ad_id,$favorites)){
				array_push($favorites, $ad_id);
			} else {
				$resp = array('success' => false, 'msg' => __('Ad has added to favourites',ET_DOMAIN) );
			}

			$option = update_user_meta($user_ID, 'ads_favourites', $favorites);

			if(!is_wp_error( $option ))
				$resp = array('success' => true, 'msg' => __('Update favourites successful',ET_DOMAIN) );

		} else {
			$resp['msg'] = __('You must login to add favourites',ET_DOMAIN);
		}

		wp_send_json( $resp );

	}



	function et_fetch_ad_by_seller () {

		$response				=	array ();
		$args					=	$_REQUEST;
		$args['posts_per_page']	=	-1;
		$args['author_name']	= ''	;
		// echo "<pre>";
		// print_r( $args ) ;
		// echo "</pre>";
		$new	=	CE_Ads::query($args);
		while($new->have_posts()) { $new->the_post();
			global $post;
			// $ad	=	CE_Ads::convert($post);
			$temp	=	array ();
			$temp['id']		=	$post->ID;
			$temp['label']	=	$post->post_title;
			$temp['value']	=	$post->post_title;
			$temp['author']	=	$post->post_author;
			$response[]	=	$temp;
		}
		wp_send_json( $response );
	}

	/**
	 * action et-product-sync
	*/
	function product_sync () {
		global $user_ID, $current_user;
		$request	=	$_POST;
		$content	=	$request['content'];
		// unset package data when edit job
		if( isset( $content['ID'] ) && !isset( $content['renew'] ) ) {
			unset($content['et_payment_package']);
		}

		// could not create with an ID
		if( $request['method'] == 'create' && isset($content['ID']) ) {
			wp_send_json(array('success' => false, 'msg' => __('You can not create a new ad with exists ID!', ET_DOMAIN)) );
		}
		$ce_option 		= new CE_Options();
		$useCaptcha		=	$ce_option->use_captcha();

		if( $useCaptcha && isset($content['recaptcha_response_field']) ) {
			$captcha	=	ET_GoogleCaptcha::getInstance();
			if( !$captcha->checkCaptcha( $content['recaptcha_challenge_field'] , $content['recaptcha_response_field']  ) ) {
				wp_send_json(array('success' => false , 'msg' => __("You enter an invalid captcha!", ET_DOMAIN)) );
			}
		}
		/**
		 * check if the user is using the right package
		*/
		$package	=	false;
		if( isset($content['et_payment_package']) && $content['et_payment_package'] != '' && !et_get_payment_disable() ) {
			//$use_package	=	ET_Seller::check_use_package( $content['et_payment_package'] );
			$package		=	ET_PaymentPackage::get( $content['et_payment_package'] );
		}
		/**
		 * update seller address if empty
		*/
		if( isset($content['et_full_location']) ) {
			$seller	=	ET_Seller::convert($current_user);
			$args	=	array ('ID' => $user_ID, 'et_address' => $content['et_full_location']);

			if( !$seller->et_address || !$seller->user_location ) {

				if( !$seller->user_location ) {

					$term	=	get_term_by('id', $content['ad_location'], 'ad_location'  );
					if(!is_wp_error($term)) {
						$args['user_location_id']	=	$content['ad_location'];
						$args['user_location']		=	$term->name;
					}
				}
				ET_Seller::update($args);
			}
		}

		/**
		 * checking old data
		*/
		if($request['method'] == 'update') {

			$prev_post 	= 	get_post( $content['ID'] ); // get current status and compare to display msg.

			if($prev_post->post_status == 'reject' ) { // change post status to pending when edit rejected ad
				$content['post_status']	=	'pending';
			}

		}
		do_action("befor_syn_ad", $content);

		$result		=	CE_Ads::sync( $request['method'], $content );

		if( !is_wp_error( $result ) ) {

			$request_status = isset($request['content']['post_status']) ? $request['content']['post_status'] :'';

			$msg = __('Ad has been updated.',ET_DOMAIN);
			$prev_status = isset($prev_post->post_status) ? $prev_post->post_status  :'';

			if( $result->post_status != $prev_status ) {
				if($result->post_status == 'publish')
					$msg = __('The ad is now active.',ET_DOMAIN);
				else
					$msg = sprintf(__('The ad is now %s.',ET_DOMAIN),$result->post_status);
			}

			$response	=	array('success' => true,  'data' => $result, 'method' => $request['method'], 'msg' => $msg );


			if( $package ) {
								// check seller use package or not
				$check = ET_Seller::package_or_free ( $package->ID, $result ); // check use package or free to return url
				if($check['success'])	{
					$response['redirect_url']	=	$check['url'];
				}

				// check seller have reached limit free plan
				$check	=	ET_Seller::ce_limit_free_plan ( $package );
				if( $check['success'] ) {
					$response['success']	=	 false;
					$response['msg']		=	 $check['msg'];
					wp_send_json ($response);
				}
			} else {

				/*
				* disable payment gate way.
				*/

				// $expiry_day  	= (int) get_theme_mod('ce_number_days_expiry',15);
				// if( !et_get_payment_disable() ) {
				// 		ce_log("disable");
				// 	// enable payment gate way and payment free.
				// 	$et_payment_package_id 	= get_post_meta($result->ID,'et_payment_package', true);
				// 	$expiry_day 		 	= get_post_meta($et_payment_package,'et_duration', true);
				// }
				// ce_log("so ngay".$expiry_day);


				// $expiry_day  	= (int) get_theme_mod('ce_number_days_expiry',15);
				// $expiry_time 	= current_time( 'timestamp' ) + $expiry_day*24*60*60;
				// $expiry_date 	= date("Y-m-d H:i:s", $expiry_time );

				// update_post_meta($result->ID,'et_expired_date',$expiry_date);


				$status 		= get_post_status($result->ID);
				$options	=	new CE_Options();
				if($status == 'archive' && !isset($content['update_type']) ){
					$ad_id = wp_update_post( array('ID' => $result->ID, 'post_status' => 'pending') );
				}
				else if( !$options->use_pending() && !isset($content['update_type']) ){
					wp_update_post( array('ID' => $result->ID, 'post_status' => 'publish') );
				}
				unset($response['et_ad_order']);
				unset($response['et_payment_package']);

				$return = array('success' => true, 'redirect_url' => get_permalink( $result->ID ), 'msg' => __('Ad has been updated',ET_DOMAIN), 'data' => $response );

				wp_send_json( $return );
			}

			wp_send_json( $response );
		}

		else
			wp_send_json (array('success' => false, 'msg' => $result->get_error_message()) );

	}
}