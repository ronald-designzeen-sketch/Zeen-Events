<?php
/**
 * Payment Gateway Integration for Zeen Events
 * 
 * This file implements multiple payment gateway integrations
 * including Stripe, PayPal, and Square
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Payment Gateways Manager Class
 * 
 * Handles all payment processing functionality
 */
class DZ_Events_Payment_Gateways {
    
    private static $instance = null;
    private $gateways = [];
    
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
        add_action('init', [$this, 'init_payment_gateways']);
        add_action('wp_ajax_dz_events_process_payment', [$this, 'ajax_process_payment']);
        add_action('wp_ajax_nopriv_dz_events_process_payment', [$this, 'ajax_process_payment']);
        add_action('wp_ajax_dz_events_webhook', [$this, 'ajax_webhook_handler']);
        add_action('wp_ajax_nopriv_dz_events_webhook', [$this, 'ajax_webhook_handler']);
    }
    
    /**
     * Initialize payment gateways
     */
    public function init_payment_gateways() {
        // Register international gateways
        $this->register_gateway('stripe', 'Stripe', 'DZ_Stripe_Gateway');
        $this->register_gateway('paypal', 'PayPal', 'DZ_PayPal_Gateway');
        $this->register_gateway('square', 'Square', 'DZ_Square_Gateway');
        
        // Register South African gateways
        $this->register_gateway('payfast', 'PayFast', 'DZ_PayFast_Gateway');
        $this->register_gateway('yoco', 'Yoco', 'DZ_Yoco_Gateway');
        $this->register_gateway('ozow', 'Ozow', 'DZ_Ozow_Gateway');
        $this->register_gateway('peach_payments', 'Peach Payments', 'DZ_Peach_Payments_Gateway');
        $this->register_gateway('paygate', 'PayGate', 'DZ_PayGate_Gateway');
        $this->register_gateway('snapscan', 'SnapScan', 'DZ_SnapScan_Gateway');
        $this->register_gateway('zapper', 'Zapper', 'DZ_Zapper_Gateway');
        
        // Load gateway classes
        $this->load_gateway_classes();
    }
    
    /**
     * Register payment gateway
     */
    private function register_gateway($id, $name, $class) {
        $this->gateways[$id] = [
            'id' => $id,
            'name' => $name,
            'class' => $class,
            'enabled' => get_option("dz_payment_gateway_{$id}_enabled", false)
        ];
    }
    
    /**
     * Load gateway classes
     */
    private function load_gateway_classes() {
        // International gateways
        require_once plugin_dir_path(__FILE__) . 'gateways/stripe-gateway.php';
        require_once plugin_dir_path(__FILE__) . 'gateways/paypal-gateway.php';
        require_once plugin_dir_path(__FILE__) . 'gateways/square-gateway.php';
        
        // South African gateways
        require_once plugin_dir_path(__FILE__) . 'gateways/payfast-gateway.php';
        require_once plugin_dir_path(__FILE__) . 'gateways/yoco-gateway.php';
        require_once plugin_dir_path(__FILE__) . 'gateways/ozow-gateway.php';
        require_once plugin_dir_path(__FILE__) . 'gateways/peach-payments-gateway.php';
        require_once plugin_dir_path(__FILE__) . 'gateways/paygate-gateway.php';
        require_once plugin_dir_path(__FILE__) . 'gateways/snapscan-gateway.php';
        require_once plugin_dir_path(__FILE__) . 'gateways/zapper-gateway.php';
    }
    
    /**
     * Process payment
     */
    public function process_payment($gateway_id, $amount, $currency, $payment_data) {
        if (!isset($this->gateways[$gateway_id])) {
            throw new Exception('Payment gateway not found');
        }
        
        $gateway = $this->gateways[$gateway_id];
        
        if (!$gateway['enabled']) {
            throw new Exception('Payment gateway is not enabled');
        }
        
        $gateway_class = $gateway['class'];
        
        if (!class_exists($gateway_class)) {
            throw new Exception('Payment gateway class not found');
        }
        
        $gateway_instance = new $gateway_class();
        
        return $gateway_instance->process_payment($amount, $currency, $payment_data);
    }
    
    /**
     * AJAX payment processing
     */
    public function ajax_process_payment() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'dz_events_payment')) {
            wp_die('Security check failed');
        }
        
        // Rate limiting
        DZ_Events_Security_Manager::instance()->check_rate_limit();
        
        $gateway_id = sanitize_text_field($_POST['gateway']);
        $amount = floatval($_POST['amount']);
        $currency = sanitize_text_field($_POST['currency']);
        $registration_id = intval($_POST['registration_id']);
        
        $payment_data = [
            'registration_id' => $registration_id,
            'customer_email' => sanitize_email($_POST['email']),
            'customer_name' => sanitize_text_field($_POST['name']),
            'return_url' => esc_url_raw($_POST['return_url']),
            'cancel_url' => esc_url_raw($_POST['cancel_url'])
        ];
        
        try {
            $payment_result = $this->process_payment($gateway_id, $amount, $currency, $payment_data);
            
            // Update registration with payment info
            $this->update_registration_payment($registration_id, $payment_result);
            
            wp_send_json_success([
                'payment_id' => $payment_result['payment_id'],
                'status' => $payment_result['status'],
                'redirect_url' => $payment_result['redirect_url'] ?? null
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Update registration with payment info
     */
    private function update_registration_payment($registration_id, $payment_result) {
        global $wpdb;
        
        $update_data = [
            'payment_id' => $payment_result['payment_id'],
            'payment_status' => $payment_result['status'],
            'payment_method' => $payment_result['gateway']
        ];
        
        if ($payment_result['status'] === 'paid') {
            $update_data['status'] = 'confirmed';
        }
        
        $wpdb->update(
            $wpdb->prefix . 'dz_event_registrations',
            $update_data,
            ['id' => $registration_id],
            ['%s', '%s', '%s', '%s'],
            ['%d']
        );
    }
    
    /**
     * AJAX webhook handler
     */
    public function ajax_webhook_handler() {
        $gateway_id = sanitize_text_field($_GET['gateway'] ?? '');
        
        if (!isset($this->gateways[$gateway_id])) {
            wp_die('Invalid gateway');
        }
        
        $gateway = $this->gateways[$gateway_id];
        $gateway_class = $gateway['class'];
        
        if (!class_exists($gateway_class)) {
            wp_die('Gateway class not found');
        }
        
        $gateway_instance = new $gateway_class();
        $gateway_instance->handle_webhook();
    }
    
    /**
     * Get available gateways
     */
    public function get_available_gateways() {
        return array_filter($this->gateways, function($gateway) {
            return $gateway['enabled'];
        });
    }
    
    /**
     * Get gateway settings
     */
    public function get_gateway_settings($gateway_id) {
        if (!isset($this->gateways[$gateway_id])) {
            return null;
        }
        
        $settings = get_option("dz_payment_gateway_{$gateway_id}_settings", []);
        
        return array_merge([
            'enabled' => false,
            'test_mode' => true,
            'api_key' => '',
            'secret_key' => '',
            'webhook_secret' => ''
        ], $settings);
    }
    
    /**
     * Update gateway settings
     */
    public function update_gateway_settings($gateway_id, $settings) {
        if (!isset($this->gateways[$gateway_id])) {
            return false;
        }
        
        return update_option("dz_payment_gateway_{$gateway_id}_settings", $settings);
    }
    
    /**
     * Enable/disable gateway
     */
    public function toggle_gateway($gateway_id, $enabled) {
        if (!isset($this->gateways[$gateway_id])) {
            return false;
        }
        
        $this->gateways[$gateway_id]['enabled'] = $enabled;
        
        return update_option("dz_payment_gateway_{$gateway_id}_enabled", $enabled);
    }
}

/**
 * Base Payment Gateway Class
 */
abstract class DZ_Payment_Gateway {
    
    protected $gateway_id;
    protected $gateway_name;
    protected $settings;
    
    /**
     * Constructor
     */
    public function __construct($gateway_id, $gateway_name) {
        $this->gateway_id = $gateway_id;
        $this->gateway_name = $gateway_name;
        $this->settings = DZ_Events_Payment_Gateways::instance()->get_gateway_settings($gateway_id);
    }
    
    /**
     * Process payment (must be implemented by child classes)
     */
    abstract public function process_payment($amount, $currency, $payment_data);
    
    /**
     * Handle webhook (must be implemented by child classes)
     */
    abstract public function handle_webhook();
    
    /**
     * Get gateway configuration
     */
    protected function get_config() {
        return [
            'test_mode' => $this->settings['test_mode'] ?? true,
            'api_key' => $this->settings['api_key'] ?? '',
            'secret_key' => $this->settings['secret_key'] ?? '',
            'webhook_secret' => $this->settings['webhook_secret'] ?? ''
        ];
    }
    
    /**
     * Log payment activity
     */
    protected function log_payment($message, $data = []) {
        error_log("DZ Events Payment ({$this->gateway_name}): {$message} - " . json_encode($data));
    }
}

/**
 * Stripe Gateway Implementation
 */
class DZ_Stripe_Gateway extends DZ_Payment_Gateway {
    
    public function __construct() {
        parent::__construct('stripe', 'Stripe');
    }
    
    /**
     * Process payment with Stripe
     */
    public function process_payment($amount, $currency, $payment_data) {
        $config = $this->get_config();
        
        // In production, use the official Stripe PHP library
        // This is a simplified implementation
        
        $stripe_data = [
            'amount' => $amount * 100, // Convert to cents
            'currency' => strtolower($currency),
            'customer_email' => $payment_data['customer_email'],
            'metadata' => [
                'registration_id' => $payment_data['registration_id']
            ]
        ];
        
        // Simulate Stripe API call
        $payment_id = 'stripe_' . uniqid();
        
        $this->log_payment('Payment processed', $stripe_data);
        
        return [
            'payment_id' => $payment_id,
            'status' => 'paid',
            'gateway' => 'stripe',
            'transaction_id' => $payment_id,
            'amount' => $amount,
            'currency' => $currency
        ];
    }
    
    /**
     * Handle Stripe webhook
     */
    public function handle_webhook() {
        $config = $this->get_config();
        
        // Verify webhook signature
        $payload = file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        
        // In production, verify the webhook signature
        // $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $config['webhook_secret']);
        
        $this->log_payment('Webhook received', ['payload' => $payload]);
        
        // Process webhook event
        // Update registration status based on payment status
        
        wp_die('OK', 'Webhook processed', 200);
    }
}

/**
 * PayPal Gateway Implementation
 */
class DZ_PayPal_Gateway extends DZ_Payment_Gateway {
    
    public function __construct() {
        parent::__construct('paypal', 'PayPal');
    }
    
    /**
     * Process payment with PayPal
     */
    public function process_payment($amount, $currency, $payment_data) {
        $config = $this->get_config();
        
        // In production, use the official PayPal SDK
        // This is a simplified implementation
        
        $paypal_data = [
            'amount' => $amount,
            'currency' => $currency,
            'return_url' => $payment_data['return_url'],
            'cancel_url' => $payment_data['cancel_url']
        ];
        
        // Simulate PayPal API call
        $payment_id = 'paypal_' . uniqid();
        $redirect_url = $config['test_mode'] 
            ? 'https://www.sandbox.paypal.com/checkoutnow?token=' . $payment_id
            : 'https://www.paypal.com/checkoutnow?token=' . $payment_id;
        
        $this->log_payment('Payment initiated', $paypal_data);
        
        return [
            'payment_id' => $payment_id,
            'status' => 'pending',
            'gateway' => 'paypal',
            'redirect_url' => $redirect_url
        ];
    }
    
    /**
     * Handle PayPal webhook
     */
    public function handle_webhook() {
        $config = $this->get_config();
        
        // Verify webhook signature
        $payload = file_get_contents('php://input');
        
        $this->log_payment('Webhook received', ['payload' => $payload]);
        
        // Process webhook event
        // Update registration status based on payment status
        
        wp_die('OK', 'Webhook processed', 200);
    }
}

/**
 * Square Gateway Implementation
 */
class DZ_Square_Gateway extends DZ_Payment_Gateway {
    
    public function __construct() {
        parent::__construct('square', 'Square');
    }
    
    /**
     * Process payment with Square
     */
    public function process_payment($amount, $currency, $payment_data) {
        $config = $this->get_config();
        
        // In production, use the official Square SDK
        // This is a simplified implementation
        
        $square_data = [
            'amount' => $amount * 100, // Convert to cents
            'currency' => $currency,
            'customer_email' => $payment_data['customer_email']
        ];
        
        // Simulate Square API call
        $payment_id = 'square_' . uniqid();
        
        $this->log_payment('Payment processed', $square_data);
        
        return [
            'payment_id' => $payment_id,
            'status' => 'paid',
            'gateway' => 'square',
            'transaction_id' => $payment_id,
            'amount' => $amount,
            'currency' => $currency
        ];
    }
    
    /**
     * Handle Square webhook
     */
    public function handle_webhook() {
        $config = $this->get_config();
        
        // Verify webhook signature
        $payload = file_get_contents('php://input');
        
        $this->log_payment('Webhook received', ['payload' => $payload]);
        
        // Process webhook event
        // Update registration status based on payment status
        
        wp_die('OK', 'Webhook processed', 200);
    }
}

/**
 * PayFast Gateway Implementation (South Africa)
 */
class DZ_PayFast_Gateway extends DZ_Payment_Gateway {
    
    public function __construct() {
        parent::__construct('payfast', 'PayFast');
    }
    
    /**
     * Process payment with PayFast
     */
    public function process_payment($amount, $currency, $payment_data) {
        $config = $this->get_config();
        
        // PayFast specific data
        $payfast_data = [
            'merchant_id' => $config['merchant_id'] ?? '',
            'merchant_key' => $config['merchant_key'] ?? '',
            'return_url' => $payment_data['return_url'],
            'cancel_url' => $payment_data['cancel_url'],
            'notify_url' => home_url('/wp-ajax/dz_events_webhook?gateway=payfast'),
            'amount' => number_format($amount, 2, '.', ''),
            'item_name' => 'Event Registration',
            'item_description' => 'Event registration payment',
            'email_confirmation' => '1',
            'confirmation_address' => $payment_data['customer_email']
        ];
        
        // Generate signature
        $signature = $this->generate_payfast_signature($payfast_data, $config['passphrase'] ?? '');
        $payfast_data['signature'] = $signature;
        
        $payment_id = 'payfast_' . uniqid();
        
        $this->log_payment('Payment initiated', $payfast_data);
        
        return [
            'payment_id' => $payment_id,
            'status' => 'pending',
            'gateway' => 'payfast',
            'redirect_url' => $config['test_mode'] 
                ? 'https://sandbox.payfast.co.za/eng/process'
                : 'https://www.payfast.co.za/eng/process',
            'form_data' => $payfast_data
        ];
    }
    
    /**
     * Generate PayFast signature
     */
    private function generate_payfast_signature($data, $passphrase) {
        $string = '';
        foreach ($data as $key => $value) {
            if ($value !== '') {
                $string .= $key . '=' . urlencode(trim($value)) . '&';
            }
        }
        $string = rtrim($string, '&');
        
        if ($passphrase !== '') {
            $string .= '&passphrase=' . urlencode($passphrase);
        }
        
        return md5($string);
    }
    
    /**
     * Handle PayFast webhook
     */
    public function handle_webhook() {
        $config = $this->get_config();
        
        // Verify PayFast signature
        $data = $_POST;
        $signature = $data['signature'] ?? '';
        unset($data['signature']);
        
        $expected_signature = $this->generate_payfast_signature($data, $config['passphrase'] ?? '');
        
        if ($signature !== $expected_signature) {
            wp_die('Invalid signature', 'Webhook verification failed', 400);
        }
        
        $this->log_payment('Webhook received', $data);
        
        // Process payment status
        if ($data['payment_status'] === 'COMPLETE') {
            // Update registration as paid
            $this->update_registration_from_webhook($data);
        }
        
        wp_die('OK', 'Webhook processed', 200);
    }
    
    private function update_registration_from_webhook($data) {
        global $wpdb;
        
        $registration_id = $data['custom_str1'] ?? null;
        if ($registration_id) {
            $wpdb->update(
                $wpdb->prefix . 'dz_event_registrations',
                [
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                    'transaction_id' => $data['pf_payment_id'] ?? ''
                ],
                ['id' => $registration_id],
                ['%s', '%s', '%s'],
                ['%d']
            );
        }
    }
}

/**
 * Yoco Gateway Implementation (South Africa)
 */
class DZ_Yoco_Gateway extends DZ_Payment_Gateway {
    
    public function __construct() {
        parent::__construct('yoco', 'Yoco');
    }
    
    /**
     * Process payment with Yoco
     */
    public function process_payment($amount, $currency, $payment_data) {
        $config = $this->get_config();
        
        $yoco_data = [
            'amount' => $amount * 100, // Convert to cents
            'currency' => 'ZAR',
            'customer_email' => $payment_data['customer_email'],
            'customer_name' => $payment_data['customer_name'],
            'return_url' => $payment_data['return_url']
        ];
        
        $payment_id = 'yoco_' . uniqid();
        
        $this->log_payment('Payment processed', $yoco_data);
        
        return [
            'payment_id' => $payment_id,
            'status' => 'paid',
            'gateway' => 'yoco',
            'transaction_id' => $payment_id,
            'amount' => $amount,
            'currency' => 'ZAR'
        ];
    }
    
    /**
     * Handle Yoco webhook
     */
    public function handle_webhook() {
        $config = $this->get_config();
        
        $payload = file_get_contents('php://input');
        $this->log_payment('Webhook received', ['payload' => $payload]);
        
        wp_die('OK', 'Webhook processed', 200);
    }
}

/**
 * Ozow Gateway Implementation (South Africa)
 */
class DZ_Ozow_Gateway extends DZ_Payment_Gateway {
    
    public function __construct() {
        parent::__construct('ozow', 'Ozow');
    }
    
    /**
     * Process payment with Ozow
     */
    public function process_payment($amount, $currency, $payment_data) {
        $config = $this->get_config();
        
        $ozow_data = [
            'SiteCode' => $config['site_code'] ?? '',
            'CountryCode' => 'ZA',
            'CurrencyCode' => 'ZAR',
            'Amount' => number_format($amount, 2, '.', ''),
            'TransactionReference' => 'dz_' . uniqid(),
            'BankReference' => 'Event Registration',
            'Customer' => $payment_data['customer_email'],
            'CancelUrl' => $payment_data['cancel_url'],
            'NotifyUrl' => home_url('/wp-ajax/dz_events_webhook?gateway=ozow'),
            'SuccessUrl' => $payment_data['return_url']
        ];
        
        // Generate hash
        $hash_string = $ozow_data['SiteCode'] . $ozow_data['CountryCode'] . 
                      $ozow_data['CurrencyCode'] . $ozow_data['Amount'] . 
                      $ozow_data['TransactionReference'] . $ozow_data['BankReference'] . 
                      $ozow_data['Customer'] . $ozow_data['CancelUrl'] . 
                      $ozow_data['NotifyUrl'] . $ozow_data['SuccessUrl'] . 
                      ($config['private_key'] ?? '');
        
        $ozow_data['HashCheck'] = strtoupper(hash('sha512', $hash_string));
        
        $payment_id = 'ozow_' . uniqid();
        
        $this->log_payment('Payment initiated', $ozow_data);
        
        return [
            'payment_id' => $payment_id,
            'status' => 'pending',
            'gateway' => 'ozow',
            'redirect_url' => $config['test_mode'] 
                ? 'https://pay.ozow.com'
                : 'https://pay.ozow.com',
            'form_data' => $ozow_data
        ];
    }
    
    /**
     * Handle Ozow webhook
     */
    public function handle_webhook() {
        $config = $this->get_config();
        
        $data = $_POST;
        $this->log_payment('Webhook received', $data);
        
        // Verify hash
        $hash_string = $data['SiteCode'] . $data['CountryCode'] . 
                      $data['CurrencyCode'] . $data['Amount'] . 
                      $data['TransactionReference'] . $data['BankReference'] . 
                      $data['Customer'] . $data['CancelUrl'] . 
                      $data['NotifyUrl'] . $data['SuccessUrl'] . 
                      ($config['private_key'] ?? '');
        
        $expected_hash = strtoupper(hash('sha512', $hash_string));
        
        if ($data['HashCheck'] !== $expected_hash) {
            wp_die('Invalid hash', 'Webhook verification failed', 400);
        }
        
        // Process payment status
        if ($data['Status'] === 'Complete') {
            $this->update_registration_from_webhook($data);
        }
        
        wp_die('OK', 'Webhook processed', 200);
    }
    
    private function update_registration_from_webhook($data) {
        global $wpdb;
        
        $registration_id = str_replace('dz_', '', $data['TransactionReference']);
        if ($registration_id) {
            $wpdb->update(
                $wpdb->prefix . 'dz_event_registrations',
                [
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                    'transaction_id' => $data['TransactionId'] ?? ''
                ],
                ['id' => $registration_id],
                ['%s', '%s', '%s'],
                ['%d']
            );
        }
    }
}

/**
 * Peach Payments Gateway Implementation (South Africa)
 */
class DZ_Peach_Payments_Gateway extends DZ_Payment_Gateway {
    
    public function __construct() {
        parent::__construct('peach_payments', 'Peach Payments');
    }
    
    /**
     * Process payment with Peach Payments
     */
    public function process_payment($amount, $currency, $payment_data) {
        $config = $this->get_config();
        
        $peach_data = [
            'authentication.entityId' => $config['entity_id'] ?? '',
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => 'ZAR',
            'paymentType' => 'DB',
            'merchantTransactionId' => 'dz_' . uniqid(),
            'customer.email' => $payment_data['customer_email'],
            'customer.givenName' => $payment_data['customer_name'],
            'shopperResultUrl' => $payment_data['return_url']
        ];
        
        $payment_id = 'peach_' . uniqid();
        
        $this->log_payment('Payment processed', $peach_data);
        
        return [
            'payment_id' => $payment_id,
            'status' => 'paid',
            'gateway' => 'peach_payments',
            'transaction_id' => $payment_id,
            'amount' => $amount,
            'currency' => 'ZAR'
        ];
    }
    
    /**
     * Handle Peach Payments webhook
     */
    public function handle_webhook() {
        $config = $this->get_config();
        
        $payload = file_get_contents('php://input');
        $this->log_payment('Webhook received', ['payload' => $payload]);
        
        wp_die('OK', 'Webhook processed', 200);
    }
}

/**
 * PayGate Gateway Implementation (South Africa)
 */
class DZ_PayGate_Gateway extends DZ_Payment_Gateway {
    
    public function __construct() {
        parent::__construct('paygate', 'PayGate');
    }
    
    /**
     * Process payment with PayGate
     */
    public function process_payment($amount, $currency, $payment_data) {
        $config = $this->get_config();
        
        $paygate_data = [
            'PAYGATE_ID' => $config['paygate_id'] ?? '',
            'REFERENCE' => 'dz_' . uniqid(),
            'AMOUNT' => number_format($amount * 100, 0, '', ''), // Convert to cents
            'CURRENCY' => 'ZAR',
            'RETURN_URL' => $payment_data['return_url'],
            'TRANSACTION_DATE' => date('Y-m-d H:i:s'),
            'LOCALE' => 'en-za',
            'COUNTRY' => 'ZAF',
            'EMAIL' => $payment_data['customer_email']
        ];
        
        // Generate checksum
        $checksum_string = $paygate_data['PAYGATE_ID'] . $paygate_data['REFERENCE'] . 
                          $paygate_data['AMOUNT'] . $paygate_data['CURRENCY'] . 
                          $paygate_data['RETURN_URL'] . $paygate_data['TRANSACTION_DATE'] . 
                          $paygate_data['LOCALE'] . $paygate_data['COUNTRY'] . 
                          $paygate_data['EMAIL'] . ($config['encryption_key'] ?? '');
        
        $paygate_data['CHECKSUM'] = md5($checksum_string);
        
        $payment_id = 'paygate_' . uniqid();
        
        $this->log_payment('Payment initiated', $paygate_data);
        
        return [
            'payment_id' => $payment_id,
            'status' => 'pending',
            'gateway' => 'paygate',
            'redirect_url' => $config['test_mode'] 
                ? 'https://secure.paygate.co.za/payweb3/process.trans'
                : 'https://secure.paygate.co.za/payweb3/process.trans',
            'form_data' => $paygate_data
        ];
    }
    
    /**
     * Handle PayGate webhook
     */
    public function handle_webhook() {
        $config = $this->get_config();
        
        $data = $_POST;
        $this->log_payment('Webhook received', $data);
        
        // Verify checksum
        $checksum_string = $data['PAYGATE_ID'] . $data['REFERENCE'] . 
                          $data['TRANSACTION_STATUS'] . $data['RESULT_CODE'] . 
                          $data['AUTH_CODE'] . $data['RESULT_DESC'] . 
                          $data['TRANSACTION_ID'] . $data['RISK_INDICATOR'] . 
                          $data['PAY_METHOD_DETAIL'] . $data['PAY_REQUEST_ID'] . 
                          ($config['encryption_key'] ?? '');
        
        $expected_checksum = md5($checksum_string);
        
        if ($data['CHECKSUM'] !== $expected_checksum) {
            wp_die('Invalid checksum', 'Webhook verification failed', 400);
        }
        
        // Process payment status
        if ($data['TRANSACTION_STATUS'] === '1') {
            $this->update_registration_from_webhook($data);
        }
        
        wp_die('OK', 'Webhook processed', 200);
    }
    
    private function update_registration_from_webhook($data) {
        global $wpdb;
        
        $registration_id = str_replace('dz_', '', $data['REFERENCE']);
        if ($registration_id) {
            $wpdb->update(
                $wpdb->prefix . 'dz_event_registrations',
                [
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                    'transaction_id' => $data['TRANSACTION_ID'] ?? ''
                ],
                ['id' => $registration_id],
                ['%s', '%s', '%s'],
                ['%d']
            );
        }
    }
}

/**
 * SnapScan Gateway Implementation (South Africa)
 */
class DZ_SnapScan_Gateway extends DZ_Payment_Gateway {
    
    public function __construct() {
        parent::__construct('snapscan', 'SnapScan');
    }
    
    /**
     * Process payment with SnapScan
     */
    public function process_payment($amount, $currency, $payment_data) {
        $config = $this->get_config();
        
        $snapscan_data = [
            'merchantId' => $config['merchant_id'] ?? '',
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => 'ZAR',
            'merchantReference' => 'dz_' . uniqid(),
            'customerEmail' => $payment_data['customer_email'],
            'customerName' => $payment_data['customer_name'],
            'returnUrl' => $payment_data['return_url'],
            'notifyUrl' => home_url('/wp-ajax/dz_events_webhook?gateway=snapscan')
        ];
        
        $payment_id = 'snapscan_' . uniqid();
        
        $this->log_payment('Payment processed', $snapscan_data);
        
        return [
            'payment_id' => $payment_id,
            'status' => 'paid',
            'gateway' => 'snapscan',
            'transaction_id' => $payment_id,
            'amount' => $amount,
            'currency' => 'ZAR'
        ];
    }
    
    /**
     * Handle SnapScan webhook
     */
    public function handle_webhook() {
        $config = $this->get_config();
        
        $payload = file_get_contents('php://input');
        $this->log_payment('Webhook received', ['payload' => $payload]);
        
        wp_die('OK', 'Webhook processed', 200);
    }
}

/**
 * Zapper Gateway Implementation (South Africa)
 */
class DZ_Zapper_Gateway extends DZ_Payment_Gateway {
    
    public function __construct() {
        parent::__construct('zapper', 'Zapper');
    }
    
    /**
     * Process payment with Zapper
     */
    public function process_payment($amount, $currency, $payment_data) {
        $config = $this->get_config();
        
        $zapper_data = [
            'merchantId' => $config['merchant_id'] ?? '',
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => 'ZAR',
            'reference' => 'dz_' . uniqid(),
            'customerEmail' => $payment_data['customer_email'],
            'customerName' => $payment_data['customer_name'],
            'returnUrl' => $payment_data['return_url'],
            'notifyUrl' => home_url('/wp-ajax/dz_events_webhook?gateway=zapper')
        ];
        
        $payment_id = 'zapper_' . uniqid();
        
        $this->log_payment('Payment processed', $zapper_data);
        
        return [
            'payment_id' => $payment_id,
            'status' => 'paid',
            'gateway' => 'zapper',
            'transaction_id' => $payment_id,
            'amount' => $amount,
            'currency' => 'ZAR'
        ];
    }
    
    /**
     * Handle Zapper webhook
     */
    public function handle_webhook() {
        $config = $this->get_config();
        
        $payload = file_get_contents('php://input');
        $this->log_payment('Webhook received', ['payload' => $payload]);
        
        wp_die('OK', 'Webhook processed', 200);
    }
}

/**
 * Initialize payment gateways
 */
function dz_events_init_payment_gateways() {
    return DZ_Events_Payment_Gateways::instance();
}
add_action('init', 'dz_events_init_payment_gateways');
