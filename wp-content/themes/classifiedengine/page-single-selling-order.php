<?php
/**
 * Template Name: Seller's order
 */
global $user_ID;
get_header();
?>
	<div class="title-page">
		<div class="main-center container">
			<span class="customize_heading text fontsize30"><?php _e("Order Detail", ET_DOMAIN); ?></span>

		</div>
	</div><!--/.title page-->

	<div class="tabs-acount">
		<div class="main-center container">
			<ul class="nav nav-tabs">
				<li><a title="<?php _e("Views all your ads", ET_DOMAIN); ?>" href="<?php echo et_get_page_link('account-listing') ?>" ><?php _e("Your Listings", ET_DOMAIN); ?></a></li>
				<li><a title="<?php _e("Views all your profile", ET_DOMAIN); ?>"  href="<?php echo et_get_page_link('account-profile'); ?>"><?php _e("Seller Profile", ET_DOMAIN); ?></a></li>
				<li><a title="<?php _e("Change password", ET_DOMAIN); ?>" href="<?php echo et_get_page_link('account-profile', array('section' => 'password')); ?>"><?php _e("Password", ET_DOMAIN); ?></a></li>
				<li><a title="<?php _e("Favourites Ads", ET_DOMAIN); ?>" href="<?php echo et_get_page_link('account-profile', array('section' => 'favourites')); ?>"><?php _e("Favourites Ads", ET_DOMAIN); ?></a></li>
				<?php do_action('page_account_nav_tab') ?>
			</ul>
		</div>
	</div><!--/.title page-->

	<div class="account-page main-content woocommerce" id="seller_listing">

<?php
        global $wpdb, $user_ID;
        $orderId = $_GET['id'];
        $order = wc_get_order($orderId);

        ?>

		<div class="container main-center accout-profile">
		  	<div class="row">
				<div class="col-md-8" id="latest_ads_container">
					<div class="jobs_container">
					    <?php do_action("ce_handle_seller_order_update", $order, $_POST);?>
                        <?php wc_print_notices(); ?>

                        <p class="order-info"><?php printf( __( 'Order <mark class="order-number">%s</mark> was placed on <mark class="order-date">%s</mark> and is currently <mark class="order-status">%s</mark>.', ET_DOMAIN ), $order->get_order_number(), date_i18n( get_option( 'date_format' ), strtotime( $order->order_date ) ), wc_get_order_status_name( $order->get_status() ) ); ?></p>

                        <?php if ( $notes = $order->get_customer_order_notes() ) :
                            ?>
                            <h2><?php _e( 'Order Updates', 'woocommerce' ); ?></h2>
                            <ol class="commentlist notes">
                                <?php foreach ( $notes as $note ) : ?>
                                    <li class="comment note">
                                        <div class="comment_container">
                                            <div class="comment-text">
                                                <p class="meta"><?php echo date_i18n( __( 'l jS \o\f F Y, h:ia', 'woocommerce' ), strtotime( $note->comment_date ) ); ?></p>
                                                <div class="description">
                                                    <?php echo wpautop( wptexturize( $note->comment_content ) ); ?>
                                                </div>
                                                <div class="clear"></div>
                                            </div>
                                            <div class="clear"></div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        <?php
                        endif;?>
                        <header>
                            <h2><?php _e( 'Product list', ET_DOMAIN ); ?></h2>
                        </header>
                        <?php if(!$order->has_status( cem_get_lock_status() )): ?>
                        <form method="POST">
                            <?php wp_nonce_field('ce_update_order_nonce','ce_update_order_nonce'); ?>
                            <input type="hidden" name="orderId" value="<?php echo $order->id ?>"/>
                            <?php endif; ?>
                            <table class="shop_table cart" style="width:100%">
                                <thead>
                                    <tr>
                                        <th class="product-name"><?php _e( 'Product', ET_DOMAIN ); ?></th>
                                        <th class="product-total"><?php _e( 'Total', ET_DOMAIN ); ?></th>
                                        <th class="product-total"><?php _e( 'Status', ET_DOMAIN ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                    if ( sizeof( $order->get_items() ) > 0 ) {
                                        foreach( $order->get_items() as $item_id => $item ) {
                                            $_product = apply_filters('woocommerce_order_item_product', $order->get_product_from_item($item), $item);
                                            if($_product->post->post_author != $user_ID)
                                            {
                                                continue;
                                            }
                                            $item_meta = new WC_Order_Item_Meta($item['item_meta'], $_product);
                                            ?>
                                            <tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'order_item', $item, $order ) ); ?>">
                                                <td class="product-name">
                                                    <?php
                                                    if ( $_product && ! $_product->is_visible() )
                                                        echo apply_filters( 'woocommerce_order_item_name', $item['name'], $item );
                                                    else
                                                        echo apply_filters( 'woocommerce_order_item_name', sprintf( '<a href="%s">%s</a>', get_permalink( $item['product_id'] ), $item['name'] ), $item );

                                                    echo apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times; %s', $item['qty'] ) . '</strong>', $item );

                                                    if ( $_product && $_product->exists() && $_product->is_downloadable() && $order->is_download_permitted() ) {

                                                        $download_files = $order->get_item_downloads( $item );
                                                        $i              = 0;
                                                        $links          = array();

                                                        foreach ( $download_files as $download_id => $file ) {
                                                            $i++;

                                                            $links[] = '<small><a href="' . esc_url( $file['download_url'] ) . '">' . sprintf( __( 'Download file%s', ET_DOMAIN ), ( count( $download_files ) > 1 ? ' ' . $i . ': ' : ': ' ) ) . esc_html( $file['name'] ) . '</a></small>';
                                                        }

                                                        echo '<br/>' . implode( '<br/>', $links );
                                                    }
                                                    ?>
                                                </td>
                                                <td class="product-total">
                                                    <?php echo $order->get_formatted_line_subtotal( $item ); ?>
                                                </td>
                                                <td class="product-total">
                                                    <?php if(!$order->has_status( cem_get_lock_status() )): ?>
                                                    <?php cem_render_item_status_select($item_id) ?>
                                                    <?php else: ?>
                                                        <?php echo wc_get_order_status_name(wc_get_order_item_meta($item_id, 'status', true));?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                ?>
                                </tbody>
                            </table>
                            <?php if(!$order->has_status( cem_get_lock_status() )): ?>
                            <input type="submit" value="<?php _e("Update order", ET_DOMAIN) ?>"/>
                            </form>
                            <?php endif; ?>
                        <header>
                            <h2><?php _e( 'Customer details', ET_DOMAIN ); ?></h2>
                        </header>
                        <dl class="customer_details">
                        <?php
                        if ( $order->billing_email ) echo '<dt>' . __( 'Email:', ET_DOMAIN ) . '</dt><dd>' . $order->billing_email . '</dd>';
                        if ( $order->billing_phone ) echo '<dt>' . __( 'Telephone:', ET_DOMAIN ) . '</dt><dd>' . $order->billing_phone . '</dd>';

                        // Additional customer details hook
                        do_action( 'woocommerce_order_details_after_customer_details', $order );
                        ?>
                        </dl>

                        <?php if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && get_option( 'woocommerce_calc_shipping' ) !== 'no' ) : ?>

                            <div class="col2-set addresses">
                                <div class="col-1">

                                <?php endif; ?>

                                <header class="title">
                                    <h3><?php _e( 'Billing Address', ET_DOMAIN ); ?></h3>
                                </header>
                                <address>
                                    <?php
                                        if ( ! $order->get_formatted_billing_address() ) _e( 'N/A', ET_DOMAIN ); else echo $order->get_formatted_billing_address();?>
                                </address>

                                <?php if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && get_option( 'woocommerce_calc_shipping' ) !== 'no' ) : ?>

                                </div><!-- /.col-1 -->

                                <div class="col-2">

                                    <header class="title">
                                        <h3><?php _e( 'Shipping Address', ET_DOMAIN ); ?></h3>
                                    </header>
                                    <address>
                                        <?php
                                        if ( ! $order->get_formatted_shipping_address() ) _e( 'N/A', ET_DOMAIN ); else echo $order->get_formatted_shipping_address();
                                        ?>
                                    </address>

                                </div><!-- /.col-2 -->

                            </div><!-- /.col2-set -->

                        <?php endif; ?>

                        <div class="clear"></div>
				    </div>
				</div>


				<div class="col-md-4" id="static-text-sidebar">
					<?php
                        ce_seller_packages_data();
                        get_sidebar();
                    ?>
                </div>
				</div>
		  	</div>
		</div><!--/.main center-->
	</div><!--/.fluid-container categories items-->


<?php
get_footer();
?>