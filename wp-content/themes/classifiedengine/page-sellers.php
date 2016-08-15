<?php
/**
 * Template Name: List Sellers
*/
get_header();
//$sellers	=	ce_get_sellers ();

$name 		= 	isset($_REQUEST['s']) ? $_REQUEST['s'] : '';
$local 		= 	isset($_REQUEST['l']) ? $_REQUEST['l'] : '';
$args 		= 	array();
$args 		= 	array('search' => $name);
if(!empty($local))
$args 		= wp_parse_args(array('meta_key' =>'user_location', 'meta_value'=> $local, 'meta_compare'=>'like'),$args);

$args_total 	= wp_parse_args(array('number'=>false),$args);
$total_users	= count(ET_Seller::list_sellers($args_total) );

$paged 			= (get_query_var('paged')) ? get_query_var('paged') : 1; // Needed for pagination
$users_per_page = get_option('posts_per_page');
$offset 		= $users_per_page * ($paged - 1);
$total_pages 	= ceil(	$total_users / $users_per_page	);
$args 			= wp_parse_args(array('number'=>$users_per_page,'offset'=>$offset),$args);
$sellers  		= (array)ET_Seller::list_sellers($args);

?>

<div class="title-page">
  	<div class="container main-center">
  		<div class="row">
	      <div class="col-md-3">
	    	<span class="customize_heading text fontsize30 sellers-amount"><?php
	    		if($total_users > 1 ) {
	    			printf(__("%d sellers", ET_DOMAIN), $total_users);
	    		} else {
	    			printf(__("%d seller", ET_DOMAIN), $total_users);
	    		}
	    	?></span>
	      </div>
	      <div class="col-md-9">
	     	<div action="<?php echo get_permalink(get_the_ID());?>" id="frm_list_seller">
		        <div class="col-md-6 search search-seller-local">
		          <input tabindex="101" type="text" id="seller_location" name="seller_location" value="<?php echo $local;?>"  placeholder="<?php _e("Enter a seller location...", ET_DOMAIN); ?>">
		          <button id="" name="" class="btn"><span class="icon" data-icon="@"></span></button>
		        </div>
		        <div class="col-md-6 search search-seller">
		          <input tabindex="100" type="text" id="seller_name" name="seller_name" value="<?php echo $name;?>" placeholder="<?php _e("Enter a seller name...", ET_DOMAIN); ?>">
                  <i class="fa fa-search"></i>
		        </div>
	        </div>
	      </div>
	    </div>
    </div>
</div>
<div class="container main-center main-content">
    <div class="row">
      	<div class="col-md-3 left_bar">
       		<?php get_sidebar() ?>
      	</div><!--/span-->
      	<div class="col-md-9 product content-seller paddingTop45" >
        <div class="row padding20">
        	<div id="seller-list" class="ce-list-seller">
	        <?php

	    		if(count($sellers) > 0){
	    			global $ce_seller;
		        	foreach( $sellers as $key => $seller ) {
		        		$ce_seller	=	ET_Seller::convert($seller);
		       			get_template_part( 'template/seller' );
		        	}
	         	}  else {
	         	?>
	         		<div class=" no-result col-md-4 col-md-12 item-product">
						<p class="intro-product">
		         			<?php _e('Do not found sellers!',ET_DOMAIN); ?>
		         		</p>
	         		</div>
	         	<?php
	         	} ?>
			</div>
	        <div class="col-md-12 pagination-page">
	        <?php
				ce_seller_pagination ($total_pages, $paged);
	        ?>
	        </div>
        </div><!--/row-->
      </div><!--/.Latest product-->
    </div><!--/.main center-->
</div><!--/.fluid-container-->

<?php get_footer(); ?>