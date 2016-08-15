<script type="text/template" id="favorite_item_template">
	<div class="item-product col-md-4">     
	    <p class="img">	  	
	        <a href="{{permalink}}">
	            {{ the_post_thumbnail }}
			</a>
	    </p> 
	    <div class="intro-product">
	        <a title="{{ post_title }}" href="{{ permalink }}">
	            <span class="title">{{ post_title }}</span>
	            <p>
                    <span class="name">{{ user_location }}</span>
                    <span class="price">$44.00</span>
	            </p>
	        </a>
	        <div class="description">
	            <p>{{ post_excerpt }}</p>
	        </div>
	    </div>             
	</div>
</script>