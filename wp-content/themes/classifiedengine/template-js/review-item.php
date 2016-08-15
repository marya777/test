<script type="text/template" id="review_item_template">
    <div class="list-reviewer-wrapper col-md-6">
        <div class="reviewer">
            <div class="avatar">
                <a href="{{ ads_link }}"  >
                    {{ avatar }}
                </a>
            </div>
            <div class="info-review">
                <h2 class="name">
                    {{ display_name }}
                </h2>
                <?php echo '<# if(attitude == "pos" ) { #>'; ?>
                    <span class="link-plus" title="<?php _e("Positive", ET_DOMAIN); ?>">
                        <span class="icon-vote">+</span>&nbsp;&nbsp;{{ date_ago }}
                    </span>
                <?php echo '<# } else { #>' ; ?>
                    <span class="link-minus" title="<?php _e("Negative", ET_DOMAIN); ?>">
                        <span class="icon-minus">-</span>&nbsp;&nbsp;{{ date_ago }}
                    </span>
                <?php echo '<# } #>'; ?>

                <p>{{ comment_content }}</p>
            </div>
        </div>
    </div>
</script>