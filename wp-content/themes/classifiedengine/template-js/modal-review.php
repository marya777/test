<?php 
/**
 * this template is render modal contact seller 
 * if you want to edit it, please override it via child theme to keep your change when update
*/
wp_reset_query();
$id= '';
$ad= '';
if( is_single() ) {
    global $post;
    $id = $post->ID;
    $ad = get_the_title( $post->ID );
}
?>
<div class="modal fade modal-feedback" id="send-review">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
	    <div class="modal-header">
	    	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>          
            <label><?php _e('Post Review', ET_DOMAIN); ?> </label>
        </div>
       
			<form class="form  send-message submit-review">
      			<div class="control-group form-group">                
                	<div class="controls">
                        <select id="attitude" name="attitude" >
                            <option value="pos"><?php _e("Positive", ET_DOMAIN); ?></option>
                            <option value="neg"><?php _e("Negative", ET_DOMAIN); ?></option>
                        </select>                       
                    </div>
              	</div>

                <div class="control-group form-group row-textarea">                
                    <div class="controls control-field">
                        <textarea  required="required" placeholder="<?php _e("Content", ET_DOMAIN); ?>" class="required"  name="comment_content" id="comment_content" rows="6"></textarea>      
                    </div>
                </div>

              	<div class="control-group form-group">                
                    <div class="controls">
    	                <div class=" left control-field" id="et-complete-control" >
                            <input  required="required" type="text" value="<?php echo $ad; ?>"  class="span6 required " placeholder="<?php _e("Related classified", ET_DOMAIN); ?>" name="review_article" id="review_article" /> 
                            <input required="required" type="hidden" value="<?php echo $id; ?>"  class="span6 required "  name="comment_post_ID" id="review_comment_post_ID" /> 
                        </div>    	                
                    </div>
              	</div>

                <?php do_action( 'ce_after_review_modal' ); ?>

              	<div class="control-group form-group row-last" >
                    <div class="controls">                  
                    <button class="btn btn-primary" type="submit"><?php _e('Post Review',ET_DOMAIN);?></button>
                    </div>
              	</div>			    
    	        <input type="reset" value="Reset" name="reset" style="z-index:-10; display:none; position:relative;" />
    	        <span  class="response"></span>
    	    </form>
		  
        </div>	      
    </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- Modal -->