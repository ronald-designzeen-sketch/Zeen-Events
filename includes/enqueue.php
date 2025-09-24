<?php
function dz_enqueue_assets() {
    // Add cache-busting version number
    $version = get_option('dz_events_version', '1.0.0');
    
    wp_enqueue_style('dz-events-style', plugin_dir_url(__FILE__) . '../assets/css/style.css', array(), $version);
    wp_enqueue_style('dz-events-widgets-advanced', plugin_dir_url(__FILE__) . '../assets/css/widgets-advanced.css', array(), $version);
    
    // Enqueue Font Awesome for icons
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', array(), '6.0.0');
    
    // Enqueue Bootstrap Icons as backup
    wp_enqueue_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css', array(), '1.10.0');
    
    // Enqueue JavaScript
    wp_enqueue_script('dz-events-script', plugin_dir_url(__FILE__) . '../assets/js/script.js', array('jquery'), $version, true);
    wp_enqueue_script('dz-events-widgets-advanced', plugin_dir_url(__FILE__) . '../assets/js/widgets-advanced.js', array('jquery'), $version, true);
    
    // Localize script for AJAX
    wp_localize_script('dz-events-script', 'dz_events_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('dz_events_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'dz_enqueue_assets');

function dz_enqueue_editor_assets() {
    wp_enqueue_style(
        'dz-events-editor-style',
        plugin_dir_url(__FILE__) . '../assets/css/editor.css',
        array('wp-edit-blocks')
    );
}
add_action('enqueue_block_editor_assets', 'dz_enqueue_editor_assets');

// Enqueue Font Awesome for admin area
// Enhanced admin screen detection with more granular control
function dz_is_dz_events_admin_screen() {
    if (!function_exists('get_current_screen')) return false;
    $screen = get_current_screen();
    if (!$screen) return false;
    
    // Match CPT list, single edit, and settings page
    if (!empty($screen->post_type) && $screen->post_type === 'dz_event') {
        return true;
    }
    
    $allowed_ids = array(
        'edit-dz_event',                 // Events list
        'dz_event',                      // Single event edit
        'settings_page_dz-events-settings', // Plugin settings
        'toplevel_page_dz-events-settings', // Main settings page
        'dz-events_page_dz-events-page-settings', // Page settings
    );
    
    return in_array($screen->id, $allowed_ids, true);
}

// Enhanced admin asset loading with versioning and conditional loading
function dz_enqueue_admin_assets($hook_suffix) {
    if (!dz_is_dz_events_admin_screen()) {
        return; // Do not load globally in admin to avoid UI conflicts
    }
    
    // Get plugin version for cache busting
    $version = get_option('dz_events_version', '1.0.0');
    
    // Load Font Awesome with versioning
    wp_enqueue_style(
        'font-awesome', 
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', 
        array(), 
        '6.0.0'
    );
    
    // Load Bootstrap Icons with versioning
    wp_enqueue_style(
        'bootstrap-icons', 
        'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css', 
        array(), 
        '1.10.0'
    );
    
    // Load admin-specific CSS if it exists
    $admin_css_path = plugin_dir_path(__FILE__) . '../assets/css/admin.css';
    if (file_exists($admin_css_path)) {
        wp_enqueue_style(
            'dz-events-admin-style',
            plugin_dir_url(__FILE__) . '../assets/css/admin.css',
            array('font-awesome', 'bootstrap-icons'),
            $version
        );
    }
    
    // Load admin-specific JavaScript if it exists
    $admin_js_path = plugin_dir_path(__FILE__) . '../assets/js/admin.js';
    if (file_exists($admin_js_path)) {
        wp_enqueue_script(
            'dz-events-admin-script',
            plugin_dir_url(__FILE__) . '../assets/js/admin.js',
            array('jquery'),
            $version,
            true
        );
        
        // Localize admin script
        wp_localize_script('dz-events-admin-script', 'dz_events_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dz_events_admin_nonce'),
            'ics_nonce' => wp_create_nonce('dz_events_ics_nonce'),
            'bulk_ics_nonce' => wp_create_nonce('dz_events_bulk_ics_nonce'),
        ));
    }
}
add_action('admin_enqueue_scripts', 'dz_enqueue_admin_assets');

// AJAX handler for event filtering
function dz_ajax_filter_events() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'dz_events_nonce')) {
        wp_die('Security check failed');
    }
    
    // Parse form data
    parse_str($_POST['form_data'], $form_data);
    
    // Build query args
    $query_args = array(
        'post_type' => 'dz_event',
        'posts_per_page' => 12,
        'post_status' => 'publish',
        'meta_query' => array(),
        'tax_query' => array()
    );
    
    // Filter by category
    if (!empty($form_data['category'])) {
        $query_args['tax_query'][] = array(
            'taxonomy' => 'dz_event_category',
            'field' => 'slug',
            'terms' => sanitize_text_field($form_data['category'])
        );
    }
    
    // Filter by status
    if (!empty($form_data['status'])) {
        $query_args['meta_query'][] = array(
            'key' => '_dz_event_status',
            'value' => sanitize_text_field($form_data['status']),
            'compare' => '='
        );
    }
    
    // Handle past events
    if (empty($form_data['show_past'])) {
        $query_args['meta_query'][] = array(
            'key' => '_dz_event_start',
            'value' => date('Y-m-d'),
            'compare' => '>='
        );
    }
    
    // Order by start date
    $query_args['meta_key'] = '_dz_event_start';
    $query_args['orderby'] = 'meta_value';
    $query_args['order'] = 'ASC';
    
    $query = new WP_Query($query_args);
    
    ob_start();
    
    if ($query->have_posts()) {
        echo '<div class="dz-events-wrapper dz-events-grid">';
        
        while ($query->have_posts()) : $query->the_post();
            $date_start = get_post_meta(get_the_ID(), '_dz_event_start', true);
            $date_end = get_post_meta(get_the_ID(), '_dz_event_end', true);
            $price = get_post_meta(get_the_ID(), '_dz_event_price', true);
            $location = get_post_meta(get_the_ID(), '_dz_event_location', true);
            $capacity = get_post_meta(get_the_ID(), '_dz_event_capacity', true);
            $status = get_post_meta(get_the_ID(), '_dz_event_status', true);
            $external_url = get_post_meta(get_the_ID(), '_dz_event_external_url', true);
            $featured = get_post_meta(get_the_ID(), '_dz_event_featured', true);
            $categories = get_the_terms(get_the_ID(), 'dz_event_category');
            $category = $categories ? $categories[0]->name : 'Uncategorized';
            
            // Format dates
            $formatted_start = $date_start ? date('M j, Y', strtotime($date_start)) : '';
            $formatted_end = $date_end ? date('M j, Y', strtotime($date_end)) : '';
            $date_display = $formatted_start;
            if ($formatted_start !== $formatted_end && $formatted_end) {
                $date_display .= ' - ' . $formatted_end;
            }
            ?>
            <div class="dz-event-card dz-event-status-<?php echo esc_attr($status); ?>">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="dz-event-thumb">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail('medium'); ?>
                        </a>
                        <?php if ($featured) : ?>
                            <span class="dz-event-badge dz-event-featured"><?php _e('Featured', 'designzeen-events'); ?></span>
                        <?php endif; ?>
                        <span class="dz-event-badge dz-event-status-<?php echo esc_attr($status); ?>">
                            <?php echo ucfirst(esc_html($status)); ?>
                        </span>
                    </div>
                <?php endif; ?>

                <div class="dz-event-content">
                    <h3 class="dz-event-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h3>
                    
                    <div class="dz-event-meta">
                        <p class="dz-event-category">
                            <span class="dz-meta-label"><?php _e('Category:', 'designzeen-events'); ?></span> 
                            <?php echo esc_html($category); ?>
                        </p>
                        
                        <?php if ($date_display) : ?>
                            <p class="dz-event-dates">
                                <span class="dz-meta-label"><?php _e('Date:', 'designzeen-events'); ?></span> 
                                <?php echo esc_html($date_display); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($location) : ?>
                            <p class="dz-event-location">
                                <span class="dz-meta-label"><?php _e('Location:', 'designzeen-events'); ?></span> 
                                <?php echo esc_html($location); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($price) : ?>
                            <p class="dz-event-price">
                                <span class="dz-meta-label"><?php _e('Price:', 'designzeen-events'); ?></span> 
                                <span class="dz-price-value"><?php echo esc_html($price); ?></span>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($capacity) : ?>
                            <p class="dz-event-capacity">
                                <span class="dz-meta-label"><?php _e('Capacity:', 'designzeen-events'); ?></span> 
                                <?php echo esc_html($capacity); ?> <?php _e('attendees', 'designzeen-events'); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <p class="dz-event-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?></p>

                    <div class="dz-event-actions">
                        <a href="<?php the_permalink(); ?>" class="dz-event-btn dz-btn-primary"><?php _e('View Details', 'designzeen-events'); ?></a>
                        <?php if ($external_url) : ?>
                            <a href="<?php echo esc_url($external_url); ?>" class="dz-event-btn dz-btn-secondary" target="_blank" rel="noopener"><?php _e('Get Tickets', 'designzeen-events'); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
        endwhile;
        echo '</div>';
    } else {
        echo '<div class="dz-no-events"><h3>' . __('No events found', 'designzeen-events') . '</h3><p>' . __('Sorry, no events match your criteria. Please try adjusting your filters.', 'designzeen-events') . '</p></div>';
    }
    
    wp_reset_postdata();
    
    $html = ob_get_clean();
    
    wp_send_json_success(array('html' => $html));
}
add_action('wp_ajax_dz_filter_events', 'dz_ajax_filter_events');
add_action('wp_ajax_nopriv_dz_filter_events', 'dz_ajax_filter_events');

// AJAX handler for calendar data
function dz_ajax_get_event_calendar_data() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'dz_events_nonce')) {
        wp_die('Security check failed');
    }
    
    $event_id = intval($_POST['event_id']);
    $event = get_post($event_id);
    
    if (!$event || $event->post_type !== 'dz_event') {
        wp_send_json_error('Event not found');
    }
    
    // Get all event meta data
    $start_date = get_post_meta($event_id, '_dz_event_start', true);
    $end_date = get_post_meta($event_id, '_dz_event_end', true);
    $start_time = get_post_meta($event_id, '_dz_event_time_start', true);
    $end_time = get_post_meta($event_id, '_dz_event_time_end', true);
    $location = get_post_meta($event_id, '_dz_event_location', true);
    $price = get_post_meta($event_id, '_dz_event_price', true);
    $capacity = get_post_meta($event_id, '_dz_event_capacity', true);
    $contact = get_post_meta($event_id, '_dz_event_contact', true);
    
    // Format dates for better display
    $formatted_start_date = '';
    $formatted_end_date = '';
    if (!empty($start_date)) {
        $formatted_start_date = date('F j, Y', strtotime($start_date));
    }
    if (!empty($end_date)) {
        $formatted_end_date = date('F j, Y', strtotime($end_date));
    }
    
    $event_data = array(
        'id' => $event_id,
        'title' => $event->post_title,
        'description' => wp_strip_all_tags($event->post_content),
        'url' => get_permalink($event_id),
        'start_date' => $start_date,
        'end_date' => $end_date,
        'start_time' => $start_time,
        'end_time' => $end_time,
        'location' => $location,
        'price' => $price,
        'capacity' => $capacity,
        'contact' => $contact,
        'formatted_start_date' => $formatted_start_date,
        'formatted_end_date' => $formatted_end_date,
        'debug_info' => array(
            'start_date_raw' => $start_date,
            'end_date_raw' => $end_date,
            'start_time_raw' => $start_time,
            'end_time_raw' => $end_time,
            'location_raw' => $location,
            'price_raw' => $price,
            'capacity_raw' => $capacity,
            'contact_raw' => $contact,
        )
    );
    
    wp_send_json_success($event_data);
}
add_action('wp_ajax_dz_get_event_calendar_data', 'dz_ajax_get_event_calendar_data');
add_action('wp_ajax_nopriv_dz_get_event_calendar_data', 'dz_ajax_get_event_calendar_data');

// AJAX handler for sending invitations
function dz_ajax_send_event_invitations() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'dz_events_nonce')) {
        wp_die('Security check failed');
    }
    
    $event_id = intval($_POST['event_id']);
    $event = get_post($event_id);
    
    if (!$event || $event->post_type !== 'dz_event') {
        wp_send_json_error('Event not found');
    }
    
    // Parse form data
    parse_str($_POST['form_data'], $form_data);
    
    $name = sanitize_text_field($form_data['name']);
    $friend_names = array_map('trim', explode("\n", $form_data['friend_names']));
    $friend_emails = array_map('trim', explode("\n", $form_data['friend_emails']));
    $message = sanitize_textarea_field($form_data['message']);
    $custom_subject = sanitize_text_field($form_data['subject']);
    
    // Validate data
    if (empty($name) || empty($friend_names) || empty($friend_emails)) {
        wp_send_json_error('Please fill in all required fields');
    }
    
    if (count($friend_names) !== count($friend_emails)) {
        wp_send_json_error('Number of names and emails must match');
    }
    
    // Send emails
    $event_title = $event->post_title;
    $event_url = get_permalink($event_id);
    $event_date = get_post_meta($event_id, '_dz_event_start', true);
    $event_location = get_post_meta($event_id, '_dz_event_location', true);
    
    $sent_count = 0;
    $errors = array();
    
    for ($i = 0; $i < count($friend_emails); $i++) {
        $friend_name = sanitize_text_field($friend_names[$i]);
        $friend_email = sanitize_email($friend_emails[$i]);
        
        if (!is_email($friend_email)) {
            $errors[] = "Invalid email: $friend_email";
            continue;
        }
        
        // Use custom subject if provided, otherwise default
        $subject = !empty($custom_subject) ? $custom_subject : sprintf(__('You\'re invited to: %s', 'designzeen-events'), $event_title);
        
        // Format event date and time
        $formatted_date = '';
        if ($event_date) {
            $formatted_date = date('l, F j, Y', strtotime($event_date));
        }
        
        $formatted_time = '';
        $start_time = get_post_meta($event_id, '_dz_event_time_start', true);
        if ($start_time) {
            $formatted_time = date('g:i A', strtotime($start_time));
        }
        
        // Build comprehensive email message
        $email_message = sprintf(
            __("Hello %s,\n\n%s has invited you to attend the following event:\n\n", 'designzeen-events'),
            $friend_name,
            $name
        );
        
        $email_message .= sprintf(
            __("📅 EVENT DETAILS:\nEvent: %s\n", 'designzeen-events'),
            $event_title
        );
        
        if ($formatted_date) {
            $email_message .= sprintf(__("Date: %s\n", 'designzeen-events'), $formatted_date);
        }
        
        if ($formatted_time) {
            $email_message .= sprintf(__("Time: %s\n", 'designzeen-events'), $formatted_time);
        }
        
        if ($event_location) {
            $email_message .= sprintf(__("Location: %s\n", 'designzeen-events'), $event_location);
        }
        
        $event_price = get_post_meta($event_id, '_dz_event_price', true);
        $email_message .= sprintf(__("Price: %s\n", 'designzeen-events'), $event_price ?: __('Contact for pricing', 'designzeen-events'));
        
        if ($message) {
            $email_message .= sprintf(__("\n💬 PERSONAL MESSAGE FROM %s:\n%s\n", 'designzeen-events'), $name, $message);
        }
        
        $email_message .= sprintf(
            __("\n🔗 VIEW EVENT & REGISTER:\n%s\n\n", 'designzeen-events'),
            $event_url
        );
        
        $email_message .= __("We hope to see you there!\n\nBest regards,\nZeen Events", 'designzeen-events');
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        if (wp_mail($friend_email, $subject, $email_message, $headers)) {
            $sent_count++;
        } else {
            $errors[] = "Failed to send email to: $friend_email";
        }
    }
    
    if ($sent_count > 0) {
        $message = sprintf(__('Successfully sent %d invitation(s)', 'designzeen-events'), $sent_count);
        if (!empty($errors)) {
            $message .= '. ' . __('Some emails failed to send: ', 'designzeen-events') . implode(', ', $errors);
        }
        wp_send_json_success($message);
    } else {
        wp_send_json_error(__('Failed to send any invitations', 'designzeen-events'));
    }
}
add_action('wp_ajax_dz_send_event_invitations', 'dz_ajax_send_event_invitations');
add_action('wp_ajax_nopriv_dz_send_event_invitations', 'dz_ajax_send_event_invitations');

// AJAX handler for event registration
function dz_ajax_register_attendee() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'dz_events_registration')) {
        wp_send_json_error('Security check failed');
    }
    
    $event_id = intval($_POST['event_id']);
    $form_data = $_POST['form_data'];
    
    // Parse form data
    parse_str($form_data, $parsed_data);
    
    $name = sanitize_text_field($parsed_data['name']);
    $email = sanitize_email($parsed_data['email']);
    $phone = sanitize_text_field($parsed_data['phone']);
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($phone)) {
        wp_send_json_error('Please fill in all required fields');
    }
    
    if (!is_email($email)) {
        wp_send_json_error('Please enter a valid email address');
    }
    
    // Check if event exists and is published
    $event = get_post($event_id);
    if (!$event || $event->post_type !== 'dz_event' || $event->post_status !== 'publish') {
        wp_send_json_error('Event not found or not available for registration');
    }
    
    // Check if event is still open for registration
    $status = get_post_meta($event_id, '_dz_event_status', true);
    if ($status === 'cancelled' || $status === 'completed') {
        wp_send_json_error('Registration is closed for this event');
    }
    
    // Check capacity if set
    $capacity = get_post_meta($event_id, '_dz_event_capacity', true);
    if ($capacity) {
        $current_registrations = get_post_meta($event_id, '_dz_event_registrations_count', true) ?: 0;
        if ($current_registrations >= $capacity) {
            wp_send_json_error('Event is at full capacity');
        }
    }
    
    // Check if user is already registered
    $existing_registration = get_posts([
        'post_type' => 'dz_event_registration',
        'meta_query' => [
            [
                'key' => '_dz_registration_event_id',
                'value' => $event_id,
                'compare' => '='
            ],
            [
                'key' => '_dz_registration_email',
                'value' => $email,
                'compare' => '='
            ]
        ],
        'posts_per_page' => 1
    ]);
    
    if (!empty($existing_registration)) {
        wp_send_json_error('You are already registered for this event');
    }
    
    // Create registration
    $registration_data = [
        'post_title' => sprintf(__('Registration for %s by %s', 'designzeen-events'), $event->post_title, $name),
        'post_type' => 'dz_event_registration',
        'post_status' => 'publish',
        'meta_input' => [
            '_dz_registration_event_id' => $event_id,
            '_dz_registration_name' => $name,
            '_dz_registration_email' => $email,
            '_dz_registration_phone' => $phone,
            '_dz_registration_date' => current_time('mysql'),
            '_dz_registration_status' => 'confirmed'
        ]
    ];
    
    $registration_id = wp_insert_post($registration_data);
    
    if ($registration_id && !is_wp_error($registration_id)) {
        // Update registration count
        $current_count = get_post_meta($event_id, '_dz_event_registrations_count', true) ?: 0;
        update_post_meta($event_id, '_dz_event_registrations_count', $current_count + 1);
        
        // Send confirmation email
        $event_title = $event->post_title;
        $event_date = get_post_meta($event_id, '_dz_event_start', true);
        $event_time = get_post_meta($event_id, '_dz_event_time_start', true);
        $event_location = get_post_meta($event_id, '_dz_event_location', true);
        
        $subject = sprintf(__('Registration Confirmation: %s', 'designzeen-events'), $event_title);
        
        $message = sprintf(
            __("Hello %s,\n\nThank you for registering for the following event:\n\n", 'designzeen-events'),
            $name
        );
        
        $message .= sprintf(__("Event: %s\n", 'designzeen-events'), $event_title);
        
        if ($event_date) {
            $message .= sprintf(__("Date: %s\n", 'designzeen-events'), date('l, F j, Y', strtotime($event_date)));
        }
        
        if ($event_time) {
            $message .= sprintf(__("Time: %s\n", 'designzeen-events'), date('g:i A', strtotime($event_time)));
        }
        
        if ($event_location) {
            $message .= sprintf(__("Location: %s\n", 'designzeen-events'), $event_location);
        }
        
        $message .= sprintf(
            __("\nRegistration ID: %s\n\n", 'designzeen-events'),
            $registration_id
        );
        
        $message .= __("We look forward to seeing you at the event!\n\nBest regards,\nZeen Events", 'designzeen-events');
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        wp_mail($email, $subject, $message, $headers);
        
        wp_send_json_success('Registration successful! You will receive a confirmation email shortly.');
    } else {
        wp_send_json_error('Failed to create registration. Please try again.');
    }
}
add_action('wp_ajax_dz_events_register_attendee', 'dz_ajax_register_attendee');
add_action('wp_ajax_nopriv_dz_events_register_attendee', 'dz_ajax_register_attendee');

// AJAX handler for archive search
function dz_ajax_archive_search() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'dz_events_archive_search')) {
        wp_send_json_error('Security check failed');
    }
    
    $search = sanitize_text_field($_POST['search'] ?? '');
    $category = sanitize_text_field($_POST['category'] ?? '');
    $date_from = sanitize_text_field($_POST['date_from'] ?? '');
    $date_to = sanitize_text_field($_POST['date_to'] ?? '');
    $price_min = floatval($_POST['price_min'] ?? 0);
    $price_max = floatval($_POST['price_max'] ?? 0);
    $status = sanitize_text_field($_POST['status'] ?? '');
    $sort = sanitize_text_field($_POST['sort'] ?? 'date_asc');
    $view = sanitize_text_field($_POST['view'] ?? 'grid');
    $page = intval($_POST['page'] ?? 1);
    $per_page = 12;
    
    // Build query args
    $args = [
        'post_type' => 'dz_event',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'paged' => $page,
        'meta_query' => []
    ];
    
    // Add search
    if (!empty($search)) {
        $args['s'] = $search;
    }
    
    // Add category filter
    if (!empty($category)) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'dz_event_category',
                'field' => 'slug',
                'terms' => $category
            ]
        ];
    }
    
    // Add date filters
    if (!empty($date_from)) {
        $args['meta_query'][] = [
            'key' => '_dz_event_start',
            'value' => $date_from,
            'compare' => '>=',
            'type' => 'DATE'
        ];
    }
    
    if (!empty($date_to)) {
        $args['meta_query'][] = [
            'key' => '_dz_event_start',
            'value' => $date_to,
            'compare' => '<=',
            'type' => 'DATE'
        ];
    }
    
    // Add price filters
    if ($price_min > 0) {
        $args['meta_query'][] = [
            'key' => '_dz_event_price',
            'value' => $price_min,
            'compare' => '>=',
            'type' => 'NUMERIC'
        ];
    }
    
    if ($price_max > 0) {
        $args['meta_query'][] = [
            'key' => '_dz_event_price',
            'value' => $price_max,
            'compare' => '<=',
            'type' => 'NUMERIC'
        ];
    }
    
    // Add status filter
    if (!empty($status)) {
        $args['meta_query'][] = [
            'key' => '_dz_event_status',
            'value' => $status,
            'compare' => '='
        ];
    }
    
    // Add sorting
    switch ($sort) {
        case 'date_desc':
            $args['orderby'] = 'meta_value';
            $args['meta_key'] = '_dz_event_start';
            $args['order'] = 'DESC';
            break;
        case 'date_asc':
        default:
            $args['orderby'] = 'meta_value';
            $args['meta_key'] = '_dz_event_start';
            $args['order'] = 'ASC';
            break;
        case 'title_asc':
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
        case 'title_desc':
            $args['orderby'] = 'title';
            $args['order'] = 'DESC';
            break;
        case 'price_asc':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = '_dz_event_price';
            $args['order'] = 'ASC';
            break;
        case 'price_desc':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = '_dz_event_price';
            $args['order'] = 'DESC';
            break;
    }
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        ob_start();
        
        echo '<div class="dz-events-container" data-view="' . esc_attr($view) . '">';
        
        while ($query->have_posts()) {
            $query->the_post();
            $event_id = get_the_ID();
            
            // Get event data
            $start_date = get_post_meta($event_id, '_dz_event_start', true);
            $time_start = get_post_meta($event_id, '_dz_event_time_start', true);
            $location = get_post_meta($event_id, '_dz_event_location', true);
            $price = get_post_meta($event_id, '_dz_event_price', true);
            $status = get_post_meta($event_id, '_dz_event_status', true);
            $featured = get_post_meta($event_id, '_dz_event_featured', true);
            
            // Format date and time
            $formatted_date = $start_date ? date('M j, Y', strtotime($start_date)) : '';
            $formatted_time = $time_start ? date('g:i A', strtotime($time_start)) : '';
            
            echo '<div class="dz-event-card" data-event-id="' . esc_attr($event_id) . '">';
            
            if (has_post_thumbnail()) {
                echo '<div class="dz-event-image">';
                the_post_thumbnail('medium');
                if ($featured) {
                    echo '<span class="dz-event-badge dz-featured">' . __('Featured', 'designzeen-events') . '</span>';
                }
                if ($status) {
                    echo '<span class="dz-event-badge dz-status dz-status-' . esc_attr($status) . '">' . esc_html(ucfirst($status)) . '</span>';
                }
                echo '</div>';
            }
            
            echo '<div class="dz-event-content">';
            echo '<h3 class="dz-event-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
            
            echo '<div class="dz-event-meta">';
            if ($formatted_date) {
                echo '<div class="dz-meta-item"><i class="fas fa-calendar-alt"></i> ' . esc_html($formatted_date) . '</div>';
            }
            if ($formatted_time) {
                echo '<div class="dz-meta-item"><i class="fas fa-clock"></i> ' . esc_html($formatted_time) . '</div>';
            }
            if ($location) {
                echo '<div class="dz-meta-item"><i class="fas fa-map-marker-alt"></i> ' . esc_html($location) . '</div>';
            }
            if ($price) {
                echo '<div class="dz-meta-item"><i class="fas fa-tag"></i> ' . esc_html($price) . '</div>';
            }
            echo '</div>';
            
            if (has_excerpt()) {
                echo '<div class="dz-event-excerpt">' . get_the_excerpt() . '</div>';
            }
            
            echo '<div class="dz-event-actions">';
            echo '<a href="' . get_permalink() . '" class="dz-btn dz-btn-primary">' . __('View Details', 'designzeen-events') . '</a>';
            echo '</div>';
            
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>';
        
        // Pagination
        if ($query->max_num_pages > 1) {
            echo '<div class="dz-pagination">';
            echo paginate_links([
                'total' => $query->max_num_pages,
                'current' => $page,
                'format' => '?page=%#%',
                'prev_text' => __('Previous', 'designzeen-events'),
                'next_text' => __('Next', 'designzeen-events')
            ]);
            echo '</div>';
        }
        
        $html = ob_get_clean();
        wp_reset_postdata();
        
        wp_send_json_success(['html' => $html]);
    } else {
        wp_send_json_error('No events found matching your criteria');
    }
}
add_action('wp_ajax_dz_events_archive_search', 'dz_ajax_archive_search');
add_action('wp_ajax_nopriv_dz_events_archive_search', 'dz_ajax_archive_search');

// Simple version management for cache busting
function dz_update_events_version() {
    $current_version = get_option('dz_events_version', '1.0.0');
    $new_version = floatval($current_version) + 0.1;
    update_option('dz_events_version', $new_version);
    return $new_version;
}

// Auto-update version when plugin is activated
function dz_auto_update_version() {
    dz_update_events_version();
}
add_action('activated_plugin', 'dz_auto_update_version');
