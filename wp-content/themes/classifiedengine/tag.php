<?php
get_header();
?>

<div class="title-page">
    <div class="main-center container">
        <h1 class="archive-title text"><?php printf(__("Tag Archives: %s", ET_DOMAIN), single_tag_title( '', false ));  ?></h1>
    </div>
</div>

<div class="main-center container main-content">
	<div class="row">
        <div class="col-md-9 product paddingTop45 area-f-right">
            <div class="categories-list" >
                <?php 
                if(have_posts()) {
                	while (have_posts()) {
                	   the_post();
                	   get_template_part(  'template/content' , 'blog' );
                	}
                }
                ?>
            </div>
            <div class="pagination-page" >
                <?php echo ce_pagination(); ?>
            </div>
		</div>

        <div class="col-md-3 area-f-left">
            <?php
                get_sidebar('blog');
            ?>
        </div><!--/span-->
	</div>
</div>
<?php
get_footer();