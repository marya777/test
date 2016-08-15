<?php
/**
 * template ad in seller profile listing
*/
global $post;
$ad 	=	CE_Ads::convert($post);

$statuses = array(
	'archive' =>
		array(
			'title' => __('ARCHIVED', ET_DOMAIN),
			'class' => 'expired'
		),
	'draft' =>
		array(
			'title' => __('DRAFT', ET_DOMAIN),
			'class' => 'pending'
		),
	'pending' =>
		array(
			'title' => __('PENDING', ET_DOMAIN),
			'class' => 'pending'
		),
	'publish' =>
		array(
			'title' => __('ACTIVE', ET_DOMAIN),
			'class' => 'active'
		),
	'reject' =>
		array(
			'title' => __('REJECTED', ET_DOMAIN),
			'class' => 'pending'
		)
	);

$queries = array('reject', 'pending', 'draft', 'publish', 'archive');

$color		=	'color-yellow';
$paid_color =	'color-yellow';

$title		=	__("FREE", ET_DOMAIN);

if( !$ad->et_paid ) {
	$paid_color	=	'color-purple';
	$title		=	__("UNPAID", ET_DOMAIN);
}

if($ad->post_status == 'publish') $color = 'color-green1';

if($ad->post_status == 'draft' || $ad->post_status == 'archive' || $ad->post_status == 'reject' ) $color = 'color-purple';

if( $ad->et_paid == 1 ) {
	$paid_color	=	'color-green1';
	$title		=	__("PAID", ET_DOMAIN);
}



// if( $ad->et_paid == 0 ){
// 	$title		=	__("UNPAID", ET_DOMAIN);
// 	//$paid_color = 'color-purple';
// }


?>

<li class="ce-ad-item tooltips" id="ad_item_<?php the_ID();?>">


	<span class="list-title"><a href="<?php the_permalink(); ?>" ><?php the_title() ?></a> </span> 
	<span class="list-date"><?php echo get_the_date(); ?></span>
	<span class="list-event button-event">
		<span class="list-status sembold <?php echo $color ?>"><?php echo $statuses[$ad->post_status]['title']; ?> </span>

		<span title="<?php echo $title; ?>" class="icon <?php echo $paid_color; ?>" data-icon="%"></span>

		<?php if($ad->post_status == 'publish' || $ad->post_status == 'pending' || $ad->post_status == 'reject'  ) {
			echo '<a class="edit" href="" title="'.__('Edit',ET_DOMAIN).'"><span class="icon" data-icon="p"></span></a>';
		} ?>

		<?php if( $ad->post_status == 'draft'  ) {
			echo '<a class="" href="'.et_get_page_link('post-ad', array ('id' => $ad->ID) ).'" title="'.__('Edit',ET_DOMAIN).'"><span class="icon" data-icon="p"></span></a>';
		} ?>

		<?php  if($ad->post_status == 'archive' ) {
			echo '<a href="'.et_get_page_link('post-ad', array ('id' => $ad->ID) ).'" title="'.__('Renew',ET_DOMAIN).'">
			<span class="icon" data-icon="1"></span></a>'	;
		}

		?>
		<a href="#" class="views" data-toggle="tooltip" data-original-title="<?php echo CE_Ads::post_views($ad->ID);?>" onclick ="return false;" title="<?php echo CE_Ads::post_views($ad->ID); ?>">
			<span class="icon" data-icon="E"></span>
		</a>
		<?php if($ad->post_status == 'draft' || $ad->post_status == 'archive') { ?>
			<a href="" class="delete" title="<?php _e("Delete", ET_DOMAIN); ?>" ><span class="icon  title="Delete" color-purple" data-icon="*"></span></a>
		<?php } else { ?>

			<a href="" class="archive"><span class="icon" title="<?php _e("Archive", ET_DOMAIN); ?>" data-icon="#"></span></a>
		<?php } ?>
	</span>
</li>