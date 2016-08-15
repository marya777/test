<script type="text/template" id="ad-item-template">

<span class="list-title"><a href="{{ guid }}">{{ post_title }}</a> </span>

<?php echo "<# var color		=	'color-yellow';
var paid_color 	=	'color-yellow';
var title 		= 	'FREE';

if( et_paid === 0 ) {
	paid_color	=	'color-purple';
	title		=	'UNPAID';
}

if( et_paid == 1 ) {
	paid_color	=	'color-green1';
	title		=	'PAID';
} #> "; ?> 
<span class="list-date"> {{ date }}</span>
<span class="list-date"> </span>
<span class="list-event button-event">
	
	<# if(post_status == 'publish' || post_status == 'pending') {  
		if(post_status =='publish') { #>
			<span class="list-status sembold color-green1 "><?php _e('Active',ET_DOMAIN);?></span>
		<# }  else { #>
			<span class="list-status sembold color-yellow"><?php _e('pending',ET_DOMAIN);?></span>
		<# } #>
		<span title = "{{ title }}" class="icon  {{ paid_color }}" data-icon="%"></span>
		<a class = "edit" href="" title="Edit"><span class="icon" data-icon="p"></span></a>
		<a href = "#" class="archive" title="0 views"><span class="icon" data-icon="E"></span></a>
		<a href = "" class="archive"><span class="icon" data-icon="#"></span></a>			

	<# } else if(post_status == 'draft') { #>
		<span class="list-status sembold color-purple">{{ post_status }}</span>
		<span title="{{ title }}" class="icon color-purple" data-icon="%"></span>
		<a class = "edit" href="" title="Edit"><span class="icon" data-icon="p"></span></a>	
		<a href  =	"#" class="archive" title="0 views"><span class="icon" data-icon="E"></span></a>
		<a href	 =	"" class="delete"><span class="icon color-purple" data-icon="*"></span></a>
		
	<# } else if(post_status == 'archive') { #>
		<span class="list-status sembold color-purple"><?php _e('Archived',ET_DOMAIN);?></span>
		<span title="{{ title }}" class="icon {{ paid_color }}" data-icon="%"></span>
		<a href="" title="renew">
		<span class="icon" data-icon="1"></span></a>		
		<a href = "<?php echo et_get_page_link('post-ad'); ?>&id={{ ID}}" class="archive" title="0 views"><span class="icon" data-icon="E"></span></a>
		<a href = "" class="delete"><span class="icon color-purple" data-icon="*"></span></a>

	<# } else if(post_status == 'reject') { #>
		<span class="list-status sembold color-purple"><?php _e('Rejected',ET_DOMAIN);?></span>			
		<span title ="{{ title }}" class="icon {{ paid_color }}" data-icon="%"></span>
		<a class  ="edit" href="" title="Edit"><span class="icon" data-icon="p"></span></a>
		<a href = "#" class="archive" title="0 views"><span class="icon" data-icon="E"></span></a>
		<a href = "" class="archive"><span class="icon" data-icon="#"></span></a>			

	<# } #>

</span>		

</script>