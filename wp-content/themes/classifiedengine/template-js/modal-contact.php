<?php 
/**
 * this template is render modal contact seller 
 * if you want to edit it, please override it via child theme to keep your change when update
*/
?>
<div class="modal fade modal-feedback" id="send-message">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
	      <div class="modal-header">
	    	  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>          
            <label><?php _e('Send',ET_DOMAIN);?> <span class="sembold seller-name"></span> <?php _e(' a message',ET_DOMAIN);?></label>
        </div>
        <?php
            $user_fname = '';
      	    $user_lname = '';
      	    $email 		= '';
      	    $phone 	 	= '';

            if ( is_user_logged_in() ) {
        		global $current_user,$user_login,$user_email;
        		// get_currentuserinfo();
      	    $user_fname = (!empty($current_user->user_firstname)) ? $current_user->user_firstname : $user_login;
      	    $user_lname = (!empty($current_user->user_lastname)) ? $current_user->user_lastname : $user_login;				  
        		$email 		  = $user_email;
        		$seller 	  = ET_Seller::convert($current_user);
        		$phone 	 	  = !empty($seller->et_phone) ? $seller->et_phone : $phone;				    
        	}

        	$user_fname = isset($_COOKIE['contactor_fname']) ? $_COOKIE['contactor_fname'] : $user_fname;
        	$user_lname = isset($_COOKIE['contactor_lname']) ? $_COOKIE['contactor_lname'] : $user_lname;
        	$email 		  = isset($_COOKIE['contactor_email']) ? $_COOKIE['contactor_email'] : $email;
          $phone 	 	  = isset($_COOKIE['contactor_phone']) ? $_COOKIE['contactor_phone'] : $phone;
        ?>
		<form class="form  send-message">
  			<div class="control-group form-group">                
            	<div class="controls">
                  	<div class="spanhalf control-field left" ><input type="text" value="<?php echo $user_fname;?>" name="first_name" class="required" placeholder="<?php _e("First name", ET_DOMAIN); ?>" id=""></div>
                  	<div class="spanhalf control-field right"><input type="text" value="<?php echo $user_lname;?>" name="last_name" class="required" placeholder="<?php _e("Last name", ET_DOMAIN); ?>" id=""></div>
                </div>
          	</div>
          	<div class="control-group form-group">                
                <div class="controls">
	                <div class="spanhalf left control-field" ><input type="text" value = "<?php echo $email;?>"   class="span6 email required " placeholder="<?php _e("Email", ET_DOMAIN); ?>" name="user_email" id="email" /> </div>
	                <div class="spanhalf right control-field"><input type="text" value = "<?php echo $phone;?>" class="span6" name="phone_number" placeholder="<?php _e("Phone Number", ET_DOMAIN); ?>" id="phone" /> </div>
                </div>
          	</div>
            <div class="control-group form-group row-textarea">                
                <div class="controls control-field">
	                <textarea  placeholder="<?php _e("Message", ET_DOMAIN); ?>" class="required" name="message" rows="6"></textarea>			               
            	</div>
          	</div>
             <?php do_action('ce_after_send_seller_message_form'); ?>
          	<div class="control-group form-group row-last" >
                <div class="controls">                  
                <button class="btn btn-primary" type="submit"><?php _e('Send Message',ET_DOMAIN);?></button>
                </div>
          	</div>
           
	        <input type="hidden" name="seller_id" id="seller_id" value="<?php global $post; echo $post->post_author; ?>"/>
          <input type="hidden" name="ad_id" id="ad_id" value=""/>
	        <input type="reset" value="Reset" name="reset" style="z-index:-10; display:none; position:relative;" />
	        <span  class="response"></span>
	    </form>
		
      </div>	      
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- Modal -->