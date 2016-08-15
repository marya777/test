<?php
class CE_FreeVisitor extends ET_PaymentVisitor {
	protected $_payment_type = 'free';
	//function __construct () {}
	function setup_checkout (ET_Order $order) { /* do nothing  */}
	function do_checkout (ET_Order $order) {
		/**
		 * check session
		*/
		$session	=	et_read_session();
		$ad_id	=	isset( $session['ad_id'] ) ? $session['ad_id'] : '';

		$ad	=	CE_Ads::convert($ad_id);
		if( !is_wp_error($ad) ) {

			global $user_ID;
			$ad_package	=	$ad->et_payment_package;
			$package	=	ET_PaymentPackage::get ($ad_package);

			$regular_price = CE_ET_PRICE;
			if(is_wp_error($package ) || $package->$regular_price > 0 )  {
				return array ('ACK' => false, 'payment_type' =>  'free' , 'msg'		=> __("Invalid Payment package", ET_DOMAIN)	) ;
			}

			if( $user_ID == $ad->post_author  || current_user_can('manage_options') ) { // check permission
				$payment_return			=	array ('ACK' => true, 'payment_type' =>  'free');
				return $payment_return;
			}
		}

		return array ('ACK' => false, 'payment_type' =>  'free' , 'msg'		=> __("Invalid Ad ID", ET_DOMAIN)	) ;

	}
}
/**
 * Class CE_UsePackageVisitor
 * Process ad order when user submit by use package
*/
class CE_UsePackageVisitor extends ET_PaymentVisitor  {
	protected $_payment_type = 'use_package';
	//function __construct () {}
	function setup_checkout (ET_Order $order) { /* do nothing  */}
	function do_checkout ( ET_Order $order ) {
		/**
		 * check session
		*/
		$session	=	et_read_session();
		$ad_id		=	isset( $session['ad_id'] ) ? $session['ad_id'] : '';

		if( $ad_id ) { // ad id existed

			$ad			=	CE_Ads::convert( $ad_id );
			if(!is_wp_error($ad)) {
				/**
				 * check user is available to use selected package
				*/
				$available	=	ET_Seller::check_use_package ( $ad->et_payment_package , $ad->post_author );

				if ( $available ) { // process order data

					$payment_return		=	array ('ACK' => true, 'payment_type' =>  'usePackage');

					/**
					 * get user current order for package
					*/
					$current_order		=	ET_Seller::get_current_order($ad->post_author, $ad->et_payment_package);

					$order	=	get_post ($current_order);
					if( is_wp_error( $order ) ) {
						return array ('ACK' => false, 'payment_type' =>  'usePackage' , 'msg'	=> __("Invalid Order or package", ET_DOMAIN)	) ;
					}

					$ad_data	=	(array)$ad;

					/**
					 * update ad order
					*/
					$ad_data['et_ad_order']	=	$current_order;
					$ad_data['post_status']	=	'pending';

					if( isset($order->post_status) && $order->post_status == 'publish') {
						$ad_data['et_paid']	=	1;
						$options	=	new CE_Options();
						if(!$options->use_pending())
							$ad_data['post_status']	=	'publish';
					} else 	{
						$ad_data['et_paid']	=	0;
					}

					$ad_data['change_status']	= 'change_status';
					/**
					 * sync Ad data
					*/
					$return =	CE_Ads::update($ad_data);

					/**
					 * update seller package quantity
					*/
					ET_Seller::update_package_data ( $ad->et_payment_package, $ad->post_author );

					return $payment_return;
				}
			}

		}

		return array ('ACK' => false, 'payment_type' =>  'usePackage' , 'msg'	=> __("Invalid Ad ID", ET_DOMAIN)	) ;
	}
}

/**
 * class CE_Payment_Factory
 * generate a payment visitor to process order by $paymentType
*/
class CE_Payment_Factory extends ET_Payment_Factory {
	function __construct () {
		// dont know what i can do here
	}

	public static function createPaymentVisitor ($paymentType , $order) {

		if (class_exists("WooCommerce")) {
			$available_payment_gateways = WooCommerce::instance()->payment_gateways()->get_available_payment_gateways();
			$paymentType_lower = strtolower($paymentType);
			if (array_key_exists($paymentType_lower, $available_payment_gateways)) {
				$class = new WC_Integrate_Visitor($paymentType);
				return apply_filters('et_factory_build_payment_visitor', $class, $paymentType, $order);
			}
		}

		switch ( $paymentType ) {
			case 'CASH' : // return cash visitor 
				$class	= 	new ET_CashVisitor ($order);
				break;
			case 'GOOGLE_CHECKOUT' :
				$class	= 	new ET_GoogleVisitor($order);
				break;
			case 'PAYPAL' :
				$class	=	new ET_PaypalVisitor ($order);
				break;
			case 'AUTHORIZE' :
				$class	=	new ET_AuthorizeVisitor($order);
				break;
			case '2CHECKOUT' : 
				$class	=	new ET_2COVisitor($order);
				break;
			case 'FREE' :
				return new CE_FreeVisitor ($order);
				break;
			case 'USEPACKAGE' :
				return new CE_UsePackageVisitor ($order);
				break;
			default :
				$class = new ET_InvalidVisitor ($order);
		}
		
		return apply_filters( 'et_factory_build_payment_visitor', $class , $paymentType,  $order  );
	}
}

define ('ET_SESSION_COOKIE', '_et_session');
class ET_Session {
	protected $_session_id;
	protected $_expired_time;
	protected $_exp_variant;
	protected $_session_data;
	protected static $instance;

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	protected function __construct () {

		if ( isset( $_COOKIE[ET_SESSION_COOKIE] ) ) {
			$cookie 		= stripslashes( $_COOKIE[ET_SESSION_COOKIE] );
			$cookie_data 	= explode( '||', $cookie );

			$this->_session_id 		= $cookie_data[0];
			$this->_expired_time 	= $cookie_data[1];

			// Update the session expiration if we're past the variant time
			if ( time() > $this->_expired_time ) {
				$this->set_expiration();
				$this->_session_id = $this->regenerate_id(true);
				update_option( "_et_session_expires_{$this->_session_id}", $this->_expired_time );
			}
		} else {
			$this->_session_id = $this->generate_id();
			$this->set_expiration();
		}

		$this->read_data();

		$this->set_cookie();
	}

	public function read_data () {
		if(!get_option( "_et_session_{$this->_session_id}", '' )) return false;
		$this->_session_data = unserialize(get_option( "_et_session_{$this->_session_id}", '' ) );
		return (array)$this->_session_data;
	}

	/**
	 * Write the data from the current session to the data storage system.
	 */
	public function write_data($key, $value ) {
		$option_key = "_et_session_{$this->_session_id}";
		if ( false === get_option( $option_key ) ) {	
			$this->_session_data	=	array($key => $value );		
			add_option( "_et_session_{$this->_session_id}", serialize($this->_session_data ), '', 'no' );
			add_option( "_et_session_expires_{$this->_session_id}", $this->_expired_time, '', 'no' );
		} else {
			$this->_session_data[$key]	= $value;
			update_option( "_et_session_{$this->_session_id}", serialize($this->_session_data) );
		}
		
	}
	/**
	 * set exprire time
	*/
	protected function set_expiration() {
		$this->_exp_variant 	= time() + (int) apply_filters( 'et_session_expiration_variant', 24 * 60 );
		$this->_expired_time 	= time() + (int) apply_filters( 'et_session_expiration', 20 * 60  );
	}

	/**
	 * Set the session cookie
	 */
	protected function set_cookie() {
		setcookie( ET_SESSION_COOKIE, $this->_session_id . '||' . $this->_expired_time , $this->_expired_time, '/' );
	}

	protected function generate_id() {
		require_once( ABSPATH . 'wp-includes/class-phpass.php');
		$hasher = new PasswordHash( 8, false );

		return md5( $hasher->get_random_bytes( 32 ) );
	}

	public function regenerate_id( $delete_old = false ) {
		if ( $delete_old ) {
			delete_option( "_et_session_{$this->_session_id}" );
		}

		$this->_session_id = $this->generate_id();

		$this->set_cookie();
	}

	public function unset_session ($key = null) {
		delete_option( "_et_session_{$this->_session_id}" );
	}
}

/**
 * Clean up expired sessions by removing data and their expiration entries from
 * the WordPress options table.
 *
 * This method should never be called directly and should instead be triggered as part
 * of a scheduled task or cron ad.
 */
function et_session_cleanup() {
	global $wpdb;

	if ( defined( 'WP_SETUP_CONFIG' ) ) {
		return;
	}

	if ( ! defined( 'WP_INSTALLING' ) ) {
		$expiration_keys = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE '_et_session_expires_%'" );

		$now = time();
		$expired_sessions = array();

		foreach( $expiration_keys as $expiration ) {
			// If the session has expired
			if ( $now > intval( $expiration->option_value ) ) {
				// Get the session ID by parsing the option_name
				$session_id = substr( $expiration->option_name, 20 );

				$expired_sessions[] = $expiration->option_name;
				$expired_sessions[] = "_et_session_$session_id";
			}
		}

		// Delete all expired sessions in a single query
		if ( ! empty( $expired_sessions ) ) {
			$option_names = implode( "','", $expired_sessions );
			$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name IN ('$option_names')" );
		}
	}

	// Allow other plugins to hook in to the garbage collection process.
	do_action( 'et_session_cleanup' );
}
add_action( 'et_session_garbage_collection', 'et_session_cleanup' );

/**
 * Register the garbage collector as a twice daily event.
 */
function et_session_register_garbage_collection() {
	if ( ! wp_next_scheduled( 'et_session_garbage_collection' ) ) {
		wp_schedule_event( time(), 'twicedaily', 'et_session_garbage_collection' );
	}
}
add_action( 'wp', 'et_session_register_garbage_collection' );

function et_write_session ($key, $value) {
	$et_session	=	ET_Session::get_instance();
	return $et_session->write_data ($key, $value);
}

function et_read_session () {
	$et_session	=	ET_Session::get_instance();
	return $et_session->read_data ();
}

function et_destroy_session ($key = null) {
	$et_session	=	ET_Session::get_instance();
	$et_session->unset_session($key);
}

/* add function */
/**
 * process uploaded image: save to upload_dir & create multiple sizes & generate metadata
 * @param  [type]  $file     [the $_FILES['data_name'] in request]
 * @param  [type]  $author   [ID of the author of this attachment]
 * @param  integer $parent=0 [ID of the parent post of this attachment]
 * @param  array [$mimes] [array of supported file extensions]
 * @return [int/WP_Error]	[attachment ID if successful, or WP_Error if upload failed]
 * @author anhcv
 */
function et_process_file_upload( $file, $author=0, $parent=0, $mimes=array() ){

	global $user_ID;
	$author = ( 0 == $author || !is_numeric($author) ) ? $user_ID : $author;

	if( isset($file['name']) && $file['size'] > 0){

		// setup the overrides
		$overrides['test_form']	= false;
		if( !empty($mimes) && is_array($mimes) ){
			$overrides['mimes']	= $mimes;
		}

		// this function also check the filetype & return errors if having any
		if(!function_exists( 'wp_handle_upload' )) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}
		$uploaded_file	=	wp_handle_upload( $file, $overrides );

		//if there was an error quit early
		if ( isset( $uploaded_file['error'] )) {
			return new WP_Error( 'upload_error', $uploaded_file['error'] );
		}
		elseif(isset($uploaded_file['file'])) {

			// The wp_insert_attachment function needs the literal system path, which was passed back from wp_handle_upload
			$file_name_and_location = $uploaded_file['file'];

			// Generate a title for the image that'll be used in the media library
			$file_title_for_media_library = preg_replace('/\.[^.]+$/', '', basename($file['name']));

			$wp_upload_dir = wp_upload_dir();

			// Set up options array to add this file as an attachment
			$attachment = array(
				'guid'				=> $uploaded_file['url'],
				'post_mime_type'	=> $uploaded_file['type'],
				'post_title'		=> $file_title_for_media_library,
				'post_content'		=> '',
				'post_status'		=> 'inherit',
				'post_author'		=> $author
			);

			// Run the wp_insert_attachment function. This adds the file to the media library and generates the thumbnails. If you wanted to attch this image to a post, you could pass the post id as a third param and it'd magically happen.
			$attach_id = wp_insert_attachment( $attachment, $file_name_and_location, $parent );
			require_once(ABSPATH . "wp-admin" . '/includes/image.php');
			$attach_data = wp_generate_attachment_metadata( $attach_id, $file_name_and_location );
			wp_update_attachment_metadata($attach_id,  $attach_data);
			return $attach_id;

		} else { // wp_handle_upload returned some kind of error. the return does contain error details, so you can use it here if you want.
			return new WP_Error( 'upload_error', __( 'There was a problem with your upload.', ET_DOMAIN ) );
		}
	}
	else { // No file was passed
		return new WP_Error( 'upload_error', __( 'Where is the file?', ET_DOMAIN ) );
	}
}
/**
 * handle file upload prefilter to tracking error
*/
//remove_filter( 'wp_handle_upload_prefilter','check_upload_size' );
add_filter ( 'wp_handle_upload_prefilter', 'et_handle_upload_prefilter', 9);
function et_handle_upload_prefilter ($file) {
	if(!is_multisite()) return $file;

	if ( get_site_option( 'upload_space_check_disabled' ) )
		return $file;

	if ( $file['error'] != '0' ) // there's already an error
		return $file;

	if ( defined( 'WP_IMPORTING' ) )
		return $file;

	$space_allowed = 1048576 * get_space_allowed();
	$space_used = get_dirsize( BLOGUPLOADDIR );
	$space_left = $space_allowed - $space_used;
	$file_size = filesize( $file['tmp_name'] );
	if ( $space_left < $file_size )
		$file['error'] = sprintf( __( 'Not enough space to upload. %1$s KB needed.', ET_DOMAIN ), number_format( ($file_size - $space_left) /1024 ) );
	if ( $file_size > ( 1024 * get_site_option( 'fileupload_maxk', 1500 ) ) )
		$file['error'] = sprintf(__('This file is too big. Files must be less than %1$s KB in size.', ET_DOMAIN), get_site_option( 'fileupload_maxk', 1500 ) );
	if ( function_exists('upload_is_user_over_quota') && upload_is_user_over_quota( false ) ) {
		$file['error'] = __( 'You have used your space quota. Please delete files before uploading.',ET_DOMAIN );
	}


	// if ( $file['error'] != '0' && !isset($_POST['html-upload']) )
	// 	wp_die( $file['error'] . ' <a href="javascript:history.go(-1)">' . __( 'Back' ) . '</a>' );
	return $file;
}

/**
 * Return all sizes of an attachment
 * @param 	$attachment_id
 * @return 	an array with [key] as the size name & [value] is an array of image data in that size
 *             e.g:
 *             array(
 *             	'thumbnail'	=> array(
 *             		'src'	=> [url],
 *             		'width'	=> [width],
 *             		'height'=> [height]
 *             	)
 *             )
 * @since 1.0
 */
function et_get_attachment_data($attach_id, $size = array() ){

	// if invalid input, return false
	if (empty($attach_id) || !is_numeric($attach_id)) return false;

	$data		= array(
		'attach_id'	=> $attach_id
		);

	if(!empty($size)) {
		$all_sizes	=	$size;	
	}else {
		$all_sizes	= get_intermediate_image_sizes();
	} 
	
	foreach ($all_sizes as $size) {
		$data[$size]	= wp_get_attachment_image_src( $attach_id, $size );
	}
	return $data;
}

// update general setting



/**
 * get theme layout list
 */



/**
 * function add a widget used in a sidebar
 * @param string  $sidebar sidebar id
 * @param string $widget widget id
 * @author mr Dakachi
 */
function et_sidebar_widget ( $sidebar, $widget) {
	global  $sidebars_widgets, $wp_registered_widget_controls;

	$sidebars_widgets[$sidebar][]	=	$widget;
	wp_set_sidebars_widgets($sidebars_widgets)	;
	
}
/**
 * get widget setting data by widget option name
 * @param string $widget_options widget option name
 * return array setting
 */
function et_get_widget_setting ( $widget_options ) {
	$settings = get_option( $widget_options );
	unset($settings['_multiwidget'], $settings['__i__']);
	return $settings;
}

function et_save_widget_settings ( $widget, $all_instances) {
	$all_instances['_multiwidget'] = 1;
	update_option( $widget, $all_instances );
}

function et_count_posts_by_time($post_type, $within = 0){
	global $wpdb, $wp_post_statuses;

	$now 	= strtotime('now');
	$range 	= date('Y-m-d H:i:s', $now - $within);

	// if within is set as 0, count all post in database
	$range_sql = $within == 0 ? "" : "AND post_date >= '{$range}'";

	$sql 		= "SELECT post_status, COUNT(ID) as count FROM {$wpdb->posts} WHERE post_type = '{$post_type}' {$range_sql} GROUP BY post_status";
	$rows 		= $wpdb->get_results($sql);
	$return 	= array();
	$statuses 	= array_keys( $wp_post_statuses );

	foreach ($rows as $row) {
		$return[$row->post_status] = $row->count;
	}
	foreach ($statuses as $status) {
		if ( empty($return[$status]) ){
			$return[$status] = 0;
		}
	}

	return (object)$return;
}
function et_count_ads($within = 0){
	return et_count_posts_by_time(CE_AD_POSTTYPE, $within);
}