<?php
/**
 * Installation related functions and actions.
 */

if (!class_exists('CE_Install')) :

    /**
     * WC_Install Class
     */
    class CE_Install
    {

        /**
         * Hook in tabs.
         */
        public function __construct()
        {
            add_action('admin_init', array($this, 'check_version'), 5);
            add_action( 'wp_ajax_et-update-database', array($this,'ajax_update_database'));
            add_action( 'wp_ajax_et-reverse-database', array($this,'ajax_reverse_database'));
        }

        /**
         * check_version function.
         *
         * @access public
         * @return void
         */
        public function check_version()
        {
            if (!defined('IFRAME_REQUEST') && (get_option('woocommerce_version') != CE_VERSION || get_option('woocommerce_db_version') != CE_VERSION)) {
                //$this->install();
            }
        }

        /**
         * Install WC
         */
        public function install()
        {
            if(!current_user_can( 'manage_options' )) return;
            // Queue upgrades
            $current_version = get_option('ce_version', null);
            if ( null == $current_version || version_compare($current_version, CE_VERSION, '<')) {
                $current_db_version = get_option('ce_db_version', null);
                if ( null == $current_db_version || version_compare($current_db_version, '1.9.0', '<')) {
                    $this->update();
                    // Update version
                    update_option('ce_version', CE_VERSION);
                } else {
                    update_option('woocommerce_db_version', CE_VERSION);
                }               

                //wp_redirect(admin_url('index.php?page=ce-whatisnew&ce-updated=true'));
            }
        }

        /**
         * Handle updates
         */
        public function update()
        {
            include('updates/1.9.0.php');
            update_option('ce_db_version', CE_VERSION);
        }

        public function ajax_update_database(){
            if(!current_user_can( 'manage_options' )) return;
            include('updates/1.9.0.php');
            wp_send_json( array('msg' => __( 'Update database successful.' , ET_DOMAIN )) );
        }

        public function ajax_reverse_database(){
            if(!current_user_can( 'manage_options' )) return;
            include('updates/1.9.0-reverse.php');
            wp_send_json( array('msg' => __( 'Update database successful.' , ET_DOMAIN )) );
        }
    }

endif;

return new CE_Install();
