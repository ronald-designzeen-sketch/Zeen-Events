<?php
/**
 * Minimal Post Types - Essential post types only
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register Events Post Type
function dz_register_event_post_type() {
    $labels = array(
        'name'               => __('Events', 'designzeen-events'),
        'singular_name'      => __('Event', 'designzeen-events'),
        'menu_name'          => __('Events', 'designzeen-events'),
        'name_admin_bar'     => __('Event', 'designzeen-events'),
        'add_new'            => __('Add New Event', 'designzeen-events'),
        'add_new_item'       => __('Add New Event', 'designzeen-events'),
        'new_item'           => __('New Event', 'designzeen-events'),
        'edit_item'          => __('Edit Event', 'designzeen-events'),
        'view_item'          => __('View Event', 'designzeen-events'),
        'all_items'          => __('All Events', 'designzeen-events'),
        'search_items'       => __('Search Events', 'designzeen-events'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_nav_menus'  => true,
        'show_in_admin_bar'  => true,
        'has_archive'        => true,
        'rewrite'            => array( 'slug' => 'events', 'with_front' => false ),
        'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-calendar-alt',
        'capability_type'    => 'post',
        'hierarchical'       => false,
        'query_var'          => true,
        'can_export'         => true,
        'delete_with_user'   => false,
    );

    register_post_type( 'dz_event', $args );
}
add_action( 'init', 'dz_register_event_post_type' );

// Register Event Category Taxonomy
function dz_register_event_taxonomies() {
    $labels = array(
        'name'              => __('Event Categories', 'designzeen-events'),
        'singular_name'     => __('Event Category', 'designzeen-events'),
        'search_items'      => __('Search Categories', 'designzeen-events'),
        'all_items'         => __('All Categories', 'designzeen-events'),
        'parent_item'       => __('Parent Category', 'designzeen-events'),
        'parent_item_colon' => __('Parent Category:', 'designzeen-events'),
        'edit_item'         => __('Edit Category', 'designzeen-events'),
        'update_item'       => __('Update Category', 'designzeen-events'),
        'add_new_item'      => __('Add New Category', 'designzeen-events'),
        'new_item_name'     => __('New Category Name', 'designzeen-events'),
        'menu_name'         => __('Categories', 'designzeen-events'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'event-category' ),
        'show_in_rest'      => true,
    );

    register_taxonomy( 'dz_event_category', array( 'dz_event' ), $args );
}
add_action( 'init', 'dz_register_event_taxonomies' );

// Flush rewrite rules on activation
function dz_events_flush_rewrite_rules() {
    dz_register_event_post_type();
    dz_register_event_taxonomies();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'dz_events_flush_rewrite_rules' );
