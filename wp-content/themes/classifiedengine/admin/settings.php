<?php
class ET_AdminSettings extends ET_AdminMenuItem {

	//private $options;
	private $ad_location;
	private $ad_category;

	static	$payment_package;
	static  $options;
	static 	$option_group;
	static 	$options_arr;
	static 	$mail_template;

	function __construct(){
		parent::__construct('et-settings',  array(
			'menu_title'	=> __('Settings', ET_DOMAIN),
			'page_title' 	=> __('SETTINGS', ET_DOMAIN),
			'callback'   	=> array($this, 'menu_view'),
			'slug'			=> 'et-settings',
			'page_subtitle'	=> __('ClassifiedEngine Settings', ET_DOMAIN),
			'pos' 			=> 6
		));

		$this->add_ajax('et_sync_paymentplan', 'sync_paymentplan', true, false);
		$this->add_ajax('et_sort_payment_plan', 'sort_payment_plan' , true , false);

		$this->add_ajax('et-change-currency', 'change_currency', true, false);
		$this->add_ajax('et-add-new-currency', 'add_currency', true, false );

		$this->add_ajax('save-option','save_option' , true , false);		
		$this->add_ajax('save-mail-template','et_save_mail_template');		//save-mail-option
		$this->add_ajax('save-api-option','save_api_option' , true , false);


		$this->add_ajax('et_update_option' , 'sync_option', true , false );

		$this->add_ajax('et-enable-option' , 'et_enable_option', true , false );
		$this->add_ajax('et-disable-option' , 'et_disable_option', true , false );
		$this->add_ajax('et-reset-mail-template','et_reset_default_mail',true,false);
		
		//branding - upload images(logo and mobile icon).		
		$this->add_ajax('et-change-branding','et_change_branding',true,false);
		//language ajax
		$this->add_ajax('et-change-language','et_ajax_change_language',true,false);


		/**
		 * payment package
		*/
		self::$payment_package	=	new ET_PaymentPackage();
		/**
		 * classified engine options
		*/
		self::$options			=	new CE_Options();
		self::$mail_template 	= 	new ET_CEMailTemplate();

		$this->ad_category =	new ET_AdCatergory();
		$this->ad_location	=	new ET_AdLocation();

		$this->ad_location->register_ajax();
		$this->ad_category->register_ajax();

	}
	
	/*
	* save mail template
	*/
	function et_save_mail_template(){

		$this->check_permission ();
		$request 	= 	$_POST['content'];
		$res 		=	array('success'=> false , 'msg'=>__('Updated email template false!', ET_DOMAIN));
		$name 		= 	'et_'.$request["name"];	
		$result 	= 	self::$mail_template->update_mail_template($request['name'],$request['value']);
		if($result){
			$res =array('success'=>true, 'msg'=>__('Updated email template success!', ET_DOMAIN));	
		}
		wp_send_json($res);
	}
	/*
	/* action : et-enable-option
	*enable option in setting dashboard
	*/
	function et_enable_option () {

		$this->check_permission ();
		header( "Content-Type: application/json" ); 
		$response	=	array (
			'success'	=> 	true,
			'msg'		=>	'enable'
		);
		
		$gateway	=	strtoupper($_POST['gateway']);
		$ce_options	=	new CE_Options();
		switch ($gateway) {
			case 'PAYMENT_TEST_MODE' :
				et_set_payment_test_mode (1);
				break;
				
			case 'PAYMENT_DISABLE' :
				et_set_payment_disable(1);
				break;

			case 'CURRENCY_ALIGN' :				
				$currency_sign 	= $ce_options->update_option('et_currency_align' , 'left');
				break;
			case 'CURRENCY_FORMAT' :				
				$currency_sign 	= $ce_options->update_option('et_currency_format' , 1);				
				break;
			case 'PENDING_AD' :				
				$ce_options->set_use_pending(1);			
				break;
			case 'COMMENT_AD' :				
				$ce_options->set_use_comment(1);			
				break;
			case 'USE_CAPTCHA' :				
				$result = $ce_options->set_use_captcha(1);
				$response['success'] = $result;
				break;
			case 'COMMENT_AD_CAPTCHA' :
				
				$result = $ce_options->set_use_captcha_form_comment(1);
				$response['success'] = $result;
				break;

			case 'FACEBOOK_LOGIN' :				
				$ce_options->set_facebook_login(1);
				break;
			case 'TWITTER_LOGIN' :				
				$ce_options->set_twitter_login(1);
				break;

			case 'MAIL_APPLY':
			case 'MAIL_REMIND':
			case 'MAIL_APPROVE':
			case 'MAIL_ARCHIVE':
			case 'MAIL_REJECT':
			case 'MAIL_CASH_NOTICE' : 
			case 'MAIL_RECEIPT' : 
				$key 		= substr($_POST['gateway'], 5);
				$return = ET_CEMailTemplate::et_set_auto_email($key);
				$response['msg'] = $key . ' is enabled ';
			break;
			case 'PAYPAL':
			case '2CHECKOUT':
			case 'GOOGLE_CHECKOUT' :
			case 'CASH' :
			default:
				$return		=	et_enable_gateway($_POST['gateway']);
				if( !$return ) {
					$response['success']	=	false;
					$response['msg']		=	__('Please fill in required text.', ET_DOMAIN);
				}
			break;
		}
		 
		echo json_encode( $response );
		exit;
	}


	/*
	* et-disable-option 
	*/
	function et_disable_option () {

		$this->check_permission ();
		header( "Content-Type: application/json" ); 
		$response	=	array (
			'success'	=> 	true,
			'msg'		=>	'disable'
		);
		
		$gateway		=	strtoupper($_POST['gateway']);
		$ce_options	=	new CE_Options();
		switch ($gateway) {

			case 'PAYMENT_TEST_MODE' :
				et_set_payment_test_mode (0);
				break;

			case 'PAYMENT_DISABLE' :
				et_set_payment_disable (0);
				break;

			case 'CURRENCY_ALIGN' :				
				$currency_sign 	= $ce_options->update_option('et_currency_align' , 'right');
				break;
			case 'CURRENCY_FORMAT' :				
				$currency_sign 	= $ce_options->update_option('et_currency_format' , 2);				
				break;

			case 'PENDING_AD' :				
				$ce_options->set_use_pending(0);
			break;
			case 'COMMENT_AD' :				
				$ce_options->set_use_comment(0);
			break;
			case 'COMMENT_AD_CAPTCHA' :			
				$result = $ce_options->set_use_captcha_form_comment(0);				
				break;

			case 'USE_CAPTCHA' :				
				$ce_options->set_use_captcha(0);
				break;
			case 'FACEBOOK_LOGIN' :				
				$ce_options->set_facebook_login(0);
				break;
			case 'TWITTER_LOGIN' :				
				$ce_options->set_twitter_login(0);
				break;

			break;		
			case 'MAIL_APPLY':
			case 'MAIL_REMIND':
			case 'MAIL_APPROVE':
			case 'MAIL_ARCHIVE':
			case 'MAIL_REJECT':
			case 'MAIL_CASH_NOTICE' : 
				$key 		= substr($_POST['gateway'], 5);
				$return = ET_CEMailTemplate::et_set_auto_email($key);
				$response['msg'] = $key . ' is disabled ';
			break;
			case 'PAYPAL':
			case '2CHECKOUT':
			case 'GOOGLE_CHECKOUT' :
			case 'CASH' :
			default : 
				$return		=	et_disable_gateway($_POST['gateway']);
			break;
		}
		 
		echo json_encode( $response );
		exit;
	}

	function et_reset_default_mail(){

		$this->check_permission ();
		$res =array('success'=>true, 'msg'=>__('Reset email template false!', ET_DOMAIN));
		$type = $_POST['type'];
		$type = 'et_'.$type;
		$result = self::$mail_template->reset_mail_template($type);		
		if($result != 1)
			$res =array('success'=>true, 'msg'=>$result);
		wp_send_json($res);

	}
	function save_option(){

		$this->check_permission ();
		$request 	= $_POST['content'];
		$option 	= $request['name'];

		$result 	= false;
		$name 		= 'et_'.$option;

		switch($name){
			case 'et_site_title':
				self::$options->set_site_title($request['value']);
				break;
			case 'et_site_desc' :
				self::$options->set_site_desc($request['value']);
				break;
			case 'et_facebook_id' :
				$result = ET_FaceAuth::save_app_id($request['value']);
				break;
			case 'et_twitter_key' :
				$result = ET_TwitterAuth::save_twitter_key($request['value']);
				break;
			case 'et_twitter_secret' :
				$result = ET_TwitterAuth::save_twitter_secret($request['value']);
				break;
			default :
				self::$options->$name 	= $request['value'];	
				$result 				= self::$options->save();


		}

		if($option == 'ce_limit_free_plan') {
			set_theme_mod( $option,  $request['value'] );
		}
		if( $option == 'private_key' || $option == 'public_key' ) {
			$google_captcha	=	ET_GoogleCaptcha::get_api();
			$google_captcha[$option] = $request['value'];
			ET_GoogleCaptcha::set_api( $google_captcha );
			wp_send_json( array ('success' => true ) );
		}
		if($result)
			$res 	= 	array('success'=>true, 'msg'=>__('Updated option successfull!', ET_DOMAIN));				
		 else  
			$res 	= array('success'=>false, 'msg'=>__('Updated option false!', ET_DOMAIN));
		wp_send_json ( $res );
		
	}
	

	function save_api_option(){

		$this->check_permission();
		
		$request 	= $_POST['content'];
		$name 		= str_replace('_', '' , strtoupper($request['name']) ) ;
		$value 		= $request['value'];

		$response	=	et_update_payment_setting ($name, $value );
		wp_send_json( $response );

	}


	public function on_add_scripts(){
		$this->add_existed_script( 'jquery' );
		$this->add_existed_script( 'underscore' );
		$this->add_existed_script( 'backbone' );
		$this->add_existed_script( 'jquery.validator' );
		$this->add_existed_script('plupload-all');
		$this->add_existed_script ( 'jquery-textarea-autosize' );
		/**
		 * add sort script ui
		*/
		$this->add_existed_script('jquery-ui-sortable');
		$this->add_script( 'jquery.nestedsort', TEMPLATEURL.'/js/lib/jquery.nestedsort.js', array('jquery','jquery-ui-sortable') );


		$this->add_script('carouFredSel', TEMPLATEURL.'/js/lib/jquery.carouFredSel-6.2.0.js', array ('jquery') , '6.2.0' , true);
		/**
		 * content setting script
		*/
		$this->add_script( 'et_setting_content', TEMPLATEURL.'/js/admin/settings-content.js', array('jquery','jquery-ui-sortable',  'underscore', 'backbone', 'ce') );
		$this->add_script( 'et_setting', TEMPLATEURL.'/js/admin/settings.js', array('jquery', 'underscore', 'backbone', 'ce') );
		
		/**
		 * localize script
		*/
		wp_localize_script( 'et_setting', 'et_setting', array(
					'payment_plan_error_msg' => __("Input is invalid. Please check again.", ET_DOMAIN),
					'del_parent_cat_msg' => __("You cannot delete a parent category. Delete its sub-categories first.", ET_DOMAIN),
					'del_parent_location_msg' => __("You cannot delete a parent location. Delete its sub-locations first.", ET_DOMAIN)
				) );
	}

	public function on_add_styles(){
		$this->add_existed_style('admin.css');
	}

	function check_permission () {

		$message	=	 array (
				'success'	=> false,
				'msg'	=> __("Permission denied!", ET_DOMAIN)
			);

		if(!current_user_can( 'manage_options' )) {
			wp_send_json ( $message );
		}
	}

	public function sync_option () {
		$this->check_permission ();
		// update option here
		/**
		 * self::$option->set ($key, $value);
		 * self::$option->save();
		*/
	}

	/**
	 * payment plan sync
	*/
	public function sync_paymentplan () {

		$this->check_permission ();

		if(isset($_REQUEST['content']['et_description']) && $_REQUEST['content']['et_description'] == '') {
			if($_REQUEST['content'][ET_FEATURED] == 1)
				$_REQUEST['content']['et_description']	=	sprintf(__("Your ad will be displayed as featured for %s days.", ET_DOMAIN), $_REQUEST['content']['et_duration']);
			else
				$_REQUEST['content']['et_description']	=	sprintf(__("Your ad will be displayed as normal for %s days.", ET_DOMAIN), $_REQUEST['content']['et_duration']);
		}

		$response	=	self::$payment_package->sync($_REQUEST);
		//$response->id	=	$response->ID;
		if($response)
			wp_send_json( array ('success' => true , 'data' => $response));
		else
			wp_send_json( array ('success' => false , 'data' => $response));

	}

	public function sort_payment_plan () {

		$this->check_permission ();

		parse_str( $_REQUEST['content']['order'] , $sort_order);

		// update new order
		global $wpdb;
		$sql = "UPDATE {$wpdb->posts} SET menu_order = CASE ID ";
		foreach ($sort_order['payment'] as $index => $id) {
			$sql .= " WHEN {$id} THEN {$index} ";
		}
		$sql .= " END WHERE ID IN (" . implode(',', $sort_order['payment']) . ")";

		// echo $sql;

		$result = $wpdb->query( $sql );


		$a	=	self::$payment_package->refesh_order();

		wp_send_json ($a);

	}

	public function change_currency () {
		$this->check_permission ();

		$response	=	ET_Payment::set_currency ($_REQUEST['new_value']);
		wp_send_json ( array ('success' => $response) );
	}

	public function add_currency () {
		$this->check_permission ();
	
		$text	=	$_POST['text'];
		$code	=	$_POST['code'];
		$icon	=	$_POST['icon'];
		$align	=	$_POST['align'];
		
		$response 	=	array('success' => false);
		
		if( $text != '' && $code != '' && $icon != '' && ($align =='left' || $align=='right')) {
			$new_cur	=	array (
				'alt'	=>	$text,
				'label' =>	$code,
				'icon'	=>	$icon,
				'align'	=>	$align,
				'code'	=>	$code
			);
			
			self::$options->add_currency ($code, $new_cur);
			ET_Payment::set_currency($code);
			$response['success']	=	true;
		} 

		wp_send_json( $response );
	}

	public function menu_view ($args) {
		$sub_section = '';
	?>
		<div class="et-main-header">
			<div id='page_title' class="title font-quicksand"><?php echo $args->menu_title ?></div>
			<div class="desc"><?php _e("Manage how your classified engine looks and feels", ET_DOMAIN); ?></div>
		</div>
		<style>.et-main-main .desc .form .form-item span.notice {color : #E0040F;}</style>
		<div class="et-main-content">
			<div class="et-main-left">
				<ul class="et-menu-content inner-menu">
					<li id="general_session">
						<a href="#section/setting-general" menu-data="general" class="section-link <?php if ( $sub_section == '' || $sub_section == 'general') echo 'active'  ?>">
							<span class="icon" data-icon="y"></span><?php _e("General",ET_DOMAIN);?>
						</a>
					</li>
					<li id="branding_section">
						<a href="#section/customize-branding" menu-data="branding" class="section-link <?php if ($sub_section == 'branding') echo 'active' ?>">
							<span class="icon" data-icon="b"></span><?php _e("Branding",ET_DOMAIN);?>
						</a>
					</li>
					<li id="link_section">
						<a href="#section/setting-ad" menu-data="ad" class="section-link <?php if ($sub_section == 'ad') echo 'active' ?>">
							<span class="icon" data-icon="l"></span><?php _e("Ads",ET_DOMAIN);?>
						</a>
					</li>

					<li>
						<a id="social_section" href="#section/setting-social" menu-data="ad" class="section-link <?php if ($sub_section == 'social') echo 'active' ?>">
							<span class="icon" data-icon="B"> </span><?php _e("Social",ET_DOMAIN);?>
						</a>
					</li>

					<li>
						<a href="#section/setting-payment"  menu-data="payment" class="section-link <?php if ($sub_section == 'payment') echo 'active' ?>">
							<span class="icon" data-icon="%"></span><?php _e("Payment",ET_DOMAIN);?>
						</a>
					</li>
					<li>
						<a href="#section/setting-mail-template"  menu-data="mail-template" class="section-link <?php if ($sub_section == 'mail-template') echo 'active' ?>">
							<span class="icon" data-icon="M"></span><?php _e("Mailing",ET_DOMAIN);?>
						</a>
					</li>
					<li>
						<a href="#section/setting-language" menu-data="language" class="section-link <?php if ($sub_section == 'language') echo 'active' ?>">
							<span class="icon" data-icon="G"></span><?php _e("Language",ET_DOMAIN);?>
						</a>
					</li>
					
					<li>
						<a href="#section/setting-update" menu-data="update" class="section-link <?php if ($sub_section == 'update') echo 'active' ?>">
							<span class="icon" data-icon="~"></span><?php _e("Update",ET_DOMAIN);?>
						</a>
					</li>
				</ul>
			</div>
			<?php
			// add value static to display html 
			
			?>
			<div class="settings-content">
				<?php require_once 'settings-general.php';?>
				<?php require_once 'settings-language.php';?>
				<?php require_once 'settings-ad.php';?>
				<?php require_once 'settings-social.php';?>
				<?php require_once 'settings-payment.php';?>
				<?php require_once 'settings-mail-template.php' ?>
				<?php require_once 'settings-branding.php'  ?> 
				<?php require_once 'setting-update.php'  ?> 
			</div>
		</div>
	<?php 
	}
	/*
	/ action et-change-branding
	*/

	function et_change_branding(){		
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
			if ( !check_ajax_referer( $imgType . '_et_uploader', '_ajax_nonce', false ) ){
				$res['msg']	= __('Security error!', ET_DOMAIN );
			}
			elseif(isset($_FILES[$fileID])){
				
				$file_extend = 	array(
									'jpg|jpeg|jpe'	=> 'image/jpeg',
									'gif'			=> 'image/gif',
									'png'			=> 'image/png',
									'bmp'			=> 'image/bmp',
									'tif|tiff'		=> 'image/tiff',

								);
				if($fileID == 'mobile_icon')
					$file_extend['ico'] = 'image/x-icon';
			
				// handle file upload
				$attach_id	=	et_process_file_upload( $_FILES[$fileID], 0, 0, $file_extend );

				if ( !is_wp_error($attach_id) ){

					try {
						$attach_data	= et_get_attachment_data($attach_id);
							$general_opts	= new CE_Options();
							$setter			= 'set_' . $imgType;
						// save this setting to theme options
							 $general_opts->$setter($attach_id);						
							

						$res	= array(
							'success'	=> true,
							'msg'		=> __('Branding image has been uploaded successfully', ET_DOMAIN ),
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
		wp_send_json($res);
	}

	// change language
	function et_ajax_change_language () {
		
		$response	=	et_change_language ($_POST['new_value']) ;

		wp_send_json($response);
	}

}

function et_get_revenue($within = 0, $status="publish"){
	global $et_global;
	// fetch revenue from server cached
	$revenue = get_transient( $et_global['db_prefix'] . 'revenue');

	// if revenue was cached in server, get it directly from database
	if ( $revenue == false || empty($revenue[$within]) ){
		$revenue[$within] = et_refresh_revenue($within, $status);
	}
	return $revenue[$within];
}

function et_refresh_revenue($within = 0, $status="publish"){
	global $wpdb, $et_global;

	$now 	= strtotime('now');
	$range 	= date('Y-m-d H:i:s', $now - $within);
	$range_sql = $within == 0 ? "" : "AND post_date >= '{$range}'";

	$sql = "SELECT ROUND(SUM(meta_value) , 2) AS revenue FROM {$wpdb->postmeta} meta 
			INNER JOIN {$wpdb->posts} AS posts ON posts.ID = meta.post_id 
			WHERE meta_key = '{$et_global['db_prefix']}order_total' {$range_sql} AND posts.post_status = '".$status."'";

	$revenue 	= $wpdb->get_var($sql);

	$new_revenue 			= get_transient( $et_global['db_prefix'] . 'revenue');
	$new_revenue[$within] 	= empty($revenue) ? $revenue : 0;

	set_transient($et_global['db_prefix'] . 'revenue', $new_revenue, 3600);

	return floatval($revenue);
}

function data_icon ( $data , $type = 'text' ) {
    if( $data == '' )
        echo '!';
    else {
    	if($type == 'text') echo 3;
    	if($type == 'link') {
    		$validator	=	new ET_Validator();
    		if($validator->validate('link', $data))  echo 3;
    		else echo '!';
    	}
    	if($type == 'email') {
    		$validator	=	new ET_Validator();
    		if($validator->validate('email', $data))  echo 3;
    		else echo '!';
    	}
    }
}