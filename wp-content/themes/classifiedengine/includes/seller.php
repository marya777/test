<?php 
/**
 * Basic User class
 */
if(!class_exists('ET_User')) {
class ET_User extends ET_Base{

	/**
	 * Insert a member
	 */
	static $instance = null;

	public function __construct(){

	}

	static public function init(){
		$instance = self::get_instance();
	}

	// public function get_instance(){
	// 	if ( self::$instance == null){
	// 		self::$instance = new ET_User();
	// 	} 
	// 	return self::$instance;
	// }

	public function _insert($data){

		$args 	= self::_filter_meta($data);
		/**
		 * validate user name
		*/
		if( !$data['user_login'] || !preg_match('/^[a-z\d_]{2,20}$/i', $data['user_login']) ) {
			return new WP_Error( 'username_invalid', __("Username only lowercase letters (a-z) and numbers are allowed.", ET_DOMAIN) );
		}

		$result = wp_insert_user( $args['data'] );

		if ($result != false && !is_wp_error( $result )){
			if(isset($args['meta']))
				foreach ($args['meta'] as $key => $value) {
					update_user_meta( $result, $key, $value );
				}

			// people can modify here
			do_action('et_insert_user', $result);
		}
		return $result;
	}

	public function _update($data){
		try {
			if (empty($data['ID']))
				throw new Exception(__('Member not found', ET_DOMAIN), 404);

			// filter meta and default data
			if(isset($data['role']))
				unset($data['role']);
			if(isset($data['user_login']))
				unset($data['user_login']);

			$args = $this->_filter_meta($data);

			// update database
			$result = wp_update_user( $args['data'] );

			if ($result != false || !is_wp_error( $result ) ){
				if(!empty($args['meta'])){
					foreach ($args['meta'] as $key => $value) {
						update_user_meta( $result, $key, $value );
					}
				}
				/**
				 * check location id and update it
				*/
				if(isset($data['user_location_id'])) {
					$term 	= 	get_term_by('id', $data['user_location_id'], 'ad_location');
					if($term){
						update_user_meta( $result, 'user_location', $term->name );
						update_user_meta( $result, 'user_location_id', $term->term_id );
					}
				}

				// people can modify here
				do_action('et_update_user', $result);
			}

			return $result;
		} catch (Exception $e) {
			return new WP_Error($e->getCode(), $e->getMessage());
		}
	}

	protected function _delete($id, $reassign = 'novalue'){
		if ( wp_delete_user( $id, $reassign ) ){
			do_action( 'et_delete_user' );
		}
	}

	// add more meta data into default userdata
	protected function _convert($data){
		$result = $data;

		if (!empty($result->data)){
			foreach ($this->meta_data as $key) {
				$result->data->$key = get_user_meta( $data->ID, $key, true );
			}
			$result->data->id	=	$result->data->ID;
			unset($result->data->user_pass);
		}
		return $result->data;
	}

	protected function _filter_meta($data){
		$return = array();
		foreach ($data as $key => $value) {
			if (in_array($key, $this->meta_data))
				$return['meta'][$key] = $value;
			else
				$return['data'][$key] = $value;
		}
		return $return;
	}
}
}

/**
 * extends WP_User_Query to override prepare function
*/
class CE_User_Query extends WP_User_Query {
	/**
	 * Prepare the query variables.
	 *
	 * @since 3.1.0
	 *
	 * @param string|array $args Optional. The query variables.
	 */
	function prepare_query( $query = array() ) {
		global $wpdb;

		if ( empty( $this->query_vars ) || ! empty( $query ) ) {
			$this->query_limit = null;
			$this->query_vars = wp_parse_args( $query, array(
				'blog_id' => $GLOBALS['blog_id'],
				'role' => '',
				'meta_key' => '',
				'meta_value' => '',
				'meta_compare' => '',
				'include' => array(),
				'exclude' => array(),
				'search' => '',
				'search_columns' => array(),
				'orderby' => 'login',
				'order' => 'ASC',
				'offset' => '',
				'number' => '',
				'count_total' => true,
				'fields' => 'all',
				'who' => ''
			) );
		}

		$qv =& $this->query_vars;

		if ( is_array( $qv['fields'] ) ) {
			$qv['fields'] = array_unique( $qv['fields'] );

			$this->query_fields = array();
			foreach ( $qv['fields'] as $field ) {
				$field = 'ID' === $field ? 'ID' : sanitize_key( $field );
				$this->query_fields[] = "$wpdb->users.$field";
			}
			$this->query_fields = implode( ',', $this->query_fields );
		} elseif ( 'all' == $qv['fields'] ) {
			$this->query_fields = "$wpdb->users.*";
		} else {
			$this->query_fields = "$wpdb->users.ID";
		}

		if ( isset( $qv['count_total'] ) && $qv['count_total'] )
			$this->query_fields = 'SQL_CALC_FOUND_ROWS ' . $this->query_fields;

		$this->query_from = "FROM $wpdb->users";
		$this->query_where = "WHERE 1=1";

		// sorting
		if ( isset( $qv['orderby'] ) ) {
			if ( in_array( $qv['orderby'], array('nicename', 'email', 'url', 'registered') ) ) {
				$orderby = 'user_' . $qv['orderby'];
			} elseif ( in_array( $qv['orderby'], array('user_nicename', 'user_email', 'user_url', 'user_registered') ) ) {
				$orderby = $qv['orderby'];
			} elseif ( 'name' == $qv['orderby'] || 'display_name' == $qv['orderby'] ) {
				$orderby = 'display_name';
			} elseif ( 'post_count' == $qv['orderby'] ) {
				// todo: avoid the JOIN
				$where = get_posts_by_author_sql(CE_AD_POSTTYPE);
				$this->query_from .= " LEFT OUTER JOIN (
					SELECT post_author, COUNT(*) as post_count
					FROM $wpdb->posts
					$where
					GROUP BY post_author
				) p ON ({$wpdb->users}.ID = p.post_author)
				";
				$orderby = 'post_count';
			} elseif ( 'ID' == $qv['orderby'] || 'id' == $qv['orderby'] ) {
				$orderby = 'ID';
			} elseif ( 'meta_value' == $qv['orderby'] ) {
				$orderby = "$wpdb->usermeta.meta_value";
			} else {
				$orderby = 'user_login';
			}
		}

		if ( empty( $orderby ) )
			$orderby = 'user_login';

		$qv['order'] = isset( $qv['order'] ) ? strtoupper( $qv['order'] ) : '';
		if ( 'ASC' == $qv['order'] )
			$order = 'ASC';
		else
			$order = 'DESC';
		$this->query_orderby = "ORDER BY $orderby $order";

		// limit
		if ( isset( $qv['number'] ) && $qv['number'] ) {
			if ( $qv['offset'] )
				$this->query_limit = $wpdb->prepare("LIMIT %d, %d", $qv['offset'], $qv['number']);
			else
				$this->query_limit = $wpdb->prepare("LIMIT %d", $qv['number']);
		}

		$search = '';
		if ( isset( $qv['search'] ) )
			$search = trim( $qv['search'] );

		if ( $search ) {
			$leading_wild = ( ltrim($search, '*') != $search );
			$trailing_wild = ( rtrim($search, '*') != $search );
			if ( $leading_wild && $trailing_wild )
				$wild = 'both';
			elseif ( $leading_wild )
				$wild = 'leading';
			elseif ( $trailing_wild )
				$wild = 'trailing';
			else
				$wild = false;
			if ( $wild )
				$search = trim($search, '*');

			$search_columns = array();
			if ( $qv['search_columns'] )
				$search_columns = array_intersect( $qv['search_columns'], array( 'ID', 'user_login', 'user_email', 'user_url', 'user_nicename' ) );
			if ( ! $search_columns ) {
				if ( false !== strpos( $search, '@') )
					$search_columns = array('user_email');
				elseif ( is_numeric($search) )
					$search_columns = array('user_login', 'ID');
				elseif ( preg_match('|^https?://|', $search) && ! ( is_multisite() && wp_is_large_network( 'users' ) ) )
					$search_columns = array('user_url');
				else
					$search_columns = array('user_login', 'user_nicename');
			}

			/**
			 * Filter the columns to search in a WP_User_Query search.
			 *
			 * The default columns depend on the search term, and include 'user_email',
			 * 'user_login', 'ID', 'user_url', and 'user_nicename'.
			 *
			 * @since 3.6.0
			 *
			 * @param array         $search_columns Array of column names to be searched.
			 * @param string        $search         Text being searched.
			 * @param WP_User_Query $this           The current WP_User_Query instance.
			 */
			$search_columns = apply_filters( 'user_search_columns', $search_columns, $search, $this );

			$this->query_where .= $this->get_search_sql( $search, $search_columns, $wild );
		}

		$blog_id = 0;
		if ( isset( $qv['blog_id'] ) )
			$blog_id = absint( $qv['blog_id'] );

		if ( isset( $qv['who'] ) && 'authors' == $qv['who'] && $blog_id ) {
			$qv['meta_key'] = $wpdb->get_blog_prefix( $blog_id ) . 'user_level';
			$qv['meta_value'] = 0;
			$qv['meta_compare'] = '!=';
			$qv['blog_id'] = $blog_id = 0; // Prevent extra meta query
		}

		$role = '';
		if ( isset( $qv['role'] ) )
			$role = trim( $qv['role'] );

		if ( $blog_id && ( $role || is_multisite() ) ) {
			$cap_meta_query = array();
			$cap_meta_query['key'] = $wpdb->get_blog_prefix( $blog_id ) . 'capabilities';

			if ( $role ) {
				$cap_meta_query['value'] = '"' . $role . '"';
				$cap_meta_query['compare'] = 'like';
			}

			if ( empty( $qv['meta_query'] ) || ! in_array( $cap_meta_query, $qv['meta_query'], true ) ) {
				$qv['meta_query'][] = $cap_meta_query;
			}
		}

		$meta_query = new WP_Meta_Query();
		$meta_query->parse_query_vars( $qv );

		if ( !empty( $meta_query->queries ) ) {
			$clauses = $meta_query->get_sql( 'user', $wpdb->users, 'ID', $this );
			$this->query_from .= $clauses['join'];
			$this->query_where .= $clauses['where'];

			if ( 'OR' == $meta_query->relation )
				$this->query_fields = 'DISTINCT ' . $this->query_fields;
		}

		if ( ! empty( $qv['include'] ) ) {
			$ids = implode( ',', wp_parse_id_list( $qv['include'] ) );
			$this->query_where .= " AND $wpdb->users.ID IN ($ids)";
		} elseif ( ! empty( $qv['exclude'] ) ) {
			$ids = implode( ',', wp_parse_id_list( $qv['exclude'] ) );
			$this->query_where .= " AND $wpdb->users.ID NOT IN ($ids)";
		}

		/**
		 * Fires after the WP_User_Query has been parsed, and before
		 * the query is executed.
		 *
		 * The passed WP_User_Query object contains SQL parts formed
		 * from parsing the given query.
		 *
		 * @since 3.1.0
		 *
		 * @param WP_User_Query $this The current WP_User_Query instance,
		 *                            passed by reference.
		 */
		do_action_ref_array( 'pre_user_query', array( &$this ) );
	}
}

/**
 * Retrieve list of users matching criteria.
 *
 * @since 3.1.0
 * @uses $wpdb
 * @uses CE_User_Query See for default arguments and information.
 *
 * @param array $args Optional.
 * @return array List of users.
 */
function ce_get_users( $args = array() ) {

	$args = wp_parse_args( $args );
	$args['count_total'] = false;

	$user_search = new CE_User_Query($args);

	return (array) $user_search->get_results();
}

/**
 * get number of ads posted by a seller
*/
function count_ads_by_seller( $userid ) {
	global $wpdb;
	$post_type = CE_AD_POSTTYPE;

	$where = get_posts_by_author_sql( $post_type, true, $userid );
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts $where" );

  	return apply_filters( 'get_usernumposts', $count, $userid );
}

/**
 * Classified Engine Seller Class
 * 
*/
class ET_Seller extends ET_User {
	static $role	=	'seller';
	static $name	=	'Seller';

	static $instance = null;

	static $seller_url	=	'seller';

	protected	$meta_data;

	public static function get_instance() {
		if(self::$instance == null) {
			self::$instance = new ET_Seller();
		}
		return self::$instance;
	}

	public static function register () {
		global $wp_roles;
		/**
		 * register wp_role seller
		*/
		// remove_role( 'seller' );
		if( !isset( $wp_roles->roles['seller'] ) || !get_option('refresh_cap',false) ) {
			$role	=	add_role (
				self::$role, 
				self::$name , 
				array(
					'edit_published_'.CE_AD_POSTTYPE.'s' => true , 
					'upload_files' => true , 
					'create_'.CE_AD_POSTTYPE => true , 
					'publish_'.CE_AD_POSTTYPE.'s' => true , 
					'delete_published_'.CE_AD_POSTTYPE.'s' => true , 
					'edit_'.CE_AD_POSTTYPE => true, 
					'delete_'.CE_AD_POSTTYPE => true , 
					'edit_'.CE_AD_POSTTYPE.'s' => true, 
					'delete_'.CE_AD_POSTTYPE.'s' => true , 
					'read' => true 
				)
			);
			update_option( 'refresh_cap', true );
		}

		//add_filter('author_link', array( 'ET_Seller', 'custom_author_link'));
		
		//$seller_url	=	apply_filters('ce_filter_seller_url', self::$seller_url );
		$seller_url	=	apply_filters('ce_filter_seller_url', self::$seller_url );

		// $rules = get_option( 'rewrite_rules' );
		// if ( ! isset( $rules['seller/([^/]+)/?$'] ) ) {
		// 	add_rewrite_rule('^'.$seller_url.'/([^/]+)/page/?([0-9]{1,})/?$','index.php?author_name=$matches[1]&paged=$matches[2]','top');

		// 	add_rewrite_rule($seller_url.'/([^/]+)/?$','index.php?author_name=$matches[1]','top');
		// 	add_rewrite_rule($seller_url.'/([^/]+)/page/?([0-9]{1,})/?$','index.php?author_name=$matches[1]&paged=$matches[2]','top');
		// }
		
		$page = et_get_page_template('page-min');
		if ( empty($page) )  {
			et_get_page_link ('min');
			$page = et_get_page_template('page-min');
		}
		add_rewrite_rule( $page->post_name . '/([a-zA-Z_]{1,})$', 'index.php?page_id=' . $page->ID . '&b=$matches[1]&f=$matches[2]', 'top');

	
		/**
		 * process seller data after paid success
		*/
		add_action('ce_payment_process' , array('ET_Seller','ce_payment_process') , 10, 4);
		
		add_action('wp_ajax_nopriv_et_request_reset_password',array('ET_Seller','et_request_reset_password') );

		add_action('wp_ajax_nopriv_et_reset_password',array('ET_Seller','ajax_user_reset_password') );
		add_action('wp_ajax_et_reset_password',array('ET_Seller','ajax_user_reset_password') );

		add_action( 'pre_user_query', array('ET_Seller','extended_user_search'),10,2 );
	}

	/**
	 * custom author link change author to seller
	 */
	public static function custom_author_link($link) {
		global $wp_rewrite;
		if ( !$wp_rewrite->using_permalinks() ){
			//$link = preg_replace('/\?author=/','\?seller=', $link);
		} else {
			$seller_url	=	apply_filters('ce_filter_seller_url', self::$seller_url );
			$link = preg_replace('/\/author\/([^\/]+\/*)$/', '/'.$seller_url.'/$1', $link);
		}
		return $link;
	}

	public function __construct () {
		/**
		 * user meta data config
		*/
		$this->meta_data = array (
			'et_avatar',
			'et_avatar_url',
			'user_hide_info',
			'user_mobile',
			'user_facebook',
			'user_twitter',
			'user_gplus',
			'user_location',
			'user_location_id',
			'et_phone',
			'et_address',
			'et_used_free_plan'
		);

		

	}	

	/**
	 * list all seller with args 
	 * order by : name, registered , post_countget
	*/
	public static function list_sellers ( $args ) {
		$defaults = array(
			'orderby' => 'post_count', 'order' => 'DESC', 'number' => '',
			'optioncount' => false, 'exclude_admin' => true, 'fields' => 'all',
			// 'show_fullname' => false, 'hide_empty' => true,
			// 'feed' => '', 'feed_image' => '', 'feed_type' => '', 'echo' => true,
			// 'style' => 'list', 'html' => true ,
			'role' => self::$role ,'number' => get_option( 'posts_per_page', 10 ) , 'count_total' => true 
		);

		$args	=	wp_parse_args( $args, $defaults   );

		$user	=	ce_get_users( $args );

		return $user;

	}

	/**
	 * conver seller to object with all metadata
	*/
	public static function convert ($user) {
		global $wp_locale;
		if(self::$instance == null) {
			self::$instance = new ET_Seller();
		}

		$seller	=	self::$instance->_convert( $user );
		$seller->profile_url	=	et_get_page_link ('account-profile');

		$seller->joined_date	=	sprintf( __("Joined on %s", ET_DOMAIN) ,  date_i18n( get_option( 'date_format' ) , strtotime($user->user_registered) ) ) ;

		global $user_ID;
		if( $user_ID == $seller->ID ) {
			$seller->package_data	=	self::get_package_data($user_ID);
			$seller->free_plan_used = 	self::get_used_free_plan();
		}else {
			$seller->package_data	=	array();
			$seller->free_plan_used = 	self::get_used_free_plan($user_ID);
		}

		if(is_admin()) {
			$seller->avatar	=	get_avatar( $seller->ID );
		}

		return apply_filters( 'ce_convert_seller', $seller);
	}

	/**
	 * insert user, with role seller
	 * data should have user_email, user_pass
	*/
	public static function insert ($data) {

		$instance				=	self::get_instance();
		$data['role']			=	self::$role;

		do_action( 'ce_before_insert_seller' , $data);

		$user			=	$instance->_insert($data);
		if( !is_wp_error( $user ) ) {
			et_login( $data['user_login'], $data['user_pass'] , true);
		}

		do_action( 'ce_after_insert_seller' , $user , $data );
		return $user;
	}
	/**
	 * update user data
	 * field: object meta data, user email, user pass
	*/
	public static function update($data){
		global $current_user;

		if( isset( $data['renew_password'] ) ) {

			if($data['renew_password'] != $data['user_pass']) // password missmatch
				return new WP_Error ('false', __("Retype password is not equal.", ET_DOMAIN));

			$old_pass 	= $data['old_password'];
			$aut		= wp_authenticate( $current_user->user_login, $old_pass );
			if(is_wp_error($aut)){ // check authentication
				unset($data['renew_password']);
				unset($data['old_password']);
				return new WP_Error ('false', __("The password entered do not match.", ET_DOMAIN));
			}

		} elseif (isset($data['user_email'])) {
			$exist_email 	= false;
			$email 			= $data['user_email'];

			/**
			 * new email is not the same with old email, check new email exist or nott
			*/
			if($email != $current_user->user_email)
				$exist_email = email_exists($email);

			if($exist_email) // email exist return false
				return new WP_Error('false',__("This email is already used. Please enter a new email.",ET_DOMAIN));
		}

		// don't allow upgrade from seller to admin
		if(!current_user_can('remove_users'))
		 	unset($data['role']);
		// don't allow change user login.
		if(isset($data['user_login']))
			unset($data['user_login']);

		$instance	=	self::get_instance ();
		$user		=	$instance->_update($data);
		/**
		 * do action after update seller
		*/
		do_action( 'ce_after_update_seller' , $user , $data );
		return $instance->_convert( get_userdata($user) );

	}

	/**
	 * check user is using package to post ad
	*/
	public static function check_use_package ($package ,$user_id = 0) {
		if(!$user_id) {
			global $user_ID;
			$user_id	=	$user_ID;
		}

		$used_package	=	self::get_package_data($user_id);
		if(isset( $used_package[$package] ) && $used_package[$package]['qty'] > 0 ) return true;
		else
			return false;

	}
	/**
	 * get all used package data
	*/
	public static function get_package_data ($user_id) {
		$used_package	=	get_user_meta($user_id, 'ce_seller_packages_data', true ); 
		return $used_package;
	}
	/**
	 * add user package data when post ad
	*/
	public static function add_package_data ( $package, $user_id = 0 ) {

		if(!$user_id) {
			global $user_ID;
			$user_id	=	$user_ID;
		}
		$used_package	=	self::get_package_data($user_id);

		$packageObj		=	ET_PaymentPackage::get($package);
		if(is_wp_error($packageObj)) return $packageObj;

		$qty	=	(int)$packageObj->et_number_posts - 1;

		$used_package[$package]	=	array( 'ID' => $package, 'qty' => $qty );

		update_user_meta( $user_id, 'ce_seller_packages_data', $used_package );

		return true;

	}
	/**
	 * update user package data after post an ad
	*/
	public static function update_package_data ($package, $user_id = 0 ) {
		if(!$user_id) {
			global $user_ID;
			$user_id	=	$user_ID;
		}

		$used_package	=	self::get_package_data($user_id);

		$qty			=	(int)$used_package[$package]['qty'] - 1;

		if($qty == 0) { // remove user current order for the package
			$group		=	self::get_current_order ($user_id);
			unset($group[$package]);
			update_user_meta ( $user_id, 'ce_seller_current_order' , $group );
		}

		$used_package[$package]['qty']	=	$qty;

		update_user_meta( $user_id, 'ce_seller_packages_data', $used_package );

	}

	/**
	 * return the order id user paid for the package
	*/
	public static function get_current_order( $user_id, $package_id = '' ) {
		$order	=	get_user_meta($user_id, 'ce_seller_current_order', true );
		if($package_id == '') 
			return $order;
		else 
			return ( isset($order[$package_id]) ? $order[$package_id] : '' );
	}

	/**
	 *  update order id user paid for package
	*/
	public static function update_current_order( $user_id, $package , $order_id ) {
		$group		=	self::get_current_order ($user_id) ;

		$group[$package]	=	$order_id;

		return	update_user_meta ($user_id, 'ce_seller_current_order' , $group );
	}

	/**
	 * action process payment update seller order data
	*/
	public static function ce_payment_process ($payment_return, $order, $payment_type, $session ) {
		if( !$payment_return['ACK'] ) return ;
		if( $payment_type == 'free' ) return;


		if( $payment_type == 'usePackage' ) {
			return;
		}

		global $user_ID;
		$order_pay	=	$order->get_order_data();

		self::update_current_order ( $order_pay['payer'],  $order_pay['payment_package'], $session['order_id'] );
		self::add_package_data ( $order_pay['payment_package'],$order_pay['payer'] );
		CE_Mailing::send_receipt( $order_pay['payer'] , $order_pay );

	}

	/**
	 * get user avatar
	*/
	public function get_avatar ( $id_or_email , $size ) {
		$default	=	'';

		if ( is_numeric($id_or_email) ) {
			$id = (int) $id_or_email;
			$user = get_userdata($id);
		} else {
			//$user	=	get_user_by('email', $id_or_email );
			$user	=	false;
		}

		$email	=	'';
		if( $user ) {

			$img	=	get_user_meta ($user->ID, 'et_avatar_url' , true);

			if($img == '') {

				$img	=	 $this->update_avatar ($user->ID);

			}

			if($img != '') return $img;

			$email	=	$user->user_email;

			$email_hash = md5( strtolower( trim( $email ) ) );

			if ( is_ssl() ) {
				$host = 'https://secure.gravatar.com';
			} else {
				if ( !empty($email) )
					$host = sprintf( "http://%d.gravatar.com", ( hexdec( $email_hash[0] ) % 2 ) );
				else
					$host = 'http://0.gravatar.com';
			}

			$out = "$host/avatar/";
			$out .= $email_hash;
			$out .= '?s='.$size;
			$out .= '&amp;d=' . urlencode( $default );

			$rating = get_option('avatar_rating');
			if ( !empty( $rating ) )
				$out .= "&amp;r={$rating}";
			$default	=	$out;
		}

		return $default;
	}

	public static function update_used_free_plan ($user_ID = '') {
		global $user_ID;
		if( $user_ID ) {
			$number	=	self::get_used_free_plan();
			$number =   $number +1 ;
			update_user_meta( $user_ID, 'ce_used_free_plan_number', $number );
			return $number;
		}
	}

	public static function get_used_free_plan ($user_ID = '') {
		global $user_ID;
		return get_user_meta( $user_ID, 'ce_used_free_plan_number', true );
	}

	/**
	 * set user avatar
	*/
	public static function set_avatar($user_id,$att_id) {
		$seller	=	self::get_instance();
		update_user_meta( $user_id, 'et_avatar', $att_id );

		return $seller->update_avatar ($user_id);

	}

	/**
	 *
	*/
	function update_avatar ($user_id) {
		$avatar	=	get_user_meta( $user_id, 'et_avatar', true );
		if($avatar != '') {
			$img	=	wp_get_attachment_image_src( $avatar, 'thumbnail' );
			$img	=	$img[0];

			update_user_meta( $user_id, 'et_avatar_url' , $img );

			return $img;
		}
	}

	/**
	 * seller profile in admin backend
	*/
	public static function show_seller_profile($profile){
		$instance = ET_Seller::get_instance();
		global $ce_config;
		wp_localize_script( 'ce', 'et_globals', array(
			'homeURL'		=> home_url(),
			'page_template'	=> get_page_template_slug(),
			'ajaxURL' 		=> admin_url( 'admin-ajax.php' ),
			'logoutURL'		=> wp_logout_url( home_url() ) ,
			'imgURL'		=> TEMPLATEURL.'/img',
			'loading'		=> __("Loading", ET_DOMAIN),
			'loadingImg' 		=> '<img class="loading loading-wheel" src="'.TEMPLATEURL . '/img/loading.gif" alt="'.__('Loading...', ET_DOMAIN).'">',
			'loadingTxt' 		=> __('Loading...', ET_DOMAIN),
			'loadingFinish' 	=> '<span class="icon loading" data-icon="3"></span>',
			'plupload_config'	=> array(
				'max_file_size' 		=> '3mb',
				'url' 					=> admin_url('admin-ajax.php'),
				'flash_swf_url' 		=> includes_url('js/plupload/plupload.flash.swf'),
				'silverlight_xap_url'	=> includes_url('js/plupload/plupload.silverlight.xap'),
				'filters' 				=> array( array( 'title' => __('Image Files',ET_DOMAIN), 'extensions' => 'jpg,jpeg,gif,png' ) )
			),
			'require_fields'	=> __("Please complete required fields.", ET_DOMAIN),
			'save'				=> __("Save", ET_DOMAIN),
			'ce_config'			=> $ce_config
		) );

		$instance->add_script('profile-backend',TEMPLATEURL.'/js/profile-backend.js',array ('jquery' ,'underscore', 'backbone' , 'plupload-all', 'ce') , '1.0' , true );
		echo'<h3>'.__('Additional Profile',ET_DOMAIN).'</h3>';
		echo '<table class="form-table">';
	?>
		<tr>
			<th><label for="description"><?php _e('Phone',ET_DOMAIN);?></label></th>
			<td><input type="text" class="regular-text code"  name="et_phone" value="<?php echo $profile->et_phone ?>" /><br />
			<span class="description"><?php _e('Phone number of seller',ET_DOMAIN);?></span></td>
		</tr>
		<tr>
			<?php
			$location_id 	= $profile->user_location_id;				              		
      		$args 		= array('taxonomy'=>'ad_location',
      			'selected'	=>$location_id,
      			'depth' 	=> 3,
      			'name' 		=>'user_location_id',
      			'hide_empty'=> false);      		
      		?>
			<th><label for="description"><?php _e('Locations',ET_DOMAIN);?></label></th>
			<td><?php wp_dropdown_categories($args ); ?></td>
		</tr>	

		<tr>
			<th><label for="description"><?php _e('Address',ET_DOMAIN);?></label></th>
			<td><textarea cols=30 rows=5 class="regular-text code"  name="et_address" ><?php echo $profile->et_address ?></textarea><br />
			<span class="description"><?php _e('Address of seller',ET_DOMAIN);?></span></td>
		</tr>

		<tr>
			<th><label for="description"><?php _e('Avatar',ET_DOMAIN);?></label></th>
			<td> 
				<div id="profile_thumb_container">
					<div id="profile_thumb_thumbnail" class="image avatar-thumbs">
						<?php echo get_avatar( $profile->ID , 150 );?>
					</div>	
					<div class="input-file upload-profile">
						<div class="left clearfix" style="clear:both; float:left;">									
							<span id="<?php echo wp_create_nonce( 'user_avatar_et_uploader' ); ?>" class="et_ajaxnonce"></span>
							<span id="profile_thumb_browse_button" class="bg-grey-button button btn-button" style="z-index: 0;">
							<?php _e("Browse files...", ET_DOMAIN); ?>	<span data-icon="o" class="icon"></span>
							</span>									
						</div>										
					</div>
					<input type="hidden" name="profile_id" id="profile_id" value="<?php echo $profile->ID;?>" />
				</div>
				<p></p>
				<p><br /><span class="description"><?php _e('Avatar of sellers.',ET_DOMAIN);?></span></p>
			</td>
		</tr>		

	<?php
		do_action('et_add_backend_seller_profile',$profile);
		
		echo '</table>';
		echo '<style>';
		echo '.avatar-thumbs img{ max-width:150px;} .loading-img{width:150px; height:150px; background-repeat:no-repeat; background-position:center center;}';
		echo'</style>';
	}

	/**
	 * callback function to update seller profile in backend
	*/
	public static function update_seller_profile($user_id){
		
		if ( !current_user_can( 'edit_user', $user_id ) ){			
			return false;
		}		
		$_POST['id'] = $user_id;
		$_POST['ID'] = $user_id;
		if(!current_user_can('remove_users'))
		 	unset($_POST['role']);
		
		$instance	=	self::get_instance ();
		$user		=	$instance->_update($_POST);
		
	}
	/**
	 *	 
	*/
	public static function list_ad_by_seller($args){
		$args = wp_parse_args($args,array('post_type'=>CE_AD_POSTTYPE,'post_status'=>'publish'));
		return CE_Ads::query($args);
	}

	/*
	 * seller forgot password
	*/
	public static function et_request_reset_password(){

		$result = et_retrieve_password();

		if ( is_wp_error($result) ){
		$response = array(
			'success' 	=> false,
			'code' 		=> 400,
			'msg' 		=> $result->get_error_message(),
			'data' 		=> array(
				'redirect_url' => home_url()
				)
			);
		} else {
			$response = array(
				'success' 	=> true,
				'code' 		=> 200,
				'msg' 		=> __('Please check your email inbox to reset password.', ET_DOMAIN),
				'data' 		=> array(
					'redirect_url' => home_url()
					)
				);
		}
		wp_send_json($response);
	}

	/**
	 * ajax reset pass
	*/
	public static function ajax_user_reset_password(){
		try {
			if ( empty($_REQUEST['user_login']) )
				throw new Exception( __("This user is not found.", ET_DOMAIN) );
			if ( empty($_REQUEST['user_key']) )
				throw new Exception( __("Invalid Key", ET_DOMAIN) );
			if ( empty($_REQUEST['user_pass']) )
				throw new Exception( __("Please enter your new password", ET_DOMAIN) );

			// validate activation key

			$validate_result = check_password_reset_key($_REQUEST['user_key'], $_REQUEST['user_login']);
			if ( is_wp_error($validate_result) ){
				throw new Exception( $validate_result->get_error_message() );
			}

			// do reset password
			$user = get_user_by('login', $_REQUEST['user_login']);
			$reset_result = reset_password($user, $_REQUEST['user_pass']);

			if ( is_wp_error($reset_result) ){
				throw new Exception( $reset_result->get_error_message() );
			}
			else {
				$response = array(
					'success' 	=> true,
					'code' 		=> 200,
					'msg' 		=> __('Your password has been changed. Please log in again.', ET_DOMAIN),
					'data' 		=> array(
						'redirect_url' => home_url()
						)
				);
			}
		} catch (Exception $e) {
			$response = array(
				'success' 	=> false,
				'code' 		=> 400,
				'msg' 		=> $e->getMessage(),
				'data' 		=> array(
					'redirect_url' => home_url()
					)
				);
		}
		wp_send_json($response);
	}
	/**
	 * add filter query search user
	**/
	public static function extended_user_search( $user_query ) {
			if($user_query->query_vars['role'] == 'seller'){
				if ( $user_query->query_vars['search'] ){
					$search = trim( $user_query->query_vars['search']);
					$user_query ->query_where = str_replace("user_nicename LIKE '".$search."'","user_nicename LIKE '%".$search."%' OR display_name LIKE '%".$search."%'", $user_query->query_where);
					$user_query ->query_where = str_replace("user_login LIKE '".$search."'","user_login LIKE '%".$search."%'", $user_query->query_where);

				}
			}
	}

	public static function package_or_free ( $package_id , $ad ) {

		$response		= 	array ('success' => false);
		$use_package	=	ET_Seller::check_use_package( $package_id );
		$package		=	ET_PaymentPackage::get( $package_id );

		if($use_package) {
			et_write_session ( 'ad_id' , $ad->ID ) ;
			$response['success']	=	true;
			$response['url']	= et_get_page_link ('process-payment' , array('paymentType' => 'usePackage') );
			return $response;
		}

		$regular_price = CE_ET_PRICE;
		if($package->$regular_price == 0 ) {
			et_write_session ( 'ad_id' ,  $ad->ID ) ;
			$response['success']	=	true;
			$response['url']	= et_get_page_link ('process-payment' , array('paymentType' => 'free'));
			return $response;

		}

		return $response;
	}

	public static function ce_limit_free_plan ($package) {
		// check and limit seller user free plan
		$response	=	array ('success' => false );
		$regular_price = CE_ET_PRICE;
		if( $package && $package->$regular_price == 0
			&& get_theme_mod( 'ce_limit_free_plan' , '' ) 
			//&& !current_user_can( 'manage_options' ) 
		) {
			/**
			 * update number of free plan seller used
			*/

			$number	=	ET_Seller::update_used_free_plan();
			if( $number > get_theme_mod( 'ce_limit_free_plan' , '' ) ) {

				$response['success'] 	= true;
				$response['msg'] 		= __("You have reached the maximum number of Free posts. Please select another plan.", ET_DOMAIN);

				return $response;
			}

		}
		return $response;
	}


}
// end ET_Seller
function ce_get_sellers ($args = array()) {

	return ET_Seller::list_sellers ($args);
}

function et_count_sellers_by_time( $time = 259200 ){
		global $wpdb;

		$from = date('Y-m-d h-i-s', strtotime('now') - $time );
		$fromsql = $time == 0 ? "" : " AND `user`.user_registered > '{$from}' ";

		$sql = "SELECT COUNT(ID) FROM {$wpdb->users} `user`
			INNER JOIN {$wpdb->usermeta} as `meta` ON `meta`.user_id = `user`.ID AND `meta`.meta_key = '{$wpdb->prefix}capabilities'
			WHERE `meta`.meta_value LIKE '%seller%' {$fromsql} ";

		$result = $wpdb->get_var($sql);
		return (int)$result;
}

if(!function_exists('ce_seller_packages_data')){
	function ce_seller_packages_data ($user_ID = '') {

		if(!$user_ID) {
			global $user_ID;
		}

		$orders			=	ET_Seller::get_current_order($user_ID);
		$package_data	=	ET_Seller::get_package_data($user_ID);

		if(!empty($package_data)){
			foreach ($package_data as $key => $value) {

				$status =  get_post_status( $value['ID'] );

				if( $value['qty'] > 0 && $status == 'publish' ) {

					$package	=	ET_PaymentPackage::get($value['ID']);
					if(!$package || is_wp_error( $package ) ) continue;
					$order		=	$orders[$value['ID']];
					$status	=	get_post_status( $order );

					?>
					<div class="widget-area user_payment_status">
						<span class="arrow-right"></span>
						<p>
						<?php 
							if($status == 'publish')
								printf(__("You purchased package <strong>%s</strong> and have %d ad/s left.", ET_DOMAIN), $package->post_title , $value['qty'] ); 
							if( $status == 'pending' )
								printf(__("You purchased package <strong>%s</strong> and have %d ad/s left. Your posted ad is pending until payment.", ET_DOMAIN), $package->post_title , $value['qty'] );
						?>
						</p>
				  	</div>
				  	<?php 
				}
			}
		}
		do_action( 'after_packge_data_sidebar' );
	}
}
