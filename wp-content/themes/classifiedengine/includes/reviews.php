<?php
abstract class ET_Comment {
	public static $instance = null;
	//abstract static function get_instance ();
}

class CE_Review extends ET_Comment {

	public static function get_instance () {
		if(self::$instance === null ) {
			self::$instance = new CE_Review();
		}
		return self::$instance;
	}

	public function insert ( $args ) {
		global $current_user, $user_ID;

		// current user can not review for himself
		if( is_wp_error( $post	=	get_post ( $args['comment_post_ID']) ) ) return $post;
		if( $post->post_author == $user_ID ) return new WP_Error('review_self' , __("You cannot review yourself.", ET_DOMAIN));
		
		// review comment not too fast, should after 3 or 5 minute to post next review
		$comments	=	get_comments( array ( 'comment_type' => '', 'author_email' => $current_user->user_email , 'number' => 1 ) );

		if(!empty($comments)) { // check latest comment
			$comment	=	$comments[0];
			$date		=	$comment->comment_date_gmt;
			$ago		=	time() -strtotime($date);
			//return error if comment to fast
			if($ago < (2*60) ) return new WP_Error('fast_review' , __("Please wait 2 minutes after each review submission.", ET_DOMAIN));
		}


		$comments	=	get_comments( array ( 'meta_key' => 'attitude', 'post_id' => $post->ID, 'comment_type' => 'review', 'author_email' => $current_user->user_email , 'number' => 1 ) );
		if( !empty($comments) ) {
			return new WP_Error('fast_review' , __("You have already reviewed this seller about this classified.", ET_DOMAIN));
		}

		unset($args['comment_author']);
		// try add review
		try {

			$browser	=	getBrowser();				
			$commentdata = wp_parse_args( 	$args , 
											array( 	'comment_post_ID' => '',
					    							'comment_author' 		=> $current_user->user_login,
					    							'comment_author_email' 	=> $current_user->user_email,
					    							'comment_author_url' 	=> 'http://',
					    							'comment_content' 		=> 'content here',
					    							'comment_type' 			=> 'review',
					    							'comment_parent' 		=> 0,
					    							'user_id' 				=> $user_ID,
					    							'comment_author_IP' 	=> $_SERVER['REMOTE_ADDR'],
					    							'comment_agent' 		=> $browser['userAgent'],
					    							//'comment_date' => time(),
					    							'comment_approved' => 0,
					    							'attitude'			=> 'pos'
					    						) 
				);

			/**
			 * insert review to database
			*/
			$comment	=	wp_insert_comment( $commentdata );

			if( !is_wp_error($comment) ) {
				update_comment_meta( $comment, 'attitude', $commentdata['attitude'] );
				return $comment;	
			}else {
				throw new Exception($comment->get_error_message());				
			}
			
		}catch(Exception $e) {
			return new WP_Error('add_review_error' , $e->getMessage() );
		}
	}

	// public function update ( $args ) {

	// }

	// public static function query ( $args ) {
	// 	$args		=	array ( 'status' => 'approve', 'comment_type' => 'review' , 'post_author' => $seller->ID , 'meta_key' => 'attitude' , 'number' => $number ,'offset' => $offset );
	// }

	// public static function sync ( $args ) {

	// }

	public static function get_reviews ( $seller, $type , $paged ) {

		/**
		 * set number of review and offset
		*/
		$number	=	get_option( 'posts_per_page' );
		$offset	=	0;

		if( $paged ) {
			$offset =	$number*( $paged-1);
		}
		if( get_query_var( 'paged' ) ) {
			$offset =	$number*( get_query_var( 'paged' )-1);
		}

		/**
		 * build param to get reviews
		*/
		$args		=	array ( 'status' => 'approve', 'comment_type' => 'review' , 'post_author' => $seller->ID , 'meta_key' => 'attitude' , 'number' => $number ,'offset' => $offset );

		if( $type == 'pos' ) {
			$args['meta_value']	=	'pos';
		}elseif( $type == 'neg') {
			$args['meta_value']	=	'neg';
		}else {
			$type = 'all';	
		}
		global $user_ID;
		// $seller		=	ET_Seller::convert ($author);
		if( current_user_can( 'manage_options' ) || $user_ID == $seller->ID ){			
			$args['status'] = '';			
		}


		/**
		 * query review by request
		*/
		$reviews    =   get_comments( $args );


		/**
		 * get total comment count
		*/
		$total_args	=	$args;
		unset($total_args['number']	);
		unset($total_args['offset']	);
		$total_comment	=	get_comments( $total_args );
		$total_page		=	ceil( count( $total_comment ) / $number );

		return array ( 'reviews' => $reviews ,  'total_page' => $total_page, 'type' => $type );

	}

}

Class CE_ReviewAction extends CE_Ajax {

	static $ce_event = array (
		'ce-load-more-reviews'
	);

	static $ce_priv_event = array (
		'et-review-sync'
	);

	public function __construct() {
		parent::__construct (self::$ce_event, self::$ce_priv_event );
		$this->add_action( 'ce_seller_bar' , 'add_review' );
		$this->add_action( 'init' , 'review_init' );
	}


	/**
	 * sync review (create)
	*/
	function review_sync (){
		global $user_ID, $current_user;
		$args	=	$_POST['content'];
		/**
		 * validate data
		*/
		if( empty($args['comment_content']) || empty($args['comment_post_ID']) ) {
			wp_send_json( array ('success' => false , 'msg' => __("Please fill in required field.", ET_DOMAIN)) );
		}


		$review		=	CE_Review::get_instance ();
		$comment	=	$review->insert($args);


		if( !is_wp_error($comment) )  {
			wp_send_json( array ('success' => true , 'msg' => __("Your review has been submitted.", ET_DOMAIN)) );	
		} else {
			wp_send_json( array ('success' => false , 'msg' =>  $comment->get_error_message() ) );	
		}
		
	}

	/**
	 * render review count in seller bar
	*/
	function add_review ($seller) {
		global $user_ID;
		$args		=	array ( 'meta_key' => 'attitude' ,  'comment_type' => 'review' , 'post_author' => $seller->ID );
		
		$pos_args	=	$args;
		$pos_args['meta_value']	=	'pos';
		$neg_args	=	$args;
		$neg_args['meta_value']	=	'neg';

		$neg_args['status'] 	= 'approve';
		$pos_args['status'] 	= 'approve';
		// $seller		=	ET_Seller::convert ($author);
		if(current_user_can( 'manage_options' ) || $user_ID == $seller->ID ){			
			$neg_args['status'] = '';
			$pos_args['status'] = '';
		}
		$neg_reviews    =   get_comments( $neg_args );
		$pos_reviews    =   get_comments( $pos_args );

		$post_author_url	=	get_author_posts_url( $seller->ID);	
		global $wp_rewrite;
		$pos_link 		= '';
		$neg_link 		= '';

		if ( $wp_rewrite->using_permalinks() ){	
			$pos_link 		=  $post_author_url.'review/pos' ;
			$neg_link 		=  $post_author_url.'review/neg' ;
		} else {
			$pos_link 		= add_query_arg( array('review' =>'pos'),$post_author_url );
			$neg_link 		= add_query_arg( array('review' => 'neg'),$post_author_url );
		}

	?>
		<div class="text-vote">               
	        <span class="link-plus">
	        	<a class="link-plus" href="<?php echo $pos_link; ?>" >
	        		<span class="icon-vote">+</span>&nbsp;&nbsp;<?php printf(__("Positive: %d", ET_DOMAIN) , count($pos_reviews) ) ?>
	        	</a>
	        </span>	
        	<span class="link-minus">
        		<a class="link-minus" href="<?php echo $neg_link; ?>" >
            		<span class="icon-minus">-</span>&nbsp;&nbsp;<?php printf(__("Negative: %d", ET_DOMAIN) , count($neg_reviews) ) ?>
            	</a>
        	</span>
			<?php if( $user_ID != $seller->ID ) {  ?>
		        <a href="#" class="link-submit-review submit-review" data-id="<?php echo $seller->ID; ?>" data-name="<?php echo $seller->display_name; ?>" >
		        	<i class="fa fa-comment"></i>&nbsp;&nbsp;<?php _e("Post Review", ET_DOMAIN); ?>
		        </a>
	        <?php } ?>
	        <input type="hidden" value="<?php _e("You must login to post review.", ET_DOMAIN); ?>" class="review-message" />
	    </div>
	<?php 
	}


	// init hook for review
	function review_init () {
		/**
	     * add review to end point
	    */
	    global $wp_rewrite;
	    $review		=	'review';
	    add_rewrite_rule( $wp_rewrite->author_base.'/([^/]+)/$review(/(.*))?/?$', 'index.php?author_name=$matches[1]&$review=$matches[2]', 'top' );

	    add_rewrite_endpoint( $review, EP_AUTHORS | EP_PAGES );
	}

	function ce_load_more_reviews () {
		$response	=	array ('success' => true );
		extract($_REQUEST);

		if( empty($reviews) ) {
			$reviews	=	'all';	
		}else {
			$type_arr	=	explode( '/', $reviews);
			if(!empty( $type_arr )) {
				if($type_arr[0] != 'page') {
					$type	=	$type_arr[0];
				}else {
					$type	=	'all';
				}	
			}
		}
		$paged ++;

		$author		=	get_user_by( 'login', $author );
		$seller 	=	ET_Seller::convert($author);
		$reviews	=	CE_Review::get_reviews ( $seller, $type , $paged );

		if( empty($reviews['reviews']) ) {
			$response['success']	=	false;
		}else {
			foreach ($reviews['reviews'] as $key => $value) {
				$reviews['reviews'][$key]->ads_link			=	($value->user_id !== '' ) ? get_author_posts_url( $value->user_id ) : '#' ;
				$reviews['reviews'][$key]->avatar			=	get_avatar( $value->user_id ) ;
				$reviews['reviews'][$key]->display_name		=	get_user_meta( $value->user_id , 'display_name' , true );
				$reviews['reviews'][$key]->date_ago			=	CE_Ads::process_post_date($value->comment_date_gmt);
				$reviews['reviews'][$key]->attitude			=	get_comment_meta($value->comment_ID, 'attitude', true);
			}

			$response['data']		=	$reviews['reviews'];
			$response['the_last']	=	false;
			$response['max_page'] 	=	$reviews['total_page'];

			/**
			 * total page == current page : clear
			*/
			if( $response['max_page'] == $paged ) {
				$response['max_page']	=	false;
				$response['the_last']	=	true;
			}
		}

		wp_send_json( $response );
	}

}

new CE_ReviewAction();