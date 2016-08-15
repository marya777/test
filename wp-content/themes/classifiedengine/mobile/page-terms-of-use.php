<?php et_get_mobile_header(); 
if(have_posts()) { the_post ();	
?>

<div data-role="content" class="resume-contentpage">		
	<h1 class="title-resume"><?php the_title(); ?></h1>
	<div class="infor-resume inset-shadow clearfix" style="border-bottom:none !important; min-height:5px;padding: 10px;"></div>
	
    <div class="blog-content">
    	
        <div class="blog-text">
        	<?php the_content(); ?>
        </div>
    </div>
	
</div><!-- /content -->
<?php 
}
et_get_mobile_footer(); ?>