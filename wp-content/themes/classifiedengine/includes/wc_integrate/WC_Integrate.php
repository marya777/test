<?php
/**
 * Project : classifiedengine
 * User: thuytien
 * Date: 11/28/2014
 * Time: 9:16 AM
 */

/**
 * Class WC_Integrate
 * Use for integrate CE with WC
 */
require_once get_template_directory() . '/includes/wc_integrate/report/CE_Admin_Report.php';
require_once get_template_directory() . '/includes/wc_integrate/class-wc-admin-dashboard-integrate.php';

class WC_Integrate
{
    /**
     * @return static
     */
    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static();
        }
        return $instance;
    }

    public function add_hook()
    {
        add_filter('woocommerce_order_class', array(static::getInstance(), 'wc_woocommerce_order_class'), 10, 3);
        add_filter('woocommerce_product_class', array(static::getInstance(), 'wc_woocommerce_product_class'), 10, 4);
        add_filter('woocommerce_valid_order_statuses_for_payment_complete', array(static::getInstance(), 'woocommerce_valid_order_statuses_for_payment_complete'), 10, 2);

        add_filter('wc_order_statuses', array(static::getInstance(), 'wc_order_statuses'), 10, 2);
        add_filter('woocommerce_payment_complete_order_status', array(static::getInstance(), 'woocommerce_payment_complete_order_status'), 10, 2);
        add_filter('woocommerce_order_item_needs_processing', array(static::getInstance(), 'woocommerce_order_item_needs_processing'), 10, 3);

        //hide product menu
        //add_filter('woocommerce_register_post_type_product', array(static::getInstance(), 'woocommerce_register_post_type_product'));
        //add_filter('woocommerce_register_post_type_shop_order', array(static::getInstance(), 'woocommerce_register_post_type_shop_order'));
        //add_filter('woocommerce_register_post_type_shop_coupon', array(static::getInstance(), 'woocommerce_register_post_type_shop_coupon'));

        //add filter function not in this class
        add_filter('woocommerce_admin_reports', array('CE_Admin_Report', 'get_reports'));

        add_filter('woocommerce_new_customer_data', array(static::getInstance(), 'woocommerce_new_customer_data'), 10, 1);
    }

    /**
     * Edit user data when create new user
     *
     * @param $userdata
     * @return mixed
     */
    function woocommerce_new_customer_data($userdata)
    {
        $userdata['role'] = 'seller';
        return $userdata;
    }

    /**
     * @param $args
     * @return mixed
     *
     * Hide coupon menu from admin menu
     */
    function woocommerce_register_post_type_shop_coupon($args){
        $args['show_ui'] = false;
        $args['public'] = false;

        return $args;
    }

    /**
     * @param $args
     * @return mixed
     *
     * Hide Order menu from admin menu
     */
    function woocommerce_register_post_type_shop_order($args){
        $args['show_ui'] = false;
        $args['public'] = false;

        return $args;
    }

    /**
     * @param $args
     * @return mixed
     *
     * Hide product menu from admin menu
     */
    function woocommerce_register_post_type_product($args)
    {

        $args['show_ui'] = false;
        $args['public'] = false;

        return $args;
    }

    /**
     * Add new custom status for new action
     */
    function ce_new_custom_status()
    {

        register_post_status('wc-confirmed', array(
            'label' => _x('Confirmed', 'Order status', ET_DOMAIN),
            'public' => false,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Confirmed <span class="count">(%s)</span>', 'Confirmed <span class="count">(%s)</span>', ET_DOMAIN)
        ));

        register_post_status('wc-fundrequested', array(
            'label' => _x('Fund fequested', 'Order status', ET_DOMAIN),
            'public' => false,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Fund Requested <span class="count">(%s)</span>', 'Fund Requested  <span class="count">(%s)</span>', ET_DOMAIN)
        ));
    }

    /**
     * @param $order_statuses
     * @return array
     *
     * Return array of available order status
     */
    function wc_order_statuses($order_statuses)
    {
        $new_statuses = array(
            'wc-confirmed' => _x('Confirmed', 'Order status', ET_DOMAIN),
            'wc-fundrequested' => _x('Fund requested', 'Order status', ET_DOMAIN)
        );
        $order_statuses = array_merge($order_statuses, $new_statuses);
        return $order_statuses;
    }

    /**
     * @param $is_isneed
     * @param $_product
     * @param $id
     * @return bool
     *
     * If product is need processing step
     */
    function woocommerce_order_item_needs_processing($is_isneed, $_product, $id)
    {
        if ($_product instanceof ET_WC_Package) {
            return false;
        }
        return $is_isneed;
    }

    /**
     * @param $status
     * @param $order
     * @return string
     *
     * Return status for complete order
     */
    function woocommerce_payment_complete_order_status($status, $order)
    {
        if ($order instanceof ET_WC_Order) {
            return 'publish';
        }
        return $status;
    }

    /**
     * @param $status
     * @param $order
     * @return array
     *
     * Return array of valid order status for process payment complete
     */
    function woocommerce_valid_order_statuses_for_payment_complete($status, $order)
    {
        if ($order instanceof ET_WC_Order) {
            return array('pending', 'draft');
        }
        return $status;
    }

    /**
     * @param $classname
     * @param $product_type
     * @param $post_type
     * @param $product_id
     * @return string
     *
     * Return class to wrap product data
     */
    function wc_woocommerce_product_class($classname, $product_type, $post_type, $product_id)
    {
        if ($classname == false && (in_array($post_type, array('payment_package')))) {
            $classname = "ET_WC_Package";
        }
        return $classname;
    }

    /**
     * @param $classname
     * @param $post_type
     * @param $order_id
     * @return string
     *
     * Return class to wrap Order data
     */
    function wc_woocommerce_order_class($classname, $post_type, $order_id)
    {
        if ($post_type == "order") {
            $classname = "ET_WC_Order";
        }
        return $classname;
    }

}