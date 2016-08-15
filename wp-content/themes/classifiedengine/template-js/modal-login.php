<!-- Modal -->
<div class="modal fade modal-login" id="loginModal"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="label-signin">
                <div class="signin-header" >
                    <button type="button" class="close close-login" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <span class="title " data-rel="login-body"><?php _e("Sign in", ET_DOMAIN); ?></span>
                    <!-- link open modal register -->
                    <a href="#" class="requestform register-request" id="open-register" rel="register-body" ><?php _e("Register", ET_DOMAIN); ?> </a>
                </div>
                <div class="register-header" style="display: none;">
                    <button type="button" class="close close-login" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <span  class="title " data-rel="login-body"><?php _e("Register", ET_DOMAIN); ?></span>
                    <!-- link open modal register -->
                    <a href="#" class="requestform signin-request" id="open-login" rel="login-body" ><?php _e("Sign in", ET_DOMAIN); ?> </a>
                </div>
            </div>
            <div class="modal-body" id="login-body">
                <div class="login-body">
                    <div class="container-form-login">
                        <form class="form-horizontal form-login" id="form-login">
                            <div class="control-group">
                                <div class="controls">
                                    <input type="text" tabindex=1 required placeholder="<?php _e("Email or Username", ET_DOMAIN); ?>" id="inputEmail" name="username">
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="controls">
                                    <input type="password" tabindex=2 required placeholder="<?php _e("Password", ET_DOMAIN); ?>" id="inputPassword" name="password">
                                </div>
                            </div>
                            <?php do_action('et_add_login_field'); ?>
                            <div class="control-group button-signin">
                                <div class="controls">
                                    <button class="btn btn-primary" type="submit"><?php _e('Sign in',ET_DOMAIN);?></button>
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="controls">
                                    <a href="#" class="forgot-password-link"> <?php _e('Forget password',ET_DOMAIN); ?> </a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="container-forgot-password" style="display:none">
                        <button type="button" class="close close-login" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h1><?php _e('Forgot password',ET_DOMAIN);?></h1>
                        <form class="forgot-password form" id="form-forgot-pass">
                            <div class="control-group form-group">
                                <p class="span-top"> <?php _e('Enter your username or email address.',ET_DOMAIN);?></p>
                                <div class="controls">
                                    <input type="text" name="user_login" class="required" placeholder="<?php _e('Username or E-mail',ET_DOMAIN);?>" id="user_login">
                                </div>
                            </div>
                            <div class="control-group form-group row-last">
                                <div class="controls">
                                    <button class="btn btn-primary" type="submit"><?php _e('Get password',ET_DOMAIN);?></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <?php
                                $ce_options = new CE_Options();
                if( $ce_options->use_facebook() || $ce_options->use_twitter() ) { ?>
                <div class="control-group row-social-login ">
                    <label class="label-social-login"><?php _e('Sign in with',ET_DOMAIN);?></label>
                    <div class="right-social right">
                        <ul>
                            <?php if( $ce_options->use_facebook() ) { ?>
                            <li><a href="#" id="facebook_auth_btn" class="facebook_auth_btn btn-social btn-fb" ></a></li>
                            <?php }
                            if( $ce_options->use_twitter() ) { ?>
                            <li><a href="<?php echo home_url('?action=twitterauth');?>" id="tw_auth_btn" class=" btn-social btn-tw" ></a></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
                <?php } ?>
            </div>
            <div class="modal-body " id="register-body" style="display:none">
                <div class="container-form-login">
                    <form class="form-horizontal form-register" id="form-register">
                        <div class="control-group">
                            <div class="controls">
                                <input type="text" required placeholder="<?php _e("Full Name", ET_DOMAIN); ?>" id="display_name" name="display_name">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="controls">
                                <input type="text" required placeholder="<?php _e("Username", ET_DOMAIN); ?>" id="user_login" name="user_login">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="controls">
                                <input type="text" required placeholder="<?php _e("Email", ET_DOMAIN); ?>" id="user_email" name="user_email">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="controls">
                                <input type="password" required placeholder="<?php _e("Password", ET_DOMAIN); ?>" id="password" name="password">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="controls">
                                <input type="password" required placeholder="<?php _e("Repeat Password", ET_DOMAIN); ?>" id="repeat_password" name="repeat_password">
                            </div>
                        </div>
                        <?php
                            //do_action( 'ce_after_register_form' );
                                // comment @since 1.8.4
                            $term_of_use    =   et_get_page_link('terms-of-use' , array () , false);
                            if($term_of_use) {
                        ?>
                        <div class="control-group">
                            <div class="controls">
                                <input type="checkbox" name="loginkeeping" id="loginkeeping" value="loginkeeping" required />
                                <span><label for="loginkeeping" ><?php printf( __("I agree with <a target='_blank' href='%s' > Terms of use </a> ", ET_DOMAIN) , $term_of_use ) ; ?></label> </span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php
                                                    do_action('et_add_register_field');
                        ?>
                        <div class="control-group">
                            <div class="controls">
                                <button class="btn btn-primary" type="submit"><?php _e('Register',ET_DOMAIN);?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->