<?php
/**
 * Database Management for Zeen Events
 * 
 * This file handles database operations, migrations, and optimizations
 * for the enterprise-ready version of the plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database Manager Class
 * 
 * Handles all database operations including migrations and optimizations
 */
class DZ_Events_Database {
    
    private static $instance = null;
    private $db;
    private $version = '1.0.0';
    
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
        
        // Run migrations on activation
        add_action('plugins_loaded', [$this, 'check_migrations']);
    }
    
    /**
     * Check if migrations need to be run
     */
    public function check_migrations() {
        $current_version = get_option('dz_events_db_version', '0.0.0');
        
        if (version_compare($current_version, $this->version, '<')) {
            $this->run_migrations($current_version);
            update_option('dz_events_db_version', $this->version);
        }
    }
    
    /**
     * Run database migrations
     * 
     * @param string $from_version Current version
     */
    private function run_migrations($from_version) {
        // Migration 1.0.0 - Create optimized tables
        if (version_compare($from_version, '1.0.0', '<')) {
            $this->create_optimized_tables();
        }
        
        // Future migrations will be added here
        // Migration 1.1.0 - Add analytics tables
        // Migration 1.2.0 - Add registration tables
        // etc.
    }
    
    /**
     * Create optimized database tables
     * 
     * This creates the enterprise-ready database structure
     */
    private function create_optimized_tables() {
        $charset_collate = $this->db->get_charset_collate();
        
        // Main events table with all data
        $events_table = "CREATE TABLE {$this->db->prefix}dz_events (
            id BIGINT PRIMARY KEY AUTO_INCREMENT,
            post_id BIGINT NOT NULL,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            content LONGTEXT,
            excerpt TEXT,
            featured_image VARCHAR(255),
            
            -- Event specific data
            start_date DATETIME NOT NULL,
            end_date DATETIME NOT NULL,
            start_time TIME,
            end_time TIME,
            location VARCHAR(255),
            capacity INT DEFAULT 0,
            registered INT DEFAULT 0,
            price DECIMAL(10,2) DEFAULT 0,
            currency VARCHAR(3) DEFAULT 'USD',
            
            -- Status and flags
            status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
            featured BOOLEAN DEFAULT FALSE,
            sold_out BOOLEAN DEFAULT FALSE,
            
            -- SEO and metadata
            meta_title VARCHAR(255),
            meta_description TEXT,
            
            -- Timestamps
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            -- Indexes for performance
            INDEX idx_start_date (start_date),
            INDEX idx_status (status),
            INDEX idx_featured (featured),
            INDEX idx_location (location),
            INDEX idx_post_id (post_id),
            FULLTEXT idx_search (title, content, location)
        ) $charset_collate;";
        
        // Categories table
        $categories_table = "CREATE TABLE {$this->db->prefix}dz_event_categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            color VARCHAR(7) DEFAULT '#0073aa',
            icon VARCHAR(50),
            parent_id INT DEFAULT NULL,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            INDEX idx_slug (slug),
            INDEX idx_parent (parent_id)
        ) $charset_collate;";
        
        // Event-category relationship table
        $relations_table = "CREATE TABLE {$this->db->prefix}dz_event_category_relations (
            event_id BIGINT NOT NULL,
            category_id INT NOT NULL,
            PRIMARY KEY (event_id, category_id),
            FOREIGN KEY (event_id) REFERENCES {$this->db->prefix}dz_events(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES {$this->db->prefix}dz_event_categories(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Analytics table
        $analytics_table = "CREATE TABLE {$this->db->prefix}dz_event_analytics (
            id BIGINT PRIMARY KEY AUTO_INCREMENT,
            event_id BIGINT NOT NULL,
            action VARCHAR(50) NOT NULL,
            data JSON,
            ip_address VARCHAR(45),
            user_agent TEXT,
            user_id BIGINT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            INDEX idx_event_id (event_id),
            INDEX idx_action (action),
            INDEX idx_created_at (created_at),
            FOREIGN KEY (event_id) REFERENCES {$this->db->prefix}dz_events(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Registrations table
        $registrations_table = "CREATE TABLE {$this->db->prefix}dz_event_registrations (
            id BIGINT PRIMARY KEY AUTO_INCREMENT,
            event_id BIGINT NOT NULL,
            user_id BIGINT DEFAULT NULL,
            email VARCHAR(255) NOT NULL,
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            phone VARCHAR(20),
            company VARCHAR(255),
            status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
            payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
            payment_id VARCHAR(255),
            ticket_code VARCHAR(50) UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            INDEX idx_event_id (event_id),
            INDEX idx_email (email),
            INDEX idx_status (status),
            INDEX idx_ticket_code (ticket_code),
            FOREIGN KEY (event_id) REFERENCES {$this->db->prefix}dz_events(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Execute table creation
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Security logs table
        $security_logs_table = "CREATE TABLE {$this->db->prefix}dz_security_logs (
            id BIGINT PRIMARY KEY AUTO_INCREMENT,
            event_type VARCHAR(50) NOT NULL,
            event_data JSON,
            ip_address VARCHAR(45),
            user_agent TEXT,
            user_id BIGINT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            INDEX idx_event_type (event_type),
            INDEX idx_ip_address (ip_address),
            INDEX idx_created_at (created_at)
        ) $charset_collate;";
        
        dbDelta($events_table);
        dbDelta($categories_table);
        dbDelta($relations_table);
        dbDelta($analytics_table);
        dbDelta($registrations_table);
        dbDelta($security_logs_table);
        
        // Migrate existing data
        $this->migrate_existing_data();
    }
    
    /**
     * Migrate existing WordPress data to optimized tables
     */
    private function migrate_existing_data() {
        // Migrate events
        $events = get_posts([
            'post_type' => 'dz_event',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ]);
        
        foreach ($events as $event) {
            $this->migrate_single_event($event);
        }
        
        // Migrate categories
        $categories = get_terms([
            'taxonomy' => 'dz_event_category',
            'hide_empty' => false
        ]);
        
        foreach ($categories as $category) {
            $this->migrate_single_category($category);
        }
    }
    
    /**
     * Migrate single event to optimized table
     * 
     * @param WP_Post $event Event post
     */
    private function migrate_single_event($event) {
        $meta = get_post_meta($event->ID);
        
        $event_data = [
            'post_id' => $event->ID,
            'title' => $event->post_title,
            'slug' => $event->post_name,
            'content' => $event->post_content,
            'excerpt' => $event->post_excerpt,
            'featured_image' => get_the_post_thumbnail_url($event->ID, 'full'),
            'start_date' => $meta['_dz_event_start'][0] ?? null,
            'end_date' => $meta['_dz_event_end'][0] ?? null,
            'start_time' => $meta['_dz_event_time_start'][0] ?? null,
            'end_time' => $meta['_dz_event_time_end'][0] ?? null,
            'location' => $meta['_dz_event_location'][0] ?? null,
            'capacity' => intval($meta['_dz_event_capacity'][0] ?? 0),
            'price' => floatval($meta['_dz_event_price'][0] ?? 0),
            'status' => $meta['_dz_event_status'][0] ?? 'upcoming',
            'featured' => $meta['_dz_event_featured'][0] === '1',
            'created_at' => $event->post_date,
            'updated_at' => $event->post_modified
        ];
        
        $this->db->insert(
            $this->db->prefix . 'dz_events',
            $event_data,
            [
                '%d', '%s', '%s', '%s', '%s', '%s',
                '%s', '%s', '%s', '%s', '%s', '%d',
                '%f', '%s', '%d', '%s', '%s'
            ]
        );
    }
    
    /**
     * Migrate single category to optimized table
     * 
     * @param WP_Term $category Category term
     */
    private function migrate_single_category($category) {
        $category_data = [
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'parent_id' => $category->parent,
            'created_at' => current_time('mysql')
        ];
        
        $this->db->insert(
            $this->db->prefix . 'dz_event_categories',
            $category_data,
            ['%s', '%s', '%s', '%d', '%s']
        );
    }
    
    /**
     * Get events from optimized table
     * 
     * @param array $filters Query filters
     * @return array Events
     */
    public function get_events($filters = []) {
        $where_conditions = ['1=1'];
        $where_values = [];
        
        // Status filter
        if (!empty($filters['status'])) {
            $where_conditions[] = 'status = %s';
            $where_values[] = $filters['status'];
        }
        
        // Featured filter
        if (isset($filters['featured']) && $filters['featured'] === 'true') {
            $where_conditions[] = 'featured = 1';
        }
        
        // Date filter
        if (isset($filters['show_past']) && $filters['show_past'] === 'false') {
            $where_conditions[] = 'start_date >= %s';
            $where_values[] = date('Y-m-d');
        }
        
        // Search filter
        if (!empty($filters['search'])) {
            $where_conditions[] = 'MATCH(title, content, location) AGAINST(%s IN NATURAL LANGUAGE MODE)';
            $where_values[] = $filters['search'];
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        $limit = intval($filters['count'] ?? 10);
        $order_by = $filters['orderby'] ?? 'start_date';
        $order = $filters['order'] ?? 'ASC';
        
        $sql = "SELECT * FROM {$this->db->prefix}dz_events 
                WHERE {$where_clause} 
                ORDER BY {$order_by} {$order} 
                LIMIT {$limit}";
        
        if (!empty($where_values)) {
            $sql = $this->db->prepare($sql, $where_values);
        }
        
        return $this->db->get_results($sql);
    }
    
    /**
     * Get single event from optimized table
     * 
     * @param int $event_id Event ID
     * @return object Event data
     */
    public function get_event($event_id) {
        $sql = "SELECT * FROM {$this->db->prefix}dz_events WHERE id = %d";
        return $this->db->get_row($this->db->prepare($sql, $event_id));
    }
    
    /**
     * Track analytics event
     * 
     * @param int $event_id Event ID
     * @param string $action Action performed
     * @param array $data Additional data
     */
    public function track_analytics($event_id, $action, $data = []) {
        $analytics_data = [
            'event_id' => $event_id,
            'action' => $action,
            'data' => json_encode($data),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'user_id' => get_current_user_id() ?: null
        ];
        
        $this->db->insert(
            $this->db->prefix . 'dz_event_analytics',
            $analytics_data,
            ['%d', '%s', '%s', '%s', '%s', '%d']
        );
    }
    
    /**
     * Get analytics for an event
     * 
     * @param int $event_id Event ID
     * @return array Analytics data
     */
    public function get_analytics($event_id) {
        $sql = "SELECT action, COUNT(*) as count 
                FROM {$this->db->prefix}dz_event_analytics 
                WHERE event_id = %d 
                GROUP BY action";
        
        $results = $this->db->get_results($this->db->prepare($sql, $event_id));
        
        $analytics = [];
        foreach ($results as $result) {
            $analytics[$result->action] = $result->count;
        }
        
        return $analytics;
    }
    
    /**
     * Optimize database tables
     */
    public function optimize_tables() {
        $tables = [
            'dz_events',
            'dz_event_categories',
            'dz_event_category_relations',
            'dz_event_analytics',
            'dz_event_registrations'
        ];
        
        foreach ($tables as $table) {
            $this->db->query("OPTIMIZE TABLE {$this->db->prefix}{$table}");
        }
    }
    
    /**
     * Clean up old analytics data
     * 
     * @param int $days Number of days to keep
     */
    public function cleanup_analytics($days = 365) {
        $sql = "DELETE FROM {$this->db->prefix}dz_event_analytics 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)";
        
        $this->db->query($this->db->prepare($sql, $days));
    }
}

/**
 * Initialize database manager
 */
function dz_events_init_database() {
    return DZ_Events_Database::instance();
}
add_action('init', 'dz_events_init_database');

/**
 * Activation hook to run migrations
 */
register_activation_hook(plugin_dir_path(__FILE__) . '../zeen-events.php', function() {
    DZ_Events_Database::instance()->check_migrations();
});

/**
 * Scheduled cleanup of analytics data
 */
if (!wp_next_scheduled('dz_events_cleanup_analytics')) {
    wp_schedule_event(time(), 'weekly', 'dz_events_cleanup_analytics');
}

add_action('dz_events_cleanup_analytics', function() {
    DZ_Events_Database::instance()->cleanup_analytics(365);
});

/**
 * Scheduled optimization of database tables
 */
if (!wp_next_scheduled('dz_events_optimize_database')) {
    wp_schedule_event(time(), 'daily', 'dz_events_optimize_database');
}

add_action('dz_events_optimize_database', function() {
    DZ_Events_Database::instance()->optimize_tables();
});
