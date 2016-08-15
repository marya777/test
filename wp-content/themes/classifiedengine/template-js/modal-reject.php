<div class="modal fade" id="reject-ad">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
	    <div class="modal-header">
	    	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <label><?php _e('Reject  ad:',ET_DOMAIN);?> <span class="post_name"></span></label>
        </div>
			<form class="form  reject-ad">
          	<input type="hidden" name="id" value="" />
            <div class="control-group form-group row-textarea">
                <div class="controls control-field">
                	<span class="label-reject"><?php _e('Sent seller a message',ET_DOMAIN); ?></span>
	                <textarea  placeholder="<?php _e('Type your message here',ET_DOMAIN);?>" class="required" name="message" rows="6"></textarea>
            	</div>
          	</div>
          	<div class="control-group form-group row-last" >
                <div class="controls">
                <button class="btn btn-primary" type="submit"><?php _e('Reject Ad',ET_DOMAIN);?></button>
                </div>
          	</div>
	        <input type="reset" value="<?php _e('Reset',ET_DOMAIN);?>" name="reset" style="z-index:-10; display:none; position:relative;" />
	        <span  class="response"></span>
	    </form>

      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
    <!-- Modal -->