<?php
get_header();
?>

<div class="title-page">
    <div class="main-center container">
        <?php
        the_archive_title( '<h1 class="text archive-title">', '</h1>' );
        the_archive_description( '<div class="taxonomy-description">', '</div>' );
        ?>
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
<?php get_footer(); ?>