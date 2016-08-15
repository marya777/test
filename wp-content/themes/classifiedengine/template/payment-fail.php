<?php
	/**
	 * this template for payment fail, you can overide this template by child theme
	*/
	global $ad;
	if($ad)
		$permalink	=	et_get_page_link('post-ad', array( 'id' => $ad->ID ));
	else 
		$permalink	=	et_get_page_link('post-ad');
?>
<div class="redirect-content" >
	<div class="main-center">

		<div class="title"><?php _e("Payment fail, friend",ET_DOMAIN);?></div>
		<div class="content">
			<?php _e("You are now redirected to post ad page ... ",ET_DOMAIN);?> <br/>
			<?php printf(__('Time left: %s', ET_DOMAIN ), '<span class="count_down">10</span>')  ?> 
		</div>
		<?php echo '<a href="'.$permalink.'" >'.__("Post Ad", ET_DOMAIN).'</a>'; ?>
	</div>
</div>