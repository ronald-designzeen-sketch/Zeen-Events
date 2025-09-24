<?php
/**
 * Zeen Events Core - Simplified Algorithm Architecture
 * 
 * This file implements the new clean, simple algorithm approach
 * following the principle: Data → Service → Renderer
 * 
 * @package ZeenEvents
 * @version 2.0.0
 * @copyright 2024 Design Zeen Agency
 * @license GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Core Class - Single Entry Point
 * 
 * This class follows the Single Responsibility Principle
 * and provides a clean, simple interface for all event operations
 */
class DZ_Events_Core {
    
    private static $instance = null;
    private $data;
    private $service;
    private $renderer;
    
    /**
     * Singleton pattern for global access
     */
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor - Initialize the three core layers
     */
    private function __construct() {
        $this->data = new DZ_Events_Data();
        $this->service = new DZ_Events_Service($this->data);
        $this->renderer = new DZ_Events_Renderer();
    }
    
    /**
     * Main entry point for displaying events
     * 
     * @param array $params Event display parameters
     * @return string Rendered HTML
     */
    public function display_events($params) {
        // Simple 3-step process
        $events = $this->data->get_events($params);
        $processed = $this->service->process($events, $params);
        return $this->renderer->render($processed, $params);
    }
    
    /**
     * Get single event data
     * 
     * @param int $event_id Event ID
     * @return object Event data
     */
    public function get_event($event_id) {
        return $this->data->get_event($event_id);
    }
    
    /**
     * Search events
     * 
     * @param string $term Search term
     * @param array $filters Additional filters
     * @return array Search results
     */
    public function search_events($term, $filters = []) {
        return $this->data->search_events($term, $filters);
    }
}

/**
 * Data Layer - Pure data operations
 * 
 * This class handles all database operations and data retrieval
 * No business logic, just data access
 */
class DZ_Events_Data {
    
    private $cache;
    private $query_builder;
    
    public function __construct() {
        $this->cache = new DZ_Events_Cache();
        $this->query_builder = new DZ_Query_Builder();
    }
    
    /**
     * Get events with filters
     * 
     * @param array $filters Query filters
     * @return WP_Query Query results
     */
    public function get_events($filters = []) {
        $cache_key = 'dz_events_' . md5(serialize($filters));
        
        // Try cache first
        if ($cached = $this->cache->get($cache_key)) {
            return $cached;
        }
        
        // Build and execute query
        $query = $this->query_builder
            ->post_type('dz_event')
            ->status('publish')
            ->apply_filters($filters)
            ->execute();
        
        // Cache results
        $this->cache->set($cache_key, $query, HOUR_IN_SECONDS);
        
        return $query;
    }
    
    /**
     * Get single event
     * 
     * @param int $event_id Event ID
     * @return object Event data
     */
    public function get_event($event_id) {
        $cache_key = "dz_event_{$event_id}";
        
        if ($cached = $this->cache->get($cache_key)) {
            return $cached;
        }
        
        $event = get_post($event_id);
        if (!$event || $event->post_type !== 'dz_event') {
            return null;
        }
        
        // Add meta data
        $event->meta = $this->get_event_meta($event_id);
        $event->categories = $this->get_event_categories($event_id);
        
        $this->cache->set($cache_key, $event, HOUR_IN_SECONDS);
        
        return $event;
    }
    
    /**
     * Search events
     * 
     * @param string $term Search term
     * @param array $filters Additional filters
     * @return WP_Query Search results
     */
    public function search_events($term, $filters = []) {
        $filters['search'] = $term;
        return $this->get_events($filters);
    }
    
    /**
     * Get event meta data
     * 
     * @param int $event_id Event ID
     * @return array Meta data
     */
    private function get_event_meta($event_id) {
        $meta_keys = [
            '_dz_event_start',
            '_dz_event_end',
            '_dz_event_time_start',
            '_dz_event_time_end',
            '_dz_event_price',
            '_dz_event_location',
            '_dz_event_capacity',
            '_dz_event_status',
            '_dz_event_external_url',
            '_dz_event_featured'
        ];
        
        $meta = [];
        foreach ($meta_keys as $key) {
            $meta[str_replace('_dz_event_', '', $key)] = get_post_meta($event_id, $key, true);
        }
        
        return $meta;
    }
    
    /**
     * Get event categories
     * 
     * @param int $event_id Event ID
     * @return array Categories
     */
    private function get_event_categories($event_id) {
        return get_the_terms($event_id, 'dz_event_category') ?: [];
    }
}

/**
 * Service Layer - Business logic
 * 
 * This class handles all business rules and data processing
 * No data access, no presentation logic
 */
class DZ_Events_Service {
    
    private $data;
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    /**
     * Process events for display
     * 
     * @param WP_Query $events Raw event data
     * @param array $params Display parameters
     * @return array Processed events
     */
    public function process($events, $params) {
        $processed = [];
        
        if (!$events->have_posts()) {
            return $processed;
        }
        
        while ($events->have_posts()) {
            $events->the_post();
            $event = $this->process_single_event(get_the_ID(), $params);
            $processed[] = $event;
        }
        
        wp_reset_postdata();
        
        return $processed;
    }
    
    /**
     * Process single event
     * 
     * @param int $event_id Event ID
     * @param array $params Display parameters
     * @return object Processed event
     */
    private function process_single_event($event_id, $params) {
        $event = $this->data->get_event($event_id);
        
        if (!$event) {
            return null;
        }
        
        // Add computed fields
        $event->formatted_date = $this->format_date($event->meta['start'], $event->meta['end']);
        $event->formatted_time = $this->format_time($event->meta['time_start'], $event->meta['time_end']);
        $event->status_class = 'dz-event-status-' . $event->meta['status'];
        $event->is_featured = $event->meta['featured'] === '1';
        $event->is_past = $this->is_past_event($event->meta['start']);
        $event->permalink = get_permalink($event_id);
        $event->thumbnail = get_the_post_thumbnail_url($event_id, 'medium');
        $event->excerpt = wp_trim_words(get_the_excerpt(), 20, '...');
        
        return $event;
    }
    
    /**
     * Format event date
     * 
     * @param string $start Start date
     * @param string $end End date
     * @return string Formatted date
     */
    private function format_date($start, $end) {
        if (!$start) return '';
        
        $start_formatted = date('M j, Y', strtotime($start));
        
        if ($end && $end !== $start) {
            $end_formatted = date('M j, Y', strtotime($end));
            return $start_formatted . ' - ' . $end_formatted;
        }
        
        return $start_formatted;
    }
    
    /**
     * Format event time
     * 
     * @param string $start Start time
     * @param string $end End time
     * @return string Formatted time
     */
    private function format_time($start, $end) {
        if (!$start) return '';
        
        $start_formatted = date('g:i A', strtotime($start));
        
        if ($end && $end !== $start) {
            $end_formatted = date('g:i A', strtotime($end));
            return $start_formatted . ' - ' . $end_formatted;
        }
        
        return $start_formatted;
    }
    
    /**
     * Check if event is past
     * 
     * @param string $start_date Start date
     * @return bool Is past event
     */
    private function is_past_event($start_date) {
        if (!$start_date) return false;
        
        $event_date = new DateTime($start_date);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        return $event_date < $today;
    }
}

/**
 * Renderer Layer - Presentation logic
 * 
 * This class handles all HTML generation and template rendering
 * No business logic, no data access
 */
class DZ_Events_Renderer {
    
    /**
     * Render events
     * 
     * @param array $events Processed events
     * @param array $params Display parameters
     * @return string Rendered HTML
     */
    public function render($events, $params) {
        if (empty($events)) {
            return $this->render_no_events();
        }
        
        $layout = $params['layout'] ?? 'grid';
        $wrapper_class = "dz-events-wrapper dz-events-{$layout}";
        
        ob_start();
        echo '<div class="' . esc_attr($wrapper_class) . '">';
        
        foreach ($events as $event) {
            echo $this->render_single_event($event, $params);
        }
        
        echo '</div>';
        
        return ob_get_clean();
    }
    
    /**
     * Render single event
     * 
     * @param object $event Event data
     * @param array $params Display parameters
     * @return string Rendered HTML
     */
    private function render_single_event($event, $params) {
        $card_class = "dz-event-card {$event->status_class}";
        if ($event->is_featured) {
            $card_class .= ' dz-event-featured';
        }
        
        ob_start();
        ?>
        <div class="<?php echo esc_attr($card_class); ?>">
            <?php if ($event->thumbnail) : ?>
                <div class="dz-event-thumb">
                    <img src="<?php echo esc_url($event->thumbnail); ?>" alt="<?php echo esc_attr($event->post_title); ?>">
                    <?php if ($event->is_featured) : ?>
                        <span class="dz-event-badge dz-event-featured"><?php _e('Featured', 'designzeen-events'); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="dz-event-content">
                <h3 class="dz-event-title">
                    <a href="<?php echo esc_url($event->permalink); ?>"><?php echo esc_html($event->post_title); ?></a>
                </h3>
                
                <div class="dz-event-meta">
                    <?php if ($event->formatted_date) : ?>
                        <p class="dz-event-date">
                            <i class="fas fa-calendar-alt"></i>
                            <?php echo esc_html($event->formatted_date); ?>
                        </p>
                    <?php endif; ?>
                    
                    <?php if ($event->formatted_time) : ?>
                        <p class="dz-event-time">
                            <i class="fas fa-clock"></i>
                            <?php echo esc_html($event->formatted_time); ?>
                        </p>
                    <?php endif; ?>
                    
                    <?php if ($event->meta['location']) : ?>
                        <p class="dz-event-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo esc_html($event->meta['location']); ?>
                        </p>
                    <?php endif; ?>
                    
                    <?php if ($event->meta['price']) : ?>
                        <p class="dz-event-price">
                            <i class="fas fa-tag"></i>
                            <?php echo esc_html($event->meta['price']); ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <p class="dz-event-excerpt"><?php echo esc_html($event->excerpt); ?></p>
                
                <div class="dz-event-actions">
                    <a href="<?php echo esc_url($event->permalink); ?>" class="dz-event-btn dz-btn-primary">
                        <?php _e('View Details', 'designzeen-events'); ?>
                    </a>
                    <?php if ($event->meta['external_url']) : ?>
                        <a href="<?php echo esc_url($event->meta['external_url']); ?>" class="dz-event-btn dz-btn-secondary" target="_blank" rel="noopener">
                            <?php _e('Get Tickets', 'designzeen-events'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Render no events message
     * 
     * @return string Rendered HTML
     */
    private function render_no_events() {
        return '<p class="dz-no-events">' . esc_html__('No events found.', 'designzeen-events') . '</p>';
    }
}

/**
 * Simple Query Builder
 * 
 * This class provides a clean, fluent interface for building WordPress queries
 */
class DZ_Query_Builder {
    
    private $query = [];
    
    /**
     * Set post type
     * 
     * @param string $post_type Post type
     * @return DZ_Query_Builder
     */
    public function post_type($post_type) {
        $this->query['post_type'] = $post_type;
        return $this;
    }
    
    /**
     * Set post status
     * 
     * @param string $status Post status
     * @return DZ_Query_Builder
     */
    public function status($status) {
        $this->query['post_status'] = $status;
        return $this;
    }
    
    /**
     * Set posts per page
     * 
     * @param int $count Number of posts
     * @return DZ_Query_Builder
     */
    public function limit($count) {
        $this->query['posts_per_page'] = $count;
        return $this;
    }
    
    /**
     * Set order by
     * 
     * @param string $field Field to order by
     * @param string $direction Order direction
     * @return DZ_Query_Builder
     */
    public function order_by($field, $direction = 'ASC') {
        $this->query['orderby'] = $field;
        $this->query['order'] = strtoupper($direction);
        return $this;
    }
    
    /**
     * Add meta query
     * 
     * @param string $key Meta key
     * @param mixed $value Meta value
     * @param string $compare Comparison operator
     * @return DZ_Query_Builder
     */
    public function where_meta($key, $value, $compare = '=') {
        if (!isset($this->query['meta_query'])) {
            $this->query['meta_query'] = [];
        }
        
        $this->query['meta_query'][] = [
            'key' => $key,
            'value' => $value,
            'compare' => $compare
        ];
        
        return $this;
    }
    
    /**
     * Add taxonomy query
     * 
     * @param string $taxonomy Taxonomy name
     * @param string $field Field to query
     * @param mixed $terms Terms to query
     * @return DZ_Query_Builder
     */
    public function where_taxonomy($taxonomy, $field, $terms) {
        if (!isset($this->query['tax_query'])) {
            $this->query['tax_query'] = [];
        }
        
        $this->query['tax_query'][] = [
            'taxonomy' => $taxonomy,
            'field' => $field,
            'terms' => $terms
        ];
        
        return $this;
    }
    
    /**
     * Apply filters from parameters
     * 
     * @param array $filters Filter parameters
     * @return DZ_Query_Builder
     */
    public function apply_filters($filters) {
        // Count
        if (isset($filters['count'])) {
            $this->limit(intval($filters['count']));
        }
        
        // Order by
        if (isset($filters['orderby'])) {
            $order = $filters['order'] ?? 'ASC';
            $this->order_by($filters['orderby'], $order);
        }
        
        // Category filter
        if (!empty($filters['category'])) {
            $this->where_taxonomy('dz_event_category', 'slug', $filters['category']);
        }
        
        // Status filter
        if (!empty($filters['status'])) {
            $this->where_meta('_dz_event_status', $filters['status']);
        }
        
        // Featured filter
        if (isset($filters['featured']) && $filters['featured'] === 'true') {
            $this->where_meta('_dz_event_featured', '1');
        }
        
        // Past events filter
        if (isset($filters['show_past']) && $filters['show_past'] === 'false') {
            $this->where_meta('_dz_event_start', date('Y-m-d'), '>=');
        }
        
        return $this;
    }
    
    /**
     * Execute the query
     * 
     * @return WP_Query Query results
     */
    public function execute() {
        return new WP_Query($this->query);
    }
}

/**
 * Simple Cache Manager
 * 
 * This class provides a clean interface for caching operations
 */
class DZ_Events_Cache {
    
    /**
     * Get cached data
     * 
     * @param string $key Cache key
     * @return mixed Cached data or false
     */
    public function get($key) {
        // Try object cache first
        $cached = wp_cache_get($key, 'dz_events');
        if ($cached !== false) {
            return $cached;
        }
        
        // Fallback to transient
        return get_transient($key);
    }
    
    /**
     * Set cached data
     * 
     * @param string $key Cache key
     * @param mixed $data Data to cache
     * @param int $ttl Time to live in seconds
     * @return bool Success
     */
    public function set($key, $data, $ttl = HOUR_IN_SECONDS) {
        // Set object cache
        wp_cache_set($key, $data, 'dz_events', $ttl);
        
        // Set transient
        return set_transient($key, $data, $ttl);
    }
    
    /**
     * Delete cached data
     * 
     * @param string $key Cache key
     * @return bool Success
     */
    public function delete($key) {
        wp_cache_delete($key, 'dz_events');
        return delete_transient($key);
    }
    
    /**
     * Clear all event caches
     * 
     * @return bool Success
     */
    public function clear_all() {
        global $wpdb;
        
        // Clear transients
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_dz_events_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_dz_events_%'");
        
        // Clear object cache
        wp_cache_flush_group('dz_events');
        
        return true;
    }
}
