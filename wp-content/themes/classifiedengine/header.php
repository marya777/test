<!DOCTYPE html>
<html <?php language_attributes(); ?> >
  <!--[if lt IE 7]> <html class="ie ie6 oldie" <?php language_attributes(); ?> > <![endif]-->
  <!--[if IE 7]>    <html class="ie ie7 oldie" <?php language_attributes(); ?> > <![endif]-->
  <!--[if IE 8]>    <html class="ie ie8 oldie" <?php language_attributes(); ?> > <![endif]-->
  <!--[if gt IE 8]> <html class="ie ie10 newest" <?php language_attributes(); ?> > <![endif]-->
  <head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" >
    <title><?php wp_title( '|', true, 'right' );?></title>
    <?php 
		/*
		* Print the <title> tag based on what is being viewed.
		*/
		global $page, $paged, $current_user, $user_ID, $page_title, $ce_option;

		$ce_option 		= new CE_Options;
		$website_logo 	= $ce_option->get_website_logo();
		$site_title 	= $ce_option->get_site_title();
		$favicon		= $ce_option->get_mobile_icon();


		if ( is_singular() && get_option( 'thread_comments' ) )
		 	wp_enqueue_script( 'comment-reply' );
	?>

		<!--<meta name="viewport" content="width=device-width, initial-scale=1.0">-->
		<meta name="viewport" content="width=device-width">
		<?php if($favicon) { ?>
		<link rel="shortcut icon" href="<?php echo $favicon[0];?>"/>
		<?php } ?>

		<!--[if IE 8]><link href="css/custom-ie8.css" rel="stylesheet"> <![endif]-->
		<!--[if lt IE 9]>
		<script src="js/html5shiv.js"></script>
		<![endif]-->
		<?php wp_head(); ?>
</head>
<body <?php body_class() ?> >
	<?php do_action( 'ce_before_header' ); ?>
    <div class="navbar header-top bg-main-header" <?php echo Schema::WPHeader(); ?>>
    	<div class="main-center container">
            <div class="row">
            	<div class="col-md-3">
					<div class="logo">
						<a href="<?php echo home_url(); ?>">
							<img src="<?php echo $website_logo[0] ?>" alt="<?php echo $site_title; ?>"/>
						</a>
					</div>
				</div>
				<div class="col-md-9 main-top-menu" <?php echo Schema::SiteNavigationElement() ?>>
					<?php
					if(has_nav_menu('et_header'))
			            wp_nav_menu( array(
			                'theme_location'  	=> 'et_header',
			                'container' 		=> 'ul',
			                'menu_class'  		=> 'sf-menu'
			              )
			            );

          			?>
					<div class="navbar-text pull-right login">
						<div class="icon-account">
							<?php if (et_is_logged_in() ){
								$roles		=	$current_user->roles;
								$role		=	array_pop($roles);
							 	?>
								<span class="profile-icon">
									<a href="<?php echo apply_filters ('ce_filter_header_account_link', et_get_page_link('account-profile') ) ; ?>" class="bg-btn-header btn-header" title="<?php echo $role == 'seller' ? __('My profile', ET_DOMAIN) : __("Account",ET_DOMAIN);?>">
										<i class="fa fa-user"></i>
									</a>
								</span>
								<span class="quite-icon">
										<a href="<?php echo wp_logout_url(home_url()); ?>"><span data-icon="Q" class="icon" id="requestLogout"></span></a>
								</span>
							<?php } else {  ?>
								<span class="profile-icon request-login">
									<a title="<?php _e("Login", ET_DOMAIN); ?>"  href="#" id="requestLogin">
										<span data-icon="U" class="icon"></span>
									</a>
								</span>
							<?php } ?>
                            <?php
                            do_action('ce_header_before_profile_icon')
                            ?>
						</div>
						<span class="button-post-ad">
							<a title="<?php _e("Post an Ad", ET_DOMAIN ) ?>" href="<?php echo et_get_page_link( array('page_type' => 'post-ad', 'post_title' => __("Post an Ad", ET_DOMAIN )) ); ?>">
							<i class="fa fa-pencil-square-o"></i>
							<?php _e("Post an Ad", ET_DOMAIN); ?>
							</a>
						</span>

					</div>

				</div>
			</div>
		</div>
    </div>
    <?php do_action( 'ce_after_header' ); ?>
<!-- start ajax content -->
<div id="ajax-content" >
<?php get_template_part( 'template/header' , 'breadcrumbs' ); ?>