<?php
/**
 *	Template Name: Term of use
 */
get_header();
?>
<?php if (have_posts()) {
	the_post();
?>
	<div class="title-page">
	    <div class="main-center container">
		<span class="text"><?php the_title();?></span>
	    </div>
	</div>
	<div class="container main-center main-content">
		<div class="row">
		    <div class="col-md-9 contenttext paddingTop34">
		   		<?php the_content(); ?>
		    </div>
		</div><!--/.main center-->
	</div><!--/.fluid-container-->
<?php }

get_footer();