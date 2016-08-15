<?php
$comments = get_comments(array('type' => 'comment', 'post_id' => $post->ID , 'status' => 'approve' ));
?>
<div class="blog-list-cmt">
	<ul class="comment-list">
		<?php 
		global $post;
		if(have_comments()) {
			global $isMobile;
			if( !$isMobile )
				wp_list_comments( array ('callback' => 'et_blog_list_comments'), $comments);
			else 
				wp_list_comments( array ('callback' => 'et_mobile_list_comments'), $comments);
		 }?>
	</ul>

	<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
		<nav id="comment-nav-below" class="navigation comment-navigation" role="navigation">
			<div class="nav-previous"><?php previous_comments_link( __( '&larr; Older Comments', ET_DOMAIN ) ); ?></div>
			<div class="nav-next"><?php next_comments_link( __( 'Newer Comments &rarr;', ET_DOMAIN ) ); ?></div>
		</nav><!-- #comment-nav-below -->
	<?php endif; // Check for comment navigation. ?>

</div>
	<?php  if( comments_open() && ! is_page() && post_type_supports( get_post_type(), 'comments' ) ) {
				$title = __("Add a comment", ET_DOMAIN).' &nbsp; <span class="fa fa-arrow-down"></span>';
				if( get_option('comment_registration') && !is_user_logged_in() ){
					$title = '';
				}

				comment_form ( array (
						'comment_field'        => ' <div class="form-item comment-text-area"><label for="comment">' . __( 'Comment', ET_DOMAIN ) . '</label>
													<div class="input">
													<textarea id="comment" name="comment" cols="45" placeholder="'.__('Your comment here',ET_DOMAIN).'" rows="8" aria-required="true"></textarea>
													</div> </div>',
						'must_log_in'          => '<p class="must-log-in">' . sprintf( __( 'You must be <a href="%s">logged in</a> to post a comment.', ET_DOMAIN ), wp_login_url( apply_filters( 'the_permalink', get_permalink( $post->ID ) ) ) ) . '</p>',
						'logged_in_as'         => '<p class="logged-in-as">' . sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>', ET_DOMAIN ), admin_url( 'profile.php' ), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post->ID ) ) ) ) . '</p>',
						'comment_notes_before' => '',
						'comment_notes_after'  => '',
						'id_form'              => 'commentform',
						'id_submit'            => 'submit',
						'title_reply'          => $title,
						'title_reply_to'       => __( 'Leave a Reply to %s', ET_DOMAIN),
						'cancel_reply_link'    => __( 'Cancel reply',ET_DOMAIN ),
						'label_submit'         => __( 'Submit Comment', ET_DOMAIN ),

				) )?>

	<?php } else { ?>
		<div class="comment-form">
			<h3 class="widget-title"><?php _e("Comment closed!", ET_DOMAIN);?></h3>
		</div>
	<?php } ?>

<?php

function et_blog_list_comments ( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;

	$date	=	get_comment_date('d S M Y');
	$date_arr	=	explode(' ', $date );

	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID();?>">
		<div id="comment-<?php comment_ID(); ?>" <?php if(get_post_type() == CE_AD_POSTTYPE) { echo Schema::Product("review");} ?>>
			<div class="thumb">
				<a href="#"><?php echo get_avatar( $comment, '' );?></a>
			</div>
			<div class="comment">
				<div class="author">
					<span <?php if(get_post_type() == CE_AD_POSTTYPE) { echo Schema::Review("author");} ?>><a href="#" <?php if(get_post_type() == CE_AD_POSTTYPE) { echo Schema::Person("name");} ?>><?php comment_author()?></a></span>
					<span class="icon" data-icon="t"></span>
					<span <?php if(get_post_type() == CE_AD_POSTTYPE) { echo Schema::Review("datePublished", sprintf('%2$s-%3$s-%1$s', $date_arr[3], $date_arr[2], $date_arr[0]));} ?>><?php echo $date_arr[2]?> <?php echo $date_arr[0]?><sup><?php echo strtoupper($date_arr[1])?></sup>, <?php echo $date_arr[3]?></span>
				</div>
				<?php if ( '0' == $comment->comment_approved ) : ?>
					<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', ET_DOMAIN ); ?></p>
				<?php endif; ?>
				<div class="content" <?php if(get_post_type() == CE_AD_POSTTYPE) { echo Schema::Review("reviewBody");} ?>>
	                <?php comment_text ()?>
	            </div>
	            <div class="reply">
	                <?php comment_reply_link(array_merge($args,	array(
												'reply_text' => __( 'Reply <span class="icon" data-icon="R"></span>', ET_DOMAIN ),
												'depth' => $depth,
												'max_depth' => $args['max_depth'] ) ));?>

				</div>
	       	</div>
		</div>
<?php

}

function et_mobile_list_comments ( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;

	$date	=	get_comment_date('d S M Y');
	$date_arr	=	explode(' ', $date );

	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID();?>">
		<div id="comment-<?php comment_ID(); ?>">
			<div class="comment detail-post">
				<span class="">
					<?php echo get_avatar( $comment, '50' );?>
				</span>
				<div class="name">
					<p><?php comment_author()?></p>
					<span class="icon" data-icon="t"><?php echo get_comment_date(); ?></span>
				</div>
			</div>
			<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', ET_DOMAIN ); ?></p>
			<?php endif; ?>
			<div class="text-cmt">
                <?php comment_text ()?>
                <div class="reply">
	                <?php comment_reply_link(array_merge($args,	array(
												'reply_text' => __( 'Reply <span class="icon" data-icon="R"></span>', ET_DOMAIN ),
												'depth' => $depth,
												'max_depth' => $args['max_depth'] ) ));?>

				</div>
            </div>

		</div>
<?php

}
