<?php
global $ce_config;
$currency       =   ET_Payment::get_currency();
?>
<!-- Modal -->
<div class="modal fade" id="modal_edit_ad" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php _e("Edit Ad", ET_DOMAIN); ?></h4>
            </div>
            <div class="modal-body">
                <div class="post-step3 form-post">
                    <form class="form edit-ad-form">
                        <div class="form-group">
                            <label class="control-label" for="title"><?php _e("Title", ET_DOMAIN); ?></label>
                            <div class="controls">
                                <input type="text" id="post_title" name="post_title" placeholder="" class="required" />
                            </div>
                        </div>
                        <div class="form-group clearfix">
                            <label class="control-label" for="title"><?php printf(__("Price (%s) ", ET_DOMAIN) , $currency['icon'] ); ?></label>
                            <div class="controls">
                                <input type="text" id="<?php echo CE_ET_PRICE; ?>" name="<?php echo CE_ET_PRICE; ?>" placeholder="" class="required">
                                <!-- <span class="icon icon-error" data-icon="!"></span> -->
                            </div>
                        </div>
                        <div class="form-group clearfix">
                            <label class="control-label" for="ad_location"><?php _e("Location", ET_DOMAIN); ?></label>
                            <div class="controls select-style">
                                <select name="ad_location" id="ad_location">
                                    <?php
                                    $locations     = ce_get_locations();
                                    $space  = '';
                                    $parent = 0;
                                    foreach ($locations as $k => $loc) {
                                            $tem_parent = $loc->parent;
                                    if( $tem_parent != $parent )  $space = 'sub';
                                    if( $tem_parent == 0 ) $space = '';
                                    $parent = $tem_parent; ?>
                                    <option class="<?php echo $space; ?>" value="<?php echo $loc->term_id ?>"data-text='<?php echo $loc->name; ?>' data-text-alter='<?php echo $loc->name; ?>' ><?php echo $loc->name; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="location"><?php _e("Address", ET_DOMAIN); ?></label>
                            <div class="controls">
                                <input type="text" id="et_full_location" name="et_full_location" placeholder="" class="required">
                                <input type="hidden" id="et_location_lat" placeholder="" name="et_location_lat" class="">
                                <input type="hidden" id="et_location_lng" placeholder="" name="et_location_lng" class="">
                            </div>
                        </div>
                        <?php do_action('ce_edit_ad_modal'); ?>
                        <div class="form-group clearfix">
                            <label class="control-label" for="category"><?php _e("Category", ET_DOMAIN); ?></label>
                            <div class="controls search-category">
                                <p class="icon" data-icon="s"></p>
                                <input type="text" id="category" placeholder="<?php _e("Search for a category...", ET_DOMAIN); ?>" name="">
                                <div class="category-all">
                                    <div class="overview" id="auto-complete-list">
                                        <!-- auto complete categories here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- add hook for add custom fields -->
                        <?php do_action( 'ce_ad_post_form_fields' ); ?>
                        <div class="form-group clearfix" id="gallery_container">
                            <label class="control-label label-uploadfile" for="photos"><?php _e("Photos", ET_DOMAIN); ?></label>
                            <div class="controls carousel-list">
                                <ul class="input-file clearfix" id="image-list">
                                    <li id="carousel_container">
                                        <span class="filename" id="carousel_browse_button">+</span>
                                    </li>
                                    <span class="et_ajaxnonce" id="<?php echo wp_create_nonce( 'ad_carousels_et_uploader' ); ?>"></span>
                                </ul>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="post_content"><?php _e("Description", ET_DOMAIN); ?></label>
                            <div class="controls mce-tinymce-controls">
                                <?php //wp_editor( 'noidung.' ,'post_content' ,ce_editor_settings() );  ?>
                                <textarea rows="10" id="post_content" name="post_content" placeholder="" class="required"></textarea>
                            </div>
                        </div>
                        <div class="form-group continue">
                            <div class="controls">
                                <button type="submit" class="btn  btn-primary"><?php _e("Edit Ad", ET_DOMAIN); ?></button>
                            </div>
                        </div>
                    </form>
                </div><!--/.content modal eidt -->
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->