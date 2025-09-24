<?php
/**
 * REST API for Zeen Events
 * 
 * This file provides a clean, simple REST API for enterprise integrations
 * Following WordPress REST API standards and best practices
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * REST API Manager Class
 * 
 * Handles all REST API endpoints and responses
 */
class DZ_Events_REST_API {
    
    private static $instance = null;
    private $namespace = 'dz-events/v1';
    
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
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Events collection endpoint
        register_rest_route($this->namespace, '/events', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_events'],
                'permission_callback' => '__return_true',
                'args' => $this->get_events_args()
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_event'],
                'permission_callback' => [$this, 'check_create_permission'],
                'args' => $this->get_create_event_args()
            ]
        ]);
        
        // Single event endpoint
        register_rest_route($this->namespace, '/events/(?P<id>\d+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_event'],
                'permission_callback' => '__return_true',
                'args' => [
                    'id' => [
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint'
                    ]
                ]
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_event'],
                'permission_callback' => [$this, 'check_update_permission'],
                'args' => $this->get_update_event_args()
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$this, 'delete_event'],
                'permission_callback' => [$this, 'check_delete_permission']
            ]
        ]);
        
        // Categories endpoint
        register_rest_route($this->namespace, '/categories', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_categories'],
                'permission_callback' => '__return_true'
            ]
        ]);
        
        // Search endpoint
        register_rest_route($this->namespace, '/search', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'search_events'],
                'permission_callback' => '__return_true',
                'args' => [
                    'q' => [
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field'
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'default' => 20,
                        'sanitize_callback' => 'absint'
                    ]
                ]
            ]
        ]);
        
        // Analytics endpoint
        register_rest_route($this->namespace, '/events/(?P<id>\d+)/analytics', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_event_analytics'],
                'permission_callback' => [$this, 'check_analytics_permission']
            ]
        ]);
        
        // Registration endpoint
        register_rest_route($this->namespace, '/events/(?P<id>\d+)/register', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'register_for_event'],
                'permission_callback' => '__return_true',
                'args' => $this->get_registration_args()
            ]
        ]);
        
        // Webhooks endpoint
        register_rest_route($this->namespace, '/webhooks', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_webhooks'],
                'permission_callback' => [$this, 'check_webhook_permission']
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_webhook'],
                'permission_callback' => [$this, 'check_webhook_permission'],
                'args' => $this->get_webhook_args()
            ]
        ]);
        
        // Analytics endpoint
        register_rest_route($this->namespace, '/analytics', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_analytics'],
                'permission_callback' => [$this, 'check_analytics_permission'],
                'args' => [
                    'period' => [
                        'type' => 'string',
                        'enum' => ['7_days', '30_days', '90_days', '1_year'],
                        'default' => '30_days'
                    ]
                ]
            ]
        ]);
    }
    
    /**
     * Get events collection
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function get_events($request) {
        $params = $request->get_params();
        
        // Convert REST API params to internal format
        $filters = [
            'count' => $params['per_page'] ?? 10,
            'status' => $params['status'] ?? '',
            'category' => $params['category'] ?? '',
            'featured' => $params['featured'] ?? '',
            'show_past' => $params['show_past'] ?? 'false',
            'orderby' => $params['orderby'] ?? 'start_date',
            'order' => $params['order'] ?? 'ASC'
        ];
        
        // Get events using core architecture
        $events = DZ_Events_Core::instance()->display_events($filters);
        
        // Format response
        $formatted_events = array_map([$this, 'format_event_response'], $events);
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $formatted_events,
            'total' => count($formatted_events),
            'page' => $params['page'] ?? 1,
            'per_page' => $params['per_page'] ?? 10
        ]);
    }
    
    /**
     * Get single event
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function get_event($request) {
        $event_id = $request->get_param('id');
        
        // Get event using core architecture
        $event = DZ_Events_Core::instance()->get_event($event_id);
        
        if (!$event) {
            return new WP_Error('event_not_found', 'Event not found', ['status' => 404]);
        }
        
        // Track view analytics
        DZ_Events_Database::instance()->track_analytics($event_id, 'view');
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $this->format_event_response($event)
        ]);
    }
    
    /**
     * Create new event
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function create_event($request) {
        $params = $request->get_params();
        
        // Create event post
        $post_data = [
            'post_title' => sanitize_text_field($params['title']),
            'post_content' => wp_kses_post($params['content']),
            'post_excerpt' => sanitize_textarea_field($params['excerpt']),
            'post_type' => 'dz_event',
            'post_status' => 'publish'
        ];
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return new WP_Error('event_creation_failed', 'Failed to create event', ['status' => 500]);
        }
        
        // Save event meta
        $this->save_event_meta($post_id, $params);
        
        // Get created event
        $event = DZ_Events_Core::instance()->get_event($post_id);
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $this->format_event_response($event),
            'message' => 'Event created successfully'
        ], 201);
    }
    
    /**
     * Update event
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function update_event($request) {
        $event_id = $request->get_param('id');
        $params = $request->get_params();
        
        // Check if event exists
        $event = get_post($event_id);
        if (!$event || $event->post_type !== 'dz_event') {
            return new WP_Error('event_not_found', 'Event not found', ['status' => 404]);
        }
        
        // Update post data
        $post_data = ['ID' => $event_id];
        
        if (isset($params['title'])) {
            $post_data['post_title'] = sanitize_text_field($params['title']);
        }
        
        if (isset($params['content'])) {
            $post_data['post_content'] = wp_kses_post($params['content']);
        }
        
        if (isset($params['excerpt'])) {
            $post_data['post_excerpt'] = sanitize_textarea_field($params['excerpt']);
        }
        
        $updated = wp_update_post($post_data);
        
        if (is_wp_error($updated)) {
            return new WP_Error('event_update_failed', 'Failed to update event', ['status' => 500]);
        }
        
        // Update event meta
        $this->save_event_meta($event_id, $params);
        
        // Get updated event
        $event = DZ_Events_Core::instance()->get_event($event_id);
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $this->format_event_response($event),
            'message' => 'Event updated successfully'
        ]);
    }
    
    /**
     * Delete event
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function delete_event($request) {
        $event_id = $request->get_param('id');
        
        // Check if event exists
        $event = get_post($event_id);
        if (!$event || $event->post_type !== 'dz_event') {
            return new WP_Error('event_not_found', 'Event not found', ['status' => 404]);
        }
        
        // Delete event
        $deleted = wp_delete_post($event_id, true);
        
        if (!$deleted) {
            return new WP_Error('event_delete_failed', 'Failed to delete event', ['status' => 500]);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'message' => 'Event deleted successfully'
        ]);
    }
    
    /**
     * Get categories
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function get_categories($request) {
        $categories = get_terms([
            'taxonomy' => 'dz_event_category',
            'hide_empty' => false
        ]);
        
        $formatted_categories = array_map([$this, 'format_category_response'], $categories);
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $formatted_categories
        ]);
    }
    
    /**
     * Search events
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function search_events($request) {
        $search_term = $request->get_param('q');
        $limit = $request->get_param('limit');
        
        // Search using core architecture
        $events = DZ_Events_Core::instance()->search_events($search_term, ['count' => $limit]);
        
        $formatted_events = array_map([$this, 'format_event_response'], $events);
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $formatted_events,
            'total' => count($formatted_events),
            'search_term' => $search_term
        ]);
    }
    
    /**
     * Get event analytics
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function get_event_analytics($request) {
        $event_id = $request->get_param('id');
        
        // Get analytics data
        $analytics = DZ_Events_Database::instance()->get_analytics($event_id);
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $analytics
        ]);
    }
    
    /**
     * Register for event
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function register_for_event($request) {
        $event_id = $request->get_param('id');
        $params = $request->get_params();
        
        // Validate event exists
        $event = get_post($event_id);
        if (!$event || $event->post_type !== 'dz_event') {
            return new WP_Error('event_not_found', 'Event not found', ['status' => 404]);
        }
        
        // Create registration
        $registration_data = [
            'event_id' => $event_id,
            'email' => sanitize_email($params['email']),
            'first_name' => sanitize_text_field($params['first_name']),
            'last_name' => sanitize_text_field($params['last_name']),
            'phone' => sanitize_text_field($params['phone']),
            'company' => sanitize_text_field($params['company']),
            'status' => 'pending'
        ];
        
        // Save registration (this would be implemented in the registration system)
        // For now, just track the registration attempt
        DZ_Events_Database::instance()->track_analytics($event_id, 'register', $registration_data);
        
        return new WP_REST_Response([
            'success' => true,
            'message' => 'Registration submitted successfully',
            'data' => $registration_data
        ]);
    }
    
    /**
     * Format event response
     * 
     * @param object $event Event data
     * @return array Formatted event data
     */
    private function format_event_response($event) {
        return [
            'id' => $event->ID,
            'title' => $event->post_title,
            'slug' => $event->post_name,
            'content' => $event->post_content,
            'excerpt' => $event->post_excerpt,
            'permalink' => get_permalink($event->ID),
            'featured_image' => get_the_post_thumbnail_url($event->ID, 'full'),
            'start_date' => $event->meta['start'] ?? '',
            'end_date' => $event->meta['end'] ?? '',
            'start_time' => $event->meta['time_start'] ?? '',
            'end_time' => $event->meta['time_end'] ?? '',
            'location' => $event->meta['location'] ?? '',
            'price' => $event->meta['price'] ?? '',
            'capacity' => $event->meta['capacity'] ?? '',
            'status' => $event->meta['status'] ?? 'upcoming',
            'featured' => $event->meta['featured'] === '1',
            'categories' => $event->categories ?? [],
            'created_at' => $event->post_date,
            'updated_at' => $event->post_modified
        ];
    }
    
    /**
     * Format category response
     * 
     * @param WP_Term $category Category term
     * @return array Formatted category data
     */
    private function format_category_response($category) {
        return [
            'id' => $category->term_id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'count' => $category->count,
            'parent' => $category->parent
        ];
    }
    
    /**
     * Save event meta data
     * 
     * @param int $post_id Post ID
     * @param array $params Event parameters
     */
    private function save_event_meta($post_id, $params) {
        $meta_fields = [
            'start_date' => '_dz_event_start',
            'end_date' => '_dz_event_end',
            'start_time' => '_dz_event_time_start',
            'end_time' => '_dz_event_time_end',
            'location' => '_dz_event_location',
            'price' => '_dz_event_price',
            'capacity' => '_dz_event_capacity',
            'status' => '_dz_event_status',
            'external_url' => '_dz_event_external_url',
            'featured' => '_dz_event_featured'
        ];
        
        foreach ($meta_fields as $param_key => $meta_key) {
            if (isset($params[$param_key])) {
                $value = $params[$param_key];
                
                // Sanitize based on field type
                switch ($param_key) {
                    case 'start_date':
                    case 'end_date':
                        $value = sanitize_text_field($value);
                        break;
                    case 'start_time':
                    case 'end_time':
                        $value = sanitize_text_field($value);
                        break;
                    case 'price':
                        $value = floatval($value);
                        break;
                    case 'capacity':
                        $value = intval($value);
                        break;
                    case 'external_url':
                        $value = esc_url_raw($value);
                        break;
                    case 'featured':
                        $value = $value ? '1' : '0';
                        break;
                    default:
                        $value = sanitize_text_field($value);
                        break;
                }
                
                update_post_meta($post_id, $meta_key, $value);
            }
        }
    }
    
    /**
     * Get events endpoint arguments
     * 
     * @return array Arguments
     */
    private function get_events_args() {
        return [
            'per_page' => [
                'type' => 'integer',
                'default' => 10,
                'sanitize_callback' => 'absint'
            ],
            'page' => [
                'type' => 'integer',
                'default' => 1,
                'sanitize_callback' => 'absint'
            ],
            'status' => [
                'type' => 'string',
                'enum' => ['upcoming', 'ongoing', 'completed', 'cancelled'],
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'category' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'featured' => [
                'type' => 'boolean',
                'default' => false
            ],
            'show_past' => [
                'type' => 'boolean',
                'default' => false
            ],
            'orderby' => [
                'type' => 'string',
                'enum' => ['start_date', 'title', 'created_at'],
                'default' => 'start_date',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'order' => [
                'type' => 'string',
                'enum' => ['ASC', 'DESC'],
                'default' => 'ASC',
                'sanitize_callback' => 'sanitize_text_field'
            ]
        ];
    }
    
    /**
     * Get create event arguments
     * 
     * @return array Arguments
     */
    private function get_create_event_args() {
        return [
            'title' => [
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'content' => [
                'type' => 'string',
                'sanitize_callback' => 'wp_kses_post'
            ],
            'excerpt' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_textarea_field'
            ],
            'start_date' => [
                'required' => true,
                'type' => 'string',
                'format' => 'date',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'end_date' => [
                'required' => true,
                'type' => 'string',
                'format' => 'date',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'location' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'price' => [
                'type' => 'number',
                'sanitize_callback' => 'floatval'
            ],
            'capacity' => [
                'type' => 'integer',
                'sanitize_callback' => 'absint'
            ]
        ];
    }
    
    /**
     * Get update event arguments
     * 
     * @return array Arguments
     */
    private function get_update_event_args() {
        $args = $this->get_create_event_args();
        
        // Make all fields optional for updates
        foreach ($args as $key => $arg) {
            unset($args[$key]['required']);
        }
        
        return $args;
    }
    
    /**
     * Get registration arguments
     * 
     * @return array Arguments
     */
    private function get_registration_args() {
        return [
            'email' => [
                'required' => true,
                'type' => 'string',
                'format' => 'email',
                'sanitize_callback' => 'sanitize_email'
            ],
            'first_name' => [
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'last_name' => [
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'phone' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'company' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            ]
        ];
    }
    
    /**
     * Check create permission
     * 
     * @param WP_REST_Request $request Request object
     * @return bool Has permission
     */
    public function check_create_permission($request) {
        return current_user_can('edit_posts');
    }
    
    /**
     * Check update permission
     * 
     * @param WP_REST_Request $request Request object
     * @return bool Has permission
     */
    public function check_update_permission($request) {
        $event_id = $request->get_param('id');
        return current_user_can('edit_post', $event_id);
    }
    
    /**
     * Check delete permission
     * 
     * @param WP_REST_Request $request Request object
     * @return bool Has permission
     */
    public function check_delete_permission($request) {
        $event_id = $request->get_param('id');
        return current_user_can('delete_post', $event_id);
    }
    
    /**
     * Check analytics permission
     * 
     * @param WP_REST_Request $request Request object
     * @return bool Has permission
     */
    public function check_analytics_permission($request) {
        return current_user_can('edit_posts');
    }
    
    /**
     * Get webhooks
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function get_webhooks($request) {
        $webhooks = get_option('dz_events_webhooks', []);
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $webhooks
        ]);
    }
    
    /**
     * Create webhook
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function create_webhook($request) {
        $params = $request->get_params();
        
        $webhook = [
            'id' => uniqid(),
            'url' => esc_url_raw($params['url']),
            'events' => $params['events'],
            'secret' => wp_generate_password(32, false),
            'active' => true,
            'created_at' => current_time('mysql')
        ];
        
        $webhooks = get_option('dz_events_webhooks', []);
        $webhooks[] = $webhook;
        update_option('dz_events_webhooks', $webhooks);
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $webhook,
            'message' => 'Webhook created successfully'
        ], 201);
    }
    
    /**
     * Get analytics data
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function get_analytics($request) {
        $period = $request->get_param('period');
        
        $analytics = DZ_Events_Analytics_Engine::instance()->get_dashboard_data($period);
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $analytics
        ]);
    }
    
    /**
     * Check webhook permission
     * 
     * @param WP_REST_Request $request Request object
     * @return bool Has permission
     */
    public function check_webhook_permission($request) {
        return current_user_can('manage_options');
    }
    
    /**
     * Get webhook arguments
     * 
     * @return array Arguments
     */
    private function get_webhook_args() {
        return [
            'url' => [
                'required' => true,
                'type' => 'string',
                'format' => 'uri',
                'sanitize_callback' => 'esc_url_raw'
            ],
            'events' => [
                'required' => true,
                'type' => 'array',
                'items' => [
                    'type' => 'string',
                    'enum' => ['event.created', 'event.updated', 'event.deleted', 'registration.created']
                ]
            ]
        ];
    }
}

/**
 * Initialize REST API
 */
function dz_events_init_rest_api() {
    return DZ_Events_REST_API::instance();
}
add_action('rest_api_init', 'dz_events_init_rest_api');
