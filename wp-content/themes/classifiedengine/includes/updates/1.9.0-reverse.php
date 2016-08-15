<?php
global $wpdb;
$wpdb->query($wpdb->prepare("UPDATE $wpdb->term_taxonomy SET  taxonomy = %s	WHERE taxonomy = %s", 'ad_category', 'product_cat' ));
$wpdb->query($wpdb->prepare("UPDATE $wpdb->posts SET  post_type = %s	WHERE post_type = %s", 'ad' , 'product'));
$wpdb->query($wpdb->prepare("UPDATE $wpdb->postmeta SET  meta_key = %s	WHERE meta_key = %s", 'et_price', '_regular_price'));
$wpdb->query($wpdb->prepare("UPDATE $wpdb->postmeta SET  meta_key = %s	WHERE meta_key = %s", 'et_featured', '_et_featured'));

$query = "SELECT $wpdb->posts.ID, $wpdb->postmeta.meta_value
                    FROM $wpdb->posts
                    JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id
                    WHERE meta_key =  '".CE_ET_PRICE."'";
$old_posts = $wpdb->get_results($query);
if(count($old_posts) > 0) {
    foreach ($old_posts as $old_post) {
        update_post_meta($old_post->ID, '_price', $old_post->meta_value);
        wp_set_object_terms($old_post->ID, 'simple', 'product_type');
    }
}