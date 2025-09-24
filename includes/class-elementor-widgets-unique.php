<?php
/**
 * Unique Elementor Widgets for Zeen Events
 * 
 * This file implements the most innovative and unique Elementor widgets
 * that provide functionality not found in any other event plugin
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
 * Unique Elementor Widgets Class
 * 
 * Provides the most innovative widgets for competitive advantage
 */
class DZ_Events_Elementor_Widgets_Unique {
    
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
        add_action('elementor/widgets/widgets_registered', [$this, 'register_unique_widgets']);
    }
    
    /**
     * Register unique widgets
     */
    public function register_unique_widgets($widgets_manager) {
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
}

/**
 * Event Social Proof Widget
 * 
 * Unique feature: Shows real-time social proof (recent registrations, testimonials, etc.)
 */
class DZ_Event_Social_Proof_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'dz_event_social_proof';
    }
    
    public function get_title() {
        return 'Event Social Proof';
    }
    
    public function get_icon() {
        return 'eicon-testimonial';
    }
    
    public function get_categories() {
        return ['dz-events-advanced'];
    }
    
    protected function _register_controls() {
        // Content Tab
        $this->start_controls_section(
            'content_section',
            [
                'label' => 'Social Proof Settings',
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
            'proof_type',
            [
                'label' => 'Social Proof Type',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'recent_registrations' => 'Recent Registrations',
                    'testimonials' => 'Event Testimonials',
                    'attendee_count' => 'Total Attendees',
                    'company_logos' => 'Company Logos',
                    'social_media' => 'Social Media Activity'
                ],
                'default' => 'recent_registrations',
            ]
        );
        
        $this->add_control(
            'display_count',
            [
                'label' => 'Number of Items',
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 20,
                'default' => 5,
            ]
        );
        
        $this->add_control(
            'auto_refresh',
            [
                'label' => 'Auto Refresh',
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
            echo '<p>Please select an event to display social proof.</p>';
            return;
        }
        
        $proof_data = $this->get_social_proof_data($event_id, $settings['proof_type'], $settings['display_count']);
        
        ?>
        <div class="dz-social-proof-widget" 
             data-event-id="<?php echo esc_attr($event_id); ?>"
             data-proof-type="<?php echo esc_attr($settings['proof_type']); ?>"
             data-auto-refresh="<?php echo esc_attr($settings['auto_refresh']); ?>">
            
            <div class="dz-social-proof-header">
                <h4 class="dz-social-proof-title"><?php echo esc_html($proof_data['title']); ?></h4>
                <span class="dz-social-proof-subtitle"><?php echo esc_html($proof_data['subtitle']); ?></span>
            </div>
            
            <div class="dz-social-proof-content">
                <?php foreach ($proof_data['items'] as $item) : ?>
                    <div class="dz-social-proof-item">
                        <div class="dz-item-avatar">
                            <img src="<?php echo esc_url($item['avatar']); ?>" alt="<?php echo esc_attr($item['name']); ?>">
                        </div>
                        <div class="dz-item-content">
                            <div class="dz-item-name"><?php echo esc_html($item['name']); ?></div>
                            <div class="dz-item-company"><?php echo esc_html($item['company']); ?></div>
                            <div class="dz-item-time"><?php echo esc_html($item['time']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            DZ_Events_Social_Proof_Widget.init();
        });
        </script>
        <?php
    }
    
    private function get_social_proof_data($event_id, $type, $count) {
        switch ($type) {
            case 'recent_registrations':
                return $this->get_recent_registrations($event_id, $count);
            case 'testimonials':
                return $this->get_event_testimonials($event_id, $count);
            case 'attendee_count':
                return $this->get_attendee_count($event_id);
            default:
                return [
                    'title' => 'Social Proof',
                    'subtitle' => 'Loading...',
                    'items' => []
                ];
        }
    }
    
    private function get_recent_registrations($event_id, $count) {
        global $wpdb;
        
        $registrations = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dz_event_registrations 
             WHERE event_id = %d AND status = 'confirmed' 
             ORDER BY created_at DESC LIMIT %d",
            $event_id, $count
        ));
        
        $items = [];
        foreach ($registrations as $reg) {
            $items[] = [
                'name' => $reg->first_name . ' ' . $reg->last_name,
                'company' => $reg->company ?: 'Individual',
                'avatar' => $this->get_gravatar($reg->email),
                'time' => human_time_diff(strtotime($reg->created_at)) . ' ago'
            ];
        }
        
        return [
            'title' => 'Recent Registrations',
            'subtitle' => count($items) . ' people registered recently',
            'items' => $items
        ];
    }
    
    private function get_gravatar($email) {
        return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?s=40&d=identicon';
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
 * Event Interactive Map Widget
 * 
 * Unique feature: Interactive map showing event location with nearby attractions
 */
class DZ_Event_Interactive_Map_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'dz_event_interactive_map';
    }
    
    public function get_title() {
        return 'Event Interactive Map';
    }
    
    public function get_icon() {
        return 'eicon-google-maps';
    }
    
    public function get_categories() {
        return ['dz-events-advanced'];
    }
    
    protected function _register_controls() {
        // Content Tab
        $this->start_controls_section(
            'content_section',
            [
                'label' => 'Map Settings',
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
            'map_type',
            [
                'label' => 'Map Type',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'roadmap' => 'Road Map',
                    'satellite' => 'Satellite',
                    'hybrid' => 'Hybrid',
                    'terrain' => 'Terrain'
                ],
                'default' => 'roadmap',
            ]
        );
        
        $this->add_control(
            'show_nearby',
            [
                'label' => 'Show Nearby Attractions',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => 'Yes',
                'label_off' => 'No',
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'map_height',
            [
                'label' => 'Map Height',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'vh'],
                'range' => [
                    'px' => [
                        'min' => 200,
                        'max' => 800,
                    ],
                    'vh' => [
                        'min' => 20,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 400,
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        $event_id = $settings['event_id'];
        
        if (!$event_id) {
            echo '<p>Please select an event to display the interactive map.</p>';
            return;
        }
        
        $location = get_post_meta($event_id, '_dz_event_location', true);
        
        if (!$location) {
            echo '<p>Event location not found.</p>';
            return;
        }
        
        ?>
        <div class="dz-interactive-map-widget" 
             data-location="<?php echo esc_attr($location); ?>"
             data-map-type="<?php echo esc_attr($settings['map_type']); ?>"
             data-show-nearby="<?php echo esc_attr($settings['show_nearby']); ?>"
             style="height: <?php echo esc_attr($settings['map_height']['size'] . $settings['map_height']['unit']); ?>;">
            
            <div class="dz-map-container">
                <div id="dz-event-map-<?php echo esc_attr($event_id); ?>" class="dz-event-map"></div>
            </div>
            
            <div class="dz-map-controls">
                <button class="dz-map-control-btn" data-action="center">Center on Event</button>
                <button class="dz-map-control-btn" data-action="directions">Get Directions</button>
                <button class="dz-map-control-btn" data-action="streetview">Street View</button>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            DZ_Events_Interactive_Map_Widget.init();
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
 * Event Live Chat Widget
 * 
 * Unique feature: Real-time chat for event attendees
 */
class DZ_Event_Live_Chat_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'dz_event_live_chat';
    }
    
    public function get_title() {
        return 'Event Live Chat';
    }
    
    public function get_icon() {
        return 'eicon-comments';
    }
    
    public function get_categories() {
        return ['dz-events-advanced'];
    }
    
    protected function _register_controls() {
        // Content Tab
        $this->start_controls_section(
            'content_section',
            [
                'label' => 'Chat Settings',
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
            'chat_type',
            [
                'label' => 'Chat Type',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'general' => 'General Chat',
                    'networking' => 'Networking Chat',
                    'qna' => 'Q&A Chat',
                    'moderated' => 'Moderated Chat'
                ],
                'default' => 'general',
            ]
        );
        
        $this->add_control(
            'require_registration',
            [
                'label' => 'Require Registration',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => 'Yes',
                'label_off' => 'No',
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'max_messages',
            [
                'label' => 'Max Messages Displayed',
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 10,
                'max' => 100,
                'default' => 50,
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        $event_id = $settings['event_id'];
        
        if (!$event_id) {
            echo '<p>Please select an event to display the live chat.</p>';
            return;
        }
        
        ?>
        <div class="dz-live-chat-widget" 
             data-event-id="<?php echo esc_attr($event_id); ?>"
             data-chat-type="<?php echo esc_attr($settings['chat_type']); ?>"
             data-require-registration="<?php echo esc_attr($settings['require_registration']); ?>"
             data-max-messages="<?php echo esc_attr($settings['max_messages']); ?>">
            
            <div class="dz-chat-header">
                <h4 class="dz-chat-title">Live Chat</h4>
                <div class="dz-chat-status">
                    <span class="dz-online-indicator"></span>
                    <span class="dz-online-count">0 online</span>
                </div>
            </div>
            
            <div class="dz-chat-messages">
                <div class="dz-chat-loading">Loading messages...</div>
            </div>
            
            <div class="dz-chat-input">
                <input type="text" placeholder="Type your message..." class="dz-chat-message-input">
                <button class="dz-chat-send-btn">Send</button>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            DZ_Events_Live_Chat_Widget.init();
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

// Initialize unique Elementor widgets
function dz_events_init_unique_elementor_widgets() {
    return DZ_Events_Elementor_Widgets_Unique::instance();
}
add_action('elementor/init', 'dz_events_init_unique_elementor_widgets');
