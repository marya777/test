<?php
/**
 * Template Name: List Categories
*/
get_header();
$ce_categories	=	ce_get_categories();
// echo "<pre>";
// print_r($ce_categories) ;
// echo "</pre>";
?>
	<div class="container-fluid title-page">
	  	<div class="main-center">
	    	<span class="text fontsize30"><?php _e("All Categories", ET_DOMAIN); ?></span>
	      	<!-- <div class="search search-catagories">
	          	<input type="text" id="" name="" placeholder="<?php _e("Search for a category...", ET_DOMAIN); ?>">
	            <i class="fa fa-search"></i>
	        </div>   -->
	    </div>
	</div><!--/.title page-->
	<?php
	$taxonomy	=	CE_AD_CAT;
	$i	=	0;
	foreach ($ce_categories as $key => $cat)   {
		if($cat->parent != 0 ) continue;
		if( $i == 0 ) {
	?>
	  	<div class="container-fluid categories-items">
	    	<div class="row main-center">
	    		<div class="col-md-12">
	    			<div class="row">
	 <?php } $i++;  ?>
	      				<div class="col-md-3 section-categories">
	        				<h1>
	        					<a href="<?php echo get_term_link( $cat, $taxonomy ); ?>" >
	        						<?php echo $cat->name ?>
	        					</a>
	        				</h1>
	            			<ul>
			            		<?php
			            			$j = 0;
			            			foreach ($ce_categories as $sub_key => $sub_cat) {
			            				if( $sub_cat->parent == $cat->term_id ) { ?>
				              				<li <?php if( $j > 3) echo 'class=hide';  ?> >
				              					<a href="<?php echo get_term_link( $sub_cat, $taxonomy ); ?>" >
				              						<?php echo $sub_cat->name; ?>
				              					</a>
												<?php echo ce_list_all_subcat( $ce_categories , $sub_cat , $taxonomy ); ?>
				              				</li>

					              		<?php
					              			$j++;
				              			}

				              		}

				              		if($j > 4 ) {
				              	?>

			              		<li class="bold view-all">
	              					<a href="#">
		              					<?php _e("View all", ET_DOMAIN); ?>
		              					<span class="icon-arrow"><i class="fa fa-arrow-down"></i></span>
		              				</a>
	              				</li>
	              				<?php } ?>
	            			</ul>
	      				</div>

	      	<?php if( $i == 4 ) { ?>
	      			</div>
	      		</div>
	    	</div><!--/.main center-->
	  	</div><!--/.fluid-container categories items-->
	  	<?php $i = 0; } ?>

  	<?php } 

  	if($i < 4 ) { ?>
					</div>
      		</div>
    	</div><!--/.main center-->
  	</div><!--/.fluid-container categories items--> <?php }

get_footer();

