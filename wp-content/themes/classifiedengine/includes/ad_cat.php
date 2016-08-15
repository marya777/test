<?php

class ET_AdCatergory extends ET_TaxCategory
{
	CONST AD_CAT = CE_AD_CAT;

	static $instance	=	null;
	protected $_tax_name    = CE_AD_CAT;
    protected $_order       = 'et_ad_category_order';
    protected $_transient   = CE_AD_CAT;
    protected $_tax_label   =  'Ad Category';
    // protected $_color       =  'job_available_colors';

	public static function register ($slug = '') {

        $slug   =   get_theme_mod( self::AD_CAT , 'ad_cat' );

		register_taxonomy( self::AD_CAT , CE_Ads::POST_TYPE,  array(
				'hierarchical'      => true,
				'labels'            => array(
					'name'              => __( 'Categories', ET_DOMAIN ),
					'singular_name'     => __( 'Category', ET_DOMAIN ),
					'search_items'      => __( 'Search Categories',ET_DOMAIN ),
					'all_items'         => __( 'All Categories',ET_DOMAIN ),
					'parent_item'       => __( 'Parent Category',ET_DOMAIN ),
					'parent_item_colon' => __( 'Parent Category:',ET_DOMAIN ),
					'edit_item'         => __( 'Edit Category',ET_DOMAIN ),
					'update_item'       => __( 'Update Category',ET_DOMAIN ),
					'add_new_item'      => __( 'Add New Category',ET_DOMAIN ),
					'new_item_name'     => __( 'New Category Name',ET_DOMAIN ),
					'menu_name'         => __( 'Category',ET_DOMAIN ),
				),
				'show_ui'           => true,
				'show_admin_column' => false,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => $slug ),
			)
		);

		self::get_instance()->register_action ();


	}

	public static function get_instance () {
		if ( self::$instance == null){
			self::$instance = new ET_AdCatergory();
		}
		return self::$instance;
	}

    public static function slug() {
        return get_theme_mod( self::AD_CAT , 'ad_cat' );
    }

	public function __construct () {
		$this->_tax_name			=	self::AD_CAT;
	}

	public static function get_category_list ( $args=array('hide_empty' => false) ) {
		$instance	= self::get_instance();
		$terms 	=	$instance->get_terms_in_order ($args);

        if( isset($args['hide_empty']) && $args['hide_empty'] ) {
            foreach ($terms as $key => $term) {
                if($term->count == 0 ) unset($terms[$key]);
            }
        }

		if( empty($terms) || defined('WPML_LOAD_API_SUPPORT') ) {
			$terms   =   get_terms( self::AD_CAT,  wp_parse_args( $args , array('hide_empty' => false ) ));
		}
		return $terms;
	}
	/**
	 * register action do with ad category
	*/
	public function register_action  () {

		add_action( 'delete_'.$this->_tax_name, array(&$this,'delete_transient' ) );
		add_action( 'created_'.$this->_tax_name,array(&$this, 'delete_transient' ));

        add_action( 'edited_'.$this->_tax_name,array(&$this, 'update_transient' ));

        add_action('save_post' , array(&$this, 'update_transient' ) );

	}

    function update_transient () {
        delete_transient( $this->_transient );
    }

    public function delete_transient ( $term_id ) {

        $order      = (array)get_option($this->_order);
        $term       = get_term_by( 'id', $term_id, $this->_tax_name );
        if($term && $term->parent) {
            $flag   =   0;
            foreach ($order as $key => $value) {
                if($value['item_id'] == $term->parent)  {
                    $flag = 1;
                    continue;
                }
                if($flag == 1 && $value['parent_id'] != $term->parent )  break;
            }
            array_splice( $order, $key , 0, array( array('item_id' => $term_id, 'parent_id' => $term->parent ) ) );
        }

        update_option( $this->_order, $order );

        delete_transient( $this->_transient );

    }
	/**
	 * register ajax action synce with ad category
	*/
	public function register_ajax () {
		add_action ('wp_ajax_et_sort_'.CE_AD_CAT, array(&$this, 'sort_terms'));
        add_action ('wp_ajax_et_sync_'.CE_AD_CAT, array(&$this, 'sync_term'));
	}

}

class ET_AdLocation extends ET_TaxCategory
{
	CONST AD_LOCATION = 'ad_location';

	static $instance	=	null;
	protected $_tax_name    = 'ad_location';
    protected $_order       = 'et_ad_location_order';
    protected $_transient   = 'ad_location';
    protected $_tax_label   = 'Ad Location';
    // protected $_color       =  'job_available_colors';

	public static function register ( $slug = '' ) {

		$slug   =   get_theme_mod( self::AD_LOCATION , 'location' );

		register_taxonomy( self::AD_LOCATION , CE_Ads::POST_TYPE,  array(
			'hierarchical'      => true,
			'labels'            => array(
				'name'              => __( 'Locations', ET_DOMAIN ),
				'singular_name'     => __( 'Location', ET_DOMAIN ),
				'search_items'      => __( 'Search Locations',ET_DOMAIN ),
				'all_items'         => __( 'All Locations',ET_DOMAIN ),
				'parent_item'       => __( 'Parent Location',ET_DOMAIN ),
				'parent_item_colon' => __( 'Parent Location:',ET_DOMAIN ),
				'edit_item'         => __( 'Edit Location',ET_DOMAIN ),
				'update_item'       => __( 'Update Location',ET_DOMAIN ),
				'add_new_item'      => __( 'Add New Location',ET_DOMAIN ),
				'new_item_name'     => __( 'New Location Name',ET_DOMAIN ),
				'menu_name'         => __( 'Location',ET_DOMAIN ),
			),
			'show_ui'           => true,
			'show_admin_column' => false,
			'query_var'         => true,
			'hierarchical'		=> true,
			'rewrite'           => array( 'slug' => $slug ),
			)
		);
		self::get_instance()->register_action();
	}

	public function __construct () {
		$this->_tax_name			=	self::AD_LOCATION;
	}

	public static function get_instance () {
		if ( self::$instance == null){
			self::$instance = new ET_AdLocation();
		}
		return self::$instance;
	}

    public static function slug() {
        return get_theme_mod( self::AD_LOCATION , 'location' );
    }

	public static function get_location_list ( $args=array('hide_empty' => false)) {
		$instance	= self::get_instance();
		$terms 	=	$instance->get_terms_in_order ($args);
        if( isset( $args['hide_empty'] ) && $args['hide_empty'] ) {
            foreach ($terms as $key => $term) {
                if($term->count == 0 ) unset($terms[$key]);
            }
        }
		return $terms;
	}

	 /**
     * print backend term list, can override if change template
    */
    function print_backend_terms () {
        ?>
        <ul class="list-job-input list-tax category list-job-categories cat-sortable tax-sortable" data-tax="<?php echo $this->_tax_name ?>">
        <?php
            $this->print_backend_terms_li () ;
        ?>
        </ul>
        <ul class="list-job-input category add-category ">
            <li class="tax-item">
                <form class="new_tax" action="" data-tax='<?php echo $this->_tax_name ?>'>
                    <div class="controls controls-2">
                        <div class="button">
                            <span class="icon" data-icon="+"></span>
                        </div>
                    </div>
                    <div class="input-form input-form-2 color-default">
                        <input class="bg-grey-input" placeholder="<?php _e('Add a location', ET_DOMAIN) ?>" type="text" />
                    </div>
                </form>
            </li>
        </ul>
        <?php
    }

    /**
	 * register action do with ad location
	*/
    public function register_action  () {

        add_action( 'delete_'.$this->_tax_name, array(&$this,'delete_transient' ) );
        add_action( 'created_'.$this->_tax_name,array(&$this, 'delete_transient' ));

        add_action( 'edited_'.$this->_tax_name,array(&$this, 'delete_transient' ));

        add_action('save_post' , array(&$this, 'update_transient' ) );

    }

     function update_transient () {
        delete_transient( $this->_transient );
    }

    public function delete_transient ($term_id) {
        //echo 1;
        $order      = (array)get_option($this->_order);
        $term       = get_term_by( 'id', $term_id, $this->_tax_name );
        if($term && $term->parent) {
            $flag   =   0;
            foreach ($order as $key => $value) {
                if($value['item_id'] == $term->parent)  {
                    $flag = 1;
                    continue;
                }
                if($flag == 1 && $value['parent_id'] != $term->parent )  break;
            }
            array_splice( $order, $key , 0, array( array('item_id' => $term_id, 'parent_id' => $term->parent ) ) );
        }

        update_option( $this->_order, $order );

        delete_transient( $this->_transient );
    }
	/**
	 * register ajax action sync with ad location
	*/
    public function register_ajax () {
		add_action ('wp_ajax_et_sort_ad_location', array(&$this, 'sort_terms'));
        add_action ('wp_ajax_et_sync_ad_location', array(&$this, 'sync_term'));
	}

}

// class display list category in sidebar.
class  CE_Walker_Category extends Walker_Category{
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
        if($depth == 0){
            $sclass = 'icon-next ';
            $uclass = 'menu-child';
        } else {
            // $sclass ='icon-next-third';
            $uclass = 'menu-third-child';
            $sclass ='';
            //$uclass ='';
        }

        $indent =   str_repeat("\t", $depth);

        $output .= "$indent <ul class='".$uclass."'>\n";

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

        $c_str  =   ET_AdCatergory::slug();
        $l_str  =   ET_AdLocation::slug();

        // for url search.
        $ad_cat           =   ( get_query_var( $c_str ));
        $ad_location      =   ( get_query_var( $l_str ));

        if( is_tax( CE_AD_CAT ) ) {
            global $wp_query;
            $queried_object =   $wp_query->get_queried_object();
            $ad_cat         =   $queried_object->slug;
        }

        if( is_tax( 'ad_location') ) {
            global $wp_query;
            $queried_object =   $wp_query->get_queried_object();
            $ad_location    =   $queried_object->slug;
        }

        if(!$ad_location) {
            if(isset($_COOKIE['et_location'])) {
                $ad_location = $_COOKIE['et_location'];
            }
        }
        /** for url sidebar
        // get term default with taxonomy = ad_category .
        // ex : abc.com/ce/ad_cat/entertainment/ ==> ad_category = entertaiment.
        */
        $cat_name = esc_attr( $category->name );
        $cat_slug = esc_attr($category->slug);
        $cat_name = apply_filters( 'list_cats', $cat_name, $category );

        /**
         * term href
        */
        $href = esc_url(get_term_link( $category ));
        $current_query_var  =   get_query_var( $tax_slug );
        if(!is_home() && !is_single() && !is_tax($args['taxonomy']) && !is_page() ) {

            if( $args['taxonomy'] == CE_AD_CAT )
                $href   =   add_query_arg(array( $c_str => $category->slug ));

            if($args['taxonomy'] == 'ad_location')
                $href   =   add_query_arg(array($l_str  => $category->slug ));
        }

        if(!empty( $current_query_var ) && $current_query_var == $category->slug ) {
            $href   =   remove_query_arg( $tax_slug , $href );
        }


        $link = '<a class="customize_text" href="' . $href . '" ';
        if ( $use_desc_for_title == 0 || empty($category->description) )
            $link .= 'title="' . esc_attr( sprintf(__( 'View all posts filed under %s',ET_DOMAIN ), $cat_name) ) . '"';
        else
            $link .= 'title="' . esc_attr( strip_tags( apply_filters( 'category_description', $category->description, $category ) ) ) . '"';
        $link .= '>';
        $link .= $cat_name . '</a>';

        if ( !empty($feed_image) || !empty($feed) ) {
            $link .= ' ';

            if ( empty($feed_image) )
                $link .= '(';

            $link .= '<a href="' . esc_url( get_term_feed_link( $category->term_id, $category->taxonomy, $feed_type ) ) . '"';

            if ( empty($feed) ) {
                $alt = ' alt="' . sprintf(__( 'Feed for all posts filed under %s',ET_DOMAIN ), $cat_name ) . '"';
            } else {
                $title = ' title="' . $feed . '"';
                $alt = ' alt="' . $feed . '"';
                $name = $feed;
                $link .= $title;
            }

            $link .= '>';

            if ( empty($feed_image) )
                $link .= $name;
            else
                $link .= "<img src='$feed_image'$alt$title" . ' />';

            $link .= '</a>';

            if ( empty($feed_image) )
                $link .= ')';
        }

        if ( !empty($show_count) )
            $link .= '<span class="cat-count"> (' . intval($category->count) . ')</span>';
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

            if( $category->slug == $ad_cat || ( $category->slug == $ad_location && !is_page_template( 'page-sellers.php' )) )
                $class .=  ' clicked active';

            if ( !empty($current_category) ) {
                $_current_category = get_term( $current_category, $category->taxonomy );
                if ( $category->term_id == $current_category )
                    $class .=  ' clicked active';
                elseif ( $category->term_id == $_current_category->parent )
                    $class .=  ' clicked active';
            }
            // echo $class;
            $output .=  ' class="' . $class . '"';
            $output .= ">$link\n";
        } else {
            $output .= "\t$link<br />\n";
        }
    }

}


/**
 * list ce categories
*/
function ce_list_categories($args =''){
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
        'taxonomy' => CE_AD_CAT , 'tax_slug'     => ''
    );

    $r = wp_parse_args( $args, $defaults );

    if ( !isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] )
        $r['pad_counts'] = true;

    if ( true == $r['hierarchical'] ) {
        $r['exclude_tree'] = $r['exclude'];
        $r['exclude'] = '';
    }

    if ( !isset( $r['class'] ) )
        $r['class'] = ( 'category' == $r['taxonomy'] ) ? 'categories' : $r['taxonomy'];

    extract( $r );

    if ( !taxonomy_exists($taxonomy) )
        return false;
     if($taxonomy == CE_AD_CAT ) {
         $categories = ce_get_categories ( $args );
     } else {
         $categories = ce_get_locations ( $args );
     }
    //$categories = get_categories( $r );

    foreach ( $categories as $key => $value ) {
        $value->has_child   =   0;
        if($value->parent != 0) continue;
        foreach ($categories as $key => $value_2) {
            if($value->term_id == $value_2->parent)
                $value->has_child   =   1;
        }
    }

    $output = '';
    if ( $title_li && 'list' == $style )
            $output ='<ul class="nav nav-list menu-left">';
             // $output = '<li title ="123" class="' . esc_attr( $class ) . '">' . $title_li . '<ul>'

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
            $posts_page = esc_url( $posts_page . '?location=alllocation' );
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
        if ( $hierarchical )
            $depth = $r['depth'];
        else
            $depth = -1; // Flat.

        $output .= ce_walk_category_tree( $categories, $depth, $r );
    }

    if ( $title_li && 'list' == $style )
        $output .='</ul>';

    $output = apply_filters( 'ce_list_categories', $output, $args );

    if ( $echo )
        echo $output;
    else
        return $output;


}

function ce_walk_category_tree() {
    $args = func_get_args();
    // the user's options are the third parameter
    if ( empty($args[2]['walker']) || !is_a($args[2]['walker'], 'Walker') )
        $walker = new CE_Walker_Category;
    else
        $walker = $args[2]['walker'];

    return call_user_func_array(array( &$walker, 'walk' ), $args );
}

/**
 * helper function to get location list
*/
function ce_get_locations($args = array()) {
    return ET_AdLocation::get_location_list ($args);
}

/**
 * helper function to get ad categories list
*/
function ce_get_categories ( $args = array() ) {
    return ET_AdCatergory::get_category_list ($args);
}
/**
* get list locations.
*
**/
function ce_list_locations( $args = array() ){
    $args = wp_parse_args( $args, array('echo'=>1) );

    //$c_str  =   ET_AdCatergory::slug();
    $l_str  =   ET_AdLocation::slug();

    $args['taxonomy']           = 'ad_location';
    $args['show_option_none']   = __('Location list is empty.',ET_DOMAIN);
    $args['tax_slug']          = $l_str;

    if($args['echo'] == 1)
        ce_list_categories($args);
    else
        return ce_list_categories($args);
}

/**
 * list all subcat in page categories, locations
*/
function ce_list_all_subcat($categories, $parent, $taxonomy) {
    $current    =   $parent;

    $i = 0;
    foreach ($categories as $key => $value) {
        if( $value->parent == $parent->term_id || $value->parent == $current->term_id )  {
            $current    =   $value;
            if( $i == 0 ) {
                echo '<i class="fa fa-arrow-right"></i>';
                echo '<ul>';
            }
            $i= 1;
        ?>
            <li class="child" style="display:none;" >
                <a href="<?php echo get_term_link( $value, $taxonomy ); ?>" >
                    <?php echo $value->name; ?>
                </a>
            </li>
        <?php
        }
    }
    if($i== 1)
        echo '</ul>';
}

/**
 * get term list
*/
function ce_get_terms ( $taxonomies, $args) {
    switch ($taxonomies) {
        case 'ad_location':
            $terms  =   ce_get_locations ( $args);
            break;
        case CE_AD_CAT:
            $terms  =   ce_get_categories ( $args);
            break;

        default:
            $terms =  null;
            break;
    }

    return apply_filters( 'ce_get_terms' ,$terms , $taxonomies, $args );
}


function ce_dropdown_tax ( $tax, $args = '') {
    $defaults = array(
        'show_option_all' => '', 'show_option_none' => '',
        'orderby' => 'id', 'order' => 'ASC',
        'show_count' => 0,
        'hide_empty' => 0, 'child_of' => 0,
        'exclude' => '', 'echo' => 1,
        'selected' => 0, 'hierarchical' => 1,
        'name' => $tax, 'id' => $tax,
        'class' => 'postform', 'depth' => 0,
        'tab_index' => 0, 'taxonomy' => $tax,
        'hide_if_empty' => false ,
        'attr' => ''
    );

    $defaults['selected'] = ( is_category() ) ? get_query_var( 'cat' ) : 0;

    // Back compat.
    if ( isset( $args['type'] ) && 'link' == $args['type'] ) {
        _deprecated_argument( __FUNCTION__, '3.0', '' );
        $args['taxonomy'] = 'link_category';
    }

    $r = wp_parse_args( $args, $defaults );

    if ( !isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] ) {
        $r['pad_counts'] = true;
    }

    extract( $r );

    $tab_index_attribute = '';
    if ( (int) $tab_index > 0 )
        $tab_index_attribute = " tabindex=\"$tab_index\"";

    $categories = ce_get_terms( $taxonomy, $r );

    $name = esc_attr( $name );
    $class = esc_attr( $class );
    $id = $id ? esc_attr( $id ) : $name;

    $attribute  =   '';
    if(!empty( $attr )) {
        foreach ($attr as $key => $value) {
            $attribute .= $key ."='".$value."'";
        }
    }

    if ( ! $r['hide_if_empty'] || ! empty($categories) )
        $output = "<select name='$name' id='$id' ".$attribute." class='$class' $tab_index_attribute>\n";
    else
        $output = '';

    if ( empty($categories) && ! $r['hide_if_empty'] && !empty($show_option_none) ) {
        $show_option_none = apply_filters( 'list_cats', $show_option_none );
        $output .= "\t<option value='-1' selected='selected'>$show_option_none</option>\n";
    }

    if ( ! empty( $categories ) ) {

        if ( $show_option_all ) {
            $show_option_all = apply_filters( 'list_cats', $show_option_all );
            $selected = ( '0' === strval($r['selected']) ) ? " selected='selected'" : '';
            $output .= "\t<option value=''$selected>$show_option_all</option>\n";
        }

        if ( $show_option_none ) {
            $show_option_none = apply_filters( 'list_cats', $show_option_none );
            $selected = ( '-1' === strval($r['selected']) ) ? " selected='selected'" : '';
            $output .= "\t<option value='-1'$selected>$show_option_none</option>\n";
        }

        if ( $hierarchical )
            $depth = $r['depth'];  // Walk the full depth.
        else
            $depth = -1; // Flat.

        $output .= walk_category_dropdown_tree( $categories, $depth, $r );
    }

    if ( ! $r['hide_if_empty'] || ! empty($categories) )
        $output .= "</select>\n";

    $output = apply_filters( 'wp_dropdown_cats', $output );

    if ( $echo )
        echo $output;

    return $output;
}