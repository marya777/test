<?php 
class ET_PaymentPackage extends ET_PostType {
	CONST POST_TYPE = 'payment_package';

	public static function get_instance () {
		if ( self::$instance == null){
			self::$instance = new ET_PaymentPackage();
		} 
		return self::$instance;
	}

	public static function register () {
		register_post_type( self::POST_TYPE, array(
			'labels' => array(
			    'name' => 'Package',
			    'singular_name' => 'Packages',
			    'add_new' => 'Packaged New',
			    'add_new_item' => 'Packaged New Packages',
			    'edit_item' => 'Edit Packages',
			    'new_item' => 'New Packages',
			    'all_items' => 'All Packages',
			    'view_item' => 'View Package',
			    'search_items' => 'Search Packages',
			    'not_found' =>  'No packages found',
			    'not_found_in_trash' => 'No Packages found in Trash', 
			    'parent_item_colon' => '',
			    'menu_name' => 'Packages'
			),
		    'public' => false,
		    'publicly_queryable' => true,
		    'show_ui' => false, 
		    'show_in_menu' => false, 
		    'query_var' => true,
		    'rewrite' => array( 'slug' => 'packages' ),
		    'capability_type' => 'post',
		    'capabilities' => array(
		    	'publish_posts' => 'publish_packages',
			    'edit_posts' => 'edit_packages',
			    'edit_others_posts' => 'edit_others_packages',
			    'delete_posts' => 'delete_packages',
			    'delete_others_posts' => 'delete_others_packages',
			    'repackage_private_posts' => 'repackage_private_packages',
			    'edit_post' => 'edit_package',
			    'delete_post' => 'delete_package',
			    'read_post' => 'read_ackages'
		    	),
		    'has_archive' => 'packages', 
		    'hierarchical' => false,
		    'menu_position' => null,
		    'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields' )
		) );
	}

	public function __construct(){
		$this->name = self::POST_TYPE;
		$this->meta_data	=	array (CE_ET_PRICE, 'et_duration' , 'et_number_posts', ET_FEATURED);
	}

	function insert ($args) {

		$args['post_title']		=	$args['title'];
		$args['post_status']	=	'publish';
		$args['post_content']	=	$args['et_description'];

		return $this->convert( get_post($this->_insert($args)) , false );

	}

	function update ($args) {

		$args['post_title']		=	$args['title'];
		$args['post_status']	=	'publish';
		$args['post_content']	=	$args['et_description'];

		return $this->convert( get_post($this->_update($args)) , false );
	}

	function convert ($post) {
		$post	=	$this->_convert($post , false );

		// temporary convert pricing and featured
		$regular_price = CE_ET_PRICE;
		if(!isset($post->_regular_price) || !$post->_regular_price) {
			$post->_regular_price = get_post_meta( $post->ID, 'et_price', true );
		}

		$et_featured = ET_FEATURED;
		if(!isset($post->_et_featured) || !$post->_et_featured) {
			$post->_et_featured = get_post_meta( $post->ID, 'et_featured', true );
		}

		$post->et_featured = $post->$et_featured;
		$post->et_price = $post->$regular_price;

		$post->backend_text =	sprintf( __('%s for %s days', ET_DOMAIN), et_get_price_format($post->$regular_price), $post->et_duration );
		return $post;
	}

	function sync ($args) {

		$request	=	$args;

		switch ($request['method']) {
			case 'add':
				$response	=	$this->insert($request['content']);
				break;
			case 'delete':
				$response	=	$this->_delete($request['content']['ID']);
				break;
			case 'update':
				$response	=	$this->update($request['content']);
				break;
			default:
				$response	=	false;	
				break;
		}

		delete_transient( 'et_payment_plans' );
		return $response;
	}

	function get_all_plans () {
		$cache	=	get_transient( 'et_payment_plans');
		
		if( $cache && false ) {
			return $cache;
		} else {
			$plans	=	self::query();
			set_transient( 'et_payment_plans', $plans, 15*24*3600 );
			return $plans;
		}
		//wp_reset_query();
	}

	

	public static function query () {
		$args 	=	array (
				'post_type'			=> self::POST_TYPE,
				'post_status'		=> 'publish',
				'orderby' 			=> 'menu_order',
				'posts_per_page'	=> -1
			);

		$plans	=	get_posts($args);
		
		$return	=	array ();


		foreach ($plans as $key => $plan) {
			$return[$plan->ID]	=	(array)self::get_instance()->convert( $plan );
		}
		usort( $return, "et_package_cmp"  );
		wp_reset_query();
		return $return;
	}

	public function refesh_order () {
		$plans 		= 	query_posts(array(
			'post_type' 	=> self::POST_TYPE,
			'numberposts' 	=> -1,
			'orderby' 		=> 'menu_order date',
			'post_status'	=> 'publish'
		));

		$cache  	= array();
		foreach ($plans as $plan) {
			$cache[$plan->ID] =  (array)$this->convert ($plan);
		}
		usort( $cache, "et_package_cmp"  );		

		set_transient( 'et_payment_plans', $cache, 15*24*3600 );
		return $cache;
	}

	public static function get ($id, $raw = false) {
		$instance	=	self::get_instance();
		$post		=	 get_post($id);
		
		if(!$post || is_wp_error($post)) {
			delete_transient( 'et_payment_plans');
			return new WP_Error ('post_type_not_correct', __("This is not payment package object", ET_DOMAIN));
		}
			

		$post		=	$instance->convert($post);
		/**
		 * check post type
		*/
		if( is_wp_error($post) || !isset($post->post_type) || $post->post_type!== self::POST_TYPE ) 
			return new WP_Error ('post_type_not_correct', __("This is not payment package object", ET_DOMAIN));

		return $post;
	}

}
/**
 * helper function to get all payment plans
*/
function et_get_payment_plans () {
	$payment	=	ET_PaymentPackage::get_instance();
	return $payment->get_all_plans ();
}

function et_package_cmp($a, $b)
{
    if ($a['menu_order'] == $b['menu_order']) {
        return 0;
    }
    return ($a['menu_order'] < $b['menu_order'] ) ? -1 : 1;
}