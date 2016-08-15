<?php
class CE_Options extends ET_Options {

        public static $instance        =        null;
        public static $opt_keys        =        array (
                    'et_pending_ad'         => 1,
                    'et_site_title'         => '',
                    'et_copyright'          => '',
                    'et_twitter_account'    => '',
                    'et_facebook_link'      => '',
                    'et_site_desc'          => '',
                    'et_google_analytics'   => '',
                    'et_language'           => "Site language",
                    'et_google_plus'        => '',
                    'et_website_logo'       => 0,
                    'et_mobile_icon'        => 0,
                    'et_default_logo'       => 0,
                    'et_currency_sign'      => '$',
                    'et_currency_align'     => 0,
                    'et_comment_ad'         => 0,
                    'et_use_captcha'        => 0,
                    'et_facebook_login'     => 0,
                    'et_twitter_login'      => 0,
            ) ;

        public static $option_name      =   'et_ce_opts';

        public static function get_instance () {
                if(self::$instance == null) {
                        self::$instance        =        new CE_Options();
                }
                return self::$instance;
        }

        public function __construct($option_name = 'et_ce_opts') {
            parent::__construct($option_name);
            $this->options_arr    =       wp_parse_args( $this->options_arr, self::$opt_keys );
        }

        /**
         * update payment setting
         * @param $gateway : payment gate name
         * @param $value : array ('key' => 'value' );
        */

        /**
         * if ceengine use pending ad, it will return true.
         * if it set to be false, company post ad will not be pending
         */
        public function use_pending () {
                return $this->get_option('et_pending_ad', true);
        }
        /**
         * set pending job option
         * @param bool $value : true or false
         */
        public function set_use_pending ( $value ) {
                return $this->update_option('et_pending_ad', $value );
        }
         /**
         * set comment ad option
         * @param bool $value : true or false
         */
         public function set_use_comment ( $value ) {
                return $this->update_option('et_comment_ad', $value );
        }
         /**
         * set captcha option
         * @param bool $value : true or false
         */
        public function use_captcha () {
            $google_captcha =   ET_GoogleCaptcha::get_api();          
            if(empty($google_captcha['private_key']) || empty($google_captcha['public_key']))
                return false;

            return $this->get_option('et_use_captcha', true);
        }
        public function set_use_captcha ( $value ) {
            $google_captcha =   ET_GoogleCaptcha::get_api();
            if(empty($google_captcha['private_key']) || empty($google_captcha['public_key'])){
                $this->update_option('et_use_captcha', 0 );
                return false;
            }
            return $this->update_option('et_use_captcha', $value );
        }
        public function set_use_captcha_form_comment ( $value ) {
            $google_captcha =   ET_GoogleCaptcha::get_api();
            return $this->update_option('et_comment_ad_captcha', $value );
        }
        public function use_facebook(){
            $app_id = ET_FaceAuth::get_app_id();
            if( empty( $app_id) )
               return false;
            return $this->get_option('et_facebook_login',false);
        }
        public function use_twitter(){
            $twitter_id     = ET_TwitterAuth::get_twitter_key();
            $twitter_secret = ET_TwitterAuth::get_twitter_secret();
            if( empty($twitter_id) || empty( $twitter_secret ) )
                return false;
            return $this->get_option('et_twitter_login');   
        }
        public function set_facebook_login ( $value ) {           
            return $this->update_option('et_facebook_login', $value );
        }
         public function set_twitter_login ( $value ) {           
            return $this->update_option('et_twitter_login', $value );
        }

        public function get_site_desc () {
                return get_bloginfo( 'description' );
                //return $this->('et_site_desc');
        }
        
        public function set_site_desc ( $new_value ) {
                update_option ('blogdescription', $new_value);
                //update_option ('description', $new_value);                
                return $this->update_option('et_site_desc', $new_value);
                
        }

        public function get_site_title () {
                return get_bloginfo('name');
        }
        
        public function set_site_title ($new_value) {
                update_option('blogname', $new_value );
                return        $this->update_option('et_site_title', $new_value);
                
        }

        public function set_site_demonstration ( $new_value ) {
                //use this pattern to remove any empty tag '
                $pattern = "/<[^\/>]*>(&nbsp;)*([\s]?)*<\/[^>]*>/";
        
                $new_value        =         preg_replace($pattern, '', $new_value);
                return        $this->update_option('et_site_demon', trim($new_value));
                
        }
        /**
         * get jobengine site desmontration text
        */
        public function get_site_demonstration () {
                return apply_filters('et_get_site_demonstration',$this->get_option ('et_site_demon'));
        }

        static function set_api_payement($gateway, $name, $value){

                $api = array();
                switch ($gateway) {
                        case 'paypal':
                                $api = ET_Paypal::get_api();                        
                                $api[$name] = $value;
                                return ET_Paypal::set_api($api);
                                break;
                        case '2checkout' :
                                $api = ET_2CO::get_api();
                                $api[$name] = $value;                                
                                return ET_2CO::set_api($api);
                                break;
                        case 'google' :
                                $api = ET_GoogleCheckout::get_api();
                                $api[$name] = $value;                                
                                return ET_GoogleCheckout::set_api($api);
                                break;                                        
                        default:
                                return false;                        
                }                
        }

        public function get_website_logo ($size = false) {
            $logo_id = $this->get_website_logo_id();
            if ($logo_id) {
                if(!$size) {
                    return wp_get_attachment_image_src( $this->get_website_logo_id(), 'full' );
                }                    
                return  wp_get_attachment_image_src( $this->get_website_logo_id(), $size );
            }else
            return array( TEMPLATEURL . '/img/website_logo.png', 200, 70);
        }

        public function get_website_logo_id () {
            return $this->get_option('et_website_logo');
        }

        /**
         * set site logo setting
         * @param string $new_logo
         */
        public function set_website_logo ($new_logo) {
                return $this->update_option('et_website_logo', $new_logo);

        }
        /**
         *        get mobile icon setting
         */
        public function get_mobile_icon () {
                $icon_id = $this->get_mobile_icon_id();
                if ($icon_id)
                        return wp_get_attachment_image_src( $this->get_mobile_icon_id(), 'thumbnail' );
                else
                        return array( TEMPLATEURL . '/img/mobile_icon.png', 144, 144);
        }

        public function get_favicon () {
                $icon_id = $this->get_mobile_icon_id();
                if ($icon_id)
                        return wp_get_attachment_image_src( $this->get_mobile_icon_id(), 'small_thumb' );
                else
                        return array( TEMPLATEURL . '/img/mobile_icon.png', 16, 16);
        }

        public function get_mobile_icon_id () {
                return $this->get_option('et_mobile_icon');
        }

        /**
         * set mobile icon
         * @param string, int $new_icon : icon attachment id
         */
        public function set_mobile_icon ($new_icon){
                return $this->update_option('et_mobile_icon', $new_icon);
        }
        /**
         *        get default company logo setting
         */
        public function get_default_logo () {
            $default_logo = $this->get_default_logo_id();
            if ($default_logo)
                    return wp_get_attachment_image_src( $this->get_default_logo_id(), 'company-logo' );
            else
                    return array( TEMPLATEURL . '/img/default_logo.jpg', 200, 200);
        }

        public function get_default_logo_id () {
            return $this->get_option('et_default_logo');
        }

        /**
         * set default company logo
         * @param string, int $new_logo : logo attachment id
         */
        public function set_default_logo ($new_logo){
            return $this->update_option('et_default_logo', $new_logo);
        }
        /**
         * set language setting
         * @param string $new_lang : language file name
         */
        public function set_language ( $new_lang ) {
            return $this->update_option('et_language', $new_lang );
        }
        /**
         * get site language setting
         */
        public function get_language ( ) {
            return $this->get_option('et_language');
        }


        public function set_customization ($value) {
            return update_option('et_customization', $value );
        }

        public function get_customization () {
            $default    =   array(
                    'background' => '#ffffff',
                    'header'    => '#4F4F4F',
                    'heading'   => '#333333',
                    'footer'    => '#F2F2F2',
                    'text'      => '#446f9f',
                    'action'    => '#e64b21',
                    'pattern'   => 'pattern1',
                    'font-heading'          => 'Arial',
                    'font-heading-weight'   => 'bold',
                    'font-heading-style'    => 'italic',
                    'font-heading-size'     => '14px',
                    'font-text'             => 'Arial',
                    'font-text-weight'      => 'normal',
                    'font-text-style'       => 'normal',
                    'font-text-size'        => '12px',
                );
            $style  =    get_option('et_customization', array());
            return wp_parse_args( $style, $default );
        }

        public function get_layout() {
            return get_option( 'ce_layout', 'sidebar-content' );
        }

        public function set_layout($new_layout) {
            update_option('ce_layout', $new_layout);
        }

        public function add_currency ($code, $value) {
            $currency_list          =   get_option('et_currency_list', array ());
            $currency_list[$code]   =   $value;
            update_option('et_currency_list', $currency_list);
        }

        public function get_currency_list () {
            return  get_option('et_currency_list', array ());
        }

        public function get_copyright() {
            return '';
        }

}

/**
 * class ce mailtemplate
*/
class ET_CEMailTemplate extends ET_Options
{
        private $prefix;
        // protected $opt_keys;
        public static $opt_keys =   array (
                'et_register_mail'              =>         "",
                'et_forgot_pass_mail'           =>         "",
                'et_reset_pass_mail'            =>         "",
                // 'et_apply_mail'                 =>         "",
                // 'et_remind_mail'                =>         "",
                'et_approve_mail'               =>         "",
                'et_reject_mail'                =>         "",
                'et_archive_mail'               =>         "",
                'et_cash_notification_mail'     =>         "",
                'et_message_to_seller_mail'     =>         "",
                'et_send_receipt_mail'          =>         "",
                'et_receipt_mail'               =>         "",

            );
        public static $option_name  =   'ce_mailtemplate';

        function __construct() {
            global $et_global;
            $this->prefix = $et_global['db_prefix'];

            parent::__construct(self::$option_name);
            // $this->options_arr    =       wp_parse_args( $this->options_arr, self::$opt_keys );
        }
        /**
         * update mail template settings
         * @param string $mail : mail type
         * @param string $value : new mail value
         */
        public function update_mail_template ( $mail, $value ) {
            $value      =   stripcslashes($value);
            $key        =   $this->prefix.$mail;

            $opt_key    =   self::$opt_keys;

            if(isset($opt_key[$key])) {
                return $this->update_option($key, $value);
            }
            return false;
        }

        function reset_mail_template ( $mail) {
            $new_value        =        '';
            switch ($mail) {
                case 'et_register_mail':
                        return $this->set_register_mail ( $new_value, true );

                case 'et_forgot_pass_mail':
                        return $this->set_forgot_pass_mail ( $new_value, true );

                case 'et_reset_pass_mail':
                        return $this->set_reset_pass_mail ( $new_value, true );

                case 'et_approve_mail':
                        return $this->set_approve_mail ( $new_value, true );

                case 'et_reject_mail':
                        return $this->set_reject_mail ( $new_value, true );

                case 'et_archive_mail':
                        return $this->set_archive_mail ( $new_value, true );

                case 'et_cash_notification_mail' :
                        return $this->set_cash_notification_mail ($new_value, true );

                case 'et_message_to_seller_mail' :
                        return $this->set_message_to_seller_mail ($new_value, true );

                case 'et_receipt_mail' :
                        return $this->set_receipt_mail ($new_value, true );
                break;

                default:
                        return false;
            }
        }

        public function set_cash_notification_mail ($new_value , $default ) {
                if($default) {
                        $new_value        =                __("<p>Dear [display_name],</p><p>[cash_message]</p><p>Sincerely,<br/> [blogname].</p>", ET_DOMAIN);
                }
                $this->update_option('et_cash_notification_mail', $new_value);
                return $new_value;
        }
        public function get_cash_notification_mail () {
                $default        =        __("<p>Dear [display_name],</p><p>[cash_message]</p><p>Sincerely,<br /> [blogname].</p>", ET_DOMAIN);
                return stripslashes( $this->get_option('et_cash_notification_mail', $default) );
        }

        public function get_register_mail ( ) {
                //$default        =        __('<p>Hello [display_name],</p><p>You have just registered an account;in [blogname] successfully.</p><p>Here is your account information:</p><ol><li>Username: [user_login]</li><li>Email: [user_email]</li></ol><p>Thanks and welcome you to [blogname].</p>', ET_DOMAIN);
                $default        =        __("<p>Hello [display_name],</p><p>You have successfully registered an account with&nbsp;&nbsp;[blogname].&nbsp;Here is your account information:</p><ol><li>Username: [user_login]</li><li>Email: [user_email]</li></ol><p>Thank you and welcome to [blogname].</p>", ET_DOMAIN);
                return stripslashes($this->get_option('et_register_mail', $default) );
        }

        public function set_register_mail ( $new_value, $default ) {
                if($default) {
                        $new_value        =        __("<p>Hello [display_name],</p><p>You have successfully registered an account with&nbsp;&nbsp;[blogname].&nbsp;Here is your account information:</p><ol><li>Username: [user_login]</li><li>Email: [user_email]</li></ol><p>Thank you and welcome to [blogname].</p>", ET_DOMAIN);
                }
                $this->update_option('et_register_mail', $new_value);
                return $new_value;
        }

        public function get_forgot_pass_mail ( ) {
                //$default        =        __('<p>Hello [display_name],</p><p>You have just sent a request for recovering your password in [blogname]. if this was not your request, please ignore this email address, otherwise, please click on the following URL to create your new password:</p><p>[activate_url]</p><p>Regards,<br />[blogname]</p>', ET_DOMAIN );
                $default        =        __("<p>Hello [display_name],</p><p> You have just sent a request to recover the password associated with your account in [blogname].</p><p> If you did not make this request, please ignore this email; otherwise, click the link below to create your new password:. </p><p>[activate_url] </p><p>Regards,</p><p>[blogname]</p>", ET_DOMAIN);
                return stripslashes( $this->get_option('et_forgot_pass_mail', $default) );
        }

        public function set_forgot_pass_mail ( $new_value, $default ) {
                if($default) {
                        $new_value     = __("<p>Hello [display_name],</p><p> You have just sent a request to recover the password associated with your account in [blogname].</p><p> If you did not make this request, please ignore this email; otherwise, click the link below to create your new password:. </p><p>[activate_url] </p><p>Regards,</p><p>[blogname]</p>", ET_DOMAIN);
                }
                $this->update_option('et_forgot_pass_mail', $new_value);
                return $new_value;
        }

        public function get_reset_pass_mail ( ) {
                //$default        =        __('<p>Hello [display_name],</p><p>You have just changed your password successfully. You can now log into our website at [site_url].</p><p><span>Sincerely,<br /></span>[blogname]</p>', ET_DOMAIN );
                $default        =        __("<p>Hello [display_name],</p><p>You have successfully changed your password. Click this link&nbsp;[site_url] to login to your [blogname]'s account.</p><p>Sincerely,<br />[blogname]</p>", ET_DOMAIN);
                return stripslashes(( $this->get_option('et_reset_pass_mail', $default) ));
        }

        public function set_reset_pass_mail ( $new_value, $default ) {
                if($default) {
                    $new_value        =        __("<p>Hello [display_name],</p><p>You have successfully changed your password. Click this link&nbsp;[site_url] to login to your [blogname]'s account.</p><p>Sincerely,<br />[blogname]</p>", ET_DOMAIN);
                }
                $this->update_option('et_reset_pass_mail', $new_value);
                return $new_value;
        }
         public function get_message_to_seller_mail ( ) {
                //$default        =        __('<p>Hello [display_name],</p><p>You have just changed your password successfully. You can now log into our website at [site_url].</p><p><span>Sincerely,<br /></span>[blogname]</p>', ET_DOMAIN );
                $default        =   __("<p>Hello [display_name],</p><p>You have a new message. Below are the sender's information:</p>First name : [first_name]<br />Last Name : [last_name] <br />Email :  [email]<br />Phone :  [phone]<br />Ad link :  [ad_url]<br />Message : [message]<p>Sincerely,<br />[blogname]</p> ", ET_DOMAIN);
                return stripslashes(( $this->get_option('et_message_to_seller_mail', $default) ));
        }

        public function set_message_to_seller_mail ( $new_value, $default ) {
              if($default) {
                    $new_value  =   __("<p>Hello [display_name],</p><p>You have a new message. Below are the sender's information:</p>First name : [first_name]<br />Last Name : [last_name] <br />Email :  [email]<br />Phone :  [phone]<br />Ad link :  [ad_url]<br /><br />Message : [message] <p>Sincerely,<br />[blogname]</p>", ET_DOMAIN); 
                }
                $this->update_option('et_message_to_seller_mail', $new_value);
                return $new_value;
        }

        /**
         * get apply job mail template, this mail will be send to companies when
         * a job seeker apply their jobs
         */
        public function get_receipt_mail ( ) {
            $default   =     __("Dear [display_name],<br />Thank you for your payment.<br />Here are the details of your transaction:<br />Ad detail:[ad]<br /><strong> Customer info</strong>:<br /> [display_name] <br /> Phone: [seller_phone]. <br /> Adress :[seller_address].<br /> Email: [seller_email]. <br /><strong> Invoice</strong> <br />Invoice No: [invoice_id]. <br />Date: [date]. <br /> Payment: [payment] <br /> Coupon code: [coupon_code] <br /> Total: [total] [currency]<br /> </p><p>Sincerely,<br />[blogname]", ET_DOMAIN);
            return stripslashes(( $this->get_option('et_receipt_mail', $default) ));
        }
        /*
         * set mail template after pay success.
         * @param $new_value : string new mail template value
         * @param $default : bool if true, mail template will be reset to default
         */
        public function set_receipt_mail ( $new_value, $default ) {
            if($default) {
               $new_value    =     __("Dear [display_name],<br />Thank you for your payment.<br />Here are the details of your transaction:<br />Ad detail:[ad]<br /><strong> Customer info</strong>:<br /> [display_name] <br /> Phone: [seller_phone]. <br /> Adress :[seller_address].<br /> Email: [seller_email]. <br /><strong> Invoice</strong> <br />Invoice No: [invoice_id]. <br />Date: [date]. <br /> Payment: [payment] <br /> Coupon code: [coupon_code] <br /> Total: [total] [currency]<br /> </p><p>Sincerely,<br />[blogname]", ET_DOMAIN);
            }
            $this->update_option('et_receipt_mail', $new_value);
            return $new_value;
        }

        public function get_approve_mail ( ) {
                $default        =        __('Dear [display_name],</p><p>Your ad [title] posted in [blogname] has been approved.</p><p>You can follow this link: [link] to view your ad.</p><p>Sincerely,<br />[blogname]</p>', ET_DOMAIN);
                return stripslashes(( $this->get_option('et_approve_mail', $default) ));
        }
        /**
         * set approve mail template
         * @param $new_value : string new mail template value
         * @param $default : bool if true, mail template will be reset to default
         */
        public function set_approve_mail ( $new_value, $default ) {
                if($default) {
                        $new_value        =        __('<p>Dear [display_name],</p><p>Your ad [title] posted in [blogname] has been approved.</p><p>You can follow this link: [link] to view your ad.</p><p>Sincerely,<br />[blogname]</p>', ET_DOMAIN);
                }
                $this->update_option('et_approve_mail', $new_value);
                return $new_value;
        }
        /**
         * get archive job mail template, this mail template will be sent to
         * companies when their jobs expired or archived
         */
        public function get_archive_mail ( ) {
                $default        =        __('<p>Dear [display_name],</p><p>Your ad: [title] in [blogname] has been archived due to expiration or manual administrative action.</p><p>If you want to continue displaying this ad in our website, please go to your dashboard at [dashboard] to renew your ad.</p><p>Sincerely,<br />[blogname]</p>', ET_DOMAIN);
                return stripslashes(( $this->get_option('et_archive_mail', $default)));
        }
        /**
         * set archive job mail template
         * @param $new_value : string new mail template value
         * @param $default : bool if true, mail template will be reset to default
         */
        public function set_archive_mail ( $new_value, $default ) {
                if($default) {
                        $new_value        =        __('<p>Dear [display_name],</p><p>Your ad: [title] in [blogname] has been archived due to expiration or manual administrative action.</p><p>If you want to continue displaying this ad in our website, please go to your dashboard at [dashboard] to renew your ad.</p><p>Sincerely,<br />[blogname]</p>', ET_DOMAIN);
                }
                $this->update_option('et_archive_mail', $new_value);
                return $new_value;
        }
        /**
         * get reject job mail template, this mail template will be sent to companies
         * when their job rejected by site
         */
        public function get_reject_mail ( ) {
                $default        =        __('<p>Dear [display_name],</p><p>Your ad [title] posted in [blogname] has been rejected. Noted reason: [reason]</p><p>Please contact the administrators via [admin_email] for more information, or go to your dashboard at [dashboard] to edit your ad and post it again.</p><p>Sincerely,<br />[blogname]</p>', ET_DOMAIN);
                return stripslashes(( $this->get_option('et_reject_mail', $default)));
        }
        /**
         * set reject mail template
         * @param $new_value : string new mail template value
         * @param $default : bool if true, mail template will be reset to default
         */
        public function set_reject_mail ( $new_value, $default ) {
            if($default) {
                    $new_value        =        __('<p>Dear [display_name],</p><p>Your ad [title] posted in [blogname] has been rejected. Noted reason: [reason]</p><p>Please contact the administrators via [admin_email] for more information, or go to your dashboard at [dashboard] to edit your ad and post it again.</p><p>Sincerely,<br />[blogname]</p>', ET_DOMAIN);
            }
            $this->update_option('et_reject_mail', $new_value);
            return $new_value;
        }
        // moved setting.php to here.
        public static function get_auto_email($type = ''){
            $options         = get_option('et_auto_email', array('apply' => 1, 'approve' => 1, 'remind' => 1, 'archive' => 1, 'reject' => 1, 'cash_notice' => 1, 'receipt' => 1 ));
            if(!isset($options['cash_notice'])) $options['cash_notice']        =        1;
            return $options[$type];
        }


        public static function get_auto_emails(){
            $options         = get_option('et_auto_email', array('apply' => 1, 'approve' => 1, 'remind' => 1, 'archive' => 1, 'reject' => 1, 'cash_notice' => 1, 'receipt' =>  1));
            if(!isset($options['cash_notice'])) $options['cash_notice']        =        1;
            return $options;
        }
        /**
         * set an automatically sending feature for a email template
         */
        public static function et_set_auto_email($type, $value = false){
            $options         = get_option('et_auto_email', array('apply' => 1, 'approve' => 1, 'remind' => 1, 'archive' => 1, 'reject' => 1, 'cash_notice' => 1, 'receipt' => 1));
            $key                 = $type;
            if ($value !== false)
                    $options[$key] = $value;
            else {
                    if (isset($options[$key]) && $options[$key] == 1)
                            $options[$key] = 0;
                    else
                            $options[$key] = 1;
            }
            return update_option( 'et_auto_email', $options );
        }

}

