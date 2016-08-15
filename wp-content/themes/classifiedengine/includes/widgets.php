<?php
/**
 * new WordPress Widget format
 * Wordpress 2.8 and above
 * @see http://codex.wordpress.org/Widgets_API#Developing_Widgets
 */
class CE_Slider_Widget extends WP_Widget {

    /**
     * Constructor
     *
     * @return void
     **/
    function CE_Slider_Widget() {
        $widget_ops = array( 'classname' => 'ce_slider', 'description' => __("CE Slider", ET_DOMAIN) );
        $this->WP_Widget( 'ce_slider', __("CE Slider", ET_DOMAIN), $widget_ops );
    }

    function get_query_args ($instance) {
    	extract($instance);
    	if($instance['ids'] != '') {
    		return array ('post_type' => CE_AD_POSTTYPE, 'post__in' => explode(',', $instance['ids']));
        }
        if($instance['query_options'] == 1) {
        	return array ('order' => 'DESC', 'post_type' => CE_AD_POSTTYPE, 'showposts' => $number , 'post_status' => 'publish');
        }

        if($instance['query_options'] == 2) {
        	return array (
        		'post_type' => CE_AD_POSTTYPE,
        		'showposts' => $number,
        		'meta_key'  => ET_FEATURED,
        		'meta_value' => 1,
        		'orderby'	=> 'date meta_value',
        		'post_status' => 'publish'
        	);
        }
    }

    /**
     * Outputs the HTML for this widget.
     *
     * @param array  An array of standard parameters for widgets in this theme
     * @param array  An array of settings for this widget instance
     * @return void Echoes it's output
     **/
    function widget( $args, $instance ) {
        extract( $args, EXTR_SKIP );
        echo $before_widget;

        $instance 	= wp_parse_args( (array) $instance, array( 
												        	'number' => 10, 
												        	'query_options' => '1', 
												        	'ids' => '' , 
												        	'full_width' => '',
												        	'fx'		=> 'cover',
												        	'direction'	=> 'up',
												        	'duration'	=> 1000,
												        	'caption'	=> '',
												        	'height'	=> 390,
												        	'width'		=> 1051,
												        	'paginate'	=> ''
												         ) );

        $query	=	$this->get_query_args ($instance);
        $s		=	CE_Ads::query($query);

        extract($instance);

        $height	.=	'px';
        $width	.=	'px';

      ?>
        <div class="slideshow" style="height : <?php echo $height ?>; ">		
    		<div class="main-center">
				<div class="html_carousel " style="display: none;">
					<div class="" id="<?php echo $this->get_field_id('title'); ?>">
						<?php 
						while ( $s->have_posts()) : $s->the_post() ;
							global $post;

							$img			=	get_post_meta( $post->ID, 'ce_carousel_image', true );
							$button			=	get_post_meta( $post->ID, 'ce_button_text', true );
							$caption_text	=	get_post_meta( $post->ID, 'ce_caption_text', true );
							$link			=	get_post_meta( $post->ID, 'ce_carousel_link', true );

							$link 		=	($link != '') ? $link : get_permalink( $post->ID );

							if(!$img) {
								if(!has_post_thumbnail()) continue;
								$img	=	wp_get_attachment_image_src( get_post_thumbnail_id(), 'ce_slide' );
								$img	=	$img[0] ;
							}
								
						 ?>
						<div class="slide" >
							<a href="<?php echo $link; ?>" title="<?php the_title(); ?>" >
								<img style="<?php echo 'width:'.$width.';height:'.$height; ?>" src="<?php echo $img ?>" title="<?php the_title(); ?>" alt="<?php the_title(); ?>"  />
							</a>
							<?php if($caption == 'on') { ?>
							<div class="caption">
								<h4><!-- <a href="<?php echo $link; ?>" ><?php the_title(); ?> </a> -->
								<?php if( $caption_text != '' ) echo $caption_text; else the_excerpt(); ?>
								</h4>
								<?php //if($button != '') { ?>
								<a href="<?php echo $link; ?>" class="btn btn-primary btn-large"><?php if($button != '') echo $button; else _e("Views Details", ET_DOMAIN); ?></a>
								<?php //} ?>
							</div>
							<?php } ?>
						</div>
						<?php endwhile; ?>

					</div>
					<div class="clearfix"></div>
					<?php if($paginate == 'on') { ?>
					<div class="bx-controls bx-has-pager bx-has-controls-direction">
						<a href="#" class="<?php echo $this->get_field_id('paginate'); ?>-bx-prev bx-prev">
							<i class="fa fa-arrow-left"></i>
						</a>

						<div id="<?php echo $this->get_field_id('paginate'); ?>" class="paginate bx-default-pager"></div>

						<a href="#" class="<?php echo $this->get_field_id('paginate'); ?>-bx-next bx-next">
							<i class="fa fa-arrow-right"></i>
						</a>
					</div>
					<?php } ?>
					
				</div>
			</div>		
		</div>
		<?php 
			

		?>
		<script type="text/javascript" >
    	(function ($){
    		$(window).on('load', function() {

    			$('.html_carousel').show();
    			
 
	    		$("#<?php echo $this->get_field_id('title'); ?>").carouFredSel({
	    			// direction : 'right',
					scroll		: {
						fx				: "<?php echo $fx; ?>",
						duration        : 1000,
						timeoutDuration : <?php echo $duration; ?>,
						pauseOnHover 	: true,

					},
					direction : "<?php echo $direction; ?>",
					// auto : false,
					// auto : false,
					items		: {
						width		: "100%",
						visible		: 1
					},
					pagination  : {
						container : $("#<?php echo $this->get_field_id('paginate'); ?>")
					},
					prev : { button :$(".<?php echo $this->get_field_id('paginate'); ?>-bx-prev") , key : 37  },
					next : { button :$(".<?php echo $this->get_field_id('paginate'); ?>-bx-next") , key : 39 },
					align : 'right',
					// width : 1051
				});
				
	    	});
	    	
    	})(jQuery);

    	</script>
    <?php 
    echo $after_widget;
    }

    /**
     * Deals with the settings when they are saved by the admin. Here is
     * where any validation should be dealt with.
     *
     * @param array  An array of new settings as submitted by the admin
     * @param array  An array of the previous settings
     * @return array The validated and (if necessary) amended settings
     **/
    function update( $new_instance, $old_instance ) {

        // update logic goes here
        $updated_instance = $new_instance;
        return $updated_instance;
    }

    /**
     * Displays the form for this widget on the Widgets page of the WP Admin area.
     *
     * @param array  An array of the current settings for this widget
     * @return void Echoes it's output
     **/
    function form( $instance ) {
    	
        $instance 	= wp_parse_args( (array) $instance, array( 
												        	'number' => 10, 
												        	'query_options' => '1', 
												        	'ids' => '' , 
												        	'full_width' => '',
												        	'fx'		=> 'cover',
												        	'direction'	=> 'up',
												        	'duration'	=> 1000,
												        	'caption'	=> '',
												        	'height'	=> 390,
												        	'width'		=> 1051,
												        	'paginate'	=> ''
												         ) );

		$number  	= $instance['number'];
		$ids 	 	= $instance['ids'];
		$options 	= $instance['query_options'];

		extract($instance);

	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Enter an ID (separate multiple entry with a comma)', ET_DOMAIN ) ?> </label>
			<input class="widefat" id="<?php echo $this->get_field_id('ids'); ?>" name="<?php echo $this->get_field_name('ids'); ?>" 
					type="text" value="<?php echo $ids; ?>" />
		</p> <p><strong>or </strong></p>
		<p>	<label> <?php _e('Select slider from: ',ET_DOMAIN);?></label>
			<select name="<?php echo $this->get_field_name('query_options'); ?>">
				<option> <?php _e('Select option',ET_DOMAIN);?></option>
				<option value = "1"  <?php if($options == 1) echo 'selected';?> > <?php _e('Latest Post',ET_DOMAIN);?></option>
				<option value = "2" <?php if($options == 2) echo 'selected';?> > <?php _e('Featured Post',ET_DOMAIN);?></option>				
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e("Number of slider", ET_DOMAIN); ?> </label>
			<input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" 
					type="text" value="<?php echo esc_attr($number ); ?>" />
		</p>	

		<p><?php _e("Carousel Options", ET_DOMAIN); ?></p>
		<p><input  <?php checked( 'on', $instance['caption'], true ); ?> type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('caption'); ?>" name="<?php echo $this->get_field_name('caption'); ?>">
		<label for="<?php echo $this->get_field_id('caption'); ?>"><?php _e("Display caption", ET_DOMAIN); ?></label><br />

		<p><input  <?php checked( 'on', $instance['paginate'], true ); ?> type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('paginate'); ?>" name="<?php echo $this->get_field_name('paginate'); ?>">
		<label for="<?php echo $this->get_field_id('paginate'); ?>"><?php _e("Display Paginator", ET_DOMAIN); ?></label><br />

		
		<p>
			<label for="<?php echo $this->get_field_id('fx'); ?>"><?php _e("Transition effect:", ET_DOMAIN); ?> </label>
			<select id="<?php echo $this->get_field_id('fx'); ?>" name="<?php echo $this->get_field_name('fx') ?>">
				<option <?php selected( 'cover', $fx, true ); ?>  value="cover">cover</option>
				<option <?php selected( 'cover-fade', $fx, true ); ?>  value="cover-fade">cover-fade</option>
				<option <?php selected( 'fade', $fx, true ); ?>	value="fade">fade</option>	
				<option <?php selected( 'uncover', $fx, true ); ?>	value="uncover">uncover</option>
				<option <?php selected( 'uncover-fade', $fx, true ); ?>	value="uncover-fade">uncover-fade</option>
				<option <?php selected( 'directscroll', $fx, true ); ?>	value="directscroll">directscroll</option>
				<option <?php selected( 'crossfade', $fx, true ); ?>	value="crossfade">crossfade</option>				
			</select>
		</p>
		<!--  The direction to scroll the carousel, determines whether the carousel scrolls horizontal or vertical and -when the carousel scrolls automatically- in what direction.
			Possible values: "right", "left", "up" or "down". 
		 -->
		<p>
			<label for="<?php echo $this->get_field_id('direction'); ?>"><?php _e("Direction:", ET_DOMAIN); ?> </label>
			<select id="<?php echo $this->get_field_id('direction'); ?>" name="<?php echo $this->get_field_name('direction') ?>">
				<option <?php selected( 'left', $direction, true ); ?> value="left">left</option>
				<option <?php selected( 'right', $direction, true ); ?> value="right">right</option>
				<option <?php selected( 'up', $direction, true ); ?> value="up">up</option>	
				<option <?php selected( 'down', $direction, true ); ?> value="down">down</option>
			</select>
		</p>
		<!-- Determines the duration of the transition in milliseconds. -->
		<p>
			<label for="<?php echo $this->get_field_id('duration'); ?>"><?php _e("Duration (milliseconds)", ET_DOMAIN); ?> </label>
			<input class="widefat" id="<?php echo $this->get_field_id('duration'); ?>" name="<?php echo $this->get_field_name('duration'); ?>" 
					type="text" value="<?php echo esc_attr($duration ); ?>" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('width'); ?>"><?php _e("Width (px)", ET_DOMAIN); ?> </label>
			<input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" 
					type="text" value="<?php echo esc_attr($width ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('height'); ?>"><?php _e("Height (px)", ET_DOMAIN); ?> </label>
			<input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" 
					type="text" value="<?php echo esc_attr($height ); ?>" />
		</p>



	<?php
    }
}

// ce cetegoried sidebar widget
/**
 * Categories widget class
 *
 * @since 2.8.0
 */
class CE_Categories_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'ce_widget_categories', 'description' => __( "A list or dropdown of categories",ET_DOMAIN ) );
		parent::__construct('ce_categories', __('CE Categories',ET_DOMAIN), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title	=	isset($instance['title']) ? $instance['title'] : '';
		$c = ! empty( $instance['count'] ) ? '1' : '0';
		$h = 1;//! empty( $instance['hierarchical'] ) ? '1' : '0';
		

		echo $before_widget;
		if ( $title )
			echo $before_title . apply_filters( 'widget_title' , $title ) . $after_title;

		$cat_args = array( 'orderby' => 'name',  'hierarchical' => $h,'show_count'	=>	$c  );

		$cat_args['hide_empty']	=	isset( $instance['hide_empty'] ) ? $instance['hide_empty'] : false;

		$c_str  =   ET_AdCatergory::slug();
		
		$cat_args['title_li'] 	= __('Categories',ET_DOMAIN);
		$cat_args['tax_slug'] 	= $c_str;
		//$cat_args['hide_empty'] = true;
		ce_list_categories( $cat_args );		
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = !empty($new_instance['count']) ? 1 : 0;
		$instance['hide_empty'] = !empty($new_instance['hide_empty']) ? 1 : 0;
		$instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;
		

		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = esc_attr( $instance['title'] );
		$count = isset($instance['count']) ? (bool) $instance['count'] :false;
		$hide_empty = isset($instance['hide_empty']) ? (bool) $instance['hide_empty'] :false;
		$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
		
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:',ET_DOMAIN ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		
		

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $count ); ?> />
		<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show post counts',ET_DOMAIN ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hide_empty'); ?>" name="<?php echo $this->get_field_name('hide_empty'); ?>"<?php checked( $hide_empty ); ?> />
		<label for="<?php echo $this->get_field_id('hide_empty'); ?>"><?php _e( 'Hide empty',ET_DOMAIN ); ?></label><br />

		<!-- <input type="checkbox" class="checkbox" id="<?php //echo $this->get_field_id('hierarchical'); ?>" name="<?php // echo $this->get_field_name('hierarchical'); ?>"<?php checked( $hierarchical ); ?> />
		<label for="<?php //echo $this->get_field_id('hierarchical'); ?>"><?php //_e( 'Show hierarchy' ); ?></label></p> -->
<?php
	}

}

class CE_Locations_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'ce_widget_locations', 'description' => __( "A list  of locations",ET_DOMAIN ) );
		parent::__construct('ce_locations', __('CE Locations',ET_DOMAIN), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );
		$title	=	isset($instance['title']) ? $instance['title'] : '';
		//$c = ! empty( $instance['count'] ) ? '1' : '0';
		$h = 1;//! empty( $instance['hierarchical'] ) ? '1' : '0';
		

		echo $before_widget;
		if ( $title )
			echo $before_title . apply_filters( 'widget_title' , $title ) . $after_title;

		$hide_empty = isset($instance['hide_empty']) ? $instance['hide_empty'] : false;
		$show_option_all_text = '';
		if( ( isset($instance['show_option_all']) ) && ($instance['show_option_all'] == 1)){
			$show_option_all_text = __("All Locations", ET_DOMAIN);
		}
		$local_args = array('orderby' => 'name',  'hierarchical' => $h,'show_count'=> 0, 'hide_empty' => $hide_empty, 'show_option_all' => $show_option_all_text);
		$l_str  =   ET_AdLocation::slug();
		
		$cat_args['title_li'] = __('Locations',ET_DOMAIN);
		$cat_args['tax_slug'] = $l_str;
		
		ce_list_locations( $local_args );

		//ce_list_categories( $local_args );

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = !empty($new_instance['count']) ? 1 : 0;
		$instance['hide_empty'] = !empty($new_instance['hide_empty']) ? 1 : 0;
		$instance['show_option_all'] = !empty($new_instance['show_option_all']) ? 1 : 0;
		$instance['hierarchical'] = 1;//!empty($new_instance['hierarchical']) ? 1 : 0;
		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = esc_attr( $instance['title'] );
		$count = isset($instance['count']) ? (bool) $instance['count'] :false;
		$hide_empty = isset($instance['hide_empty']) ? (bool) $instance['hide_empty'] :false;
		$show_option_all = isset($instance['show_option_all']) ? (bool) $instance['show_option_all'] :false;
		$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
		
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:',ET_DOMAIN ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<!-- <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php //echo $this->get_field_name('hierarchical'); ?>"<?php checked( $hierarchical ); ?> />
		<label for="<?php //echo $this->get_field_id('hierarchical',ET_DOMAIN); ?>"><?php //_e( 'Show hierarchy',ET_DOMAIN ); ?></label></p> -->

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_option_all'); ?>" name="<?php echo $this->get_field_name('show_option_all'); ?>"<?php checked( $show_option_all ); ?> />
		<label for="<?php echo $this->get_field_id('show_option_all'); ?>"><?php _e( "Show All Locations option", ET_DOMAIN); ?></label></p>

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hide_empty'); ?>" name="<?php echo $this->get_field_name('hide_empty'); ?>"<?php checked( $hide_empty ); ?> />
		<label for="<?php echo $this->get_field_id('hide_empty'); ?>"><?php _e( 'Hide empty',ET_DOMAIN ); ?></label><br />
<?php
	}

}

/**
 * new WordPress Widget format
 * Wordpress 2.8 and above
 * @see http://codex.wordpress.org/Widgets_API#Developing_Widgets
 */
class CE_Social_Widget extends WP_Widget {

    /**
     * Constructor
     *
     * @return void
     **/
    function CE_Social_Widget() {
        $widget_ops = array( 'classname' => 'ce-social', 'description' => __("CE Social widget link", ET_DOMAIN) );
        $this->WP_Widget( 'ce-social', __("CE Social", ET_DOMAIN), $widget_ops );
    }

    /**
     * Outputs the HTML for this widget.
     *
     * @param array  An array of standard parameters for widgets in this theme
     * @param array  An array of settings for this widget instance
     * @return void Echoes it's output
     **/
    function widget( $args, $instance ) {
        extract( $args, EXTR_SKIP );
        echo $before_widget;
        echo $before_title;
        echo apply_filters( 'widget_title' , $instance['title'] ) ; // Can set this with a widget option, or omit altogether
        echo $after_title;

        extract($instance);
       
       	?>
       
            <ul>
            	<?php if($fb_link != '') { ?>
              	<li><a href="<?php echo $fb_link; ?>"><?php _e("Facebook", ET_DOMAIN); ?></a></li>
              	<?php } ?>

              	<?php if($tw_link != '') { ?>
              	<li><a href="<?php echo $tw_link ?>"><?php _e("Twitter", ET_DOMAIN); ?></a></li>
              	<?php } ?>

              	<?php if($pin_link != '') { ?>
              	<li><a href="<?php echo $pin_link ?>"><?php _e("Pinterest", ET_DOMAIN); ?></a></li>
              	<?php } ?>

              	<?php if($google_link != '') { ?>
              	<li><a href="<?php echo $google_link; ?>"><?php _e("Google+", ET_DOMAIN); ?></a></li>
              	<?php } ?>
            </ul>
        
       <?php
	    //
	    // Widget display logic goes here
	    //

    	echo $after_widget;
    }

    /**
     * Deals with the settings when they are saved by the admin. Here is
     * where any validation should be dealt with.
     *
     * @param array  An array of new settings as submitted by the admin
     * @param array  An array of the previous settings
     * @return array The validated and (if necessary) amended settings
     **/
    function update( $new_instance, $old_instance ) {

        // update logic goes here
        $updated_instance = $new_instance;
        return $updated_instance;
    }

    /**
     * Displays the form for this widget on the Widgets page of the WP Admin area.
     *
     * @param array  An array of the current settings for this widget
     * @return void Echoes it's output
     **/
    function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, 
        				array(  'title' => __("Connect", ET_DOMAIN) , 
        						'fb' => '' , 
        						'fb_link' => '' , 
        						'tw' => '' , 
        						'tw_link' => '' , 
        						'google' => '' , 
        						'google_link' => '',
        						'pin'	=> '',
        						'pin_link' => ''
        					)  
        				);
        extract($instance);
    ?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:',ET_DOMAIN ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<?php _e("Facebook", ET_DOMAIN); ?>
		<p>
			<input placeholder="<?php _e("Facebook Link", ET_DOMAIN); ?>" class="widefat" id="" name="<?php echo $this->get_field_name('fb_link'); ?>" type="text" value="<?php echo $fb_link; ?>" />
		</p>
		
		<?php _e("Twitter", ET_DOMAIN); ?>
		<p>
			<input placeholder="<?php _e("Twitter Link", ET_DOMAIN); ?>" class="widefat" id="" name="<?php echo $this->get_field_name('tw_link'); ?>" type="text" value="<?php echo $tw_link; ?>" />
		</p>

		<?php _e("Google+", ET_DOMAIN); ?>
		<p>
			<input placeholder="<?php _e("Google+ Link", ET_DOMAIN); ?>" class="widefat" id="" name="<?php echo $this->get_field_name('google_link'); ?>" type="text" value="<?php echo $google_link; ?>" />
		</p>

		<?php _e("Pinterest", ET_DOMAIN); ?>
		<p>
			<input placeholder="<?php _e("Pinterest Link", ET_DOMAIN); ?>" class="widefat" id="" name="<?php echo $this->get_field_name('pin_link'); ?>" type="text" value="<?php echo $pin_link; ?>" />
		</p>

    <?php 
    }
}

// add_action( 'widgets_init', create_function( '', "register_widget( 'CE_Social_Widget' );" ) );

/**
 * new WordPress Widget format
 * Wordpress 2.8 and above
 * @see http://codex.wordpress.org/Widgets_API#Developing_Widgets
 */
class CE_Price_Filter_Widget extends WP_Widget {

    /**
     * Constructor
     *
     * @return void
     **/
    function CE_Price_Filter_Widget() {
        $widget_ops = array( 'classname' => 'ce-price-filter', 'description' => 'Filter Ad by Price Widget' );
        $this->WP_Widget( 'ce-price-filter', __("CE Price Filter", ET_DOMAIN), $widget_ops );
    }

    /**
     * Outputs the HTML for this widget.
     *
     * @param array  An array of standard parameters for widgets in this theme
     * @param array  An array of settings for this widget instance
     * @return void Echoes it's output
     **/
    function widget( $args, $instance ) {
        extract( $args, EXTR_SKIP );
        extract( $instance, EXTR_SKIP );
        echo $before_widget;
        // echo $before_title;
        // echo 'Title'; // Can set this with a widget option, or omit altogether
        // echo $after_title;
       ?>
			<div class="control-group search-price clearfix">
                <label class="control-label" for="price"><?php echo $title; ?></label>
                <div class="controls">
                    <input type="text" id="ce-prev-price" class="number" placeholder="$0.000" value="" />
                    <span class="arrow-next"></span>
                    <input type="text" id="ce-next-price" class="number" placeholder="$4,830.95" value="" />
                </div>
            </div> 
       <?php 

       add_action ('wp_ajax_nopriv_update_ce_filter_price' , array($this, 'get_price_filter'));
       add_action ('wp_ajax_update_ce_filter_price' , array($this, 'get_price_filter'));
	    //
	    // Widget display logic goes here
	    //

	    echo $after_widget;
    }

    function get_price_filter () {
    	wp_send_json( array('success' => true) );
    }

    /**
     * Deals with the settings when they are saved by the admin. Here is
     * where any validation should be dealt with.
     *
     * @param array  An array of new settings as submitted by the admin
     * @param array  An array of the previous settings
     * @return array The validated and (if necessary) amended settings
     **/
    function update( $new_instance, $old_instance ) {

        // update logic goes here
        $updated_instance = $new_instance;
        return $updated_instance;
    }

    /**
     * Displays the form for this widget on the Widgets page of the WP Admin area.
     *
     * @param array  An array of the current settings for this widget
     * @return void Echoes it's output
     **/
    function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array( 'title' => __("Price", ET_DOMAIN) ) );
        extract($instance);
        ?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:',ET_DOMAIN ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

        <?php 
        // display field names here using:
        // $this->get_field_id( 'option_name' ) - the CSS ID
        // $this->get_field_name( 'option_name' ) - the HTML name
        // $instance['option_name'] - the option value
    }
}


?>