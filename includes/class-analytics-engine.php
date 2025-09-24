<?php
/**
 * Analytics Engine for Zeen Events
 * 
 * This file implements comprehensive analytics and reporting
 * to provide insights into event performance and user behavior
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Analytics Engine Class
 * 
 * Handles all analytics and reporting functionality
 */
class DZ_Events_Analytics_Engine {
    
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
        
        add_action('init', [$this, 'init_analytics']);
        add_action('wp_footer', [$this, 'track_page_views']);
    }
    
    /**
     * Initialize analytics
     */
    public function init_analytics() {
        // Track event views
        add_action('dz_events_event_viewed', [$this, 'track_event_view'], 10, 2);
        
        // Track registrations
        add_action('dz_events_registration_created', [$this, 'track_registration'], 10, 2);
        
        // Track social shares
        add_action('dz_events_social_share', [$this, 'track_social_share'], 10, 3);
        
        // Track calendar downloads
        add_action('dz_events_calendar_download', [$this, 'track_calendar_download'], 10, 2);
    }
    
    /**
     * Track event view
     */
    public function track_event_view($event_id, $context = 'page') {
        $this->track_action('view', $event_id, [
            'context' => $context,
            'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
    
    /**
     * Track registration
     */
    public function track_registration($event_id, $registration_data) {
        $this->track_action('register', $event_id, [
            'registration_id' => $registration_data['id'] ?? null,
            'email' => $this->hash_email($registration_data['email'] ?? ''),
            'source' => $registration_data['source'] ?? 'direct'
        ]);
    }
    
    /**
     * Track social share
     */
    public function track_social_share($event_id, $platform, $url) {
        $this->track_action('share', $event_id, [
            'platform' => $platform,
            'url' => $url
        ]);
    }
    
    /**
     * Track calendar download
     */
    public function track_calendar_download($event_id, $calendar_type) {
        $this->track_action('calendar_download', $event_id, [
            'calendar_type' => $calendar_type
        ]);
    }
    
    /**
     * Track page views
     */
    public function track_page_views() {
        if (is_singular('dz_event')) {
            global $post;
            do_action('dz_events_event_viewed', $post->ID, 'page');
        } elseif (is_post_type_archive('dz_event')) {
            do_action('dz_events_event_viewed', 0, 'archive');
        }
    }
    
    /**
     * Track action
     */
    private function track_action($action, $event_id, $data = []) {
        $analytics_data = [
            'event_id' => $event_id,
            'action' => $action,
            'data' => json_encode($data),
            'ip_address' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'user_id' => get_current_user_id() ?: null,
            'session_id' => $this->get_session_id(),
            'created_at' => current_time('mysql')
        ];
        
        $this->db->insert(
            $this->db->prefix . 'dz_event_analytics',
            $analytics_data,
            ['%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s']
        );
    }
    
    /**
     * Get analytics dashboard data
     */
    public function get_dashboard_data($period = '30_days') {
        $date_range = $this->get_date_range($period);
        
        return [
            'overview' => $this->get_overview_stats($date_range),
            'events' => $this->get_event_stats($date_range),
            'registrations' => $this->get_registration_stats($date_range),
            'traffic' => $this->get_traffic_stats($date_range),
            'conversions' => $this->get_conversion_stats($date_range),
            'top_events' => $this->get_top_events($date_range),
            'recent_activity' => $this->get_recent_activity($date_range)
        ];
    }
    
    /**
     * Get overview statistics
     */
    private function get_overview_stats($date_range) {
        $sql = "SELECT 
                    COUNT(DISTINCT event_id) as total_events,
                    COUNT(CASE WHEN action = 'view' THEN 1 END) as total_views,
                    COUNT(CASE WHEN action = 'register' THEN 1 END) as total_registrations,
                    COUNT(CASE WHEN action = 'share' THEN 1 END) as total_shares,
                    COUNT(CASE WHEN action = 'calendar_download' THEN 1 END) as total_calendar_downloads
                FROM {$this->db->prefix}dz_event_analytics 
                WHERE created_at BETWEEN %s AND %s";
        
        $result = $this->db->get_row($this->db->prepare($sql, $date_range['start'], $date_range['end']));
        
        return [
            'total_events' => $result->total_events ?? 0,
            'total_views' => $result->total_views ?? 0,
            'total_registrations' => $result->total_registrations ?? 0,
            'total_shares' => $result->total_shares ?? 0,
            'total_calendar_downloads' => $result->total_calendar_downloads ?? 0,
            'conversion_rate' => $this->calculate_conversion_rate($result->total_views ?? 0, $result->total_registrations ?? 0)
        ];
    }
    
    /**
     * Get event statistics
     */
    private function get_event_stats($date_range) {
        $sql = "SELECT 
                    event_id,
                    COUNT(CASE WHEN action = 'view' THEN 1 END) as views,
                    COUNT(CASE WHEN action = 'register' THEN 1 END) as registrations,
                    COUNT(CASE WHEN action = 'share' THEN 1 END) as shares,
                    COUNT(CASE WHEN action = 'calendar_download' THEN 1 END) as calendar_downloads
                FROM {$this->db->prefix}dz_event_analytics 
                WHERE created_at BETWEEN %s AND %s
                GROUP BY event_id
                ORDER BY views DESC
                LIMIT 10";
        
        return $this->db->get_results($this->db->prepare($sql, $date_range['start'], $date_range['end']));
    }
    
    /**
     * Get registration statistics
     */
    private function get_registration_stats($date_range) {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as registrations
                FROM {$this->db->prefix}dz_event_analytics 
                WHERE action = 'register' 
                AND created_at BETWEEN %s AND %s
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        
        return $this->db->get_results($this->db->prepare($sql, $date_range['start'], $date_range['end']));
    }
    
    /**
     * Get traffic statistics
     */
    private function get_traffic_stats($date_range) {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as views,
                    COUNT(DISTINCT ip_address) as unique_visitors
                FROM {$this->db->prefix}dz_event_analytics 
                WHERE action = 'view' 
                AND created_at BETWEEN %s AND %s
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        
        return $this->db->get_results($this->db->prepare($sql, $date_range['start'], $date_range['end']));
    }
    
    /**
     * Get conversion statistics
     */
    private function get_conversion_stats($date_range) {
        $sql = "SELECT 
                    event_id,
                    COUNT(CASE WHEN action = 'view' THEN 1 END) as views,
                    COUNT(CASE WHEN action = 'register' THEN 1 END) as registrations
                FROM {$this->db->prefix}dz_event_analytics 
                WHERE created_at BETWEEN %s AND %s
                GROUP BY event_id
                HAVING views > 0";
        
        $results = $this->db->get_results($this->db->prepare($sql, $date_range['start'], $date_range['end']));
        
        $conversions = [];
        foreach ($results as $result) {
            $conversions[] = [
                'event_id' => $result->event_id,
                'views' => $result->views,
                'registrations' => $result->registrations,
                'conversion_rate' => $this->calculate_conversion_rate($result->views, $result->registrations)
            ];
        }
        
        return $conversions;
    }
    
    /**
     * Get top performing events
     */
    private function get_top_events($date_range) {
        $sql = "SELECT 
                    a.event_id,
                    p.post_title,
                    COUNT(CASE WHEN a.action = 'view' THEN 1 END) as views,
                    COUNT(CASE WHEN a.action = 'register' THEN 1 END) as registrations,
                    COUNT(CASE WHEN a.action = 'share' THEN 1 END) as shares
                FROM {$this->db->prefix}dz_event_analytics a
                LEFT JOIN {$this->db->posts} p ON a.event_id = p.ID
                WHERE a.created_at BETWEEN %s AND %s
                GROUP BY a.event_id
                ORDER BY views DESC
                LIMIT 5";
        
        return $this->db->get_results($this->db->prepare($sql, $date_range['start'], $date_range['end']));
    }
    
    /**
     * Get recent activity
     */
    private function get_recent_activity($date_range) {
        $sql = "SELECT 
                    a.*,
                    p.post_title
                FROM {$this->db->prefix}dz_event_analytics a
                LEFT JOIN {$this->db->posts} p ON a.event_id = p.ID
                WHERE a.created_at BETWEEN %s AND %s
                ORDER BY a.created_at DESC
                LIMIT 20";
        
        return $this->db->get_results($this->db->prepare($sql, $date_range['start'], $date_range['end']));
    }
    
    /**
     * Get event-specific analytics
     */
    public function get_event_analytics($event_id, $period = '30_days') {
        $date_range = $this->get_date_range($period);
        
        $sql = "SELECT 
                    action,
                    COUNT(*) as count,
                    DATE(created_at) as date
                FROM {$this->db->prefix}dz_event_analytics 
                WHERE event_id = %d 
                AND created_at BETWEEN %s AND %s
                GROUP BY action, DATE(created_at)
                ORDER BY date ASC";
        
        $results = $this->db->get_results($this->db->prepare($sql, $event_id, $date_range['start'], $date_range['end']));
        
        // Organize by action
        $analytics = [];
        foreach ($results as $result) {
            if (!isset($analytics[$result->action])) {
                $analytics[$result->action] = [];
            }
            $analytics[$result->action][$result->date] = $result->count;
        }
        
        return $analytics;
    }
    
    /**
     * Get conversion funnel
     */
    public function get_conversion_funnel($event_id, $period = '30_days') {
        $date_range = $this->get_date_range($period);
        
        $sql = "SELECT 
                    action,
                    COUNT(*) as count
                FROM {$this->db->prefix}dz_event_analytics 
                WHERE event_id = %d 
                AND created_at BETWEEN %s AND %s
                GROUP BY action";
        
        $results = $this->db->get_results($this->db->prepare($sql, $event_id, $date_range['start'], $date_range['end']));
        
        $funnel = [
            'views' => 0,
            'shares' => 0,
            'calendar_downloads' => 0,
            'registrations' => 0
        ];
        
        foreach ($results as $result) {
            if (isset($funnel[$result->action])) {
                $funnel[$result->action] = $result->count;
            }
        }
        
        return $funnel;
    }
    
    /**
     * Get geographic data
     */
    public function get_geographic_data($period = '30_days') {
        $date_range = $this->get_date_range($period);
        
        $sql = "SELECT 
                    ip_address,
                    COUNT(*) as count
                FROM {$this->db->prefix}dz_event_analytics 
                WHERE created_at BETWEEN %s AND %s
                GROUP BY ip_address
                ORDER BY count DESC
                LIMIT 100";
        
        $results = $this->db->get_results($this->db->prepare($sql, $date_range['start'], $date_range['end']));
        
        // Convert IPs to countries (simplified - in production, use a proper GeoIP service)
        $geographic_data = [];
        foreach ($results as $result) {
            $country = $this->get_country_from_ip($result->ip_address);
            if (!isset($geographic_data[$country])) {
                $geographic_data[$country] = 0;
            }
            $geographic_data[$country] += $result->count;
        }
        
        return $geographic_data;
    }
    
    /**
     * Export analytics data
     */
    public function export_analytics($format = 'csv', $period = '30_days') {
        $date_range = $this->get_date_range($period);
        
        $sql = "SELECT 
                    a.*,
                    p.post_title
                FROM {$this->db->prefix}dz_event_analytics a
                LEFT JOIN {$this->db->posts} p ON a.event_id = p.ID
                WHERE a.created_at BETWEEN %s AND %s
                ORDER BY a.created_at DESC";
        
        $results = $this->db->get_results($this->db->prepare($sql, $date_range['start'], $date_range['end']));
        
        if ($format === 'csv') {
            return $this->export_to_csv($results);
        } elseif ($format === 'json') {
            return json_encode($results);
        }
        
        return $results;
    }
    
    /**
     * Export to CSV
     */
    private function export_to_csv($data) {
        $csv = "Date,Event ID,Event Title,Action,IP Address,User Agent,Data\n";
        
        foreach ($data as $row) {
            $csv .= sprintf(
                "%s,%d,%s,%s,%s,%s,%s\n",
                $row->created_at,
                $row->event_id,
                $row->post_title ?? '',
                $row->action,
                $row->ip_address,
                $row->user_agent,
                $row->data
            );
        }
        
        return $csv;
    }
    
    /**
     * Calculate conversion rate
     */
    private function calculate_conversion_rate($views, $registrations) {
        if ($views == 0) return 0;
        return round(($registrations / $views) * 100, 2);
    }
    
    /**
     * Get date range for period
     */
    private function get_date_range($period) {
        $end_date = current_time('Y-m-d H:i:s');
        
        switch ($period) {
            case '7_days':
                $start_date = date('Y-m-d H:i:s', strtotime('-7 days'));
                break;
            case '30_days':
                $start_date = date('Y-m-d H:i:s', strtotime('-30 days'));
                break;
            case '90_days':
                $start_date = date('Y-m-d H:i:s', strtotime('-90 days'));
                break;
            case '1_year':
                $start_date = date('Y-m-d H:i:s', strtotime('-1 year'));
                break;
            default:
                $start_date = date('Y-m-d H:i:s', strtotime('-30 days'));
                break;
        }
        
        return [
            'start' => $start_date,
            'end' => $end_date
        ];
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Get session ID
     */
    private function get_session_id() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return session_id();
    }
    
    /**
     * Hash email for privacy
     */
    private function hash_email($email) {
        return hash('sha256', $email . wp_salt());
    }
    
    /**
     * Get country from IP (simplified)
     */
    private function get_country_from_ip($ip) {
        // In production, use a proper GeoIP service like MaxMind
        // This is a simplified version
        $geoip_data = @file_get_contents("http://ip-api.com/json/{$ip}");
        if ($geoip_data) {
            $data = json_decode($geoip_data, true);
            return $data['country'] ?? 'Unknown';
        }
        return 'Unknown';
    }
    
    /**
     * Clean old analytics data
     */
    public function clean_old_data($days = 365) {
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $this->db->query($this->db->prepare(
            "DELETE FROM {$this->db->prefix}dz_event_analytics WHERE created_at < %s",
            $cutoff_date
        ));
    }
}

/**
 * Initialize analytics engine
 */
function dz_events_init_analytics_engine() {
    return DZ_Events_Analytics_Engine::instance();
}
add_action('init', 'dz_events_init_analytics_engine');

/**
 * Clean old analytics data monthly
 */
if (!wp_next_scheduled('dz_events_clean_analytics')) {
    wp_schedule_event(time(), 'monthly', 'dz_events_clean_analytics');
}

add_action('dz_events_clean_analytics', function() {
    DZ_Events_Analytics_Engine::instance()->clean_old_data(365);
});
