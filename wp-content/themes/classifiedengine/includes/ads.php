<?php
class CE_Ads extends ET_PostType
{
    CONST POST_TYPE = CE_AD_POSTTYPE;
    CONST NONCE = 'edit_ad_nonce';
    
    static $category = CE_AD_CAT;
    static $location = ET_AdLocation::AD_LOCATION;
    
    static $instance = null;
    
    public static function register($slug = '') {
        $slug = array();
        $slug['post'] = get_theme_mod('ad_post', CE_AD_POSTTYPE);
        $slug['archive'] = get_theme_mod('ad_archive', CE_AD_POSTTYPE . 's');
        register_post_type(self::POST_TYPE, array(
            'labels' => array(
                'name' => __('Ads', ET_DOMAIN) ,
                'singular_name' => __('Ads', ET_DOMAIN) ,
                'add_new' => __('Add New', ET_DOMAIN) ,
                'add_new_item' => __('Add New Ads', ET_DOMAIN) ,
                'edit_item' => __('Edit Ads', ET_DOMAIN) ,
                'new_item' => __('New Ads', ET_DOMAIN) ,
                'all_items' => __('All Ads', ET_DOMAIN) ,
                'view_item' => __('View Ad', ET_DOMAIN) ,
                'search_items' => __('Search Ads', ET_DOMAIN) ,
                'not_found' => __('No ads found', ET_DOMAIN) ,
                'not_found_in_trash' => __('No Ads found in Trash', ET_DOMAIN) ,
                'parent_item_colon' => '',
                'menu_name' => __('Ads', ET_DOMAIN)
            ) ,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array(
                'slug' => $slug['post']
            ) ,
            'capability_type' => CE_AD_POSTTYPE,
            'capabilities' => array(
                'publish_posts' => 'publish_' . self::POST_TYPE . 's',
                'edit_posts' => 'edit_' . self::POST_TYPE . 's',
                'edit_others_posts' => 'edit_others_' . self::POST_TYPE . 's',
                'delete_posts' => 'delete_' . self::POST_TYPE . 's',
                'delete_others_posts' => 'delete_others_' . self::POST_TYPE . 's',
                'read_private_posts' => 'read_private_' . self::POST_TYPE . 's',
                'edit_post' => 'edit_' . self::POST_TYPE,
                'delete_post' => 'delete_' . self::POST_TYPE,
                'read_post' => 'read_' . self::POST_TYPE . 's'
            ) ,
            'has_archive' => $slug['archive'],
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array(
                'title',
                'editor',
                'author',
                'thumbnail',
                'excerpt',
                'comments',
                'custom-fields'
            )
        ));
        
        // register a post status: Reject
        register_post_status('reject', array(
            'label' => __('Reject', ET_DOMAIN) ,
            'private' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Reject <span class="count">(%s)</span>', 'Reject <span class="count">(%s)</span>') ,
        ));
        
        register_post_status('archive', array(
            'label' => __('Archive', ET_DOMAIN) ,
            'private' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Archive <span class="count">(%s)</span>', 'Archive <span class="count">(%s)</span>') ,
        ));
        
        /**
         * add meta box in edit ad backend - for admin
         */
        if (current_user_can('manage_options')) {
            add_action('add_meta_boxes', array(
                'CE_Ads',
                'add_meta_boxes'
            ));
            add_action('save_post', array(
                'CE_Ads',
                'save_meta_fields'
            ));
            
            if ((basename($_SERVER['SCRIPT_FILENAME']) == 'post.php' && isset($_GET['action']) && $_GET['action'] == 'edit') || (basename($_SERVER['SCRIPT_FILENAME']) == 'post-new.php' && (isset($_GET['post_type']) && $_GET['post_type'] == self::POST_TYPE))) {
                add_action('admin_head', array(
                    'CE_Ads',
                    'add_meta_script'
                ));
                add_filter('wp_dropdown_users', array(
                    'CE_Ads',
                    'wp_dropdown_users'
                ));
            }
        }
        
        self::update_ad_cap();
        
        $instance = self::get_instance();
        $instance->add_post_action();
        et_register_post_type_count_views(CE_AD_POSTTYPE);
    }
    
    /*
     * add role to user access ad
    */
    public static function update_ad_cap() {
        
        $product_capabilities = self::get_core_capabilities();
        
        global $wp_roles;
        
        $admin = get_role('administrator');
        $editor = get_role('editor');
        
        if (isset($editor->capabilities['edit_products']) && $admin->capabilities['edit_products'] && $editor->capabilities['edit_products']) return;
        foreach ($product_capabilities as $cap_group) {
            foreach ($cap_group as $capability) {
                $wp_roles->add_cap('administrator', $capability);
                $wp_roles->add_cap('editor', $capability);
            }
        }
    }
    public static function get_core_capabilities() {
        $capabilities = array();
        
        $capability_types = array(
            self::POST_TYPE,
            'shop_order',
            'shop_coupon',
            'shop_webhook'
        );
        
        foreach ($capability_types as $capability_type) {
            
            $capabilities[$capability_type] = array(
                
                // Post type
                "edit_{$capability_type}",
                "read_{$capability_type}",
                "delete_{$capability_type}",
                "edit_{$capability_type}s",
                "edit_others_{$capability_type}s",
                "publish_{$capability_type}s",
                "read_private_{$capability_type}s",
                "delete_{$capability_type}s",
                "delete_private_{$capability_type}s",
                "delete_published_{$capability_type}s",
                "delete_others_{$capability_type}s",
                "edit_private_{$capability_type}s",
                "edit_published_{$capability_type}s",
                
                // Terms
                "manage_{$capability_type}_terms",
                "edit_{$capability_type}_terms",
                "delete_{$capability_type}_terms",
                "assign_{$capability_type}_terms"
            );
        }
        return $capabilities;
    }
    public function __construct() {
        $this->name = self::POST_TYPE;
        $extend_meta_fields = apply_filters('ce_ad_meta_data', array());
        
        $this->meta_data = array_merge(array(
            ET_FEATURED,
            'et_full_location',
            'et_location_lat',
            'et_location_lng',
            'et_location',
            CE_ET_PRICE,
            'et_payment_package',
            'et_ad_order',
            'et_expired_date',
            'et_paid',
            'et_carousels',
            'et_post_views'
        ) , $extend_meta_fields);
    }
    
    static public function get_instance() {
        if (self::$instance == null) {
            self::$instance = new CE_Ads();
        }
        return self::$instance;
    }
    
    private function add_post_action() {
        
        /**
         * filter pre get post set post type to ad
         */
        $this->add_filter('pre_get_posts', 'pre_get_posts');
        
        $this->add_filter('excerpt_length', 'excerpt_length');
        $this->add_filter('excerpt_more', 'excerpt_more');
        
        /**
         * filter pre get post set post type to ad
         */
        $this->add_filter('display_post_states', 'custom_post_state');
        
        /**
         * update ad when payment success
         */
        $this->add_action('ce_payment_process', 'ce_payment_process', 10, 4);
        
        /**
         * add action publish ad, update ad order and related ad in a package
         */
        $this->add_action('ce_publish_ad', 'publish_ad_action');
        
        /**
         * catch ad change status event, update expired date
         */
        $this->add_action('transition_post_status', 'change_ad_status_action', 10, 3);
        
        /**
         * add column
         */
        
        $this->add_filter('manage_' . CE_AD_POSTTYPE . '_posts_columns', 'add_post_column');
        $this->add_action('manage_' . CE_AD_POSTTYPE . '_posts_custom_column', 'post_column', 10, 2);
        
        /**
         * filter post title to append post view
         */
        $this->add_filter('the_title', 'the_title');
        
        $this->add_filter('comments_open', 'comments_open');
        $this->add_filter('get_comments_number', 'get_comments_number', 10, 2);
    }
    
    public function posts_orderby() {
        global $wpdb;
        $order = "DESC";
        if (isset($_REQUEST['sortby']) && $_REQUEST['sortby'] == 'price') {
            $order = "ASC";
        }
        
        // return "{$wpdb->postmeta}.meta_value DESC";
        return "{$wpdb->postmeta}.meta_value + 0 {$order}, {$wpdb->posts}.post_date DESC";
    }
    
    // for search.
    static public function posts_orderby_for_search() {
        global $wpdb;
        
        // return "{$wpdb->postmeta}.meta_value DESC";
        return "{$wpdb->postmeta}.meta_value + 0 DESC, {$wpdb->posts}.post_date DESC";
    }
    
    public function db_location_join($join) {
        global $wpdb, $wp_query;
        
        //$join .= " INNER JOIN {$wpdb->postmeta} as etmeta ON {$wpdb->posts}.ID = etmeta.post_id AND etmeta.meta_key = 'et_location' ";
        $join.= " INNER JOIN {$wpdb->postmeta} as etmeta1 ON {$wpdb->posts}.ID = etmeta1.post_id AND etmeta1.meta_key = 'et_full_location' ";
        
        //echo $join;
        return $join;
    }
    
    public function db_location_where($where) {
        global $wpdb, $wp_query;
        $loc = $wp_query->query_vars['et_address'];
        
        //if (empty($loc) || empty($wp_query->location)) return $where;
        
        //$loc = empty($loc) ? $wp_query['location'] : $loc;
        
        $where.= " OR ( {$wpdb->posts}.post_type = '" . CE_AD_POSTTYPE . "'
						AND ({$wpdb->posts}.post_status = 'publish') AND
						 etmeta1.meta_value LIKE '%{$loc}%' OR etmeta1.meta_value = '" . __('Anywhere', ET_DOMAIN) . "' ) ";
        return $where;
    }
    
    /**
     * pre get posts filter function
     */
    function pre_get_posts($query) {
        global $wpdb, $wp_query, $et_global;
        
        remove_filter('posts_orderby', array(
            $this,
            'posts_orderby'
        ));
        
        // hook to filter and search by address
        if (!empty($query->query_vars['et_address'])) {
            set_query_var('et_address', $query->query_vars['et_address']);
            add_filter('posts_join', array(
                $this,
                'db_location_join'
            ));
            add_filter('posts_where', array(
                $this,
                'db_location_where'
            ));
        } else {
            remove_filter('posts_join', array(
                $this,
                'db_location_join'
            ));
            remove_filter('posts_where', array(
                $this,
                'db_location_where'
            ));
        }
        
        $query->set('orderby', 'date');
        
        if (is_search()) {
            $s = addslashes(get_query_var('s'));
            $wp_query->set('s', $s);
        }
        
        if ((is_home() || is_author() || is_tax(CE_AD_CAT) || is_tax('ad_location') || is_post_type_archive(CE_AD_POSTTYPE) || is_tax() || is_search()) && !is_admin()) {
            if (!$query->is_main_query()) return $query;
            
            $query->set('post_type', CE_AD_POSTTYPE);
            
            // allow people view publish jobs in archive only
            $query->set('post_status', 'publish');
        }
        
        /**
         * add meta key to order
         */
        if (isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == CE_AD_POSTTYPE) {
            
            $orderby = ce_get_ad_orderby();
            
            if ($query->query_vars['meta_key'] && $query->query_vars['meta_key'] == 'et_post_views') $orderby = 'et_post_views';

            if (!empty($orderby) && $orderby != 'date' && !is_admin()) {
                $query->set('meta_key', $orderby);
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'DESC');
            }
            
            if ( empty($query->query_vars['meta_key']) && !is_admin() ) {
                $query->query_vars['meta_key'] = ET_FEATURED;
            }
            
            /**
             * add filter posts_orderby to order by meta key
             */
            if ($query->query_vars['meta_key'] && $orderby != 'date' && !empty($orderby)) {
                $this->add_filter('posts_orderby', 'posts_orderby');
            }
        }
        
        $c_str = ET_AdCatergory::slug();
        $l_str = ET_AdLocation::slug();
        $ad_location = get_query_var($l_str);
        
        if (is_home() || (is_post_type_archive(CE_AD_POSTTYPE) && !is_admin())) {
            
            if ((isset($_COOKIE['et_location']) && $ad_location != 'alllocation' && !is_author())) {
                
                $location_cookie = $_COOKIE['et_location'];
                $query->query_vars['tax_query'][] = array(
                    'taxonomy' => 'ad_location',
                    'field' => 'slug',
                    'terms' => trim($location_cookie)
                );
            }
            
            if (isset($_GET[$c_str])) {
                $query->query_vars['tax_query'][] = array(
                    'taxonomy' => CE_AD_CAT,
                    'field' => 'slug',
                    'terms' => trim($_GET[$c_str])
                );
            }
            
            $query->query_vars['tax_query']['relation'] = 'AND';
        }
        
        /**
         * filter product_cat tax query
         */
        if (is_tax(CE_AD_CAT)) {
            
            if (!$ad_location) {
                if (isset($_COOKIE['et_location'])) {
                    $ad_location = $_COOKIE['et_location'];
                }
            }
            
            // $ad_category = get_query_var( CE_AD_CAT );
            if (!empty($ad_location)) {
                $query->query_vars['tax_query'][] = array(
                    'taxonomy' => 'ad_location',
                    'field' => 'slug',
                    'terms' => trim($ad_location)
                );
                $query->query_vars['tax_query']['relation'] = 'AND';
            }
        }
        
        /**
         * filter tax ad_location query
         */
        if (is_tax('ad_location')) {
            
            // $ad_location = get_query_var( 'location' ) ;
            $product_cat = get_query_var($c_str);
            if (!empty($product_cat)) {
                $query->query_vars['tax_query'][] = array(
                    'taxonomy' => CE_AD_CAT,
                    'field' => 'slug',
                    'terms' => trim($product_cat)
                );
                $query->query_vars['tax_query']['relation'] = 'AND';
            }
        }
        
        if (is_feed()) {
            
            // sorting by featured
            add_filter('posts_orderby', array(&$this,
                'posts_orderby'
            ));
            $query->set('meta_key', $et_global['db_prefix'] . 'featured');
            $query->set('orderby', 'date');
            $query->set('order', 'DESC');
            $query->set('post_status', 'publish');
            
            // if post type isn't set, we set it job by default
            if (get_query_var('post_type') == '') $query->set('post_type', CE_AD_POSTTYPE);
        }
        
        return $query;
    }
    
    function excerpt_length() {
        return 15;
    }
    
    function excerpt_more() {
        return '';
    }
    
    public static function custom_sort_args() {
        $order_arr = array(
            ET_FEATURED => array(
                'label' => __("Featured", ET_DOMAIN) ,
                'key' => __("featured", ET_DOMAIN)
            ) ,
            'date' => array(
                'label' => __("Latest", ET_DOMAIN) ,
                'key' => __("date", ET_DOMAIN)
            ) ,
            CE_ET_PRICE => array(
                'label' => __("Price", ET_DOMAIN) ,
                'key' => __("price", ET_DOMAIN)
            ) ,
            'et_post_views' => array(
                'label' => __("Popular", ET_DOMAIN) ,
                'key' => __("post view", ET_DOMAIN)
            )
        );
        
        return apply_filters('ce_ad_custom_sort_args', $order_arr);
    }
    
    function custom_post_state($states) {
        global $post;
        if ($post->post_status == 'reject') $states[] = __('Reject', ET_DOMAIN);
        if ($post->post_status == 'archive') $states[] = __('Archive', ET_DOMAIN);
        if ($post->post_status == 'sold') $states[] = __('Sold', ET_DOMAIN);
        return $states;
    }
    
    /**
     * catch action ce_payment_process and update ad data
     */
    function ce_payment_process($payment_return, $order, $payment_type, $session) {
        if (!$payment_return['ACK']) {
            if ($payment_return['payment_status'] != 'Completed') return '';
        }
        
        $ad_id = $session['ad_id'];
        if ($payment_type == 'cash') {
            $this->_update(array(
                'ID' => $ad_id,
                'post_status' => 'pending',
                'et_paid' => 0,
                'et_ad_order' => $session['order_id']
            ));
            return;
        }
        
        if ($payment_type == 'usePackage') {
            return;
        }
        
        $options = new CE_Options();
        
        if ($payment_type == 'free') {
            
            /**
             * sync Ad data
             */
            if ($options->use_pending())
             // pending ad
            $this->_update(array(
                'ID' => $ad_id,
                'post_status' => 'pending',
                'et_paid' => 2
            ));
            else $this->_update(array(
                'ID' => $ad_id,
                'post_status' => 'publish',
                'et_paid' => 2
            ));
            return $payment_return;
        }
        
        $status = strtoupper($payment_return['payment_status']);
        if ($status == 'PENDING') {
            $this->_update(array(
                'ID' => $ad_id,
                'post_status' => 'pending',
                'et_paid' => 1,
                'et_ad_order' => $session['order_id']
            ));
        }
        
        if (($status == 'COMPLETED') || $status == 'PUBLISH') {
            if ($options->use_pending()) {
                 // pending ad enable
                $this->_update(array(
                    'ID' => $ad_id,
                    'post_status' => 'pending',
                    'et_paid' => 1,
                    'et_ad_order' => $session['order_id']
                ));
            } else {
                $this->_update(array(
                    'ID' => $ad_id,
                    'post_status' => 'publish',
                    'et_paid' => 1,
                    'et_ad_order' => $session['order_id']
                ));
            }
            return $payment_return;
        }
        
        if ($status == 'FRAUD') {
            $this->_update(array(
                'ID' => $ad_id,
                'post_status' => 'draft',
                'et_paid' => 0,
                'et_ad_order' => $session['order_id']
            ));
        } else {
            
            /**
             * in some case the payment will be pending
             */
            $this->_update(array(
                'ID' => $ad_id,
                'post_status' => 'pending',
                'et_paid' => 0,
                'et_ad_order' => $session['order_id']
            ));
        }
        
        return $payment_return;
    }
    
    /**
     * action trigger when publish an ad
     */
    function publish_ad_action($ad_id) {
        
        if (get_post_type($ad_id) != CE_AD_POSTTYPE) return;
        
        $order = get_post_meta($ad_id, 'et_ad_order', true);
        if ($order) {
            
            /**
             * update order status
             */
            if (!isset($_POST['_et_nonce'])) wp_update_post(array(
                'ID' => $order,
                'post_status' => 'publish'
            ));
            
            $ads = new WP_Query(array(
                'post_type' => CE_AD_POSTTYPE,
                'post_status' => array(
                    'pending'
                ) ,
                'meta_value' => $order,
                'meta_key' => 'et_ad_order',
                'posts_per_page' => - 1,
                'orderby' => 'post_date',
                'order' => 'DESC',
                'post__not_in' => array(
                    $ad_id
                )
            ));
            
            if (!$ads->have_posts()) return;
            
            /**
             * update ads in same package
             */
            $options = new CE_Options();
            
            if ($options->use_pending) {
                foreach ((array)$ads->posts as $ad) {
                    $this->_update(array(
                        'ID' => $ad->ID,
                        'post_status' => 'pending',
                        'et_paid' => 1
                    ));
                }
            } else {
                foreach ((array)$ads->posts as $ad) {
                    $this->_update(array(
                        'ID' => $ad->ID,
                        'post_status' => 'publish',
                        'et_paid' => 1
                    ));
                }
            }
        }
    }
    
    /**
     * catch event change ad status, update expired date
     */
    public function change_ad_status_action($new_status, $old_status, $post) {
        if ($post->post_type != self::POST_TYPE) return;
        
        $payment_package = $this->_get_field($post->ID, 'et_payment_package');
        $package = ET_PaymentPackage::get($payment_package);
        
        $old_expiration = $this->_get_field($post->ID, 'et_expired_date');
        
        /**
         * if an ad didnt have a package, force publish
         */
        if (!$package || is_wp_error($package)) {
            
            if (et_get_payment_disable()) {
                
                /**
                 * update expiry of Ad in case disable payment gateway;
                 * @version 1.8.2
                 */
                $this->update_expiry_ad_free($new_status, $old_status, $old_expiration, $post);
            }
            
            if ($new_status == 'publish') {
                do_action('ce_publish_ad', $post->ID);
            }
            CE_Mailing::ad_change_status($new_status, $old_status, $post);
            return false;
        };
        
        $duration = (int)$package->et_duration;
        
        if ($new_status == 'pending') {
             // clear ad expired date and post view when change from archive to pending
            if ($old_status == "archive" || $old_status == "draft") {
                 // force update expired date if job is change from draft or archive to publish
                $expired_date = date('Y-m-d H:i:s', strtotime("+{$duration} days"));
                $this->_update_field($post->ID, 'et_expired_date', '');
                $this->_update_field($post->ID, 'et_post_views', 0);
                wp_update_post(array(
                    'ID' => $post->ID,
                    'post_date' => ''
                ));
            }
        } elseif ($new_status == 'publish') {
             // update post expired date when publish
            if ($old_status == "archive" || $old_status == "draft") {
                 // force update expired date if job is change from draft or archive to publish
                
                $expired_date = date('Y-m-d H:i:s', strtotime("+{$duration} days"));
                $this->_update_field($post->ID, 'et_expired_date', $expired_date);
            } else {
                 // update expired date when the expired date less then current time
                
                if (empty($old_expiration) || current_time('timestamp') > strtotime($old_expiration)) {
                    $expired_date = date('Y-m-d H:i:s', strtotime("+{$duration} days"));
                    $this->_update_field($post->ID, 'et_expired_date', $expired_date);
                }
            }
            
            // wp_schedule_single_event( time() + 2, 'ce_publish_ad' , $post->ID );
            do_action('ce_publish_ad', $post->ID);
        }
        
        /**
         * send mail when change ad status
         */
        CE_Mailing::ad_change_status($new_status, $old_status, $post);
    }
    
    /**
     * update expiry date for ad incase disable payment gateways.
     * @since 1.8.1
     * @author danng
     */
    
    function update_expiry_ad_free($new_status, $old_status, $old_expiration, $post) {
        
        $duration = (int)get_theme_mod('ce_number_days_expiry', '');
        if (!empty($duration) && $duration > 0) {
            if ($new_status == 'pending') {
                 // clear ad expired date and post view when change from archive to pending
                if ($old_status == "archive" || $old_status == "draft") {
                     // force update expired date if job is change from draft or archive to publish
                    
                    $expired_date = date('Y-m-d H:i:s', strtotime("+{$duration} days"));
                    $this->_update_field($post->ID, 'et_expired_date', '');
                }
            } elseif ($new_status == 'publish') {
                 // update post expired date when publish
                if ($old_status == "archive" || $old_status == "draft") {
                     // force update expired date if job is change from draft or archive to publish
                    
                    $expired_date = date('Y-m-d H:i:s', strtotime("+{$duration} days"));
                    $this->_update_field($post->ID, 'et_expired_date', $expired_date);
                } else {
                     // update expired date when the expired date less then current time
                    
                    if (empty($old_expiration) || current_time('timestamp') > strtotime($old_expiration)) {
                        $expired_date = date('Y-m-d H:i:s', strtotime("+{$duration} days"));
                        $this->_update_field($post->ID, 'et_expired_date', $expired_date);
                    }
                }
            }
        }
    }
    
    public function validate_data($data) {
        global $ce_config;
        
        $require_fields = get_theme_mod('ad_require_fields', array(
            CE_ET_PRICE,
            'ad_location',
            CE_AD_CAT
        ));
        
        if (isset($data['renew']) && !isset($data['et_payment_package']) && !et_get_payment_disable()) {
            return new WP_Error('ad_empty_package', __("Cannot create a ad with an empty package.", ET_DOMAIN));
        } else {
            global $user_ID;
        }
        
        if (!isset($data['post_content']) || $data['post_content'] == '') {
            return new WP_Error('ad_empty_content', __("Please complete all required fields.", ET_DOMAIN));
        }
        
        if (!isset($data[CE_AD_CAT]) && in_array(CE_AD_CAT, $require_fields)) {
            return new WP_Error('invalid_category', __("An ad should has a category!", ET_DOMAIN));
        }
        
        if (!isset($data['ad_location']) && in_array('ad_location', $require_fields)) {
            return new WP_Error('invalid_location', __("An ad should has a location!", ET_DOMAIN));
        }
        
        if ((!isset($data[CE_ET_PRICE]) || $data[CE_ET_PRICE] == '') && in_array(CE_ET_PRICE, $require_fields)) {
            return new WP_Error('invalid_location', __("You should enter price for your item!", ET_DOMAIN));
        }
        
        /**
         * check max category options, filter ad category
         */
        if ($ce_config['max_cat']) {
            $num_of_cat = count($data[CE_AD_CAT]);
            if ($ce_config['max_cat'] < $num_of_cat) {
                for ($i = $ce_config['max_cat']; $i < $num_of_cat; $i++) {
                    unset($data[CE_AD_CAT][$i]);
                }
            }
        }
        
        $data['tax_input'] = array(
            CE_AD_CAT => $data[CE_AD_CAT],
            'ad_location' => $data['ad_location'],
        );
        unset($data[CE_AD_CAT]);
        unset($data['ad_location']);
        
        foreach ($data as $key => $value) {
            if (!is_array($value) && !is_numeric($value) && $key != 'post_content') $data[$key] = strip_tags($value);
        }
        
        $data['post_content'] = $this->filter_content($data['post_content']);
        
        if (!empty($data[CE_ET_PRICE]) && in_array(CE_ET_PRICE, $require_fields)) $data[CE_ET_PRICE] = $this->filter_price($data[CE_ET_PRICE]);
        
        return apply_filters('ce_ad_validate_data', $data);
    }
    
    public static function insert($data) {
        
        global $user_ID;
        $instance = self::get_instance();
        
        /**
         * process, validate ad data
         */
        $data = $instance->validate_data($data);
        if (is_wp_error($data)) return $data;
        
        $data[ET_FEATURED] = 0;
        $data['et_paid'] = 2;
        $data['post_status'] = 'draft';
        
        if (!et_get_payment_disable()) {
            if (!isset($data['et_payment_package'])) {
                return new WP_Error('invalid_package', __("You have to select a payment package!", ET_DOMAIN));
            }
            
            $package = ET_PaymentPackage::get($data['et_payment_package'], false);
            if (is_wp_error($package)) {
                return new WP_Error('invalid_package', __("You select an invalid payment package!", ET_DOMAIN));
            }
            $et_featured = ET_FEATURED;
            if ($package->$et_featured) {
                $data[ET_FEATURED] = 1;
            }
            
            $regurlar_price = CE_ET_PRICE;
            if ($package->$regurlar_price > 0) {
                $data['et_paid'] = 0;
            }
        } else {
            $data['post_status'] = 'pending';
        }
        
        $return = $instance->_insert($data);
        
        if (!($return instanceof WP_Error) && isset($data['et_carousels']) && !empty($data['et_carousels'])) {
            foreach ($data['et_carousels'] as $key => $value) {
                $att = get_post($value);
                if (current_user_can('manage_options') || $att->post_author == $user_ID) {
                    wp_update_post(array(
                        'ID' => $value,
                        'post_parent' => $return
                    ));
                }
            }
            
            if (current_user_can('manage_options') || $att->post_author == $user_ID) {
                if (isset($data['featured_image'])) set_post_thumbnail($return, $data['featured_image']);
                else set_post_thumbnail($return, $value);
            }
        }
        
        if (!current_user_can('manage_options')) {
            $ad_category = $data['tax_input'][CE_AD_CAT];
            $ad_location = $data['tax_input']['ad_location'];
            
            $cat = array();
            $loc = array();
            
            foreach ($ad_category as $key => $value) {
                $term = get_term_by('id', $value, CE_AD_CAT);
                if (!is_wp_error($term)) $cat[] = $term->slug;
            }
            
            $term = get_term_by('id', $ad_location, 'ad_location');
            if (!is_wp_error($term)) $loc[] = $term->slug;
            
            wp_set_object_terms($return, $cat, CE_AD_CAT);
            wp_set_object_terms($return, $loc, 'ad_location');
        }
        
        do_action('ce_insert_ad', $return, $data);
        
        return $return;
    }
    
    /**
     * update_ad
     */
    public static function update($data) {
        
        if (!$data['ID']) return new WP_Error('no_id', __("Invalid ad ID.", ET_DOMAIN));
        
        $post = get_post($data['ID']);
        
        /**
         * check permission
         */
        if (!current_user_can('manage_options')) {
            global $user_ID;
            if ($post->post_author != $user_ID) return new WP_Error('permission_denied', __("You donnot have grant to edit this ad.", ET_DOMAIN));
        }
        
        /**
         * check post type match
         */
        if ($post->post_type !== self::POST_TYPE) return new WP_Error('invalid_post_type', __("Post type not support.", ET_DOMAIN));
        
        $instance = self::get_instance();
        
        if (!isset($data['change_status'])) {
            
            /**
             * process, validate ad data
             */
            $data = $instance->validate_data($data);
            
            if (is_wp_error($data)) return $data;
        }
        
        if (isset($data['et_payment_package']) && !et_get_payment_disable()) {
            $package = ET_PaymentPackage::get($data['et_payment_package'], false);
            if (is_wp_error($package)) {
                return new WP_Error('invalid_package', __("You select an invalid payment package!", ET_DOMAIN));
            }
            
            $data[ET_FEATURED] = 0;
            $et_featured = ET_FEATURED;
            if ($package->$et_featured) {
                $data[ET_FEATURED] = 1;
            }
        }
        
        $return = $instance->_update($data);
        
        if (!($return instanceof WP_Error) && isset($data['et_carousels']) && !empty($data['et_carousels'])) {
            
            foreach ($data['et_carousels'] as $key => $value) {
                $att = get_post($value);
                if (current_user_can('manage_options') || $att->post_author == $user_ID) {
                    wp_update_post(array(
                        'ID' => $value,
                        'post_parent' => $return
                    ));
                }
            }
            
            if (current_user_can('manage_options') || $att->post_author == $user_ID) {
                
                /**
                 * featured image not null and should be in carousels array data
                 */
                if (isset($data['featured_image']) && in_array($data['featured_image'], $data['et_carousels'])) {
                    set_post_thumbnail($return, $data['featured_image']);
                } else {
                    $thumbnail = get_post_thumbnail_id($return);
                    
                    if (!in_array($thumbnail, $data['et_carousels'])) set_post_thumbnail($return, $att->ID);
                }
            }
        }
        
        do_action('ce_update_ad', $return, $data);
        
        return $return;
    }
    
    public static function sync($method, $args) {
        
        switch ($method) {
            case 'create':
                $result = self::insert($args);
                
                break;

            case 'update':
                
                /**
                 * for security: prevent common user change status and set featured
                 */
                if (!current_user_can('manage_options')) {
                    $ad = self::convert(get_post($args['ID']));
                    
                    // check , seller cannot change ad to publish.
                    if ($ad->post_status != 'publish' && $args['post_status'] == 'publish') $args['post_status'] = 'pending';
                    
                    unset($args[ET_FEATURED]);
                }
                
                if (isset($args['update_type'])) {
                    if ($args['update_type'] == 'change_status') {
                        
                        $data = array();
                        $data['ID'] = $args['ID'];
                        $data['post_status'] = $args['post_status'];
                        $data['change_status'] = $args['update_type'];
                        
                        $result = self::update($data);
                        if ($result && $args['post_status'] == 'reject') {
                            $reason = isset($args['message']) ? $args['message'] : '';
                            CE_Mailing::mail_reject($args['ID'], $reason);
                        }
                    } else {
                        
                        $data = array();
                        $data['ID'] = $args['ID'];
                        $data[ET_FEATURED] = $args[ET_FEATURED];
                        $data['change_status'] = $args['update_type'];
                        
                        $result = self::update($data);
                    }
                    break;
                }
                
                $result = self::update($args);
                
                break;

            case 'delete':
                $result = self::convert(get_post($args['ID']));
                global $user_ID;
                if ($user_ID == $result->post_author || current_user_can('manage_options')) $post = wp_update_post(array(
                    'ID' => $args['ID'],
                    'post_status' => 'trash'
                ));
                
                //return $result;
                break;

            default:
                $result = new WP_Error('invalid_method', __("You do something not valid!", ET_DOMAIN));
                break;
            }
            
            if (!is_wp_error($result)) return self::convert(get_post($result));
            else return $result;
        }
        
        /**
         * Delete a ad + reply of this ad
         */
        public static function delete($id, $force_delete = false) {
            
            $replies = get_posts(array(
                'post_parent' => $id,
                'post_type' => 'reply'
            ));
            
            if (is_array($replies) && count($replies) > 0) {
                
                foreach ($replies as $reply) {
                    if ($force_delete) {
                        wp_delete_post($reply->ID, $force_delete);
                    } else {
                        wp_trash_post($reply->ID);
                    }
                }
            }
            
            if ($force_delete) {
                if (wp_delete_post($id, $force_delete) != false) do_action('et_delete_' . self::POST_TYPE, $id);
            } else {
                if (wp_trash_post($id) != false) do_action('et_delete_' . self::POST_TYPE, $id);
            }
        }
        
        public static function get($id) {
            return self::get_instance()->_get($id);
        }
        
        public static function convert($post) {
            global $current_user, $user_ID, $isMobile;
            
            if (is_int($post)) {
                $post = get_post($post);
            }
            
            $result = self::get_instance()->_convert($post, false);
            $result->id = $result->ID;
            
            $result->permalink = get_permalink($post->ID);
            
            $result->location = wp_get_object_terms($post->ID, self::$location);
            $result->category = wp_get_object_terms($post->ID, self::$category);
            
            $result->date_ago = self::process_post_date($result->post_date);
            
            /**
             * ad price in formated wiht currency
             */
            $regurlar_price = CE_ET_PRICE;
            if (!$result->$regurlar_price) {
                $result->$regurlar_price = get_post_meta($post->ID, 'et_price', true);
            }
            
            $result->price = et_get_price_format((double)$result->$regurlar_price);
            
            $et_featured = ET_FEATURED;
            if (!$result->$et_featured) {
                $result->et_featured = get_post_meta($post->ID, 'et_featured', true);
            }
            
            if (!$isMobile) $result->the_post_thumbnail = get_the_post_thumbnail($result->ID, 'ad-thumbnail-grid', array(
                'alt' => get_the_title()
            ));
            else $result->the_post_thumbnail = get_the_post_thumbnail($result->ID, 'ad-thumbnail-mobile', array(
                'alt' => get_the_title()
            ));
            
            /**
             * return feauted image id
             */
            $result->featured_image = get_post_thumbnail_id($result->ID);
            
            if (current_user_can('manage_options') || $result->post_author == $user_ID) {
                $children = get_children(array(
                    'numberposts' => 15,
                    'order' => 'ASC',
                    'post_mime_type' => 'image',
                    'post_parent' => $post->ID,
                    'post_type' => 'attachment'
                ));
                
                $result->et_carousels = array();
                
                foreach ($children as $key => $value) {
                    $result->et_carousels[] = $key;
                }
                if (has_post_thumbnail($result->ID)) {
                    $thumbnail_id = get_post_thumbnail_id($result->ID);
                    if (!in_array($thumbnail_id, $result->et_carousels)) $result->et_carousels[] = $thumbnail_id;
                }
            }
            
            if (current_user_can('manage_options') || $result->post_author == $user_ID) {
                $result->date = date(get_option('date_format') , strtotime($result->post_date));
            }
            
            return apply_filters('ce_convert_ad', $result);
        }
        
        function filter_content($content) {
            $pattern = "/<[^\/>]*>(&nbsp;)*([\s]?)*<\/[^>]*>/";
             //use this pattern to remove any empty tag '<a target="_blank" rel="nofollow" href="$1">$3</a>'
            
            $content = preg_replace($pattern, '', $content);
            
            $link_pattern = "/<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>/";
            $content = str_replace('<a', '<a target="_blank" rel="nofollow"', $content);
            $tags_accep = apply_filters('ce_accept_tags_content', '<p><a><ul><ol><li><i><h3><h4><h5><h6><label><span><b><em><strong><br>');
            $content = strip_tags($content, $tags_accep);
            
            return $content;
        }
        function filter_price($price) {
            
            $option = new CE_Options;
            $currency_format = $option->get_option('et_currency_format');
            if ($currency_format != 2) {
                
                //default format
                $price = (double)str_replace(',', '', $price);
            } else {
                $price = str_replace('.', '', $price);
                $price = (double)str_replace(',', '.', $price);
            }
            return $price;
        }
        
        /**
         * update post views
         * from 1.8.2 version, use et_count_post_views function in core(don't use this function)
         */
        public static function update_post_views($post) {
            $views = self::get_field($post->ID, 'et_post_views');
            
            if ($post->post_status == 'publish') {
                $cookie = 'cookie_' . $post->ID . '_visited';
                if (!isset($_COOKIE[$cookie])) {
                    self::update_field($post->ID, 'et_post_views', $views + 1);
                    setcookie($cookie, 'is_visited', time() + 3 * 3600);
                }
            }
        }
        
        public static function process_post_date($post_date) {
            $blogtime = current_time('mysql');
            
            $date = strtotime($blogtime) - strtotime($post_date);
            
            // echo $date;
            $day_ago = $date / (3600 * 24);
            
            $date_ago = apply_filters('ce_ad_process_post_date', '', $day_ago);
            
            if ($date_ago != '') return $date_ago;
            
            /**
             * day ago
             */
            if ($day_ago >= 1) {
                $day_ago = round($day_ago);
                if ($day_ago == 1) {
                    $date_ago = __("Yesterday", ET_DOMAIN);
                } else {
                    $date_ago = sprintf(__("%d days ago", ET_DOMAIN) , $day_ago);
                }
                return $date_ago;
            }
            
            /**
             * hour ago
             */
            $hour = $date / (3600);
            if ($hour > 1) {
                $day_ago = round($hour);
                if ($hour == 1) {
                    $date_ago = sprintf(__("%d hour ago", ET_DOMAIN) , $day_ago);
                } else {
                    $date_ago = sprintf(__("%d hours ago", ET_DOMAIN) , $day_ago);
                }
                return $date_ago;
            }
            
            /**
             * minute ago
             */
            $minute = $date / (60);
            if ($minute > 1) {
                $day_ago = round($minute);
                if ($hour == 1) {
                    $date_ago = sprintf(__("%d minute ago", ET_DOMAIN) , $day_ago);
                } else {
                    $date_ago = sprintf(__("%d minutes ago", ET_DOMAIN) , $day_ago);
                }
                return $date_ago;
            } else {
                
                // $day_ago	=	round($date);
                if ($date <= 1) $date_ago = sprintf(__("%d second ago", ET_DOMAIN) , $date);
                else $date_ago = sprintf(__("%d seconds ago", ET_DOMAIN) , $date);
            }
            
            return $date_ago;
        }
        
        /**
         * Additional methods in theme
         */
        public static function change_status($id, $new_status) {
            $available_statuses = array(
                'pending',
                'publish',
                'trash'
            );
            
            if (in_array($new_status, $available_statuses)) return self::update(array(
                'ID' => $id,
                'post_status' => $new_status,
                'change_status' => $new_status
            ));
            else return false;
        }
        
        public static function update_field($id, $key, $value) {
            $instance = self::get_instance();
            $instance->_update_field($id, $key, $value);
        }
        
        public static function get_field($id, $key) {
            $instance = self::get_instance();
            
            return $instance->_get_field($id, $key);
        }
        
        public function update_meta_fields($id, $args) {
            
            foreach ($this->meta_data as $key => $value) {
                if (isset($args[$value])) {
                    $this->_update_field($id, $value, $args[$value]);
                }
            }
        }
        
        /**
         * return a wp_query ojbect, query ad post by seller id
         */
        public static function get_ads_by_seller($user_id = '', $args = array()) {
            if ($user_id == '') {
                global $user_ID;
                $user_id = $user_ID;
            }
            $args = wp_parse_args($args, array(
                'post_type' => self::POST_TYPE,
                'author' => $user_id
            ));
            return new WP_Query($args);
        }
        
        /**
         * query_ad
         * return a wp_query object
         * query ad post
         * args : query args
         */
        public static function query($args = array()) {
            
            $args = wp_parse_args($args, array(
                'post_type' => self::POST_TYPE,
                'post_status' => 'publish'
            ));
            if (is_page_template('page-account-listing.php')) {
                 // query in page account listing
                global $user_ID;
                $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                $args = wp_parse_args($args, array(
                    'posts_per_page' => - 1,
                    'post_author' => $user_ID,
                    'paged' => $paged,
                    'post_status' => array(
                        'publish',
                        'pending',
                        'reject',
                        'archive',
                        'draft'
                    )
                ));
            }
            
            // query in search default
            if (is_search()) {
                $c_str = ET_AdCatergory::slug();
                $l_str = ET_AdLocation::slug();
                
                $ad_location = (get_query_var($l_str)) ? get_query_var($l_str) : '';
                $product_cat = (get_query_var($c_str)) ? get_query_var($c_str) : '';
                
                $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                
                add_filter('posts_orderby', array(
                    'CE_Ads',
                    'posts_orderby_for_search'
                ));
                
                $args = array(
                    'paged' => $paged,
                    'post_type' => CE_AD_POSTTYPE,
                    'post_status' => 'publish',
                    'orderby' => 'meta_value_num',
                    'meta_key' => ET_FEATURED
                );
                $args_term = array();
                
                $url = add_query_arg('s', get_query_var('s') , home_url() . '/');
                if (!empty($ad_location) && !empty($product_cat)) {
                    
                    //$cat	=	get_term_by( 'name', urldecode($product_cat), 'product_cat');
                    
                    $args_term = array(
                        'tax_query' => array(
                            'relation' => 'AND',
                            array(
                                'taxonomy' => CE_AD_CAT,
                                'field' => 'slug',
                                'terms' => trim(urldecode($product_cat))
                            ) ,
                            array(
                                'taxonomy' => 'ad_location',
                                'field' => 'slug',
                                'terms' => trim(urldecode($ad_location))
                            )
                        )
                    );
                } else if (!empty($ad_location)) {
                    
                    $args_term = array(
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'ad_location',
                                'field' => 'slug',
                                'terms' => $ad_location
                            )
                        )
                    );
                } else if (!empty($product_cat)) {
                    $args_term = array(
                        'tax_query' => array(
                            array(
                                'taxonomy' => CE_AD_CAT,
                                'field' => 'slug',
                                'terms' => $product_cat
                            )
                        )
                    );
                }
                
                $args = wp_parse_args($args_term, $args);
                $search_str = trim(get_query_var('s'));
                if ($search_str) $args = wp_parse_args($args, array(
                    'post_type' => self::POST_TYPE,
                    's' => get_query_var('s')
                ));
                else $args = wp_parse_args($args, array(
                    'post_type' => self::POST_TYPE
                ));
            }
            
            /**
             * add address to search
             */
            if (isset($args['s']) && $args['s'] != '') {
                $args['et_address'] = $args['s'];
            }
            
            $query = new WP_Query($args);
            
            return $query;
        }
        
        /**
         * add manage ad post column
         */
        public function add_post_column($columns) {
            return array_merge($columns, array(
                'expired_date' => __('Expiry Date', ET_DOMAIN)
            ));
        }
        
        /**
         * enter expired date
         */
        public function post_column($column, $post_id) {
            if ($column == 'expired_date') {
                $expired_date = get_post_meta($post_id, 'et_expired_date', true);
                if ($expired_date != '') echo date('Y/m/d', strtotime($expired_date));
            }
        }
        
        /**
         * append post view
         */
        public function the_title($title) {
            
            if (is_admin() && is_post_type_archive(CE_AD_POSTTYPE)) {
                global $post;
                $post_view = self::post_views($post->ID);
                return $title . ' (' . $post_view . ')';
            }
            
            return $title;
        }
        
        public function comments_open($open) {
            global $post;
            if ($post->post_type == self::POST_TYPE) return true;
            return $open;
        }
        
        public function get_comments_number($count, $post_id) {
            $post = get_post($post_id);
            if ($post->post_type == self::POST_TYPE) {
                $comments = get_comments(array(
                    'type' => 'comment',
                    'post_id' => $post->ID,
                    'status' => 'approve'
                ));
                $count = count($comments);
            }
            return $count;
        }
        
        public static function post_views($id) {
            $text_single = __('%d view', ET_DOMAIN);
            $text_plural = __('%d views', ET_DOMAIN);
            
            $view = (int)self::get_field($id, 'et_post_views');
            if ($view <= 1) return sprintf($text_single, number_format($view));
            else return sprintf($text_plural, number_format($view));
        }
        
        /**
         * All about meta boxes in backend
         */
        static public function add_meta_boxes() {
            add_meta_box('ad_info', __('Ad Information', ET_DOMAIN) , array(
                'CE_Ads',
                'meta_view'
            ) , self::POST_TYPE, 'normal', 'high');
            
            add_meta_box('post_carousel', __('Settings for carousel', ET_DOMAIN) , array(
                'CE_Ads',
                'carousel_settings'
            ) , 'post', 'normal', 'low');
            
            add_meta_box('post_carousel', __('Settings for carousel', ET_DOMAIN) , array(
                'CE_Ads',
                'carousel_settings'
            ) , self::POST_TYPE, 'normal', 'low');
        }
        
        public static function add_meta_script() {
            
            global $wp_scripts, $post;
            $ui = $wp_scripts->query('jquery-ui-core');
            $url = "http://code.jquery.com/ui/{$ui->ver}/themes/smoothness/jquery-ui.css";
            wp_enqueue_style('jquery-ui-redmond', $url, false, $ui->ver);
            
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-autocomplete');
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_style('jquery-ui-datepicker');
            
            // wp_enqueue_script('et-googlemap-api');
            wp_enqueue_script('gmap');
            
            wp_enqueue_script('edit-ad', TEMPLATEURL . '/js/admin/edit-ad.js', array(
                'jquery',
                'jquery-ui-autocomplete',
                'jquery-ui-datepicker'
            ));
            
            $replace = array(
                'd' => 'dd',
                 // two digi date
                'j' => 'd',
                 // no leading zero date
                'm' => 'mm',
                 // two digi month
                'n' => 'm',
                 // no leading zero month
                'l' => 'DD',
                 // date name long
                'D' => 'D',
                 // date name short
                'F' => 'MM',
                 // month name long
                'M' => 'M',
                 // month name shỏrt
                'Y' => 'yy',
                 // 4 digits year
                'y' => 'y',
            );
            $date_format = str_replace(array_keys($replace) , array_values($replace) , get_option('date_format'));
            
            wp_localize_script('edit-ad', 'edit_ad', array(
                'dateFormat' => $date_format,
                'ce_ad_cat' => CE_AD_CAT,
                'regular_price' => CE_ET_PRICE,
                '_et_featured' => ET_FEATURED
            ));
        }
        
        public static function wp_dropdown_users($output) {
            global $post, $user_ID;
            
            /**
             * remove filter to prevent loop
             */
            remove_filter('wp_dropdown_users', array(
                'CE_Ads',
                'wp_dropdown_users'
            ));
            
            $output = wp_dropdown_users(array(
                'who' => 'sellers',
                'name' => 'post_author_override',
                'selected' => empty($post->ID) ? $user_ID : $post->post_author,
                'include_selected' => true,
                'echo' => false
            ));
            
            return $output;
        }
        
        static public function meta_view($post) {
            $payment_package = et_get_payment_plans();
            $currency = ET_Payment::get_currency();
            
            $ad = (array)self::convert($post);
?>
		<table class="form-table ad-info">
			<input type="hidden" name="_et_nonce" value="<?php
            echo wp_create_nonce(self::NONCE) ?>">
			<tbody>
			<tr valign="top">
				<th scope="row"><label for=""><strong><?php
            _e("Packages:", ET_DOMAIN); ?></strong></label></th>
				<td>
					<?php
            foreach ($payment_package as $key => $plan) { ?>
					<p>
						<input data-duration="2" class="ad-package" type="radio" id="et_ad_package_<?php
                echo $plan['ID'] ?>" name="et_payment_package" value="<?php
                echo $plan['ID'] ?>" <?php
                checked($plan['ID'], $ad['et_payment_package'], true); ?>>
						<label for="et_ad_package_<?php
                echo $plan['ID'] ?>"><strong><?php
                echo $plan['post_title'] ?>  <?php
                echo et_get_price_format($plan[CE_ET_PRICE]) ?></strong> - <?php
                echo $plan['backend_text'] ?></label>
					</p>
					<?php
            } ?>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="<?php
            echo ET_FEATURED; ?>"><strong><?php
            _e("Featured Ad:", ET_DOMAIN); ?></strong></label></th>
				<td>
					<input type="hidden" value="0" name="<?php
            echo ET_FEATURED; ?>" />
					<input value="1"  name="<?php
            echo ET_FEATURED; ?>" type="checkbox" id="<?php
            echo ET_FEATURED; ?>" <?php
            checked(1, $ad[ET_FEATURED], true); ?> >
					<p class="description"><label for="<?php
            echo ET_FEATURED; ?>" ><?php
            _e("Make this ad featured in listing.", ET_DOMAIN); ?></label></p>
				</td>

			</tr>

			<tr valign="top">
				<th scope="row"><label for="et_expired_date"><strong><?php
            _e("Expired Date:", ET_DOMAIN); ?></strong></label></th>
				<td>
					<input  name="et_expired_date" type="text" id="et_expired_date" value="<?php
            echo $ad['et_expired_date'] ?>" class="regular-text">
					<p class="description"><?php
            _e("Specify a date when ad will be archived.", ET_DOMAIN); ?></p>
				</td>

			</tr>
			<?php
            if (class_exists('CE_MARKET')): ?>
			<tr valign="top">
				<th scope="row"><label for="<?php
                echo CE_ET_PRICE; ?>"><strong><?php
                _e("Price:", ET_DOMAIN); ?></strong></label></th>
				<td>
					<input name="<?php
                echo CE_ET_PRICE; ?>" type="text" id="<?php
                echo CE_ET_PRICE; ?>" value="<?php
                echo $ad[CE_ET_PRICE] ?>" class="regular-text number"> <?php
                echo $currency['icon'] ?>
					<p class="description price"><span><?php
                echo number_format((double)$ad[CE_ET_PRICE], 2) ?></span><?php
                echo $currency['icon'] ?></p>
				</td>
			</tr>
			<?php
            endif; ?>
			<tr valign="top">
				<th scope="row"><label for="et_full_location"><strong><?php
            _e("Adress details:", ET_DOMAIN); ?></strong> </label></th>
				<td>
					<input name="et_full_location" type="text" id="et_full_location" value="<?php
            echo $ad['et_full_location'] ?>" class="regular-text ltr">
					<p class="description"><?php
            _e("This address is used for contact purpose.", ET_DOMAIN); ?></p>
					<input name="et_location_lat" type="hidden" id="et_location_lat" value="<?php
            echo $ad['et_location_lat'] ?>" class="regular-text ltr">
					<input name="et_location_lng" type="hidden" id="et_location_lng" value="<?php
            echo $ad['et_location_lng'] ?>" class="regular-text ltr">
				</td>
			</tr>
			<?php
            do_action('et_meta_fields', $ad); ?>

			<tr valign="top">
				<?php
            $user = get_user_by('id', $ad['post_author']); ?>
				<th scope="row"><label for="seller"><strong><?php
            _e("Assign to a seller:", ET_DOMAIN); ?></strong> </label></th>
				<td>
					<input name="seller" type="text" id="seller" value="<?php
            echo $user->display_name; ?>" class="regular-text ltr">
					<input type="hidden" id="et_author" name="post_author_override" value="<?php
            echo $post->post_author ?>">
					<p class="description"><?php
            _e("Choose a seller to make him become the author of this item.", ET_DOMAIN); ?></p>
				</td>
			</tr>
			</tbody>
		</table>
		<?php
            
            // print users list
            $users = get_users(array(
                'roles' => array(
                    'administrator',
                    'seller'
                )
            ));
            $template = array();
            foreach ($users as $user) {
                $template[] = array(
                    'value' => $user->ID,
                    'label' => $user->display_name
                );
            }
?>
		<script type="text/template" id="et_sellers">
			<?php
            echo json_encode($template); ?>
		</script>
	<?php
        }
        
        static public function carousel_settings($post) {
            $ad = $post;
            $image = get_post_meta($post->ID, 'ce_carousel_image', true);
            $button = get_post_meta($post->ID, '_ce_button_text', true);
            $caption = get_post_meta($post->ID, 'ce_caption_text', true);
            $link = get_post_meta($post->ID, 'ce_carousel_link', true);
?>
		<table class="form-table ad-info">
			<input type="hidden" name="_et_nonce" value="<?php
            echo wp_create_nonce(self::NONCE) ?>">
			<tbody>
			<tr valign="top">
				<td></td>
				<td>
					<p class="description"><?php
            _e("If you use this post in carousel, you can setting for it here, if not, you can skip it.", ET_DOMAIN); ?></p>
				</td>

			</tr>
			<tr valign="top">
				<th scope="row"><label for="ce_caption_text"><strong><?php
            _e("Caption text:", ET_DOMAIN); ?></strong></label></th>
				<td>
					<textarea cols="10" rows="3"  name="ce_caption_text" type="text" id="ce_caption_text" class="large-text"><?php
            echo $caption ?></textarea>
					<p class="description"><?php
            _e("Enter the text for the caption in carousel", ET_DOMAIN); ?></p>
				</td>

			</tr>

			<tr valign="top">
				<th scope="row"><label for="_ce_button_text"><strong><?php
            _e("Button text:", ET_DOMAIN); ?></strong></label></th>
				<td>
					<input  name="_ce_button_text" type="text" id="_ce_button_text" value="<?php
            echo $button ?>" class="large-text">
					<p class="description"><?php
            _e("Enter the text for the button in carousel", ET_DOMAIN); ?></p>
				</td>

			</tr>

			<tr valign="top">
				<th scope="row"><label for="ce_carousel_link"><strong><?php
            _e("Slide link:", ET_DOMAIN); ?></strong></label></th>
				<td>
					<input class="large-text" value="<?php
            echo $link ?>"  name="ce_carousel_link" type="text" id="ce_carousel_link" >
					<p class="description"><label for="ce_carousel_link" ><?php
            _e("You can leave it blank to use post featured image.", ET_DOMAIN); ?></label></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="ce_carousel_image"><strong><?php
            _e("Image for carousel:", ET_DOMAIN); ?></strong></label></th>
				<td>
					<input class="large-text" value="<?php
            echo $image; ?>"  name="ce_carousel_image" type="text" id="ce_carousel_image" >
					<p class="description"><label for="ce_carousel_image" ><?php
            _e("You can leave it blank to use post featured image.", ET_DOMAIN); ?></label></p>
				</td>

			</tr>


			</tbody>
		</table>
	<?php
        }
        
        static public function save_meta_fields($post_id) {
            
            if (!isset($_POST['_et_nonce']) || !wp_verify_nonce($_POST['_et_nonce'], self::NONCE)) return;
            unset($_POST['_et_nonce']);
            
            update_post_meta($post_id, 'ce_carousel_image', $_POST['ce_carousel_image']);
            update_post_meta($post_id, '_ce_button_text', $_POST['_ce_button_text']);
            update_post_meta($post_id, 'ce_caption_text', $_POST['ce_caption_text']);
            update_post_meta($post_id, 'ce_carousel_link', $_POST['ce_carousel_link']);
            
            // cancel if current post isn't job
            if (!isset($_POST['post_type']) || $_POST['post_type'] != self::POST_TYPE) return;
            
            $ce_ad = CE_Ads::get_instance();
            
            if ($_POST['et_expired_date'] == '') {
                unset($_POST['et_expired_date']);
            } else {
                $_POST['et_expired_date'] = date('Y-m-d h:i:s', strtotime($_POST['et_expired_date']));
            }
            
            $ce_ad->update_meta_fields($post_id, $_POST);
            
            $order = get_post_meta($post_id, 'et_ad_order', true);
            if ($order) {
                
                /**
                 * update order status
                 */
                wp_update_post(array(
                    'ID' => $order,
                    'post_status' => 'publish'
                ));
            }
        }
    }
    
    function ce_get_price_format($amount, $style = '') {
        
        $option = new CE_Options();
        $currency = $option->get_option('et_currency_sign');
        $align = $option->get_option('et_currency_align');
        $price_format = $option->get_option('et_currency_format');
        
        $format = '%1$s';
        
        switch ($style) {
            case 'sup':
                $format = '<sup ' . Schema::Offer("priceCurrency") . '>%s</sup>';
                break;

            case 'sub':
                $format = '<sub ' . Schema::Offer("priceCurrency") . '>%s</sub>';
                break;

            default:
                $format = '<var ' . Schema::Offer("priceCurrency") . '>%s</var>';
                break;
        }
        
        $decimal = get_theme_mod('et_decimal', 2);
        $decimal_point = ($price_format != 2) ? "." : ",";
        $thousand_sep = ($price_format != 2) ? "," : ".";
        
        if ($align != "right") {
            $format = $format . '<var ' . Schema::Offer("price") . '>%s</var>';
            return sprintf($format, $currency, number_format((double)$amount, $decimal, $decimal_point, $thousand_sep));
        } else {
            $format = '<var ' . Schema::Offer("price") . '>%s</var>' . $format;
            return sprintf($format, number_format((double)$amount, $decimal, $decimal_point, $thousand_sep) , $currency);
        }
    }
    
    