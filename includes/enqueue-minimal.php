<?php
/**
 * Minimal Enqueue - Essential scripts and styles only
 */

if (!defined('ABSPATH')) {
    exit;
}

// Enqueue frontend assets
function dz_enqueue_assets() {
    $version = DZ_EVENTS_VERSION;
    
    // Only enqueue if we're on a page that needs it
    if (is_singular('dz_event') || is_post_type_archive('dz_event') || has_shortcode(get_post()->post_content ?? '', 'zeen_events')) {
        wp_enqueue_style('dz-events-style', DZ_EVENTS_PLUGIN_URL . 'assets/css/style.css', array(), $version);
        wp_enqueue_script('dz-events-script', DZ_EVENTS_PLUGIN_URL . 'assets/js/script.js', array('jquery'), $version, true);
        
        // Localize script for AJAX
        wp_localize_script('dz-events-script', 'dz_events_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dz_events_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'dz_enqueue_assets');

// Enqueue admin assets
function dz_enqueue_admin_assets($hook) {
    global $post_type;
    
    if ($post_type === 'dz_event' || strpos($hook, 'dz_event') !== false) {
        wp_enqueue_style('dz-events-admin', DZ_EVENTS_PLUGIN_URL . 'assets/css/style.css', array(), DZ_EVENTS_VERSION);
        wp_enqueue_script('dz-events-admin', DZ_EVENTS_PLUGIN_URL . 'assets/js/script.js', array('jquery'), DZ_EVENTS_VERSION, true);
    }
}
add_action('admin_enqueue_scripts', 'dz_enqueue_admin_assets');

// Basic AJAX handlers
function dz_ajax_get_event_calendar_data() {
    if (!wp_verify_nonce($_POST['nonce'], 'dz_events_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    $event_id = intval($_POST['event_id']);
    $event = get_post($event_id);
    
    if (!$event || $event->post_type !== 'dz_event') {
        wp_send_json_error('Event not found');
    }
    
    $event_data = array(
        'title' => $event->post_title,
        'start_date' => get_post_meta($event_id, '_dz_event_start', true),
        'end_date' => get_post_meta($event_id, '_dz_event_end', true),
        'location' => get_post_meta($event_id, '_dz_event_location', true),
        'description' => $event->post_content
    );
    
    wp_send_json_success($event_data);
}
add_action('wp_ajax_dz_get_event_calendar_data', 'dz_ajax_get_event_calendar_data');
add_action('wp_ajax_nopriv_dz_get_event_calendar_data', 'dz_ajax_get_event_calendar_data');
