<?php
/**
 * Multi-site Support for Zeen Events
 * 
 * This file implements network-wide event management
 * and cross-site event aggregation
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Multi-site Support Class
 * 
 * Handles all multi-site functionality
 */
class DZ_Events_Multisite_Support {
    
    private static $instance = null;
    
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
        if (!is_multisite()) {
            return;
        }
        
        add_action('init', [$this, 'init_multisite_support']);
        add_action('network_admin_menu', [$this, 'add_network_admin_menu']);
        add_action('wp_ajax_dz_events_network_events', [$this, 'ajax_network_events']);
        add_action('wp_ajax_dz_events_sync_events', [$this, 'ajax_sync_events']);
    }
    
    /**
     * Initialize multi-site support
     */
    public function init_multisite_support() {
        // Add network-wide event aggregation
        add_action('dz_events_get_network_events', [$this, 'get_network_events']);
        
        // Add cross-site event synchronization
        add_action('save_post_dz_event', [$this, 'sync_event_to_network'], 10, 2);
        add_action('delete_post', [$this, 'remove_event_from_network']);
        
        // Add network-wide analytics
        add_action('dz_events_network_analytics', [$this, 'get_network_analytics']);
    }
    
    /**
     * Add network admin menu
     */
    public function add_network_admin_menu() {
        add_menu_page(
            'Network Events',
            'Network Events',
            'manage_network',
            'dz-network-events',
            [$this, 'network_events_page'],
            'dashicons-calendar-alt',
            30
        );
        
        add_submenu_page(
            'dz-network-events',
            'All Network Events',
            'All Events',
            'manage_network',
            'dz-network-events',
            [$this, 'network_events_page']
        );
        
        add_submenu_page(
            'dz-network-events',
            'Network Analytics',
            'Analytics',
            'manage_network',
            'dz-network-analytics',
            [$this, 'network_analytics_page']
        );
        
        add_submenu_page(
            'dz-network-events',
            'Network Settings',
            'Settings',
            'manage_network',
            'dz-network-settings',
            [$this, 'network_settings_page']
        );
    }
    
    /**
     * Get network events
     */
    public function get_network_events($filters = []) {
        $sites = get_sites(['number' => 0]);
        $all_events = [];
        
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            
            // Get events from this site
            $site_events = get_posts([
                'post_type' => 'dz_event',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'meta_query' => $this->build_meta_query($filters)
            ]);
            
            // Add site information to each event
            foreach ($site_events as $event) {
                $event->site_id = $site->blog_id;
                $event->site_name = get_bloginfo('name');
                $event->site_url = get_site_url();
                $event->network_permalink = network_site_url() . '?site=' . $site->blog_id . '&event=' . $event->ID;
                
                // Add event meta
                $event->meta = $this->get_event_meta($event->ID);
                
                $all_events[] = $event;
            }
            
            restore_current_blog();
        }
        
        // Sort events by date
        usort($all_events, function($a, $b) {
            $date_a = $a->meta['start'] ?? '';
            $date_b = $b->meta['start'] ?? '';
            return strcmp($date_a, $date_b);
        });
        
        // Apply additional filters
        $all_events = $this->apply_network_filters($all_events, $filters);
        
        return $all_events;
    }
    
    /**
     * Build meta query for network events
     */
    private function build_meta_query($filters) {
        $meta_query = [];
        
        // Date filter
        if (isset($filters['date_from']) && $filters['date_from']) {
            $meta_query[] = [
                'key' => '_dz_event_start',
                'value' => $filters['date_from'],
                'compare' => '>=',
                'type' => 'DATE'
            ];
        }
        
        if (isset($filters['date_to']) && $filters['date_to']) {
            $meta_query[] = [
                'key' => '_dz_event_start',
                'value' => $filters['date_to'],
                'compare' => '<=',
                'type' => 'DATE'
            ];
        }
        
        // Status filter
        if (isset($filters['status']) && $filters['status']) {
            $meta_query[] = [
                'key' => '_dz_event_status',
                'value' => $filters['status'],
                'compare' => '='
            ];
        }
        
        // Featured filter
        if (isset($filters['featured']) && $filters['featured']) {
            $meta_query[] = [
                'key' => '_dz_event_featured',
                'value' => '1',
                'compare' => '='
            ];
        }
        
        return $meta_query;
    }
    
    /**
     * Apply additional filters to network events
     */
    private function apply_network_filters($events, $filters) {
        // Site filter
        if (isset($filters['site_id']) && $filters['site_id']) {
            $events = array_filter($events, function($event) use ($filters) {
                return $event->site_id == $filters['site_id'];
            });
        }
        
        // Search filter
        if (isset($filters['search']) && $filters['search']) {
            $search_term = strtolower($filters['search']);
            $events = array_filter($events, function($event) use ($search_term) {
                return strpos(strtolower($event->post_title), $search_term) !== false ||
                       strpos(strtolower($event->post_content), $search_term) !== false ||
                       strpos(strtolower($event->meta['location'] ?? ''), $search_term) !== false;
            });
        }
        
        // Limit results
        if (isset($filters['limit']) && $filters['limit']) {
            $events = array_slice($events, 0, $filters['limit']);
        }
        
        return $events;
    }
    
    /**
     * Get event meta data
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
            '_dz_event_featured'
        ];
        
        $meta = [];
        foreach ($meta_keys as $key) {
            $meta[str_replace('_dz_event_', '', $key)] = get_post_meta($event_id, $key, true);
        }
        
        return $meta;
    }
    
    /**
     * Network events page
     */
    public function network_events_page() {
        if (!current_user_can('manage_network')) {
            wp_die('Access denied');
        }
        
        $filters = [
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'status' => $_GET['status'] ?? '',
            'featured' => $_GET['featured'] ?? '',
            'site_id' => $_GET['site_id'] ?? '',
            'search' => $_GET['search'] ?? '',
            'limit' => $_GET['limit'] ?? 50
        ];
        
        $events = $this->get_network_events($filters);
        $sites = get_sites(['number' => 0]);
        
        ?>
        <div class="wrap">
            <h1>Network Events</h1>
            
            <!-- Filters -->
            <div class="dz-network-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="dz-network-events">
                    
                    <div class="dz-filter-row">
                        <div class="dz-filter-group">
                            <label for="date_from">From Date:</label>
                            <input type="date" id="date_from" name="date_from" value="<?php echo esc_attr($filters['date_from']); ?>">
                        </div>
                        
                        <div class="dz-filter-group">
                            <label for="date_to">To Date:</label>
                            <input type="date" id="date_to" name="date_to" value="<?php echo esc_attr($filters['date_to']); ?>">
                        </div>
                        
                        <div class="dz-filter-group">
                            <label for="status">Status:</label>
                            <select id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="upcoming" <?php selected($filters['status'], 'upcoming'); ?>>Upcoming</option>
                                <option value="ongoing" <?php selected($filters['status'], 'ongoing'); ?>>Ongoing</option>
                                <option value="completed" <?php selected($filters['status'], 'completed'); ?>>Completed</option>
                            </select>
                        </div>
                        
                        <div class="dz-filter-group">
                            <label for="site_id">Site:</label>
                            <select id="site_id" name="site_id">
                                <option value="">All Sites</option>
                                <?php foreach ($sites as $site) : ?>
                                    <option value="<?php echo $site->blog_id; ?>" <?php selected($filters['site_id'], $site->blog_id); ?>>
                                        <?php echo get_blog_option($site->blog_id, 'blogname'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="dz-filter-group">
                            <label for="search">Search:</label>
                            <input type="text" id="search" name="search" value="<?php echo esc_attr($filters['search']); ?>" placeholder="Search events...">
                        </div>
                        
                        <div class="dz-filter-group">
                            <input type="submit" class="button button-primary" value="Filter">
                            <a href="?page=dz-network-events" class="button">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Events Table -->
            <div class="dz-network-events-table">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Site</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event) : ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($event->post_title); ?></strong>
                                    <?php if ($event->meta['featured'] === '1') : ?>
                                        <span class="dz-featured-badge">Featured</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url($event->site_url); ?>" target="_blank">
                                        <?php echo esc_html($event->site_name); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if ($event->meta['start']) : ?>
                                        <?php echo esc_html(date('M j, Y', strtotime($event->meta['start']))); ?>
                                        <?php if ($event->meta['time_start']) : ?>
                                            <br><small><?php echo esc_html(date('g:i A', strtotime($event->meta['time_start']))); ?></small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($event->meta['location'] ?? ''); ?></td>
                                <td>
                                    <span class="dz-status-badge dz-status-<?php echo esc_attr($event->meta['status']); ?>">
                                        <?php echo esc_html(ucfirst($event->meta['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url($event->network_permalink); ?>" class="button button-small" target="_blank">View</a>
                                    <a href="<?php echo esc_url($event->site_url . '/wp-admin/post.php?post=' . $event->ID . '&action=edit'); ?>" class="button button-small" target="_blank">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="dz-network-stats">
                <h3>Network Statistics</h3>
                <div class="dz-stats-grid">
                    <div class="dz-stat-item">
                        <span class="dz-stat-number"><?php echo count($events); ?></span>
                        <span class="dz-stat-label">Total Events</span>
                    </div>
                    <div class="dz-stat-item">
                        <span class="dz-stat-number"><?php echo count($sites); ?></span>
                        <span class="dz-stat-label">Active Sites</span>
                    </div>
                    <div class="dz-stat-item">
                        <span class="dz-stat-number"><?php echo count(array_filter($events, function($e) { return $e->meta['featured'] === '1'; })); ?></span>
                        <span class="dz-stat-label">Featured Events</span>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .dz-network-filters {
            background: #fff;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }
        
        .dz-filter-row {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .dz-filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .dz-filter-group label {
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .dz-network-events-table {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .dz-featured-badge {
            background: #ff6b35;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            text-transform: uppercase;
            margin-left: 8px;
        }
        
        .dz-status-badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .dz-status-upcoming { background: #0073aa; color: white; }
        .dz-status-ongoing { background: #00a32a; color: white; }
        .dz-status-completed { background: #646970; color: white; }
        
        .dz-network-stats {
            background: #fff;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }
        
        .dz-stats-grid {
            display: flex;
            gap: 30px;
            margin-top: 15px;
        }
        
        .dz-stat-item {
            text-align: center;
        }
        
        .dz-stat-number {
            display: block;
            font-size: 2em;
            font-weight: 600;
            color: #0073aa;
        }
        
        .dz-stat-label {
            font-size: 12px;
            text-transform: uppercase;
            color: #646970;
        }
        </style>
        <?php
    }
    
    /**
     * Network analytics page
     */
    public function network_analytics_page() {
        if (!current_user_can('manage_network')) {
            wp_die('Access denied');
        }
        
        $analytics = $this->get_network_analytics();
        
        ?>
        <div class="wrap">
            <h1>Network Analytics</h1>
            
            <div class="dz-network-analytics">
                <div class="dz-analytics-overview">
                    <h3>Network Overview</h3>
                    <div class="dz-analytics-grid">
                        <div class="dz-analytics-card">
                            <h4>Total Events</h4>
                            <div class="dz-analytics-number"><?php echo $analytics['total_events']; ?></div>
                        </div>
                        <div class="dz-analytics-card">
                            <h4>Total Views</h4>
                            <div class="dz-analytics-number"><?php echo $analytics['total_views']; ?></div>
                        </div>
                        <div class="dz-analytics-card">
                            <h4>Total Registrations</h4>
                            <div class="dz-analytics-number"><?php echo $analytics['total_registrations']; ?></div>
                        </div>
                        <div class="dz-analytics-card">
                            <h4>Active Sites</h4>
                            <div class="dz-analytics-number"><?php echo $analytics['active_sites']; ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="dz-analytics-sites">
                    <h3>Site Performance</h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Site</th>
                                <th>Events</th>
                                <th>Views</th>
                                <th>Registrations</th>
                                <th>Conversion Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($analytics['sites'] as $site) : ?>
                                <tr>
                                    <td><?php echo esc_html($site['name']); ?></td>
                                    <td><?php echo $site['events']; ?></td>
                                    <td><?php echo $site['views']; ?></td>
                                    <td><?php echo $site['registrations']; ?></td>
                                    <td><?php echo $site['conversion_rate']; ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <style>
        .dz-analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .dz-analytics-card {
            background: #fff;
            padding: 20px;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            text-align: center;
        }
        
        .dz-analytics-card h4 {
            margin: 0 0 10px 0;
            color: #646970;
            font-size: 14px;
            text-transform: uppercase;
        }
        
        .dz-analytics-number {
            font-size: 2.5em;
            font-weight: 600;
            color: #0073aa;
        }
        </style>
        <?php
    }
    
    /**
     * Network settings page
     */
    public function network_settings_page() {
        if (!current_user_can('manage_network')) {
            wp_die('Access denied');
        }
        
        if (isset($_POST['submit'])) {
            $this->save_network_settings();
        }
        
        $settings = $this->get_network_settings();
        
        ?>
        <div class="wrap">
            <h1>Network Settings</h1>
            
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable Network Events</th>
                        <td>
                            <input type="checkbox" name="network_events_enabled" value="1" <?php checked($settings['network_events_enabled'], 1); ?>>
                            <p class="description">Enable network-wide event aggregation</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Default Event Limit</th>
                        <td>
                            <input type="number" name="default_event_limit" value="<?php echo esc_attr($settings['default_event_limit']); ?>" min="1" max="1000">
                            <p class="description">Maximum number of events to display in network views</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Enable Cross-Site Sync</th>
                        <td>
                            <input type="checkbox" name="cross_site_sync" value="1" <?php checked($settings['cross_site_sync'], 1); ?>>
                            <p class="description">Automatically sync events across all sites</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Network Analytics</th>
                        <td>
                            <input type="checkbox" name="network_analytics" value="1" <?php checked($settings['network_analytics'], 1); ?>>
                            <p class="description">Enable network-wide analytics tracking</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Get network analytics
     */
    public function get_network_analytics() {
        $sites = get_sites(['number' => 0]);
        $analytics = [
            'total_events' => 0,
            'total_views' => 0,
            'total_registrations' => 0,
            'active_sites' => count($sites),
            'sites' => []
        ];
        
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            
            $site_events = wp_count_posts('dz_event');
            $site_analytics = DZ_Events_Analytics_Engine::instance()->get_dashboard_data('30_days');
            
            $site_data = [
                'id' => $site->blog_id,
                'name' => get_bloginfo('name'),
                'events' => $site_events->publish ?? 0,
                'views' => $site_analytics['overview']['total_views'] ?? 0,
                'registrations' => $site_analytics['overview']['total_registrations'] ?? 0,
                'conversion_rate' => $site_analytics['overview']['conversion_rate'] ?? 0
            ];
            
            $analytics['sites'][] = $site_data;
            $analytics['total_events'] += $site_data['events'];
            $analytics['total_views'] += $site_data['views'];
            $analytics['total_registrations'] += $site_data['registrations'];
            
            restore_current_blog();
        }
        
        return $analytics;
    }
    
    /**
     * Get network settings
     */
    private function get_network_settings() {
        return get_site_option('dz_events_network_settings', [
            'network_events_enabled' => 1,
            'default_event_limit' => 50,
            'cross_site_sync' => 0,
            'network_analytics' => 1
        ]);
    }
    
    /**
     * Save network settings
     */
    private function save_network_settings() {
        $settings = [
            'network_events_enabled' => isset($_POST['network_events_enabled']) ? 1 : 0,
            'default_event_limit' => intval($_POST['default_event_limit']),
            'cross_site_sync' => isset($_POST['cross_site_sync']) ? 1 : 0,
            'network_analytics' => isset($_POST['network_analytics']) ? 1 : 0
        ];
        
        update_site_option('dz_events_network_settings', $settings);
        
        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }
    
    /**
     * AJAX network events
     */
    public function ajax_network_events() {
        check_ajax_referer('dz_network_events_nonce', 'nonce');
        
        if (!current_user_can('manage_network')) {
            wp_die('Access denied');
        }
        
        $filters = $_POST['filters'] ?? [];
        $events = $this->get_network_events($filters);
        
        wp_send_json_success($events);
    }
    
    /**
     * AJAX sync events
     */
    public function ajax_sync_events() {
        check_ajax_referer('dz_sync_events_nonce', 'nonce');
        
        if (!current_user_can('manage_network')) {
            wp_die('Access denied');
        }
        
        $result = $this->sync_all_events();
        
        wp_send_json_success($result);
    }
    
    /**
     * Sync all events across network
     */
    private function sync_all_events() {
        $sites = get_sites(['number' => 0]);
        $synced = 0;
        
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            
            $events = get_posts([
                'post_type' => 'dz_event',
                'posts_per_page' => -1,
                'post_status' => 'publish'
            ]);
            
            foreach ($events as $event) {
                do_action('dz_events_sync_event_to_network', $event->ID, $site->blog_id);
                $synced++;
            }
            
            restore_current_blog();
        }
        
        return ['synced' => $synced, 'sites' => count($sites)];
    }
}

/**
 * Initialize multi-site support
 */
function dz_events_init_multisite_support() {
    if (is_multisite()) {
        return DZ_Events_Multisite_Support::instance();
    }
    return null;
}
add_action('init', 'dz_events_init_multisite_support');
