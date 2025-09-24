<?php
/**
 * Setup Wizard for Zeen Events
 * 
 * This file implements an easy-to-use setup wizard
 * that guides users through plugin configuration
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
 * Setup Wizard Class
 * 
 * Provides an intuitive setup experience
 */
class DZ_Events_Setup_Wizard {
    
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
        add_action('admin_menu', [$this, 'add_setup_wizard_menu']);
        add_action('admin_init', [$this, 'handle_setup_wizard']);
        add_action('wp_ajax_dz_events_setup_wizard', [$this, 'ajax_setup_wizard']);
    }
    
    /**
     * Add setup wizard menu
     */
    public function add_setup_wizard_menu() {
        add_submenu_page(
            'edit.php?post_type=dz_event',
            'Setup Wizard',
            'Setup Wizard',
            'manage_options',
            'dz-events-setup-wizard',
            [$this, 'render_setup_wizard']
        );
    }
    
    /**
     * Handle setup wizard
     */
    public function handle_setup_wizard() {
        if (isset($_GET['page']) && $_GET['page'] === 'dz-events-setup-wizard') {
            // Check if setup is already completed
            $setup_completed = get_option('dz_events_setup_completed', false);
            
            if ($setup_completed && !isset($_GET['restart'])) {
                wp_redirect(admin_url('edit.php?post_type=dz_event'));
                exit;
            }
        }
    }
    
    /**
     * Render setup wizard
     */
    public function render_setup_wizard() {
        $current_step = isset($_GET['step']) ? intval($_GET['step']) : 1;
        $total_steps = 5;
        
        ?>
        <div class="wrap dz-setup-wizard">
            <div class="dz-setup-header">
                <h1>Zeen Events Setup Wizard</h1>
                <p>Let's get your events plugin configured in just a few steps!</p>
            </div>
            
            <div class="dz-setup-progress">
                <div class="dz-progress-bar">
                    <div class="dz-progress-fill" style="width: <?php echo ($current_step / $total_steps) * 100; ?>%"></div>
                </div>
                <div class="dz-progress-steps">
                    <?php for ($i = 1; $i <= $total_steps; $i++) : ?>
                        <div class="dz-step <?php echo $i <= $current_step ? 'active' : ''; ?>">
                            <span class="dz-step-number"><?php echo $i; ?></span>
                            <span class="dz-step-label"><?php echo $this->get_step_label($i); ?></span>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
            
            <div class="dz-setup-content">
                <?php $this->render_step($current_step); ?>
            </div>
        </div>
        
        <style>
        .dz-setup-wizard {
            max-width: 800px;
            margin: 20px auto;
        }
        
        .dz-setup-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .dz-setup-progress {
            margin-bottom: 40px;
        }
        
        .dz-progress-bar {
            height: 4px;
            background: #e1e5e9;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .dz-progress-fill {
            height: 100%;
            background: #0073aa;
            transition: width 0.3s ease;
        }
        
        .dz-progress-steps {
            display: flex;
            justify-content: space-between;
        }
        
        .dz-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            opacity: 0.5;
            transition: opacity 0.3s ease;
        }
        
        .dz-step.active {
            opacity: 1;
        }
        
        .dz-step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e1e5e9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .dz-step.active .dz-step-number {
            background: #0073aa;
            color: white;
        }
        
        .dz-step-label {
            font-size: 12px;
            text-align: center;
        }
        
        .dz-setup-content {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .dz-step-content h2 {
            margin-top: 0;
            color: #0073aa;
        }
        
        .dz-form-group {
            margin-bottom: 20px;
        }
        
        .dz-form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .dz-form-group input,
        .dz-form-group select,
        .dz-form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .dz-form-group .description {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .dz-setup-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        
        .dz-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        
        .dz-btn-primary {
            background: #0073aa;
            color: white;
        }
        
        .dz-btn-secondary {
            background: #f1f1f1;
            color: #333;
        }
        
        .dz-btn:hover {
            opacity: 0.9;
        }
        
        .dz-step-preview {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 4px;
            margin-top: 20px;
        }
        </style>
        <?php
    }
    
    /**
     * Render specific step
     */
    private function render_step($step) {
        switch ($step) {
            case 1:
                $this->render_welcome_step();
                break;
            case 2:
                $this->render_basic_settings_step();
                break;
            case 3:
                $this->render_display_settings_step();
                break;
            case 4:
                $this->render_integrations_step();
                break;
            case 5:
                $this->render_completion_step();
                break;
        }
    }
    
    /**
     * Render welcome step
     */
    private function render_welcome_step() {
        ?>
        <div class="dz-step-content">
            <h2>Welcome to Zeen Events!</h2>
            <p>Thank you for choosing Zeen Events, the most powerful and user-friendly event management plugin for WordPress.</p>
            
            <div class="dz-features-list">
                <h3>What you'll get:</h3>
                <ul>
                    <li>✅ Easy event creation and management</li>
                    <li>✅ Beautiful event displays with multiple layouts</li>
                    <li>✅ Advanced registration and ticketing system</li>
                    <li>✅ Payment gateway integrations</li>
                    <li>✅ Real-time analytics and reporting</li>
                    <li>✅ Mobile-responsive design</li>
                    <li>✅ SEO optimization</li>
                    <li>✅ Multi-site support</li>
                </ul>
            </div>
            
            <div class="dz-setup-actions">
                <div></div>
                <a href="?page=dz-events-setup-wizard&step=2" class="dz-btn dz-btn-primary">Get Started</a>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render basic settings step
     */
    private function render_basic_settings_step() {
        ?>
        <div class="dz-step-content">
            <h2>Basic Settings</h2>
            <p>Let's configure the basic settings for your events.</p>
            
            <form method="post" action="">
                <?php wp_nonce_field('dz_setup_wizard', 'dz_setup_nonce'); ?>
                <input type="hidden" name="step" value="2">
                
                <div class="dz-form-group">
                    <label for="default_layout">Default Event Layout</label>
                    <select id="default_layout" name="default_layout">
                        <option value="grid">Grid Layout</option>
                        <option value="list">List Layout</option>
                        <option value="carousel">Carousel Layout</option>
                    </select>
                    <p class="description">Choose how events will be displayed by default.</p>
                </div>
                
                <div class="dz-form-group">
                    <label for="events_per_page">Events Per Page</label>
                    <input type="number" id="events_per_page" name="events_per_page" value="6" min="1" max="50">
                    <p class="description">Number of events to show per page.</p>
                </div>
                
                <div class="dz-form-group">
                    <label for="show_past_events">Show Past Events</label>
                    <select id="show_past_events" name="show_past_events">
                        <option value="no">No</option>
                        <option value="yes">Yes</option>
                    </select>
                    <p class="description">Whether to display past events in listings.</p>
                </div>
                
                <div class="dz-form-group">
                    <label for="primary_color">Primary Color</label>
                    <input type="color" id="primary_color" name="primary_color" value="#0073aa">
                    <p class="description">Main color for buttons and highlights.</p>
                </div>
                
                <div class="dz-setup-actions">
                    <a href="?page=dz-events-setup-wizard&step=1" class="dz-btn dz-btn-secondary">Back</a>
                    <button type="submit" class="dz-btn dz-btn-primary">Continue</button>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render display settings step
     */
    private function render_display_settings_step() {
        ?>
        <div class="dz-step-content">
            <h2>Display Settings</h2>
            <p>Customize how your events will look and behave.</p>
            
            <form method="post" action="">
                <?php wp_nonce_field('dz_setup_wizard', 'dz_setup_nonce'); ?>
                <input type="hidden" name="step" value="3">
                
                <div class="dz-form-group">
                    <label for="show_featured_image">Show Featured Images</label>
                    <select id="show_featured_image" name="show_featured_image">
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
                
                <div class="dz-form-group">
                    <label for="show_event_date">Show Event Date</label>
                    <select id="show_event_date" name="show_event_date">
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
                
                <div class="dz-form-group">
                    <label for="show_event_location">Show Event Location</label>
                    <select id="show_event_location" name="show_event_location">
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
                
                <div class="dz-form-group">
                    <label for="show_event_price">Show Event Price</label>
                    <select id="show_event_price" name="show_event_price">
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
                
                <div class="dz-form-group">
                    <label for="show_register_button">Show Register Button</label>
                    <select id="show_register_button" name="show_register_button">
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
                
                <div class="dz-setup-actions">
                    <a href="?page=dz-events-setup-wizard&step=2" class="dz-btn dz-btn-secondary">Back</a>
                    <button type="submit" class="dz-btn dz-btn-primary">Continue</button>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render integrations step
     */
    private function render_integrations_step() {
        ?>
        <div class="dz-step-content">
            <h2>Integrations</h2>
            <p>Connect your events with external services.</p>
            
            <form method="post" action="">
                <?php wp_nonce_field('dz_setup_wizard', 'dz_setup_nonce'); ?>
                <input type="hidden" name="step" value="4">
                
                <div class="dz-form-group">
                    <label for="google_maps_api">Google Maps API Key</label>
                    <input type="text" id="google_maps_api" name="google_maps_api" placeholder="Enter your Google Maps API key">
                    <p class="description">Required for interactive maps and location features.</p>
                </div>
                
                <div class="dz-form-group">
                    <label for="stripe_api_key">Stripe API Key</label>
                    <input type="text" id="stripe_api_key" name="stripe_api_key" placeholder="Enter your Stripe API key">
                    <p class="description">Required for payment processing.</p>
                </div>
                
                <div class="dz-form-group">
                    <label for="mailchimp_api">Mailchimp API Key</label>
                    <input type="text" id="mailchimp_api" name="mailchimp_api" placeholder="Enter your Mailchimp API key">
                    <p class="description">For email marketing integration.</p>
                </div>
                
                <div class="dz-form-group">
                    <label for="analytics_tracking">Enable Analytics Tracking</label>
                    <select id="analytics_tracking" name="analytics_tracking">
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                    <p class="description">Track event views, registrations, and conversions.</p>
                </div>
                
                <div class="dz-setup-actions">
                    <a href="?page=dz-events-setup-wizard&step=3" class="dz-btn dz-btn-secondary">Back</a>
                    <button type="submit" class="dz-btn dz-btn-primary">Continue</button>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render completion step
     */
    private function render_completion_step() {
        ?>
        <div class="dz-step-content">
            <h2>Setup Complete!</h2>
            <p>Congratulations! Your Zeen Events plugin is now configured and ready to use.</p>
            
            <div class="dz-completion-summary">
                <h3>What's Next?</h3>
                <ul>
                    <li>✅ Create your first event</li>
                    <li>✅ Customize event displays</li>
                    <li>✅ Set up payment processing</li>
                    <li>✅ Configure email notifications</li>
                    <li>✅ Add events to your pages</li>
                </ul>
            </div>
            
            <div class="dz-step-preview">
                <h4>Quick Start Guide:</h4>
                <ol>
                    <li>Go to <strong>Events → Add New</strong> to create your first event</li>
                    <li>Use the <strong>[dz_events]</strong> shortcode to display events on any page</li>
                    <li>Customize the appearance in <strong>Events → Settings</strong></li>
                    <li>Check out the <strong>Events Dashboard</strong> for analytics</li>
                </ol>
            </div>
            
            <div class="dz-setup-actions">
                <a href="<?php echo admin_url('post-new.php?post_type=dz_event'); ?>" class="dz-btn dz-btn-primary">Create First Event</a>
                <a href="<?php echo admin_url('edit.php?post_type=dz_event'); ?>" class="dz-btn dz-btn-secondary">Go to Events</a>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get step label
     */
    private function get_step_label($step) {
        $labels = [
            1 => 'Welcome',
            2 => 'Basic Settings',
            3 => 'Display',
            4 => 'Integrations',
            5 => 'Complete'
        ];
        
        return $labels[$step] ?? 'Step ' . $step;
    }
    
    /**
     * AJAX setup wizard
     */
    public function ajax_setup_wizard() {
        check_ajax_referer('dz_setup_wizard_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Access denied');
        }
        
        $step = intval($_POST['step']);
        $data = $_POST['data'] ?? [];
        
        // Save step data
        $this->save_step_data($step, $data);
        
        wp_send_json_success(['message' => 'Step data saved successfully']);
    }
    
    /**
     * Save step data
     */
    private function save_step_data($step, $data) {
        $settings = get_option('dz_events_settings', []);
        
        switch ($step) {
            case 2:
                $settings['default_layout'] = $data['default_layout'] ?? 'grid';
                $settings['events_per_page'] = intval($data['events_per_page'] ?? 6);
                $settings['show_past_events'] = $data['show_past_events'] ?? 'no';
                $settings['primary_color'] = $data['primary_color'] ?? '#0073aa';
                break;
                
            case 3:
                $settings['show_featured_image'] = $data['show_featured_image'] ?? 'yes';
                $settings['show_event_date'] = $data['show_event_date'] ?? 'yes';
                $settings['show_event_location'] = $data['show_event_location'] ?? 'yes';
                $settings['show_event_price'] = $data['show_event_price'] ?? 'yes';
                $settings['show_register_button'] = $data['show_register_button'] ?? 'yes';
                break;
                
            case 4:
                $settings['google_maps_api'] = $data['google_maps_api'] ?? '';
                $settings['stripe_api_key'] = $data['stripe_api_key'] ?? '';
                $settings['mailchimp_api'] = $data['mailchimp_api'] ?? '';
                $settings['analytics_tracking'] = $data['analytics_tracking'] ?? 'yes';
                break;
                
            case 5:
                update_option('dz_events_setup_completed', true);
                break;
        }
        
        update_option('dz_events_settings', $settings);
    }
}

/**
 * Initialize setup wizard
 */
function dz_events_init_setup_wizard() {
    return DZ_Events_Setup_Wizard::instance();
}
add_action('init', 'dz_events_init_setup_wizard');
