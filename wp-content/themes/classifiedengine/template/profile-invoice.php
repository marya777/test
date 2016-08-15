<?php
$color		=	'color-yellow';
global $customer_order;
$order = wc_get_order( $customer_order );
?>
<li class="ce-ad-item tooltips" id="order_item_<?php echo $order->get_order_number() ?>">
    <div>
        <span class="list-title"><a href="<?php echo $order->get_view_order_url() ?>"><?php echo sprintf( __("Order No. %s", ET_DOMAIN), $order->get_order_number()) ?></a></span>
        <span class="list-event button-event">
            <span class="list-status sembold <?php echo $color ?>"><?php echo wc_get_order_status_name($order->get_status()) ?> </span>
        </span>
    </div>
    <div class="clearfix">
    </div>
</li>