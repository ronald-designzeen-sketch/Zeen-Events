<?php
/**
 * Advanced Admin Dashboard for Zeen Events
 * 
 * This file provides a comprehensive admin dashboard
 * with analytics, reporting, and management tools
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Dashboard Class
 * 
 * Handles the advanced admin dashboard functionality
 */
class DZ_Events_Admin_Dashboard {
    
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
        add_action('admin_menu', [$this, 'add_dashboard_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_dashboard_assets']);
        add_action('wp_ajax_dz_events_dashboard_data', [$this, 'ajax_dashboard_data']);
    }
    
    /**
     * Add dashboard menu
     */
    public function add_dashboard_menu() {
        add_submenu_page(
            'edit.php?post_type=dz_event',
            'Analytics Dashboard',
            'Analytics',
            'manage_options',
            'dz-events-dashboard',
            [$this, 'dashboard_page']
        );
        
        add_submenu_page(
            'edit.php?post_type=dz_event',
            'Performance Monitor',
            'Performance',
            'manage_options',
            'dz-events-performance',
            [$this, 'performance_page']
        );
        
        add_submenu_page(
            'edit.php?post_type=dz_event',
            'Security Center',
            'Security',
            'manage_options',
            'dz-events-security',
            [$this, 'security_page']
        );
    }
    
    /**
     * Enqueue dashboard assets
     */
    public function enqueue_dashboard_assets($hook) {
        if (strpos($hook, 'dz-events') === false) {
            return;
        }
        
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], '3.9.1', true);
        wp_enqueue_script('dz-events-dashboard', plugin_dir_url(__FILE__) . '../assets/js/dashboard.js', ['jquery', 'chart-js'], '1.0.0', true);
        wp_enqueue_style('dz-events-dashboard', plugin_dir_url(__FILE__) . '../assets/css/dashboard.css', [], '1.0.0');
        
        wp_localize_script('dz-events-dashboard', 'dz_dashboard', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dz_dashboard_nonce')
        ]);
    }
    
    /**
     * Dashboard page
     */
    public function dashboard_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Access denied');
        }
        
        ?>
        <div class="wrap dz-dashboard">
            <h1>Analytics Dashboard</h1>
            
            <div class="dz-dashboard-grid">
                <!-- Overview Cards -->
                <div class="dz-dashboard-overview">
                    <div class="dz-stat-card">
                        <h3>Total Events</h3>
                        <div class="dz-stat-number" id="total-events">-</div>
                        <div class="dz-stat-change" id="events-change">-</div>
                    </div>
                    
                    <div class="dz-stat-card">
                        <h3>Total Views</h3>
                        <div class="dz-stat-number" id="total-views">-</div>
                        <div class="dz-stat-change" id="views-change">-</div>
                    </div>
                    
                    <div class="dz-stat-card">
                        <h3>Registrations</h3>
                        <div class="dz-stat-number" id="total-registrations">-</div>
                        <div class="dz-stat-change" id="registrations-change">-</div>
                    </div>
                    
                    <div class="dz-stat-card">
                        <h3>Conversion Rate</h3>
                        <div class="dz-stat-number" id="conversion-rate">-</div>
                        <div class="dz-stat-change" id="conversion-change">-</div>
                    </div>
                </div>
                
                <!-- Charts -->
                <div class="dz-dashboard-charts">
                    <div class="dz-chart-container">
                        <h3>Event Views Over Time</h3>
                        <canvas id="views-chart"></canvas>
                    </div>
                    
                    <div class="dz-chart-container">
                        <h3>Top Performing Events</h3>
                        <canvas id="top-events-chart"></canvas>
                    </div>
                    
                    <div class="dz-chart-container">
                        <h3>Registration Funnel</h3>
                        <canvas id="funnel-chart"></canvas>
                    </div>
                    
                    <div class="dz-chart-container">
                        <h3>Geographic Distribution</h3>
                        <canvas id="geo-chart"></canvas>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="dz-dashboard-activity">
                    <h3>Recent Activity</h3>
                    <div id="recent-activity" class="dz-activity-list">
                        <!-- Activity items will be loaded here -->
                    </div>
                </div>
                
                <!-- Top Events Table -->
                <div class="dz-dashboard-table">
                    <h3>Top Performing Events</h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Views</th>
                                <th>Registrations</th>
                                <th>Conversion Rate</th>
                                <th>Shares</th>
                            </tr>
                        </thead>
                        <tbody id="top-events-table">
                            <!-- Table rows will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Load dashboard data
            loadDashboardData();
            
            // Refresh data every 5 minutes
            setInterval(loadDashboardData, 300000);
        });
        
        function loadDashboardData() {
            jQuery.post(ajaxurl, {
                action: 'dz_events_dashboard_data',
                nonce: dz_dashboard.nonce
            }, function(response) {
                if (response.success) {
                    updateDashboard(response.data);
                }
            });
        }
        
        function updateDashboard(data) {
            // Update overview cards
            jQuery('#total-events').text(data.overview.total_events);
            jQuery('#total-views').text(data.overview.total_views);
            jQuery('#total-registrations').text(data.overview.total_registrations);
            jQuery('#conversion-rate').text(data.overview.conversion_rate + '%');
            
            // Update charts
            updateViewsChart(data.traffic);
            updateTopEventsChart(data.top_events);
            updateFunnelChart(data.conversions);
            updateGeoChart(data.geographic);
            
            // Update recent activity
            updateRecentActivity(data.recent_activity);
            
            // Update top events table
            updateTopEventsTable(data.top_events);
        }
        </script>
        <?php
    }
    
    /**
     * Performance page
     */
    public function performance_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Access denied');
        }
        
        ?>
        <div class="wrap dz-performance">
            <h1>Performance Monitor</h1>
            
            <div class="dz-performance-grid">
                <div class="dz-performance-card">
                    <h3>Page Load Times</h3>
                    <div class="dz-metric">
                        <span class="dz-metric-value" id="avg-load-time">-</span>
                        <span class="dz-metric-unit">ms</span>
                    </div>
                </div>
                
                <div class="dz-performance-card">
                    <h3>Database Queries</h3>
                    <div class="dz-metric">
                        <span class="dz-metric-value" id="query-count">-</span>
                        <span class="dz-metric-unit">queries</span>
                    </div>
                </div>
                
                <div class="dz-performance-card">
                    <h3>Cache Hit Rate</h3>
                    <div class="dz-metric">
                        <span class="dz-metric-value" id="cache-hit-rate">-</span>
                        <span class="dz-metric-unit">%</span>
                    </div>
                </div>
                
                <div class="dz-performance-card">
                    <h3>Memory Usage</h3>
                    <div class="dz-metric">
                        <span class="dz-metric-value" id="memory-usage">-</span>
                        <span class="dz-metric-unit">MB</span>
                    </div>
                </div>
            </div>
            
            <div class="dz-performance-actions">
                <button class="button button-primary" onclick="optimizePerformance()">Optimize Performance</button>
                <button class="button" onclick="clearCache()">Clear Cache</button>
                <button class="button" onclick="generateAssets()">Generate Optimized Assets</button>
            </div>
        </div>
        <?php
    }
    
    /**
     * Security page
     */
    public function security_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Access denied');
        }
        
        $security_logs = $this->get_security_logs();
        
        ?>
        <div class="wrap dz-security">
            <h1>Security Center</h1>
            
            <div class="dz-security-overview">
                <div class="dz-security-card">
                    <h3>Security Status</h3>
                    <div class="dz-security-status" id="security-status">
                        <span class="dz-status-indicator dz-status-good"></span>
                        <span class="dz-status-text">All systems secure</span>
                    </div>
                </div>
                
                <div class="dz-security-card">
                    <h3>Recent Security Events</h3>
                    <div class="dz-security-events">
                        <?php foreach (array_slice($security_logs, 0, 5) as $log) : ?>
                            <div class="dz-security-event">
                                <span class="dz-event-type"><?php echo esc_html($log->event_type); ?></span>
                                <span class="dz-event-time"><?php echo esc_html($log->timestamp); ?></span>
                                <span class="dz-event-ip"><?php echo esc_html($log->ip); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="dz-security-actions">
                <button class="button button-primary" onclick="runSecurityScan()">Run Security Scan</button>
                <button class="button" onclick="viewSecurityLogs()">View All Logs</button>
                <button class="button" onclick="exportSecurityLogs()">Export Logs</button>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX handler for dashboard data
     */
    public function ajax_dashboard_data() {
        check_ajax_referer('dz_dashboard_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Access denied');
        }
        
        $period = $_POST['period'] ?? '30_days';
        
        $data = [
            'overview' => DZ_Events_Analytics_Engine::instance()->get_dashboard_data($period)['overview'],
            'traffic' => DZ_Events_Analytics_Engine::instance()->get_dashboard_data($period)['traffic'],
            'top_events' => DZ_Events_Analytics_Engine::instance()->get_dashboard_data($period)['top_events'],
            'conversions' => DZ_Events_Analytics_Engine::instance()->get_dashboard_data($period)['conversions'],
            'geographic' => DZ_Events_Analytics_Engine::instance()->get_geographic_data($period),
            'recent_activity' => DZ_Events_Analytics_Engine::instance()->get_dashboard_data($period)['recent_activity']
        ];
        
        wp_send_json_success($data);
    }
    
    /**
     * Get security logs
     */
    private function get_security_logs() {
        global $wpdb;
        
        $sql = "SELECT * FROM {$wpdb->prefix}dz_security_logs 
                ORDER BY timestamp DESC 
                LIMIT 50";
        
        return $wpdb->get_results($sql);
    }
}

/**
 * Initialize admin dashboard
 */
function dz_events_init_admin_dashboard() {
    return DZ_Events_Admin_Dashboard::instance();
}
add_action('admin_init', 'dz_events_init_admin_dashboard');
