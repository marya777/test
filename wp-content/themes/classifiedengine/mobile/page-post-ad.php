<?php
ob_start();
$response	=	array();
if( isset($_REQUEST['post_title']) )  {
	$response	=	ce_mobile_process_post_ad () ;
	if( $response['success'] ) {
		wp_redirect ( $response['url'] );
		exit;
	}
}

et_get_mobile_header();

?>
<div data-role="content" class="post-classified" >
	<?php if( isset($_REQUEST['ad_id']) ) {
		if(!et_get_payment_disable())
			get_template_part( 'mobile/template' , 'payment' );
		else {
			wp_redirect( get_permalink( $_REQUEST['ad_id'] ));
			exit;
		}
	} else { ?>

	    <h1 class="page-title" >
	    	<?php _e("Post an Ad", ET_DOMAIN); ?>
	   		<span class="step-number"><?php _e("Step Ad Content", ET_DOMAIN); ?></span>
	   	</h1>
		<?php
			if( isset($response['success']) && !$response['success'] ) { ?>
				<span class="post-ad-error"> <?php echo $response['msg'] ?> </span>	
				<?php
			}

			if( !is_user_logged_in() )
				get_template_part( 'mobile/template' , 'register' );

			get_template_part( 'mobile/template' , 'post-ad' );

	}

	$term_of_use	=	et_get_page_link('terms-of-use' , array () , false);
	if($term_of_use) { ?>

		<div class="term-of-use">
			<?php printf( __("By posting your ad, you agree to our <a target='_blank' href='%s' > Terms of use </a> ", ET_DOMAIN) , $term_of_use ) ; ?>
			(*)
		</div>
	<?php } ?>
</div>

<?php

et_get_mobile_footer();