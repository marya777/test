<script type="text/template" id="post_item_template">
	<div class="post-wrapper">
	    
	    <div class="detail-post">  
	    	<?php echo "<# if(typeof category  != 'undefined') { #>
	    		<a title='{{ category['title_link'] }}' href='{{ category['cat_link'] }}' >{{category['name'] }}</a>
	    	<#} #>"; ?>
	        <span><i class="fa fa-comments"></i>{{ comment_count }}</span>
	    </div> 
	    <div class="clearfix"></div>
	    <div id="post-{{ ID }}" >
	        <h1 class="entry-title"><a title="" rel="bookmark" href="{{ guid }}">{{ post_title }}</a></h1>
	        <div class="entry-content">
				{{ post_excerpt }}
	        </div>
			<a  class="read-post" href="{{ track_url }} "><?php _e( 'Readmore',ET_DOMAIN ); ?>&nbsp;&nbsp;<i class="fa fa-arrow-right"></i></a>
	    </div>
	</div>

</script>