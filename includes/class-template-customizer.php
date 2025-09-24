<?php
/**
 * Template Customizer for Zeen Events
 * 
 * This file implements a comprehensive template customizer
 * that allows maximum customization of single event and archive templates
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
 * Template Customizer Class
 * 
 * Handles template customization, layout builder, and dynamic content
 */
class DZ_Events_Template_Customizer {
    
    private static $instance = null;
    private $template_options = [];
    private $layout_components = [];
    private $custom_styles = [];
    
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
        add_action('init', [$this, 'init_template_customizer']);
        add_action('wp_ajax_dz_events_save_template_layout', [$this, 'ajax_save_template_layout']);
        add_action('wp_ajax_dz_events_get_template_preview', [$this, 'ajax_get_template_preview']);
        add_action('wp_ajax_dz_events_reset_template', [$this, 'ajax_reset_template']);
    }
    
    /**
     * Initialize template customizer
     */
    public function init_template_customizer() {
        $this->load_template_options();
        $this->load_layout_components();
        $this->register_template_hooks();
    }
    
    /**
     * Load template customization options
     */
    private function load_template_options() {
        $this->template_options = [
            'single_event' => [
                'name' => 'Single Event Template',
                'description' => 'Customize the single event page layout and content',
                'components' => [
                    'header' => [
                        'name' => 'Event Header',
                        'description' => 'Event title, featured image, and badges',
                        'enabled' => true,
                        'components' => [
                            'featured_image' => [
                                'name' => 'Featured Image',
                                'type' => 'image',
                                'enabled' => true,
                                'settings' => [
                                    'size' => 'large',
                                    'show_overlay' => true,
                                    'overlay_opacity' => 0.3,
                                    'overlay_color' => '#000000'
                                ]
                            ],
                            'event_title' => [
                                'name' => 'Event Title',
                                'type' => 'title',
                                'enabled' => true,
                                'settings' => [
                                    'tag' => 'h1',
                                    'class' => 'dz-event-title',
                                    'show_breadcrumbs' => true
                                ]
                            ],
                            'event_badges' => [
                                'name' => 'Event Badges',
                                'type' => 'badges',
                                'enabled' => true,
                                'settings' => [
                                    'show_status' => true,
                                    'show_featured' => true,
                                    'show_categories' => true,
                                    'badge_style' => 'modern'
                                ]
                            ],
                            'event_meta' => [
                                'name' => 'Quick Meta Info',
                                'type' => 'meta',
                                'enabled' => true,
                                'settings' => [
                                    'show_date' => true,
                                    'show_time' => true,
                                    'show_location' => true,
                                    'show_price' => true,
                                    'layout' => 'horizontal'
                                ]
                            ]
                        ]
                    ],
                    'content' => [
                        'name' => 'Main Content Area',
                        'description' => 'Event description and main content',
                        'enabled' => true,
                        'components' => [
                            'event_description' => [
                                'name' => 'Event Description',
                                'type' => 'content',
                                'enabled' => true,
                                'settings' => [
                                    'show_excerpt' => true,
                                    'excerpt_length' => 200,
                                    'show_full_content' => true,
                                    'content_style' => 'modern'
                                ]
                            ],
                            'event_details' => [
                                'name' => 'Event Details Table',
                                'type' => 'details_table',
                                'enabled' => true,
                                'settings' => [
                                    'show_icons' => true,
                                    'show_labels' => true,
                                    'table_style' => 'modern',
                                    'responsive' => true
                                ]
                            ],
                            'custom_fields' => [
                                'name' => 'Custom Fields',
                                'type' => 'custom_fields',
                                'enabled' => true,
                                'settings' => [
                                    'show_all' => true,
                                    'group_by_section' => true,
                                    'field_style' => 'card'
                                ]
                            ]
                        ]
                    ],
                    'sidebar' => [
                        'name' => 'Sidebar Area',
                        'description' => 'Sidebar content and widgets',
                        'enabled' => true,
                        'components' => [
                            'registration_form' => [
                                'name' => 'Registration Form',
                                'type' => 'registration',
                                'enabled' => true,
                                'settings' => [
                                    'form_style' => 'modern',
                                    'show_pricing' => true,
                                    'show_capacity' => true
                                ]
                            ],
                            'event_actions' => [
                                'name' => 'Event Actions',
                                'type' => 'actions',
                                'enabled' => true,
                                'settings' => [
                                    'show_calendar' => true,
                                    'show_share' => true,
                                    'show_invite' => true,
                                    'button_style' => 'modern'
                                ]
                            ],
                            'related_events' => [
                                'name' => 'Related Events',
                                'type' => 'related_events',
                                'enabled' => true,
                                'settings' => [
                                    'count' => 3,
                                    'show_images' => true,
                                    'show_meta' => true
                                ]
                            ],
                            'event_map' => [
                                'name' => 'Event Map',
                                'type' => 'map',
                                'enabled' => false,
                                'settings' => [
                                    'map_style' => 'roadmap',
                                    'show_markers' => true,
                                    'zoom_level' => 15
                                ]
                            ]
                        ]
                    ],
                    'footer' => [
                        'name' => 'Event Footer',
                        'description' => 'Footer content and additional information',
                        'enabled' => true,
                        'components' => [
                            'event_gallery' => [
                                'name' => 'Event Gallery',
                                'type' => 'gallery',
                                'enabled' => false,
                                'settings' => [
                                    'gallery_style' => 'grid',
                                    'show_thumbnails' => true,
                                    'lightbox' => true
                                ]
                            ],
                            'event_testimonials' => [
                                'name' => 'Event Testimonials',
                                'type' => 'testimonials',
                                'enabled' => false,
                                'settings' => [
                                    'show_avatars' => true,
                                    'auto_rotate' => true,
                                    'rotation_speed' => 5000
                                ]
                            ],
                            'event_sponsors' => [
                                'name' => 'Event Sponsors',
                                'type' => 'sponsors',
                                'enabled' => false,
                                'settings' => [
                                    'show_logos' => true,
                                    'logo_size' => 'medium',
                                    'link_to_websites' => true
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'archive' => [
                'name' => 'Events Archive Template',
                'description' => 'Customize the events archive page layout and functionality',
                'components' => [
                    'header' => [
                        'name' => 'Archive Header',
                        'description' => 'Page title, description, and search',
                        'enabled' => true,
                        'components' => [
                            'page_title' => [
                                'name' => 'Page Title',
                                'type' => 'title',
                                'enabled' => true,
                                'settings' => [
                                    'tag' => 'h1',
                                    'class' => 'dz-archive-title',
                                    'show_breadcrumbs' => true
                                ]
                            ],
                            'page_description' => [
                                'name' => 'Page Description',
                                'type' => 'description',
                                'enabled' => true,
                                'settings' => [
                                    'show_description' => true,
                                    'description_style' => 'modern'
                                ]
                            ],
                            'search_filter' => [
                                'name' => 'Search & Filter',
                                'type' => 'search_filter',
                                'enabled' => true,
                                'settings' => [
                                    'show_search' => true,
                                    'show_category_filter' => true,
                                    'show_date_filter' => true,
                                    'show_price_filter' => true,
                                    'filter_style' => 'modern'
                                ]
                            ]
                        ]
                    ],
                    'content' => [
                        'name' => 'Events Grid/List',
                        'description' => 'Events display area',
                        'enabled' => true,
                        'components' => [
                            'events_grid' => [
                                'name' => 'Events Grid',
                                'type' => 'events_grid',
                                'enabled' => true,
                                'settings' => [
                                    'layout' => 'grid',
                                    'columns' => 3,
                                    'show_images' => true,
                                    'show_meta' => true,
                                    'show_excerpt' => true,
                                    'card_style' => 'modern'
                                ]
                            ],
                            'pagination' => [
                                'name' => 'Pagination',
                                'type' => 'pagination',
                                'enabled' => true,
                                'settings' => [
                                    'pagination_style' => 'modern',
                                    'show_page_numbers' => true,
                                    'show_prev_next' => true
                                ]
                            ]
                        ]
                    ],
                    'sidebar' => [
                        'name' => 'Archive Sidebar',
                        'description' => 'Sidebar widgets and filters',
                        'enabled' => true,
                        'components' => [
                            'category_filter' => [
                                'name' => 'Category Filter',
                                'type' => 'category_filter',
                                'enabled' => true,
                                'settings' => [
                                    'show_counts' => true,
                                    'filter_style' => 'modern'
                                ]
                            ],
                            'date_filter' => [
                                'name' => 'Date Filter',
                                'type' => 'date_filter',
                                'enabled' => true,
                                'settings' => [
                                    'show_past_events' => false,
                                    'filter_style' => 'modern'
                                ]
                            ],
                            'featured_events' => [
                                'name' => 'Featured Events',
                                'type' => 'featured_events',
                                'enabled' => true,
                                'settings' => [
                                    'count' => 5,
                                    'show_images' => true,
                                    'show_meta' => true
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Load layout components
     */
    private function load_layout_components() {
        $this->layout_components = [
            'layouts' => [
                'single_column' => [
                    'name' => 'Single Column',
                    'description' => 'Full-width single column layout',
                    'structure' => [
                        'header' => ['width' => '100%'],
                        'content' => ['width' => '100%'],
                        'sidebar' => ['width' => '100%'],
                        'footer' => ['width' => '100%']
                    ]
                ],
                'two_column' => [
                    'name' => 'Two Column',
                    'description' => 'Content and sidebar layout',
                    'structure' => [
                        'header' => ['width' => '100%'],
                        'content' => ['width' => '70%', 'float' => 'left'],
                        'sidebar' => ['width' => '30%', 'float' => 'right'],
                        'footer' => ['width' => '100%', 'clear' => 'both']
                    ]
                ],
                'three_column' => [
                    'name' => 'Three Column',
                    'description' => 'Content with two sidebars',
                    'structure' => [
                        'header' => ['width' => '100%'],
                        'sidebar_left' => ['width' => '25%', 'float' => 'left'],
                        'content' => ['width' => '50%', 'float' => 'left'],
                        'sidebar_right' => ['width' => '25%', 'float' => 'right'],
                        'footer' => ['width' => '100%', 'clear' => 'both']
                    ]
                ],
                'grid' => [
                    'name' => 'Grid Layout',
                    'description' => 'CSS Grid layout system',
                    'structure' => [
                        'header' => ['grid_area' => 'header'],
                        'content' => ['grid_area' => 'content'],
                        'sidebar' => ['grid_area' => 'sidebar'],
                        'footer' => ['grid_area' => 'footer']
                    ]
                ]
            ],
            'responsive_breakpoints' => [
                'mobile' => ['max_width' => '768px'],
                'tablet' => ['min_width' => '769px', 'max_width' => '1024px'],
                'desktop' => ['min_width' => '1025px']
            ]
        ];
    }
    
    /**
     * Register template hooks
     */
    private function register_template_hooks() {
        // Single event hooks
        add_action('dz_single_event_header', [$this, 'render_single_event_header'], 10);
        add_action('dz_single_event_content', [$this, 'render_single_event_content'], 10);
        add_action('dz_single_event_sidebar', [$this, 'render_single_event_sidebar'], 10);
        add_action('dz_single_event_footer', [$this, 'render_single_event_footer'], 10);
        
        // Archive hooks
        add_action('dz_events_archive_header', [$this, 'render_archive_header'], 10);
        add_action('dz_events_archive_content', [$this, 'render_archive_content'], 10);
        add_action('dz_events_archive_sidebar', [$this, 'render_archive_sidebar'], 10);
        
        // Custom CSS
        add_action('wp_head', [$this, 'output_custom_styles'], 100);
    }
    
    /**
     * Render single event header
     */
    public function render_single_event_header() {
        $options = $this->get_template_options('single_event');
        $header_components = $options['components']['header']['components'] ?? [];
        
        echo '<div class="dz-single-event-header">';
        
        foreach ($header_components as $component_id => $component) {
            if ($component['enabled']) {
                $this->render_component($component_id, $component, 'single_event');
            }
        }
        
        echo '</div>';
    }
    
    /**
     * Render single event content
     */
    public function render_single_event_content() {
        $options = $this->get_template_options('single_event');
        $content_components = $options['components']['content']['components'] ?? [];
        
        echo '<div class="dz-single-event-content">';
        
        foreach ($content_components as $component_id => $component) {
            if ($component['enabled']) {
                $this->render_component($component_id, $component, 'single_event');
            }
        }
        
        echo '</div>';
    }
    
    /**
     * Render single event sidebar
     */
    public function render_single_event_sidebar() {
        $options = $this->get_template_options('single_event');
        $sidebar_components = $options['components']['sidebar']['components'] ?? [];
        
        echo '<div class="dz-single-event-sidebar">';
        
        foreach ($sidebar_components as $component_id => $component) {
            if ($component['enabled']) {
                $this->render_component($component_id, $component, 'single_event');
            }
        }
        
        echo '</div>';
    }
    
    /**
     * Render single event footer
     */
    public function render_single_event_footer() {
        $options = $this->get_template_options('single_event');
        $footer_components = $options['components']['footer']['components'] ?? [];
        
        echo '<div class="dz-single-event-footer">';
        
        foreach ($footer_components as $component_id => $component) {
            if ($component['enabled']) {
                $this->render_component($component_id, $component, 'single_event');
            }
        }
        
        echo '</div>';
    }
    
    /**
     * Render archive header
     */
    public function render_archive_header() {
        $options = $this->get_template_options('archive');
        $header_components = $options['components']['header']['components'] ?? [];
        
        echo '<div class="dz-events-archive-header">';
        
        foreach ($header_components as $component_id => $component) {
            if ($component['enabled']) {
                $this->render_component($component_id, $component, 'archive');
            }
        }
        
        echo '</div>';
    }
    
    /**
     * Render archive content
     */
    public function render_archive_content() {
        $options = $this->get_template_options('archive');
        $content_components = $options['components']['content']['components'] ?? [];
        
        echo '<div class="dz-events-archive-content">';
        
        foreach ($content_components as $component_id => $component) {
            if ($component['enabled']) {
                $this->render_component($component_id, $component, 'archive');
            }
        }
        
        echo '</div>';
    }
    
    /**
     * Render archive sidebar
     */
    public function render_archive_sidebar() {
        $options = $this->get_template_options('archive');
        $sidebar_components = $options['components']['sidebar']['components'] ?? [];
        
        echo '<div class="dz-events-archive-sidebar">';
        
        foreach ($sidebar_components as $component_id => $component) {
            if ($component['enabled']) {
                $this->render_component($component_id, $component, 'archive');
            }
        }
        
        echo '</div>';
    }
    
    /**
     * Render individual component
     */
    private function render_component($component_id, $component, $template_type) {
        $method_name = 'render_' . $component['type'] . '_component';
        
        if (method_exists($this, $method_name)) {
            echo '<div class="dz-component dz-component-' . esc_attr($component_id) . '">';
            $this->$method_name($component, $template_type);
            echo '</div>';
        }
    }
    
    /**
     * Render title component
     */
    private function render_title_component($component, $template_type) {
        $settings = $component['settings'];
        $tag = $settings['tag'] ?? 'h1';
        $class = $settings['class'] ?? '';
        
        echo '<' . $tag . ' class="' . esc_attr($class) . '">';
        
        if ($template_type === 'single_event') {
            the_title();
        } else {
            echo get_the_archive_title();
        }
        
        echo '</' . $tag . '>';
        
        if ($settings['show_breadcrumbs'] ?? false) {
            $this->render_breadcrumbs();
        }
    }
    
    /**
     * Render badges component
     */
    private function render_badges_component($component, $template_type) {
        if ($template_type !== 'single_event') {
            return;
        }
        
        $settings = $component['settings'];
        $event_id = get_the_ID();
        
        echo '<div class="dz-event-badges">';
        
        if ($settings['show_status'] ?? true) {
            $status = get_post_meta($event_id, '_dz_event_status', true);
            if ($status) {
                echo '<span class="dz-badge dz-badge-status dz-badge-' . esc_attr($status) . '">';
                echo esc_html(ucfirst($status));
                echo '</span>';
            }
        }
        
        if ($settings['show_featured'] ?? true) {
            $featured = get_post_meta($event_id, '_dz_event_featured', true);
            if ($featured) {
                echo '<span class="dz-badge dz-badge-featured">';
                echo __('Featured Event', 'designzeen-events');
                echo '</span>';
            }
        }
        
        if ($settings['show_categories'] ?? true) {
            $categories = get_the_terms($event_id, 'dz_event_category');
            if ($categories && !is_wp_error($categories)) {
                foreach ($categories as $category) {
                    echo '<span class="dz-badge dz-badge-category">';
                    echo esc_html($category->name);
                    echo '</span>';
                }
            }
        }
        
        echo '</div>';
    }
    
    /**
     * Render meta component
     */
    private function render_meta_component($component, $template_type) {
        if ($template_type !== 'single_event') {
            return;
        }
        
        $settings = $component['settings'];
        $event_id = get_the_ID();
        
        echo '<div class="dz-event-meta dz-meta-' . esc_attr($settings['layout'] ?? 'horizontal') . '">';
        
        if ($settings['show_date'] ?? true) {
            $start_date = get_post_meta($event_id, '_dz_event_start', true);
            if ($start_date) {
                echo '<div class="dz-meta-item dz-meta-date">';
                echo '<i class="fas fa-calendar-alt"></i>';
                echo '<span>' . esc_html(date('F j, Y', strtotime($start_date))) . '</span>';
                echo '</div>';
            }
        }
        
        if ($settings['show_time'] ?? true) {
            $time_start = get_post_meta($event_id, '_dz_event_time_start', true);
            if ($time_start) {
                echo '<div class="dz-meta-item dz-meta-time">';
                echo '<i class="fas fa-clock"></i>';
                echo '<span>' . esc_html(date('g:i A', strtotime($time_start))) . '</span>';
                echo '</div>';
            }
        }
        
        if ($settings['show_location'] ?? true) {
            $location = get_post_meta($event_id, '_dz_event_location', true);
            if ($location) {
                echo '<div class="dz-meta-item dz-meta-location">';
                echo '<i class="fas fa-map-marker-alt"></i>';
                echo '<span>' . esc_html($location) . '</span>';
                echo '</div>';
            }
        }
        
        if ($settings['show_price'] ?? true) {
            $price = get_post_meta($event_id, '_dz_event_price', true);
            if ($price) {
                echo '<div class="dz-meta-item dz-meta-price">';
                echo '<i class="fas fa-tag"></i>';
                echo '<span>' . esc_html($price) . '</span>';
                echo '</div>';
            }
        }
        
        echo '</div>';
    }
    
    /**
     * Render content component
     */
    private function render_content_component($component, $template_type) {
        $settings = $component['settings'];
        
        if ($settings['show_excerpt'] ?? true) {
            echo '<div class="dz-event-excerpt">';
            the_excerpt();
            echo '</div>';
        }
        
        if ($settings['show_full_content'] ?? true) {
            echo '<div class="dz-event-content">';
            the_content();
            echo '</div>';
        }
    }
    
    /**
     * Render details table component
     */
    private function render_details_table_component($component, $template_type) {
        if ($template_type !== 'single_event') {
            return;
        }
        
        $settings = $component['settings'];
        $event_id = get_the_ID();
        
        echo '<div class="dz-event-details-table">';
        echo '<h3>' . __('Event Details', 'designzeen-events') . '</h3>';
        echo '<table class="dz-details-table">';
        
        // Get all event meta fields
        $meta_fields = [
            'start_date' => ['label' => __('Start Date', 'designzeen-events'), 'icon' => 'fa-calendar-alt'],
            'end_date' => ['label' => __('End Date', 'designzeen-events'), 'icon' => 'fa-calendar-alt'],
            'time_start' => ['label' => __('Start Time', 'designzeen-events'), 'icon' => 'fa-clock'],
            'time_end' => ['label' => __('End Time', 'designzeen-events'), 'icon' => 'fa-stopwatch'],
            'location' => ['label' => __('Location', 'designzeen-events'), 'icon' => 'fa-map-marker-alt'],
            'price' => ['label' => __('Price', 'designzeen-events'), 'icon' => 'fa-tag'],
            'capacity' => ['label' => __('Capacity', 'designzeen-events'), 'icon' => 'fa-users'],
            'contact' => ['label' => __('Contact', 'designzeen-events'), 'icon' => 'fa-phone']
        ];
        
        foreach ($meta_fields as $field_key => $field_data) {
            $meta_key = '_dz_event_' . $field_key;
            $value = get_post_meta($event_id, $meta_key, true);
            
            if ($value) {
                echo '<tr>';
                echo '<td class="dz-detail-label">';
                if ($settings['show_icons'] ?? true) {
                    echo '<i class="fas ' . esc_attr($field_data['icon']) . '"></i> ';
                }
                echo esc_html($field_data['label']);
                echo '</td>';
                echo '<td class="dz-detail-value">' . esc_html($value) . '</td>';
                echo '</tr>';
            }
        }
        
        echo '</table>';
        echo '</div>';
    }
    
    /**
     * Render registration component
     */
    private function render_registration_component($component, $template_type) {
        if ($template_type !== 'single_event') {
            return;
        }
        
        $settings = $component['settings'];
        $event_id = get_the_ID();
        
        echo '<div class="dz-event-registration">';
        echo '<h3>' . __('Register for this Event', 'designzeen-events') . '</h3>';
        
        // Check if event is still open for registration
        $status = get_post_meta($event_id, '_dz_event_status', true);
        $capacity = get_post_meta($event_id, '_dz_event_capacity', true);
        $price = get_post_meta($event_id, '_dz_event_price', true);
        
        if ($status === 'cancelled' || $status === 'completed') {
            echo '<p class="dz-registration-closed">' . __('Registration is closed for this event.', 'designzeen-events') . '</p>';
        } else {
            echo '<div class="dz-registration-form">';
            echo '<form class="dz-event-registration-form" data-event-id="' . esc_attr($event_id) . '">';
            echo '<div class="dz-form-group">';
            echo '<label for="dz_reg_name">' . __('Full Name', 'designzeen-events') . '</label>';
            echo '<input type="text" id="dz_reg_name" name="name" required>';
            echo '</div>';
            echo '<div class="dz-form-group">';
            echo '<label for="dz_reg_email">' . __('Email Address', 'designzeen-events') . '</label>';
            echo '<input type="email" id="dz_reg_email" name="email" required>';
            echo '</div>';
            echo '<div class="dz-form-group">';
            echo '<label for="dz_reg_phone">' . __('Phone Number', 'designzeen-events') . '</label>';
            echo '<input type="tel" id="dz_reg_phone" name="phone" required>';
            echo '</div>';
            
            if ($settings['show_pricing'] ?? true && $price) {
                echo '<div class="dz-pricing-info">';
                echo '<strong>' . __('Price:', 'designzeen-events') . '</strong> ' . esc_html($price);
                echo '</div>';
            }
            
            if ($settings['show_capacity'] ?? true && $capacity) {
                echo '<div class="dz-capacity-info">';
                echo '<strong>' . __('Capacity:', 'designzeen-events') . '</strong> ' . esc_html($capacity) . ' ' . __('attendees', 'designzeen-events');
                echo '</div>';
            }
            
            echo '<button type="submit" class="dz-register-btn">' . __('Register Now', 'designzeen-events') . '</button>';
            echo '</form>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * Render actions component
     */
    private function render_actions_component($component, $template_type) {
        if ($template_type !== 'single_event') {
            return;
        }
        
        $settings = $component['settings'];
        $event_id = get_the_ID();
        
        echo '<div class="dz-event-actions">';
        echo '<h3>' . __('Event Actions', 'designzeen-events') . '</h3>';
        
        if ($settings['show_calendar'] ?? true) {
            echo '<div class="dz-action-item">';
            echo '<button class="dz-action-btn dz-calendar-btn" data-event-id="' . esc_attr($event_id) . '">';
            echo '<i class="fas fa-calendar-plus"></i> ' . __('Add to Calendar', 'designzeen-events');
            echo '</button>';
            echo '</div>';
        }
        
        if ($settings['show_share'] ?? true) {
            echo '<div class="dz-action-item">';
            echo '<button class="dz-action-btn dz-share-btn" data-event-id="' . esc_attr($event_id) . '">';
            echo '<i class="fas fa-share-alt"></i> ' . __('Share Event', 'designzeen-events');
            echo '</button>';
            echo '</div>';
        }
        
        if ($settings['show_invite'] ?? true) {
            echo '<div class="dz-action-item">';
            echo '<button class="dz-action-btn dz-invite-btn" data-event-id="' . esc_attr($event_id) . '">';
            echo '<i class="fas fa-user-plus"></i> ' . __('Invite Friends', 'designzeen-events');
            echo '</button>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * Render breadcrumbs
     */
    private function render_breadcrumbs() {
        echo '<nav class="dz-breadcrumbs">';
        echo '<a href="' . home_url() . '">' . __('Home', 'designzeen-events') . '</a>';
        echo '<span class="dz-breadcrumb-separator">/</span>';
        echo '<a href="' . get_post_type_archive_link('dz_event') . '">' . __('Events', 'designzeen-events') . '</a>';
        
        if (is_singular('dz_event')) {
            echo '<span class="dz-breadcrumb-separator">/</span>';
            echo '<span class="dz-breadcrumb-current">' . get_the_title() . '</span>';
        }
        
        echo '</nav>';
    }
    
    /**
     * Get template options
     */
    public function get_template_options($template_type) {
        $default_options = $this->template_options[$template_type] ?? [];
        $saved_options = get_option("dz_template_options_{$template_type}", []);
        
        return array_merge_recursive($default_options, $saved_options);
    }
    
    /**
     * Save template options
     */
    public function save_template_options($template_type, $options) {
        return update_option("dz_template_options_{$template_type}", $options);
    }
    
    /**
     * Get layout components
     */
    public function get_layout_components() {
        return $this->layout_components;
    }
    
    /**
     * Output custom styles
     */
    public function output_custom_styles() {
        $custom_css = get_option('dz_template_custom_css', '');
        
        if ($custom_css) {
            echo '<style type="text/css" id="dz-template-custom-css">';
            echo $custom_css;
            echo '</style>';
        }
    }
    
    /**
     * AJAX save template layout
     */
    public function ajax_save_template_layout() {
        if (!wp_verify_nonce($_POST['nonce'], 'dz_events_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $template_type = sanitize_text_field($_POST['template_type']);
        $layout_data = $_POST['layout_data'];
        
        $result = $this->save_template_options($template_type, $layout_data);
        
        if ($result) {
            wp_send_json_success('Template layout saved successfully');
        } else {
            wp_send_json_error('Failed to save template layout');
        }
    }
    
    /**
     * AJAX get template preview
     */
    public function ajax_get_template_preview() {
        if (!wp_verify_nonce($_POST['nonce'], 'dz_events_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $template_type = sanitize_text_field($_POST['template_type']);
        $layout_data = $_POST['layout_data'];
        
        // Temporarily save layout data
        $this->save_template_options($template_type, $layout_data);
        
        // Generate preview
        ob_start();
        $this->render_template_preview($template_type);
        $preview_html = ob_get_clean();
        
        wp_send_json_success(['preview_html' => $preview_html]);
    }
    
    /**
     * AJAX reset template
     */
    public function ajax_reset_template() {
        if (!wp_verify_nonce($_POST['nonce'], 'dz_events_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $template_type = sanitize_text_field($_POST['template_type']);
        
        $result = delete_option("dz_template_options_{$template_type}");
        
        if ($result) {
            wp_send_json_success('Template reset to default');
        } else {
            wp_send_json_error('Failed to reset template');
        }
    }
    
    /**
     * Render template preview
     */
    private function render_template_preview($template_type) {
        // This would render a preview of the template
        // Implementation depends on specific requirements
        echo '<div class="dz-template-preview">';
        echo '<p>Template preview for: ' . esc_html($template_type) . '</p>';
        echo '</div>';
    }
}

/**
 * Initialize template customizer
 */
function dz_events_init_template_customizer() {
    return DZ_Events_Template_Customizer::instance();
}
add_action('init', 'dz_events_init_template_customizer');
