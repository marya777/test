<?php
define( 'MOBILE_PATH', dirname(__FILE__) );

add_action( 'template_redirect', 'prevent_user_mobile' );
function prevent_user_mobile() {
	if(is_page_template( 'page-login.php' )){
		global $user_ID;
		if($user_ID){
			wp_redirect( home_url() );
			exit;
		}
	}
}
/**
 * Handle mobile here
 */
add_filter('template_include', 'et_template_mobile', 20);
function et_template_mobile($template){
	global $user_ID, $wp_query, $wp_rewrite;
	$new_template = $template;
	// no need to redirect when in admin
	if ( is_admin() ) return $template;

	/***
	  * Detect mobile and redirect to the correlative layout file
	  */
	$filename 		= basename($template);
	if ( et_load_mobile()  ){
		do_action("pre_redirect_template_mobile", $template);
		$child_path		= get_stylesheet_directory() . '/mobile' . '/' . $filename;
		$parent_path 	= get_template_directory() . '/mobile' . '/' . $filename;
		if ( file_exists($child_path) ){
			$new_template = $child_path;
		} else if ( file_exists( $parent_path )){
			$new_template = $parent_path;
		} else {
			wp_redirect( home_url() );
			// $new_template = get_template_directory() . '/mobile/unsupported.php';
		}
	}

	return $new_template;
}

/**
 *
 */
function et_load_mobile(){
	global $isMobile;
	$detector = new ET_MobileDetect();
	$isMobile = apply_filters( 'ce_is_mobile', ( $detector->isMobile() && !$detector->isTablet() ) ? true : false );
	if ( $isMobile /*&& (!isset($_COOKIE['mobile']) || md5('disable') != $_COOKIE['mobile'] )*/){
		return true;
	} else {
		return false;
	}
}

/**
 * Get mobile version header template
 * @author toannm
 * @param name of the custom header template
 * @version 1.0
 * @copyright enginethemes.com team
 * @license enginethemes.com team
 */
function et_get_mobile_header( $name = null ){
	do_action( 'get_header', $name );
	//$templates = array();
	$templates = MOBILE_PATH . '/' . 'header.php';
	if ( isset($name) ) {
        $templates = MOBILE_PATH . '/' . "header-{$name}.php";
    }

    $child_path		= get_stylesheet_directory() . '/mobile' . '/header.php';
	if ( isset($name) ) {
        $child_path = get_stylesheet_directory() . '/mobile' . '/header-{$name}.php';
    }

	if ( file_exists($child_path) ){
		$new_template = $child_path;
	} else {
		$new_template = $templates;
	}

    //$templates = apply_filters('template_include', $templates);
	//$templates = apply_filters( 'template_include', $templates );
	//if ('' != locate_template($templates, true))
		load_template( $new_template);
}

/**
 * Get mobile version header template
 * @author toannm
 * @param name of the custom header template
 * @version 1.0
 * @copyright enginethemes.com team
 * @license enginethemes.com team
 */
function et_get_mobile_footer( $name = null ) {

	do_action( 'get_footer', $name );

	//$templates = array();
	$templates = MOBILE_PATH . '/' . 'footer.php';
	if ( isset($name) )
		$templates = MOBILE_PATH . '/' . "footer-{$name}.php";
	
	$child_path		= get_stylesheet_directory() . '/mobile' . '/footer.php';
	if ( isset($name) ) {
        $child_path = get_stylesheet_directory() . '/mobile' . '/footer-{$name}.php';
    }

    if ( file_exists($child_path) ){
		$new_template = $child_path;
	} else {
		$new_template = $templates;
	}

	// $templates = apply_filters( 'template_include', $templates );
	// Backward compat code will be removed in a future release
	//if ('' != locate_template($templates, true) )
    load_template($new_template);
}

add_action('et_mobile_footer', 'ce_mobile_ad_template');
function ce_mobile_ad_template () {
?>
	<script type="text/template" id="ce_mobile_ad_template">
	<li data-icon="false">
	    <a href="{{ permalink }}">
	        <span class="product-img">
	            {{ the_post_thumbnail }}
	            <# if(parseInt(<?php echo ET_FEATURED; ?>) == 1) { #> <span class="flag"><i class="fa fa-bookmark"></i></span>  <# } #>
	        </span>
	        <span class="product-text">
	            <span class="text clearfix">{{ post_title }}</span>
	            <span class="price clearfix">{{ price }}</span>
	            <?php echo "<# if(typeof location[0] !== 'undefined') { #>
                	<span class='address'>{{location[0].name}}</span>
           		 <# } #> "; ?>
	        </span>
	    </a>
	</li>
	</script>
<?php
	if( is_page_template( 'page-sellers.php' ) ) {
	?>
		<script type="text/template" id="ce_seller_template">
			<li data-icon="false">
			    <div class="intro-profile">
			    	<a href="{{ads_link}}" title="{{ads_link_title}}" >
			        	{{ avatar }}
			        	<div class="seller-detail">
			        		<span class="seller-name">{{ display_name }}</span>
			                <span class="sembold colorgray"><?php echo "<# if(user_location !== '') { #> </br>
				        	{{user_location}} <# } #>"; ?></span>
			                <span class="date-join">{{ joined_date }} </span>
				        </div>
			      	</a>
			    </div>
			</li>

	</script>
	<?php
	}
}

add_filter ('option_page_on_front', 'filter_on_front_page') ;
function filter_on_front_page ($page_on_front) {
	global $isMobile;
	$detector = new ET_MobileDetect();
	$isMobile = apply_filters( 'et_is_mobile', ($detector->isMobile()) ? true : false );

	if ( $isMobile && $page_on_front ){
		return '';
	} 
	return $page_on_front;
}

//ce list taxonomy
class  CE_Walker_Taxonomy extends Walker_Category{
     /**
     * Starts the list before the elements are added.
     *
     * @see Walker::start_lvl()
     *
     * @since 2.1.0
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param int    $depth  Depth of category. Used for tab indentation.
     * @param array  $args   An array of arguments. Will only append content if style argument value is 'list'.
     *                       @see ce_list_categories()
     */

     function start_lvl( &$output, $depth = 0, $args = array() ) {
		if ( 'list' != $args['style'] )
			return;
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='catelory-child'>\n";
	}
	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker::end_lvl()
	 *
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. Will only append content if style argument value is 'list'.
	 *                       @wsee wp_list_categories()
	 */
	function end_lvl( &$output, $depth = 0, $args = array() ) {
		if ( 'list' != $args['style'] )
			return;

		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}


     /**
     * Start the element output.
     *
     * @see Walker::start_el()
     *
     * @since 2.1.0
     *
     * @param string $output   Passed by reference. Used to append additional content.
     * @param object $category Category data object.
     * @param int    $depth    Depth of category in reference to parents. Default 0.
     * @param array  $args     An array of arguments. @see wp_list_categories()
     * @param int    $id       ID of the current category.
     */
    function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		extract($args);

		$cat_name = esc_attr( $category->name );
		$cat_name = apply_filters( 'list_cats', $cat_name, $category );
		$link = '<a href="#" data = '.$category->slug.' data-tax="'.$args['taxonomy'].'"'; //' . esc_url( get_term_link($category) ) . '
		if ( $use_desc_for_title == 0 || empty($category->description) )
			$link .= 'title="' . esc_attr( sprintf(__( 'View all posts filed under %s',ET_DOMAIN ), $cat_name) ) . '"';
		else
			$link .= 'title="' . esc_attr( strip_tags( apply_filters( 'category_description', $category->description, $category ) ) ) . '"';

		if( is_tax() ) {
			$queried_object	=	get_queried_object_id();
			if($queried_object == $category->term_id) {
				$link .= 'class="active"';
			}
		}

		$link .= '>';
		$link .= '<span class="dot"></span>'.$cat_name . '</a>';


		if ( !empty($show_count) )
			$link .= ' (' . intval($category->count) . ')';

		if ( 'list' == $args['style'] ) {
			$output .= "\t<li";
			$class = 'cat-item cat-item-' . $category->term_id;
			if ( !empty($current_category) ) {
				$_current_category = get_term( $current_category, $category->taxonomy );
				if ( $category->term_id == $current_category )
					$class .=  ' current-cat';
				elseif ( $category->term_id == $_current_category->parent )
					$class .=  ' current-cat-parent';
			}
			$output .=  ' class="' . $class . '"';
			$output .= ">$link\n";
		} else {
			$output .= "\t$link<br />\n";
		}
	}


}

/**
 * ce mobile taxonomy
*/
function ce_mobile_taxonomy($args =''){
    $defaults = array(
        'show_option_all' => '', 'show_option_none' => __('Category list is empty.',ET_DOMAIN),
        'orderby' => 'name', 'order' => 'ASC',
        'style' => 'list',
        'show_count' => 0, 'hide_empty' => 0,
        'use_desc_for_title' => 1, 'child_of' => 0,
        'feed' => '', 'feed_type' => '',
        'feed_image' => '', 'exclude' => '',
        'exclude_tree' => '', 'current_category' => 0,
        'hierarchical' => true, 'title_li' => __( 'Categories', ET_DOMAIN ),
        'echo' => 1, 'depth' => 3,
        'taxonomy' => CE_AD_CAT
    );

    $r = wp_parse_args( $args, $defaults );

    if ( !isset( $r['class'] ) )
        $r['class'] = ( 'category' == $r['taxonomy'] ) ? 'categories' : $r['taxonomy'];

    extract( $r );

    if ( !taxonomy_exists($taxonomy) )
        return false;
    if($taxonomy == CE_AD_CAT) {
    	$categories = ce_get_categories( $r );
    	$label	=	__("Filter by <span class='semibold'>Category</span>", ET_DOMAIN);
    }
    if($taxonomy == 'ad_location') {
    	$categories = ce_get_locations( $r );
    	$label	=	__("Filter by <span class='semibold'>Location</span>", ET_DOMAIN);
    }

    $categories	=	apply_filters( 'ce_mobile_taxonomy_search', $categories , $taxonomy );
    $label		=	apply_filters( 'cce_mobile_taxonomy_search_label' , $label , $taxonomy );

    foreach ( $categories as $key => $value ) {
        $value->has_child   =   0;
        if($value->parent != 0) continue;
        foreach ($categories as $key => $value_2) {
            if($value->term_id == $value_2->parent)
                $value->has_child   =   1;
        }
    }

    $output = '';

	$output.= '<div data-role="collapsible" data-icon="false">';
    $output.='<h4><span class="text">'.$label.'</span> <i class="fa fa-arrow-right"></i></h4>';
    $output.='<div class="content-category '.$taxonomy.' ">';
    $output.='<ul class="catelory-items category-items '.$taxonomy.'" data-tax="'.$taxonomy.'">';


    if ( empty( $categories ) ) {
        if ( ! empty( $show_option_none ) ) {
            if ( 'list' == $style )
                $output .= '<li>' . $show_option_none . '</li>';
            else
                $output .= $show_option_none;
        }

    } else {
        if ( ! empty( $show_option_all ) ) {
            $posts_page = ( 'page' == get_option( 'show_on_front' ) && get_option( 'page_for_posts' ) ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
            $posts_page = esc_url( $posts_page );
            if ( 'list' == $style )
                $output .= "<li><a href='$posts_page'>$show_option_all</a></li>";
            else
                $output .= "<a href='$posts_page'>$show_option_all</a>";
        }

        if ( empty( $r['current_category'] ) && ( is_category() || is_tax() || is_tag() ) ) {
            $current_term_object = get_queried_object();
            if ( $current_term_object && $r['taxonomy'] === $current_term_object->taxonomy )
                $r['current_category'] = get_queried_object_id();
        }
        $depth = 3;
        $output .= ce_walk_taxonomy_tree( $categories, $depth, $r );
    }

    $output .='</ul></div></div>';

    $output = apply_filters( 'ce_mobile_taxonomy', $output, $args );

    if ( $echo )
        echo $output;
    else
        return $output;


}

function ce_walk_taxonomy_tree() {
    $args = func_get_args();
    // the user's options are the third parameter
    if ( empty($args[2]['walker']) || !is_a($args[2]['walker'], 'Walker') )
        $walker = new CE_Walker_Taxonomy;
    else
        $walker = $args[2]['walker'];

    return call_user_func_array(array( &$walker, 'walk' ), $args );
}

/**
 * depend on YOAST SEO add meta to mobile header
*/
//add_action( 'et_mobile_header' ,  'et_mobile_seo_yoast' );
function et_mobile_seo_yoast() {
	if(class_exists('WPSEO_Frontend')) {
		// et_mobile_header
		try {
			$seo_yoast	=	new WPSEO_Frontend();
			$seo_yoast->head();
		} catch (Exception $e) {
		}

	}
}


//ce list taxonomy
class  CE_Walker_Taxonomy_List extends Walker_Category{
     /**
     * Starts the list before the elements are added.
     *
     * @see Walker::start_lvl()
     *
     * @since 2.1.0
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param int    $depth  Depth of category. Used for tab indentation.
     * @param array  $args   An array of arguments. Will only append content if style argument value is 'list'.
     *                       @see ce_list_categories()
     */

     function start_lvl( &$output, $depth = 0, $args = array() ) {
		if ( 'list' != $args['style'] )
			return;
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='catelory-child'>\n";
	}
	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker::end_lvl()
	 *
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. Will only append content if style argument value is 'list'.
	 *                       @wsee wp_list_categories()
	 */
	function end_lvl( &$output, $depth = 0, $args = array() ) {
		if ( 'list' != $args['style'] )
			return;

		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}


     /**
     * Start the element output.
     *
     * @see Walker::start_el()
     *
     * @since 2.1.0
     *
     * @param string $output   Passed by reference. Used to append additional content.
     * @param object $category Category data object.
     * @param int    $depth    Depth of category in reference to parents. Default 0.
     * @param array  $args     An array of arguments. @see wp_list_categories()
     * @param int    $id       ID of the current category.
     */
    function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		extract($args);

		$cat_name = esc_attr( $category->name );
		$cat_name = apply_filters( 'list_cats', $cat_name, $category );
		$link = '<a data-ajax="false" href="'.get_term_link( $category , $args['taxonomy'] ).'" data = '.$category->slug.' data-tax="'.$args['taxonomy'].'"'; //' . esc_url( get_term_link($category) ) . '
		if ( $use_desc_for_title == 0 || empty($category->description) )
			$link .= 'title="' . esc_attr( sprintf(__( 'View all posts filed under %s',ET_DOMAIN ), $cat_name) ) . '"';
		else
			$link .= 'title="' . esc_attr( strip_tags( apply_filters( 'category_description', $category->description, $category ) ) ) . '"';


		$link .= '>';
		$link .= '<span class="dot"></span>'.$cat_name . '</a>';


		if ( !empty($show_count) )
			$link .= ' (' . intval($category->count) . ')';

		/**
         * check cat is root and has children add icon next
        */
        if( $category->has_child ){
            if($args['hierarchical'])
                $link   =   '<div class="border-bottom" >'.$link.'<i class="fa fa-arrow-right"></i></div>';
            else
                $link   =   '<div class="border-bottom" >'.$link.'</div>';
        }
        else if($category->parent == 0)  {
            $link   =   '<div class="border-bottom" >'.$link.'</div>';
        }

		if ( 'list' == $args['style'] ) {
			$output .= "\t<li";
			$class = 'cat-item cat-item-' . $category->term_id;
			if ( !empty($current_category) ) {
				$_current_category = get_term( $current_category, $category->taxonomy );
				if ( $category->term_id == $current_category )
					$class .=  ' current-cat';
				elseif ( $category->term_id == $_current_category->parent )
					$class .=  ' current-cat-parent';
			}
			$output .=  ' class="' . $class . '"';
			$output .= ">$link\n";
		} else {
			$output .= "\t$link<br />\n";
		}
	}


}

function ce_walk_taxonomy_list_tree() {
    $args = func_get_args();
    // the user's options are the third parameter
    if ( empty($args[2]['walker']) || !is_a($args[2]['walker'], 'Walker') )
        $walker = new CE_Walker_Taxonomy_List;
    else
        $walker = $args[2]['walker'];

    return call_user_func_array(array( &$walker, 'walk' ), $args );
}

/**
 * print mobile taxonomy list
*/
function ce_mobile_taxonomy_list($args =''){
    $defaults = array(
        'show_option_all' => '', 'show_option_none' => __('Category list is empty.',ET_DOMAIN),
        'orderby' => 'name', 'order' => 'ASC',
        'style' => 'list',
        'show_count' => 0, 'hide_empty' => 0,
        'use_desc_for_title' => 1, 'child_of' => 0,
        'feed' => '', 'feed_type' => '',
        'feed_image' => '', 'exclude' => '',
        'exclude_tree' => '', 'current_category' => 0,
        'hierarchical' => true, 'title_li' => __( 'Categories', ET_DOMAIN ),
        'echo' => 1, 'depth' => 3,
        'taxonomy' => CE_AD_CAT
    );

    $r = wp_parse_args( $args, $defaults );

    if ( !isset( $r['class'] ) )
        $r['class'] = ( 'category' == $r['taxonomy'] ) ? 'categories' : $r['taxonomy'];

    extract( $r );

    if ( !taxonomy_exists($taxonomy) )
        return false;
    if($taxonomy == CE_AD_CAT ) {
    	$categories = ce_get_categories( $r );
    }
    if($taxonomy == 'ad_location') {
    	$categories = ce_get_locations( $r );
    }

    $categories	=	apply_filters( 'ce_mobile_taxonomy_list', $categories , $taxonomy );

    foreach ( $categories as $key => $value ) {
        $value->has_child   =   0;
        if($value->parent != 0) continue;
        foreach ($categories as $key => $value_2) {
            if($value->term_id == $value_2->parent)
                $value->has_child   =   1;
        }
    }

    $output = '';
    $output.='<ul class="catelory-items category-items '.$taxonomy.'" data-tax="'.$taxonomy.'">';


    if ( empty( $categories ) ) {
        if ( ! empty( $show_option_none ) ) {
            if ( 'list' == $style )
                $output .= '<li>' . $show_option_none . '</li>';
            else
                $output .= $show_option_none;
        }

    } else {
        if ( ! empty( $show_option_all ) ) {
            $posts_page = ( 'page' == get_option( 'show_on_front' ) && get_option( 'page_for_posts' ) ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
            $posts_page = esc_url( $posts_page );
            if ( 'list' == $style )
                $output .= "<li><a data-ajax='false' href='$posts_page'>$show_option_all</a></li>";
            else
                $output .= "<a href='$posts_page'>$show_option_all</a>";
        }

        if ( empty( $r['current_category'] ) && ( is_category() || is_tax() || is_tag() ) ) {
            $current_term_object = get_queried_object();
            if ( $current_term_object && $r['taxonomy'] === $current_term_object->taxonomy )
                $r['current_category'] = get_queried_object_id();
        }
        $depth = 3;
        $output .= ce_walk_taxonomy_list_tree( $categories, $depth, $r );
    }

    $output .='</ul></div></div>';

    $output = apply_filters( 'ce_mobile_taxonomy', $output, $args );

    if ( $echo )
        echo $output;
    else
        return $output;


}


class themeslug_walker_nav_menu extends Walker_Nav_Menu {
	// add main/sub classes to li's and links
	 function start_el( &$output, $item, $depth = 0, $args = array(), $id=0 ) {
	    global $wp_query;
	    $indent = ( $depth > 0 ? str_repeat( "\t", $depth ) : '' ); // code indent
	    // depth dependent classes
	    $depth_classes = array(
	        ( $depth == 0 ? 'main-menu-item' : 'sub-menu-item' ),
	        ( $depth >=2 ? 'sub-sub-menu-item' : '' ),
	        ( $depth % 2 ? 'menu-item-odd' : 'menu-item-even' ),
	        'menu-item-depth-' . $depth
	    );
	    $depth_class_names = esc_attr( implode( ' ', $depth_classes ) );
	    // passed classes
	    $classes = empty( $item->classes ) ? array() : (array) $item->classes;
	    $class_names = esc_attr( implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) ) );
	    // build html
	    $output .= $indent . '<li id="nav-menu-item-'. $item->ID . '" class="' . $depth_class_names . ' ' . $class_names . '">';

	    // link attributes
	    $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
	    $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
	    $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
	    $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
	    $attributes .= ' class="menu-link ' . ( $depth > 0 ? 'sub-menu-link' : 'main-menu-link' ) . '"';

	    $attributes .= ' data-ajax="false" ';
	    $item_output = sprintf( '%1$s<a%2$s>%3$s%4$s%5$s</a>%6$s',
	        $args->before,
	        $attributes,
	        $args->link_before,
	        apply_filters( 'the_title', $item->title, $item->ID ),
	        $args->link_after,
	        $args->after
	    );
	    // build html
	    $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}
}



/*
 * load more post action
 */
add_action ('wp_ajax_et-mobile-load-more-post', 'et_mobile_load_more_post');
add_action ('wp_ajax_nopriv_et-mobile-load-more-post', 'et_mobile_load_more_post');
function et_mobile_load_more_post () {
	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );

	$page 		=	isset($_POST['page']) ? $_POST['page'] : 1;
	$template	=	isset($_POST['template']) ? $_POST['template'] : 'category';

	if( $template == 'date' ) {
		$query	=	new WP_Query( $_POST['template_value'].'&post_status=publish&paged='.$page);
	} else {
		$term	=	get_term_children($_POST['template_value'], 'category');
		$term[]	=	$_POST['template_value'];
		$term  	=	implode($term, ',');
		$args	=	array (
			'post_status'	=>	 'publish',
			'post_type'		=>	 'post',
			'paged' 		=> 	 $page ,
			'cat'			=>	 $term
		);
		$query	=	new WP_Query($args);
	}

	$data 	=	'';

	if($query->have_posts()) {
		while($query->have_posts()) {

			$query->the_post();
			global $post;
			$date		=	get_the_date('d S M Y');
			$date_arr	=	explode(' ', $date );

			$cat		=	wp_get_post_categories($post->ID);

			$cat		=	get_category($cat[0]);

	 		$data 		.= '<li>
                    <div class="infor-resume clearfix" style="border-bottom:none !important;">
                    	<span class="arrow-right"></span>
                        <div class="thumb-img" style="margin-left: 0px !important;">
                            <a href="'.get_author_posts_url($post->post_author).'">'.get_avatar( $post->post_author, 50 ).'</a>
                        </div>
                        <div class="intro-text">
                            <h1>'.get_the_author().'</h1>
                            <p class="blog-date">
                            '.get_the_date().' ,
                            	<span>
                                    <a href="'.get_category_link( $cat ).'" class="ui-link">
                                        '.$cat->name.'                                   </a>
                                </span>&nbsp; &nbsp;
	                            <span class="blog-count-cmt">
	                            	<span class="icon" data-icon="q"></span>'.get_comments_number().'
	                            </span>
                            </p>
                        </div>
                    </div>
                    <div class="blog-content">
                        <a href="'.get_permalink().'" class="blog-title">
							'.get_the_title().'
                        </a>
                        <div class="blog-text">
                        	'.get_the_post_thumbnail().'
                            '.apply_filters('the_excerpt', get_the_excerpt()).'
                        </div>
                    </div>
                </li>';
        }
        echo json_encode(array (
        	'data'		=>	$data,
        	'success'	=>	 true,
        	'msg'		=>	'',
        	'total'		=>  $query->max_num_pages
        ))	;
	} else {
	 		echo json_encode(array (
        	'data'		=>	$data,
        	'success'	=>	 false,
        	'msg'		=>	__('There is no posts yet.', ET_DOMAIN)
        ))	;
	}
	exit;
}



/*
 * function process post ad on mobile
 * @return : array : success : true or false
 * if false include a msg, if tru include a redirect url
*/
function ce_mobile_process_post_ad () {

	global $user_ID;
	$request	=	$_REQUEST;
	$response	=	array();
	$package	=	false;
	/**
	 * check if the user is using the right package
	*/
	if( isset($request['et_payment_package']) && $request['et_payment_package'] != '' ) {
		// $use_package	=	ET_Seller::check_use_package( $request['et_payment_package'] );
		$package		=	ET_PaymentPackage::get( $request['et_payment_package'] );

	}

	$check	=	ET_Seller::ce_limit_free_plan ($package);
	if( $check['success'] ) {
		$response['success']	=	 false;
		$response['msg']		=	 $check['msg'];
		return $response;
	}

	$method		=	'create';

	if(isset( $_POST['ID']) ) $method	=	'update';

	if( isset( $_FILES['et_carousel']) ) {

		$request['et_carousels']	=	array();
		$number_of_files	=	count( $_FILES['et_carousel']['name']);
		$max_carousels		=	get_theme_mod( 'ce_number_of_carousel', 15 );

		// check max number of carousel
		$number_of_files	=	($number_of_files >= $max_carousels ) ? $max_carousels : $number_of_files;

		for( $i = 0; $i < $number_of_files; $i++ ) {

			$file['name']		=	$_FILES['et_carousel']['name'][$i];
			$file['type']		=	$_FILES['et_carousel']['type'][$i];
			$file['tmp_name']	=	$_FILES['et_carousel']['tmp_name'][$i];
			$file['size']		=	$_FILES['et_carousel']['size'][$i];
			$file['error']		=	$_FILES['et_carousel']['error'][$i];

			// handle file upload
			$attach_id	=	et_process_file_upload( $file, $user_ID, 0, array(
								'jpg|jpeg|jpe'	=> 'image/jpeg',
								'gif'			=> 'image/gif',
								'png'			=> 'image/png',
								'bmp'			=> 'image/bmp',
								'tif|tiff'		=> 'image/tiff'
								)
							);

			if ( !is_wp_error($attach_id) ) {

				$attach_data	= et_get_attachment_data($attach_id);
				$res	= array(
					'attach_id'	=> $attach_id,
					'success'	=> true,
					'msg'		=> __('Image upload success!', ET_DOMAIN ),
					'data'		=> $attach_data
				);

				$request['et_carousels'][]	=	 $attach_id;

			}
		}

	}

	$request[CE_AD_CAT]	=	explode(',',  $_REQUEST[CE_AD_CAT] );

	$return	=	CE_Ads::sync( $method , $request );

	if(!is_wp_error( $return ) ) {

		$response['success']	=	true;

		if( $package ) {

			$check = ET_Seller::package_or_free( $package->ID, $return); // check use package or free to return url

			if( $check['success'] )	{
				$response['url']	=	$check['url'];
				return $response;
			}

		}

		$adID			=	$return->ID;

		$options	=	new CE_Options();
		if( !$options->use_pending() && et_get_payment_disable() ){
			wp_update_post( array('ID' => $return->ID, 'post_status' => 'publish') );
		}
		// end disable payment gatewway

		$response['url']	= et_get_page_link('post-ad' , array ('ad_id' => $adID ));

	} else  {
		$response	=	array('success' => false, 'msg' => $return->get_error_message());
	}

	return $response;

}

add_action("ce_on_add_scripts_mobile","ce_on_add_scripts_mobile");
function ce_on_add_scripts_mobile(){
	$use_minify = get_theme_mod( 'ce_minify', 0 );
	//if(is_page_template("page-post-ad.php")){
		wp_enqueue_script( 'et-googlemap-api');
		wp_enqueue_script("gmap");
	//}
}


