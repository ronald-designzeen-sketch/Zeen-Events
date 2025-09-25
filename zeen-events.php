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

// Enqueue basic styles and scripts
function dz_events_enqueue_assets() {
    // Only enqueue on event pages
    if (is_singular('dz_event') || is_post_type_archive('dz_event')) {
        wp_enqueue_style('dz-events-style', DZ_EVENTS_PLUGIN_URL . 'assets/css/style.css', array(), DZ_EVENTS_VERSION);
        wp_enqueue_script('dz-events-script', DZ_EVENTS_PLUGIN_URL . 'assets/js/script.js', array('jquery'), DZ_EVENTS_VERSION, true);
        
        // Localize script for AJAX
        wp_localize_script('dz-events-script', 'dz_events_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dz_events_nonce')
        ));
    }
    
    // Check for shortcode in current post content
    global $post;
    if ($post && has_shortcode($post->post_content, 'zeen_events')) {
        wp_enqueue_style('dz-events-style', DZ_EVENTS_PLUGIN_URL . 'assets/css/style.css', array(), DZ_EVENTS_VERSION);
        wp_enqueue_script('dz-events-script', DZ_EVENTS_PLUGIN_URL . 'assets/js/script.js', array('jquery'), DZ_EVENTS_VERSION, true);
        
        // Localize script for AJAX
        wp_localize_script('dz-events-script', 'dz_events_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dz_events_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'dz_events_enqueue_assets');

// Enhanced shortcode for displaying events
function dz_events_shortcode($atts) {
    $atts = shortcode_atts(array(
        'posts_per_page' => 6,
        'category' => '',
        'orderby' => 'meta_value',
        'meta_key' => '_dz_event_start',
        'order' => 'ASC',
        'layout' => 'grid',
        'show_date' => 'true',
        'show_time' => 'true',
        'show_location' => 'true',
        'show_price' => 'true',
        'show_excerpt' => 'true',
        'show_organizer' => 'false',
        'featured_only' => 'false',
        'upcoming_only' => 'true',
        'columns' => '3',
        'image_size' => 'medium',
        'button_text' => 'View Details'
    ), $atts);

    $args = array(
        'post_type' => 'dz_event',
        'posts_per_page' => intval($atts['posts_per_page']),
        'orderby' => $atts['orderby'],
        'order' => $atts['order'],
        'post_status' => 'publish'
    );

    // Set meta key for date ordering
    if ($atts['orderby'] === 'meta_value') {
        $args['meta_key'] = $atts['meta_key'];
    }

    // Category filter
    if (!empty($atts['category'])) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'dz_event_category',
                'field' => 'slug',
                'terms' => $atts['category']
            )
        );
    }

    // Featured events only
    if ($atts['featured_only'] === 'true') {
        $args['meta_query'] = array(
            array(
                'key' => '_dz_event_featured',
                'value' => '1',
                'compare' => '='
            )
        );
    }

    // Upcoming events only
    if ($atts['upcoming_only'] === 'true') {
        $today = date('Y-m-d');
        if (!isset($args['meta_query'])) {
            $args['meta_query'] = array();
        }
        $args['meta_query'][] = array(
            'key' => '_dz_event_start',
            'value' => $today,
            'compare' => '>=',
            'type' => 'DATE'
        );
        $args['meta_query']['relation'] = 'AND';
    }

    $events = new WP_Query($args);
    
    if (!$events->have_posts()) {
        return '<div class="dz-events-no-events"><p>' . __('No events found.', 'designzeen-events') . '</p></div>';
    }

    $layout_class = 'dz-events-' . esc_attr($atts['layout']);
    $columns_class = 'dz-events-columns-' . esc_attr($atts['columns']);
    
    $output = '<div class="dz-events-shortcode ' . $layout_class . ' ' . $columns_class . '">';
    
    while ($events->have_posts()) {
        $events->the_post();
        $event_id = get_the_ID();
        
        $start_date = get_post_meta($event_id, '_dz_event_start', true);
        $end_date = get_post_meta($event_id, '_dz_event_end', true);
        $start_time = get_post_meta($event_id, '_dz_event_start_time', true);
        $end_time = get_post_meta($event_id, '_dz_event_end_time', true);
        $location = get_post_meta($event_id, '_dz_event_location', true);
        $price = get_post_meta($event_id, '_dz_event_price', true);
        $organizer = get_post_meta($event_id, '_dz_event_organizer', true);
        $featured = get_post_meta($event_id, '_dz_event_featured', true);
        
        $output .= '<div class="dz-event-card' . ($featured ? ' dz-featured' : '') . '">';
        
        // Featured badge
        if ($featured) {
            $output .= '<div class="dz-featured-badge">' . __('Featured', 'designzeen-events') . '</div>';
        }
        
        // Event image
        if (has_post_thumbnail()) {
            $output .= '<div class="dz-event-image">';
            $output .= '<a href="' . get_permalink() . '">';
            $output .= get_the_post_thumbnail($event_id, $atts['image_size']);
            $output .= '</a></div>';
        }
        
        $output .= '<div class="dz-event-content">';
        $output .= '<h3 class="dz-event-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
        
        // Event meta information
        $output .= '<div class="dz-event-meta">';
        
        if ($atts['show_date'] === 'true' && $start_date) {
            $date_format = 'M j, Y';
            if ($end_date && $end_date !== $start_date) {
                $date_format = 'M j - j, Y';
            }
            $output .= '<div class="dz-meta-item dz-meta-date">';
            $output .= '<i class="dz-icon dz-icon-calendar"></i> ';
            $output .= date($date_format, strtotime($start_date));
            $output .= '</div>';
        }
        
        if ($atts['show_time'] === 'true' && $start_time) {
            $output .= '<div class="dz-meta-item dz-meta-time">';
            $output .= '<i class="dz-icon dz-icon-clock"></i> ';
            $output .= date('g:i A', strtotime($start_time));
            if ($end_time) {
                $output .= ' - ' . date('g:i A', strtotime($end_time));
            }
            $output .= '</div>';
        }
        
        if ($atts['show_location'] === 'true' && $location) {
            $output .= '<div class="dz-meta-item dz-meta-location">';
            $output .= '<i class="dz-icon dz-icon-location"></i> ';
            $output .= esc_html($location);
            $output .= '</div>';
        }
        
        if ($atts['show_price'] === 'true' && $price) {
            $output .= '<div class="dz-meta-item dz-meta-price">';
            $output .= '<i class="dz-icon dz-icon-price"></i> ';
            $output .= esc_html($price);
            $output .= '</div>';
        }
        
        if ($atts['show_organizer'] === 'true' && $organizer) {
            $output .= '<div class="dz-meta-item dz-meta-organizer">';
            $output .= '<i class="dz-icon dz-icon-user"></i> ';
            $output .= esc_html($organizer);
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        // Event excerpt
        if ($atts['show_excerpt'] === 'true' && has_excerpt()) {
            $output .= '<div class="dz-event-excerpt">' . get_the_excerpt() . '</div>';
        }
        
        // Event actions
        $output .= '<div class="dz-event-actions">';
        $output .= '<a href="' . get_permalink() . '" class="dz-btn dz-btn-primary">' . esc_html($atts['button_text']) . '</a>';
        $output .= '</div>';
        
        $output .= '</div></div>';
    }
    
    $output .= '</div>';
    
    wp_reset_postdata();
    
    return $output;
}
add_shortcode('zeen_events', 'dz_events_shortcode');

// Additional shortcodes for specific use cases
function dz_events_featured_shortcode($atts) {
    $atts['featured_only'] = 'true';
    return dz_events_shortcode($atts);
}
add_shortcode('zeen_events_featured', 'dz_events_featured_shortcode');

function dz_events_upcoming_shortcode($atts) {
    $atts['upcoming_only'] = 'true';
    $atts['posts_per_page'] = isset($atts['posts_per_page']) ? $atts['posts_per_page'] : 3;
    return dz_events_shortcode($atts);
}
add_shortcode('zeen_events_upcoming', 'dz_events_upcoming_shortcode');

function dz_events_list_shortcode($atts) {
    $atts['layout'] = 'list';
    $atts['columns'] = '1';
    return dz_events_shortcode($atts);
}
add_shortcode('zeen_events_list', 'dz_events_list_shortcode');

// Add basic admin columns for events
function dz_events_admin_columns($columns) {
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = $columns['title'];
    $new_columns['event_date'] = __('Event Date', 'designzeen-events');
    $new_columns['event_location'] = __('Location', 'designzeen-events');
    $new_columns['event_price'] = __('Price', 'designzeen-events');
    $new_columns['date'] = $columns['date'];
    
    return $new_columns;
}
add_filter('manage_dz_event_posts_columns', 'dz_events_admin_columns');

function dz_events_admin_column_content($column, $post_id) {
    switch ($column) {
        case 'event_date':
            $start_date = get_post_meta($post_id, '_dz_event_start', true);
            if ($start_date) {
                echo date('M j, Y', strtotime($start_date));
            } else {
                echo '—';
            }
            break;
        case 'event_location':
            $location = get_post_meta($post_id, '_dz_event_location', true);
            echo $location ? esc_html($location) : '—';
            break;
        case 'event_price':
            $price = get_post_meta($post_id, '_dz_event_price', true);
            echo $price ? esc_html($price) : '—';
            break;
    }
}
add_action('manage_dz_event_posts_custom_column', 'dz_events_admin_column_content', 10, 2);

// Basic AJAX handler for future functionality
function dz_events_ajax_handler() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'dz_events_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    $action = sanitize_text_field($_POST['action']);
    
    switch ($action) {
        case 'dz_events_get_event_data':
            $event_id = intval($_POST['event_id']);
            $event = get_post($event_id);
            
            if (!$event || $event->post_type !== 'dz_event') {
                wp_send_json_error('Event not found');
            }
            
            $event_data = array(
                'id' => $event_id,
                'title' => $event->post_title,
                'content' => $event->post_content,
                'excerpt' => $event->post_excerpt,
                'start_date' => get_post_meta($event_id, '_dz_event_start', true),
                'end_date' => get_post_meta($event_id, '_dz_event_end', true),
                'location' => get_post_meta($event_id, '_dz_event_location', true),
                'price' => get_post_meta($event_id, '_dz_event_price', true),
                'url' => get_permalink($event_id)
            );
            
            wp_send_json_success($event_data);
            break;
            
        default:
            wp_send_json_error('Invalid action');
    }
}
add_action('wp_ajax_dz_events_get_event_data', 'dz_events_ajax_handler');
add_action('wp_ajax_nopriv_dz_events_get_event_data', 'dz_events_ajax_handler');

// Add meta boxes for event data
function dz_events_add_meta_boxes() {
    add_meta_box(
        'dz_event_details',
        __('Event Details', 'designzeen-events'),
        'dz_events_meta_box_callback',
        'dz_event',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'dz_events_add_meta_boxes');

function dz_events_meta_box_callback($post) {
    wp_nonce_field('dz_events_meta_box', 'dz_events_meta_box_nonce');
    
    $start_date = get_post_meta($post->ID, '_dz_event_start', true);
    $end_date = get_post_meta($post->ID, '_dz_event_end', true);
    $start_time = get_post_meta($post->ID, '_dz_event_start_time', true);
    $end_time = get_post_meta($post->ID, '_dz_event_end_time', true);
    $location = get_post_meta($post->ID, '_dz_event_location', true);
    $address = get_post_meta($post->ID, '_dz_event_address', true);
    $price = get_post_meta($post->ID, '_dz_event_price', true);
    $capacity = get_post_meta($post->ID, '_dz_event_capacity', true);
    $organizer = get_post_meta($post->ID, '_dz_event_organizer', true);
    $contact_email = get_post_meta($post->ID, '_dz_event_contact_email', true);
    $contact_phone = get_post_meta($post->ID, '_dz_event_contact_phone', true);
    $website = get_post_meta($post->ID, '_dz_event_website', true);
    $featured = get_post_meta($post->ID, '_dz_event_featured', true);
    
    echo '<table class="form-table">';
    
    // Start Date
    echo '<tr>';
    echo '<th><label for="dz_event_start">' . __('Start Date', 'designzeen-events') . '</label></th>';
    echo '<td><input type="date" id="dz_event_start" name="dz_event_start" value="' . esc_attr($start_date) . '" /></td>';
    echo '</tr>';
    
    // End Date
    echo '<tr>';
    echo '<th><label for="dz_event_end">' . __('End Date', 'designzeen-events') . '</label></th>';
    echo '<td><input type="date" id="dz_event_end" name="dz_event_end" value="' . esc_attr($end_date) . '" /></td>';
    echo '</tr>';
    
    // Start Time
    echo '<tr>';
    echo '<th><label for="dz_event_start_time">' . __('Start Time', 'designzeen-events') . '</label></th>';
    echo '<td><input type="time" id="dz_event_start_time" name="dz_event_start_time" value="' . esc_attr($start_time) . '" /></td>';
    echo '</tr>';
    
    // End Time
    echo '<tr>';
    echo '<th><label for="dz_event_end_time">' . __('End Time', 'designzeen-events') . '</label></th>';
    echo '<td><input type="time" id="dz_event_end_time" name="dz_event_end_time" value="' . esc_attr($end_time) . '" /></td>';
    echo '</tr>';
    
    // Location
    echo '<tr>';
    echo '<th><label for="dz_event_location">' . __('Location/Venue', 'designzeen-events') . '</label></th>';
    echo '<td><input type="text" id="dz_event_location" name="dz_event_location" value="' . esc_attr($location) . '" style="width: 100%;" /></td>';
    echo '</tr>';
    
    // Address
    echo '<tr>';
    echo '<th><label for="dz_event_address">' . __('Full Address', 'designzeen-events') . '</label></th>';
    echo '<td><textarea id="dz_event_address" name="dz_event_address" rows="3" style="width: 100%;">' . esc_textarea($address) . '</textarea></td>';
    echo '</tr>';
    
    // Price
    echo '<tr>';
    echo '<th><label for="dz_event_price">' . __('Price', 'designzeen-events') . '</label></th>';
    echo '<td><input type="text" id="dz_event_price" name="dz_event_price" value="' . esc_attr($price) . '" placeholder="e.g., $50, Free, $25-$100" /></td>';
    echo '</tr>';
    
    // Capacity
    echo '<tr>';
    echo '<th><label for="dz_event_capacity">' . __('Capacity', 'designzeen-events') . '</label></th>';
    echo '<td><input type="number" id="dz_event_capacity" name="dz_event_capacity" value="' . esc_attr($capacity) . '" min="1" /></td>';
    echo '</tr>';
    
    // Organizer
    echo '<tr>';
    echo '<th><label for="dz_event_organizer">' . __('Organizer', 'designzeen-events') . '</label></th>';
    echo '<td><input type="text" id="dz_event_organizer" name="dz_event_organizer" value="' . esc_attr($organizer) . '" style="width: 100%;" /></td>';
    echo '</tr>';
    
    // Contact Email
    echo '<tr>';
    echo '<th><label for="dz_event_contact_email">' . __('Contact Email', 'designzeen-events') . '</label></th>';
    echo '<td><input type="email" id="dz_event_contact_email" name="dz_event_contact_email" value="' . esc_attr($contact_email) . '" style="width: 100%;" /></td>';
    echo '</tr>';
    
    // Contact Phone
    echo '<tr>';
    echo '<th><label for="dz_event_contact_phone">' . __('Contact Phone', 'designzeen-events') . '</label></th>';
    echo '<td><input type="tel" id="dz_event_contact_phone" name="dz_event_contact_phone" value="' . esc_attr($contact_phone) . '" /></td>';
    echo '</tr>';
    
    // Website
    echo '<tr>';
    echo '<th><label for="dz_event_website">' . __('Event Website', 'designzeen-events') . '</label></th>';
    echo '<td><input type="url" id="dz_event_website" name="dz_event_website" value="' . esc_attr($website) . '" style="width: 100%;" /></td>';
    echo '</tr>';
    
    // Featured Event
    echo '<tr>';
    echo '<th><label for="dz_event_featured">' . __('Featured Event', 'designzeen-events') . '</label></th>';
    echo '<td><input type="checkbox" id="dz_event_featured" name="dz_event_featured" value="1" ' . checked($featured, 1, false) . ' /> <label for="dz_event_featured">' . __('Mark as featured event', 'designzeen-events') . '</label></td>';
    echo '</tr>';
    
    echo '</table>';
}

function dz_events_save_meta_box($post_id) {
    if (!isset($_POST['dz_events_meta_box_nonce'])) {
        return;
    }
    
    if (!wp_verify_nonce($_POST['dz_events_meta_box_nonce'], 'dz_events_meta_box')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    $fields = array(
        'dz_event_start',
        'dz_event_end',
        'dz_event_start_time',
        'dz_event_end_time',
        'dz_event_location',
        'dz_event_address',
        'dz_event_price',
        'dz_event_capacity',
        'dz_event_organizer',
        'dz_event_contact_email',
        'dz_event_contact_phone',
        'dz_event_website'
    );
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
        }
    }
    
    // Handle featured checkbox
    $featured = isset($_POST['dz_event_featured']) ? 1 : 0;
    update_post_meta($post_id, '_dz_event_featured', $featured);
}
add_action('save_post', 'dz_events_save_meta_box');

// Register WordPress Widget
class DZ_Events_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'dz_events_widget',
            __('Zeen Events Widget', 'designzeen-events'),
            array('description' => __('Display events in a widget', 'designzeen-events'))
        );
    }
    
    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);
        $number = !empty($instance['number']) ? absint($instance['number']) : 3;
        $show_date = !empty($instance['show_date']) ? 1 : 0;
        $show_location = !empty($instance['show_location']) ? 1 : 0;
        $featured_only = !empty($instance['featured_only']) ? 1 : 0;
        $category = !empty($instance['category']) ? $instance['category'] : '';
        
        echo $args['before_widget'];
        
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        
        $query_args = array(
            'post_type' => 'dz_event',
            'posts_per_page' => $number,
            'post_status' => 'publish',
            'orderby' => 'meta_value',
            'meta_key' => '_dz_event_start',
            'order' => 'ASC'
        );
        
        if ($featured_only) {
            $query_args['meta_query'] = array(
                array(
                    'key' => '_dz_event_featured',
                    'value' => '1',
                    'compare' => '='
                )
            );
        }
        
        if (!empty($category)) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'dz_event_category',
                    'field' => 'slug',
                    'terms' => $category
                )
            );
        }
        
        $events = new WP_Query($query_args);
        
        if ($events->have_posts()) {
            echo '<div class="dz-events-widget">';
            while ($events->have_posts()) {
                $events->the_post();
                $event_id = get_the_ID();
                $start_date = get_post_meta($event_id, '_dz_event_start', true);
                $location = get_post_meta($event_id, '_dz_event_location', true);
                
                echo '<div class="dz-widget-event">';
                echo '<h4><a href="' . get_permalink() . '">' . get_the_title() . '</a></h4>';
                
                if ($show_date && $start_date) {
                    echo '<div class="dz-widget-date">' . date('M j, Y', strtotime($start_date)) . '</div>';
                }
                
                if ($show_location && $location) {
                    echo '<div class="dz-widget-location">' . esc_html($location) . '</div>';
                }
                
                echo '</div>';
            }
            echo '</div>';
            wp_reset_postdata();
        } else {
            echo '<p>' . __('No events found.', 'designzeen-events') . '</p>';
        }
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Upcoming Events', 'designzeen-events');
        $number = !empty($instance['number']) ? absint($instance['number']) : 3;
        $show_date = !empty($instance['show_date']) ? 1 : 0;
        $show_location = !empty($instance['show_location']) ? 1 : 0;
        $featured_only = !empty($instance['featured_only']) ? 1 : 0;
        $category = !empty($instance['category']) ? $instance['category'] : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'designzeen-events'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of events to show:', 'designzeen-events'); ?></label>
            <input class="tiny-text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="number" step="1" min="1" value="<?php echo $number; ?>" size="3">
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_date); ?> id="<?php echo $this->get_field_id('show_date'); ?>" name="<?php echo $this->get_field_name('show_date'); ?>" />
            <label for="<?php echo $this->get_field_id('show_date'); ?>"><?php _e('Show event date', 'designzeen-events'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_location); ?> id="<?php echo $this->get_field_id('show_location'); ?>" name="<?php echo $this->get_field_name('show_location'); ?>" />
            <label for="<?php echo $this->get_field_id('show_location'); ?>"><?php _e('Show event location', 'designzeen-events'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($featured_only); ?> id="<?php echo $this->get_field_id('featured_only'); ?>" name="<?php echo $this->get_field_name('featured_only'); ?>" />
            <label for="<?php echo $this->get_field_id('featured_only'); ?>"><?php _e('Show only featured events', 'designzeen-events'); ?></label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category (optional):', 'designzeen-events'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>" type="text" value="<?php echo esc_attr($category); ?>" placeholder="<?php _e('Category slug', 'designzeen-events'); ?>">
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['number'] = (!empty($new_instance['number'])) ? absint($new_instance['number']) : 3;
        $instance['show_date'] = !empty($new_instance['show_date']) ? 1 : 0;
        $instance['show_location'] = !empty($new_instance['show_location']) ? 1 : 0;
        $instance['featured_only'] = !empty($new_instance['featured_only']) ? 1 : 0;
        $instance['category'] = (!empty($new_instance['category'])) ? sanitize_text_field($new_instance['category']) : '';
        
        return $instance;
    }
}

function dz_events_register_widget() {
    register_widget('DZ_Events_Widget');
}
add_action('widgets_init', 'dz_events_register_widget');

// Card Settings and Design Customization System
class DZ_Events_Card_Settings {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_head', array($this, 'output_custom_css'));
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=dz_event',
            __('Card Settings', 'designzeen-events'),
            __('Card Settings', 'designzeen-events'),
            'manage_options',
            'dz-events-card-settings',
            array($this, 'admin_page')
        );
    }
    
    public function register_settings() {
        register_setting('dz_events_card_settings', 'dz_events_card_options');
        
        add_settings_section(
            'dz_events_card_design',
            __('Card Design Settings', 'designzeen-events'),
            array($this, 'design_section_callback'),
            'dz-events-card-settings'
        );
        
        add_settings_field(
            'card_background_color',
            __('Card Background Color', 'designzeen-events'),
            array($this, 'color_field_callback'),
            'dz-events-card-settings',
            'dz_events_card_design',
            array('field' => 'card_background_color', 'default' => '#ffffff')
        );
        
        add_settings_field(
            'card_border_color',
            __('Card Border Color', 'designzeen-events'),
            array($this, 'color_field_callback'),
            'dz-events-card-settings',
            'dz_events_card_design',
            array('field' => 'card_border_color', 'default' => '#e1e1e1')
        );
        
        add_settings_field(
            'card_border_radius',
            __('Card Border Radius (px)', 'designzeen-events'),
            array($this, 'number_field_callback'),
            'dz-events-card-settings',
            'dz_events_card_design',
            array('field' => 'card_border_radius', 'default' => '8', 'min' => '0', 'max' => '50')
        );
        
        add_settings_field(
            'card_shadow',
            __('Card Shadow', 'designzeen-events'),
            array($this, 'select_field_callback'),
            'dz-events-card-settings',
            'dz_events_card_design',
            array('field' => 'card_shadow', 'default' => 'medium', 'options' => array(
                'none' => __('None', 'designzeen-events'),
                'light' => __('Light', 'designzeen-events'),
                'medium' => __('Medium', 'designzeen-events'),
                'heavy' => __('Heavy', 'designzeen-events')
            ))
        );
        
        add_settings_field(
            'title_color',
            __('Title Color', 'designzeen-events'),
            array($this, 'color_field_callback'),
            'dz-events-card-settings',
            'dz_events_card_design',
            array('field' => 'title_color', 'default' => '#333333')
        );
        
        add_settings_field(
            'title_font_size',
            __('Title Font Size (px)', 'designzeen-events'),
            array($this, 'number_field_callback'),
            'dz-events-card-settings',
            'dz_events_card_design',
            array('field' => 'title_font_size', 'default' => '20', 'min' => '12', 'max' => '48')
        );
        
        add_settings_field(
            'meta_text_color',
            __('Meta Text Color', 'designzeen-events'),
            array($this, 'color_field_callback'),
            'dz-events-card-settings',
            'dz_events_card_design',
            array('field' => 'meta_text_color', 'default' => '#666666')
        );
        
        add_settings_field(
            'button_background_color',
            __('Button Background Color', 'designzeen-events'),
            array($this, 'color_field_callback'),
            'dz-events-card-settings',
            'dz_events_card_design',
            array('field' => 'button_background_color', 'default' => '#0073aa')
        );
        
        add_settings_field(
            'button_text_color',
            __('Button Text Color', 'designzeen-events'),
            array($this, 'color_field_callback'),
            'dz-events-card-settings',
            'dz_events_card_design',
            array('field' => 'button_text_color', 'default' => '#ffffff')
        );
        
        add_settings_field(
            'button_hover_color',
            __('Button Hover Color', 'designzeen-events'),
            array($this, 'color_field_callback'),
            'dz-events-card-settings',
            'dz_events_card_design',
            array('field' => 'button_hover_color', 'default' => '#005177')
        );
        
        add_settings_field(
            'featured_badge_color',
            __('Featured Badge Color', 'designzeen-events'),
            array($this, 'color_field_callback'),
            'dz-events-card-settings',
            'dz_events_card_design',
            array('field' => 'featured_badge_color', 'default' => '#0073aa')
        );
        
        add_settings_field(
            'grid_gap',
            __('Grid Gap (px)', 'designzeen-events'),
            array($this, 'number_field_callback'),
            'dz-events-card-settings',
            'dz_events_card_design',
            array('field' => 'grid_gap', 'default' => '20', 'min' => '0', 'max' => '50')
        );
        
        add_settings_field(
            'image_height',
            __('Image Height (px)', 'designzeen-events'),
            array($this, 'number_field_callback'),
            'dz-events-card-settings',
            'dz_events_card_design',
            array('field' => 'image_height', 'default' => '200', 'min' => '100', 'max' => '400')
        );
    }
    
    public function design_section_callback() {
        echo '<p>' . __('Customize the appearance of your event cards. Changes will be applied globally to all event displays.', 'designzeen-events') . '</p>';
    }
    
    public function color_field_callback($args) {
        $options = get_option('dz_events_card_options');
        $value = isset($options[$args['field']]) ? $options[$args['field']] : $args['default'];
        echo '<input type="color" name="dz_events_card_options[' . $args['field'] . ']" value="' . esc_attr($value) . '" />';
    }
    
    public function number_field_callback($args) {
        $options = get_option('dz_events_card_options');
        $value = isset($options[$args['field']]) ? $options[$args['field']] : $args['default'];
        $min = isset($args['min']) ? $args['min'] : '';
        $max = isset($args['max']) ? $args['max'] : '';
        echo '<input type="number" name="dz_events_card_options[' . $args['field'] . ']" value="' . esc_attr($value) . '" min="' . $min . '" max="' . $max . '" />';
    }
    
    public function select_field_callback($args) {
        $options = get_option('dz_events_card_options');
        $value = isset($options[$args['field']]) ? $options[$args['field']] : $args['default'];
        echo '<select name="dz_events_card_options[' . $args['field'] . ']">';
        foreach ($args['options'] as $key => $label) {
            echo '<option value="' . $key . '" ' . selected($value, $key, false) . '>' . $label . '</option>';
        }
        echo '</select>';
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Event Card Settings', 'designzeen-events'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('dz_events_card_settings');
                do_settings_sections('dz-events-card-settings');
                submit_button();
                ?>
            </form>
            
            <div class="card" style="margin-top: 20px;">
                <h2><?php _e('Preview', 'designzeen-events'); ?></h2>
                <p><?php _e('Here\'s how your event cards will look with the current settings:', 'designzeen-events'); ?></p>
                <div id="dz-card-preview" style="max-width: 300px; margin: 20px 0;">
                    <!-- Preview will be generated by JavaScript -->
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            function updatePreview() {
                var options = {};
                $('input, select').each(function() {
                    var name = $(this).attr('name');
                    if (name && name.startsWith('dz_events_card_options[')) {
                        var field = name.match(/\[([^\]]+)\]/)[1];
                        options[field] = $(this).val();
                    }
                });
                
                var preview = $('#dz-card-preview');
                var shadow = '';
                switch(options.card_shadow) {
                    case 'light': shadow = '0 1px 3px rgba(0,0,0,0.1)'; break;
                    case 'medium': shadow = '0 2px 8px rgba(0,0,0,0.15)'; break;
                    case 'heavy': shadow = '0 4px 16px rgba(0,0,0,0.2)'; break;
                    default: shadow = 'none';
                }
                
                preview.html(`
                    <div style="
                        background: ${options.card_background_color || '#ffffff'};
                        border: 1px solid ${options.card_border_color || '#e1e1e1'};
                        border-radius: ${options.card_border_radius || '8'}px;
                        box-shadow: ${shadow};
                        overflow: hidden;
                        margin-bottom: ${options.grid_gap || '20'}px;
                    ">
                        <div style="
                            height: ${options.image_height || '200'}px;
                            background: linear-gradient(45deg, #f0f0f0, #e0e0e0);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: #999;
                        ">Event Image</div>
                        <div style="padding: 20px;">
                            <h3 style="
                                color: ${options.title_color || '#333333'};
                                font-size: ${options.title_font_size || '20'}px;
                                margin: 0 0 15px 0;
                            ">Sample Event Title</h3>
                            <div style="
                                color: ${options.meta_text_color || '#666666'};
                                margin-bottom: 15px;
                                font-size: 14px;
                            ">📅 Dec 25, 2024 • 📍 Sample Location</div>
                            <button style="
                                background: ${options.button_background_color || '#0073aa'};
                                color: ${options.button_text_color || '#ffffff'};
                                border: none;
                                padding: 10px 20px;
                                border-radius: 4px;
                                cursor: pointer;
                            ">View Details</button>
                        </div>
                    </div>
                `);
            }
            
            $('input, select').on('change input', updatePreview);
            updatePreview();
        });
        </script>
        <?php
    }
    
    public function output_custom_css() {
        $options = get_option('dz_events_card_options');
        if (!$options) return;
        
        $css = '<style type="text/css">';
        $css .= '.dz-event-card {';
        
        if (isset($options['card_background_color'])) {
            $css .= 'background-color: ' . esc_attr($options['card_background_color']) . ';';
        }
        
        if (isset($options['card_border_color'])) {
            $css .= 'border-color: ' . esc_attr($options['card_border_color']) . ';';
        }
        
        if (isset($options['card_border_radius'])) {
            $css .= 'border-radius: ' . esc_attr($options['card_border_radius']) . 'px;';
        }
        
        if (isset($options['card_shadow'])) {
            switch ($options['card_shadow']) {
                case 'light':
                    $css .= 'box-shadow: 0 1px 3px rgba(0,0,0,0.1);';
                    break;
                case 'medium':
                    $css .= 'box-shadow: 0 2px 8px rgba(0,0,0,0.15);';
                    break;
                case 'heavy':
                    $css .= 'box-shadow: 0 4px 16px rgba(0,0,0,0.2);';
                    break;
            }
        }
        
        $css .= '}';
        
        if (isset($options['title_color'])) {
            $css .= '.dz-event-title a { color: ' . esc_attr($options['title_color']) . '; }';
        }
        
        if (isset($options['title_font_size'])) {
            $css .= '.dz-event-title { font-size: ' . esc_attr($options['title_font_size']) . 'px; }';
        }
        
        if (isset($options['meta_text_color'])) {
            $css .= '.dz-event-meta, .dz-meta-item { color: ' . esc_attr($options['meta_text_color']) . '; }';
        }
        
        if (isset($options['button_background_color'])) {
            $css .= '.dz-btn-primary { background-color: ' . esc_attr($options['button_background_color']) . '; }';
        }
        
        if (isset($options['button_text_color'])) {
            $css .= '.dz-btn-primary { color: ' . esc_attr($options['button_text_color']) . '; }';
        }
        
        if (isset($options['button_hover_color'])) {
            $css .= '.dz-btn-primary:hover { background-color: ' . esc_attr($options['button_hover_color']) . '; }';
        }
        
        if (isset($options['featured_badge_color'])) {
            $css .= '.dz-featured-badge { background-color: ' . esc_attr($options['featured_badge_color']) . '; }';
        }
        
        if (isset($options['grid_gap'])) {
            $css .= '.dz-events-shortcode { gap: ' . esc_attr($options['grid_gap']) . 'px; }';
        }
        
        if (isset($options['image_height'])) {
            $css .= '.dz-event-image { height: ' . esc_attr($options['image_height']) . 'px; }';
            $css .= '.dz-event-image img { height: ' . esc_attr($options['image_height']) . 'px; }';
        }
        
        $css .= '</style>';
        
        echo $css;
    }
}

// Initialize Card Settings
DZ_Events_Card_Settings::get_instance();

// Custom Fields System
class DZ_Events_Custom_Fields {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('add_meta_boxes', array($this, 'add_custom_fields_meta_box'));
        add_action('save_post', array($this, 'save_custom_fields'));
        add_action('admin_menu', array($this, 'add_custom_fields_admin_menu'));
        add_action('admin_init', array($this, 'register_custom_fields_settings'));
    }
    
    public function add_custom_fields_meta_box() {
        add_meta_box(
            'dz_events_custom_fields',
            __('Custom Fields', 'designzeen-events'),
            array($this, 'custom_fields_meta_box_callback'),
            'dz_event',
            'normal',
            'high'
        );
    }
    
    public function custom_fields_meta_box_callback($post) {
        wp_nonce_field('dz_events_custom_fields', 'dz_events_custom_fields_nonce');
        
        $custom_fields = get_option('dz_events_custom_fields', array());
        $post_meta = get_post_meta($post->ID, '_dz_events_custom_fields', true);
        
        echo '<div id="dz-custom-fields-container">';
        
        if (empty($custom_fields)) {
            echo '<p>' . __('No custom fields defined. <a href="' . admin_url('edit.php?post_type=dz_event&page=dz-events-custom-fields') . '">Add custom fields</a> to extend your events.', 'designzeen-events') . '</p>';
        } else {
            foreach ($custom_fields as $field) {
                $field_value = isset($post_meta[$field['name']]) ? $post_meta[$field['name']] : '';
                
                echo '<div class="dz-custom-field" style="margin-bottom: 15px;">';
                echo '<label for="dz_custom_field_' . esc_attr($field['name']) . '" style="display: block; margin-bottom: 5px; font-weight: bold;">';
                echo esc_html($field['label']);
                if ($field['required']) {
                    echo ' <span style="color: red;">*</span>';
                }
                echo '</label>';
                
                switch ($field['type']) {
                    case 'text':
                        echo '<input type="text" id="dz_custom_field_' . esc_attr($field['name']) . '" name="dz_custom_fields[' . esc_attr($field['name']) . ']" value="' . esc_attr($field_value) . '" style="width: 100%;" />';
                        break;
                    case 'textarea':
                        echo '<textarea id="dz_custom_field_' . esc_attr($field['name']) . '" name="dz_custom_fields[' . esc_attr($field['name']) . ']" rows="3" style="width: 100%;">' . esc_textarea($field_value) . '</textarea>';
                        break;
                    case 'number':
                        echo '<input type="number" id="dz_custom_field_' . esc_attr($field['name']) . '" name="dz_custom_fields[' . esc_attr($field['name']) . ']" value="' . esc_attr($field_value) . '" style="width: 100%;" />';
                        break;
                    case 'email':
                        echo '<input type="email" id="dz_custom_field_' . esc_attr($field['name']) . '" name="dz_custom_fields[' . esc_attr($field['name']) . ']" value="' . esc_attr($field_value) . '" style="width: 100%;" />';
                        break;
                    case 'url':
                        echo '<input type="url" id="dz_custom_field_' . esc_attr($field['name']) . '" name="dz_custom_fields[' . esc_attr($field['name']) . ']" value="' . esc_attr($field_value) . '" style="width: 100%;" />';
                        break;
                    case 'select':
                        echo '<select id="dz_custom_field_' . esc_attr($field['name']) . '" name="dz_custom_fields[' . esc_attr($field['name']) . ']" style="width: 100%;">';
                        echo '<option value="">' . __('Select an option', 'designzeen-events') . '</option>';
                        $options = explode("\n", $field['options']);
                        foreach ($options as $option) {
                            $option = trim($option);
                            if (!empty($option)) {
                                echo '<option value="' . esc_attr($option) . '" ' . selected($field_value, $option, false) . '>' . esc_html($option) . '</option>';
                            }
                        }
                        echo '</select>';
                        break;
                    case 'checkbox':
                        echo '<input type="checkbox" id="dz_custom_field_' . esc_attr($field['name']) . '" name="dz_custom_fields[' . esc_attr($field['name']) . ']" value="1" ' . checked($field_value, '1', false) . ' />';
                        echo '<label for="dz_custom_field_' . esc_attr($field['name']) . '">' . esc_html($field['description']) . '</label>';
                        break;
                    case 'date':
                        echo '<input type="date" id="dz_custom_field_' . esc_attr($field['name']) . '" name="dz_custom_fields[' . esc_attr($field['name']) . ']" value="' . esc_attr($field_value) . '" style="width: 100%;" />';
                        break;
                    case 'time':
                        echo '<input type="time" id="dz_custom_field_' . esc_attr($field['name']) . '" name="dz_custom_fields[' . esc_attr($field['name']) . ']" value="' . esc_attr($field_value) . '" style="width: 100%;" />';
                        break;
                }
                
                if (!empty($field['description']) && $field['type'] !== 'checkbox') {
                    echo '<p class="description">' . esc_html($field['description']) . '</p>';
                }
                
                echo '</div>';
            }
        }
        
        echo '</div>';
    }
    
    public function save_custom_fields($post_id) {
        if (!isset($_POST['dz_events_custom_fields_nonce'])) {
            return;
        }
        
        if (!wp_verify_nonce($_POST['dz_events_custom_fields_nonce'], 'dz_events_custom_fields')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        if (isset($_POST['dz_custom_fields'])) {
            $custom_fields = $_POST['dz_custom_fields'];
            $sanitized_fields = array();
            
            foreach ($custom_fields as $key => $value) {
                $sanitized_fields[sanitize_key($key)] = sanitize_text_field($value);
            }
            
            update_post_meta($post_id, '_dz_events_custom_fields', $sanitized_fields);
        }
    }
    
    public function add_custom_fields_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=dz_event',
            __('Custom Fields', 'designzeen-events'),
            __('Custom Fields', 'designzeen-events'),
            'manage_options',
            'dz-events-custom-fields',
            array($this, 'custom_fields_admin_page')
        );
    }
    
    public function register_custom_fields_settings() {
        register_setting('dz_events_custom_fields_settings', 'dz_events_custom_fields');
    }
    
    public function custom_fields_admin_page() {
        if (isset($_POST['add_custom_field'])) {
            $this->add_custom_field();
        }
        
        if (isset($_POST['delete_custom_field'])) {
            $this->delete_custom_field();
        }
        
        $custom_fields = get_option('dz_events_custom_fields', array());
        ?>
        <div class="wrap">
            <h1><?php _e('Custom Fields', 'designzeen-events'); ?></h1>
            
            <div class="card" style="max-width: 800px;">
                <h2><?php _e('Add New Custom Field', 'designzeen-events'); ?></h2>
                <form method="post" action="">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Field Name', 'designzeen-events'); ?></th>
                            <td>
                                <input type="text" name="field_name" required style="width: 100%;" />
                                <p class="description"><?php _e('Internal field name (lowercase, no spaces)', 'designzeen-events'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Field Label', 'designzeen-events'); ?></th>
                            <td>
                                <input type="text" name="field_label" required style="width: 100%;" />
                                <p class="description"><?php _e('Display label for the field', 'designzeen-events'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Field Type', 'designzeen-events'); ?></th>
                            <td>
                                <select name="field_type" style="width: 100%;">
                                    <option value="text"><?php _e('Text', 'designzeen-events'); ?></option>
                                    <option value="textarea"><?php _e('Textarea', 'designzeen-events'); ?></option>
                                    <option value="number"><?php _e('Number', 'designzeen-events'); ?></option>
                                    <option value="email"><?php _e('Email', 'designzeen-events'); ?></option>
                                    <option value="url"><?php _e('URL', 'designzeen-events'); ?></option>
                                    <option value="select"><?php _e('Select Dropdown', 'designzeen-events'); ?></option>
                                    <option value="checkbox"><?php _e('Checkbox', 'designzeen-events'); ?></option>
                                    <option value="date"><?php _e('Date', 'designzeen-events'); ?></option>
                                    <option value="time"><?php _e('Time', 'designzeen-events'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Options', 'designzeen-events'); ?></th>
                            <td>
                                <textarea name="field_options" rows="3" style="width: 100%;"></textarea>
                                <p class="description"><?php _e('For select fields, enter one option per line', 'designzeen-events'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Description', 'designzeen-events'); ?></th>
                            <td>
                                <textarea name="field_description" rows="2" style="width: 100%;"></textarea>
                                <p class="description"><?php _e('Help text for the field', 'designzeen-events'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Required', 'designzeen-events'); ?></th>
                            <td>
                                <input type="checkbox" name="field_required" value="1" />
                                <label><?php _e('Make this field required', 'designzeen-events'); ?></label>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(__('Add Custom Field', 'designzeen-events'), 'primary', 'add_custom_field'); ?>
                </form>
            </div>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2><?php _e('Existing Custom Fields', 'designzeen-events'); ?></h2>
                <?php if (empty($custom_fields)): ?>
                    <p><?php _e('No custom fields defined yet.', 'designzeen-events'); ?></p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Field Name', 'designzeen-events'); ?></th>
                                <th><?php _e('Label', 'designzeen-events'); ?></th>
                                <th><?php _e('Type', 'designzeen-events'); ?></th>
                                <th><?php _e('Required', 'designzeen-events'); ?></th>
                                <th><?php _e('Actions', 'designzeen-events'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($custom_fields as $index => $field): ?>
                                <tr>
                                    <td><code><?php echo esc_html($field['name']); ?></code></td>
                                    <td><?php echo esc_html($field['label']); ?></td>
                                    <td><?php echo esc_html(ucfirst($field['type'])); ?></td>
                                    <td><?php echo $field['required'] ? __('Yes', 'designzeen-events') : __('No', 'designzeen-events'); ?></td>
                                    <td>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="field_index" value="<?php echo $index; ?>" />
                                            <input type="submit" name="delete_custom_field" value="<?php _e('Delete', 'designzeen-events'); ?>" class="button button-small" onclick="return confirm('<?php _e('Are you sure you want to delete this custom field?', 'designzeen-events'); ?>');" />
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    private function add_custom_field() {
        $custom_fields = get_option('dz_events_custom_fields', array());
        
        $field = array(
            'name' => sanitize_key($_POST['field_name']),
            'label' => sanitize_text_field($_POST['field_label']),
            'type' => sanitize_text_field($_POST['field_type']),
            'options' => sanitize_textarea_field($_POST['field_options']),
            'description' => sanitize_textarea_field($_POST['field_description']),
            'required' => isset($_POST['field_required']) ? 1 : 0
        );
        
        $custom_fields[] = $field;
        update_option('dz_events_custom_fields', $custom_fields);
        
        echo '<div class="notice notice-success"><p>' . __('Custom field added successfully!', 'designzeen-events') . '</p></div>';
    }
    
    private function delete_custom_field() {
        $custom_fields = get_option('dz_events_custom_fields', array());
        $index = intval($_POST['field_index']);
        
        if (isset($custom_fields[$index])) {
            unset($custom_fields[$index]);
            $custom_fields = array_values($custom_fields); // Re-index array
            update_option('dz_events_custom_fields', $custom_fields);
            echo '<div class="notice notice-success"><p>' . __('Custom field deleted successfully!', 'designzeen-events') . '</p></div>';
        }
    }
}

// Initialize Custom Fields
DZ_Events_Custom_Fields::get_instance();

// Elementor Integration
class DZ_Events_Elementor {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('elementor/widgets/widgets_registered', array($this, 'register_elementor_widgets'));
        add_action('elementor/elements/categories_registered', array($this, 'add_elementor_category'));
        add_action('elementor/init', array($this, 'init_elementor_support'));
    }
    
    public function init_elementor_support() {
        // Add Elementor support for events post type
        add_post_type_support('dz_event', 'elementor');
    }
    
    public function add_elementor_category($elements_manager) {
        $elements_manager->add_category(
            'dz-events',
            array(
                'title' => __('Zeen Events', 'designzeen-events'),
                'icon' => 'fa fa-calendar',
            )
        );
    }
    
    public function register_elementor_widgets() {
        if (!class_exists('Elementor\Widget_Base')) {
            return;
        }
        
        // Register Events Grid Widget
        require_once DZ_EVENTS_PLUGIN_PATH . 'includes/elementor-events-widget.php';
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new DZ_Events_Elementor_Widget());
        
        // Register Single Event Widget
        require_once DZ_EVENTS_PLUGIN_PATH . 'includes/elementor-single-event-widget.php';
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new DZ_Single_Event_Elementor_Widget());
    }
}

// Initialize Elementor Integration
if (did_action('elementor/loaded')) {
    DZ_Events_Elementor::get_instance();
} else {
    add_action('elementor/loaded', array('DZ_Events_Elementor', 'get_instance'));
}

// Create Elementor Widget Files
function dz_events_create_elementor_widgets() {
    $plugin_path = DZ_EVENTS_PLUGIN_PATH;
    
    // Create includes directory if it doesn't exist
    if (!file_exists($plugin_path . 'includes')) {
        wp_mkdir_p($plugin_path . 'includes');
    }
    
    // Events Grid Widget
    $events_widget_content = '<?php
/**
 * Elementor Events Grid Widget
 */

if (!defined(\'ABSPATH\')) {
    exit;
}

class DZ_Events_Elementor_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return \'dz_events_grid\';
    }
    
    public function get_title() {
        return __(\'Events Grid\', \'designzeen-events\');
    }
    
    public function get_icon() {
        return \'eicon-posts-grid\';
    }
    
    public function get_categories() {
        return [\'dz-events\'];
    }
    
    public function get_keywords() {
        return [\'events\', \'grid\', \'calendar\', \'zeen\'];
    }
    
    protected function _register_controls() {
        $this->start_controls_section(
            \'content_section\',
            [
                \'label\' => __(\'Content\', \'designzeen-events\'),
                \'tab\' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            \'posts_per_page\',
            [
                \'label\' => __(\'Number of Events\', \'designzeen-events\'),
                \'type\' => \Elementor\Controls_Manager::NUMBER,
                \'default\' => 6,
                \'min\' => 1,
                \'max\' => 50,
            ]
        );
        
        $this->add_control(
            \'columns\',
            [
                \'label\' => __(\'Columns\', \'designzeen-events\'),
                \'type\' => \Elementor\Controls_Manager::SELECT,
                \'default\' => \'3\',
                \'options\' => [
                    \'1\' => \'1 Column\',
                    \'2\' => \'2 Columns\',
                    \'3\' => \'3 Columns\',
                    \'4\' => \'4 Columns\',
                ],
            ]
        );
        
        $this->add_control(
            \'layout\',
            [
                \'label\' => __(\'Layout\', \'designzeen-events\'),
                \'type\' => \Elementor\Controls_Manager::SELECT,
                \'default\' => \'grid\',
                \'options\' => [
                    \'grid\' => __(\'Grid\', \'designzeen-events\'),
                    \'list\' => __(\'List\', \'designzeen-events\'),
                ],
            ]
        );
        
        $this->add_control(
            \'category\',
            [
                \'label\' => __(\'Category\', \'designzeen-events\'),
                \'type\' => \Elementor\Controls_Manager::TEXT,
                \'placeholder\' => __(\'Category slug (optional)\', \'designzeen-events\'),
            ]
        );
        
        $this->add_control(
            \'featured_only\',
            [
                \'label\' => __(\'Featured Events Only\', \'designzeen-events\'),
                \'type\' => \Elementor\Controls_Manager::SWITCHER,
                \'label_on\' => __(\'Yes\', \'designzeen-events\'),
                \'label_off\' => __(\'No\', \'designzeen-events\'),
                \'return_value\' => \'true\',
                \'default\' => \'false\',
            ]
        );
        
        $this->add_control(
            \'upcoming_only\',
            [
                \'label\' => __(\'Upcoming Events Only\', \'designzeen-events\'),
                \'type\' => \Elementor\Controls_Manager::SWITCHER,
                \'label_on\' => __(\'Yes\', \'designzeen-events\'),
                \'label_off\' => __(\'No\', \'designzeen-events\'),
                \'return_value\' => \'true\',
                \'default\' => \'true\',
            ]
        );
        
        $this->end_controls_section();
        
        $this->start_controls_section(
            \'display_section\',
            [
                \'label\' => __(\'Display Options\', \'designzeen-events\'),
                \'tab\' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            \'show_date\',
            [
                \'label\' => __(\'Show Date\', \'designzeen-events\'),
                \'type\' => \Elementor\Controls_Manager::SWITCHER,
                \'label_on\' => __(\'Show\', \'designzeen-events\'),
                \'label_off\' => __(\'Hide\', \'designzeen-events\'),
                \'return_value\' => \'true\',
                \'default\' => \'true\',
            ]
        );
        
        $this->add_control(
            \'show_time\',
            [
                \'label\' => __(\'Show Time\', \'designzeen-events\'),
                \'type\' => \Elementor\Controls_Manager::SWITCHER,
                \'label_on\' => __(\'Show\', \'designzeen-events\'),
                \'label_off\' => __(\'Hide\', \'designzeen-events\'),
                \'return_value\' => \'true\',
                \'default\' => \'true\',
            ]
        );
        
        $this->add_control(
            \'show_location\',
            [
                \'label\' => __(\'Show Location\', \'designzeen-events\'),
                \'type\' => \Elementor\Controls_Manager::SWITCHER,
                \'label_on\' => __(\'Show\', \'designzeen-events\'),
                \'label_off\' => __(\'Hide\', \'designzeen-events\'),
                \'return_value\' => \'true\',
                \'default\' => \'true\',
            ]
        );
        
        $this->add_control(
            \'show_price\',
            [
                \'label\' => __(\'Show Price\', \'designzeen-events\'),
                \'type\' => \Elementor\Controls_Manager::SWITCHER,
                \'label_on\' => __(\'Show\', \'designzeen-events\'),
                \'label_off\' => __(\'Hide\', \'designzeen-events\'),
                \'return_value\' => \'true\',
                \'default\' => \'true\',
            ]
        );
        
        $this->add_control(
            \'show_excerpt\',
            [
                \'label\' => __(\'Show Excerpt\', \'designzeen-events\'),
                \'type\' => \Elementor\Controls_Manager::SWITCHER,
                \'label_on\' => __(\'Show\', \'designzeen-events\'),
                \'label_off\' => __(\'Hide\', \'designzeen-events\'),
                \'return_value\' => \'true\',
                \'default\' => \'true\',
            ]
        );
        
        $this->add_control(
            \'button_text\',
            [
                \'label\' => __(\'Button Text\', \'designzeen-events\'),
                \'type\' => \Elementor\Controls_Manager::TEXT,
                \'default\' => __(\'View Details\', \'designzeen-events\'),
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $shortcode_atts = array(
            \'posts_per_page\' => $settings[\'posts_per_page\'],
            \'columns\' => $settings[\'columns\'],
            \'layout\' => $settings[\'layout\'],
            \'category\' => $settings[\'category\'],
            \'featured_only\' => $settings[\'featured_only\'],
            \'upcoming_only\' => $settings[\'upcoming_only\'],
            \'show_date\' => $settings[\'show_date\'],
            \'show_time\' => $settings[\'show_time\'],
            \'show_location\' => $settings[\'show_location\'],
            \'show_price\' => $settings[\'show_price\'],
            \'show_excerpt\' => $settings[\'show_excerpt\'],
            \'button_text\' => $settings[\'button_text\'],
        );
        
        echo do_shortcode(\'[zeen_events \' . http_build_query($shortcode_atts, \'\', \' \') . \']\');
    }
}';
    
    file_put_contents($plugin_path . 'includes/elementor-events-widget.php', $events_widget_content);
    
    // Single Event Widget
    $single_widget_content = '<?php
/**
 * Elementor Single Event Widget
 */

if (!defined(\'ABSPATH\')) {
    exit;
}

class DZ_Single_Event_Elementor_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return \'dz_single_event\';
    }
    
    public function get_title() {
        return __(\'Single Event\', \'designzeen-events\');
    }
    
    public function get_icon() {
        return \'eicon-single-post\';
    }
    
    public function get_categories() {
        return [\'dz-events\'];
    }
    
    public function get_keywords() {
        return [\'event\', \'single\', \'calendar\', \'zeen\'];
    }
    
    protected function _register_controls() {
        $this->start_controls_section(
            \'content_section\',
            [
                \'label\' => __(\'Content\', \'designzeen-events\'),
                \'tab\' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            \'event_id\',
            [
                \'label\' => __(\'Event\', \'designzeen-events\'),
                \'type\' => \Elementor\Controls_Manager::SELECT2,
                \'options\' => $this->get_events_options(),
                \'label_block\' => true,
            ]
        );
        
        $this->add_control(
            \'show_image\',
            [
                \'label\' => __(\'Show Image\', \'designzeen-events\'),
                \'type\' => \Elementor\Controls_Manager::SWITCHER,
                \'label_on\' => __(\'Show\', \'designzeen-events\'),
                \'label_off\' => __(\'Hide\', \'designzeen-events\'),
                \'return_value\' => \'true\',
                \'default\' => \'true\',
            ]
        );
        
        $this->add_control(
            \'show_content\',
            [
                \'label\' => __(\'Show Content\', \'designzeen-events\'),
                \'type\' => \Elementor\Controls_Manager::SWITCHER,
                \'label_on\' => __(\'Show\', \'designzeen-events\'),
                \'label_off\' => __(\'Hide\', \'designzeen-events\'),
                \'return_value\' => \'true\',
                \'default\' => \'true\',
            ]
        );
        
        $this->end_controls_section();
    }
    
    private function get_events_options() {
        $events = get_posts(array(
            \'post_type\' => \'dz_event\',
            \'posts_per_page\' => -1,
            \'post_status\' => \'publish\',
        ));
        
        $options = array();
        foreach ($events as $event) {
            $options[$event->ID] = $event->post_title;
        }
        
        return $options;
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        if (empty($settings[\'event_id\'])) {
            echo \'<p>\' . __(\'Please select an event.\', \'designzeen-events\') . \'</p>\';
            return;
        }
        
        $event_id = $settings[\'event_id\'];
        $event = get_post($event_id);
        
        if (!$event || $event->post_type !== \'dz_event\') {
            echo \'<p>\' . __(\'Event not found.\', \'designzeen-events\') . \'</p>\';
            return;
        }
        
        echo \'<div class="dz-single-event-elementor">\';
        
        if ($settings[\'show_image\'] === \'true\' && has_post_thumbnail($event_id)) {
            echo \'<div class="dz-event-image">\';
            echo get_the_post_thumbnail($event_id, \'large\');
            echo \'</div>\';
        }
        
        echo \'<div class="dz-event-content">\';
        echo \'<h2 class="dz-event-title">\' . get_the_title($event_id) . \'</h2>\';
        
        // Event meta
        $start_date = get_post_meta($event_id, \'_dz_event_start\', true);
        $location = get_post_meta($event_id, \'_dz_event_location\', true);
        $price = get_post_meta($event_id, \'_dz_event_price\', true);
        
        echo \'<div class="dz-event-meta">\';
        if ($start_date) {
            echo \'<div class="dz-meta-item">📅 \' . date(\'M j, Y\', strtotime($start_date)) . \'</div>\';
        }
        if ($location) {
            echo \'<div class="dz-meta-item">📍 \' . esc_html($location) . \'</div>\';
        }
        if ($price) {
            echo \'<div class="dz-meta-item">💰 \' . esc_html($price) . \'</div>\';
        }
        echo \'</div>\';
        
        if ($settings[\'show_content\'] === \'true\') {
            echo \'<div class="dz-event-description">\' . apply_filters(\'the_content\', $event->post_content) . \'</div>\';
        }
        
        echo \'<div class="dz-event-actions">\';
        echo \'<a href="\' . get_permalink($event_id) . \'" class="dz-btn dz-btn-primary">\' . __(\'View Full Details\', \'designzeen-events\') . \'</a>\';
        echo \'</div>\';
        
        echo \'</div></div>\';
    }
}';
    
    file_put_contents($plugin_path . 'includes/elementor-single-event-widget.php', $single_widget_content);
}

// Create Elementor widget files on plugin activation
register_activation_hook(__FILE__, 'dz_events_create_elementor_widgets');

// Global Admin Settings Page
class DZ_Events_Admin_Settings {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Zeen Events Settings', 'designzeen-events'),
            __('Zeen Events', 'designzeen-events'),
            'manage_options',
            'dz-events-settings',
            array($this, 'admin_page'),
            'dashicons-calendar-alt',
            30
        );
        
        add_submenu_page(
            'dz-events-settings',
            __('General Settings', 'designzeen-events'),
            __('General Settings', 'designzeen-events'),
            'manage_options',
            'dz-events-settings',
            array($this, 'admin_page')
        );
    }
    
    public function register_settings() {
        register_setting('dz_events_settings', 'dz_events_options');
        
        // General Settings Section
        add_settings_section(
            'dz_events_general',
            __('General Settings', 'designzeen-events'),
            array($this, 'general_section_callback'),
            'dz-events-settings'
        );
        
        add_settings_field(
            'events_per_page',
            __('Events Per Page', 'designzeen-events'),
            array($this, 'number_field_callback'),
            'dz-events-settings',
            'dz_events_general',
            array('field' => 'events_per_page', 'default' => '10', 'min' => '1', 'max' => '100')
        );
        
        add_settings_field(
            'default_date_format',
            __('Default Date Format', 'designzeen-events'),
            array($this, 'select_field_callback'),
            'dz-events-settings',
            'dz_events_general',
            array('field' => 'default_date_format', 'default' => 'M j, Y', 'options' => array(
                'M j, Y' => 'Dec 25, 2024',
                'F j, Y' => 'December 25, 2024',
                'j M Y' => '25 Dec 2024',
                'Y-m-d' => '2024-12-25',
                'm/d/Y' => '12/25/2024',
                'd/m/Y' => '25/12/2024'
            ))
        );
        
        add_settings_field(
            'default_time_format',
            __('Default Time Format', 'designzeen-events'),
            array($this, 'select_field_callback'),
            'dz-events-settings',
            'dz_events_general',
            array('field' => 'default_time_format', 'default' => 'g:i A', 'options' => array(
                'g:i A' => '2:30 PM',
                'H:i' => '14:30',
                'g:i a' => '2:30 pm'
            ))
        );
        
        add_settings_field(
            'currency_symbol',
            __('Currency Symbol', 'designzeen-events'),
            array($this, 'text_field_callback'),
            'dz-events-settings',
            'dz_events_general',
            array('field' => 'currency_symbol', 'default' => '$')
        );
        
        add_settings_field(
            'currency_position',
            __('Currency Position', 'designzeen-events'),
            array($this, 'select_field_callback'),
            'dz-events-settings',
            'dz_events_general',
            array('field' => 'currency_position', 'default' => 'before', 'options' => array(
                'before' => __('Before amount ($50)', 'designzeen-events'),
                'after' => __('After amount (50$)', 'designzeen-events')
            ))
        );
        
        // Display Settings Section
        add_settings_section(
            'dz_events_display',
            __('Display Settings', 'designzeen-events'),
            array($this, 'display_section_callback'),
            'dz-events-settings'
        );
        
        add_settings_field(
            'show_featured_badge',
            __('Show Featured Badge', 'designzeen-events'),
            array($this, 'checkbox_field_callback'),
            'dz-events-settings',
            'dz_events_display',
            array('field' => 'show_featured_badge', 'default' => '1')
        );
        
        add_settings_field(
            'featured_badge_text',
            __('Featured Badge Text', 'designzeen-events'),
            array($this, 'text_field_callback'),
            'dz-events-settings',
            'dz_events_display',
            array('field' => 'featured_badge_text', 'default' => 'Featured')
        );
        
        add_settings_field(
            'default_image_size',
            __('Default Image Size', 'designzeen-events'),
            array($this, 'select_field_callback'),
            'dz-events-settings',
            'dz_events_display',
            array('field' => 'default_image_size', 'default' => 'medium', 'options' => array(
                'thumbnail' => __('Thumbnail (150x150)', 'designzeen-events'),
                'medium' => __('Medium (300x300)', 'designzeen-events'),
                'large' => __('Large (1024x1024)', 'designzeen-events'),
                'full' => __('Full Size', 'designzeen-events')
            ))
        );
        
        add_settings_field(
            'enable_ajax_loading',
            __('Enable AJAX Loading', 'designzeen-events'),
            array($this, 'checkbox_field_callback'),
            'dz-events-settings',
            'dz_events_display',
            array('field' => 'enable_ajax_loading', 'default' => '1')
        );
        
        // SEO Settings Section
        add_settings_section(
            'dz_events_seo',
            __('SEO Settings', 'designzeen-events'),
            array($this, 'seo_section_callback'),
            'dz-events-settings'
        );
        
        add_settings_field(
            'events_page_title',
            __('Events Page Title', 'designzeen-events'),
            array($this, 'text_field_callback'),
            'dz-events-settings',
            'dz_events_seo',
            array('field' => 'events_page_title', 'default' => 'Events')
        );
        
        add_settings_field(
            'events_page_description',
            __('Events Page Description', 'designzeen-events'),
            array($this, 'textarea_field_callback'),
            'dz-events-settings',
            'dz_events_seo',
            array('field' => 'events_page_description', 'default' => 'Discover our upcoming events and join us for amazing experiences.')
        );
        
        add_settings_field(
            'enable_structured_data',
            __('Enable Structured Data', 'designzeen-events'),
            array($this, 'checkbox_field_callback'),
            'dz-events-settings',
            'dz_events_seo',
            array('field' => 'enable_structured_data', 'default' => '1')
        );
        
        // Integration Settings Section
        add_settings_section(
            'dz_events_integration',
            __('Integration Settings', 'designzeen-events'),
            array($this, 'integration_section_callback'),
            'dz-events-settings'
        );
        
        add_settings_field(
            'google_maps_api_key',
            __('Google Maps API Key', 'designzeen-events'),
            array($this, 'text_field_callback'),
            'dz-events-settings',
            'dz_events_integration',
            array('field' => 'google_maps_api_key', 'default' => '')
        );
        
        add_settings_field(
            'enable_social_sharing',
            __('Enable Social Sharing', 'designzeen-events'),
            array($this, 'checkbox_field_callback'),
            'dz-events-settings',
            'dz_events_integration',
            array('field' => 'enable_social_sharing', 'default' => '1')
        );
        
        add_settings_field(
            'facebook_app_id',
            __('Facebook App ID', 'designzeen-events'),
            array($this, 'text_field_callback'),
            'dz-events-settings',
            'dz_events_integration',
            array('field' => 'facebook_app_id', 'default' => '')
        );
    }
    
    public function general_section_callback() {
        echo '<p>' . __('Configure general settings for your events.', 'designzeen-events') . '</p>';
    }
    
    public function display_section_callback() {
        echo '<p>' . __('Customize how events are displayed on your website.', 'designzeen-events') . '</p>';
    }
    
    public function seo_section_callback() {
        echo '<p>' . __('Optimize your events for search engines.', 'designzeen-events') . '</p>';
    }
    
    public function integration_section_callback() {
        echo '<p>' . __('Configure third-party integrations and APIs.', 'designzeen-events') . '</p>';
    }
    
    public function text_field_callback($args) {
        $options = get_option('dz_events_options');
        $value = isset($options[$args['field']]) ? $options[$args['field']] : $args['default'];
        echo '<input type="text" name="dz_events_options[' . $args['field'] . ']" value="' . esc_attr($value) . '" style="width: 300px;" />';
    }
    
    public function textarea_field_callback($args) {
        $options = get_option('dz_events_options');
        $value = isset($options[$args['field']]) ? $options[$args['field']] : $args['default'];
        echo '<textarea name="dz_events_options[' . $args['field'] . ']" rows="3" style="width: 100%; max-width: 500px;">' . esc_textarea($value) . '</textarea>';
    }
    
    public function number_field_callback($args) {
        $options = get_option('dz_events_options');
        $value = isset($options[$args['field']]) ? $options[$args['field']] : $args['default'];
        $min = isset($args['min']) ? $args['min'] : '';
        $max = isset($args['max']) ? $args['max'] : '';
        echo '<input type="number" name="dz_events_options[' . $args['field'] . ']" value="' . esc_attr($value) . '" min="' . $min . '" max="' . $max . '" />';
    }
    
    public function select_field_callback($args) {
        $options = get_option('dz_events_options');
        $value = isset($options[$args['field']]) ? $options[$args['field']] : $args['default'];
        echo '<select name="dz_events_options[' . $args['field'] . ']">';
        foreach ($args['options'] as $key => $label) {
            echo '<option value="' . $key . '" ' . selected($value, $key, false) . '>' . $label . '</option>';
        }
        echo '</select>';
    }
    
    public function checkbox_field_callback($args) {
        $options = get_option('dz_events_options');
        $value = isset($options[$args['field']]) ? $options[$args['field']] : $args['default'];
        echo '<input type="checkbox" name="dz_events_options[' . $args['field'] . ']" value="1" ' . checked($value, 1, false) . ' />';
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook === 'toplevel_page_dz-events-settings') {
            wp_enqueue_style('dz-events-admin', DZ_EVENTS_PLUGIN_URL . 'assets/css/admin.css', array(), DZ_EVENTS_VERSION);
            wp_enqueue_script('dz-events-admin', DZ_EVENTS_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), DZ_EVENTS_VERSION, true);
        }
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Zeen Events Settings', 'designzeen-events'); ?></h1>
            
            <div class="dz-events-admin-header">
                <div class="dz-events-logo">
                    <h2><?php _e('Zeen Events', 'designzeen-events'); ?></h2>
                    <p><?php _e('Professional Event Management Plugin', 'designzeen-events'); ?></p>
                </div>
                <div class="dz-events-version">
                    <span class="version-badge">v<?php echo DZ_EVENTS_VERSION; ?></span>
                </div>
            </div>
            
            <div class="dz-events-admin-tabs">
                <nav class="nav-tab-wrapper">
                    <a href="#general" class="nav-tab nav-tab-active"><?php _e('General', 'designzeen-events'); ?></a>
                    <a href="#display" class="nav-tab"><?php _e('Display', 'designzeen-events'); ?></a>
                    <a href="#seo" class="nav-tab"><?php _e('SEO', 'designzeen-events'); ?></a>
                    <a href="#integration" class="nav-tab"><?php _e('Integration', 'designzeen-events'); ?></a>
                    <a href="#help" class="nav-tab"><?php _e('Help', 'designzeen-events'); ?></a>
                </nav>
            </div>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('dz_events_settings');
                do_settings_sections('dz-events-settings');
                ?>
                
                <div class="dz-events-settings-content">
                    <div id="general" class="tab-content active">
                        <div class="card">
                            <h2><?php _e('General Settings', 'designzeen-events'); ?></h2>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Events Per Page', 'designzeen-events'); ?></th>
                                    <td><?php $this->number_field_callback(array('field' => 'events_per_page', 'default' => '10', 'min' => '1', 'max' => '100')); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Default Date Format', 'designzeen-events'); ?></th>
                                    <td><?php $this->select_field_callback(array('field' => 'default_date_format', 'default' => 'M j, Y', 'options' => array(
                                        'M j, Y' => 'Dec 25, 2024',
                                        'F j, Y' => 'December 25, 2024',
                                        'j M Y' => '25 Dec 2024',
                                        'Y-m-d' => '2024-12-25',
                                        'm/d/Y' => '12/25/2024',
                                        'd/m/Y' => '25/12/2024'
                                    ))); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Default Time Format', 'designzeen-events'); ?></th>
                                    <td><?php $this->select_field_callback(array('field' => 'default_time_format', 'default' => 'g:i A', 'options' => array(
                                        'g:i A' => '2:30 PM',
                                        'H:i' => '14:30',
                                        'g:i a' => '2:30 pm'
                                    ))); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Currency Symbol', 'designzeen-events'); ?></th>
                                    <td><?php $this->text_field_callback(array('field' => 'currency_symbol', 'default' => '$')); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Currency Position', 'designzeen-events'); ?></th>
                                    <td><?php $this->select_field_callback(array('field' => 'currency_position', 'default' => 'before', 'options' => array(
                                        'before' => __('Before amount ($50)', 'designzeen-events'),
                                        'after' => __('After amount (50$)', 'designzeen-events')
                                    ))); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div id="display" class="tab-content">
                        <div class="card">
                            <h2><?php _e('Display Settings', 'designzeen-events'); ?></h2>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Show Featured Badge', 'designzeen-events'); ?></th>
                                    <td><?php $this->checkbox_field_callback(array('field' => 'show_featured_badge', 'default' => '1')); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Featured Badge Text', 'designzeen-events'); ?></th>
                                    <td><?php $this->text_field_callback(array('field' => 'featured_badge_text', 'default' => 'Featured')); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Default Image Size', 'designzeen-events'); ?></th>
                                    <td><?php $this->select_field_callback(array('field' => 'default_image_size', 'default' => 'medium', 'options' => array(
                                        'thumbnail' => __('Thumbnail (150x150)', 'designzeen-events'),
                                        'medium' => __('Medium (300x300)', 'designzeen-events'),
                                        'large' => __('Large (1024x1024)', 'designzeen-events'),
                                        'full' => __('Full Size', 'designzeen-events')
                                    ))); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Enable AJAX Loading', 'designzeen-events'); ?></th>
                                    <td><?php $this->checkbox_field_callback(array('field' => 'enable_ajax_loading', 'default' => '1')); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div id="seo" class="tab-content">
                        <div class="card">
                            <h2><?php _e('SEO Settings', 'designzeen-events'); ?></h2>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Events Page Title', 'designzeen-events'); ?></th>
                                    <td><?php $this->text_field_callback(array('field' => 'events_page_title', 'default' => 'Events')); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Events Page Description', 'designzeen-events'); ?></th>
                                    <td><?php $this->textarea_field_callback(array('field' => 'events_page_description', 'default' => 'Discover our upcoming events and join us for amazing experiences.')); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Enable Structured Data', 'designzeen-events'); ?></th>
                                    <td><?php $this->checkbox_field_callback(array('field' => 'enable_structured_data', 'default' => '1')); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div id="integration" class="tab-content">
                        <div class="card">
                            <h2><?php _e('Integration Settings', 'designzeen-events'); ?></h2>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Google Maps API Key', 'designzeen-events'); ?></th>
                                    <td><?php $this->text_field_callback(array('field' => 'google_maps_api_key', 'default' => '')); ?>
                                    <p class="description"><?php _e('Required for map integration in event locations.', 'designzeen-events'); ?></p></td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Enable Social Sharing', 'designzeen-events'); ?></th>
                                    <td><?php $this->checkbox_field_callback(array('field' => 'enable_social_sharing', 'default' => '1')); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Facebook App ID', 'designzeen-events'); ?></th>
                                    <td><?php $this->text_field_callback(array('field' => 'facebook_app_id', 'default' => '')); ?>
                                    <p class="description"><?php _e('Required for Facebook sharing integration.', 'designzeen-events'); ?></p></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div id="help" class="tab-content">
                        <div class="card">
                            <h2><?php _e('Help & Documentation', 'designzeen-events'); ?></h2>
                            <div class="dz-events-help-content">
                                <h3><?php _e('Shortcodes', 'designzeen-events'); ?></h3>
                                <p><?php _e('Use these shortcodes to display events on your website:', 'designzeen-events'); ?></p>
                                <ul>
                                    <li><code>[zeen_events]</code> - <?php _e('Display events grid', 'designzeen-events'); ?></li>
                                    <li><code>[zeen_events_featured]</code> - <?php _e('Display featured events only', 'designzeen-events'); ?></li>
                                    <li><code>[zeen_events_upcoming]</code> - <?php _e('Display upcoming events only', 'designzeen-events'); ?></li>
                                    <li><code>[zeen_events_list]</code> - <?php _e('Display events in list format', 'designzeen-events'); ?></li>
                                </ul>
                                
                                <h3><?php _e('Elementor Integration', 'designzeen-events'); ?></h3>
                                <p><?php _e('Look for the "Zeen Events" category in Elementor to find our widgets:', 'designzeen-events'); ?></p>
                                <ul>
                                    <li><?php _e('Events Grid Widget', 'designzeen-events'); ?></li>
                                    <li><?php _e('Single Event Widget', 'designzeen-events'); ?></li>
                                </ul>
                                
                                <h3><?php _e('Customization', 'designzeen-events'); ?></h3>
                                <p><?php _e('Customize your event displays:', 'designzeen-events'); ?></p>
                                <ul>
                                    <li><a href="<?php echo admin_url('edit.php?post_type=dz_event&page=dz-events-card-settings'); ?>"><?php _e('Card Settings', 'designzeen-events'); ?></a> - <?php _e('Customize card appearance', 'designzeen-events'); ?></li>
                                    <li><a href="<?php echo admin_url('edit.php?post_type=dz_event&page=dz-events-custom-fields'); ?>"><?php _e('Custom Fields', 'designzeen-events'); ?></a> - <?php _e('Add custom event fields', 'designzeen-events'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                var target = $(this).attr('href');
                
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                
                $('.tab-content').removeClass('active');
                $(target).addClass('active');
            });
        });
        </script>
        <?php
    }
}

// Initialize Admin Settings
DZ_Events_Admin_Settings::get_instance();

// Template Customizer for Advanced Layouts
class DZ_Events_Template_Customizer {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_head', array($this, 'output_custom_templates'));
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=dz_event',
            __('Template Customizer', 'designzeen-events'),
            __('Template Customizer', 'designzeen-events'),
            'manage_options',
            'dz-events-template-customizer',
            array($this, 'admin_page')
        );
    }
    
    public function register_settings() {
        register_setting('dz_events_template_settings', 'dz_events_template_options');
    }
    
    public function admin_page() {
        if (isset($_POST['save_template'])) {
            $this->save_template();
        }
        
        $templates = get_option('dz_events_template_options', array());
        ?>
        <div class="wrap">
            <h1><?php _e('Template Customizer', 'designzeen-events'); ?></h1>
            
            <div class="dz-template-customizer">
                <div class="dz-template-sidebar">
                    <h2><?php _e('Template Library', 'designzeen-events'); ?></h2>
                    
                    <div class="dz-template-categories">
                        <button class="dz-category-btn active" data-category="all"><?php _e('All Templates', 'designzeen-events'); ?></button>
                        <button class="dz-category-btn" data-category="grid"><?php _e('Grid Layouts', 'designzeen-events'); ?></button>
                        <button class="dz-category-btn" data-category="list"><?php _e('List Layouts', 'designzeen-events'); ?></button>
                        <button class="dz-category-btn" data-category="card"><?php _e('Card Styles', 'designzeen-events'); ?></button>
                        <button class="dz-category-btn" data-category="modern"><?php _e('Modern Styles', 'designzeen-events'); ?></button>
                    </div>
                    
                    <div class="dz-template-preview">
                        <h3><?php _e('Template Preview', 'designzeen-events'); ?></h3>
                        <div id="dz-template-preview-content">
                            <!-- Preview content will be loaded here -->
                        </div>
                    </div>
                </div>
                
                <div class="dz-template-main">
                    <div class="dz-template-editor">
                        <h2><?php _e('Template Editor', 'designzeen-events'); ?></h2>
                        
                        <form method="post" action="">
                            <div class="dz-template-tabs">
                                <button type="button" class="dz-tab-btn active" data-tab="html"><?php _e('HTML', 'designzeen-events'); ?></button>
                                <button type="button" class="dz-tab-btn" data-tab="css"><?php _e('CSS', 'designzeen-events'); ?></button>
                                <button type="button" class="dz-tab-btn" data-tab="settings"><?php _e('Settings', 'designzeen-events'); ?></button>
                            </div>
                            
                            <div class="dz-template-content">
                                <div id="html-tab" class="dz-tab-content active">
                                    <h3><?php _e('HTML Template', 'designzeen-events'); ?></h3>
                                    <textarea name="template_html" id="template_html" rows="20" style="width: 100%; font-family: monospace;"><?php echo isset($templates['html']) ? esc_textarea($templates['html']) : $this->get_default_html_template(); ?></textarea>
                                </div>
                                
                                <div id="css-tab" class="dz-tab-content">
                                    <h3><?php _e('CSS Styles', 'designzeen-events'); ?></h3>
                                    <textarea name="template_css" id="template_css" rows="20" style="width: 100%; font-family: monospace;"><?php echo isset($templates['css']) ? esc_textarea($templates['css']) : $this->get_default_css_template(); ?></textarea>
                                </div>
                                
                                <div id="settings-tab" class="dz-tab-content">
                                    <h3><?php _e('Template Settings', 'designzeen-events'); ?></h3>
                                    <table class="form-table">
                                        <tr>
                                            <th scope="row"><?php _e('Template Name', 'designzeen-events'); ?></th>
                                            <td><input type="text" name="template_name" value="<?php echo isset($templates['name']) ? esc_attr($templates['name']) : 'Custom Template'; ?>" style="width: 100%;" /></td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php _e('Template Description', 'designzeen-events'); ?></th>
                                            <td><textarea name="template_description" rows="3" style="width: 100%;"><?php echo isset($templates['description']) ? esc_textarea($templates['description']) : ''; ?></textarea></td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php _e('Template Category', 'designzeen-events'); ?></th>
                                            <td>
                                                <select name="template_category" style="width: 100%;">
                                                    <option value="grid" <?php selected(isset($templates['category']) ? $templates['category'] : 'grid', 'grid'); ?>><?php _e('Grid Layout', 'designzeen-events'); ?></option>
                                                    <option value="list" <?php selected(isset($templates['category']) ? $templates['category'] : 'grid', 'list'); ?>><?php _e('List Layout', 'designzeen-events'); ?></option>
                                                    <option value="card" <?php selected(isset($templates['category']) ? $templates['category'] : 'grid', 'card'); ?>><?php _e('Card Style', 'designzeen-events'); ?></option>
                                                    <option value="modern" <?php selected(isset($templates['category']) ? $templates['category'] : 'grid', 'modern'); ?>><?php _e('Modern Style', 'designzeen-events'); ?></option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php _e('Enable Template', 'designzeen-events'); ?></th>
                                            <td><input type="checkbox" name="template_enabled" value="1" <?php checked(isset($templates['enabled']) ? $templates['enabled'] : 1, 1); ?> /></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="dz-template-actions">
                                <button type="submit" name="save_template" class="button button-primary"><?php _e('Save Template', 'designzeen-events'); ?></button>
                                <button type="button" id="preview-template" class="button"><?php _e('Preview', 'designzeen-events'); ?></button>
                                <button type="button" id="reset-template" class="button"><?php _e('Reset to Default', 'designzeen-events'); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .dz-template-customizer {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        
        .dz-template-sidebar {
            width: 300px;
            background: #fff;
            padding: 20px;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }
        
        .dz-template-main {
            flex: 1;
            background: #fff;
            padding: 20px;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }
        
        .dz-template-categories {
            margin-bottom: 20px;
        }
        
        .dz-category-btn {
            display: block;
            width: 100%;
            padding: 8px 12px;
            margin-bottom: 5px;
            background: #f1f1f1;
            border: 1px solid #ddd;
            border-radius: 3px;
            cursor: pointer;
            text-align: left;
        }
        
        .dz-category-btn.active,
        .dz-category-btn:hover {
            background: #0073aa;
            color: #fff;
        }
        
        .dz-template-tabs {
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .dz-tab-btn {
            padding: 10px 20px;
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            margin-right: 10px;
        }
        
        .dz-tab-btn.active {
            border-bottom-color: #0073aa;
            color: #0073aa;
        }
        
        .dz-tab-content {
            display: none;
        }
        
        .dz-tab-content.active {
            display: block;
        }
        
        .dz-template-actions {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        
        .dz-template-actions .button {
            margin-right: 10px;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Tab switching
            $('.dz-tab-btn').on('click', function() {
                var tab = $(this).data('tab');
                
                $('.dz-tab-btn').removeClass('active');
                $(this).addClass('active');
                
                $('.dz-tab-content').removeClass('active');
                $('#' + tab + '-tab').addClass('active');
            });
            
            // Category filtering
            $('.dz-category-btn').on('click', function() {
                $('.dz-category-btn').removeClass('active');
                $(this).addClass('active');
                
                var category = $(this).data('category');
                // Filter templates by category
                filterTemplates(category);
            });
            
            // Preview template
            $('#preview-template').on('click', function() {
                var html = $('#template_html').val();
                var css = $('#template_css').val();
                
                var preview = '<style>' + css + '</style>' + html;
                $('#dz-template-preview-content').html(preview);
            });
            
            // Reset template
            $('#reset-template').on('click', function() {
                if (confirm('<?php _e('Are you sure you want to reset to default template?', 'designzeen-events'); ?>')) {
                    $('#template_html').val('<?php echo esc_js($this->get_default_html_template()); ?>');
                    $('#template_css').val('<?php echo esc_js($this->get_default_css_template()); ?>');
                }
            });
            
            function filterTemplates(category) {
                // This would filter the template library
                // For now, just show all templates
            }
        });
        </script>
        <?php
    }
    
    private function save_template() {
        $template_data = array(
            'name' => sanitize_text_field($_POST['template_name']),
            'description' => sanitize_textarea_field($_POST['template_description']),
            'category' => sanitize_text_field($_POST['template_category']),
            'enabled' => isset($_POST['template_enabled']) ? 1 : 0,
            'html' => wp_kses_post($_POST['template_html']),
            'css' => wp_strip_all_tags($_POST['template_css'])
        );
        
        update_option('dz_events_template_options', $template_data);
        
        echo '<div class="notice notice-success"><p>' . __('Template saved successfully!', 'designzeen-events') . '</p></div>';
    }
    
    private function get_default_html_template() {
        return '<div class="dz-event-card">
    <div class="dz-event-image">
        <a href="{{event_url}}">
            {{event_image}}
        </a>
    </div>
    <div class="dz-event-content">
        <h3 class="dz-event-title">
            <a href="{{event_url}}">{{event_title}}</a>
        </h3>
        <div class="dz-event-meta">
            <div class="dz-meta-item dz-meta-date">📅 {{event_date}}</div>
            <div class="dz-meta-item dz-meta-time">🕐 {{event_time}}</div>
            <div class="dz-meta-item dz-meta-location">📍 {{event_location}}</div>
            <div class="dz-meta-item dz-meta-price">💰 {{event_price}}</div>
        </div>
        <div class="dz-event-excerpt">{{event_excerpt}}</div>
        <div class="dz-event-actions">
            <a href="{{event_url}}" class="dz-btn dz-btn-primary">View Details</a>
        </div>
    </div>
</div>';
    }
    
    private function get_default_css_template() {
        return '.dz-event-card {
    background: #fff;
    border: 1px solid #e1e1e1;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    position: relative;
}

.dz-event-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.dz-event-image {
    position: relative;
    overflow: hidden;
}

.dz-event-image img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.dz-event-card:hover .dz-event-image img {
    transform: scale(1.05);
}

.dz-event-content {
    padding: 20px;
}

.dz-event-title {
    margin: 0 0 15px 0;
    font-size: 1.25rem;
    line-height: 1.3;
}

.dz-event-title a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s ease;
}

.dz-event-title a:hover {
    color: #0073aa;
}

.dz-event-meta {
    margin: 15px 0;
    font-size: 0.9rem;
    color: #666;
}

.dz-meta-item {
    margin: 5px 0;
    display: flex;
    align-items: center;
}

.dz-event-excerpt {
    margin: 15px 0;
    color: #666;
    line-height: 1.5;
}

.dz-event-actions {
    margin-top: 20px;
}

.dz-btn {
    display: inline-block;
    padding: 10px 20px;
    background: #0073aa;
    color: #fff;
    text-decoration: none;
    border-radius: 4px;
    transition: background 0.3s ease;
    border: none;
    cursor: pointer;
    font-size: 0.9rem;
}

.dz-btn:hover {
    background: #005a87;
    color: #fff;
}';
    }
    
    public function output_custom_templates() {
        $templates = get_option('dz_events_template_options');
        
        if (!$templates || !$templates['enabled']) {
            return;
        }
        
        if (!empty($templates['css'])) {
            echo '<style type="text/css" id="dz-events-custom-template">';
            echo $templates['css'];
            echo '</style>';
        }
    }
}

// Initialize Template Customizer
DZ_Events_Template_Customizer::get_instance();
