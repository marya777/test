<?php 
et_get_mobile_header();

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

<div data-role="content" class="list-categories" >
    <h1 class="title-categories" ><?php printf(__("%s sellers", ET_DOMAIN) , $total_users ); ?></h1> 
    <ul data-role="listview" class="seller-list" >
        
   		<?php

		if(count($sellers) > 0){
			global $ce_seller;
        	foreach( $sellers as $key => $seller ) {
        		$ce_seller	=	ET_Seller::convert($seller);
       			get_template_part( 'mobile/seller', 'mobile' );
        	}
     	}  else {         		
     	?>
     		<li class="no-result list-divider"><h2><?php _e("No sellers here.", ET_DOMAIN); ?></h2></li>
     	<?php 
     	} ?>
     	
	</ul>
    <div id="seller-list-inview" ></div>
</div>
<?php 

et_get_mobile_footer();
