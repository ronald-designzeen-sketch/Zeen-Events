<?php
/**
 * Advanced Elementor Widgets for Zeen Events
 * 
 * This file implements innovative Elementor widgets that provide
 * unique functionality not found in other event plugins
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
 * Advanced Elementor Widgets Class
 * 
 * Provides unique, industry-leading Elementor widgets
 */
class DZ_Events_Elementor_Widgets_Advanced {
    
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
        add_action('elementor/widgets/widgets_registered', [$this, 'register_advanced_widgets']);
        add_action('elementor/elements/categories_registered', [$this, 'add_widget_categories']);
    }
    
    /**
     * Register advanced widgets
     */
    public function register_advanced_widgets($widgets_manager) {
        // Event Countdown Timer Widget
        $widgets_manager->register_widget_type(new DZ_Event_Countdown_Timer_Widget());
        
        // Event Progress Bar Widget
        $widgets_manager->register_widget_type(new DZ_Event_Progress_Bar_Widget());
        
        // Event Weather Widget
        $widgets_manager->register_widget_type(new DZ_Event_Weather_Widget());
        
        // Event Social Proof Widget
        $widgets_manager->register_widget_type(new DZ_Event_Social_Proof_Widget());
        
        // Event Interactive Map Widget
        $widgets_manager->register_widget_type(new DZ_Event_Interactive_Map_Widget());
        
        // Event Live Chat Widget
        $widgets_manager->register_widget_type(new DZ_Event_Live_Chat_Widget());
        
        // Event Polls & Surveys Widget
        $widgets_manager->register_widget_type(new DZ_Event_Polls_Widget());
        
        // Event Networking Widget
        $widgets_manager->register_widget_type(new DZ_Event_Networking_Widget());
        
        // Event Gamification Widget
        $widgets_manager->register_widget_type(new DZ_Event_Gamification_Widget());
        
        // Event AI Assistant Widget
        $widgets_manager->register_widget_type(new DZ_Event_AI_Assistant_Widget());
    }
    
    /**
     * Add widget categories
     */
    public function add_widget_categories($elements_manager) {
        $elements_manager->add_category(
            'dz-events-advanced',
            [
                'title' => 'Zeen Events Advanced',
                'icon' => 'fa fa-calendar-plus'
            ]
        );
    }
}

/**
 * Event Countdown Timer Widget
 * 
 * Unique feature: Real-time countdown with multiple display formats
 */
class DZ_Event_Countdown_Timer_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'dz_event_countdown_timer';
    }
    
    public function get_title() {
        return 'Event Countdown Timer';
    }
    
    public function get_icon() {
        return 'eicon-countdown';
    }
    
    public function get_categories() {
        return ['dz-events-advanced'];
    }
    
    protected function _register_controls() {
        // Content Tab
        $this->start_controls_section(
            'content_section',
            [
                'label' => 'Event Selection',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'event_id',
            [
                'label' => 'Select Event',
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_events_options(),
                'default' => '',
            ]
        );
        
        $this->add_control(
            'display_format',
            [
                'label' => 'Display Format',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'full' => 'Full (Days, Hours, Minutes, Seconds)',
                    'compact' => 'Compact (DD:HH:MM:SS)',
                    'minimal' => 'Minimal (Days only)',
                    'custom' => 'Custom Format'
                ],
                'default' => 'full',
            ]
        );
        
        $this->add_control(
            'custom_format',
            [
                'label' => 'Custom Format',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '{days} days, {hours} hours, {minutes} minutes',
                'condition' => [
                    'display_format' => 'custom'
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Style Tab
        $this->start_controls_section(
            'style_section',
            [
                'label' => 'Timer Style',
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'timer_style',
            [
                'label' => 'Timer Style',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'default' => 'Default',
                    'circular' => 'Circular Progress',
                    'flip' => 'Flip Cards',
                    'digital' => 'Digital Display',
                    'minimal' => 'Minimal Text'
                ],
                'default' => 'default',
            ]
        );
        
        $this->add_control(
            'primary_color',
            [
                'label' => 'Primary Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#0073aa',
            ]
        );
        
        $this->add_control(
            'text_color',
            [
                'label' => 'Text Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        $event_id = $settings['event_id'];
        
        if (!$event_id) {
            echo '<p>Please select an event to display the countdown timer.</p>';
            return;
        }
        
        $event_date = get_post_meta($event_id, '_dz_event_start', true);
        $event_time = get_post_meta($event_id, '_dz_event_time_start', true);
        
        if (!$event_date) {
            echo '<p>Event date not found.</p>';
            return;
        }
        
        $datetime = $event_date . ' ' . $event_time;
        $timestamp = strtotime($datetime);
        
        ?>
        <div class="dz-countdown-timer" 
             data-timestamp="<?php echo esc_attr($timestamp); ?>"
             data-format="<?php echo esc_attr($settings['display_format']); ?>"
             data-custom-format="<?php echo esc_attr($settings['custom_format']); ?>"
             data-style="<?php echo esc_attr($settings['timer_style']); ?>"
             data-primary-color="<?php echo esc_attr($settings['primary_color']); ?>"
             data-text-color="<?php echo esc_attr($settings['text_color']); ?>">
            
            <div class="dz-timer-display">
                <div class="dz-timer-days">
                    <span class="dz-timer-number">00</span>
                    <span class="dz-timer-label">Days</span>
                </div>
                <div class="dz-timer-hours">
                    <span class="dz-timer-number">00</span>
                    <span class="dz-timer-label">Hours</span>
                </div>
                <div class="dz-timer-minutes">
                    <span class="dz-timer-number">00</span>
                    <span class="dz-timer-label">Minutes</span>
                </div>
                <div class="dz-timer-seconds">
                    <span class="dz-timer-number">00</span>
                    <span class="dz-timer-label">Seconds</span>
                </div>
            </div>
            
            <div class="dz-timer-message" style="display: none;">
                <p>The event has started!</p>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            DZ_Events_Countdown_Timer.init();
        });
        </script>
        <?php
    }
    
    private function get_events_options() {
        $events = get_posts([
            'post_type' => 'dz_event',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ]);
        
        $options = [];
        foreach ($events as $event) {
            $options[$event->ID] = $event->post_title;
        }
        
        return $options;
    }
}

/**
 * Event Progress Bar Widget
 * 
 * Unique feature: Shows event progress (registrations, capacity, time remaining)
 */
class DZ_Event_Progress_Bar_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'dz_event_progress_bar';
    }
    
    public function get_title() {
        return 'Event Progress Bar';
    }
    
    public function get_icon() {
        return 'eicon-progress-tracker';
    }
    
    public function get_categories() {
        return ['dz-events-advanced'];
    }
    
    protected function _register_controls() {
        // Content Tab
        $this->start_controls_section(
            'content_section',
            [
                'label' => 'Progress Settings',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'event_id',
            [
                'label' => 'Select Event',
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_events_options(),
                'default' => '',
            ]
        );
        
        $this->add_control(
            'progress_type',
            [
                'label' => 'Progress Type',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'registrations' => 'Registration Progress',
                    'time_remaining' => 'Time Remaining',
                    'capacity' => 'Capacity Filled',
                    'custom' => 'Custom Progress'
                ],
                'default' => 'registrations',
            ]
        );
        
        $this->add_control(
            'show_percentage',
            [
                'label' => 'Show Percentage',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => 'Yes',
                'label_off' => 'No',
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'show_numbers',
            [
                'label' => 'Show Numbers',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => 'Yes',
                'label_off' => 'No',
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        $event_id = $settings['event_id'];
        
        if (!$event_id) {
            echo '<p>Please select an event to display the progress bar.</p>';
            return;
        }
        
        $progress_data = $this->get_progress_data($event_id, $settings['progress_type']);
        
        ?>
        <div class="dz-progress-bar-widget">
            <div class="dz-progress-header">
                <h4 class="dz-progress-title"><?php echo esc_html($progress_data['title']); ?></h4>
                <?php if ($settings['show_percentage'] === 'yes') : ?>
                    <span class="dz-progress-percentage"><?php echo esc_html($progress_data['percentage']); ?>%</span>
                <?php endif; ?>
            </div>
            
            <div class="dz-progress-bar-container">
                <div class="dz-progress-bar" 
                     data-progress="<?php echo esc_attr($progress_data['percentage']); ?>"
                     style="width: <?php echo esc_attr($progress_data['percentage']); ?>%;">
                </div>
            </div>
            
            <?php if ($settings['show_numbers'] === 'yes') : ?>
                <div class="dz-progress-numbers">
                    <span class="dz-current"><?php echo esc_html($progress_data['current']); ?></span>
                    <span class="dz-separator">/</span>
                    <span class="dz-total"><?php echo esc_html($progress_data['total']); ?></span>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    private function get_progress_data($event_id, $type) {
        switch ($type) {
            case 'registrations':
                $capacity = intval(get_post_meta($event_id, '_dz_event_capacity', true));
                $registered = $this->get_registration_count($event_id);
                $percentage = $capacity > 0 ? round(($registered / $capacity) * 100) : 0;
                
                return [
                    'title' => 'Registration Progress',
                    'current' => $registered,
                    'total' => $capacity,
                    'percentage' => $percentage
                ];
                
            case 'time_remaining':
                $event_date = get_post_meta($event_id, '_dz_event_start', true);
                $event_time = get_post_meta($event_id, '_dz_event_time_start', true);
                $datetime = $event_date . ' ' . $event_time;
                $timestamp = strtotime($datetime);
                $now = time();
                $total_seconds = $timestamp - $now;
                $days_remaining = max(0, floor($total_seconds / 86400));
                $total_days = 30; // Assuming 30 days from event creation
                $percentage = min(100, round((($total_days - $days_remaining) / $total_days) * 100));
                
                return [
                    'title' => 'Time Remaining',
                    'current' => $days_remaining,
                    'total' => $total_days,
                    'percentage' => $percentage
                ];
                
            default:
                return [
                    'title' => 'Progress',
                    'current' => 0,
                    'total' => 100,
                    'percentage' => 0
                ];
        }
    }
    
    private function get_registration_count($event_id) {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}dz_event_registrations WHERE event_id = %d AND status = 'confirmed'",
            $event_id
        ));
    }
    
    private function get_events_options() {
        $events = get_posts([
            'post_type' => 'dz_event',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ]);
        
        $options = [];
        foreach ($events as $event) {
            $options[$event->ID] = $event->post_title;
        }
        
        return $options;
    }
}

/**
 * Event Weather Widget
 * 
 * Unique feature: Shows weather forecast for event location and date
 */
class DZ_Event_Weather_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'dz_event_weather';
    }
    
    public function get_title() {
        return 'Event Weather Forecast';
    }
    
    public function get_icon() {
        return 'eicon-weather';
    }
    
    public function get_categories() {
        return ['dz-events-advanced'];
    }
    
    protected function _register_controls() {
        // Content Tab
        $this->start_controls_section(
            'content_section',
            [
                'label' => 'Weather Settings',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'event_id',
            [
                'label' => 'Select Event',
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_events_options(),
                'default' => '',
            ]
        );
        
        $this->add_control(
            'weather_days',
            [
                'label' => 'Forecast Days',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '1' => '1 Day',
                    '3' => '3 Days',
                    '5' => '5 Days',
                    '7' => '7 Days'
                ],
                'default' => '3',
            ]
        );
        
        $this->add_control(
            'show_details',
            [
                'label' => 'Show Detailed Info',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => 'Yes',
                'label_off' => 'No',
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        $event_id = $settings['event_id'];
        
        if (!$event_id) {
            echo '<p>Please select an event to display the weather forecast.</p>';
            return;
        }
        
        $location = get_post_meta($event_id, '_dz_event_location', true);
        $event_date = get_post_meta($event_id, '_dz_event_start', true);
        
        if (!$location) {
            echo '<p>Event location not found.</p>';
            return;
        }
        
        ?>
        <div class="dz-weather-widget" 
             data-location="<?php echo esc_attr($location); ?>"
             data-event-date="<?php echo esc_attr($event_date); ?>"
             data-days="<?php echo esc_attr($settings['weather_days']); ?>"
             data-show-details="<?php echo esc_attr($settings['show_details']); ?>">
            
            <div class="dz-weather-header">
                <h4 class="dz-weather-title">Weather Forecast</h4>
                <span class="dz-weather-location"><?php echo esc_html($location); ?></span>
            </div>
            
            <div class="dz-weather-forecast">
                <div class="dz-weather-loading">Loading weather data...</div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            DZ_Events_Weather_Widget.init();
        });
        </script>
        <?php
    }
    
    private function get_events_options() {
        $events = get_posts([
            'post_type' => 'dz_event',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ]);
        
        $options = [];
        foreach ($events as $event) {
            $options[$event->ID] = $event->post_title;
        }
        
        return $options;
    }
}

// Initialize advanced Elementor widgets
function dz_events_init_advanced_elementor_widgets() {
    return DZ_Events_Elementor_Widgets_Advanced::instance();
}
add_action('elementor/init', 'dz_events_init_advanced_elementor_widgets');
