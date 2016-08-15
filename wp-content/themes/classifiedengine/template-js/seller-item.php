<script type="text/template" id="seller_item_template">
	<div class="col-md-12 item-product">
		<div class="col-md-5 intro-profle">
			<a href="{{ ads_link }}" title="{{ ads_link_title }}">
				{{ avatar }}
				<span class="sembold" <# if(!user_location) { #> style="margin-top:8px;" <# } #> ><span class="colorgray">{{ display_name }}</span><br>
				{{ user_location }}</span> 
				<span class="date-join">{{ joined_date }} </span>
			</a>
		</div>
		<div class="col-md-7 content-img-right">
			{{ ads_html }}
		</div>
	</div>
</script>