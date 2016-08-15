<?php
/**
 * Groups configuration for default Minify implementation
 * @package Minify
 */

/** 
 * You may wish to use the Minify URI Builder app to suggest
 * changes. http://yourdomain/min/builder/
 *
 * See http://code.google.com/p/minify/wiki/CustomSource for other ideas
 **/
$baseUrl = TEMPLATEURL;
// echo $baseUrl;die;

$base_js    =   array(
    '//js/classified-engine.js',    
    '//js/lib/gmaps.js',
    '//mobile/js/jquery-inview.min.js',
    // '//js/front.js'
);

global $wp_rewrite, $wpdb;
if ( is_multisite() && $wpdb->blogid != 1  )
    $file = THEME_CONTENT_DIR . '/css/customization_' . $wpdb->blogid . '.css';
else 
    $file = THEME_CONTENT_DIR . '/css/customization.css';

$version    =   get_bloginfo( 'version' );
if( version_compare( $version, '3.9' , '>=') ) {
    $includes   =   array (            
            /**
             * version 3.9
            */
            '//../../../wp-includes/js/plupload/plupload.full.min.js',

            '//../../../wp-includes/js/underscore.min.js',
            '//../../../wp-includes/js/backbone.min.js',
            '//js/lib/jquery.validate.min.js',
            '//js/lib/jquery.carouFredSel-6.2.0.js',
            '//js/lib/chosen.js',
            // '//js/lib/jquery.tinyscrollbar.min.js',
            '//js/superfish.min.js',
            '//js/bx-slider.js'
        );
}else { 
    $includes   =   array (
            
            // '//../../../wp-includes/js/jquery/ui/jquery.ui.autocomplete.min.js',
            /**
             * older than version 3.9
            */
            '//../../../wp-includes/js/plupload/plupload.js',
            '//../../../wp-includes/js/plupload/plupload.html5.js',
            '//../../../wp-includes/js/plupload/plupload.flash.js',
            '//../../../wp-includes/js/plupload/plupload.silverlight.js',
            '//../../../wp-includes/js/plupload/plupload.html4.js',

            '//../../../wp-includes/js/underscore.min.js',
            '//../../../wp-includes/js/backbone.min.js',
            '//js/lib/jquery.validate.min.js',
            '//js/lib/jquery.carouFredSel-6.2.0.js',
            '//js/lib/chosen.js',
            '//js/superfish.min.js',
            '//js/bx-slider.js'
        );
}

$minify_path =  array(
    
    'wp-includes'   => $includes,

    'theme_css' => array(
    	// "//css/bootstrap.css", 
        "//css/font-awesome.min.css",
        "//mobile/fonts/font-face.css",
        // "//css/bootstrap.css",
        "//css/custom-ie8.css",
        "//css/custom.css", 
        "//css/custom-responsive.css",
        $file ,
        get_stylesheet_directory().'/style.css'
    ),

    
    'front'                 => array_merge ($base_js, array('//js/front.js') ),
    'index'                 => array_merge ($base_js, array('//js/index.js') ),
    'post-ad'               => array_merge ($base_js, array('//js/post-ad.js') ),
    'reset-pass'            => array_merge ($base_js, array('//js/reset-password.js') ),
    'single-ad'             => array_merge ($base_js, array('//js/single-ad.js') ),
    'seller-profile'        => array_merge ($base_js, array('//js/seller-profile.js') ),
    'seller-list'           => array_merge ($base_js, array('//js/seller-list.js') ),

    'sidebar'	=> array(
        // '//../../../wp-includes/js/tinymce/tiny_mce.js',
        // '//../../../wp-includes/js/tinymce/wp-tinymce-schema.js',
        // '//../../../wp-includes/js/tinymce/langs/wp-lang-en.js',
    	'//js/sidebar-widget.js'
    ),

    'mobile-js' => array(
        '//mobile/js/jquery.js',
        '//../../../wp-includes/js/underscore.min.js',
        '//../../../wp-includes/js/backbone.min.js',
        '//mobile/js/carousel.js',
        '//mobile/js/jquery.mobile-1.3.1.min.js',
        // '//mobile/js/jquery.simplemodal.js',
        '//mobile/js/jquery-inview.min.js',
        '//mobile/js/script.js',
        '//mobile/js/mobile-script.js',
        '//js/lib/gmaps.js',
        '//mobile/js/post-ad.js',
    ),

    'mobile-css' => array(
        '//mobile/css/reset.css',
        // '//mobile/css/jquery.mobile-1.3.1.min',
        '//mobile/css/jquery.mobile.structure-1.3.1.min.css',
        '//mobile/css/jquery.mobile.theme-1.3.1.min.css',
        // '//css/font-awesome.min.css',
        '//mobile/css/custom.css'
    )
);

return apply_filters('ce_minify_source_path' , $minify_path);