<?php if ( is_single() ) { ?>
    <div class="bg-breadcrumb header-filter">
      	<div class="main-center container">
	        <ul class="breadcrumb" <?php echo Schema::WebPage("breadcrumb") ?>>
	          <li class="active"><a href="<?php echo home_url(); ?>"><span class="arrow-left"><i class="fa fa-arrow-left"></i></span><?php _e("Back to Home", ET_DOMAIN); ?></span></a></li>
	        </ul>
     	</div>
    </div> <!--/.breadcrumb page-->

  	<?php } elseif ( is_page() && !is_front_page() && !is_page_template( 'author-reviews.php' ) ) { ?>
	  	<div class="bg-breadcrumb header-filter">
			<div class="main-center container">
				<ul class="breadcrumb" <?php echo Schema::WebPage("breadcrumb") ?>>
				  	<li>
				  		<a href="<?php echo home_url() ?>"><?php _e('Home page', ET_DOMAIN) ?></a>
				  		<span class="divider"><i class="fa fa-arrow-right"></i></span>

                        </li>
				  	<?php
				  	if( isset($_REQUEST['section']) && is_page_template('page-account-profile.php') && $_REQUEST['section'] == 'password' ) { 	?>
				  		<li class="active"><a href="<?php echo get_permalink(); ?>"><?php echo get_the_title(); ?> </a><span class="divider"><i class="fa fa-arrow-right"></i></span></li> 
				  		<li class="active"><?php _e('Password', ET_DOMAIN); ?> </li> <?php
				  	} else { ?>
				  		<li class="active"><?php echo get_the_title(); ?> </li>	<?php
				  	}
				  	?>
				</ul>
			</div>
		</div> <!--/.breadcrumb page-->

	<?php } elseif( is_author() ) {

		$author	=	$wp_query->queried_object; ?>

		<div class="bg-breadcrumb header-filter">
			<div class="main-center container">
				<ul class="breadcrumb" <?php echo Schema::WebPage("breadcrumb") ?>>
				  	<li>
				  		<a href="<?php echo home_url() ?>"><?php _e('Home page', ET_DOMAIN) ?></a>
				  		<span class="divider"><i class="fa fa-arrow-right"></i> </span>
				  	</li>
				  	<li class="active">
				  		<?php echo $author->display_name; ?>
					</li>

				</ul>
			</div>
		</div> <!--/.breadcrumb page-->

	<?php  } elseif ( is_category() || is_tax(CE_AD_CAT) ) {

		$term_text = '';
		$term = get_queried_object();

		if( !is_wp_error($term) && $term ){

			$term_parent = get_term( $term->parent, CE_AD_CAT );

			if( !is_wp_error($term_parent) && $term_parent){
				$term_text = '<li><a href= "'.get_term_link($term->parent,CE_AD_CAT).'">'.$term_parent->name.'</a> <span class="divider"><i class="fa fa-arrow-right"></i> </span>  </li>';
			}

		}

		$breadcrumbs	=	 '<li><a class="home" href="' . home_url() . '">' . __("Home page", ET_DOMAIN) . '</a> <span class="divider"><i class="fa fa-arrow-right"></i> </span> </li> ';
		$before			=	'<li>';
		$after			=	'</li>';
		$breadcrumbs	.= $term_text . $before  . single_cat_title('', false) .  $after;
	 ?>
		<div class="bg-breadcrumb header-filter">
		    <div class="main-center container">
		        <ul class="breadcrumb" <?php echo Schema::WebPage("breadcrumb") ?>>
		            <?php echo $breadcrumbs;   ?>

		        </ul>
		    </div>
		</div> <!--/.breadcrumb page-->
	<?php } ?>