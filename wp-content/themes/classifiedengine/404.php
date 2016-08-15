<?php
get_header();
get_template_part( 'template-search' );
?>
<div class="container main-center page-404">
	<div class="text-page-left">
		<p><span class="txt1"><?php _e("Oops...", ET_DOMAIN); ?></span><br />
			<span class="txt2"><?php _e("This page cannot be found.", ET_DOMAIN); ?></span><br />
			<span class="txt2"><?php printf(__("Go back to %s", ET_DOMAIN), '<a href="'.home_url().'">'.__("Homepage", ET_DOMAIN).'</a>') ?> </span>
		</p>
	</div>
	<div class="text-page-right">
		<span class="bg-404">
        	<span class="bg-404-color"></span>
        </span>
	</div>
</div>
<?php

get_footer();