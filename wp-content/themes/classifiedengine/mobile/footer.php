
</div><!-- /page -->
<?php
wp_footer();
$use_minify = get_theme_mod( 'ce_minify', 0 );

if( $use_minify ) {
	$link	=	et_get_page_link ('min');
	$jslink	=	add_query_arg(array('g' => 'mobile-js', 'ver' => CE_VERSION ), $link);

    $css_link   =   add_query_arg(array('g' => 'mobile-css', 'ver' => CE_VERSION ), $link);

	?>
	<script type="text/javascript">

		 // Add a script element as a child of the body
		function downloadJSAtOnload(src) {
		 	var element = document.createElement("script");
			 	element.src = src;
		 		document.body.appendChild(element);
		}

		 // Check for browser support of event handling capability
		if (window.addEventListener)
			window.addEventListener("load", downloadJSAtOnload("<?php echo $jslink ?>"), false);
		else if (window.attachEvent)
			window.attachEvent("onload", downloadJSAtOnload("<?php echo $jslink ?>"));
		else window.onload = downloadJSAtOnload("<?php echo $jslink ?>");

		function downloadCssAtOnload(src) {
		 	var element = document.createElement("link");
			 	element.href = src;
			 	element.rel = 'stylesheet';
		 		document.head.appendChild(element);
		}

		var style	=	[
			// "<?php echo TEMPLATEURL ?>/fonts/font-face.css",
			"<?php echo TEMPLATEURL ?>/css/font-awesome.min.css",
			"<?php echo TEMPLATEURL ?>/mobile/css/jquery.mobile-1.3.1.min.css",
			"<?php echo $css_link; ?>"
			];
		for( var i=0 ; i < style.length; i++) {
			 // Check for browser support of event handling capability
			if (window.addEventListener)
				window.addEventListener("load", downloadCssAtOnload(style[i]), false);
			else if (window.attachEvent)
				window.attachEvent("onload", downloadCssAtOnload(style[i]));
			else window.onload = downloadCssAtOnload(style[i]);
		}

	</script>

	<?php
} else {
 ?>
	<script src="<?php echo TEMPLATEURL ?>/mobile/js/jquery.js"></script>
	<script type="text/javascript" src="<?php echo FRAMEWORK_URL . '/js/lib/underscore-min.js'?>"></script>
	<script src="<?php echo TEMPLATEURL ?>/mobile/js/jquery.mobile-1.3.1.min.js"></script>
	<script src="<?php echo TEMPLATEURL ?>/mobile/js/carousel.js"></script>
	<script src="<?php echo TEMPLATEURL ?>/mobile/js/jquery-inview.min.js"></script>
	<script src="<?php echo TEMPLATEURL ?>/mobile/js/script.js"></script>
	<script src="<?php echo TEMPLATEURL ?>/mobile/js/mobile-script.js"></script>

<?php
	if( is_page_template( 'page-post-ad.php' ) ) {
	?>
		<script src="<?php echo TEMPLATEURL ?>/mobile/js/post-ad.js"></script>
	<?php
	}
	if( is_page_template( 'page-reset-password.php' ) ){ ?>
		<script src="<?php echo TEMPLATEURL ?>/mobile/js/reset-password.js"></script>
		<?php
	}
}
get_template_part( 'template-js/review', 'item' );
do_action('et_mobile_footer' ); ?>
</body>
</html>