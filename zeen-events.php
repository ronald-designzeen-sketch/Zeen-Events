<?php
/**
 * Plugin Name: Zeen Events
 * Plugin URI: https://designzeen.com
 * Description: Professional event management plugin with multiple layouts, advanced filtering, and Elementor integration.
 * Version: 2.0.0
 * Author: Ronald @ Design Zeen Agency
 * Author URI: https://designzeen.com  
 * Text Domain: designzeen-events
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: false
 * 
 * Copyright (C) 2024 Design Zeen Agency
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Define plugin constants
define( 'DZ_EVENTS_VERSION', '2.0.0' );
define( 'DZ_EVENTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DZ_EVENTS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

// Check WordPress and PHP version compatibility
function dz_events_check_requirements() {
    global $wp_version;
    
    // Check WordPress version
    if ( version_compare( $wp_version, '5.0', '<' ) ) {
        add_action( 'admin_notices', 'dz_events_wp_version_notice' );
        return false;
    }
    
    // Check PHP version
    if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
        add_action( 'admin_notices', 'dz_events_php_version_notice' );
        return false;
    }
    
    return true;
}

// WordPress version notice
function dz_events_wp_version_notice() {
    echo '<div class="notice notice-error"><p>';
    echo '<strong>Zeen Events</strong> requires WordPress 5.0 or higher. ';
    echo 'You are running WordPress ' . get_bloginfo( 'version' ) . '. ';
    echo 'Please update WordPress to use this plugin.';
    echo '</p></div>';
}

// PHP version notice
function dz_events_php_version_notice() {
    echo '<div class="notice notice-error"><p>';
    echo '<strong>Zeen Events</strong> requires PHP 7.4 or higher. ';
    echo 'You are running PHP ' . PHP_VERSION . '. ';
    echo 'Please contact your hosting provider to update PHP.';
    echo '</p></div>';
}

// Check requirements before loading plugin
if ( ! dz_events_check_requirements() ) {
    return;
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

// Basic single event template handling
function dz_single_event_template($template) {
    if (is_singular('dz_event')) {
        $custom_template = locate_template(array('single-dz_event.php'));
        if ($custom_template) {
            return $custom_template;
        }
    }
    return $template;
}
add_filter('template_include', 'dz_single_event_template');

// Basic archive template handling
function dz_archive_event_template($template) {
    if (is_post_type_archive('dz_event')) {
        $custom_template = locate_template(array('archive-dz_event.php'));
        if ($custom_template) {
            return $custom_template;
        }
    }
    return $template;
}
add_filter('template_include', 'dz_archive_event_template');

// Load plugin textdomain for translations
function dz_events_load_textdomain() {
    load_plugin_textdomain(
        'designzeen-events',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages'
    );
}
add_action('plugins_loaded', 'dz_events_load_textdomain');
