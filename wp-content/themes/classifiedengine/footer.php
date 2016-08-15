</div>
	<div class="bg-footer" <?php echo Schema::WPFooter() ?>>
		<div class="container main-center">
		  	<div class="row">
			  	<div class="col-md-5">
			  		<div class="about-company">

				  		<?php
				  		global $ce_option;
				  		if(is_active_sidebar('ce_footer_1') ) {
				  			dynamic_sidebar( 'ce_footer_1' );
				  		}else {

				  			$instance = array('title'=>'ClassifiedEngine','text'=>__('<p>ClassifiedEngine is the most advanced and usable classifieds Wordpress theme, the only one truly responsive and front-end controls packed.</p>',ET_DOMAIN));
				  			the_widget( 'WP_Widget_Text', $instance);

				  		} ?>
						<span class="copyright"> <?php if($ce_option->et_copyright != '' ) echo $ce_option->et_copyright .'<br />'; ?>
							<!-- <span class="enginethemes">
								<a href="http://www.enginethemes.com/themes/classifiedengine/" >Classified Ad SoftwarePowered by WordPress
							</span> -->
						</span>

					</div>
			  	</div>
			  	<div class="col-md-7">
			  		<?php
			  		if(is_active_sidebar( 'ce_footer_2' ))
			  			dynamic_sidebar( 'ce_footer_2' );
			  		else {
			  			if(current_user_can( 'manage_options' )) {
			  				echo '<div class="footer-sample"><span style="display:block; margin-top:100px;">';
				  			printf(__(' Go to <a href="%s" >Widgets Dashboard</a> and set widgets for this area.',ET_DOMAIN ), admin_url( 'widgets.php' ));
				  			echo '</span></div>';
			  			}
			  		}
			  		?>

		  		</div>
			</div>
		</div>
	</div>
	<?php
		if( current_user_can( 'manage_options' ) || is_page_template( 'page-account-listing.php' )){
			echo '<div class="hidden">';
			wp_editor( 'div_load_tiny','div_load_tiny', ce_ad_editor_settings());
			echo '</div>';
		}

		wp_footer();
	?>
	</body>
</html>