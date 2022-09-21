<?php 
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// Delete all posts from "book" custom post type. 9827037932
global $wpdb;

// Remove posts of custom post type book
 $wpdb->query( 
    $wpdb->prepare( 
        "DELETE FROM $wpdb->posts
            WHERE post_type LIKE %s",
            "mdlibs-book"
    )
 );

// Remove Custom Fields associated with post type
$wpdb->query( 
    $wpdb->prepare( 
        "DELETE FROM $wpdb->postmeta
            WHERE post_id NOT IN (%d)",
            "SELECT ID FROM $wpdb->posts"
    )
);

// Remove Taxonomy Terms associated with post type

$sql_terms = $wpdb->query( 
    "DELETE FROM $wpdb->terms
        WHERE term_id IN (SELECT term_taxonomy_id FROM $wpdb->term_relationships WHERE object_id NOT IN (SELECT ID FROM $wpdb->posts))"
);

// Remove Taxonomy Terms relationships associated with post type
if($sql_terms){
    $wpdb->query( 
        $wpdb->prepare( 
            "DELETE FROM $wpdb->term_relationships
                WHERE object_id NOT IN (%d)",
                "SELECT ID FROM $wpdb->posts"
        )
    );
}