<?php
/**
 * this file contain function render template in front end
*/



/**
 * print seller bar
 * contain base infomation
*/
if( !function_exists('ce_seller_bar')):
	function ce_seller_bar( $seller ) {
		global $post;
		$i = 0;
		$args	=	array('author'=>$seller->ID,'showposts'=>-1 , 'meta_key' => ET_FEATURED,'post_type' => CE_AD_POSTTYPE, 'post_status' => 'publish');
		if($post) {
			$args['post__not_in']	=	array( $post->ID );
		}
		$ads = CE_Ads::query($args);
		$post_author_url	=	get_author_posts_url( $seller->ID);

	?>
    <div class="seller-profile" <?php echo Schema::Product("brand")?>>
        <form class="info-seller">
            <div class="intro-profle clearfix">
            	<a href="<?php echo $post_author_url; ?>" title="<?php printf(__("View all ads by %s", ET_DOMAIN), $seller->display_name) ?>" >
                	<div class="avatar-info-seller" <?php echo Schema::Organization("logo"); ?>>
	                	<?php echo get_avatar( $seller->ID, '60' ); ?>
                    </div>
                    <div class="info-location-seller">
	                    <h3 class="colorgray seller-name" <?php echo Schema::Organization("legalName") ?> ><?php echo $seller->display_name; ?></h3>
	               		<span class="location" <?php echo Schema::Organization("address") ?>><span <?php echo Schema::PostalAddress("addressCountry") ?>><?php echo ( get_user_meta($seller->ID,'user_location',true) != '') ?  get_user_meta($seller->ID,'user_location',true) : __("No location specified", ET_DOMAIN); ?></span></span><br />
                    	<span class="date-join"><?php printf(__("Joined on %s", ET_DOMAIN), date_i18n( get_option('date_format') ,strtotime($seller->user_registered))) ?> </span>
                    </div>
	            </a>

	            <button type="submit" class="btn btn-primary sembold"><?php _e("Send this seller a message", ET_DOMAIN); ?></button>
            </div>
            <div class="text-profile">

                <p class="text-phone">
                    <span class="colorgreen"><?php _e("Phone Number", ET_DOMAIN); ?></span><br />
                    <span <?php echo Schema::Organization("telephone"); ?>><?php if( $seller->et_phone != '' )echo $seller->et_phone; else _e("No phone provided.", ET_DOMAIN); ?></span>
                     <input type="hidden" name="seller_id" id="seller_id" value = "<?php echo $seller->ID;?>" />
                     <input type="hidden" name="ad_id" id="ad_id" value = "<?php the_ID();?>" />
                </p> 
                <p class="text-address">
                    <span class="colorgreen"><?php _e("Address", ET_DOMAIN); ?></span><br />
                    <?php 
                    if( is_singular( CE_AD_POSTTYPE ) ) {
                    	$ad_location	=	get_post_meta( $post->ID, 'et_full_location', true );
                    	if($ad_location) echo $ad_location; else   _e("No address", ET_DOMAIN);
                    } else {
                    	if( $seller->et_address != '') echo $seller->et_address; else _e("No address", ET_DOMAIN);
                    } ?>
                </p>
                <?php do_action( 'ce_seller_add_info' , $seller ); ?>
            </div>
            <?php 
            	do_action( 'ce_seller_bar' , $seller );

            if($ads->have_posts()) { ?>
            	<div class="clearfix"></div>
	        	<ul class="list-seller-img">
	        		<?php while( $ads->have_posts() ) { $ads->the_post();	$i++;
	        			if($i == 4) break;
	        		?>
	                	<li><a title="<?php the_title(); ?>" href="<?php the_permalink(); ?>"><?php echo get_the_post_thumbnail(get_the_ID(), 'thumbnail' ); ?></a></li>
	                <?php } ?>
	                <?php if( $ads->found_posts > 3 ) { ?>
	                	<li class="last"><a  href="<?php echo get_author_posts_url($seller->ID); ?>" title="<?php printf(__("View more ads by %s", ET_DOMAIN), $seller->display_name ) ?>"><?php echo ($ads->found_posts - 3) ?>+</a></li>
	                <?php } ?>
	            </ul>
            <?php } ?>
            <div class="responsive-hide">
            	<button type="submit" class="btn btn-primary sembold"><?php _e("Send this seller a message", ET_DOMAIN); ?></button>
            </div>
        </form>
    </div> 

<?php wp_reset_query();
}
endif;

function ce_editor_settings($args = array()){
	 return apply_filters( 'ce_editor_settings', array(
		'quicktags'  	=> true,
		'media_buttons' => false,
		'wpautop'		=> false,
		'tinymce'   	=> array(
			'content_css'	=> get_template_directory_uri() . '/js/lib/tiny_mce/content.css',
			'height'   => 200,
			'autoresize_min_height'=> 200,
			'autoresize_max_height'=> 350,
			'theme_advanced_buttons1' => 'bold,italic,|,link,unlink,bullist,numlist',
			'theme_advanced_buttons2' => '',
			'theme_advanced_buttons3' => '',
			'theme_advanced_statusbar_location' => 'none',
			'setup' =>  "function(ed){
				ed.onChange.add(function(ed, l) {
					var content	= ed.getContent();
					if(ed.isDirty() || content === '' ){
						ed.save();
						jQuery(ed.getElement()).blur(); // trigger change event for textarea
					}

				});

				// We set a tabindex value to the iframe instead of the initial textarea
				ed.onInit.add(function() {
					var editorId = ed.editorId,
						textarea = jQuery('#'+editorId);
					jQuery('#'+editorId+'_ifr').attr('tabindex', textarea.attr('tabindex'));
					textarea.attr('tabindex', null);
				});
			}"
		)
	));
}

function ce_ad_editor_settings() {
	return apply_filters( 'ce_ad_editor_settings', array(
		'quicktags'  => false,
		'media_buttons' => false,
		'wpautop'	=> false,
		//'tabindex'	=>	'2',
		'teeny'		=> true,
		'tinymce'   => array(
			'content_css'	=> get_template_directory_uri() . '/js/lib/tiny_mce/content.css',
			'height'   => 250,
			'autoresize_min_height'=> 250,
			'autoresize_max_height'=> 550,
			'theme_advanced_buttons1' => 'bold,|,italic,|,underline,|,bullist,numlist,|,wp_fullscreen',
			'theme_advanced_buttons2' => '',
			'theme_advanced_buttons3' => '',
			'theme_advanced_statusbar_location' => 'none',
			'theme_advanced_resizing'	=> true ,
			'setup' =>  "function(ed){
				ed.onChange.add(function(ed, l) {
					var content	= ed.getContent();
					if(ed.isDirty() || content === '' ){
						ed.save();
						jQuery(ed.getElement()).blur(); // trigger change event for textarea
					}

				});

				// We set a tabindex value to the iframe instead of the initial textarea
				ed.onInit.add(function() {
					var editorId = ed.editorId,
						textarea = jQuery('#'+editorId);
					jQuery('#'+editorId+'_ifr').attr('tabindex', textarea.attr('tabindex'));
					textarea.attr('tabindex', null);
				});
			}"
		)
	));
}

add_filter( 'teeny_mce_buttons', 'ce_teeny_mce_buttons');
function ce_teeny_mce_buttons ($buttons) {
	return array('bold', 'italic', 'underline', 'bullist', 'numlist', 'link', 'unlink');
}

function ce_tinymce_add_plugins($plugin_array){

	$autoresize = get_template_directory_uri() . '/js/lib/tiny_mce/plugins/autoresize/editor_plugin.js';
	//$et_heading = get_template_directory_uri() . '/js/lib/tiny_mce/plugins/et_heading/editor_plugin.js';
	//$wordcount = get_stylesheet_directory_uri() . '/js/lib/tiny_mce/plugins/wordcount/editor_plugin.js';

	//$plugin_array['feimage'] = $feimage;
	if(!is_admin())
		$plugin_array['autoresize'] = $autoresize;

	//$plugin_array['etHeading']	= $et_heading;
	//$plugin_array['wordcount']	= $wordcount;

    return $plugin_array;
}

add_filter('mce_external_plugins','ce_tinymce_add_plugins');



if(!function_exists('ce_seller_item_template')) {
	function ce_seller_item_template() {
		// template-js/seller-item.php
		get_template_part( 'template-js/seller', 'item' );
	}
}
if(!function_exists('ce_template_favorite_ad')) {
	function ce_template_favorite_ad() {
		get_template_part( 'template-js/favorite', 'item' );
	}
}


if(!function_exists('ce_template_post_item')) {
	function ce_template_post_item() {
		// template-js/post-content.php
		get_template_part( 'template-js/post', 'content' );
	}
}

if(!function_exists('ce_template_modal_login')){
	function ce_template_modal_login(){
		// template-js/modal-login.php
		get_template_part( 'template-js/modal' , 'login' );
	}
}

if(!function_exists('ce_template_edit_ad')) {
	function ce_template_edit_ad () {
		// template-js/modal-editad.php
		get_template_part( 'template-js/modal' , 'editad' );
	}
}

if(!function_exists('ce_template_send_message')){
	function ce_template_send_message(){
		// template-js/modal-contact.php
		get_template_part( 'template-js/modal', 'contact' );
	}
}

if(!function_exists('ce_template_modal_reject_ad')){
	function ce_template_modal_reject_ad(){
		// template-js/modal-reject.php
		get_template_part( 'template-js/modal', 'reject' );
	}
}

if(!function_exists('ce_template_modal_review')){
	function ce_template_modal_review(){
		// template-js/modal-reject.php
		get_template_part( 'template-js/modal', 'review' );
		get_template_part( 'template-js/review', 'item' );
	}
}


if(!function_exists('ce_template_ad_item')) {
	function ce_template_ad_item () {

		if( is_page_template ('page-account-listing.php') ){
			// template-js/aditem-account-listing.php
			get_template_part( 'template-js/aditem', 'account-listing' );
		} else {
			// template-js/aditem-home.php
			get_template_part( 'template-js/aditem', 'home' );
		}
	}
}

/**
 * ce single related item
*/
if(!function_exists('ce_single_related_item')) {
	function ce_single_related_item() {
		// template-js/aditem-related.php
		get_template_part( 'template-js/aditem', 'related' );
	}
}



if(!function_exists('ce_single_button_template')) {
	function ce_single_button_template() {
	?>
		<script type="text/template" id="single-ad-button">

			<li class="tooltips update edit"><a data-toggle="tooltip" title="<?php _e("Edit", ET_DOMAIN); ?>" data-original-title="<?php _e("Edit", ET_DOMAIN); ?>" href="#"><span class="icon" data-icon="p"></span></a></li>
			<# if( post_status == 'publish' ) {
				if( parseInt(<?php echo ET_FEATURED; ?>) == 0 ) { #>
		        	<li class="tooltips  flag toggleFeature"><a href="#" data-toggle="tooltip" title="<?php _e("Set as featured", ET_DOMAIN); ?>" data-original-title="<?php _e("Set as featured", ET_DOMAIN); ?>" ><span class="icon" data-icon="^"></span></a></li>
		        <# } else { #>
		        	<li class="tooltips  flag toggleFeature featured"><a href="#" data-toggle="tooltip" title="<?php _e("Remove featured", ET_DOMAIN); ?>" data-original-title="<?php _e("Remove featured", ET_DOMAIN); ?>" ><span class="icon color-yellow" data-icon="^"></span></a></li>
		        <# }
	    	}else {#>
	    		<li class="tooltips remove approve"><a data-toggle="tooltip" title="<?php _e("Approve", ET_DOMAIN); ?>" data-original-title="<?php _e("Approve", ET_DOMAIN); ?>" href="#"><span class="icon color-green" data-icon="3"></span></a></li>
	    	<# } #>
	        <# if( post_status == 'pending') { #>
	            <li class="tooltips remove reject"><a data-toggle="tooltip" title="<?php _e("Reject", ET_DOMAIN); ?>" data-original-title="<?php _e("Reject", ET_DOMAIN); ?>" href="#"><span class="icon color-purple" data-icon="*"></span></a></li>
	        <# } else if( post_status != 'archive') { #>
	            <li class="tooltips  remove archive"><a data-toggle="tooltip" title="<?php _e("Archive", ET_DOMAIN); ?>" data-original-title="<?php _e("Archive", ET_DOMAIN); ?>" href="#"><span class="icon" data-icon="#"></span></a></li>
	        <# } #>

	    </script>
	<?php
	}
}

function ce_single_heading_message() {
?>
	<script type="text/template" id="single-ad-heading-msg">
		<# if( post_status == 'pending' ) { #>
			<div class="main-center">
	            <div class="text">
	                <?php _e("THIS AD IS PENDING. YOU CAN APPROVE OR REJECT IT.", ET_DOMAIN); ?>
	            </div>
	            <div class="arrow"></div>
	        </div>
       	<# } else if(post_status == 'reject') { #>
       		<div class="main-center">
	            <div class="text">
	                <?php _e("THIS AD IS REJECTED.", ET_DOMAIN); ?>
	            </div>
	            <div class="arrow"></div>
	        </div>
       	<# } else if(post_status == 'archive') { #>
       		<div class="main-center">
	            <div class="text">
	                <?php _e("THIS AD IS ARCHIVED.", ET_DOMAIN); ?>
	            </div>
	            <div class="arrow"></div>
	        </div>
       	<# } else if( post_status == 'draft') { #>
       		<div class="main-center">
	            <div class="text">
	                <?php _e("THIS AD IS DRAFT.", ET_DOMAIN); ?>
	            </div>
	            <div class="arrow"></div>
	        </div>
	    <# } #>
	</script>
<?php
}


/**
 * print categories to js
*/
function ce_categories_json () {
	$categories    = ce_get_categories();
	$cats	=	array();
	foreach ($categories as $key => $value) {
		$cat	=	array ('id' => $value->term_id, 'label' => $value->name);
		if($value->parent) {
			$term	=	get_term_by( 'id', $value->parent, CE_AD_CAT );
			if(!$term) continue;
			$i		=	1;
			while ($term->parent != 0) {
				$term	=	get_term_by( 'id', $term->parent, CE_AD_CAT );
				$i ++;
			}
			$cat['css']		=	$i*(20).'px';
			$cat['category']	=	$term->name;

		} else {
			$cat['category'] = $value->name;
		}
		$cats[]	=	$cat;
	}
	?>
	<script type="application/json" id="ce_categories">
	<?php
		echo json_encode($cats);
	?>
	</script>
	<?php
}

function et_block_ie($version, $page){
	$info = getBrowser();
	//if ( $info['name'] == 'Internet Explorer' && version_compare($version, $info['version'], '>=') && file_exists(get_template_directory()  . '/' . $page)){
		if (!is_page_template('page-unsupported.php')){
				// find a template "unsupported"
				// If template doesn't existed, create it

			?>
			<script type="text/javascript">
				var detectBrowser = function () {

					var isOpera = this.check(/opera/);
					var isIE = !isOpera && check(/msie/);
					var isIE8 = isIE && check(/msie 8/);
					var isIE7 = isIE && check(/msie 7/);
					var isIE6 = isIE && check(/msie 6/);

					if( ( isIE6 || isIE7 )  ) window.location	=	'<?php echo et_get_page_link("unsupported"); ?>';
				}

				var check  = function (r) {
					var ua = navigator.userAgent.toLowerCase();
					return r.test(ua);
				}
				detectBrowser ();

			</script>

			<?php

		}

}

/**
 * Detect user's browser and version
 * @return array browser info
 */
function getBrowser()
{
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version= "";
    $ub = "MSIE";
    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }

    // Next get the name of the useragent yes separately and for good reason.
    if (preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
    {
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    }
    elseif (preg_match('/Firefox/i',$u_agent))
    {
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
    }
    elseif (preg_match('/Chrome/i',$u_agent))
    {
        $bname = 'Google Chrome';
        $ub = "Chrome";
    }
    elseif (preg_match('/Safari/i',$u_agent))
    {
        $bname = 'Apple Safari';
        $ub = "Safari";
    }
    elseif (preg_match('/Opera/i',$u_agent))
    {
        $bname = 'Opera';
        $ub = "Opera";
    }
    elseif (preg_match('/Netscape/i',$u_agent))
    {
        $bname = 'Netscape';
        $ub = "Netscape";
    }

    // Finally get the correct version number.
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }

    // See how many we have.
    $i = count($matches['browser']);
    if( isset($matches['version'] ) ) {
	    if ($i != 1) {
	        //we will have two since we are not using 'other' argument yet
	        //see if version is before or after the name
	        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
	            $version= isset($matches['version'][0]) ? $matches['version'][0] : '4.0';
	        }
	        else {
	            $version= isset($matches['version'][1]) ? $matches['version'][1] : '4.0';
	        }
	    }
	    else {
	        $version= isset($matches['version'][0]) ? $matches['version'][0] : '4.0';
	    }
	}else {
		$version= '4.0';
	}

    if($ub == "MSIE") {
    	preg_match('/(MSIE) [0-9.]*;/', $u_agent, $matches);
    	$version	=	isset($matches[0]) ? $matches[0] : '1.0';
    	$version	=	str_replace(array('MSIE', ';', ' '), '', $version);
    }

    // Check if we have a number.
    if ($version==null || $version=="") {$version="?";}

    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
}


if(!function_exists( 'ce_ad_order_filter' )) {
	function ce_ad_order_filter () {
		$order_arr      =   CE_Ads::custom_sort_args();
		$request_key    =   ET_FEATURED;
		if(isset( $_REQUEST['sortby']) ) {
		    $sortby  =   $_REQUEST['sortby'];

		    foreach ($order_arr as $key => $value) {
		        if( $value['key'] == $sortby )  {
		            $request_key = $key;
		            break;
		        }
		    }
		}
	?>
		<div class="btn-group dropdown-search button-search-page">
            <button class="btn dropdown-toggle featured-home" data-toggle="dropdown">
                <span class="select"><?php echo $order_arr[$request_key]['label']; ?></span>
                <i class="fa fa-arrow-down" style="margin-left: 20px;"></i>
            </button>
            <!-- <a href="#" title="Sort Descending"><i class="fa fa-arrow-circle-o-down icon-arrow-2"></i></a> -->
            <ul class="dropdown-menu">
                <?php
                unset($order_arr[$request_key]);
                $i = 0;
                foreach ($order_arr as $key => $value) {
                    $i++;
                ?>
                    <li><a href="<?php echo urldecode (add_query_arg(array('sortby' => $value['key']) )) ;  ?>"><?php echo $value['label'] ?></a></li>
                    <?php if($i < 3 ) echo '<li class="divider"></li>';
                } ?>

            </ul>
        </div>

	<?php
	}
}

function ce_get_ad_orderby () {

	$order_arr	    =	CE_Ads::custom_sort_args();
	$order_request  =	ET_FEATURED;

	if(isset($_REQUEST['sortby'])) {
		foreach ($order_arr as $key => $value) {
			if($value['key'] == $_REQUEST['sortby'] ) {
				$order_request = $key;
				break;
			}

		}
	}
	return $order_request;
}

/*
* version 1.7.3
* Template for carousel image upload .
* @ Danng
*/
function ce_get_template_carousel(){ ?>
	<script type="text/template" id="ce_carousel_template">
        <li class="image-item catelory-img-upload" id="{{ attach_id }}"><span class="img-gallery">
            <img title="" data-id="{{ attach_id }}" src="{{ thumbnail[0] }}" />
            <span title="<?php _e("Delete", ET_DOMAIN); ?>" class="delete-img delete"><i class="fa fa-times"></i></span>
            </span><input title="<?php _e("click to select a featured image", ET_DOMAIN); ?>" type="radio" name="featured_image" id="" value="{{attach_id }}" <# if(typeof is_feature !== "undefined" ) { #> checked="true" <# } #> >
        </li>
    </script>
    <?php
}


/**
 * Retrieve HTML dropdown (select) content for category list.
 *
 * @uses Walker_CategoryDropdown to create HTML dropdown content.
 * @since 2.1.0
 * @see Walker_CategoryDropdown::walk() for parameters and return description.
 */
function ce_walk_tax_dropdown_tree() {
	$args = func_get_args();
	// the user's options are the third parameter
	if ( empty($args[2]['walker']) || !is_a($args[2]['walker'], 'Walker') )
		$walker = new CE_Walker_CategoryDropdown;
	else
		$walker = $args[2]['walker'];

	return call_user_func_array(array( &$walker, 'walk' ), $args );
}

/**
 * Create HTML dropdown list of Categories.
 *
 * @package WordPress
 * @since 2.1.0
 * @uses Walker
 */
class CE_Walker_CategoryDropdown extends Walker {
	/**
	 * @see Walker::$tree_type
	 * @since 2.1.0
	 * @var string
	 */
	public $tree_type = 'category';

	/**
	 * @see Walker::$db_fields
	 * @since 2.1.0
	 * @todo Decouple this
	 * @var array
	 */
	public $db_fields = array ('parent' => 'parent', 'id' => 'term_id');

	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 * @since 2.1.0
	 *
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category Category data object.
	 * @param int    $depth    Depth of category. Used for padding.
	 * @param array  $args     Uses 'selected' and 'show_count' keys, if they exist. @see wp_dropdown_categories()
	 */
	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		// $pad = 'style="padding:'.$depth *10.'px;"';

		/** This filter is documented in wp-includes/category-template.php */
		$cat_name = apply_filters( 'list_cats', $category->name, $category );

		$output .= "\t<option class=\"level-$depth\" value=\"".$category->term_id."\"";
		if ( $category->term_id == $args['selected'] )
			$output .= ' selected="selected"';
		$output .= '>';
		$output .= $cat_name;
		if ( $args['show_count'] )
			$output .= '&nbsp;&nbsp;('. number_format_i18n( $category->count ) .')';
		$output .= "</option>\n";
	}
}
?>