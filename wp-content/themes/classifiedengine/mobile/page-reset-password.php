<?php
/**
 * Template Name: Reset Password
 */

et_get_mobile_header();

?>
<div data-role="content" class="post-classified" >
   
    <?php
    $user_login = isset($_REQUEST['user_login']) ? $_REQUEST['user_login'] :'';
    $key        = isset($_REQUEST['key']) ? $_REQUEST['key'] :'';
    ?>
    <div class="form-account">
        <form action="" id="reset_password" class="reset_password"novalidate="novalidate">
            <input type="hidden" value="<?php echo $user_login;?>" name="user_login" id="user_login">  
            <input type="hidden" value="<?php echo $key;?>" name="user_key" id="user_key"> 
            <div class="form-item form-group" id="">
                <label>New Password</label>
                <div class="controls">
                    <input type="password" class="required"  id="user_new_pass" class="bg-default-input " name="user_new_pass">
                </div>
            </div>
            <div class="form-item form-group" id="">
                <label>Retype New Password</label>
                <div class="controls">
                    <input type="password" class="required"   id="user_pass_again" class="bg-default-input " name="user_pass_again">
                </div>
            </div>                  
            <div class="line-hr"></div>
            <div class="form-item form-group">
                <button type="submit" class="btn  btn-primary" id="submit_profile"><?php _e('SAVE CHANGE',ET_DOMAIN);?></button>                            
            </div> 
        </form>
    </div>   
</div>

<?php 

et_get_mobile_footer();
