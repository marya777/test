
<script type="text/template" id="ce-single-related">	
	<div class="item-product ad-carousel related-classified">
	    <?php echo " <# if( parseInt(".ET_FEATURED .") ) { #>"; ?>
	        <span class="icon-featured"><?php __("Featured", ET_DOMAIN) ?></span>   
	    <?php echo "<# } #>"; ?>
	    <p class="img">
	  	
	        <a href="{{ permalink }}">
	        	<span class="shadown-img"><img src="<?php echo TEMPLATEURL; ?>/img/shadown-black.png"></span>
	            {{ the_post_thumbnail }}
	        </a>
	    </p>
	    <!-- ad details -->
	    <div class="intro-product">
	    	<h5 class="title">{{ post_title }}</h5>
        	<a href="{{ permalink }}" >              	
              	<span class="name"> <?php echo "<# if( typeof location[0] !== 'undefined' ) { #> {{ location[0].name }} <# } #>" ; ?></span><br/>
              	<span class="price">{{ price }}</span>
          	</a>
        </div>        
	</div>
</script>