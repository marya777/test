<?php
/**
* Author: Dakachi
* Date created: 10-03-2014
* Description: Google recaptcha class
* GOOGLE CAPTCHA TUTORIAL
* To use google captcha, you need to have 2 variables public key and private key
* Register at https://www.google.com/recaptcha/admin/create
* //======================================================
* To generate Google recaptcha box:
* //========================================
* $GCaptcha = DCGoogleCaptcha::getInstance();
* $publicKey = ''; 
* $GCaptcha->generateCaptchaBox($publicKey)
* //======================================================
* To check words:
* //========================================
* $GCaptcha = DCGoogleCaptcha::getInstance();
* $privateKey = ''; 
* if ($GCaptcha->checkCaptcha($privateKey)) {
*   //Correct result
* }
* else {
*   //Incorrect result
* }
* //======================================================
*/

class ET_GoogleCaptcha
{
    private static $_instance;
    public function __construct(){ }
    private function __clone() {}
    
    public static function getInstance(){
        if ( ! self::$_instance instanceof ET_GoogleCaptcha )
            self::$_instance = new ET_GoogleCaptcha();
        return self::$_instance;
    }
    
    /**
    * generate captcha box to check security
    * 
    * @param mixed $publicKey
    */
    public function generateCaptchaBox($customize = false)
    {
        $key        =   $this->get_api();
        $publicKey =   $key['public_key'];

        echo "<div style='width: 100%'>" . recaptcha_get_html( $publicKey, null, false, $customize) . '</div>';
    }

    /**
    * check words typed correctly
    * 
    * @param mixed $privateKey
    */
    public function checkCaptcha($challenge , $response )
    {
        $key        =   $this->get_api();
        $privateKey =   $key['private_key'];

        $bResult = false;
        
        if ( $response ) {
            $response = recaptcha_check_answer($privateKey, $_SERVER['REMOTE_ADDR'], $challenge, $response);
            
            if ($response->is_valid) {
                $bResult = true;
            }
        }
        
        return $bResult;
    }

    public static function get_api () {
        return get_option('et_google_api_key', array (
                                    'private_key' =>  /*'6LdmzO8SAAAAALkfFCb7Twppu4axyXtjm4maJ82Y'*/ '' , 
                                    'public_key' =>  '' /*'6LdmzO8SAAAAAOQgKCsol68zZ4ob8W4AFxss8USn'*/ )
                );
    }

    public static function set_api ( $api ) {
        update_option( 'et_google_api_key' , $api );
    }

}
add_action( 'init' , 'et_init_setup_captcha' );
function et_init_setup_captcha () {
    $ce_option = new CE_Options();
    $useCaptcha =   $ce_option->use_captcha();
    if($useCaptcha ) {
        // add captcha to register form
        // add_filter ('wp_authenticate_user','filter_authenticate');  
        //add_action( 'login_form', 'render_captcha');
        //add_action( 'login_redirect','login_wp_default_redirect');
        
        // check login for wordpress default
        // add_action( 'login_redirect' ,'login_wp_default_redirect');
        add_action( 'et_add_register_field', 'render_captcha_register' );    
        //add_action( 'et_add_login_field', 'render_captcha_login' );
        add_action( 'wp_head','add_style_google_captcha' );
        //add_action( 'wp_footer','captcha_google_template' );
        // renser captcha 
        add_action( 'et_insert_captcha_post_ad', 'render_captcha_form_post_ad' );
        add_action( 'et_insert_captcha_post_ad_loged', 'render_captcha_form_post_ad_logged' );
        
        add_action( 'comment_form_after_fields','render_captcha_comment' );
        add_action( 'comment_form_logged_in_after','comment_form_logged_in_after' );
        
        //add_filter( 'preprocess_comment','ce_preprocess_comment' );

        // for send to seller mesage form.
        add_action( 'ce_after_send_seller_message_form','send_message_seller_captcha' );
        add_action( 'wp_footer', 'ce_captcha_add_script');
        add_action( 'wp_ajax_check_captcha_comment', 'check_captcha_comment');
        add_action( 'wp_ajax_nopriv_check_captcha_comment', 'check_captcha_comment');
        
       // add_action( 'wp_footer', 'append_html_catpcha', 100);

        /* 
        * mobile verion 
        * @verion 2.9.5
        */
        add_action('ce_mobile_after_reg_form','register_mobile_captcha');

    }
}

function check_captcha_login_wp(){

}
function ce_preprocess_comment($comment){
    
    if( get_post_type($_REQUEST['comment_post_ID']) != CE_AD_POSTTYPE )
        return $comment;
    
    $ce_option      = new CE_Options();
    $useCaptcha     = $ce_option->use_captcha();    
    $cm_ad          = $ce_option->get_option('et_comment_ad');
    $cm_ad_captcha  = $ce_option->get_option('et_comment_ad_captcha');

    if( isset( $_REQUEST['recaptcha_challenge_field'] ) ) {
        $captcha    =   ET_GoogleCaptcha::getInstance();
        if( !$captcha->checkCaptcha( $_REQUEST['recaptcha_challenge_field'] , $_REQUEST['recaptcha_response_field']  ) ) {
            wp_die( __( 'Error: You have entered an incorrect CAPTCHA value. Click the BACK button on your browser, and try again.', ET_DOMAIN ) );          
        }
    }
    return $comment;
}

function render_captcha_comment($html){
    $ce_option      = new CE_Options();
    $cm_ad_captcha  = $ce_option->get_option('et_comment_ad_captcha');
   
    if( is_singular(CE_AD_POSTTYPE ) && $cm_ad_captcha ){
        $captcha    =   ET_GoogleCaptcha::getInstance();
        echo  "<div class='form-item' id='reCaptcha' >";
        $captcha->generateCaptchaBox(true);
        echo "</div>"; 
    }

}
function comment_form_logged_in_after(){
    $ce_option      = new CE_Options();
    $cm_ad_captcha  = $ce_option->get_option('et_comment_ad_captcha');
   
    if( is_singular(CE_AD_POSTTYPE ) && $cm_ad_captcha && !current_user_can( 'manage_options' ) ){
        $captcha    =   ET_GoogleCaptcha::getInstance();
        echo  "<div class='form-item' id='reCaptcha' >";
        $captcha->generateCaptchaBox(true);
        echo "</div>"; 
    }
}

function login_wp_default_redirect(){
 
    if( isset($_REQUEST['recaptcha_challenge_field']) ){

        $ce_option = new CE_Options();
        $useCaptcha =   $ce_option->use_captcha();      
        $captcha    =   ET_GoogleCaptcha::getInstance();
        $check      =   $captcha->checkCaptcha( $_REQUEST['recaptcha_challenge_field'] , $_REQUEST['recaptcha_response_field'] );
        
        if(!$check){
            wp_clear_auth_cookie();           
            wp_die( __( 'Error: You have entered an incorrect CAPTCHA value. Click the BACK button on your browser, and try again.',ET_DOMAIN ) );

        }
        return 'wp-admin';
        
    }
}


function render_captcha_register(){
    if(is_user_logged_in())
        return '';
    $option         = new CE_Options;
    $captcha        = ET_GoogleCaptcha::getInstance();

    if(  !is_singular(CE_AD_POSTTYPE ) && ! is_singular('post' ) && !is_page_template( 'page-post-ad.php' ) )  {
        echo  "<div class='form-item' id='reCaptcha' >";
        $captcha->generateCaptchaBox();
        echo "</div>";
    } else {
        echo '<div class="captcha-append" id="reCaptchaLogin">';
        echo '</div>';
    }
   
}
function render_captcha(){
    $captcha    =   ET_GoogleCaptcha::getInstance();
    echo  "<div class='form-item' id='reCaptcha' >";
    $captcha->generateCaptchaBox();
    echo "</div>";   
}
function render_captcha_form_post_ad(){
    if(is_user_logged_in())
       return '';
    $captcha    =   ET_GoogleCaptcha::getInstance();
    echo  "<div class='form-item' id='reCaptcha' >";
    $captcha->generateCaptchaBox(true);    
    echo "</div>";   
}

function render_captcha_form_post_ad_logged(){
    if( !is_user_logged_in() )
        return '';
    else {
        $captcha    =   ET_GoogleCaptcha::getInstance();
        echo  "<div class='form-item' id='reCaptcha' >";
        $captcha->generateCaptchaBox(true);    
        echo "</div>";   
    }
}
function send_message_seller_captcha(){
    $flag =  apply_filters( 'add_captcha_seller_mesage_form', true );
    
    if(!$flag)
        return '';

    if(current_user_can('manage_options' ))
        return '';

    $ce_option      = new CE_Options();
    $useCaptcha     = $ce_option->use_captcha();    
    $cm_ad          = $ce_option->get_option('et_comment_ad');
    $cm_ad_captcha  = $ce_option->get_option('et_comment_ad_captcha');
    
    if ( get_option('comment_registration') && ! is_user_logged_in() ){
        $captcha    =   ET_GoogleCaptcha::getInstance();
        echo  "<div class='form-item' id='reCaptcha' >";
        $captcha->generateCaptchaBox(true);    
        echo "</div>";   
    } else if($cm_ad && $cm_ad_captcha && !is_user_logged_in() ){ ?>

        <div class="control-group form-group wrap-captcha">
            <div class="captcha-append" id="reCaptchaMessage">
            </div>
        </div>
        <?php 
       
    } else {  
        $captcha    =   ET_GoogleCaptcha::getInstance();
        echo  "<div class='form-item' id='reCaptcha' >";
        $captcha->generateCaptchaBox();    
        echo "</div>";   
    }
}

function ce_captcha_add_script(){
    if(is_singular(CE_AD_POSTTYPE) && !current_user_can( 'manage_options' )){
    ?>
    <script type="text/javascript">
    (function($){
        $(document).ready(function(){
            $("input#recaptcha_response_field").focusout(function(){
                $("form span.msg-custom").html('');
            });

            $("form.comment-form input#submit").click(function(event){
                var $target     = $(event.currentTarget).closest('form'),
                    loading     =   new CE.Views.LoadingButton({el :$target.find('input#submit') }),
                    recaptcha   = $target.find("input[name = 'recaptcha_challenge_field']").val(),
                    response    = $target.find("input[name = 'recaptcha_response_field']").val();

                $target.find("span.msg-custom").html('');
               
                if(!$target.valid())
                    return false;
                var check = $.ajax({
                        data : { recaptcha : recaptcha, response : response , action : 'check_captcha_comment'},
                        url: et_globals.ajaxURL,
                        beforeSend: function(event){
                            
                            loading.loading();
                        },
                        success : function(resp){
                            loading.finish();
                        },
                        async: false
                    });

                var result = jQuery.parseJSON(check.responseText);
                if(!result.success){
                    $target.find("input#recaptcha_response_field").addClass('error');
                    $target.find("span.msg-custom").html('<label> <?php _e('Invalid captcha.',ET_DOMAIN);?> </label>');
                    $target.find("span.msg-custom").show();
                    $(".btn-reload").trigger('click');

                }
                return result.success;
            })
        });
       
       

    })(jQuery);
    </script>
    <?php 
    }
}
/*
* check captcha in comment form.
*/
function check_captcha_comment(){

    if(current_user_can( 'manage_options' ))
        wp_send_json( array('success' => true) );

    $captcha    =  ET_GoogleCaptcha::getInstance();
    $recaptcha  = $_REQUEST['recaptcha'];
    $response   = $_REQUEST['response'];
    $result     = $captcha->checkCaptcha( $recaptcha, $response);
    wp_send_json( array('success' => $result) );
}

/**
* render captcha into form register user in mobile
* @version 2.9.5
*/
function register_mobile_captcha(){
    $ce_option      = new CE_Options();
    $useCaptcha     = $ce_option->use_captcha();

    if( $useCaptcha ){ ?>
        <div data-role="fieldcontain" class="post-new-classified clearfix register-captcha"> 
            <?php render_captcha(); ?>
        </div>
        <?php
   }
}

function append_html_catpcha(){
    if( is_singular(CE_AD_POSTTYPE) ){
        ?>
        <script type="text/javascript">
        (function($) {
            $(document).ready(function(){
                var html = $("#reCaptcha").html();
                $(".captcha-append").html(html);
            });

       })(jQuery);
        </script>
        <?php
    }
}

/**
 * The reCAPTCHA server URL's
 */
define("RECAPTCHA_API_SERVER", "http://www.google.com/recaptcha/api");
define("RECAPTCHA_API_SECURE_SERVER", "https://www.google.com/recaptcha/api");
define("RECAPTCHA_VERIFY_SERVER", "www.google.com");

/**
 * Encodes the given data into a query string format
 * @param $data - array of string elements to be encoded
 * @return string - encoded request
 */
function _recaptcha_qsencode ($data) {
        $req = "";
        foreach ( $data as $key => $value )
                $req .= $key . '=' . urlencode( stripslashes($value) ) . '&';

        // Cut the last '&'
        $req=substr($req,0,strlen($req)-1);
        return $req;
}



/**
 * Submits an HTTP POST to a reCAPTCHA server
 * @param string $host
 * @param string $path
 * @param array $data
 * @param int port
 * @return array response
 */
function _recaptcha_http_post($host, $path, $data, $port = 80) {

        $req = _recaptcha_qsencode ($data);

        $http_request  = "POST $path HTTP/1.0\r\n";
        $http_request .= "Host: $host\r\n";
        $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
        $http_request .= "Content-Length: " . strlen($req) . "\r\n";
        $http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
        $http_request .= "\r\n";
        $http_request .= $req;

        $response = '';
        if( false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
                die ('Could not open socket');
        }

        fwrite($fs, $http_request);

        while ( !feof($fs) )
                $response .= fgets($fs, 1160); // One TCP-IP packet
        fclose($fs);
        $response = explode("\r\n\r\n", $response, 2);

        return $response;
}



/**
 * Gets the challenge HTML (javascript and non-javascript version).
 * This is called from the browser, and the resulting reCAPTCHA HTML widget
 * is embedded within the HTML form it was called from.
 * @param string $pubkey A public key for reCAPTCHA
 * @param string $error The error given by reCAPTCHA (optional, default is null)
 * @param boolean $use_ssl Should the request be made over ssl? (optional, default is false)

 * @return string - The HTML to be embedded in the user's form.
 */


function recaptcha_get_html ($pubkey, $error = null, $use_ssl = false,$customize = false)
{
    if ($pubkey == null || $pubkey == '') {
        die ("To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>");
    }
    
    if ($use_ssl) {
                $server = RECAPTCHA_API_SECURE_SERVER;
        } else {
                $server = RECAPTCHA_API_SERVER;
        }

    $errorpart = "";
    if ($error) {
           $errorpart = "&amp;error=" . $error;
    }

    ?>
        <script type="text/javascript">
            var RecaptchaOptions = {
                theme : 'custom',
                custom_theme_widget: 'recaptcha_widget'
            };
        </script>               
        <div id="recaptcha_widget" style="margin-top:10px;">

            <div class="control-group row-captcha">
                <label class="control-label customize_text"> <?php _e('Enter the words',ET_DOMAIN);?> </label>
                <div class="controls">
                    <a id="recaptcha_image" href="#" class="thumbnail"></a>
                    <div class="recaptcha_only_if_incorrect_sol" style="color:red"><?php _e("Incorrect please try again", ET_DOMAIN); ?></div>
                </div>
            </div>

            <div class="control-group">
                <label class="recaptcha_only_if_image control-label"><?php _e("Enter the words above:", ET_DOMAIN); ?></label>
                <label class="recaptcha_only_if_audio control-label"><?php _e("Enter the numbers you hear:", ET_DOMAIN); ?></label>

                <div class="controls">
                    <div class="input-append">
                        <input type="text" tabindex=3 id="recaptcha_response_field" name="recaptcha_response_field" class="required input-recaptcha" />
                        <span style="display:none" class="msg-custom"></span>
                        <span class="google-action">
                            <?php if($customize){ ?>
                                 <a class="button btn-reload" href="#"><i  data-icon="0" class="icon "></i></a>
                            <?php } else { ?>
                                 <a class="button" href="javascript:Recaptcha.reload()"><i  data-icon="0" class="icon"></i></a>
                            <?php } ?>                       
                           <a class="button" href="javascript:Recaptcha.showhelp()"><i data-icon="?" class="icon"></i></a>
                    </span>
                    </div>
                </div>
            </div>

        </div>

        <script type="text/javascript"
           src="<?php echo $server . '/challenge?k=' . $pubkey . $errorpart; ?>">
        </script>

       

        <?php 
        return '';
      
}


function add_style_google_captcha(){
    ?>
    <style type="text/css">
        .form-post #recaptcha_response_field{
            margin-top: 0;
        }
        #recaptcha_widget a ,
        #captchaReg a {
            display: inline-block;                     
            vertical-align: middle;
            text-align: center;
            line-height: 35px;
            position: relative;           

        }
        #recaptcha_widget{
            position: relative;
        }

        #recaptcha_widget .input-recaptcha, #captchaReg .input-recaptcha {width: 130px;}
        .ui-mobile  #recaptcha_widget .input-recaptcha, 
        .ui-mobile #captchaReg .input-recaptcha{
            width: 100%;
            background: #fff;
            height: 16px !important;                
            border-radius: 3px;
        }
        .ui-mobile  .register-form .post-new-classified div.ui-body-c{
            sborder:1px !important;
            height: 36px;
            margin: 5px 0;
        }
        .ui-mobile .google-action{ display: block; clear: both;}
        .ui-mobile .input-append{
            border:none !important;
        }
        #recaptcha_widget .button ,  #captchaReg .button {
            display: inline-block;                       
            width: 38px;
            height: 38px;
            font-size: 14px;                      
            color: #333;         
            text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);
            vertical-align: middle;
            cursor: pointer;
            background-color: #F5F5F5;
            border: 1px solid #BBB;           
                  }
        #recaptcha_widget label {
            line-height: 20px;
        }
        .recaptcha_only_if_incorrect_sol,
        .recaptcha_only_if_imag,
        .recaptcha_only_if_audio{
            display: none !important;
        }
        .input-recaptcha{float: left;height: 38px !important; margin-right: 5px;}
        .google-action{position: relative; clear: right; }
        .modal-login .control-label{
            display: none !important;
        }
        .post-step2 .recaptcha_only_if_image,
        .post-step3 .recaptcha_only_if_image{
            visibility: hidden;
        }
        .post-step2 .row-captcha,.post-step3 .row-captcha{
            border-bottom: 0 !important;
            padding-top: 0;
            padding-bottom: 0;
        }
        #reCaptcha .message{
            position:  absolute;
            right: 0;
        }
        .post-step2 a#recaptcha_image, .post-step3 a#recaptcha_image{overflow: hidden; display: block; padding: 0; }
        .comment-respond .input-append {position: relative;}
        .comment-respond .input-append label.error,
        .comment-respond .input-append .msg-custom label{ position: absolute; left:230px; color: #e87863; font-size: 13px; font-weight: normal;  }
        .comment-respond .input-append .msg-custom label{bottom: 0;}
        .comment-respond #reCaptcha  #recaptcha_image{height: 64px !important; display: block;}
        .comment-respond .row-captcha{ padding-bottom: 10px;}
        .comment-respond .row-captcha label{display: none !important;}
        .send-message .customize_text,
        .send-message .recaptcha_only_if_image{display: none;}
        .send-message a#recaptcha_image{overflow: hidden; display: block;}
        form.send-message .row-captcha{margin-bottom: 20px !important;}
        .send-message .input-append{ padding-bottom: 10px;}
        .send-message #recaptcha_response_field{ border: 1px solid #bcc0bb; width: 215px; border-radius: 3px; }
        .send-message .captcha-append{ padding-bottom: 10px;}
        .send-message .wrap-captcha{ padding-bottom: 20px; display: block; height: 102px;}
        .comment-respond .input-append label.error{}
    </style>
    <?php 
}

/**
 * A ReCaptchaResponse is returned from recaptcha_check_answer()
 */
class ReCaptchaResponse {
        var $is_valid;
        var $error;
}

/**
  * Calls an HTTP POST function to verify if the user's guess was correct
  * @param string $privkey
  * @param string $remoteip
  * @param string $challenge
  * @param string $response
  * @param array $extra_params an array of extra variables to post to the server
  * @return ReCaptchaResponse
  */
function recaptcha_check_answer ($privkey, $remoteip, $challenge, $response, $extra_params = array())
{
    if ($privkey == null || $privkey == '') {
        die ("To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>");
    }

    if ($remoteip == null || $remoteip == '') {
        die ("For security reasons, you must pass the remote ip to reCAPTCHA");
    }

    
    
        //discard spam submissions
        if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0) {
                $recaptcha_response = new ReCaptchaResponse();
                $recaptcha_response->is_valid = false;
                $recaptcha_response->error = 'incorrect-captcha-sol';
                return $recaptcha_response;
        }

        $response = _recaptcha_http_post (RECAPTCHA_VERIFY_SERVER, "/recaptcha/api/verify",
                                          array (
                                                 'privatekey' => $privkey,
                                                 'remoteip' => $remoteip,
                                                 'challenge' => $challenge,
                                                 'response' => $response
                                                 ) + $extra_params
                                          );

        $answers = explode ("\n", $response [1]);
        $recaptcha_response = new ReCaptchaResponse();

        if (trim ($answers [0]) == 'true') {
                $recaptcha_response->is_valid = true;
        }
        else {
                $recaptcha_response->is_valid = false;
                $recaptcha_response->error = $answers [1];
        }
        return $recaptcha_response;

}

/**
 * gets a URL where the user can sign up for reCAPTCHA. If your application
 * has a configuration page where you enter a key, you should provide a link
 * using this function.
 * @param string $domain The domain where the page is hosted
 * @param string $appname The name of your application
 */
function recaptcha_get_signup_url ($domain = null, $appname = null) {
    return "https://www.google.com/recaptcha/admin/create?" .  _recaptcha_qsencode (array ('domains' => $domain, 'app' => $appname));
}

function _recaptcha_aes_pad($val) {
    $block_size = 16;
    $numpad = $block_size - (strlen ($val) % $block_size);
    return str_pad($val, strlen ($val) + $numpad, chr($numpad));
}

/* Mailhide related code */

function _recaptcha_aes_encrypt($val,$ky) {
    if (! function_exists ("mcrypt_encrypt")) {
        die ("To use reCAPTCHA Mailhide, you need to have the mcrypt php module installed.");
    }
    $mode=MCRYPT_MODE_CBC;   
    $enc=MCRYPT_RIJNDAEL_128;
    $val=_recaptcha_aes_pad($val);
    return mcrypt_encrypt($enc, $ky, $val, $mode, "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0");
}


function _recaptcha_mailhide_urlbase64 ($x) {
    return strtr(base64_encode ($x), '+/', '-_');
}

/* gets the reCAPTCHA Mailhide url for a given email, public key and private key */
function recaptcha_mailhide_url($pubkey, $privkey, $email) {
    if ($pubkey == '' || $pubkey == null || $privkey == "" || $privkey == null) {
        die ("To use reCAPTCHA Mailhide, you have to sign up for a public and private key, " .
             "you can do so at <a href='http://www.google.com/recaptcha/mailhide/apikey'>http://www.google.com/recaptcha/mailhide/apikey</a>");
    }
    

    $ky = pack('H*', $privkey);
    $cryptmail = _recaptcha_aes_encrypt ($email, $ky);
    
    return "http://www.google.com/recaptcha/mailhide/d?k=" . $pubkey . "&c=" . _recaptcha_mailhide_urlbase64 ($cryptmail);
}

/**
 * gets the parts of the email to expose to the user.
 * eg, given johndoe@example,com return ["john", "example.com"].
 * the email is then displayed as john...@example.com
 */
function _recaptcha_mailhide_email_parts ($email) {
    $arr = preg_split("/@/", $email );

    if (strlen ($arr[0]) <= 4) {
        $arr[0] = substr ($arr[0], 0, 1);
    } else if (strlen ($arr[0]) <= 6) {
        $arr[0] = substr ($arr[0], 0, 3);
    } else {
        $arr[0] = substr ($arr[0], 0, 4);
    }
    return $arr;
}

/**
 * Gets html to display an email address given a public an private key.
 * to get a key, go to:
 *
 * http://www.google.com/recaptcha/mailhide/apikey
 */
function recaptcha_mailhide_html($pubkey, $privkey, $email) {
    $emailparts = _recaptcha_mailhide_email_parts ($email);
    $url = recaptcha_mailhide_url ($pubkey, $privkey, $email);
    
    return htmlentities($emailparts[0]) . "<a href='" . htmlentities ($url) .
        "' onclick=\"window.open('" . htmlentities ($url) . "', '', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=500,height=300'); return false;\" title=\"Reveal this e-mail address\">...</a>@" . htmlentities ($emailparts [1]);

}


?>
