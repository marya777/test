<?php
class ET_AdminPayments extends ET_AdminMenuItem {

	function __construct(){
		parent::__construct('et-payments',  array(
			'menu_title'	=> __('Payments', ET_DOMAIN),
			'page_title' 	=> __('PAYMENTS', ET_DOMAIN),
			'callback'   	=> array($this, 'menu_view'),
			'slug'			=> 'et-payments',
			'page_subtitle'	=> __('ClassifiedEngine Payments', ET_DOMAIN),
			'pos' 			=> 11,
			'icon_class'	=> 'icon-payment'
		));
		$this->add_ajax('et-load-payments','load_payments',true,false);
	}
	
	function on_add_scripts() {
		$this->add_script( 'et-icon_class-seller', TEMPLATEURL.'/js/admin/payments.js', array('jquery','jquery-ui-sortable',  'underscore', 'backbone', 'ce') );
	}

	function on_add_styles() {
		$this->add_existed_style('admin.css');
	}

	public function menu_view ($args) {
		global $wpdb;		
		$count 				= et_count_posts_by_time('order');

		$revenue 			= et_get_revenue(30*24*60*60);
		$pending_revenue 	= et_get_revenue(30*24*60*60, 'pending');
		$total				=	0;
		foreach ($count as $value) { 
			$total += $value ;
		}
		$total	=	number_format($total,0,'.','.');
		?>
		<div class="et-main-header">
			
			<div class="title font-quicksand"><?php _e('Payments', ET_DOMAIN) ?></div>
			<div id="payments_point" class="desc">
				<?php _e('Overview of your financial transactions', ET_DOMAIN) ?>
			</div>			
			<ul id="et-head-statistics" class="et-head-statistics">
        			<li>
        				<div class="icon-overview orange">
        					<div class="icon" data-icon="^"></div>
        				</div>
        				<div class="info">
	        				<div class="number font-quicksand orange bg-none"><?php echo $count->pending ?></div>
	        				<div class="type"><?php _e("Pending",ET_DOMAIN);?></div>
        				</div>
        			</li>
        			<li>
        				<div class="icon-overview grey">
        					<div class="icon" data-icon="%"></div>
        				</div>
        				<div class="info">
	        				<div class="number font-quicksand grey bg-none"><?php echo et_get_price_format($pending_revenue, 'sup'); ?></div>
	        				<div class="type"><?php _e("Unpaid",ET_DOMAIN);?></div>
	        			</div>
        			</li>
        			<li>
        				<div class="icon-overview green">
        					<div class="icon" data-icon="%"></div>
        				</div>        				
        				<div class="info">
	        				<div class="number font-quicksand green bg-none"><?php echo et_get_price_format($revenue, 'sup'); ?></div>
	        				<div class="type"><?php _e("Revenue Made",ET_DOMAIN);?></div>
        				</div>
        			</li>
        		</ul>
		</div>


		

		<div class="et-main-content" id="setting-payments">
			<div class="search-box">

		        	<input type="text" placeholder="<?php _e('Search ads...',ET_DOMAIN);?>" value="" class="bg-grey-input search-ads">

		        	<span data-icon="s" class="icon"></span>
		    </div>
			<div id="et-main-left_point" class="et-main-left">
				<?php
					$payment_gate	=	ET_Payment::get_support_payment_gateway();
									
					$orders			=	ET_AdOrder::get_orders(array ( 
											'post_status' => array ('pending','publish','draft'),
											'payment' => array_keys( $payment_gate )														
										)
									);

					$currency		=	ET_Payment::get_currency_list();

				?>
				<ul class="et-menu-content inner-menu processor-list">        			
	    			<li><a href="all" rel="all" class="active"><?php _e('All', ET_DOMAIN) ?></a></li>
        			<?php foreach ($payment_gate as $key => $value) { ?>
        			<li><a rel="<?php echo $key?>" href="#<?php echo $key ?>"><?php echo $value['label']?></a></li>
        			<?php }?>

				</ul>
			</div>
			<div class="settings-content">
				<div class="et-main-main clearfix inner-content" id="setting-general">		
					<div class="title font-quicksand"><?php _e('List of Payments',ET_DOMAIN);?></div>
						<div class="desc">
							<div class="form no-margin no-padding no-background container-payment">
								
								<ul class="list-inner list-payment">
									<?php	
									$plans   = et_get_payment_plans();								
									if($orders->have_posts()) {
							        	while($orders->have_posts()) {
							        	 $orders->the_post(); 
							        		global $post;

							        		$order		=	new ET_AdOrder(get_the_ID ());
							        		$order_data	=	$order->get_order_data();	
							        		$products	=	$order_data['products'];

							        		$ad_id		=	array_pop($products);
							        		$ad 		= 	get_post($ad_id['ID']);
							        		
							        		if(!isset( $currency[$order_data['currency']])) {
							        			$icon	=	$order_data['currency'];
							        		} else {
							        			$icon	=	$currency[$order_data['currency']]['icon'];
							        		}						        		
							        	
							        		?>
						        			<li>
						        				<div class="method"><?php echo $payment_gate[$order_data['payment']]['label']?></div>
						        				<div class="content">

						        					

						        					<span class="price font-quicksand">
						        						<?php echo et_get_price_format($order_data['total'], 'sup'); ?>
						        					</span>
						        					<?php 
						        					if( $ad ) { ?>
									        					<?php if( $ad->post_status == 'pending' ) { ?> 
									        						<a title="<?php _e("Pending", ET_DOMAIN); ?>" class="color-red error" href="#"><span class="icon" data-icon="!"></span></a>
									        					<?php } elseif($ad->post_status == 'publish') { ?> 
									        						<a title="<?php _e("Confirmed", ET_DOMAIN); ?>" class="color-green" href="#"><span class="icon" data-icon="2"></span></a>
									        					<?php }else {
									        					?> 
									        						<a title="<?php _e("Failed", ET_DOMAIN); ?>" class="color" style="color :grey;" href="#"><span class="icon" data-icon="*"></span></a>
									        					<?php 
									        					} ?>	
																<a target="_blank" href="<?php echo get_permalink($ad->ID) ?>" class="ad ad-name"><?php echo get_the_title($ad->ID); ?></a>
																<?php
																echo '(' .$order_data['payment']. ')' ;
									        					 _e(' by ', ET_DOMAIN);						        						
							        							?> 
							        							<a target="_blank" href="<?php echo get_author_posts_url($ad->post_author, $author_nicename = '') ?>" class="company"><?php echo get_the_author_meta('display_name',$ad->post_author) ?></a>
													
														<?php 
													} else { 
														$seller_name	=	'<a target="_blank" href="'.get_author_posts_url($post->post_author).'" class="company">'.get_the_author_meta('display_name',$post->post_author) .'</a>'; ?>
														<span><?php printf (__("This ad has been deleted by %s", ET_DOMAIN) , $seller_name ); ?></span>
														<?php 
													} ?>
							 						
						        				</div>
						        			</li>
						        			<?php
						        			}
						        		} else {
						        			_e('There are no payments yet.',ET_DOMAIN);
						        		}
									
									?>
		        	        		
	        			        </ul>
	        			        <?php if($orders->max_num_pages > 1) {?>
					        	<button class="et-button btn-button load-more" id="load-more">
					        		<?php _e("Load more",ET_DOMAIN);?>
								</button>
								<?php }?>

							</div>
						</div>
					</div>

			</div>
		</div>

	<?php
	}


	/*
	 * action et-load-payments
	 * load and search payments
	*/
	public static function load_payments(){

		//et_ad_order = {post_id,metaky,metavalue }{id_ad,et_ad_order,'id_order'} 372 	et_ad_order 	373
		$s			=	isset($_POST['search']) ? $_POST['search'] : '';
		$gateway 	=	$_POST['payment'];
		$page 		=	isset($_POST['page']) ? $_POST['page'] : 1;
		$order_ids	=	array ();

		$response 	= 	array (
		        			'data'		=>	'',
		        			'success'	=>	 false,
		        			'msg'		=>	__('There are no payments yet.', ET_DOMAIN)
	        			);

		if(!empty($s)) {

			$query_search 	=	new WP_Query (	array (
									'post_status' 	=> array('pending','draft', 'publish', 'archive', 'expired','trash'),
									's'				=>	$s,
									'post_type' 	=> CE_AD_POSTTYPE,
									'posts_per_page' => -1,
								)) ;

			while ($query_search->have_posts()) {

				$query_search->the_post ();
				$order_id 	= get_post_meta( get_the_ID(), 'et_ad_order', true)	;
				$order 		= get_post($order_id);
				if($order)
					$order_ids	=	array_merge($order_ids, (array)$order->ID );
			}

			if (empty($order_ids))
				wp_send_json($response);

		}

		$payment_gate	=	ET_Payment::get_support_payment_gateway();
		$currency		=	ET_Payment::get_currency_list();

		if( $gateway == "" || $gateway == 'all')  {
			$gateway 	=	array_keys($payment_gate);
		}
		if(!empty($order_ids))
			$args	=	array (
				'payment'		=>	$gateway,
				'post_status'	=>	array ( 'pending', 'publish','draft'),
				'post__in'		=>	$order_ids,
				'paged' 		=>  $page 
			);
		else 
			$args	=	array (
			'payment'		=>	$gateway,
			'post_status'	=>	array ( 'pending', 'publish','draft'),
			'paged' 		=>  $page
		);

		$query		=	ET_AdOrder::get_orders ($args);
		$result 	= 	array();
		$item 		= 	array();
		$plans   	= 	et_get_payment_plans();

		if($query->have_posts()) {

			while($query->have_posts()) {

				$query->the_post();
				global $post;

        		$order		=	new ET_AdOrder(get_the_ID ());
        		$order_data	=	$order->get_order_data();
				$order_note = $order->get_order_note();
        		$products 	= 	$order_data['products'];
        		$ad_id		=	array_pop($products);
				$ad 		= 	get_post($ad_id['ID']);

        		if(!isset( $currency[$order_data['currency']])) {
        			$icon	=	$order_data['currency'];
        		} else {
        			$icon	=	$currency[$order_data['currency']]['icon'];
        		}
        		$post_status = isset($ad->post_status) ? $ad->post_status : '';

        		if($post_status == 'pending') { 
					$icon = '<a title="'.__("Pending", ET_DOMAIN).'" class="color-red error" href="#"><span class="icon" data-icon="!"></span></a>';
				} elseif($post_status == 'publish') {
					$icon = '<a title="'. __("Confirmed", ET_DOMAIN) .'" class="color-green" href="#"><span class="icon" data-icon="2"></span></a>';
				 }else
					$icon = "<a title='".__("Failed", ET_DOMAIN)."' class='color' style='color :grey;'' href='#''><span class='icon' data-icon='*'></span></a>";

				if($ad){
					$item 				= CE_Ads::convert($ad);
					$item->method 		= $payment_gate[$order_data['payment']]['label'];
					$item->link			= get_permalink($ad->ID);
					$item->price 		= et_get_price_format($order_data['total'], 'sup');
					$item->author_url 	= get_author_posts_url($ad->ID);
					$item->author_name 	= get_the_author_meta('display_name',$ad->post_author);
					$item->plan 		= $order_data['payment'];
					$item->status = $order_data["status"];
					$item->note = $order_note ? ". Note :" . $order_note : '';
					$item->icon 		= $icon;

				} else {
					$item = array();

						$seller_name	='<a target="_blank" href="'.get_author_posts_url($post->post_author).'" class="company">'.get_the_author_meta('display_name',$post->post_author) .'</a>';
						$html 	='<li class="seller-item"><div class="method">'.$payment_gate[$order_data['payment']]['label'].'</div><div class="content>';
 						$html   .= $icon;
						$html 	.= '<span class="price font-quicksand">';
						$html 	.= et_get_price_format($order_data['total'], 'sup');
						$html   .='</span><span>';
						$html 	.= sprintf (__(" This ad has been deleted by %s", ET_DOMAIN) , $seller_name );
						$html 	.='</span>';
						$html 	.='</div> </li>';
					$item['html'] = $html;

				}


				$result[] 			= $item;

	        }

	        $response = array (
				'data'		=>	$result,
				'page' 		=> $page, 
        		'success'	=>	 true,
        		'msg'		=>	'',
        		'total'		=>  $query->max_num_pages
			);
		}
		wp_send_json($response);
	}
}