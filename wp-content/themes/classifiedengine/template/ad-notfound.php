<?php 
/**
 * template not found
*/

if(!is_author( )) {  ?>
<div class="col-md-4 no-result">  
    <p class="intro-product">
    	<?php _e("No results found for your query.", ET_DOMAIN); ?>
    </p>   
    
    <span class="button-post-ad">
		<a title="<?php _e("Post an Ad", ET_DOMAIN); ?>" href="<?php echo et_get_page_link( array('page_type' => 'post-ad', 'post_title' => __("Post an Ad", ET_DOMAIN )) ); ?>">
			<span data-icon="W" class="icon"></span>
			<?php _e("Post an Ad", ET_DOMAIN); ?>							
		</a>
	</span>        
	
</div><!--/span-->
<?php } else {
?>	
<div class="col-md-4 no-result">  
    <p class="intro-product">
    	<?php _e("No ads posted by this seller.", ET_DOMAIN); ?>
    </p>         
	
</div><!--/span-->

<?php } ?>