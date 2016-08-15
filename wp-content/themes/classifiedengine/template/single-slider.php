<?php 
/**
 * slider template in single ad
*/
global $post;
$attachment = get_children( array(
            'numberposts' => 15,
            'order' => 'ASC',
            'post_mime_type' => 'image',
            'post_parent' => $post->ID,
            'post_type' => 'attachment'
          ),OBJECT );

$slides   = array();
$thumb_id   =   get_post_thumbnail_id($post->ID);
if( $thumb_id ) {
    $alt = trim(strip_tags( get_post_meta($thumb_id, '_wp_attachment_image_alt', true) ));
    $title = get_the_title( $thumb_id );
    $src            = wp_get_attachment_image_src( $thumb_id , 'ad-slide'); 
    $src_full       = wp_get_attachment_image_src( $thumb_id , 'ad-slide-large'); 
    $src_origin     = wp_get_attachment_image_src( $thumb_id, 'full' );
    $slides[]   = $src;    
}
$i = 0;
/**
  * ad images
 */
if( !empty($attachment) || !empty($slides) ) { ?>
    <div class="col-md-12 bg-slide-listing" style="opacity: 0;">
        <span class="btn-zoom in"><i class="fa fa-expand"></i></span> 
        <div class="slide-listing-wraper">     
            <ul class="slide-listing">
                <?php if( isset($attachment[$thumb_id]) ) unset($attachment[$thumb_id]); ?>
                    <li id="0" style="text-align : center;">
                        <a href="<?php echo $src_origin[0]; ?>" class="item-slider" rel="item-slider" title="<?php echo get_the_title($thumb_id); ?>" target="_blank" >
                            <img <?php echo Schema::Product("image"); ?> src="<?php echo $src[0] ?>" data-normal = "<?php echo $src[0]; ?>" data-full="<?php echo $src_full[0] ?>" alt="<?php echo $alt ?>" title="<?php echo $title; ?>"/>
                        </a>
                    </li>
                <?php $i++;  ?>
                <?php 
                
                foreach ( $attachment as $key => $att) {
                    $alt = trim(strip_tags( get_post_meta($att->ID, '_wp_attachment_image_alt', true) ));
                    $title = "Image : " . $alt;//get_the_title( $att->ID ); //disable for perfomance;
                    $image          = wp_get_attachment_image_src( $att->ID, 'ad-slide' );
                    $image_full     = wp_get_attachment_image_src( $att->ID, 'ad-slide-large' );
                    $image_origin   = wp_get_attachment_image_src( $att->ID, 'full' );
                    $slides[] = $image
                ?>
                    <li id="<?php echo $i; ?>" style="text-align : center;">
                        <a href="<?php echo $image_origin[0]; ?>"  class="item-slider" rel ="item-slider" title="<?php echo get_the_title($att->ID); ?>" target="_blank" >
                            <img <?php echo Schema::Product("image"); ?> src="<?php echo $image[0] ?>" data-normal = "<?php echo $image[0]; ?>" data-full="<?php echo $image_full[0] ?>" alt="<?php echo $alt ?>" title="<?php echo $title; ?>"/>
                        </a>
                    </li>
                <?php $i++; }  ?>
            </ul>
        </div>
        <?php if(count($slides) > 1 ) { ?>
        <div class="bg-slide-thumbnails">
            <span class="prev-slide"><span class="icon-prev"></span></span>
            <div class="slide-thumbnails">
                <div id="bx-pager" class="contronl-slide">
                <?php foreach ($slides as $key => $slide) { ?>
                <a class="<?php if($key == 0) echo 'active'; ?>" data-slide-index="<?php echo $key ?>" href=""><img src="<?php echo $slide[0] ?>" /></a>
                <?php } ?>
                </div>
            </div>
            <span class="next-slide"><span class="icon-next"></span></span> 
        </div>
        <?php } ?>
    </div>
<?php 
}