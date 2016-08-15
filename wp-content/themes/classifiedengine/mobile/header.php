<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1" />

    <title><?php wp_title( '|', true, 'right' );?></title>
    <?php 
    $use_minify = get_theme_mod( 'ce_minify', 0 );
    if($use_minify) {
        
    } else {
    ?>
        <link rel="stylesheet"  href="<?php echo TEMPLATEURL; ?>/css/font-awesome.min.css" >
        <link rel="stylesheet"  href="<?php echo TEMPLATEURL; ?>/mobile/css/reset.css">
        <link rel="stylesheet"  href="<?php echo TEMPLATEURL; ?>/mobile/css/jquery.mobile-1.3.1.min.css">
        <link rel="stylesheet"  href="<?php echo TEMPLATEURL; ?>/mobile/css/jquery.mobile.structure-1.3.1.min.css">
        <link rel="stylesheet"  href="<?php echo TEMPLATEURL; ?>/mobile/css/jquery.mobile.theme-1.3.1.min.css">
        <!-- font face -->        
        <link rel="stylesheet"  href="<?php echo TEMPLATEURL; ?>/mobile/css/custom.css">
    <?php } ?>
    
    <?php 
        do_action( 'et_mobile_header' ); 
        $ce_option          = new CE_Options;
        $website_logo       = $ce_option->get_website_logo();
        $mobile_icon        = $ce_option->get_mobile_icon();   
        $google_analytics   = $ce_option->get_option('et_google_analytics');
        $site_title         = $ce_option->get_site_title();
        $cutomize           = $ce_option->get_customization();
        
        global $wp_query;
        $c_str  =   ET_AdCatergory::slug();
        $l_str  =   ET_AdLocation::slug();

        $ad_cat             = isset($_GET['cat']) ? $_GET['cat'] : get_query_var($c_str);
        $ad_location        = isset($_GET['location']) ? $_GET['location'] : get_query_var($l_str);
    ?>
    <style type="text/css">
        .ui-header {
            background-color: <?php echo isset($cutomize['header']) ? $cutomize['header'] :'#fff'; ?> !important;
        }
        a.search-btn {
            background-color: <?php echo isset($cutomize['action_1']) ? $cutomize['action_1'] : '#fff'; ?> !important;
        }
        .ui-select .ui-btn  {
            background-color: <?php echo isset($cutomize['action_2']) ? $cutomize['action_2'] : '#fff'; ?> !important;
        }
        .btn-blue {
            background-color: <?php echo isset($cutomize['action_2']) ? $cutomize['action_2'] : '#2489ce'; ?> !important;
        }
    </style>
    <link rel="shortcut icon" href="<?php echo $mobile_icon[0]; ?>">
    
    <?php 
        wp_head();
    if ( is_singular() && get_option( 'thread_comments' ) ) {

    ?>
        <script type="text/javascript" src="<?php echo includes_url( 'js/comment-reply.js' );?>"></script>
    <?php } ?>
    <script type="text/javascript">
        var et_globals = {
            "ajaxURL"       : "<?php echo admin_url('admin-ajax.php'); ?>",
            "homeURL"       : "<?php echo home_url(); ?>",
            "imgURL"        : "<?php echo TEMPLATEURL . '/img'; ?>",
            "jsURL"         : "<?php echo TEMPLATEURL . '/js'; ?>",
            "dashboardURL"  : "<?php echo et_get_page_link('dashboard'); ?>",
            "logoutURL"     : "<?php echo wp_logout_url( home_url() ); ?>",
            "routerRootCompanies" : "<?php echo et_get_page_link('companies'); ?>",
            'loading_text'  : '<?php _e("Loading", ET_DOMAIN); ?>',
            'ad_cat'        : '<?php echo $ad_cat;?>',
            'ad_location'   :'<?php echo $ad_location;?>',
            'max_cat'       : '<?php echo get_theme_mod( 'ce_number_of_category' , '' ) ?>', 
            'ce_ad_cat' : '<?php echo CE_AD_CAT; ?>',
            '_et_featured' : '<?php echo ET_FEATURED; ?>'
        };
    </script>
    <?php 
    echo stripslashes($google_analytics);
    ?>
</head>
<body  <?php body_class("cbp-spmenu-push"); ?>>
<div data-role="page" >
    <!-- Menu Mobile
    ================================================== -->
    
    <nav class="cbp-spmenu cbp-spmenu-vertical cbp-spmenu-left cbp-spmenu-s1" id="cbp-spmenu-s1">
        <div class="button-menu-top menu-left-push">
            <span id="hideLeftPush" class="hideLeftPush icon-menu-mobile">
                <i class="fa fa-times" style="color:#fff; text-shadow:none;cursor:pointer;padding: 11px 5px 10px 20px"></i>
            </span>
            <img class="logo" height="20" src="<?php echo $website_logo[0]; ?>" title="<?php echo $site_title;?>">
        </div>
        <?php if(has_nav_menu('et_mobile_header')) { ?>
        <ul class="menu-left-top">
            <?php 
            
                wp_nav_menu( array(
                    'theme_location'    => 'et_mobile_header',              
                    'container'         => 'ul',
                    'menu_class'        => 'sf-menu',
                    'walker'            => new themeslug_walker_nav_menu
                  ) 
                );
            ?>  
        </ul>
        <?php } ?>
        <ul class="menu-add-static">
            <li><a href="<?php echo et_get_page_link ('post-ad'); ?>" data-ajax="false" class="menu-link main-menu-link ui-link"><?php _e("Post an Ad", ET_DOMAIN); ?></a></li>
            <?php if( is_user_logged_in() ){ ?>
            <li><a href="<?php echo wp_logout_url( home_url() ); ?>" data-ajax="false" class="menu-link main-menu-link ui-link" ><?php _e("Logout", ET_DOMAIN); ?></a></li>
            <?php } ?>
        </ul>
        
    </nav>
   
    <!-- Menu Mobile / End -->
   
    <div <?php if( is_home() || is_tax() ) { ?> class="aminate-header" <?php } ?> data-size="big">
    
        <div data-role="header" class="header-bar">     
            
            <div class="logo-menu">
                <?php if(has_nav_menu('et_mobile_header')) { ?>
                <div class="header-menu-res headerLeftPush" id="headerLeftPush" >
                    <span class="icon-menu-mobile"><i class="fa fa-bars"></i></span>&nbsp;&nbsp;
                    
                </div>
                <?php } ?>
                <div class="logo">
                    <a  data-ajax = "false" href="<?php echo home_url(); ?>">
                        <img width="" height="20" src="<?php echo $website_logo[0]; ?>">
                    </a>
                </div>
            </div>       
        </div><!-- /header -->
        <?php if( is_home() || is_tax() ) { ?>
        <div class="search headroom" >      
            <div class="search-text">
                <input type="text" name="search" id="txt_search" class="txt_search" placeholder="<?php _e("Search classifieds...", ET_DOMAIN); ?>" >
                <span class="icon" data-icon="s"></span>
            </div>
            <a href="#" class="icon search-btn category-btn" data-icon="y" data-ui="false"></a>
            <div class="menu-filter" style="display: none;">
                <div class="menu-filter-inner">
                    <!-- <div class="icon-header">
                        <a class="icon" data-icon="y"></a>
                    </div> -->
                    <div data-role="collapsible-set" data-theme="c" data-content-theme="d" style="max-height: 250px;overflow: hidden;">
                        <?php
                            ce_mobile_taxonomy(array('taxonomy' => CE_AD_CAT));
                            ce_mobile_taxonomy(array('taxonomy' => 'ad_location' , 'show_option_none' => __('Location list is empty.',ET_DOMAIN) )  );
                        ?>
                        
                    </div>
                    <a href="#" class="ui-btn-s btn-blue filter-search-btn btn-wide width90 search-button" > <?php _e("Search", ET_DOMAIN); ?> </a>
                </div>
            </div>
        </div> 
        <?php } ?>
    </div>
   
