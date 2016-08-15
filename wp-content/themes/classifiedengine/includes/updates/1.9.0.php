<?php
global $wpdb;
$result = $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->term_taxonomy SET  taxonomy = %s	WHERE taxonomy = %s", CE_AD_CAT, 'ad_category' ) );
$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET  post_type = %s	WHERE post_type = %s", CE_AD_POSTTYPE, 'ad' ) );
$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->postmeta SET  meta_key = %s	WHERE meta_key = %s", CE_ET_PRICE, 'et_price' ) );
$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->postmeta SET  meta_key = %s	WHERE meta_key = %s", '_et_featured', 'et_featured' ) );

$query = "SELECT $wpdb->posts.ID, $wpdb->postmeta.meta_value
                    FROM $wpdb->posts
                    JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id
                    WHERE meta_key =  '".CE_ET_PRICE."'";
$old_posts = $wpdb->get_results( $query );

if ( count( $old_posts ) > 0 ) {
    foreach ( $old_posts as $old_post ) {
        update_post_meta( $old_post->ID, '_regular_price', $old_post->meta_value );
        update_post_meta( $old_post->ID, '_visibility', 'visible' );
        wp_set_object_terms( $old_post->ID, 'product_type', 'simple' );
    }
}