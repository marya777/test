<?php
/**
 * Class template for Admin menus
 */
abstract class ET_AdminMenuItem extends ET_Base{

	abstract public function menu_view($args);
	abstract public function on_add_scripts();
	abstract public function on_add_styles();

	function __construct($menu_name, $args = array()){
		parent::__construct();
		$this->menu_name = $menu_name;
		$this->menu_args = wp_parse_args( $args, array(
			'menu_title' => 'Menu title',
			'page_title' => 'Menu title',
			'slug' 			=> 'menu-slug',
			'callback' 	=> array($this, 'menu_view')
		) );

		// actions
		$this->add_action('et_admin_menu', 'add_option_page');
		$this->add_action('et_admin_enqueue_scripts-' . $this->menu_args['slug'], 'on_add_scripts');
		$this->add_action('et_admin_enqueue_styles-' . $this->menu_args['slug'], 'on_add_styles');
		
		/**
		 * add mutual script 
		*/
		$this->add_action('et_admin_enqueue_styles-' . $this->menu_args['slug'], 'add_script_ce');
	}
	

	function add_script_ce () {
		$this->add_existed_script( 'ce' );

		wp_localize_script( 'ce', 'et_globals', array(
			'ajaxURL' 	=> admin_url( 'admin-ajax.php' ),
			'logoutURL'	=> wp_logout_url( home_url() ),
			'imgURL'	=> TEMPLATEURL.'/img',
			'loading'	=> __("Loading", ET_DOMAIN),
			'confirm_delete_plan'	=> __('Are you sure you want to delete this payment plan?', ET_DOMAIN),
			'plupload_config'	=> array(
				'max_file_size' 		=> apply_filters('ce_max_file_size_upload', '3mb'),
				'url' 					=> admin_url('admin-ajax.php'),
				'flash_swf_url' 		=> includes_url('js/plupload/plupload.flash.swf'),
				'silverlight_xap_url'	=> includes_url('js/plupload/plupload.silverlight.xap'),
				'filters' 				=> array( array( 'title' => __('Image Files',ET_DOMAIN), 'extensions' => 'jpg,jpeg,gif,png' ) ),
				'msg' 					=> array('FILE_EXTENSION_ERROR' => __('File extension error. Only allow  %s file extensions.',ET_DOMAIN),
												 'FILE_SIZE_ERROR'		=> __('Max file size %s.',ET_DOMAIN),
												 'FILE_DUPLICATE_ERROR'	=> __('File already present in the queue.', ET_DOMAIN),
												 'FILE_COUNT_ERROR' 	=> __('File count error.',ET_DOMAIN),
												 'IMAGE_FORMAT_ERROR' 	=> __('Image format either wrong or not supported.',ET_DOMAIN),
												 'IMAGE_MEMORY_ERROR' 	=> __('Runtime ran out of available memory',ET_DOMAIN),
												 'HTTP_ERROR' 			=> __('Upload URL might be wrong or doesn\'t exist.',ET_DOMAIN),
												)
			),
			'loadingImg' 		=> '<img class="loading loading-wheel" src="'.TEMPLATEURL . '/img/loading.gif" alt="'.__('Loading...', ET_DOMAIN).'">',
			'loading' 		=> __('Loading', ET_DOMAIN),
			'ce_ad_cat' 		=> CE_AD_CAT,
			'regular_price' => CE_ET_PRICE,
			'_et_featured' => ET_FEATURED
		) );
	}

	public function add_option_page(){
		// default args
		et_register_menu_section($this->menu_name, $this->menu_args);
	}
}

class ET_AdminOverview extends ET_AdminMenuItem {

	private $options;

	function __construct(){
		parent::__construct('et-overview',  array(
			'menu_title'	=> __('Overview', ET_DOMAIN),
			'page_title' 	=> __('OVERVIEW', ET_DOMAIN),
			'callback'   	=> array($this, 'menu_view'),
			'slug'			=> 'et-overview',
			'page_subtitle'	=> __('ClassifiedEngine Overview', ET_DOMAIN),
			'pos' 			=> 5,
			'icon_class'	=> 'icon-menu-overview'
		));

		$this->add_ajax( 'et_overview_filter_stats', 'get_statistic' );

		$this->add_ajax('et_archive_expired_ads', 'archive_ads');
	}

	public function on_add_scripts(){
		$this->add_existed_script( 'jquery' );
		$this->add_existed_script( 'underscore' );
		$this->add_existed_script( 'backbone' );
		$this->add_script('bx_slider', TEMPLATEURL.'/js/bx-slider.js', array ('jquery') , '1.0' , true);
		$this->add_script('et-overview',TEMPLATEURL.'/js/admin/overview.js',array('jquery', 'underscore', 'backbone', 'ce') );
		wp_localize_script( 'ce', 'et_overview',array('reject_ad'=> __('Reject ad successfull',ET_DOMAIN)) );

	}

	public function on_add_styles(){
		$this->add_existed_style('admin.css');
	}


	/**
	 * ajax callback for acton et_archive_expired_ads
	*/
	public function archive_ads(){
		if (current_user_can( 'manage_options' )){
			/**
			 * manual archive ads
			*/
			$count = $this->archive_expired_ads( $_REQUEST['paged'] );
			/**
			 * refesh cron hook
			*/
			wp_clear_scheduled_hook( CE_Schedule::$cron_hook );
			$resp = array('success' => true, 'data' => array( 'count' => $count ));
		}else {
			$resp = array('success' => false, 'msg' => __("You don't have permission to perform this action"));
		}
		
		wp_send_json($resp );
	}


	/**
	 * find and archive all expired ad
	 * @author toannm
	 * @since 1.0
	 */
	public function archive_expired_ads ($paged = false) {

		global $wpdb, $et_global, $post;
		$paged	=	($paged -1) * 10;

		$current = date('Y-m-d H:i:s', current_time('timestamp') );
		$sql = "SELECT DISTINCT ID FROM {$wpdb->posts} as p
				INNER JOIN {$wpdb->postmeta} as mt ON mt.post_id = p.ID AND mt.meta_key = 'et_expired_date'
				WHERE 	(p.post_type = '{CE_AD_POSTTYPE}') 			AND
						(p.post_status = 'publish') 	AND
						(mt.meta_value < '{$current}') 	AND
						(mt.meta_value != '' )
				LIMIT {$paged}, 10";

		$archived_jobs = $wpdb->get_results($sql);

		$count = 0;
		//$archived_jobs = $wpdb->get_results($sql);

		foreach ($archived_jobs as $job) {
			$result	=	CE_Ads::update(array( 'ID' => $job->ID , 'post_status' => 'archive', 'change_status' => 'change_status' )) ;
			if ( !is_wp_error( $result ) ) {
				$count++;
			}
		}

		return $count;
	}

	function get_statistic() {
		global $post, $et_after_time;
		$resp 	= array();
		$within = $_REQUEST['within'] ;
		try {
			if ( !is_numeric($within) )
				throw new Exception( __('Invalid input!', ET_DOMAIN) );

			// get statistic
			$ads_count 		= et_count_ads($within);
			//$app_count 		= et_count_applications($within);
			$revenue 		= et_get_revenue($within);

			// get pending ads

			$resp =  array(
						'success' 	=> true,
						'code' 		=> 200,
						'msg' 		=> __('Data is fetched successfully', ET_DOMAIN),
						'data' 		=> array(
										'pending_adss' 	=> empty($ads_count->pending) ? 0 : $ads_count->pending ,
										'active_ads' 	=> empty($ads_count->publish) ? 0 : $ads_count->publish,
										'revenue' 		=> empty($revenue) ? 0 : $revenue,
										//'applications' 	=> empty($app_count->publish) ? 0 : $app_count->publish
										)

						);
		} catch (Exception $e) {
			$resp = build_error_ajax_response(array(), $e->getMessage );
		}

		wp_send_json( $resp );
	}

	function count_expired_ads () {
		global $wpdb, $et_global, $post;

		$current = date('Y-m-d H:i:s', current_time('timestamp') );

		$sql = "SELECT DISTINCT ID FROM {$wpdb->posts} as p
				INNER JOIN {$wpdb->postmeta} as mt ON mt.post_id = p.ID AND mt.meta_key = 'et_expired_date' 
				WHERE 	(p.post_type = '{CE_AD_POSTTYPE}') 			AND
						(p.post_status = 'publish') 	AND
						(mt.meta_value < '{$current}') 	AND
						(mt.meta_value != '' ) ";
		$archived_jobs = $wpdb->get_results($sql);
		return count($archived_jobs);
	}

	/**
	 * render view
	*/
	public function menu_view ($args) {

		$arg_pending = array(
	                'post_type'   => CE_AD_POSTTYPE,
	                'post_status' => 'pending',
	                'showposts'   => -1,
	                'meta_key'    =>  'et_paid',
	                'orderby'     =>  'meta_value post_date'
         		);
		$arg_publish = array(
	                'post_type'   => CE_AD_POSTTYPE,
	                'post_status' => 'publish',
	                'showposts'   => -1,
	                //'meta_key'    =>  'et_paid',
	                // 'orderby'     =>  'meta_value post_date'
         		);

		$ad_pending = CE_Ads::query($arg_pending);
		$revenue 	= et_get_revenue(30*24*60*60);
		$appCount 	= wp_count_posts('application');

	?>
		<div class="et-main-header">
			<div id="page_title" class="title font-quicksand"><?php echo $args->menu_title ?></div>
			<div class="desc">
				<?php _e('What happened in', ET_DOMAIN) ?>
				<span class="select-style">
					<select id="time_limit" title="<?php _e('latest 30 days', ET_DOMAIN) ?>" arrow="▼">
						<option value="<?php echo 30*24*60*60 ?>" selected="selected"><?php _e('latest 30 days', ET_DOMAIN) ?></option>
						<option value="<?php echo 15*24*60*60 ?>"><?php _e('latest 15 days', ET_DOMAIN) ?></option>
						<option value="<?php echo 7*24*60*60 ?>"><?php _e('latest 7 days', ET_DOMAIN) ?></option>
						<option value="<?php echo 24*60*60 ?>"><?php _e('last days', ET_DOMAIN) ?></option>
						<option value="<?php echo 0 ?>"><?php _e('all time', ET_DOMAIN) ?></option>
					</select>
				</span>
			</div>
			<ul class="et-head-statistics">
				<li>
					<div class="icon-overview orange">
						<div data-icon="^" class="icon"></div>
					</div>
					<div class="info">
						<div class="number font-quicksand orange bg-none" id="stats_pending_ads"><?php echo $ad_pending->found_posts;?></div>
						<div class="type"><?php _e('Pending Ads',ET_DOMAIN);?></div>
					</div>
				</li>
				<li>
					<div class="icon-overview blue">
						<div data-icon="l" class="icon"></div>
					</div>
					<div class="info">
						<div class="number font-quicksand blue bg-none" id="stats_active_ads"><?php echo CE_Ads::query($arg_publish)->found_posts;?></div>
						<div class="type"><?php _e('Posted Ads',ET_DOMAIN); ?> </div>
					</div>
				</li>
				<li>
					<div class="icon-overview green">
						<div data-icon="%" class="icon"></div>
					</div>
					<div class="info">
						<div class="number font-quicksand green bg-none" id="stats_revenue">
							<?php echo $revenue;?><sup>$</sup></div>
						<div class="type"><?php _e('Revenue Made',ET_DOMAIN);?></div>
					</div>
				</li>

				<?php
				$num = $this->count_expired_ads();
				if ($num > 0){
				?>
				<li id="expired_jobs">
					<a class="icon-expired button" title="<?php _e('Click icon to archive jobs', ET_DOMAIN) ?>" id="archive" href="#">
						<span class="icon" data-icon="#"></span>
					</a>
					<div class="info">
						<div id="" class="number font-quicksand bg-none"><?php echo $num ?></div>
						<div class="type"><?php _e('Expired ads', ET_DOMAIN) ?></div>
					</div>
				</li>
				<?php } ?>

			</ul>

		</div>
		<div class="et-main-content" id="overview">
			<div class="overview-content et-main-main no-margin overview">
				<div class="stat-container">
					<div id="users_statistic" class="col-6 stat-6"></div>
					<div id="threads_pie" class="col-6 stat-6"></div>
				</div>
				<div class="stat-container   no-margin no-padding">
					<div id="threads_statistic" class="col-12 stat-12">
						<div class="title font-quicksand"><?php _e('Pending Ads',ET_DOMAIN);?></div>
						<?php
						echo '<ul class="list-payment list-inner">';
						if($ad_pending->have_posts()){
							while($ad_pending->have_posts()){
								global $post;
								$ad_pending->the_post();
								?>
								<li id="ad_<?php echo $post->ID;?>">
									<div class="method">
										<a class="color-active act-approve" rel="<?php echo $post->ID ?>" href="#"><span class="icon" data-icon="3"></span></a>
										<a class="color-orange act-reject" rel="<?php echo $post->ID ?>" href="#"><span class="icon" data-icon="*"></span></a>
									</div>
									<div class="content" data-id="<?php echo $post->ID ?>">
										<a class="color-red error" href="#"><span class="icon" data-icon="!"></span></a>
										<a target="_blank" href="<?php the_permalink() ?>" class="ad ad-name"><?php the_title(); ?></a> <?php _e('at', ET_DOMAIN); ?> <a target="_blank" href="<?php echo get_author_posts_url($post->post_author) ?>" target="_blank" class="company"><?php echo get_the_author() ?></a>
									</div>
								</li>
								<?php

							}

						}  else {
								echo '<li>';
								_e('There are no pending ads.', ET_DOMAIN);
								echo'</li>';
						}
						echo'</ul>';
						?>
					</div>
				</div>
				<div class="stat-container ">
					<div id="threads_statistic" class="col-12 stat-12">
						<div class="title font-quicksand"><?php _e('Latest Payments',ET_DOMAIN);?></div>
						<?php
						$payment_gate	=	ET_Payment::get_support_payment_gateway();
						$orders			=	ET_AdOrder::get_orders(array (
												'post_status' => array ('pending','publish','draft'),
												'payment' => array_keys( $payment_gate )
											));
						?>

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
						        			<li title="<?php echo $ad->post_status; ?>">
						        				<div class="method"><?php echo $payment_gate[$order_data['payment']]['label']?></div>
						        				<div class="content">

						        					<?php if( $post->post_status == 'pending' ) { ?>
						        						<a title="<?php _e("Pending", ET_DOMAIN); ?>" class="color-red error" href="#"><span class="icon" data-icon="!"></span></a>
						        					<?php } elseif($post->post_status == 'publish') { ?>
						        						<a title="<?php _e("Confirmed", ET_DOMAIN); ?>" class="color-green" href="#"><span class="icon" data-icon="2"></span></a>
						        					<?php }else {
						        					?>
						        						<a title="<?php _e("Failed", ET_DOMAIN); ?>" class="color" style="color :grey;" href="#"><span class="icon" data-icon="*"></span></a>
						        					<?php
						        					} ?>

						        					<span class="price font-quicksand">
						        						<?php echo et_get_price_format($order_data['total'], 'sup'); ?>
						        					</span>
						        					<?php if( $ad ) {
						        						if($ad->post_type == CE_AD_POSTTYPE) {
								        					?>
																<a target="_blank" href="<?php echo get_permalink($ad->ID) ?>" class="ad ad-name"><?php echo $ad->post_title ?></a>
															<?php
															echo '(' .$order_data['payment']. ')' ;
								        					 _e(' at ', ET_DOMAIN);
						        						} else {
						        							printf(__("%s by ", ET_DOMAIN), $ad->post_title .'(' . $order_data['payment']. ')' );
						        						} ?>

						        					<a target="_blank" href="<?php echo get_author_posts_url($ad->post_author, $author_nicename = '') ?>" class="company"><?php echo get_the_author_meta('display_name',$ad->post_author) ?></a>

													<?php
													} else {
														$seller_name	=	'<a target="_blank" href="'.get_author_posts_url($post->post_author).'" class="company">'.get_the_author_meta('display_name',$post->post_author) .'</a>';
													?>
														<span><?php printf (__("This ad has been deleted by %s", ET_DOMAIN) , $seller_name ); ?></span>
													<?php } ?>

						        				</div>
						        			</li>
						        			<?php
						        			}
						        		} else {
						        			echo '<li>';
						        			_e('There are no payments yet.',ET_DOMAIN);
						        			echo '</li>';
						        		}
									?>
        			        </ul>
						</div>
					</div>
				</div> <!-- /.stat-container !-->

				<div class="stat-container ">
					<div id="threads_statistic" class="col-12 stat-12">
						<div class="title font-quicksand"><?php _e('Latest Sellers',ET_DOMAIN);?></div>
						<?php
						$sellers = ET_Seller::list_sellers(array());
						?>

							<div class="form no-margin no-padding no-background container-payment">
								<ul class="list-inner list-payment">
								<?php
								if($sellers){
									foreach($sellers as $key=>$seller){
										echo '<li>';
										echo '<a href="'.get_author_posts_url( $seller->ID).'">'.$seller->display_name.'</a>';
										$ads = CE_Ads::query(array('author'=>$seller->ID,'post_type' => CE_AD_POSTTYPE, 'post_status' => 'publish'));
										echo '<span class="ad-companies"> '. sprintf( et_number( __('No ads', ET_DOMAIN), __('%d ad', ET_DOMAIN), __('%d ads', ET_DOMAIN), $ads->found_posts ), $ads->found_posts) .' </span>'; 
										echo'</li>';
									}
									?>
									<li>
									<a href="<?php echo et_get_page_link('sellers'); ?>"> <?php _e("View all sellers",ET_DOMAIN)?></a>
									</li><?php
								} else {
									echo '<li>';
									_e('There are no sellers yet.',ET_DOMAIN);
									echo '</li>';
								}

								?>
								</ul>
							</div>

					</div>
				</div>

			</div>
		</div>

	<?php
		/**
		 * print template for modal reject
		*/
		ce_template_modal_reject_ad();
	}
}

?>
