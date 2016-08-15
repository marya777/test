<?php

/**
 * class control CE mailing
 */
class CE_Mailing extends ET_CEMailTemplate
{
    
    public static $instance = null;
    public static $options = null;
    
    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new CE_Mailing();
            self::$options = self::get_auto_emails();
        }
        return self::$instance;
    }
    
    public static function header_email() {
        $blog_name = get_option('blogname');
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers.= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers.= "From: " . $blog_name . " < " . get_option('admin_email') . "> \r\n";
        $headers = apply_filters('filter_header_email_', $headers);
        return $headers;
    }
    
    public static function footer_email() {
    }
    
    /**
     * function send contact mail to seller
     */
    public static function contact_seller($request) {
        if (!isset($request['seller_id'])) return false;
        $instance = self::get_instance();
        $blog_name = get_option('blogname');
        if (function_exists('icl_get_home_url')) $blog_name = icl_get_home_url();
        
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers.= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers.= "From: " . $blog_name . " < " . $request['email_user'] . "> \r\n";
        $headers.= "Reply-To : " . $blog_name . " < " . $request['email_user'] . "> \r\n";;
        $headers = apply_filters('ce_mailing_contact_header', $headers);
        
        $subject = apply_filters('ce_mailing_contact_subject', sprintf(__(' The message sent by user from %s', ET_DOMAIN) , get_option('blogname')));
        
        $seller_id = $request['seller_id'];
        
        $email_user = isset($request['email_user']) ? $request['email_user'] : '';
        
        $user = get_userdata($seller_id);
        
        $mailt_opt = new ET_CEMailTemplate();
        $content = $mailt_opt->get_message_to_seller_mail();
        
        $content = str_ireplace('[display_name]', $user->display_name, $content);
        $content = str_ireplace('[first_name]', $request['first_name'], $content);
        $content = str_ireplace('[last_name]', $request['last_name'], $content);
        $content = str_ireplace('[email]', $request['email_user'], $content);
        $content = str_ireplace('[message]', $request['message'], $content);
        $content = str_ireplace('[phone]', $request['phone'], $content);
        $ad_id = isset($request['ad_id']) ? $request['ad_id'] : false;
        if ($ad_id) {
            $content = str_ireplace('[ad_url]', '<a href="' . get_permalink($ad_id) . '">' . get_the_title($ad_id) . '</a>', $content);
            $content = apply_filters('ce_fillter_mesasge_to_seller', $content, $ad_id);
        }
        
        return $instance->wp_mail($user->user_email, $subject, $content, $headers, false);
    }
    
    public static function mail_reject($ad_id, $reason) {
        
        $instance = self::get_instance();
        
        if (!self::$options['reject']) return false;
        
        $header = apply_filters('ce_mailing_reject_header', CE_Mailing::header_email());
        $subject = apply_filters('ce_mailing_reject_subject', __(' Your ad has rejected', ET_DOMAIN));
        
        $post = get_post($ad_id);
        
        $content_mail = $instance->get_reject_mail();
        $content_mail = str_ireplace('[reason]', $reason, $content_mail);
        
        $mail_author = get_the_author_meta('user_email', $post->post_author);
        
        return $instance->wp_mail($mail_author, $subject, $content_mail, $header, array(
            'user_id' => $post->post_author,
            'post' => $post
        ));
    }
    
    public static function ad_change_status($new_status, $old_status, $post) {
        if ($new_status == $old_status) return false;
        if (!in_array($new_status, array(
            'publish',
            'archive'
        ))) return false;
        
        $instance = self::get_instance();
        
        /**
         * disable approve ad email
         */
        if ($new_status == 'publish' && !self::$options['approve']) return false;
        
        /**
         * disable archive ad mail
         */
        if ($new_status == 'archive' && !self::$options['archive']) return false;
        
        switch ($new_status) {
            case 'publish':
                $headers = apply_filters('ce_mailing_approve_header', CE_Mailing::header_email());
                $content_mail = $instance->get_approve_mail();
                $subject = apply_filters('ce_mailing_approve_subject', __('Your ad has been approved', ET_DOMAIN));
                break;

            case 'archive':
                $headers = apply_filters('ce_mailing_archive_header', CE_Mailing::header_email());
                $content_mail = $instance->get_archive_mail();
                $subject = apply_filters('ce_mailing_archive_subject', __('Your ad has been archived', ET_DOMAIN));
                break;

            default:
                return false;
        }
        
        $author = $post->post_author;
        $user = get_userdata($author);
        
        return $instance->wp_mail($user->user_email, $subject, $content_mail, $headers, array(
            'user_id' => $author,
            'post' => $post
        ));
    }
    
    /*
    /* sent to seller after them register successful.
    */
    public static function register_seller($seller_id) {
        
        $header = apply_filters('ce_mailing_register_header', CE_Mailing::header_email());
        $user = get_userdata($seller_id);
        $seller_email = $user->user_email;
        $instance = self::get_instance();
        $register_mail = apply_filters("ce_mailing_register_content", $instance->get_register_mail() , $seller_id);
        
        $subject = apply_filters('ce_mailing_register_subject', sprintf(__("Congratulations! You have successfully registered to %s.", ET_DOMAIN) , get_option('blogname')));
        
        return $instance->wp_mail($seller_email, $subject, $register_mail, $header, array(
            'user_id' => $seller_id
        ));
    }
    
    /**
     * send receipt when submit a payment
     * send successful payment email
     */
    public static function send_receipt($user_id, $order) {
        
        $instance = self::get_instance();
        
        if (!self::$options['receipt']) return false;
        
        $headers = apply_filters('ce_mailing_receipt_header', CE_Mailing::header_email());
        $subject = apply_filters('ce_mailing_receipt_subject', __('Thank you for your payment!', ET_DOMAIN));
        
        $user = get_userdata($user_id);
        
        $mailt_opt = new ET_CEMailTemplate();
        $content = $mailt_opt->get_receipt_mail();
        
        $products = $order['products'];
        
        $coupon_code = $order['coupon_code'];
        
        if (empty($coupon_code)) $coupon_code = __('No coupon', ET_DOMAIN);
        
        $args = array_pop($products);
        
        $ad_id = $args['ID'];
        
        $ad_url = '<a href="' . get_permalink($ad_id) . '">' . get_the_title($ad_id) . '</a>';
        
        $seller = ET_Seller::convert($user);
        
        $order_id = get_post_meta($ad_id, 'et_ad_order', true);
        
        $content = str_ireplace('[ad]', $ad_url, $content);
        $content = str_ireplace('[display_name]', $user->display_name, $content);
        $content = str_ireplace('[seller_address]', $seller->et_address, $content);
        $content = str_ireplace('[seller_phone]', $seller->et_phone, $content);
        $content = str_ireplace('[seller_email]', $user->user_email, $content);
        
        $content = str_ireplace('[coupon_code]', $coupon_code, $content);
        $content = str_ireplace('[invoice_id]', $order_id, $content);
        $content = str_ireplace('[payment]', $order['payment'], $content);
        $content = str_ireplace('[date]', date(get_option('date_format') , time()) , $content);
        $content = str_ireplace('[total]', $order['total'], $content);
        $content = str_ireplace('[currency]', $order['currency'], $content);
        $content = str_ireplace('[blogname]', get_option('blogname') , $content);
        
        return $instance->wp_mail($user->user_email, $subject, $content, $headers, false);
    }
    
    public static function send_cash_message($cash_message, $ad) {
        global $current_user, $user_ID;
        
        $mail_opt = new ET_CEMailTemplate();
        
        $instance = self::get_instance();
        
        $auto_email = ET_CEMailTemplate::get_auto_emails();
        
        if ($auto_email['cash_notice']) {
            
            // get cash notification mail template and filter placeholder
            
            $message = $mail_opt->get_cash_notification_mail();
            $message = str_ireplace('[cash_message]', $cash_message, $message);
            
            // $message    =   et_filter_authentication_placeholder ($message, $user_ID);
            
            $filter = array(
                'user_id' => $user_ID
            );
            
            if ($ad) {
                $filter['post'] = $ad;
            }
            
            // sent cash notification to user
            return $instance->wp_mail($current_user->data->user_email, __("Cash payment notification", ET_DOMAIN) , $message, '', $filter);
        }
    }
    
    /*
    /* sent mail for user forgot password
    */
    public static function forgot_password($emai) {
        
        // check email exists
        $status = email_exists($email);
        if (!$status) return array(
            'success' => false,
            'msg' => __("The email don't exists in system.", ET_DOMAIN)
        );
        
        // generate password
        
        $mail_opt = new ET_CEMailTemplate();
        $forgot_pass_mail = $mailt_opt->get_forgot_pass_mail();
    }
    
    /**
     * send mail function
     */
    public function wp_mail($to, $subject, $content, $header = '', $filter = array()) {
        
        if (isset($filter['user_id'])) {
            $content = $this->et_filter_authentication_placeholder($content, $filter['user_id']);
        }
        
        if (isset($filter['post'])) {
             // filter post placeholder
            $content = $this->et_filter_ad_placeholder($content, $filter['post']);
        }
        
        $content = $this->get_mail_header() . $content . $this->get_mail_footer();
        
        return wp_mail($to, $subject, $content, $header);
    }
    
    /**
     * return mail header template
     */
    function get_mail_header() {
        
        $mail_header = apply_filters('et_get_mail_header', '');
        if ($mail_header != '') return $mail_header;
        
        $size = apply_filters('je_mail_logo_size', array(
            120,
            50
        ));
        $options = new CE_Options();
        $logo_url = $options->get_website_logo($size);
        
        $customize = $options->get_customization();
        
        $mail_header = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
                        <html>
                        <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
                        <meta name="format-detection" content="telephone=no">
                        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />
                        <style  type="text/css">

                            body {-webkit-text-size-adjust:none; -ms-text-size-adjust:none;}
                            body {margin:0; padding:0;}
                            /* Resolves webkit padding issue. */
                            body, #body_style {width:100% !important;}
                            #full_width{
                                width:100%;
                            }
                            table {border-spacing:0;}
                            img {display:block; border:none; outline:none; text-decoration:none;}

                            /* Resolves the Outlook 2007, 2010, and Gmail td padding issue. */
                            table td {border-collapse:collapse;}

                            @media only  screen and (max-width: 550px) {

                                td[class="logo"],
                                td.logo {
                                    width: 100%;
                                    height: auto !important;
                                    display:table;
                                    padding-bottom:0px !important;

                                }
                                td[class="blog-info"],
                                td.blog-info {
                                    width: 100%;
                                    clear:both;
                                    display:table;
                                }
                                *[class="logo"] {float: none !important;width: 100% !important;}
                                *[align="center"] {float: none !important;width: 100% !important;}
                            }
                        </style>
                        </head>
                        <body style="font-family: Arial, sans-serif;font-size: 0.9em;margin: 0; padding: 0; color: #222222;">
                            <table width="100%" cellspacing="0" cellpadding="0">
                            <tr style="background: ' . $customize['header'] . '; height: 63px; vertical-align: middle;">
                                <td class="logo" style="padding: 10px 5px 10px 20px; width: 20%;">
                                    <img style="max-height: 50px; min-width:150px; max-width:100%; " src="' . $logo_url[0] . '" alt="' . get_option('blogname') . '">
                                </td>
                                <td class="blog-info" style="padding: 10px 20px 10px 5px; min-width:300px;">
                                    <span style="text-shadow: 0 0 1px #151515; color: #b0b0b0;">' . get_option('blogdescription') . '</span>
                                </td>
                            </tr>
                            <tr><td colspan="2" style="height: 5px; width:100%; background-color: ' . $customize['background'] . ';"></td></tr>
                            <tr>
                                <td colspan="2" style="background: #ffffff; width:100%; color: #222222; line-height: 18px; padding: 10px 20px;">';
        return $mail_header;
    }
    
    function get_mail_footer() {
        
        $mail_footer = apply_filters('et_get_mail_footer', '');
        if ($mail_footer != '') return $mail_footer;
        
        $info = apply_filters('et_mail_footer_contact_info', get_option('blogname') . ' <br>
                        ' . get_option('admin_email') . ' <br />');
        $options = new CE_Options();
        $customize = $options->get_customization();
        $copyright = apply_filters('get_copyright', '');
        
        $mail_footer = '</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="background: ' . $customize['background'] . '; padding: 10px 20px; color: #666;">
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="vertical-align: top; text-align: left; width: 50%;">' . $copyright . '</td>
                                        <td style="text-align: right; width: 50%;">' . $info . '</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        </table>

                    </body>
                    </html>';
        return $mail_footer;
    }
    
    function et_filter_authentication_placeholder($content, $user_id) {
        $user = new WP_User($user_id);
        
        $content = str_ireplace('[user_login]', $user->user_login, $content);
        $content = str_ireplace('[user_name]', $user->user_login, $content);
        $content = str_ireplace('[user_nicename]', ucfirst($user->user_nicename) , $content);
        $content = str_ireplace('[user_email]', $user->user_email, $content);
        $content = str_ireplace('[display_name]', ucfirst($user->display_name) , $content);
        $content = str_ireplace('[seller]', ucfirst($user->display_name) , $content);
        $content = str_ireplace('[dashboard]', et_get_page_link('account-listing') , $content);
        $content = str_ireplace('[blogname]', get_option('blogname') , $content);
        
        $content = apply_filters('et_filter_auth_email', $content, $user_id);
        return $content;
    }
    
    function et_filter_ad_placeholder($content, $ad_id) {
        $ad = get_post($ad_id);
        $content = str_ireplace('[title]', apply_filters('the_title', $ad->post_title) , $content);
        $content = str_ireplace('[desc]', apply_filters('the_content', $ad->post_content) , $content);
        $content = str_ireplace('[excerpt]', apply_filters('the_excerpt', $ad->post_excerpt) , $content);
        $content = str_ireplace('[link]', get_permalink($ad_id) , $content);
        $content = str_ireplace('[dashboard]', et_get_page_link('account-listing') , $content);
        
        $site_url = home_url();
        if (function_exists('icl_get_home_url')) $site_url = icl_get_home_url();
        
        $content = str_ireplace('[site_url]', $site_url, $content);
        $content = str_ireplace('[blogname]', get_bloginfo('name') , $content);
        $content = str_ireplace('[admin_email]', get_option('admin_email') , $content);
        
        $content = apply_filters('et_filter_ad_email', $content, $ad_id);
        
        return $content;
    }
}
