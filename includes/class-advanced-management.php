<?php
/**
 * Advanced Event Management for Zeen Events
 * 
 * This file implements advanced event management features
 * including recurring events, event series, and bulk operations
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Advanced Event Management Class
 * 
 * Handles advanced event management functionality
 */
class DZ_Events_Advanced_Management {
    
    private static $instance = null;
    private $db;
    
    /**
     * Singleton pattern
     */
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        
        add_action('init', [$this, 'init_advanced_management']);
        add_action('admin_menu', [$this, 'add_advanced_admin_menu']);
        add_action('wp_ajax_dz_events_bulk_operations', [$this, 'ajax_bulk_operations']);
        add_action('wp_ajax_dz_events_create_series', [$this, 'ajax_create_series']);
        add_action('wp_ajax_dz_events_duplicate_event', [$this, 'ajax_duplicate_event']);
    }
    
    /**
     * Initialize advanced management
     */
    public function init_advanced_management() {
        // Add recurring event support
        add_action('save_post_dz_event', [$this, 'handle_recurring_events'], 10, 2);
        
        // Add event series support
        add_action('dz_events_create_series', [$this, 'create_event_series'], 10, 2);
        
        // Add bulk operations
        add_action('dz_events_bulk_update', [$this, 'bulk_update_events'], 10, 2);
        
        // Add event templates
        add_action('dz_events_save_template', [$this, 'save_event_template'], 10, 2);
        add_action('dz_events_load_template', [$this, 'load_event_template'], 10, 2);
    }
    
    /**
     * Add advanced admin menu
     */
    public function add_advanced_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=dz_event',
            'Advanced Management',
            'Advanced',
            'manage_options',
            'dz-events-advanced',
            [$this, 'advanced_management_page']
        );
        
        add_submenu_page(
            'edit.php?post_type=dz_event',
            'Event Templates',
            'Templates',
            'manage_options',
            'dz-events-templates',
            [$this, 'event_templates_page']
        );
        
        add_submenu_page(
            'edit.php?post_type=dz_event',
            'Bulk Operations',
            'Bulk Operations',
            'manage_options',
            'dz-events-bulk',
            [$this, 'bulk_operations_page']
        );
    }
    
    /**
     * Handle recurring events
     */
    public function handle_recurring_events($post_id, $post) {
        if ($post->post_type !== 'dz_event') {
            return;
        }
        
        $recurring_type = get_post_meta($post_id, '_dz_recurring_type', true);
        $recurring_end = get_post_meta($post_id, '_dz_recurring_end', true);
        
        if (!$recurring_type || $recurring_type === 'none') {
            return;
        }
        
        $this->create_recurring_events($post_id, $recurring_type, $recurring_end);
    }
    
    /**
     * Create recurring events
     */
    private function create_recurring_events($original_id, $type, $end_date) {
        $original_event = get_post($original_id);
        $start_date = get_post_meta($original_id, '_dz_event_start', true);
        $start_time = get_post_meta($original_id, '_dz_event_time_start', true);
        
        if (!$start_date) {
            return;
        }
        
        $current_date = new DateTime($start_date);
        $end_datetime = new DateTime($end_date);
        $created_events = [];
        
        while ($current_date <= $end_datetime) {
            // Calculate next occurrence
            switch ($type) {
                case 'daily':
                    $current_date->add(new DateInterval('P1D'));
                    break;
                case 'weekly':
                    $current_date->add(new DateInterval('P1W'));
                    break;
                case 'monthly':
                    $current_date->add(new DateInterval('P1M'));
                    break;
                case 'yearly':
                    $current_date->add(new DateInterval('P1Y'));
                    break;
                default:
                    return;
            }
            
            if ($current_date > $end_datetime) {
                break;
            }
            
            // Create new event
            $new_event_id = $this->duplicate_event($original_id, [
                'post_title' => $original_event->post_title . ' - ' . $current_date->format('M j, Y'),
                'start_date' => $current_date->format('Y-m-d'),
                'start_time' => $start_time,
                'recurring_type' => 'none', // Don't make copies recurring
                'parent_event' => $original_id
            ]);
            
            if ($new_event_id) {
                $created_events[] = $new_event_id;
            }
        }
        
        // Update original event with series info
        update_post_meta($original_id, '_dz_recurring_series', $created_events);
        
        return $created_events;
    }
    
    /**
     * Create event series
     */
    public function create_event_series($template_id, $series_data) {
        $template = get_post($template_id);
        if (!$template || $template->post_type !== 'dz_event') {
            return false;
        }
        
        $events = [];
        $start_date = new DateTime($series_data['start_date']);
        $end_date = new DateTime($series_data['end_date']);
        $interval = $series_data['interval'] ?? 1;
        $frequency = $series_data['frequency'] ?? 'weekly';
        
        while ($start_date <= $end_date) {
            $event_id = $this->duplicate_event($template_id, [
                'post_title' => $template->post_title . ' - ' . $start_date->format('M j, Y'),
                'start_date' => $start_date->format('Y-m-d'),
                'start_time' => $series_data['start_time'] ?? get_post_meta($template_id, '_dz_event_time_start', true),
                'series_id' => uniqid('series_'),
                'series_position' => count($events) + 1
            ]);
            
            if ($event_id) {
                $events[] = $event_id;
            }
            
            // Calculate next date
            switch ($frequency) {
                case 'daily':
                    $start_date->add(new DateInterval("P{$interval}D"));
                    break;
                case 'weekly':
                    $start_date->add(new DateInterval("P{$interval}W"));
                    break;
                case 'monthly':
                    $start_date->add(new DateInterval("P{$interval}M"));
                    break;
            }
        }
        
        return $events;
    }
    
    /**
     * Duplicate event
     */
    public function duplicate_event($original_id, $overrides = []) {
        $original = get_post($original_id);
        if (!$original) {
            return false;
        }
        
        // Create new post
        $new_post = [
            'post_title' => $overrides['post_title'] ?? $original->post_title . ' (Copy)',
            'post_content' => $original->post_content,
            'post_excerpt' => $original->post_excerpt,
            'post_status' => $original->post_status,
            'post_type' => $original->post_type,
            'post_author' => get_current_user_id()
        ];
        
        $new_id = wp_insert_post($new_post);
        if (!$new_id) {
            return false;
        }
        
        // Copy meta data
        $meta_keys = [
            '_dz_event_start',
            '_dz_event_end',
            '_dz_event_time_start',
            '_dz_event_time_end',
            '_dz_event_price',
            '_dz_event_location',
            '_dz_event_capacity',
            '_dz_event_status',
            '_dz_event_featured',
            '_dz_event_contact',
            '_dz_event_external_url'
        ];
        
        foreach ($meta_keys as $key) {
            $value = get_post_meta($original_id, $key, true);
            if ($value) {
                update_post_meta($new_id, $key, $value);
            }
        }
        
        // Apply overrides
        foreach ($overrides as $key => $value) {
            if ($key === 'post_title') {
                wp_update_post(['ID' => $new_id, 'post_title' => $value]);
            } else {
                update_post_meta($new_id, '_dz_' . $key, $value);
            }
        }
        
        // Copy taxonomies
        $taxonomies = get_object_taxonomies($original->post_type);
        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_object_terms($original_id, $taxonomy);
            if (!is_wp_error($terms) && !empty($terms)) {
                $term_ids = wp_list_pluck($terms, 'term_id');
                wp_set_object_terms($new_id, $term_ids, $taxonomy);
            }
        }
        
        return $new_id;
    }
    
    /**
     * Bulk update events
     */
    public function bulk_update_events($event_ids, $updates) {
        $updated = 0;
        $errors = [];
        
        foreach ($event_ids as $event_id) {
            try {
                // Update post
                if (isset($updates['post_status'])) {
                    wp_update_post([
                        'ID' => $event_id,
                        'post_status' => $updates['post_status']
                    ]);
                }
                
                // Update meta
                foreach ($updates['meta'] ?? [] as $key => $value) {
                    update_post_meta($event_id, '_dz_' . $key, $value);
                }
                
                // Update taxonomies
                foreach ($updates['taxonomies'] ?? [] as $taxonomy => $term_ids) {
                    wp_set_object_terms($event_id, $term_ids, $taxonomy);
                }
                
                $updated++;
                
            } catch (Exception $e) {
                $errors[] = "Event {$event_id}: " . $e->getMessage();
            }
        }
        
        return [
            'updated' => $updated,
            'errors' => $errors
        ];
    }
    
    /**
     * Save event template
     */
    public function save_event_template($event_id, $template_name) {
        $event = get_post($event_id);
        if (!$event || $event->post_type !== 'dz_event') {
            return false;
        }
        
        $template_data = [
            'name' => $template_name,
            'post_title' => $event->post_title,
            'post_content' => $event->post_content,
            'post_excerpt' => $event->post_excerpt,
            'meta' => [],
            'taxonomies' => []
        ];
        
        // Save meta data
        $meta_keys = [
            '_dz_event_start',
            '_dz_event_end',
            '_dz_event_time_start',
            '_dz_event_time_end',
            '_dz_event_price',
            '_dz_event_location',
            '_dz_event_capacity',
            '_dz_event_status',
            '_dz_event_featured',
            '_dz_event_contact',
            '_dz_event_external_url'
        ];
        
        foreach ($meta_keys as $key) {
            $value = get_post_meta($event_id, $key, true);
            if ($value) {
                $template_data['meta'][$key] = $value;
            }
        }
        
        // Save taxonomies
        $taxonomies = get_object_taxonomies($event->post_type);
        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_object_terms($event_id, $taxonomy);
            if (!is_wp_error($terms) && !empty($terms)) {
                $template_data['taxonomies'][$taxonomy] = wp_list_pluck($terms, 'term_id');
            }
        }
        
        // Save template
        $templates = get_option('dz_event_templates', []);
        $templates[] = $template_data;
        update_option('dz_event_templates', $templates);
        
        return true;
    }
    
    /**
     * Load event template
     */
    public function load_event_template($template_id, $event_id) {
        $templates = get_option('dz_event_templates', []);
        
        if (!isset($templates[$template_id])) {
            return false;
        }
        
        $template = $templates[$template_id];
        
        // Update post
        wp_update_post([
            'ID' => $event_id,
            'post_title' => $template['post_title'],
            'post_content' => $template['post_content'],
            'post_excerpt' => $template['post_excerpt']
        ]);
        
        // Update meta
        foreach ($template['meta'] as $key => $value) {
            update_post_meta($event_id, $key, $value);
        }
        
        // Update taxonomies
        foreach ($template['taxonomies'] as $taxonomy => $term_ids) {
            wp_set_object_terms($event_id, $term_ids, $taxonomy);
        }
        
        return true;
    }
    
    /**
     * Advanced management page
     */
    public function advanced_management_page() {
        ?>
        <div class="wrap">
            <h1>Advanced Event Management</h1>
            
            <div class="dz-advanced-tabs">
                <nav class="nav-tab-wrapper">
                    <a href="#recurring" class="nav-tab nav-tab-active">Recurring Events</a>
                    <a href="#series" class="nav-tab">Event Series</a>
                    <a href="#templates" class="nav-tab">Templates</a>
                    <a href="#bulk" class="nav-tab">Bulk Operations</a>
                </nav>
                
                <div id="recurring" class="tab-content active">
                    <h2>Recurring Events</h2>
                    <p>Create events that repeat automatically based on your schedule.</p>
                    
                    <div class="dz-recurring-form">
                        <h3>Create Recurring Event</h3>
                        <form id="dz-recurring-form">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Base Event</th>
                                    <td>
                                        <select id="base_event" name="base_event" required>
                                            <option value="">Select an event...</option>
                                            <?php
                                            $events = get_posts([
                                                'post_type' => 'dz_event',
                                                'posts_per_page' => -1,
                                                'post_status' => 'publish'
                                            ]);
                                            foreach ($events as $event) {
                                                echo '<option value="' . $event->ID . '">' . esc_html($event->post_title) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Recurrence Type</th>
                                    <td>
                                        <select id="recurring_type" name="recurring_type" required>
                                            <option value="daily">Daily</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly">Monthly</option>
                                            <option value="yearly">Yearly</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">End Date</th>
                                    <td>
                                        <input type="date" id="recurring_end" name="recurring_end" required>
                                    </td>
                                </tr>
                            </table>
                            <p class="submit">
                                <input type="submit" class="button button-primary" value="Create Recurring Events">
                            </p>
                        </form>
                    </div>
                </div>
                
                <div id="series" class="tab-content">
                    <h2>Event Series</h2>
                    <p>Create a series of related events from a template.</p>
                    
                    <div class="dz-series-form">
                        <h3>Create Event Series</h3>
                        <form id="dz-series-form">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Template Event</th>
                                    <td>
                                        <select id="template_event" name="template_event" required>
                                            <option value="">Select a template...</option>
                                            <?php
                                            foreach ($events as $event) {
                                                echo '<option value="' . $event->ID . '">' . esc_html($event->post_title) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Start Date</th>
                                    <td>
                                        <input type="date" id="series_start" name="series_start" required>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">End Date</th>
                                    <td>
                                        <input type="date" id="series_end" name="series_end" required>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Frequency</th>
                                    <td>
                                        <select id="series_frequency" name="series_frequency" required>
                                            <option value="daily">Daily</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly">Monthly</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Interval</th>
                                    <td>
                                        <input type="number" id="series_interval" name="series_interval" value="1" min="1" max="12">
                                        <p class="description">Every X days/weeks/months</p>
                                    </td>
                                </tr>
                            </table>
                            <p class="submit">
                                <input type="submit" class="button button-primary" value="Create Series">
                            </p>
                        </form>
                    </div>
                </div>
                
                <div id="templates" class="tab-content">
                    <h2>Event Templates</h2>
                    <p>Save and reuse event configurations.</p>
                    
                    <div class="dz-templates-list">
                        <h3>Saved Templates</h3>
                        <div id="templates-container">
                            <!-- Templates will be loaded here -->
                        </div>
                    </div>
                </div>
                
                <div id="bulk" class="tab-content">
                    <h2>Bulk Operations</h2>
                    <p>Perform operations on multiple events at once.</p>
                    
                    <div class="dz-bulk-operations">
                        <h3>Select Events</h3>
                        <div class="dz-event-selector">
                            <input type="text" id="bulk-search" placeholder="Search events...">
                            <div id="bulk-events-list">
                                <!-- Events will be loaded here -->
                            </div>
                        </div>
                        
                        <div class="dz-bulk-actions">
                            <h3>Bulk Actions</h3>
                            <form id="dz-bulk-form">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">Status</th>
                                        <td>
                                            <select id="bulk_status" name="bulk_status">
                                                <option value="">No change</option>
                                                <option value="publish">Published</option>
                                                <option value="draft">Draft</option>
                                                <option value="private">Private</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Featured</th>
                                        <td>
                                            <select id="bulk_featured" name="bulk_featured">
                                                <option value="">No change</option>
                                                <option value="1">Featured</option>
                                                <option value="0">Not Featured</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Category</th>
                                        <td>
                                            <select id="bulk_category" name="bulk_category">
                                                <option value="">No change</option>
                                                <?php
                                                $categories = get_terms([
                                                    'taxonomy' => 'dz_event_category',
                                                    'hide_empty' => false
                                                ]);
                                                foreach ($categories as $category) {
                                                    echo '<option value="' . $category->term_id . '">' . esc_html($category->name) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                                <p class="submit">
                                    <input type="submit" class="button button-primary" value="Apply to Selected Events">
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .dz-advanced-tabs .tab-content {
            display: none;
            padding: 20px 0;
        }
        
        .dz-advanced-tabs .tab-content.active {
            display: block;
        }
        
        .dz-recurring-form,
        .dz-series-form,
        .dz-bulk-operations {
            background: #fff;
            padding: 20px;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            margin: 20px 0;
        }
        
        .dz-event-selector {
            margin: 20px 0;
        }
        
        .dz-bulk-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ccd0d4;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Tab switching
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                var target = $(this).attr('href');
                
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                
                $('.tab-content').removeClass('active');
                $(target).addClass('active');
            });
            
            // Recurring form
            $('#dz-recurring-form').on('submit', function(e) {
                e.preventDefault();
                // Implementation for recurring events
            });
            
            // Series form
            $('#dz-series-form').on('submit', function(e) {
                e.preventDefault();
                // Implementation for event series
            });
            
            // Bulk operations
            $('#dz-bulk-form').on('submit', function(e) {
                e.preventDefault();
                // Implementation for bulk operations
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX bulk operations
     */
    public function ajax_bulk_operations() {
        check_ajax_referer('dz_bulk_operations_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Access denied');
        }
        
        $event_ids = array_map('intval', $_POST['event_ids']);
        $updates = $_POST['updates'];
        
        $result = $this->bulk_update_events($event_ids, $updates);
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX create series
     */
    public function ajax_create_series() {
        check_ajax_referer('dz_create_series_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Access denied');
        }
        
        $template_id = intval($_POST['template_id']);
        $series_data = $_POST['series_data'];
        
        $events = $this->create_event_series($template_id, $series_data);
        
        wp_send_json_success(['events' => $events]);
    }
    
    /**
     * AJAX duplicate event
     */
    public function ajax_duplicate_event() {
        check_ajax_referer('dz_duplicate_event_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Access denied');
        }
        
        $event_id = intval($_POST['event_id']);
        $overrides = $_POST['overrides'] ?? [];
        
        $new_id = $this->duplicate_event($event_id, $overrides);
        
        if ($new_id) {
            wp_send_json_success(['new_id' => $new_id]);
        } else {
            wp_send_json_error('Failed to duplicate event');
        }
    }
}

/**
 * Initialize advanced event management
 */
function dz_events_init_advanced_management() {
    return DZ_Events_Advanced_Management::instance();
}
add_action('init', 'dz_events_init_advanced_management');
