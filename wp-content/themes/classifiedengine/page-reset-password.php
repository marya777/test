<?php
/**
 * Template Name: Reset Password
 */

get_header();
global $post,$page;
the_post();
$current_page = $post;
$current_id = get_the_ID();

?>
  <div class="title-page">
    <div class="main-center container">
    	<span class="text"><?php echo apply_filters('title_reset_page','Account');?></span>
    </div>
  </div>
  <div class="container main-center main-content">
        <div class="row" id="page_reset_password">
            <div class="col-md-9 contenttext paddingTop34">
                <?php
                $user_login = isset($_REQUEST['user_login']) ? $_REQUEST['user_login'] :'';
                $key        = isset($_REQUEST['key']) ? $_REQUEST['key'] :'';
                ?>
                <div class="form-account">
                    <form action="" id="reset_password" novalidate="novalidate">
                        <input type="hidden" value="<?php echo $user_login;?>" name="user_login" id="user_login">
                        <input type="hidden" value="<?php echo $key;?>" name="user_key" id="user_key">
                        <div class="form-item form-group" id="">
                            <label><?php _e('New Password',ET_DOMAIN);?></label>
                            <div class="controls">
                                <input type="password" value="" id="user_new_pass" class="bg-default-input " name="user_new_pass">
                            </div>
                        </div>
                        <div class="form-item form-group" id="">
                            <label><?php _e('Retype New Password',ET_DOMAIN);?></label>
                            <div class="controls">
                                <input type="password" value="" id="user_pass_again" class="bg-default-input " name="user_pass_again">
                            </div>
                        </div>
                        <div class="line-hr"></div>
                        <div class="form-item form-group">
                            <button type="submit" class="btn  btn-primary" id="submit_profile"><?php _e('SAVE CHANGE',ET_DOMAIN);?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div><!--/.main center-->
  </div><!--/.fluid-container-->

<?php get_footer(); ?>