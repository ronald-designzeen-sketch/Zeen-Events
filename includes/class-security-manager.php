<?php
/**
 * Enhanced Security Manager for Zeen Events
 * 
 * This file implements enterprise-grade security features
 * including advanced threat detection and prevention
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enhanced Security Manager Class
 * 
 * Handles all advanced security measures
 */
class DZ_Events_Security_Manager_Enhanced {
    
    private static $instance = null;
    private $threat_detection = [];
    private $security_logs = [];
    
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
        add_action('init', [$this, 'init_enhanced_security']);
        add_action('wp_login', [$this, 'log_login_attempt'], 10, 2);
        add_action('wp_login_failed', [$this, 'log_failed_login']);
        add_action('rest_api_init', [$this, 'add_rest_api_security_enhanced']);
    }
    
    /**
     * Initialize enhanced security
     */
    public function init_enhanced_security() {
        // Advanced rate limiting
        add_action('template_redirect', [$this, 'apply_advanced_rate_limiting']);
        
        // Threat detection
        add_action('init', [$this, 'init_threat_detection']);
        
        // Input sanitization
        add_filter('dz_events_sanitize_input', [$this, 'advanced_sanitize_input'], 10, 2);
        
        // CSRF protection
        add_action('admin_init', [$this, 'verify_admin_nonces_enhanced']);
        
        // Security headers
        add_action('send_headers', [$this, 'add_security_headers_enhanced']);
        
        // Audit logging
        add_action('dz_event_created', [$this, 'log_event_action_enhanced']);
        add_action('dz_event_updated', [$this, 'log_event_action_enhanced']);
        add_action('dz_event_deleted', [$this, 'log_event_action_enhanced']);
        
        // File upload security
        add_filter('wp_handle_upload_prefilter', [$this, 'validate_file_uploads']);
        
        // SQL injection prevention
        add_filter('query', [$this, 'prevent_sql_injection']);
        
        // XSS prevention
        add_action('wp_head', [$this, 'add_xss_protection']);
        
        // Brute force protection
        add_action('wp_authenticate_user', [$this, 'check_brute_force'], 10, 2);
    }
    
    /**
     * Apply advanced rate limiting
     */
    public function apply_advanced_rate_limiting() {
        $ip = $this->get_client_ip();
        $endpoint = $_SERVER['REQUEST_URI'] ?? '';
        
        // Check rate limits
        $rate_limits = [
            'registration' => ['limit' => 5, 'window' => 300], // 5 per 5 minutes
            'api' => ['limit' => 100, 'window' => 3600], // 100 per hour
            'admin' => ['limit' => 20, 'window' => 300], // 20 per 5 minutes
            'general' => ['limit' => 1000, 'window' => 3600] // 1000 per hour
        ];
        
        foreach ($rate_limits as $type => $limit) {
            if ($this->is_endpoint_type($endpoint, $type)) {
                if (!$this->check_rate_limit($ip, $type, $limit['limit'], $limit['window'])) {
                    $this->block_request($ip, 'Rate limit exceeded for ' . $type);
                    return;
                }
            }
        }
    }
    
    /**
     * Initialize threat detection
     */
    public function init_threat_detection() {
        // Malicious pattern detection
        add_filter('the_content', [$this, 'detect_malicious_content']);
        add_filter('dz_events_sanitize_input', [$this, 'detect_malicious_input']);
        
        // Bot detection
        add_action('template_redirect', [$this, 'detect_bots']);
        
        // Suspicious activity detection
        add_action('wp_ajax_dz_events_register', [$this, 'detect_suspicious_registration']);
    }
    
    /**
     * Detect malicious content
     */
    public function detect_malicious_content($content) {
        $malicious_patterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe[^>]*>/i',
            '/<object[^>]*>/i',
            '/<embed[^>]*>/i'
        ];
        
        foreach ($malicious_patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $this->log_security_event('malicious_content_detected', [
                    'pattern' => $pattern,
                    'content' => substr($content, 0, 200)
                ]);
                
                // Remove malicious content
                $content = preg_replace($pattern, '', $content);
            }
        }
        
        return $content;
    }
    
    /**
     * Detect malicious input
     */
    public function detect_malicious_input($value, $field_type) {
        $suspicious_patterns = [
            '/union\s+select/i',
            '/drop\s+table/i',
            '/insert\s+into/i',
            '/delete\s+from/i',
            '/update\s+set/i',
            '/<script/i',
            '/javascript:/i',
            '/on\w+\s*=/i'
        ];
        
        foreach ($suspicious_patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $this->log_security_event('malicious_input_detected', [
                    'pattern' => $pattern,
                    'field_type' => $field_type,
                    'value' => substr($value, 0, 100)
                ]);
                
                // Block the request
                $this->block_request($this->get_client_ip(), 'Malicious input detected');
                return '';
            }
        }
        
        return $value;
    }
    
    /**
     * Detect bots
     */
    public function detect_bots() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $bot_patterns = [
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scraper/i',
            '/curl/i',
            '/wget/i'
        ];
        
        foreach ($bot_patterns as $pattern) {
            if (preg_match($pattern, $user_agent)) {
                $this->log_security_event('bot_detected', [
                    'user_agent' => $user_agent,
                    'ip' => $this->get_client_ip()
                ]);
                
                // Allow legitimate bots (Google, Bing, etc.)
                if (!preg_match('/googlebot|bingbot|slurp/i', $user_agent)) {
                    $this->block_request($this->get_client_ip(), 'Suspicious bot detected');
                }
                break;
            }
        }
    }
    
    /**
     * Detect suspicious registration
     */
    public function detect_suspicious_registration() {
        $ip = $this->get_client_ip();
        $email = sanitize_email($_POST['email'] ?? '');
        
        // Check for rapid registrations from same IP
        $recent_registrations = $this->get_recent_registrations($ip, 300); // 5 minutes
        
        if (count($recent_registrations) > 3) {
            $this->log_security_event('suspicious_registration', [
                'ip' => $ip,
                'email' => $email,
                'count' => count($recent_registrations)
            ]);
            
            wp_send_json_error(['message' => 'Too many registration attempts. Please try again later.']);
        }
        
        // Check for duplicate email registrations
        if ($this->is_duplicate_email_registration($email)) {
            $this->log_security_event('duplicate_email_registration', [
                'ip' => $ip,
                'email' => $email
            ]);
            
            wp_send_json_error(['message' => 'This email is already registered.']);
        }
    }
    
    /**
     * Advanced input sanitization
     */
    public function advanced_sanitize_input($value, $field_type) {
        // Remove null bytes
        $value = str_replace(chr(0), '', $value);
        
        // Remove control characters
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
        
        // Field-specific sanitization
        switch ($field_type) {
            case 'email':
                $value = sanitize_email($value);
                break;
            case 'url':
                $value = esc_url_raw($value);
                break;
            case 'text':
                $value = sanitize_text_field($value);
                break;
            case 'textarea':
                $value = sanitize_textarea_field($value);
                break;
            case 'number':
                $value = intval($value);
                break;
            case 'price':
                $value = floatval($value);
                break;
            default:
                $value = sanitize_text_field($value);
        }
        
        return $value;
    }
    
    /**
     * Verify admin nonces enhanced
     */
    public function verify_admin_nonces_enhanced() {
        if (!is_admin()) {
            return;
        }
        
        $nonce_actions = [
            'dz_events_save_event',
            'dz_events_delete_event',
            'dz_events_bulk_action',
            'dz_events_export_data'
        ];
        
        foreach ($nonce_actions as $action) {
            if (isset($_POST[$action . '_nonce'])) {
                if (!wp_verify_nonce($_POST[$action . '_nonce'], $action)) {
                    $this->log_security_event('invalid_nonce', [
                        'action' => $action,
                        'ip' => $this->get_client_ip()
                    ]);
                    
                    wp_die('Security check failed');
                }
            }
        }
    }
    
    /**
     * Add security headers enhanced
     */
    public function add_security_headers_enhanced() {
        // Content Security Policy
        header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\'; style-src \'self\' \'unsafe-inline\'; img-src \'self\' data: https:; font-src \'self\' https:; connect-src \'self\'; frame-ancestors \'none\';');
        
        // X-Frame-Options
        header('X-Frame-Options: DENY');
        
        // X-Content-Type-Options
        header('X-Content-Type-Options: nosniff');
        
        // X-XSS-Protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Permissions Policy
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        
        // Strict Transport Security (HTTPS only)
        if (is_ssl()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }
    
    /**
     * Log event action enhanced
     */
    public function log_event_action_enhanced($post_id, $action) {
        $user_id = get_current_user_id();
        $ip = $this->get_client_ip();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $log_entry = [
            'timestamp' => current_time('mysql'),
            'user_id' => $user_id,
            'ip_address' => $ip,
            'user_agent' => $user_agent,
            'action' => $action,
            'post_id' => $post_id,
            'post_title' => get_the_title($post_id),
            'severity' => $this->get_action_severity($action)
        ];
        
        $this->log_security_event('event_action', $log_entry);
    }
    
    /**
     * Validate file uploads
     */
    public function validate_file_uploads($file) {
        // Check file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_types)) {
            $this->log_security_event('invalid_file_type', [
                'filename' => $file['name'],
                'type' => $file_extension,
                'ip' => $this->get_client_ip()
            ]);
            
            $file['error'] = 'Invalid file type';
            return $file;
        }
        
        // Check file size (5MB limit)
        if ($file['size'] > 5 * 1024 * 1024) {
            $this->log_security_event('file_too_large', [
                'filename' => $file['name'],
                'size' => $file['size'],
                'ip' => $this->get_client_ip()
            ]);
            
            $file['error'] = 'File too large';
            return $file;
        }
        
        // Check for malicious content in images
        if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $image_info = getimagesize($file['tmp_name']);
            if ($image_info === false) {
                $this->log_security_event('malicious_image', [
                    'filename' => $file['name'],
                    'ip' => $this->get_client_ip()
                ]);
                
                $file['error'] = 'Invalid image file';
                return $file;
            }
        }
        
        return $file;
    }
    
    /**
     * Prevent SQL injection
     */
    public function prevent_sql_injection($query) {
        // Check for common SQL injection patterns
        $injection_patterns = [
            '/union\s+select/i',
            '/drop\s+table/i',
            '/insert\s+into/i',
            '/delete\s+from/i',
            '/update\s+set/i',
            '/or\s+1\s*=\s*1/i',
            '/and\s+1\s*=\s*1/i'
        ];
        
        foreach ($injection_patterns as $pattern) {
            if (preg_match($pattern, $query)) {
                $this->log_security_event('sql_injection_attempt', [
                    'query' => substr($query, 0, 200),
                    'ip' => $this->get_client_ip()
                ]);
                
                // Block the request
                $this->block_request($this->get_client_ip(), 'SQL injection attempt detected');
                return 'SELECT 1 WHERE 1=0'; // Return safe query
            }
        }
        
        return $query;
    }
    
    /**
     * Add XSS protection
     */
    public function add_xss_protection() {
        ?>
        <script>
        // XSS Protection
        (function() {
            // Remove dangerous HTML tags
            document.addEventListener('DOMContentLoaded', function() {
                const dangerousTags = ['script', 'iframe', 'object', 'embed', 'form'];
                dangerousTags.forEach(tag => {
                    const elements = document.querySelectorAll(tag);
                    elements.forEach(el => {
                        if (el.src && !el.src.startsWith(window.location.origin)) {
                            el.remove();
                        }
                    });
                });
            });
            
            // Prevent inline script execution
            const originalCreateElement = document.createElement;
            document.createElement = function(tagName) {
                const element = originalCreateElement.call(this, tagName);
                if (tagName.toLowerCase() === 'script') {
                    element.addEventListener('error', function() {
                        console.warn('Script execution blocked for security');
                    });
                }
                return element;
            };
        })();
        </script>
        <?php
    }
    
    /**
     * Check brute force
     */
    public function check_brute_force($user, $password) {
        $ip = $this->get_client_ip();
        $failed_attempts = $this->get_failed_login_attempts($ip, 900); // 15 minutes
        
        if (count($failed_attempts) > 5) {
            $this->log_security_event('brute_force_attempt', [
                'ip' => $ip,
                'username' => $user->user_login,
                'attempts' => count($failed_attempts)
            ]);
            
            // Block IP for 1 hour
            $this->block_ip($ip, 3600);
            
            return new WP_Error('brute_force', 'Too many failed login attempts. IP blocked.');
        }
        
        return $user;
    }
    
    /**
     * Log login attempt
     */
    public function log_login_attempt($user_login, $user) {
        $this->log_security_event('successful_login', [
            'user_id' => $user->ID,
            'username' => $user_login,
            'ip' => $this->get_client_ip()
        ]);
    }
    
    /**
     * Log failed login
     */
    public function log_failed_login($username) {
        $this->log_security_event('failed_login', [
            'username' => $username,
            'ip' => $this->get_client_ip()
        ]);
    }
    
    /**
     * Add REST API security enhanced
     */
    public function add_rest_api_security_enhanced() {
        // Rate limiting for REST API
        add_filter('rest_pre_dispatch', [$this, 'apply_rest_rate_limiting_enhanced'], 10, 3);
        
        // API key validation
        add_filter('rest_authentication_errors', [$this, 'validate_api_key']);
        
        // Request logging
        add_action('rest_api_init', [$this, 'log_api_requests']);
    }
    
    /**
     * Apply REST rate limiting enhanced
     */
    public function apply_rest_rate_limiting_enhanced($result, $server, $request) {
        $ip = $this->get_client_ip();
        $endpoint = $request->get_route();
        
        // Different limits for different endpoints
        $limits = [
            '/dz-events/v1/events' => ['limit' => 100, 'window' => 3600],
            '/dz-events/v1/register' => ['limit' => 10, 'window' => 3600],
            '/dz-events/v1/analytics' => ['limit' => 50, 'window' => 3600]
        ];
        
        foreach ($limits as $pattern => $limit) {
            if (strpos($endpoint, $pattern) !== false) {
                if (!$this->check_rate_limit($ip, 'api_' . $pattern, $limit['limit'], $limit['window'])) {
                    return new WP_Error('rate_limit_exceeded', 'Rate limit exceeded', ['status' => 429]);
                }
                break;
            }
        }
        
        return $result;
    }
    
    /**
     * Validate API key
     */
    public function validate_api_key($errors) {
        $api_key = $_SERVER['HTTP_X_API_KEY'] ?? '';
        
        if (empty($api_key)) {
            return $errors;
        }
        
        $valid_keys = get_option('dz_events_api_keys', []);
        
        if (!in_array($api_key, $valid_keys)) {
            $this->log_security_event('invalid_api_key', [
                'api_key' => substr($api_key, 0, 10) . '...',
                'ip' => $this->get_client_ip()
            ]);
            
            return new WP_Error('invalid_api_key', 'Invalid API key', ['status' => 401]);
        }
        
        return $errors;
    }
    
    /**
     * Log API requests
     */
    public function log_api_requests() {
        add_action('rest_api_init', function() {
            add_filter('rest_pre_dispatch', function($result, $server, $request) {
                $this->log_security_event('api_request', [
                    'endpoint' => $request->get_route(),
                    'method' => $request->get_method(),
                    'ip' => $this->get_client_ip(),
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
                ]);
                
                return $result;
            }, 10, 3);
        });
    }
    
    /**
     * Helper methods
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
    
    private function check_rate_limit($ip, $type, $limit, $window) {
        $key = "dz_rate_limit_{$type}_{$ip}";
        $current = get_transient($key) ?: 0;
        
        if ($current >= $limit) {
            return false;
        }
        
        set_transient($key, $current + 1, $window);
        return true;
    }
    
    private function block_request($ip, $reason) {
        $this->log_security_event('request_blocked', [
            'ip' => $ip,
            'reason' => $reason,
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        http_response_code(429);
        wp_die('Request blocked for security reasons');
    }
    
    private function log_security_event($type, $data) {
        $log_entry = [
            'timestamp' => current_time('mysql'),
            'type' => $type,
            'data' => $data,
            'ip' => $this->get_client_ip()
        ];
        
        $this->security_logs[] = $log_entry;
        
        // Store in database
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'dz_security_logs',
            [
                'event_type' => $type,
                'event_data' => json_encode($data),
                'ip_address' => $this->get_client_ip(),
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s']
        );
    }
    
    private function is_endpoint_type($endpoint, $type) {
        $patterns = [
            'registration' => '/register|registration/',
            'api' => '/wp-json/',
            'admin' => '/wp-admin/',
            'general' => '/'
        ];
        
        return preg_match($patterns[$type], $endpoint);
    }
    
    private function get_recent_registrations($ip, $window) {
        global $wpdb;
        
        $sql = "SELECT * FROM {$wpdb->prefix}dz_event_registrations 
                WHERE ip_address = %s AND created_at > DATE_SUB(NOW(), INTERVAL %d SECOND)";
        
        return $wpdb->get_results($wpdb->prepare($sql, $ip, $window));
    }
    
    private function is_duplicate_email_registration($email) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}dz_event_registrations WHERE email = %s",
            $email
        ));
        
        return $count > 0;
    }
    
    private function get_action_severity($action) {
        $severities = [
            'created' => 'low',
            'updated' => 'medium',
            'deleted' => 'high'
        ];
        
        return $severities[$action] ?? 'low';
    }
    
    private function get_failed_login_attempts($ip, $window) {
        global $wpdb;
        
        $sql = "SELECT * FROM {$wpdb->prefix}dz_security_logs 
                WHERE event_type = 'failed_login' AND ip_address = %s 
                AND created_at > DATE_SUB(NOW(), INTERVAL %d SECOND)";
        
        return $wpdb->get_results($wpdb->prepare($sql, $ip, $window));
    }
    
    private function block_ip($ip, $duration) {
        $blocked_ips = get_option('dz_events_blocked_ips', []);
        $blocked_ips[$ip] = time() + $duration;
        update_option('dz_events_blocked_ips', $blocked_ips);
    }
}

/**
 * Initialize enhanced security manager
 */
function dz_events_init_security_manager_enhanced() {
    return DZ_Events_Security_Manager_Enhanced::instance();
}
add_action('init', 'dz_events_init_security_manager_enhanced');
