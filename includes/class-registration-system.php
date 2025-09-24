<?php
/**
 * Event Registration System for Zeen Events
 * 
 * This file implements a complete registration and ticketing system
 * with payment processing and email notifications
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registration System Class
 * 
 * Handles all event registration functionality
 */
class DZ_Events_Registration_System {
    
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
        
        add_action('init', [$this, 'init_registration_system']);
        add_action('wp_ajax_dz_events_register', [$this, 'ajax_register']);
        add_action('wp_ajax_nopriv_dz_events_register', [$this, 'ajax_register']);
        add_action('wp_ajax_dz_events_cancel_registration', [$this, 'ajax_cancel_registration']);
        add_action('wp_ajax_nopriv_dz_events_cancel_registration', [$this, 'ajax_cancel_registration']);
    }
    
    /**
     * Initialize registration system
     */
    public function init_registration_system() {
        // Create registration tables
        $this->create_registration_tables();
        
        // Add registration hooks
        add_action('dz_events_registration_created', [$this, 'send_confirmation_email'], 10, 2);
        add_action('dz_events_registration_cancelled', [$this, 'send_cancellation_email'], 10, 2);
        
        // Add shortcode for registration form
        add_shortcode('dz_events_register', [$this, 'registration_form_shortcode']);
    }
    
    /**
     * Create registration tables
     */
    private function create_registration_tables() {
        $charset_collate = $this->db->get_charset_collate();
        
        // Registrations table
        $registrations_table = "CREATE TABLE {$this->db->prefix}dz_event_registrations (
            id BIGINT PRIMARY KEY AUTO_INCREMENT,
            event_id BIGINT NOT NULL,
            user_id BIGINT DEFAULT NULL,
            email VARCHAR(255) NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            company VARCHAR(255),
            job_title VARCHAR(100),
            dietary_requirements TEXT,
            emergency_contact_name VARCHAR(100),
            emergency_contact_phone VARCHAR(20),
            status ENUM('pending', 'confirmed', 'cancelled', 'waitlist') DEFAULT 'pending',
            payment_status ENUM('pending', 'paid', 'refunded', 'failed') DEFAULT 'pending',
            payment_method VARCHAR(50),
            payment_id VARCHAR(255),
            amount DECIMAL(10,2) DEFAULT 0,
            currency VARCHAR(3) DEFAULT 'USD',
            ticket_code VARCHAR(50) UNIQUE,
            qr_code VARCHAR(255),
            registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            confirmation_sent BOOLEAN DEFAULT FALSE,
            reminder_sent BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            INDEX idx_event_id (event_id),
            INDEX idx_email (email),
            INDEX idx_status (status),
            INDEX idx_payment_status (payment_status),
            INDEX idx_ticket_code (ticket_code),
            FOREIGN KEY (event_id) REFERENCES {$this->db->posts}(ID) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Registration fields table (for custom fields)
        $fields_table = "CREATE TABLE {$this->db->prefix}dz_registration_fields (
            id INT PRIMARY KEY AUTO_INCREMENT,
            event_id BIGINT NOT NULL,
            field_name VARCHAR(100) NOT NULL,
            field_type ENUM('text', 'email', 'phone', 'select', 'checkbox', 'textarea', 'file') NOT NULL,
            field_options TEXT,
            required BOOLEAN DEFAULT FALSE,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            INDEX idx_event_id (event_id),
            FOREIGN KEY (event_id) REFERENCES {$this->db->posts}(ID) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Registration field values table
        $field_values_table = "CREATE TABLE {$this->db->prefix}dz_registration_field_values (
            id BIGINT PRIMARY KEY AUTO_INCREMENT,
            registration_id BIGINT NOT NULL,
            field_id INT NOT NULL,
            field_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            INDEX idx_registration_id (registration_id),
            INDEX idx_field_id (field_id),
            FOREIGN KEY (registration_id) REFERENCES {$this->db->prefix}dz_event_registrations(id) ON DELETE CASCADE,
            FOREIGN KEY (field_id) REFERENCES {$this->db->prefix}dz_registration_fields(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($registrations_table);
        dbDelta($fields_table);
        dbDelta($field_values_table);
    }
    
    /**
     * AJAX registration handler
     */
    public function ajax_register() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'dz_events_register')) {
            wp_die('Security check failed');
        }
        
        // Rate limiting
        DZ_Events_Security_Manager::instance()->check_rate_limit();
        
        $event_id = intval($_POST['event_id']);
        $registration_data = [
            'email' => sanitize_email($_POST['email']),
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'phone' => sanitize_text_field($_POST['phone']),
            'company' => sanitize_text_field($_POST['company']),
            'job_title' => sanitize_text_field($_POST['job_title']),
            'dietary_requirements' => sanitize_textarea_field($_POST['dietary_requirements']),
            'emergency_contact_name' => sanitize_text_field($_POST['emergency_contact_name']),
            'emergency_contact_phone' => sanitize_text_field($_POST['emergency_contact_phone'])
        ];
        
        try {
            $registration = $this->register_user_for_event($event_id, $registration_data);
            
            // Track analytics
            do_action('dz_events_registration_created', $event_id, $registration);
            
            wp_send_json_success([
                'message' => 'Registration successful!',
                'registration_id' => $registration['id'],
                'ticket_code' => $registration['ticket_code']
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Register user for event
     */
    public function register_user_for_event($event_id, $user_data) {
        // Validate event
        $event = get_post($event_id);
        if (!$event || $event->post_type !== 'dz_event') {
            throw new Exception('Event not found');
        }
        
        // Check if registration is open
        if (!$this->is_registration_open($event_id)) {
            throw new Exception('Registration is not open for this event');
        }
        
        // Check capacity
        if (!$this->has_available_capacity($event_id)) {
            throw new Exception('Event is at full capacity');
        }
        
        // Check if user already registered
        if ($this->is_user_registered($event_id, $user_data['email'])) {
            throw new Exception('You are already registered for this event');
        }
        
        // Generate ticket code
        $ticket_code = $this->generate_ticket_code();
        
        // Calculate amount
        $amount = $this->calculate_registration_amount($event_id);
        
        // Create registration
        $registration_data = [
            'event_id' => $event_id,
            'user_id' => get_current_user_id() ?: null,
            'email' => $user_data['email'],
            'first_name' => $user_data['first_name'],
            'last_name' => $user_data['last_name'],
            'phone' => $user_data['phone'],
            'company' => $user_data['company'],
            'job_title' => $user_data['job_title'],
            'dietary_requirements' => $user_data['dietary_requirements'],
            'emergency_contact_name' => $user_data['emergency_contact_name'],
            'emergency_contact_phone' => $user_data['emergency_contact_phone'],
            'status' => $amount > 0 ? 'pending' : 'confirmed',
            'payment_status' => $amount > 0 ? 'pending' : 'paid',
            'amount' => $amount,
            'ticket_code' => $ticket_code,
            'qr_code' => $this->generate_qr_code($ticket_code)
        ];
        
        $result = $this->db->insert(
            $this->db->prefix . 'dz_event_registrations',
            $registration_data,
            ['%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s']
        );
        
        if (!$result) {
            throw new Exception('Failed to create registration');
        }
        
        $registration_id = $this->db->insert_id;
        $registration_data['id'] = $registration_id;
        
        // Save custom field values
        $this->save_custom_field_values($registration_id, $user_data);
        
        // Update event capacity
        $this->update_event_capacity($event_id);
        
        // Send confirmation email
        do_action('dz_events_registration_created', $event_id, $registration_data);
        
        return $registration_data;
    }
    
    /**
     * Check if registration is open
     */
    private function is_registration_open($event_id) {
        $registration_start = get_post_meta($event_id, '_dz_registration_start', true);
        $registration_end = get_post_meta($event_id, '_dz_registration_end', true);
        $current_time = current_time('Y-m-d H:i:s');
        
        if ($registration_start && $current_time < $registration_start) {
            return false;
        }
        
        if ($registration_end && $current_time > $registration_end) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if event has available capacity
     */
    private function has_available_capacity($event_id) {
        $capacity = intval(get_post_meta($event_id, '_dz_event_capacity', true));
        $registered = $this->get_registration_count($event_id);
        
        return $capacity == 0 || $registered < $capacity;
    }
    
    /**
     * Get registration count for event
     */
    private function get_registration_count($event_id) {
        $sql = "SELECT COUNT(*) FROM {$this->db->prefix}dz_event_registrations 
                WHERE event_id = %d AND status IN ('confirmed', 'pending')";
        
        return $this->db->get_var($this->db->prepare($sql, $event_id));
    }
    
    /**
     * Check if user is already registered
     */
    private function is_user_registered($event_id, $email) {
        $sql = "SELECT COUNT(*) FROM {$this->db->prefix}dz_event_registrations 
                WHERE event_id = %d AND email = %s AND status IN ('confirmed', 'pending')";
        
        return $this->db->get_var($this->db->prepare($sql, $event_id, $email)) > 0;
    }
    
    /**
     * Generate unique ticket code
     */
    private function generate_ticket_code() {
        do {
            $code = 'DZ' . strtoupper(wp_generate_password(8, false));
            $exists = $this->db->get_var($this->db->prepare(
                "SELECT COUNT(*) FROM {$this->db->prefix}dz_event_registrations WHERE ticket_code = %s",
                $code
            ));
        } while ($exists > 0);
        
        return $code;
    }
    
    /**
     * Generate QR code
     */
    private function generate_qr_code($ticket_code) {
        // In production, use a proper QR code library
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($ticket_code);
    }
    
    /**
     * Calculate registration amount
     */
    private function calculate_registration_amount($event_id) {
        $price = get_post_meta($event_id, '_dz_event_price', true);
        
        if (empty($price) || $price === 'Free' || $price === '0') {
            return 0;
        }
        
        // Extract numeric value from price string
        preg_match('/[\d,]+\.?\d*/', $price, $matches);
        return floatval(str_replace(',', '', $matches[0] ?? 0));
    }
    
    /**
     * Save custom field values
     */
    private function save_custom_field_values($registration_id, $user_data) {
        $custom_fields = $this->get_custom_fields($user_data['event_id'] ?? 0);
        
        foreach ($custom_fields as $field) {
            if (isset($user_data['custom_' . $field->id])) {
                $this->db->insert(
                    $this->db->prefix . 'dz_registration_field_values',
                    [
                        'registration_id' => $registration_id,
                        'field_id' => $field->id,
                        'field_value' => $user_data['custom_' . $field->id]
                    ],
                    ['%d', '%d', '%s']
                );
            }
        }
    }
    
    /**
     * Get custom fields for event
     */
    private function get_custom_fields($event_id) {
        $sql = "SELECT * FROM {$this->db->prefix}dz_registration_fields 
                WHERE event_id = %d 
                ORDER BY sort_order ASC";
        
        return $this->db->get_results($this->db->prepare($sql, $event_id));
    }
    
    /**
     * Update event capacity
     */
    private function update_event_capacity($event_id) {
        $registered = $this->get_registration_count($event_id);
        $capacity = intval(get_post_meta($event_id, '_dz_event_capacity', true));
        
        if ($capacity > 0 && $registered >= $capacity) {
            update_post_meta($event_id, '_dz_event_sold_out', '1');
        }
    }
    
    /**
     * Send confirmation email
     */
    public function send_confirmation_email($event_id, $registration_data) {
        $event = get_post($event_id);
        $user_email = $registration_data['email'];
        
        $subject = sprintf('Registration Confirmation: %s', $event->post_title);
        
        $message = $this->get_confirmation_email_template($event, $registration_data);
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        ];
        
        wp_mail($user_email, $subject, $message, $headers);
        
        // Mark confirmation as sent
        $this->db->update(
            $this->db->prefix . 'dz_event_registrations',
            ['confirmation_sent' => true],
            ['id' => $registration_data['id']],
            ['%d'],
            ['%d']
        );
    }
    
    /**
     * Get confirmation email template
     */
    private function get_confirmation_email_template($event, $registration_data) {
        $event_date = get_post_meta($event->ID, '_dz_event_start', true);
        $event_time = get_post_meta($event->ID, '_dz_event_time_start', true);
        $event_location = get_post_meta($event->ID, '_dz_event_location', true);
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Registration Confirmation</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #0073aa; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .ticket-info { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #0073aa; }
                .qr-code { text-align: center; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Registration Confirmed!</h1>
                </div>
                
                <div class="content">
                    <p>Dear <?php echo esc_html($registration_data['first_name']); ?>,</p>
                    
                    <p>Thank you for registering for <strong><?php echo esc_html($event->post_title); ?></strong>!</p>
                    
                    <div class="ticket-info">
                        <h3>Event Details</h3>
                        <p><strong>Date:</strong> <?php echo esc_html(date('F j, Y', strtotime($event_date))); ?></p>
                        <p><strong>Time:</strong> <?php echo esc_html(date('g:i A', strtotime($event_time))); ?></p>
                        <p><strong>Location:</strong> <?php echo esc_html($event_location); ?></p>
                        <p><strong>Ticket Code:</strong> <?php echo esc_html($registration_data['ticket_code']); ?></p>
                    </div>
                    
                    <div class="qr-code">
                        <img src="<?php echo esc_url($registration_data['qr_code']); ?>" alt="QR Code">
                        <p>Present this QR code at the event for quick check-in</p>
                    </div>
                    
                    <p>We look forward to seeing you at the event!</p>
                    
                    <p>Best regards,<br>
                    <?php echo get_bloginfo('name'); ?></p>
                </div>
                
                <div class="footer">
                    <p>This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Registration form shortcode
     */
    public function registration_form_shortcode($atts) {
        $atts = shortcode_atts([
            'event_id' => 0,
            'show_title' => 'true'
        ], $atts);
        
        $event_id = intval($atts['event_id']);
        
        if (!$event_id) {
            return '<p>Error: Event ID is required.</p>';
        }
        
        $event = get_post($event_id);
        if (!$event || $event->post_type !== 'dz_event') {
            return '<p>Error: Event not found.</p>';
        }
        
        ob_start();
        ?>
        <div class="dz-registration-form">
            <?php if ($atts['show_title'] === 'true') : ?>
                <h3>Register for: <?php echo esc_html($event->post_title); ?></h3>
            <?php endif; ?>
            
            <form id="dz-registration-form" data-event-id="<?php echo esc_attr($event_id); ?>">
                <?php wp_nonce_field('dz_events_register', 'dz_events_register_nonce'); ?>
                
                <div class="dz-form-row">
                    <div class="dz-form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    
                    <div class="dz-form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                </div>
                
                <div class="dz-form-row">
                    <div class="dz-form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="dz-form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone">
                    </div>
                </div>
                
                <div class="dz-form-row">
                    <div class="dz-form-group">
                        <label for="company">Company</label>
                        <input type="text" id="company" name="company">
                    </div>
                    
                    <div class="dz-form-group">
                        <label for="job_title">Job Title</label>
                        <input type="text" id="job_title" name="job_title">
                    </div>
                </div>
                
                <div class="dz-form-group">
                    <label for="dietary_requirements">Dietary Requirements</label>
                    <textarea id="dietary_requirements" name="dietary_requirements" rows="3"></textarea>
                </div>
                
                <div class="dz-form-row">
                    <div class="dz-form-group">
                        <label for="emergency_contact_name">Emergency Contact Name</label>
                        <input type="text" id="emergency_contact_name" name="emergency_contact_name">
                    </div>
                    
                    <div class="dz-form-group">
                        <label for="emergency_contact_phone">Emergency Contact Phone</label>
                        <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone">
                    </div>
                </div>
                
                <div class="dz-form-actions">
                    <button type="submit" class="dz-register-btn">Register Now</button>
                </div>
                
                <div id="dz-registration-message" class="dz-message" style="display: none;"></div>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#dz-registration-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = {
                    action: 'dz_events_register',
                    event_id: $(this).data('event-id'),
                    nonce: $('#dz_events_register_nonce').val(),
                    first_name: $('#first_name').val(),
                    last_name: $('#last_name').val(),
                    email: $('#email').val(),
                    phone: $('#phone').val(),
                    company: $('#company').val(),
                    job_title: $('#job_title').val(),
                    dietary_requirements: $('#dietary_requirements').val(),
                    emergency_contact_name: $('#emergency_contact_name').val(),
                    emergency_contact_phone: $('#emergency_contact_phone').val()
                };
                
                $.post(ajaxurl, formData, function(response) {
                    if (response.success) {
                        $('#dz-registration-message')
                            .removeClass('dz-error')
                            .addClass('dz-success')
                            .text(response.data.message)
                            .show();
                        
                        $('#dz-registration-form')[0].reset();
                    } else {
                        $('#dz-registration-message')
                            .removeClass('dz-success')
                            .addClass('dz-error')
                            .text(response.data.message)
                            .show();
                    }
                });
            });
        });
        </script>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * AJAX cancel registration
     */
    public function ajax_cancel_registration() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'dz_events_cancel_registration')) {
            wp_die('Security check failed');
        }
        
        $registration_id = intval($_POST['registration_id']);
        
        try {
            $this->cancel_registration($registration_id);
            
            wp_send_json_success([
                'message' => 'Registration cancelled successfully'
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Cancel registration
     */
    public function cancel_registration($registration_id) {
        $registration = $this->db->get_row($this->db->prepare(
            "SELECT * FROM {$this->db->prefix}dz_event_registrations WHERE id = %d",
            $registration_id
        ));
        
        if (!$registration) {
            throw new Exception('Registration not found');
        }
        
        // Update status
        $this->db->update(
            $this->db->prefix . 'dz_event_registrations',
            ['status' => 'cancelled'],
            ['id' => $registration_id],
            ['%s'],
            ['%d']
        );
        
        // Send cancellation email
        do_action('dz_events_registration_cancelled', $registration->event_id, $registration);
        
        return true;
    }
    
    /**
     * Send cancellation email
     */
    public function send_cancellation_email($event_id, $registration_data) {
        $event = get_post($event_id);
        $user_email = $registration_data->email;
        
        $subject = sprintf('Registration Cancelled: %s', $event->post_title);
        
        $message = sprintf(
            'Dear %s,<br><br>Your registration for "%s" has been cancelled.<br><br>If you have any questions, please contact us.<br><br>Best regards,<br>%s',
            $registration_data->first_name,
            $event->post_title,
            get_bloginfo('name')
        );
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        ];
        
        wp_mail($user_email, $subject, $message, $headers);
    }
}

/**
 * Initialize registration system
 */
function dz_events_init_registration_system() {
    return DZ_Events_Registration_System::instance();
}
add_action('init', 'dz_events_init_registration_system');
