<?php
$color		=	'color-yellow';
global $seller_orders;
$firstProduct = reset($seller_orders);
switch($firstProduct->post_status){
    case 'wc-processing':
        $color = 'color-green1';
        break;
}
?>

<li class="ce-ad-item tooltips" id="ad_item_<?php echo $firstProduct->order_id ?>">
    <div class="clearfix">
        <span class="list-title"><a href="<?php echo et_get_page_link( array('page_type' => 'single-selling-order', 'post_title' => __("Order detail", ET_DOMAIN )), array('id'=>$firstProduct->order_id)) ?>"><?php echo sprintf( __("Order No. #%d", ET_DOMAIN), $firstProduct->order_id) ?></a></span>
        <span class="list-event button-event">
            <span class="list-status sembold <?php echo $color ?>"><?php echo wc_get_order_status_name($firstProduct->post_status) ?> </span>
        </span>
    </div>
    <div class="clearfix order-detail collapse" id="#collapseDetail-<?php echo $firstProduct->order_id ?>">
        <table class="shop_table cart" cellspacing="0" style="width: 100%">
            <thead>
                <th class="product-name"><?php _e("Product", ET_DOMAIN) ?></th>
                <th class="product-quantity"><?php _e("Quality", ET_DOMAIN) ?></th>
                <th class="product-subtotal"><?php _e("Subtotal", ET_DOMAIN) ?></th>
                <th class="product-status"><?php _e("Status", ET_DOMAIN) ?></th>
            </thead>
        <?php foreach($seller_orders as $product): ?>
            <tr>
                <td><a href="<?php echo get_permalink($product->product_id) ?>"><?php echo $product->product_title; ?></a></td>
                <td class="product-quantity"><?php echo $product->qty; ?></td>
                <td class="product-subtotal"><?php echo wc_price($product->subtotal); ?></td>
                <td>
                    <?php echo cem_get_order_item_status(wc_get_order_item_meta($product->order_item_id, 'status')) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </table>
    </div>
</li>