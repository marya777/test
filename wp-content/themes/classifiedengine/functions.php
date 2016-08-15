<?php
define('TEMPLATEURL', get_template_directory_uri());

// change this to 'production' when publishing the theme, to use minified scripts & styles instead
define('ENGINE_ENVIRONMENT', 'development');
define('ENV_PRODUCTION', false);

define("ET_UPDATE_PATH", "http://forum.enginethemes.com/?do=product-update");

include_once dirname(__FILE__) . '/define/ver2.php';


if (!defined('ET_URL')) define('ET_URL', 'http://www.enginethemes.com/');

if (!defined('ET_CONTENT_DIR')) define('ET_CONTENT_DIR', WP_CONTENT_DIR . '/et-content/');

if (!defined('THEME_CONTENT_DIR ')) define('THEME_CONTENT_DIR', WP_CONTENT_DIR . '/et-content' . '/classifiedengine');
if (!defined('THEME_CONTENT_URL')) define('THEME_CONTENT_URL', content_url() . '/et-content' . '/classifiedengine');

if (!defined('THEME_LANGUAGE_PATH')) define('THEME_LANGUAGE_PATH', THEME_CONTENT_DIR . '/lang/');

define('ET_LANGUAGE_PATH', THEME_CONTENT_DIR . '/lang/');

if (!defined('ET_CSS_PATH')) define('ET_CSS_PATH', THEME_CONTENT_DIR . '/css');

//require_once dirname(__FILE__).'/socials/fb-sdk/autoload.php';

require_once dirname(__FILE__) . '/includes/index.php';

require_once dirname(__FILE__) . '/admin/index.php';

// for mobile
require_once get_template_directory() . '/mobile/functions.php';

//google captcha class
require_once get_template_directory() . '/includes/google-captcha.php';

require_once get_template_directory() . '/includes/social_sdk.php';

require_once get_template_directory() . '/includes/ce_guide.php';

if (class_exists('WooCommerce')) {
    require_once get_template_directory() . '/includes/wc_integrate/WC_Integrate.php';
}

if (!defined('MINIFY_MIN_DIR')) {
    define('MINIFY_MIN_DIR', get_template_directory() . '/min/');
}

/**
 * Classified Engine main class
 * init required things
 * add control in front-end
 */
class ET_ClassifiedEngine extends ET_Base
{
    static $use_mininfy = false;
    static $minify_link = null;
    static $use_permalink = true;
    
    public $scripts;
    public $styles;
    
    function __construct() {
        parent::__construct();
        
        $this->after_setup_theme_init();
        
        /**
         * theme init
         */
        $this->add_action('init', 'ce_init');
        $this->add_action('init', 'ce_payment_postback_handle');
        
        /**
         * filter page title
         */
        $this->add_filter('wp_title', 'wp_title', 10, 2);
        
        /**
         * add class to body
         */
        $this->add_filter('body_class', 'body_class');
        
        /**
         * control theme template redirect
         */
        $this->add_action('template_redirect', 'template_redirect');
        
        /**
         * filter user avatar, replace by user upload avatar image
         */
        $this->add_filter('get_avatar', 'get_avatar', 10, 5);
        
        /**
         * filter post thumnail image, if not set use no image
         */
        $this->add_filter('post_thumbnail_html', 'post_thumbnail_html', 10, 5);
        
        /*
         * register widget and sidebar
        */
        $this->add_action('widgets_init', 'widgets_init');
        
        /**
         * enqueue front end scripts
         */
        $this->add_action('wp_enqueue_scripts', 'on_add_scripts');
        
        /**
         * enqueue front end styles
         */
        $this->add_action('wp_print_styles', 'on_add_styles', 10);
        
        /**
         * init js view
         */
        $this->add_action('wp_footer', 'footer_script', 100);
        
        /**
         * init js view
         */
        $this->add_action('wp_head', 'wp_head');
        
        /**
         * add query vars
         */
        $this->add_filter('query_vars', 'add_query_vars');
        
        $this->add_filter('wp_mail', 'wp_mail');
        
        $this->add_filter('excerpt_lengt', 'ce_limit_exceprt');
        
        $this->add_filter('et_retrieve_password_message', 'filter_retrieve_password_message', 10, 3);
        
        /**
         * localize jquery validator
         */
        $this->add_action('wp_footer', 'localize_validator', 200);
        $this->add_action('admin_print_footer_scripts', 'localize_validator', 200);
        
        /**
         * add action admin menu prevent seller enter admin area
         */
        $this->add_action('admin_menu', 'redirect_seller');
        $this->add_action("wp_before_admin_bar_render", "admin_bar_menu");
        
        //$this->add_action('wp_head', 'block_ie_redirect');
        
        remove_action('wp_enqueue_scripts', 'et_deregister_jquery');
        
        // self::$use_permalink =   $wp_rewrite->using_permalink();
        
        // custom template feed
        remove_all_actions('do_feed_rss2');
        $this->add_filter('rss2_ns', 'ce_feed_add_namespace');
        $this->add_action('do_feed_rss2', 'custom_template_feed', 10, 1);
        $this->add_action('rss2_item', 'add_element_to_feed_item');
        
        /**
         * action to update user profile in backend
         *
         */
        $this->add_action('edit_user_profile_update', 'update_seller_profile');
        $this->add_action('personal_options_update', 'update_seller_profile');
        
        // show profile user in backend
        $this->add_action('show_user_profile', 'show_seller_profile');
        $this->add_action('edit_user_profile', 'show_seller_profile');
        
        // show admin notices and url link to wizard after active themes.
        $this->add_action('admin_notices', 'notice_after_installing_theme');
        
        /**
         * pre filter get page link to get transient
         */
        $this->add_filter('et_pre_filter_get_page_link', 'pre_filter_get_page_link', 10, 2);
        
        /**
         * do action save post to update page template transient
         */
        $this->add_action('save_post', 'update_transient_page_template');
        
        /**
         * delete page template transient when update permalink
         */
        $this->add_filter('admin_head', 'remove_page_template_transient');
        
        self::$use_mininfy = get_theme_mod('ce_minify', 0);
        if (current_user_can('manage_options') && isset($_COOKIE['et-customizer']) && $_COOKIE['et-customizer'] == true) {
            self::$use_mininfy = false;
        }
        
        // new ET_TwitterAuth();
        // new ET_FaceAuth();
        
        // update_user_meta( 51, 'et_facebook_id',  "100001337146037" );
        
        $this->add_filter('excerpt_length', 'custom_excerpt_length', 999);
        
        /**
         * add hook control ad with WPML
         */
        $this->add_action('ce_insert_ad', 'insert_ad');
        
        $this->add_action('template_include', 'author_reviews_rewrite_template');
        
        if (class_exists("WooCommerce")) {
            WC_Integrate::getInstance()->add_hook();
        }
    }
    
    /**
     * Run action for after_setup_theme default.
     * @since 1.8.3
     */
    
    function after_setup_theme_init() {
        
        register_nav_menu('et_header', __("Header menu", ET_DOMAIN));
        register_nav_menu('et_mobile_header', __("Mobile Header menu", ET_DOMAIN));
        
        add_theme_support('woocommerce');
        add_theme_support('post-thumbnails', array(
            'post',
            CE_AD_POSTTYPE
        ));
        add_theme_support('infinite-scroll', array(
            'container' => 'publish_list',
        ));
        
        add_image_size('ad-thumbnail-list', '130', '130', true);
        add_image_size('ad-thumbnail-grid', '240', '185', true);
        
        add_image_size('ad-thumbnail-mobile', '85', '85', true);
        
        add_image_size('ad-slide', '710', '300', false);
        add_image_size('ad-slide-large', '1050', '563', false);
        
        add_image_size('ce_slide', '1050', '390', true);
        
        add_filter('widget_text', 'do_shortcode');
        
        // setup a default copy right text after setup theme
        $ce_options = new CE_Options();
        if ($ce_options->et_copyright == '' && !get_option('set_copyright', false)) {
            $ce_options->update_option('et_copyright', '<span class="enginethemes"><a href="http://www.enginethemes.com/themes/classifiedengine/" >Classified Ad Software - Powered by WordPress</span>');
            update_option('set_copyright', true);
        }
    }
    
    function ce_init() {
        global $ce_config;
        $ce_config = apply_filters('et_filter_ce_config', array());
        $ce_config = wp_parse_args($ce_config, array(
            'number_of_carousel' => get_theme_mod('ce_number_of_carousel', 15) ,
            'max_cat' => get_theme_mod('ce_number_of_category', '') ,
            'use_infinite_scroll' => get_theme_mod('ce_use_infinite_scroll', 0) ,
            'default_grid' => get_theme_mod('ce_body_view', 'grid')
        ));
        
        $detector = new ET_MobileDetect();
        
        // disable admin bar if user can not manage options
        if (!current_user_can('manage_options') || $detector->isMobile()):
            show_admin_bar(false);
        endif;
        
        /**
         * register post type
         */
        CE_Ads::register();
        ET_PaymentPackage::register();
        
        /**
         * register taxonomy
         */
        ET_AdCatergory::register();
        ET_AdLocation::register();
        
        /**
         * register user role seller
         */
        ET_Seller::register();
        
        /**
         * init ajax action for theme
         */
        new CE_Ajax();
        new CE_AjaxAd();
        new CE_AjaxSeller();
        
        new CE_Schedule();
        
        /**
         * register mutual use script
         */
        $http = 'http';
        if (is_ssl()) $http = 'https';
        wp_register_script('et-googlemap-api', $http . '://maps.googleapis.com/maps/api/js?sensor=true', '3.0', true);
        
        // global $wp_rewrite;
        if (self::$use_mininfy) self::$minify_link = et_get_page_link('min');
        
        /**
         * enqueue classified engine js
         */
        if (self::$use_mininfy && !is_admin()) {
            wp_register_script('ce', add_query_arg(array(
                'g' => 'wp-includes'
            ) , self::$minify_link) , array(
                'jquery',
                'underscore',
                'backbone',
                'plupload-all'
            ) , CE_VERSION, true);
        } else {
            wp_register_script('gmap', TEMPLATEURL . '/js/lib/gmaps.js', array(
                'et-googlemap-api'
            ) , CE_VERSION, true);
            wp_register_script('ce', TEMPLATEURL . '/js/classified-engine.js', array(
                'jquery',
                'underscore',
                'backbone'
            ) , CE_VERSION, true);
        }
        
        wp_register_script('jquery.validator', TEMPLATEURL . '/js/lib/jquery.validate.min.js', array(
            'jquery'
        ) , CE_VERSION, true);
        
        /**
         * check option create content directory for classified theme
         */
        if (!get_option('ce_installed', false)) {
            et_create_content_directory();
            update_option('ce_installed', 1);
        }
        
        /**
         * override author link
         */
        
        global $wp_rewrite;
        if ($wp_rewrite->using_permalinks()) {
            $wp_rewrite->author_base = apply_filters('ce_filter_seller_url', ET_Seller::$seller_url);
            $wp_rewrite->author_structure = '/' . $wp_rewrite->author_base . '/%author%';
        }
        
        /**
         * add review to end point
         */
        $review = 'review';
        add_rewrite_rule($wp_rewrite->author_base . '/([^/]+)/$review(/(.*))?/?$', 'index.php?author_name=$matches[1]&$review=$matches[3]', 'top');
        
        add_rewrite_endpoint($review, EP_AUTHORS | EP_PAGES);
    }
    
    function ce_payment_postback_handle() {
        
        /**
         * only paypal using this, and skip if wooCommerce actived
         */
        if (!class_exists('WooCommerce')) {
            if (!empty($_GET['paypalListener']) && $_GET['paypalListener'] == 'paypal_standard_IPN') {
                $paypal = new ET_Paypal();
                $paypal->check_ipn_response();
            }
        }
    }
    
    /**
     * Creates a nicely formatted and more specific title element text for output
     * in head of document, based on current view.
     *
     * @param string $title Default title text for current view.
     * @param string $sep Optional separator.
     * @return string The filtered title.
     */
    function wp_title($title, $sep) {
        global $paged, $page;
        
        if (is_feed()) return $title;
        
        // Add the site name.
        $title.= get_bloginfo('name');
        
        // Add the site description for the home/front page.
        $site_description = get_bloginfo('description', 'display');
        if ($site_description && (is_home() || is_front_page())) $title = "$title $sep $site_description";
        
        // Add a page number if necessary.
        if ($paged >= 2 || $page >= 2) $title = "$title $sep " . sprintf(__('Page %s', ET_DOMAIN) , max($paged, $page));
        
        return $title;
    }
    
    /**
     * filter body class to add required,
     * check cookie add class control list view
     */
    function body_class($class) {
        
        global $ce_config;
        
        /**
         * remove class search in body class
         */
        if (is_search()) unset($class[0]);
        
        if (is_singular(CE_AD_POSTTYPE)) {
            $class[1] = 'single-classified';
            return $class;
        }
        
        if (is_page_template('page-sellers.php')) return $class;
        
        /**
         * check cookie to set class for list view
         */
        $list_view = isset($_COOKIE['ce_list_view']) ? $_COOKIE['ce_list_view'] : $ce_config['default_grid'];
        if (is_author() || (isset($_GET['section']) && $_GET['section'] == 'favourites')) {
            $class[] = 'body-list-view';
        } else {
            if ($list_view == 'grid') $class[] = 'body-grid-view';
            else $class[] = 'body-list-view';
        }
        
        return $class;
    }
    
    /**
     * control theme template redirect
     */
    function template_redirect() {
        global $user_ID;
        if (!$user_ID) {
            if (is_page_template('page-account-listing.php') || is_page_template('page-account-profile.php')) {
                wp_redirect(home_url());
            }
        }
        
        /**
         * update ad location cookie
         */
        
        if (is_tax('ad_location')) {
            global $wp_query;
            $object = $wp_query->get_queried_object();
            setcookie('et_location', $object->slug, time() + 3600 * 24 * 30, "/");
        }
        $location = get_query_var('location');
        
        if ($location == 'alllocation') {
            
            setcookie('et_location', '', time() + 3600 * 24 * 30, "/");
        } else if (!empty($location)) {
            
            setcookie('et_location', $location, time() + 3600 * 24 * 30, "/");
        }
        
        /**
         * update cookie view for ad
         * move to function et_count_post_views of core
         * @since 1.8.2
         */
        
        // if(is_singular( 'ad' )) {
        //  global $post;
        
        //CE_Ads::update_post_views($post);
        
        // }
        
        
    }
    
    /**
     * filter wp avatar
     */
    function get_avatar($avatar, $id_or_email, $size, $default, $alt) {
        
        $seller = ET_Seller::get_instance();
        $profile_picture = $seller->get_avatar($id_or_email, $size);
        
        /**
         * overide $default by profile picture
         */
        if ($profile_picture != '') {
            $default = $profile_picture;
        }
        
        if (false === $alt) $safe_alt = '';
        else $safe_alt = esc_attr($alt);
        
        $avatar = "<img alt='{$safe_alt}' src='{$default}' class='avatar avatar-{$size} photo avatar-default' height='{$size}' width='{$size}' />";
        
        return $avatar;
    }
    
    /**
     * filter post thumnail image, replace no_image.gif if ad didnt have featured image
     */
    function post_thumbnail_html($html, $post_id, $post_thumbnail_id, $size, $attr) {
        global $ce_config, $post;
        
        // if($size !== '') return $html;
        if ($post && $post->post_type !== CE_AD_POSTTYPE) return $html;
        
        $list_view = isset($_COOKIE['ce_list_view']) ? $_COOKIE['ce_list_view'] : $ce_config['default_grid'];
        
        if (is_single()) {
            $list_view = 'grid';
        }
        
        if (is_author()) {
            $list_view = 'list';
        }
        
        if ($list_view == 'grid') {
            $size = 'ad-thumbnail-grid';
            $src = TEMPLATEURL . '/img/no_image.gif';
        } else {
            $size = 'ad-thumbnail-list';
            $src = TEMPLATEURL . '/img/no_image-list.gif';
        }
        
        global $isMobile;
        if ($isMobile && $html != '') {
            return $html;
        }
        
        if ($html == '') {
            $html = '<img ' . Schema::Product('image') . ' data-grid="' . TEMPLATEURL . '/img/no_image.gif" data-list="' . TEMPLATEURL . '/img/no_image-list.gif"  src="' . $src . '" class="attachment-ad-thumbnail wp-post-image" alt="">';
        } else {
            $alt = trim(strip_tags(get_post_meta($post_thumbnail_id, '_wp_attachment_image_alt', true)));
            $title = get_the_title($post_thumbnail_id);
            $src_list = wp_get_attachment_image_src($post_thumbnail_id, 'ad-thumbnail-list');
            $src_grid = wp_get_attachment_image_src($post_thumbnail_id, 'ad-thumbnail-grid');
            if ($size == 'ad-thumbnail-list') {
                $src = $src_list;
            } else {
                $src = $src_grid;
            }
            if (!et_is_mobile()) $html = '<img ' . Schema::Product('image') . ' data-list="' . $src_list[0] . '" data-grid="' . $src_grid[0] . '" src="' . $src[0] . '"  data-src="' . $src[0] . '" class="attachment-ad-thumbnail wp-post-image" alt="' . $alt . '" title="' . $title . '">';
            else $html = '<img ' . Schema::Product('image') . ' data-list="' . $src_list[0] . '" data-grid="' . $src_grid[0] . '" src="' . $src[0] . '" class="attachment-ad-thumbnail wp-post-image" alt="' . $alt . '" title="' . $title . '">';
        }
        
        return $html;
    }
    
    /**
     * register sidebar and widgets for theme
     */
    function widgets_init() {
        
        register_widget('CE_Slider_Widget');
        register_widget('CE_Categories_Widget');
        register_widget('CE_Locations_Widget');
        
        register_widget('CE_Social_Widget');
        
        // register_widget( 'CE_Price_Filter_Widget' );
        
        $handle = '';
        if (current_user_can('manage_options')) {
            $before_widget = '<aside id="%1$s" class="widget %2$s"><div class="sort-handle"><i class="fa fa-align-justify"></i></div>';
            $handle = '<div class="sort-handle"><i class="fa fa-align-justify"></i></div>';
        } else {
            
            $before_widget = '<aside id="%1$s" class="widget %2$s">';
        }
        
        register_sidebar(array(
            'name' => __('Main Sidebar', ET_DOMAIN) ,
            'id' => 'sidebar-main-left',
            'description' => __('Drop widgets here to position them at the left or right side of your Homepage', ET_DOMAIN) ,
            'before_widget' => $before_widget,
            'after_widget' => "</aside>",
            'before_title' => '<div class="widget-title customize_heading">',
            'after_title' => '</div>',
        ));
        register_sidebar(array(
            'name' => __('Homepage Top Sidebar', ET_DOMAIN) ,
            'id' => 'sidebar-home-top',
            'description' => __('Drop widgets here to position them at the top of your Homepage', ET_DOMAIN) ,
            'before_widget' => $before_widget,
            'after_widget' => "</aside>",
            'before_title' => '<div class="widget-title customize_heading">',
            'after_title' => '</div>',
        ));
        
        register_sidebar(array(
            'name' => __('Homepage Bottom Sidebar', ET_DOMAIN) ,
            'id' => 'sidebar-home-bottom',
            'description' => __('Drop widgets here to position them at the bottom of your Homepage', ET_DOMAIN) ,
            'before_widget' => $before_widget,
            'after_widget' => "</aside>",
            'before_title' => '<div class="widget-title customize_heading">',
            'after_title' => '</div>',
        ));
        
        register_sidebar(array(
            'name' => __('Post Ad Sidebar', ET_DOMAIN) ,
            'id' => 'sidebar-post-ad',
            'description' => __('Drop widgets here to position them at the right side of Post-an-Ad page', ET_DOMAIN) ,
            'before_widget' => '<div id="%1$s" class="widget-area %2$s"><span class="arrow-right"></span>' . $handle,
            'after_widget' => "</div>",
            'before_title' => '<div class="widget-title customize_heading">',
            'after_title' => '</div>',
        ));
        
        register_sidebar(array(
            
            'name' => __('Sellers List Sidebar', ET_DOMAIN) ,
            'id' => 'sidebar-list-seller',
            'description' => __("Drop widgets here to position them at the left side of Sellers' list page", ET_DOMAIN) ,
            'before_widget' => '<div id="%1$s" class="widget-area %2$s"><span class="arrow-right"></span>' . $handle,
            'after_widget' => "</div>",
            'before_title' => '<div class="widget-title customize_heading">',
            'after_title' => '</div>',
        ));
        
        register_sidebar(array(
            'name' => __('Seller Profile Sidebar', ET_DOMAIN) ,
            'id' => 'sidebar-seller-profile',
            'description' => __('Drop widgets here to position them at the right side of Seller Profile page', ET_DOMAIN) ,
            'before_widget' => '<div id="%1$s" class="widget-area %2$s"><span class="arrow-right"></span>' . $handle,
            'after_widget' => "</div>",
            'before_title' => '<div class="widget-title customize_heading">',
            'after_title' => '</div>',
        ));
        
        register_sidebar(array(
            'name' => __('Blog Sidebar', ET_DOMAIN) ,
            'id' => 'sidebar-blog',
            'description' => __('Drop widgets here to position them at the left or right side of your Blog page', ET_DOMAIN) ,
            'before_widget' => $before_widget,
            'after_widget' => "</aside>",
            'before_title' => '<h3 class="widget-title customize_heading">',
            'after_title' => '</h3>',
        ));
        
        // register_sidebar ( array(
        //  'name' => __( 'Page Sidebar', ET_DOMAIN ),
        //  'id' => 'sidebar-page',
        //  'description' => __( 'Drop widgets here to position them at the right sight in page template.', ET_DOMAIN ),
        //  'before_widget' => '<div id="%1$s" class="widget-area %2$s"><span class="arrow-right"></span>'.$handle,
        //  'after_widget' => "</div>",
        //  'before_title' => '<div class="widget-title customize_heading">',
        //  'after_title' => '</div>',
        //  )
        // );
        
        
        
        /**
         * sidebar footer 1
         */
        register_sidebar(array(
            'name' => __('Footer 1', ET_DOMAIN) ,
            'id' => 'ce_footer_1',
            'description' => '',
            'before_widget' => '<div class="widget widget_text">',
            'after_widget' => '</div>',
            'before_title' => '<h3 class="widgettitle">',
            'after_title' => '</h3>'
        ));
        
        register_sidebar(array(
            'name' => __('Footer 2', ET_DOMAIN) ,
            'id' => 'ce_footer_2',
            'description' => '',
            'before_widget' => '<div class="body-grid-view section-link">',
            'after_widget' => '</div>',
            'before_title' => '<h3>',
            'after_title' => '</h3>'
        ));
        register_sidebar(array(
            'name' => __('Mobile header', ET_DOMAIN) ,
            'id' => 'mobile_header',
            'description' => '',
            'before_widget' => '',
            'after_widget' => '',
            'before_title' => '',
            'after_title' => ''
        ));
        register_sidebar(array(
            'name' => __('Mobile Footer', ET_DOMAIN) ,
            'id' => 'mobile_footer',
            'description' => '',
            'before_widget' => '',
            'after_widget' => '',
            'before_title' => '',
            'after_title' => ''
        ));
    }
    
    /**
     * add scripts in front-end
     */
    function on_add_scripts() {
        global $user_ID;
        
        if ($this->is_mobile()) {
            do_action('ce_on_add_scripts_mobile');
            return false;
        }
        $this->add_existed_script('et-googlemap-api');
        $this->add_existed_script('jquery');
        $this->scripts = array(
            array(
                'jquery' => home_url('/wp-includes/js/jquery/jquery.js')
            )
        );
        
        /**
         *  CE WP lib
         */
        if (self::$use_mininfy) {
            $link = add_query_arg(array(
                'g' => 'wp-includes'
            ) , self::$minify_link);
            $this->add_script('wp-lib', $link, array(
                'jquery'
            ) , CE_VERSION, false);
            
            // $this->add_existed_script('ce');
            array_push($this->scripts, array(
                'wp-lib' => $link
            ));
        } else {
            $this->add_script('inview', TEMPLATEURL . "/mobile/js/jquery-inview.min.js");
            array_push($this->scripts, array(
                'inview' => TEMPLATEURL . "/mobile/js/jquery-inview.min.js"
            ));
        }
        
        /**
         * enqueue auto complete script
         */
        if (is_page_template('page-account-profile.php') || is_page_template('page-account-listing.php') || is_page_template('page-post-ad.php') || current_user_can('manage_options') || is_single() || is_author()) {
            $this->add_existed_script('jquery-ui-autocomplete');
            array_push($this->scripts, array(
                'jquery-ui-autocomplete' => '//cdnjs.cloudflare.com/ajax/libs/jquery.devbridge-autocomplete/1.2.7/jquery.devbridge-autocomplete.min.js'
            ));
        }
        
        /**
         * jquery validator script
         */
        
        if (!self::$use_mininfy) {
            
            $this->add_existed_script('plupload-all');
            $this->add_existed_script('jquery.validator');
            
            $this->add_script('carouFredSel', TEMPLATEURL . '/js/lib/jquery.carouFredSel-6.2.0.js', array(
                'jquery'
            ) , '6.2.0', true);
            $this->add_script('chosen', TEMPLATEURL . '/js/lib/chosen.js', array(
                'jquery'
            ) , '1.1.0', true);
            $this->add_script('bx_slider', TEMPLATEURL . '/js/bx-slider.js', array(
                'jquery'
            ) , CE_VERSION, true);
            
            $this->add_script('superfish', TEMPLATEURL . '/js/superfish.min.js', array(
                'jquery'
            ) , CE_VERSION, true);
        }
        
        /**
         * ce FRONT-END control script
         */
        if (self::$use_mininfy) {
            $link = add_query_arg(array(
                'g' => 'front'
            ) , self::$minify_link);
            $this->add_script('front', $link, array() , CE_VERSION, true);
        } else {
            $this->add_script('front', TEMPLATEURL . '/js/front.js', array(
                'jquery',
                'underscore',
                'backbone',
                'plupload-all',
                'ce'
            ) , CE_VERSION, true);
            $link = TEMPLATEURL . '/js/front.js';
        }
        
        array_push($this->scripts, array(
            'front' => $link
        ));
        
        /**
         * add widget control for admin in front-end
         */
        if (current_user_can('manage_options')) {
            if (self::$use_mininfy) {
                $link = add_query_arg(array(
                    'g' => 'sidebar'
                ) , self::$minify_link);
                $this->add_script('sidebar-widget', $link, array(
                    'jquery-ui-sortable'
                ) , CE_VERSION, true);
            } else {
                $this->add_script('sidebar-widget', TEMPLATEURL . '/js/sidebar-widget.js', array(
                    'jquery',
                    'jquery-ui-sortable',
                    'underscore',
                    'backbone',
                    'ce',
                    'front'
                ) , CE_VERSION, true);
                $link = TEMPLATEURL . '/js/sidebar-widget.js';
            }
            
            array_push($this->scripts, array(
                'sidebar-widget' => $link
            ));
        }
        
        /**
         * home page script
         */
        if (is_home() || is_tax() || is_author() || is_search()) {
            if (self::$use_mininfy) {
                $link = add_query_arg(array(
                    'g' => 'index'
                ) , self::$minify_link);
                $this->add_script('index', $link, array(
                    'wp-lib'
                ) , CE_VERSION, true);
            } else {
                $this->add_script('index', TEMPLATEURL . '/js/index.js', array(
                    'ce'
                ) , CE_VERSION, true);
                $link = TEMPLATEURL . '/js/index.js';
            }
            
            array_push($this->scripts, array(
                'index' => $link
            ));
        }
        
        /**
         * enqueue script for post ad
         */
        if (is_page_template('page-post-ad.php')) {
            if (self::$use_mininfy) {
                $link = add_query_arg(array(
                    'g' => 'post-ad'
                ) , self::$minify_link);
                $this->add_script('post-ad', $link, array(
                    'wp-lib'
                ) , CE_VERSION, true);
            } else {
                $this->add_script('post-ad', TEMPLATEURL . '/js/post-ad.js', array(
                    'jquery',
                    'jquery-ui-autocomplete',
                    'plupload-all',
                    'underscore',
                    'backbone',
                    'ce',
                    'front'
                ) , CE_VERSION, true);
                $link = TEMPLATEURL . '/js/post-ad.js';
            }
            array_push($this->scripts, array(
                'post-ad' => $link
            ));
        }
        
        /*
        /* add js for form reset passord
        */
        if (is_page_template('page-reset-password.php')) {
            if (self::$use_mininfy) {
                $link = add_query_arg(array(
                    'g' => 'reset-pass'
                ) , self::$minify_link);
                $this->add_script('reset-pass', $link, array(
                    'wp-lib'
                ) , CE_VERSION, true);
            } else {
                $this->add_script('reset-pass', TEMPLATEURL . '/js/reset-password.js', array(
                    'jquery',
                    'jquery-ui-autocomplete',
                    'underscore',
                    'backbone',
                    'ce',
                    'front'
                ) , CE_VERSION, true);
                $link = TEMPLATEURL . '/js/reset-password.js';
            }
            array_push($this->scripts, array(
                'reset-pass' => $link
            ));
        }
        
        /**
         * enqueue script for single ad
         */
        if (is_singular(CE_AD_POSTTYPE)) {
            if (self::$use_mininfy) {
                $link = add_query_arg(array(
                    'g' => 'single-ad'
                ) , self::$minify_link);
                $this->add_script('single-ad', $link, array(
                    'wp-lib'
                ) , CE_VERSION, true);
            } else {
                $this->add_script('single-ad', TEMPLATEURL . '/js/single-ad.js', array(
                    'jquery',
                    'plupload-all',
                    'underscore',
                    'backbone',
                    'ce',
                    'front'
                ) , CE_VERSION, true);
                $link = TEMPLATEURL . '/js/single-ad.js';
            }
            
            array_push($this->scripts, array(
                'single-ad' => $link
            ));
            
            $pubid = get_option( 'addthis_pubid', 'ra-54d0545d28de45aa' );
            $this->add_script('addthis-script', '//s7.addthis.com/js/300/addthis_widget.js#pubid='.$pubid, array() , CE_VERSION, true);
            $this->add_script('fancybox-script', TEMPLATEURL . '/js/jquery.fancybox.js', array() , CE_VERSION, true);
            
            // wp_enqueue_script('jqueryeasing', TEMPLATEURL . '/js/jquery.easing.1.3.min.js', false, '1.3'  ); // Easing animations script
            // wp_enqueue_script('jquerymousewheel', TEMPLATEURL . '/js/jquery.mousewheel.3.0.4.pack.js', false, '3.0.4' ); // Mouse wheel support script
            
            
        }
        
        /**
         * enqueue script for seller profile, listing
         */
        if (is_page_template('page-account-profile.php') || is_page_template('page-account-listing.php')) {
            if (self::$use_mininfy) {
                $link = add_query_arg(array(
                    'g' => 'seller-profile'
                ) , self::$minify_link);
                $this->add_script('seller-profile', $link, array(
                    'wp-lib'
                ) , CE_VERSION, true);
            } else {
                $this->add_script('seller-profile', TEMPLATEURL . '/js/seller-profile.js', array(
                    'jquery',
                    'underscore',
                    'backbone',
                    'plupload-all',
                    'ce',
                    'front'
                ) , CE_VERSION, true);
                $link = TEMPLATEURL . '/js/seller-profile.js';
            }
            
            array_push($this->scripts, array(
                'seller-profile' => $link
            ));
        }
        
        if (is_page_template('page-account-sell-order.php')) {
            if (self::$use_mininfy) {
                $link = add_query_arg(array(
                    'g' => 'profile-order-list'
                ) , self::$minify_link);
                $this->add_script('profile-order-list', $link, array(
                    'wp-lib'
                ) , CE_VERSION, true);
            } else {
                $this->add_script('profile-order-list', TEMPLATEURL . '/js/profile-order-list.js', array(
                    'jquery',
                    'underscore',
                    'backbone',
                    'plupload-all',
                    'ce',
                    'front'
                ) , CE_VERSION, true);
                $link = TEMPLATEURL . '/js/profile-order-list.js';
            }
            
            array_push($this->scripts, array(
                'seller-profile' => $link
            ));
        }
        
        if (is_page_template('page-sellers.php')) {
            if (self::$use_mininfy) {
                $link = add_query_arg(array(
                    'g' => 'seller-list'
                ) , self::$minify_link);
                $this->add_script('seller-list', $link, array(
                    'wp-lib'
                ) , CE_VERSION, true);
            } else {
                $this->add_script('seller-list', TEMPLATEURL . '/js/seller-list.js', array(
                    'jquery',
                    'underscore',
                    'backbone',
                    'ce',
                    'front'
                ) , CE_VERSION, true);
            }
            
            array_push($this->scripts, 'seller-list');
        }
        
        $this->localize_script();
        
        $option = new CE_Options;
        $google_analytics = $option->get_option('et_google_analytics');
        echo stripslashes($google_analytics);
        do_action('ce_on_add_scripts');
    }
    
    function is_mobile() {
        $detector = new ET_MobileDetect();
        $isMobile = apply_filters('ce_is_mobile', ($detector->isMobile() && !$detector->isTablet()) ? true : false);
        if ($isMobile) return true;
        return false;
    }
    
    /**
     * localize script
     */
    function localize_script() {
        global $ce_config;
        
        wp_localize_script('jquery', 'et_globals', array(
            'homeURL' => home_url() ,
            'page_template' => get_page_template_slug() ,
            'ajaxURL' => admin_url('admin-ajax.php') ,
            'logoutURL' => wp_logout_url(home_url()) ,
            'imgURL' => TEMPLATEURL . '/img',
            'loading' => __("Loading", ET_DOMAIN) ,
            'loadingImg' => '<img class="loading loading-wheel" src="' . TEMPLATEURL . '/img/loading.gif" alt="' . __('Loading...', ET_DOMAIN) . '">',
            'loadingTxt' => __('Loading...', ET_DOMAIN) ,
            'loadingFinish' => '<span class="icon loading" data-icon="3"></span>',
            'plupload_config' => array(
                'max_file_size' => apply_filters('ce_max_file_size_upload', '3mb') ,
                'url' => admin_url('admin-ajax.php') ,
                'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf') ,
                'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap') ,
                'filters' => array(
                    array(
                        'title' => __('Image Files', ET_DOMAIN) ,
                        'extensions' => 'jpg,jpeg,gif,png'
                    )
                ) ,
                'msg' => array(
                    'FILE_EXTENSION_ERROR' => __('File extension error. Only allow  %s file extensions.', ET_DOMAIN) ,
                    'FILE_SIZE_ERROR' => __('This file is too big. Files must be less than %s.', ET_DOMAIN) ,
                    'FILE_DUPLICATE_ERROR' => __('File already present in the queue.', ET_DOMAIN) ,
                    'FILE_COUNT_ERROR' => __('File count error.', ET_DOMAIN) ,
                    'IMAGE_FORMAT_ERROR' => __('Image format either wrong or not supported.', ET_DOMAIN) ,
                    'IMAGE_MEMORY_ERROR' => __('Runtime ran out of available memory', ET_DOMAIN) ,
                    'HTTP_ERROR' => __('Upload URL might be wrong or doesn\'t exist.', ET_DOMAIN) ,
                )
            ) ,
            'require_fields' => __("Please complete required fields.", ET_DOMAIN) ,
            'save' => __("Save", ET_DOMAIN) ,
            'ce_config' => $ce_config,
            'search_message' => __("Please enter a keyword.", ET_DOMAIN) ,
            'max_cat_msg' => __("You have reached the maximum number of categories allowed for an ad.", ET_DOMAIN) ,
            'orderby' => ce_get_ad_orderby() ,
            'limit_free_plan' => get_theme_mod('ce_limit_free_plan', '') ,
            'limit_free_msg' => __("You have reached the maximum number of Free posts. Please select another plan.", ET_DOMAIN) ,
            'carouselTitle' => __("Click to set a featured image", ET_DOMAIN) ,
            'removeCarousel' => __("Remove", ET_DOMAIN) ,
            'ad_require_fields' => get_theme_mod('ad_require_fields', array(
                'et_full_location',
                CE_ET_PRICE,
                'ad_location',
                CE_AD_CAT
            )) ,
            'msg_featued' => __('click to select a featured image', ET_DOMAIN) ,
            'ce_ad_cat' => CE_AD_CAT,
            'regular_price' => CE_ET_PRICE,
            '_et_featured' => ET_FEATURED
        ));
    }
    
    /**
     * add styles
     */
    function on_add_styles() {
        
        if ($this->is_mobile()) {
            return false;
        }
        
        // echo $cssUri;
        if (!self::$use_mininfy) {
            $this->add_style('font', TEMPLATEURL . '/fonts/font-face.css', array() , CE_VERSION);
            $this->add_style('font-awesome', TEMPLATEURL . '/css/font-awesome.min.css', array() , CE_VERSION);
        }
        
        /**
         * bootstrap css
         */
        
        // if( !self::$use_mininfy ) {
        $this->add_style('bootstrap', TEMPLATEURL . '/css/bootstrap.css', array() , CE_VERSION);
        
        // }
        //$this->add_style( 'bootstrap-responsive', TEMPLATEURL . '/css/bootstrap-responsive.css', array(), CE_VERSION );
        
        
        
        /**
         * classifiedengine custom style
         */
        if (self::$use_mininfy) {
            $link = add_query_arg(array(
                'g' => 'theme_css'
            ) , self::$minify_link);
            $this->add_style('ce_style', $link, array() , CE_VERSION);
        } else {
            
            $this->add_style('custom-ie8', TEMPLATEURL . '/css/custom-ie8.css', array() , CE_VERSION);
            $this->add_style('custom', TEMPLATEURL . '/css/custom.css', array() , CE_VERSION);
            
            $this->add_style('custom-responsive', TEMPLATEURL . '/css/custom-responsive.css', array(
                'custom'
            ) , CE_VERSION);
        }
        
        /**
         * customizer bar style
         */
        if (current_user_can('manage_options')) {
            $this->add_style('customizer', TEMPLATEURL . '/css/customizer.css', array() , CE_VERSION);
            $this->add_style('et_colorpicker', FRAMEWORK_URL . '/js/lib/css/colorpicker.css', array());
        }
        
        /**
         * jquery ui css
         */
        $http = et_get_http();
        $this->add_style('jquery-ui', $http . '://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.min.css', array() , '1.10.3');
        
        do_action('et_after_print_styles');
        if (!self::$use_mininfy) {
            $this->add_style('style', get_stylesheet_uri() , array() , CE_VERSION);
        }
        if (is_singular(CE_AD_POSTTYPE)) {
            $this->add_style('facybox-style', TEMPLATEURL . '/css/fancybox.css?ver=4.0', array() , CE_VERSION);
        }
        
        do_action('ce_on_add_styles');
    }
    
    function footer_script() {
        
        if ($this->is_mobile()) {
            return false;
        }
        
        global $user_ID, $current_user, $ce_config;
        
        do_action('ce_before_footer_script');
        
        $option = new CE_Options;
?>
        <script type="text/javascript">
            (function ($) {


                $(document).ready(function ($) {
                    $.validator.addMethod('accept', function () { return true; });

                    $.validator.addMethod('isMoney', function (value,element) {
                        var ad_price_format = '<?php
        echo $currency_format = $option->get_option('et_currency_format'); ?>';

                        if(ad_price_format == '2' ){
                            var check = this.optional(element) || /^-?(?:\d+|\d{1,3}(?:.\d{3})+)(?:\,\d+)?$/.test(value);
                            if(check == false ){
                                return false;
                            }
                            var price   = value.split(","),
                                prev    = price[0].toString(),
                                regex   = new RegExp('[.]', 'g'),
                                full    = '',
                                number  = prev.replace(regex, ','),
                                comma   = value.split(",").length -1;

                            if(comma > 1 || dot > 0)
                                return false;

                            if( price.length >= 2 ){
                                var dot     = price[1].split(".").length -1;
                                var full = number + "." +  price[1].toString();
                                value = full;
                            }

                        }
                        return  this.optional(element) || /^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/.test(value);

                    }, '<?php
        _e('Please enter a valid price', ET_DOMAIN); ?>');

                    CE.app  =   new CE.Views.App();

                    if(typeof CE.Views.Index != 'undefined') {
                        CE.Index    =   new CE.Views.Index();
                    }

                    if(typeof CE.Views.Seller_Profile !== 'undefined') {
                        CE.Seller_Listing   =   new CE.Views.Seller_Listing ();

                        CE.Seller_Profile   =   new CE.Views.Seller_Profile ();
                    }

                    if(typeof CE.Views.Post_Ad !== 'undefined') {
                        CE.Post_Ad  =   new CE.Views.Post_Ad();
                    }

                    if( typeof CE.Views.Single_Ad !== 'undefined') {
                        CE.Single_Ad = new CE.Views.Single_Ad ();
                    }
                    if(typeof CE.Views.List_seller !== 'undefined' ) {
                        CE.List_Seller  =   new CE.Views.List_seller();
                    }


                });
            })(jQuery);
        </script>


    <?php
        do_action('ce_after_footer_script');
        
        if (is_singular(CE_AD_POSTTYPE) || is_author() || is_page_template('author-reviews.php') || get_query_var('review')) {
            ce_template_send_message();
            ce_single_related_item();
            ce_template_modal_review();
        }
        
        if (is_singular(CE_AD_POSTTYPE) && current_user_can('manage_options')) {
            ce_single_button_template();
            ce_single_heading_message();
            ce_template_modal_reject_ad();
        }
        
        /**
         * print modal login template when user is not logged in
         */
        if (!is_user_logged_in()) {
            ce_template_modal_login();
        }
        
        if ((is_page_template('page-account-listing.php') || current_user_can('manage_options')) && (!is_page_template("page-post-ad.php"))) {
            ce_template_edit_ad();
            ce_template_modal_reject_ad();
        }
        
        if (is_page_template('page-sellers.php')) {
            ce_seller_item_template();
        }
        if (is_page_template('page-account-profile.php')) {
            ce_template_favorite_ad();
        }
        
        if (is_user_logged_in() || is_page_template('page-post-ad.php')) {
            ce_get_template_carousel();
        }
        
        ce_template_post_item();
    }
    
    function wp_head() {
        
        if ($this->is_mobile()) {
            return false;
        }
        
        et_block_ie('7.0', 'page-unsupported.php');
        
        if (is_singular(CE_AD_POSTTYPE)) {
?>
            <script type="text/javascript">
                var addthis_config = addthis_config||{};
                addthis_config.data_track_addressbar = false;
                addthis_config.data_track_clickback = false;
                addthis_config.services_compact     = false;

            </script>
            <?php
        }
        
        if (is_single()) {
            global $post;
?>
            <meta property="og:url" content="<?php
            echo get_permalink($post->ID); ?>"/>
            <meta property="og:title" content="<?php
            echo get_the_title($post->ID); ?>"/>
            <meta property="og:description" content="<?php
            echo strip_tags(apply_filters('the_excerpt', $post->post_content)); ?>" />
            <meta property="og:type" content="article" />
            <meta property="og:image" content="<?php
            echo wp_get_attachment_url(get_post_thumbnail_id($post->ID)); ?>" />
        <?php
        }
        
        global $user_ID, $current_user, $ce_config;
        
        if ($user_ID) {
            
            // generate js current user data
            
            
?>
                <script type="application/json" id="current_user_data"><?php
            echo json_encode(ET_Seller::convert($current_user)); ?></script>
            <?php
        }
        
        /**
         * print template categories json
         */
        if (is_page_template('page-post-ad.php') || current_user_can('manage_options') || is_page_template('page-account-listing.php')) {
            ce_categories_json();
        }
        
        if (is_search() || is_home() || is_tax()) {
            
            // attempt remove search suggestion
            $keywords = array();
            
            /*array('Android developer', 'IT', 'Wordpress',' Frontend Developer', 'Developer Programer','Quality Control','Quality Assurance','PHP','Java', 'Zend', 'IOS','html/css','backbone js');*/
?>
                <script type="application/json" id="list_keywords"><?php
            echo json_encode($keywords); ?></script>
            <?php
        }
        
        if (current_user_can('manage_options') || is_page_template('page-account-listing.php') || $ce_config['use_infinite_scroll']) {
            ce_template_ad_item();
        }
    }
    
    /**
     * add query var paymentType
     */
    function add_query_vars($query_vars) {
        array_push($query_vars, 'paymentType');
        array_push($query_vars, 'review');
        array_push($query_vars, ET_AdCatergory::slug() , ET_AdLocation::slug());
        return $query_vars;
    }
    
    /**
     * filter mail headers
     */
    function wp_mail($compact) {
        if (isset($_GET['action']) && $_GET['action'] == 'lostpassword') return $compact;
        if ($compact['headers'] == '') {
            
            //$compact['headers']   = 'MIME-Version: 1.0' . "\r\n";
            $compact['headers'] = 'Content-type: text/html; charset=utf-8' . "\r\n";
            $compact['headers'].= "From: " . get_option('blogname') . " < " . get_option('admin_email') . "> \r\n";
        }
        
        $compact['message'] = str_ireplace('[site_url]', home_url() , $compact['message']);
        $compact['message'] = str_ireplace('[blogname]', get_bloginfo('name') , $compact['message']);
        $compact['message'] = str_ireplace('[admin_email]', get_option('admin_email') , $compact['message']);
        
        $compact['message'] = html_entity_decode($compact['message'], ENT_QUOTES, 'UTF-8');
        $compact['subject'] = html_entity_decode($compact['subject'], ENT_QUOTES, 'UTF-8');
        
        //$compact['message']       =   et_get_mail_header().$compact['message'].et_get_mail_footer();
        
        return $compact;
    }
    
    /**
     * filter retrieve password message
     */
    function filter_retrieve_password_message($message, $active_key, $user_data) {
        
        $user_login = $user_data->user_login;
        $mail_opt = new ET_CEMailTemplate();
        $forgot_message = $mail_opt->get_forgot_pass_mail();
        
        $activate_url = et_get_page_link('reset-password', array(
            'key' => $active_key,
            'user_login' => rawurlencode($user_login)
        ));
        
        $mailing = new CE_Mailing();
        $forgot_message = $mailing->et_filter_authentication_placeholder($forgot_message, $user_data->ID);
        $forgot_message = str_ireplace('[activate_url]', $activate_url, $forgot_message);
        
        return $forgot_message;
    }
    
    /**
     * limti excerpt ad
     */
    function ce_limit_exceprt() {
        return 150;
    }
    
    /**
     * localize validator script
     */
    public function localize_validator() {
        if ($this->is_mobile()) return '';
        if ((is_admin() && isset($_GET['page']) && $_GET['page'] == 'engine-settings') || !is_admin()) {
?>
                <script type="text/javascript">
                (function ($) {
                    $.extend($.validator.messages, {
                        required: "<?php
            _e("This field is required.", ET_DOMAIN) ?>",
                        email: "<?php
            _e("Please enter a valid email address.", ET_DOMAIN) ?>",
                        url: "<?php
            _e("Please enter a valid URL.", ET_DOMAIN) ?>",
                        number: "<?php
            _e("Please enter a valid number.", ET_DOMAIN) ?>",
                        digits: "<?php
            _e("Please enter only digits.", ET_DOMAIN) ?>",
                        equalTo: "<?php
            _e("Please enter the same value again.", ET_DOMAIN) ?>"
                    });
                })(jQuery);
                </script>
            <?php
        }
    }
    
    /**
     * redirect if seller enter admin area
     */
    function redirect_seller() {
        if (!(current_user_can('manage_options') || current_user_can('editor'))) {
            wp_redirect(home_url());
        }
    }
    
    /**
     * add ce admin menu to admin bar
     */
    function admin_bar_menu() {
        global $wp_admin_bar;
        
        $args = array(
            "id" => 'ce_setting',
            "title" => 'Classified Dashboard',
            "href" => admin_url('admin.php?page=et-overview') ,
            "parent" => false,
            "meta" => array(
                'tabindex' => 20
            )
        );
        
        $wp_admin_bar->add_menu($args);
        $childs = array(
            'overview' => array(
                'section' => 'et-overview',
                'title' => __("Overview", ET_DOMAIN)
            ) ,
            'setting' => array(
                'section' => 'et-settings',
                'title' => __("Settings", ET_DOMAIN)
            ) ,
            
            'seller' => array(
                'section' => 'et-sellers',
                'title' => __("Sellers", ET_DOMAIN)
            ) ,
            'payment' => array(
                'section' => 'et-payments',
                'title' => __("Payments", ET_DOMAIN)
            )
        );
        $childs = apply_filters('et_admin_bar_menu', $childs);
        foreach ($childs as $key => $value) {
            
            $child = array(
                "id" => 'ce_setting-' . $key,
                "title" => $value['title'],
                "href" => admin_url('admin.php?page=' . $value['section']) ,
                "parent" => 'ce_setting',
                "meta" => array(
                    'tabindex' => 20
                )
            );
            
            $wp_admin_bar->add_menu($child);
        }
    }
    
    /**
     * detect browser, if that is IE 7 or below, notice to visitor
     */
    function block_ie_redirect() {
        
        // et_block_ie('7.0', 'page-unsupported.php');
        
        
    }
    
    //redirect template fee
    function custom_template_feed($for_comment) {
        $rss_template = get_template_directory() . '/feed-rss2.php';
        if (get_query_var('post_type') == CE_AD_POSTTYPE && file_exists($rss_template)) load_template($rss_template);
        else do_feed_rss2($for_comment);
    }
    function ce_feed_add_namespace() {
        echo 'xmlns:media="http://search.yahoo.com/mrss/"
        xmlns:georss="http://www.georss.org/georss"';
        
        // xmlns:mycustomfields="'.  get_bloginfo('wpurl').'" ';
        
        
    }
    function add_element_to_feed_item() {
        global $post;
        $ad = CE_Ads::convert($post);
        $user = get_user_by('id', $ad->post_author);
        $seller = ET_Seller::convert($user);
        $thumb_id = get_post_thumbnail_id(get_the_ID());
        
        $ad_category = isset($ad->category[0]) ? $ad->category[0]->name : '';
        $ad_location = isset($ad->location[0]) ? $ad->location[0]->name : '';
        echo '<seller>' . $seller->display_name . '</seller>';
        echo '<category>' . $ad_category . '</category>';
        echo '<location>' . $ad_location . '</location>';
    }
    
    //update seller profile(user and  not current user)
    function update_seller_profile($user_id) {
        
        ET_Seller::update_seller_profile($user_id);
    }
    
    // show user profile
    function show_seller_profile($profile) {
        ET_Seller::show_seller_profile($profile);
    }
    
    // show url to wizard after active theme
    public function notice_after_installing_theme() {
        $this->wizard_status = get_option('et_wizard_status', 0);
        if (isset($this->wizard_status) && !$this->wizard_status) {
?>
            <style type="text/css">
            .et-updated{
                background-color: lightYellow;
                border: 1px solid #E6DB55;
                border-radius: 3px;
                webkit-border-radius: 3px;
                moz-border-radius: 3px;
                margin: 20px 15px 0 0;
                padding: 0 10px;
            }
            </style>
            <div id="notice_wizard" class="et-updated">
                <p>
                    <?php
            printf(__("You have just installed ClassifiedEngine theme, we recommend you follow through our <a href='%s'>setup wizard</a> to set up the basic configuration for your website!", ET_DOMAIN) , admin_url('admin.php?page=et-wizard')) ?>
                </p>
            </div>
        <?php
        }
    }
    
    /**
     * pre filter et_get_page_link
     */
    public function pre_filter_get_page_link($link, $page_type) {
        if (defined('ICL_LANGUAGE_CODE')) {
            $transient = get_transient('page-' . $page_type . '.php_' . ICL_LANGUAGE_CODE);
            
            //if(!$transient) return 1;
            
            return $transient;
        } else {
            
            return get_transient('page-' . $page_type . '.php');
        }
    }
    
    /**
     * update page template transient
     */
    public function update_transient_page_template($post_id) {
        global $post;
        if (!$post) return;
        if (!defined('ICL_LANGUAGE_CODE')) {
            if (isset($_POST['page_template']) && $post->post_status == 'publish') {
                set_transient($_POST['page_template'], get_permalink($post_id));
            }
            
            if ($post->post_status != 'publish' && isset($_POST['page_template'])) {
                delete_transient($_POST['page_template']);
            }
        } else {
            if (isset($_POST['page_template']) && $post->post_status == 'publish') {
                set_transient($_POST['page_template'] . '_' . ICL_LANGUAGE_CODE, get_permalink($post_id));
            }
            
            if ($post->post_status != 'publish' && isset($_POST['page_template'])) {
                delete_transient($_POST['page_template'] . '_' . ICL_LANGUAGE_CODE);
            }
        }
    }
    
    public function remove_page_template_transient() {
        
        if (isset($_REQUEST['settings-updated'])) {
            $page_templates = wp_get_theme()->get_page_templates();
            if (!defined('ICL_LANGUAGE_CODE')) {
                foreach ($page_templates as $key => $value) {
                    delete_transient($key);
                }
            } else {
                foreach ($page_templates as $key => $value) {
                    delete_transient($key . '_' . ICL_LANGUAGE_CODE);
                }
            }
        }
    }
    
    function custom_excerpt_length($length) {
        return 20;
    }
    
    public function insert_ad($id) {
        if (defined('WPML_LOAD_API_SUPPORT') && defined('ICL_LANGUAGE_CODE')) {
            $_POST['icl_post_language'] = $language_code = ICL_LANGUAGE_CODE;
            wpml_update_translatable_content('post_ad', $id, $language_code);
        }
    }
    
    function author_reviews_rewrite_template($template) {
        global $wp_query;
        
        $filename = basename($template);
        
        if (array_key_exists('review', $wp_query->query_vars) && $filename == 'author.php') {
            
            if ($this->is_mobile()) {
                $template = get_template_directory() . '/mobile/author-reviews.php';
            } else {
                $template = get_template_directory() . '/author-reviews.php';
            }
        }
        return $template;
    }
}

global $classified, $ce_config;
add_action('after_setup_theme', 'ce_setup_theme', 1);
function ce_setup_theme() {
    global $classified;
    $classified = new ET_ClassifiedEngine();
}

/*
 *  display list paging.
 *  if not add your query, will get the global $wp_query
*/
function ce_pagination($query = '') {
    
    if ($query == '') {
        global $wp_query;
        $query = $wp_query;
    }
    
    global $ce_config;
    if ($ce_config['use_infinite_scroll'] && $query->max_num_pages > 1 && !is_category()) {
        $query_var = array();
        
        $query_var['post_type'] = $query->query_vars['post_type'] != '' ? $query->query_vars['post_type'] : 'post';
        $query_var['post_status'] = isset($query->query_vars['post_status']) ? $query->query_vars['post_status'] : 'publish';
        $query_var['orderby'] = isset($query->query_vars['orderby']) ? $query->query_vars['orderby'] : 'date';
        $query_var['order'] = $query->query_vars['order'];
        $query_var['action'] = 'ce-load-more-' . $query_var['post_type'];
        if (!empty($query->query_vars['meta_key'])) $query_var['meta_key'] = isset($query->query_vars['meta_key']) ? $query->query_vars['meta_key'] : 'et_featured';
        
        $query_var = array_merge($query_var, $query->query);
        
        $query_string = '';
        foreach ($query_var as $key => $value) {
            $query_string.= '&' . $key . '=' . $value;
        }
        
        if (isset($_REQUEST['sortby'])) {
            $query_var['sortby'] = $_REQUEST['sortby'];
        }
?>
    <div id="inview">
        <input type="hidden" value="<?php
        echo $query_string
?>" name="query_string" id="query_string" />
        <div class="bubblingG">
            <span id="bubblingG_1">
            </span>
            <span id="bubblingG_2">
            </span>
            <span id="bubblingG_3">
            </span>
        </div>
        <?php
        _e('Loading more', ET_DOMAIN); ?>
        <input type="hidden" value="<?php
        echo $query_string
?>" name="query_string" id="query_string" />
        <script type="application/json" id="ce_query"><?php
        echo json_encode($query_var); ?></script>
    </div>
    <?php
        return;
    }
    
    $big = 999999999;
    
    // need an unlikely integer
    
    $paginate = paginate_links(array(
        'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))) ,
        'format' => '?paged=%#%',
        'current' => max(1, get_query_var('paged')) ,
        'total' => $query->max_num_pages,
        'type' => 'array',
        'prev_text' => '<i class="fa fa-arrow-left"></i>',
        'next_text' => '<i class="fa fa-arrow-right"></i>',
        'end_size' => 2,
        'mid_size' => 1
    ));
    
    if ($paginate) {
        echo ' <ul class="pagination">';
        foreach ($paginate as $key => $value) {
            echo '<li>' . $value . '</li>';
        }
        echo '</ul>';
    }
}

function ce_seller_pagination($total, $current) {
    global $ce_config;
    if ($ce_config['use_infinite_scroll'] && $total > 1) {
?>
        <div id="inview" >
            <div class="bubblingG">
                <span id="bubblingG_1">
                </span>
                <span id="bubblingG_2">
                </span>
                <span id="bubblingG_3">
                </span>
            </div>
            <?php
        _e('Loading more sellers', ET_DOMAIN); ?>
        </div>
    <?php
        return;
    }
    
    $big = 9999999;
    $paginate = paginate_links(array(
        
        //'base'        => $base, // the base URL, including query arg
        'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))) ,
        
        //'format'  => $number, // this defines the query parameter that will be used, in this case
        'format' => '?paged=%#%',
        'prev_text' => '<i class="fa fa-arrow-left"></i>',
        'next_text' => '<i class="fa fa-arrow-right"></i>',
        'total' => $total,
        
        // the total number of pages we have
        'current' => $current,
        
        // the current page
        'end_size' => 1,
        'mid_size' => 3,
        'type' => 'array',
    ));
    
    if (is_array($paginate)) {
        echo ' <ul class="pagination">';
        foreach ($paginate as $key => $value) {
            echo '<li>' . $value . '</li>';
        }
        echo '</ul>';
    }
}

function ce_review_pagination($total) {
    
    global $ce_config;
    
    $current = 1;
    if (get_query_var('paged')) $current = get_query_var('paged');
    
    if ($ce_config['use_infinite_scroll'] && $total > 1) {
        global $wp_query;
        $query_var = array();
        
        $query_var['reviews'] = $wp_query->query_vars['review'];
        $query_var['author'] = $wp_query->query_vars['author_name'];
        $query_var['action'] = 'ce-load-more-reviews';
        $query_string = '';
        foreach ($query_var as $key => $value) {
            $query_string.= '&' . $key . '=' . $value;
        }
?>
        <div id="review-inview" >
            <div class="bubblingG">
                <span id="bubblingG_1">
                </span>
                <span id="bubblingG_2">
                </span>
                <span id="bubblingG_3">
                </span>
            </div>
            <?php
        _e('Loading more reviews', ET_DOMAIN); ?>
        </div>
        <input type="hidden" value="<?php
        echo $query_string
?>" name="query_string" id="query_string" />
        <script type="application/json" id="reviews_query"><?php
        echo json_encode($query_var); ?></script>
    <?php
        return;
    }
    
    $big = 9999999;
    $paginate = paginate_links(array(
        
        //'base'        => $base, // the base URL, including query arg
        'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))) ,
        
        //'format'  => $number, // this defines the query parameter that will be used, in this case
        'format' => '?paged=%#%',
        'prev_text' => '<i class="fa fa-arrow-left"></i>',
        'next_text' => '<i class="fa fa-arrow-right"></i>',
        'total' => $total,
        
        // the total number of pages we have
        'current' => $current,
        
        // the current page
        'end_size' => 1,
        'mid_size' => 3,
        'type' => 'array',
    ));
    
    if (is_array($paginate)) {
        echo ' <ul class="pagination">';
        foreach ($paginate as $key => $value) {
            echo '<li>' . $value . '</li>';
        }
        echo '</ul>';
    }
}

/**
 * Return format text with a number
 * @since 1.0
 * @param $zero zero format
 * @param $single single format
 * @param $plural plural format
 * @param $number input number
 */
function et_number($zero, $single, $plural, $number) {
    if ($number == 0) return $zero;
    elseif ($number == 1) return $single;
    else return $plural;
}

add_action('et_cash_checkout', 'send_cash_message');
function send_cash_message($cash_message) {
    
    // $auto_email = et_get_auto_emails();
    $session = et_read_session();
    $ad_id = '';
    if ($session['ad_id']) $ad_id = $session['ad_id'];
    CE_Mailing::send_cash_message($cash_message, $ad_id);
}

function custom_footer_script($scripts) {
?>
<script type="text/javascript">

         // Add a script element as a child of the body
        function downloadJSAtOnload(src) {
            var element = document.createElement("script");
                element.src = src;
                document.body.appendChild(element);
        }

        <?php
    foreach ($scripts as $key => $value) {
        $a = array_pop($value)
?>
            if (window.addEventListener)
                window.addEventListener("load", downloadJSAtOnload("<?php
        echo $a
?>"), false);
            else if (window.attachEvent)
                window.attachEvent("onload", downloadJSAtOnload("<?php
        echo $a
?>"));
            else window.onload = downloadJSAtOnload("<?php
        echo $a
?>");
        <?php
    } ?>

    </script>
<?php
}

if (!function_exists('ae_check_ajax_referer')):
    
    /**
     * Verifies the AJAX request to prevent processing requests external of the blog.
     *
     * @since 2.0.3
     *
     * @param string $action Action nonce
     * @param string $query_arg where to look for nonce in $_REQUEST (since 2.5)
     */
    function ae_check_ajax_referer($action = - 1, $query_arg = false, $die = true) {
        $nonce = '';
        
        if ($query_arg && isset($_REQUEST[$query_arg])) $nonce = $_REQUEST[$query_arg];
        elseif (isset($_REQUEST['_ajax_nonce'])) $nonce = $_REQUEST['_ajax_nonce'];
        elseif (isset($_REQUEST['_wpnonce'])) $nonce = $_REQUEST['_wpnonce'];
        
        $result = ae_verify_nonce($nonce, $action);
        
        if ($die && false == $result) {
            if (defined('DOING_AJAX') && DOING_AJAX) wp_die(-1);
            else die('-1');
        }
        
        /**
         * Fires once the AJAX request has been validated or not.
         *
         * @since 2.1.0
         *
         * @param string $action The AJAX nonce action.
         * @param bool   $result Whether the AJAX request nonce was validated.
         */
        do_action('check_ajax_referer', $action, $result);
        
        return $result;
    }
endif;

if (!function_exists('ae_verify_nonce')):
    
    /**
     * Verify that correct nonce was used with time limit.
     *
     * The user is given an amount of time to use the token, so therefore, since the
     * UID and $action remain the same, the independent variable is the time.
     *
     * @since 2.0.3
     *
     * @param string $nonce Nonce that was used in the form to verify
     * @param string|int $action Should give context to what is taking place and be the same when nonce was created.
     * @return bool Whether the nonce check passed or failed.
     */
    function ae_verify_nonce($nonce, $action = - 1) {
        $user = wp_get_current_user();
        $uid = (int)$user->ID;
        if (!$uid) {
            
            /**
             * Filter whether the user who generated the nonce is logged out.
             *
             * @since 3.5.0
             *
             * @param int    $uid    ID of the nonce-owning user.
             * @param string $action The nonce action.
             */
            $uid = apply_filters('nonce_user_logged_out', $uid, $action);
        }
        
        $i = wp_nonce_tick();
        
        // Nonce generated 0-12 hours ago
        if (substr(wp_hash($i . $action . $uid, 'nonce') , -12, 10) === $nonce) return 1;
        
        // Nonce generated 12-24 hours ago
        if (substr(wp_hash(($i - 1) . $action . $uid, 'nonce') , -12, 10) === $nonce) return 2;
        return false;
    }
endif;

if (!function_exists('ae_create_nonce')):
    
    /**
     * Creates a random, one time use token.
     *
     * @since 2.0.3
     *
     * @param string|int $action Scalar value to add context to the nonce.
     * @return string The one use form token
     */
    function ae_create_nonce($action = - 1) {
        $user = wp_get_current_user();
        $uid = (int)$user->ID;
        if (!$uid) {
            
            /** This filter is documented in wp-includes/pluggable.php */
            $uid = apply_filters('nonce_user_logged_out', $uid, $action);
        }
        
        $i = wp_nonce_tick();
        
        return substr(wp_hash($i . $action . $uid, 'nonce') , -12, 10);
    }
endif;
