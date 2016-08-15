<script type="text/template" id="ad-item-template">
	<?php echo "<# if( parseInt(". ET_FEATURED . ") == 1 ) { #>"; ?>
    	<span class="icon-featured"><?php _e("Featured", ET_DOMAIN) ?></span>   
  	<?php echo "<# } #> "; ?>
   	<p class="img"><a href="<?php echo "{{ guid }}"; ?>">
   		<?php echo " <# if( parseInt(". ET_FEATURED . ") == 1 ) { #> "; ?>
	    	<span class="shadown-img"><img src="<?php echo TEMPLATEURL; ?>/img/shadown-black.png"></span>
	    <?php echo " <# } #>  "; ?>
   		{{ the_post_thumbnail }}</a>
   	</p>
   	<?php 
   	/**
   	 * front-end control button 
 	*/
	if( current_user_can( 'manage_options' ) ) { ?>
	    <# if( post_status != 'pending' ) { #>
		    <ul class="button-event">
		        <li class="tooltips update edit"><a href="#" data-toggle="tooltip" title="<?php _e("Edit", ET_DOMAIN); ?>" data-original-title="<?php _e("Edit", ET_DOMAIN)?>" ><span  class="icon" data-icon="p"></span></a></li>
		        <li class="tooltips flag toggle-feature">
		        	<a href="#" data-toggle="tooltip" <?php echo "<# if( parseInt(". ET_FEATURED . ") == 1 ) { #>"; ?> title="<?php _e("Remove featured", ET_DOMAIN); ?>" <?php echo "<# } else { #>"; ?>  title="<?php _e("Set as featured", ET_DOMAIN); ?>" <?php echo "<# } #>"; ?> data-original-title="<?php _e("Set as featured", ET_DOMAIN) ?>" >
		        	<span class="icon <?php echo "<# if( parseInt(". ET_FEATURED . ") == 1 ) { #>"; ?> color-yellow <?php echo "<# } #>"; ?>" data-icon="^"></span></a>
		        </li>
		        <li class="tooltips remove archive"><a href="#" data-toggle="tooltip" title="<?php _e("Delete", ET_DOMAIN); ?>" data-original-title="<?php _e("Delete", ET_DOMAIN)?>" ><span class="icon" data-icon="#"></span></a></li>
		    </ul>
		<# }else { #>
			<ul class="button-event">
				<li class="check approve"><a href="#"><span class="icon" data-icon="3"></span></a></li>
				<li class="delete reject"><a href="#"><span class="icon-delete icon" data-icon="*"></span></a></li>
				<li class="update edit"><a href="#"><span class="icon" data-icon="p"></span></a></li>
			</ul>
		<# } #> 
	<?php } ?>  
	<div class="intro-product">
		<h5 class="title"><a href="{{ guid }}">{{ post_title }}</a></h5>
        <a href="{{ guid }}">
            <p>
                <span class="name"> <?php echo "<# if( typeof location[0] !== 'undefined' ) { #> {{ location[0].name }} <# } #>" ; ?></span>
                <span class="price">{{ price }}</span>
            </p>
        </a>
        <div class="description" >
            {{ the_excerpt }}
        </div>
    </div>             
         
<!--/span-->
</script>