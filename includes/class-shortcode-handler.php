<?php
/**
 * Simplified Shortcode Implementation
 * 
 * This replaces the complex 627-line shortcode function
 * with a clean, simple 20-line implementation
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the simplified shortcode
 */
add_shortcode('dz_events', 'dz_events_shortcode_simple');

/**
 * Simplified shortcode function
 * 
 * This function is now only 20 lines instead of 627 lines
 * It follows the simple algorithm: Data → Service → Renderer
 * 
 * @param array $atts Shortcode attributes
 * @return string Rendered HTML
 */
function dz_events_shortcode_simple($atts) {
    // Parse and sanitize attributes
    $params = shortcode_atts([
        'count'      => 6,
        'layout'     => 'grid',
        'category'   => '',
        'status'     => '',
        'orderby'    => 'meta_value',
        'order'      => 'ASC',
        'meta_key'   => '_dz_event_start',
        'show_past'  => 'false',
        'featured'   => 'false',
        'search'     => 'false',
        'search_placeholder' => __('Search events...', 'designzeen-events')
    ], $atts, 'dz_events');
    
    // Single line execution using the new core architecture
    return DZ_Events_Core::instance()->display_events($params);
}

/**
 * Legacy shortcode support
 * 
 * This maintains backward compatibility with the old shortcode
 * while using the new simplified architecture
 */
function dz_events_shortcode_legacy($atts) {
    // Convert legacy attributes to new format
    $legacy_mapping = [
        'use_custom_cards' => 'layout', // Map to layout
        'search_placeholder' => 'search_placeholder'
    ];
    
    $params = [];
    foreach ($atts as $key => $value) {
        if (isset($legacy_mapping[$key])) {
            $params[$legacy_mapping[$key]] = $value;
        } else {
            $params[$key] = $value;
        }
    }
    
    return DZ_Events_Core::instance()->display_events($params);
}

/**
 * AJAX handler for search functionality
 * 
 * This provides a simple AJAX endpoint for real-time search
 */
function dz_events_ajax_search() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'dz_events_search')) {
        wp_die('Security check failed');
    }
    
    $search_term = sanitize_text_field($_POST['search_term']);
    $filters = [
        'count' => intval($_POST['count'] ?? 20),
        'layout' => sanitize_text_field($_POST['layout'] ?? 'grid')
    ];
    
    // Use the core architecture for search
    $events = DZ_Events_Core::instance()->search_events($search_term, $filters);
    
    wp_send_json_success([
        'events' => $events,
        'count' => count($events),
        'search_term' => $search_term
    ]);
}
add_action('wp_ajax_dz_events_search', 'dz_events_ajax_search');
add_action('wp_ajax_nopriv_dz_events_search', 'dz_events_ajax_search');

/**
 * AJAX handler for filtering events
 * 
 * This provides a simple AJAX endpoint for category and date filtering
 */
function dz_events_ajax_filter() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'dz_events_filter')) {
        wp_die('Security check failed');
    }
    
    $filters = [
        'count' => intval($_POST['count'] ?? 20),
        'layout' => sanitize_text_field($_POST['layout'] ?? 'grid'),
        'category' => sanitize_text_field($_POST['category'] ?? ''),
        'status' => sanitize_text_field($_POST['status'] ?? ''),
        'date_from' => sanitize_text_field($_POST['date_from'] ?? ''),
        'date_to' => sanitize_text_field($_POST['date_to'] ?? '')
    ];
    
    // Use the core architecture for filtering
    $events = DZ_Events_Core::instance()->display_events($filters);
    
    wp_send_json_success([
        'events' => $events,
        'count' => count($events),
        'filters' => $filters
    ]);
}
add_action('wp_ajax_dz_events_filter', 'dz_events_ajax_filter');
add_action('wp_ajax_nopriv_dz_events_filter', 'dz_events_ajax_filter');

/**
 * Cache invalidation hooks
 * 
 * These hooks ensure the cache is cleared when events are updated
 */
function dz_events_clear_cache_on_save($post_id) {
    if (get_post_type($post_id) === 'dz_event') {
        DZ_Events_Cache::instance()->clear_all();
    }
}
add_action('save_post', 'dz_events_clear_cache_on_save');
add_action('delete_post', 'dz_events_clear_cache_on_save');

/**
 * Cache invalidation for taxonomy changes
 */
function dz_events_clear_cache_on_taxonomy_change($term_id, $tt_id, $taxonomy) {
    if ($taxonomy === 'dz_event_category') {
        DZ_Events_Cache::instance()->clear_all();
    }
}
add_action('created_dz_event_category', 'dz_events_clear_cache_on_taxonomy_change', 10, 3);
add_action('edited_dz_event_category', 'dz_events_clear_cache_on_taxonomy_change', 10, 3);
add_action('delete_dz_event_category', 'dz_events_clear_cache_on_taxonomy_change', 10, 3);

/**
 * Performance optimization
 * 
 * This function optimizes the shortcode for better performance
 */
function dz_events_optimize_shortcode() {
    // Preload common data
    if (is_admin() || wp_doing_ajax()) {
        return;
    }
    
    // Add preload hints for better performance
    add_action('wp_head', function() {
        echo '<link rel="preload" href="' . plugin_dir_url(__FILE__) . '../assets/css/style.css" as="style">';
        echo '<link rel="preload" href="' . plugin_dir_url(__FILE__) . '../assets/js/script.js" as="script">';
    });
}
add_action('init', 'dz_events_optimize_shortcode');

/**
 * Debug mode support
 * 
 * This provides debugging information when WP_DEBUG is enabled
 */
function dz_events_debug_info() {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }
    
    if (current_user_can('manage_options')) {
        add_action('wp_footer', function() {
            echo '<!-- Zeen Events Debug Info -->';
            echo '<!-- Core Instance: ' . (DZ_Events_Core::instance() ? 'Loaded' : 'Not Loaded') . ' -->';
            echo '<!-- Cache Status: ' . (DZ_Events_Cache::instance() ? 'Active' : 'Inactive') . ' -->';
            echo '<!-- Memory Usage: ' . memory_get_usage(true) . ' bytes -->';
            echo '<!-- Query Count: ' . get_num_queries() . ' -->';
        });
    }
}
add_action('init', 'dz_events_debug_info');
