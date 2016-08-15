<?php
class ET_AdminSellers extends ET_AdminMenuItem {

	function __construct(){
		parent::__construct('et-sellers',  array(
			'menu_title'	=> __('Sellers', ET_DOMAIN),
			'page_title' 	=> __('SELLERS', ET_DOMAIN),
			'callback'   	=> array($this, 'menu_view'),
			'slug'			=> 'et-sellers',
			'page_subtitle'	=> __('ClassifiedEngine Sellers', ET_DOMAIN),
			'pos' 			=> 7,
			'icon_class'	=> 'icon-sellers'
		));
		$this->add_ajax('ce-backend-search-sellers','ce_search_sellers' , true , false);
	}
	
	function on_add_scripts() {
		$this->add_script( 'et-setting-seller', TEMPLATEURL.'/js/admin/seller.js', array('jquery','jquery-ui-sortable',  'underscore', 'backbone', 'ce') );
	}

	function on_add_styles() {
		$this->add_existed_style('admin.css');
	}

	public function menu_view ($args) {
		global $wpdb;
		
		$items_per_page = apply_filters('et_sellers_per_page', 10); 

		$sellers 		= ET_Seller::list_sellers(array('number'=>$items_per_page));

		$total 			= get_users(array('role'=>'seller'));		
		?>	
		<div class="et-main-header">
			<div id="seller_point" class="title font-quicksand"><?php echo $args->menu_title ?></div>
			<div class="desc"><?php _e("Overview of registered sellers", ET_DOMAIN); ?></div>
			<ul class="et-head-statistics">
				<li>
					<div class="icon-overview">
					</div>
					<div class="info">
						<?php $count = et_count_sellers_by_time(0); ?>
						<div class="number font-quicksand"><?php echo $count; ?></div>
						<div class="type"><?php _e('Total sellers', ET_DOMAIN); ?></div>
					<div>
				</li>
				<li>
					<div class="icon-overview">
					</div>
					<div class="info">
						<?php 
						$day = getdate(time());
						$date =  $day['mday'];
						//idate('d', time()) == $date;
						?>
						<?php $count = et_count_sellers_by_time($date*24*60*60); ?>
						<div class="number font-quicksand"><?php echo $count; ?></div>
						<div class="type"><?php _e('New sellers', ET_DOMAIN); ?></div>
					<div>
				</li>
			</ul>

		</div>		

		<style>.et-main-main .desc .form .form-item span.notice {color : #E0040F;}</style>
		<div class="et-main-content" id="seller_setting">

			<div class="search-box">
				<input type="text" id="search_seller" class="bg-grey-input" placeholder="<?php _e('Search a seller...', ET_DOMAIN) ?>" />
				<span class="icon" data-icon="s"></span>
			</div>
			<script type="text/data" id="list_sellers_response">
					<?php echo json_encode($sellers) ?>
			</script>

			<div class="et-main-main no-margin clearfix overview list">
				<div class="title font-quicksand"><?php _e('All Sellers', ET_DOMAIN) ?></div>
				<ul class="list-inner list-payment seller-list">
					<?php  foreach ($sellers as $seller) {
							global $wp_query;
							query_posts('author='.$seller->ID.'&post_type='.CE_AD_POSTTYPE.'&post_status=publish&posts_per_page=-1');
						?>
						<li class="seller-item" data-id="<?php echo $seller->ID?>">

							<div class="content">
								<?php echo get_avatar( $seller->ID, '20' ); ?>
								<a href="<?php echo get_author_posts_url($seller->ID) ?>" class="ad"><?php echo $seller->display_name ?></a> 
								- <a href="<?php echo get_author_posts_url($seller->ID) ?>" class="seller"><?php printf( et_number( __('No ads', ET_DOMAIN), __('%d ad', ET_DOMAIN), __('%d ads', ET_DOMAIN), $wp_query->found_posts ), $wp_query->found_posts)  ?></a>
							</div>
						</li>
					<?php } ?>
				</ul>
				<button class="et-button btn-button load-more" <?php if ( ceil( count($total)/ $items_per_page) <= 1 ) echo 'style="display: none"' ?>>
					<?php _e('More sellers', ET_DOMAIN) ?>
				</button>
			</div>
		</div>
	<?php
	}
	/*
	*/

	/**
	*Get liset seller.
	 * function call by action ce-backend-search-sellers
	**/

	function ce_search_sellers(){
		global $wpdb;
		$data = $_REQUEST['content'];

		header( 'HTTP/1.0 200 OK' );
		header( 'Content-type: application/json' );
		try {
			$paged = isset($data['paged']) ? $data['paged'] : 1;

			$items_per_page = apply_filters('et_sellers_per_page', 10);

			$number_items   = $items_per_page * $paged;

			$args 			= array('number'=>$number_items);

			$args 			= wp_parse_args(array('search'=> !empty($data['s']) ? $data['s'] : false), $args);

			$sellers 		= ET_Seller::list_sellers($args);

			$all_sellers	= get_users(array('role'=>'seller','search'=> !empty($data['s']) ? $data['s'] : false));

			$total 			= count($all_sellers);

			foreach ($sellers as $key => $seller) {
				global $wp_query;
				query_posts('author='.$seller->ID.'&post_type='.CE_AD_POSTTYPE.'&post_status=publish&posts_per_page=-1');
				$sellers[$key]->permalink 	= get_author_posts_url($seller->ID);
				$sellers[$key]->count_text 	= sprintf( et_number( __('No ads', ET_DOMAIN), __('%d ad', ET_DOMAIN), __('%d ads', ET_DOMAIN), $wp_query->found_posts ), $wp_query->found_posts);
			}
			wp_reset_query();

			$msg = __('List sellers!',ET_DOMAIN);
			if($total == 0)
			$msg = __('Do not found sellers!',ET_DOMAIN);
			$res = array(
				'success' 	=> true,
				'msg' 		=> $msg,
				'data' 		=> array(
					'sellers'  => $sellers,
					'pagination' => array(
						'paged' 		=> $data['paged'],
						'total' 		=> $total,
						'total_page' 	=> ceil( $total / $items_per_page )
						),
					'query' 	=> $data
					)
				);
		} catch (Exception $e) {
			$res	= array(
				'success'	=> false,
				'msg'		=> $e->getMessage(), // __('There is an error occurred', ET_DOMAIN ),
				'code'		=> $e->getCode()
			);
		}

		wp_send_json($res);
	}

}