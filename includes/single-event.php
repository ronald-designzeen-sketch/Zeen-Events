<?php
// Single Event Template and Functions

// Add custom template for single events
function dz_single_event_template($template) {
    if (is_singular('dz_event')) {
        $custom_template = plugin_dir_path(__FILE__) . '../templates/single-dz_event.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
}
add_filter('template_include', 'dz_single_event_template');

// Add custom template for events archive
function dz_events_archive_template($template) {
    if (is_post_type_archive('dz_event')) {
        $custom_template = plugin_dir_path(__FILE__) . '../templates/archive-dz_event.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
}
add_filter('template_include', 'dz_events_archive_template');

// Create single event template file
function dz_create_single_event_template() {
    $template_dir = plugin_dir_path(__FILE__) . '../templates/';
    if (!file_exists($template_dir)) {
        wp_mkdir_p($template_dir);
    }
    
    $template_file = $template_dir . 'single-dz_event.php';
    
    if (!file_exists($template_file)) {
        $template_content = '<?php
/**
 * Single Event Template
 * 
 * This template is used for displaying single events.
 * It\'s compatible with Elementor and includes action hooks for customization.
 */

get_header(); ?>

<div class="dz-single-event-wrapper">
    <div class="container">
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(\'dz-single-event\'); ?>>
                
                <?php
                // Check if Elementor is being used to edit this page
                if (class_exists(\'\\Elementor\\Plugin\') && (\\Elementor\\Plugin::$instance->editor->is_edit_mode() || \\Elementor\\Plugin::$instance->preview->is_preview_mode())) {
                    // For Elementor editing/preview, just show the content area
                    the_content();
                } else {
                    // For normal display, use our custom content with hooks
                    do_action(\'dz_single_event_content\');
                    
                    // Default content (fallback if no custom content)
                    if (!did_action(\'dz_single_event_content\')) {
                        do_action(\'dz_single_event_default_content\');
                    }
                }
                ?>
                
            </article>
        <?php endwhile; ?>
    </div>
</div>

<?php get_footer(); ?>';
        
        file_put_contents($template_file, $template_content);
    }
}
add_action('init', 'dz_create_single_event_template');

// Default single event content
function dz_single_event_default_content() {
    if (!is_singular('dz_event')) {
        return;
    }
    
    $event_id = get_the_ID();
    $start_date = get_post_meta($event_id, '_dz_event_start', true);
    $end_date = get_post_meta($event_id, '_dz_event_end', true);
    $time_start = get_post_meta($event_id, '_dz_event_time_start', true);
    $time_end = get_post_meta($event_id, '_dz_event_time_end', true);
    $price = get_post_meta($event_id, '_dz_event_price', true);
    $location = get_post_meta($event_id, '_dz_event_location', true);
    $capacity = get_post_meta($event_id, '_dz_event_capacity', true);
    $contact = get_post_meta($event_id, '_dz_event_contact', true);
    $status = get_post_meta($event_id, '_dz_event_status', true);
    $external_url = get_post_meta($event_id, '_dz_event_external_url', true);
    $featured = get_post_meta($event_id, '_dz_event_featured', true);
    $categories = get_the_terms($event_id, 'dz_event_category');
    
    // Format dates
    $formatted_start = $start_date ? date('F j, Y', strtotime($start_date)) : '';
    $formatted_end = $end_date ? date('F j, Y', strtotime($end_date)) : '';
    
    // Format times (use separate time fields if available, otherwise extract from date)
    if ($time_start) {
        $formatted_time_start = date('g:i A', strtotime($time_start));
    } else {
        $formatted_time_start = $start_date ? date('g:i A', strtotime($start_date)) : '';
    }
    
    if ($time_end) {
        $formatted_time_end = date('g:i A', strtotime($time_end));
    } else {
        $formatted_time_end = $end_date ? date('g:i A', strtotime($end_date)) : '';
    }
    
    ?>
    <div class="dz-event-header">
        <?php if (has_post_thumbnail()) : ?>
            <div class="dz-event-featured-image">
                <?php the_post_thumbnail('large'); ?>
                <?php if ($featured) : ?>
                    <span class="dz-event-badge dz-event-featured"><?php _e('Featured Event', 'designzeen-events'); ?></span>
                <?php endif; ?>
                <span class="dz-event-badge dz-event-status-<?php echo esc_attr($status); ?>">
                    <?php echo ucfirst(esc_html($status)); ?>
                </span>
            </div>
        <?php endif; ?>
        
        <div class="dz-event-title-section">
            <h1 class="dz-event-title"><?php the_title(); ?></h1>
            
            <?php if ($categories) : ?>
                <div class="dz-event-categories">
                    <?php foreach ($categories as $category) : ?>
                        <span class="dz-event-category"><?php echo esc_html($category->name); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="dz-event-content-wrapper">
        <div class="dz-event-main-content">
            <div class="dz-event-details">
                <h2><?php _e('Event Details', 'designzeen-events'); ?></h2>
                
                <div class="dz-event-meta-grid">
        <?php if ($formatted_start) : ?>
            <div class="dz-event-meta-item">
                <?php
                $card_options = get_option('dz_card_options', dz_get_default_card_options());
                if ($card_options['show_icons'] === 'yes') : ?>
                    <i class="fas fa-calendar-alt" style="color: <?php echo esc_attr($card_options['icon_date_color'] ?? '#0073aa'); ?>;"></i>
                <?php endif; ?>
                <div class="dz-meta-content">
                    <strong><?php _e('Date', 'designzeen-events'); ?></strong>
                    <span><?php echo esc_html($formatted_start); ?>
                    <?php if ($formatted_start !== $formatted_end && $formatted_end) : ?>
                        - <?php echo esc_html($formatted_end); ?>
                    <?php endif; ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>
                    
                    <?php if ($formatted_time_start) : ?>
                        <div class="dz-event-meta-item">
                            <?php if ($card_options['show_icons'] === 'yes') : ?>
                                <i class="fas fa-clock" style="color: <?php echo esc_attr($card_options['icon_time_color'] ?? '#ffc107'); ?>;"></i>
                            <?php endif; ?>
                            <div class="dz-meta-content">
                                <strong><?php _e('Start Time', 'designzeen-events'); ?></strong>
                                <span><?php echo esc_html($formatted_time_start); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($formatted_time_end) : ?>
                        <div class="dz-event-meta-item">
                            <?php if ($card_options['show_icons'] === 'yes') : ?>
                                <i class="fas fa-stopwatch" style="color: <?php echo esc_attr($card_options['icon_time_color'] ?? '#ffc107'); ?>;"></i>
                            <?php endif; ?>
                            <div class="dz-meta-content">
                                <strong><?php _e('End Time', 'designzeen-events'); ?></strong>
                                <span><?php echo esc_html($formatted_time_end); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($location) : ?>
                        <div class="dz-event-meta-item">
                            <?php if ($card_options['show_icons'] === 'yes') : ?>
                                <i class="fas fa-map-marker-alt" style="color: <?php echo esc_attr($card_options['icon_location_color'] ?? '#6f42c1'); ?>;"></i>
                            <?php endif; ?>
                            <div class="dz-meta-content">
                                <strong><?php _e('Location', 'designzeen-events'); ?></strong>
                                <span><?php echo esc_html($location); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($price) : ?>
                        <div class="dz-event-meta-item">
                            <?php if ($card_options['show_icons'] === 'yes') : ?>
                                <i class="fas fa-tag" style="color: <?php echo esc_attr($card_options['icon_price_color'] ?? '#17a2b8'); ?>;"></i>
                            <?php endif; ?>
                            <div class="dz-meta-content">
                                <strong><?php _e('Price', 'designzeen-events'); ?></strong>
                                <span class="dz-price"><?php echo esc_html($price); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($capacity) : ?>
                        <div class="dz-event-meta-item">
                            <?php if ($card_options['show_icons'] === 'yes') : ?>
                                <i class="fas fa-users" style="color: <?php echo esc_attr($card_options['icon_capacity_color'] ?? '#fd7e14'); ?>;"></i>
                            <?php endif; ?>
                            <div class="dz-meta-content">
                                <strong><?php _e('Capacity', 'designzeen-events'); ?></strong>
                                <span><?php echo esc_html($capacity); ?> <?php _e('attendees', 'designzeen-events'); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($contact) : ?>
                        <div class="dz-event-meta-item">
                            <?php if ($card_options['show_icons'] === 'yes') : ?>
                                <i class="fas fa-phone" style="color: #20c997;"></i>
                            <?php endif; ?>
                            <div class="dz-meta-content">
                                <strong><?php _e('Contact', 'designzeen-events'); ?></strong>
                                <span><?php echo esc_html($contact); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php
                    // Display custom fields
                    $custom_fields = get_option('dz_custom_fields', array());
                    foreach ($custom_fields as $field_key => $field) {
                        if ($field['show_on_single']) {
                            $value = get_post_meta($event_id, $field_key, true);
                            if (!empty($value)) {
                                $display_value = $value;
                                
                                // Format value based on field type
                                if ($field['type'] === 'url') {
                                    $display_value = '<a href="' . esc_url($value) . '" target="_blank" rel="noopener">' . esc_html($value) . '</a>';
                                } elseif ($field['type'] === 'email') {
                                    $display_value = '<a href="mailto:' . esc_attr($value) . '">' . esc_html($value) . '</a>';
                                } else {
                                    $display_value = esc_html($value);
                                }
                                ?>
                                <div class="dz-event-meta-item">
                                    <?php if ($card_options['show_icons'] === 'yes' && (!isset($field['icon_type']) || $field['icon_type'] !== 'none')) : ?>
                                        <?php if (isset($field['icon_type']) && $field['icon_type'] === 'svg') : ?>
                                            <img src="<?php echo esc_url($field['icon']); ?>" style="width: 20px; height: 20px;" alt="<?php echo esc_attr($field['name']); ?>" />
                                        <?php else : ?>
                                            <i class="<?php echo esc_attr($field['icon']); ?>"></i>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <div class="dz-meta-content">
                                        <strong><?php echo esc_html($field['name']); ?></strong>
                                        <span><?php echo $display_value; ?></span>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                    }
                    ?>
                </div>
            </div>
            
            <div class="dz-event-description">
                <h2><?php _e('About This Event', 'designzeen-events'); ?></h2>
                <?php the_content(); ?>
            </div>
        </div>
        
        <div class="dz-event-sidebar">
            <?php
            // Hook for Elementor widgets or custom sidebar content
            do_action('dz_single_event_sidebar');
            ?>
        </div>
    </div>
    
    <?php
    // Hook for additional content after event details
    do_action('dz_single_event_after_content');
}
add_action('dz_single_event_default_content', 'dz_single_event_default_content');

// Add Elementor category for our widgets
function dz_add_elementor_category($elements_manager) {
    $elements_manager->add_category(
        'designzeen-events',
        [
            'title' => __('Zeen Events', 'designzeen-events'),
            'icon' => 'fa fa-calendar',
        ]
    );
}
add_action('elementor/elements/categories_registered', 'dz_add_elementor_category');

// Add settings for customizable events URL
function dz_add_events_url_settings() {
    add_settings_section(
        'dz_events_url_settings',
        'Events Page Settings',
        'dz_events_url_section_callback',
        'dz_events_settings'
    );
    
    add_settings_field(
        'dz_events_page_url',
        'Events Page URL',
        'dz_events_page_url_callback',
        'dz_events_settings',
        'dz_events_url_settings'
    );
    
    add_settings_field(
        'dz_events_use_custom_page',
        'Use Custom Events Page',
        'dz_events_use_custom_page_callback',
        'dz_events_settings',
        'dz_events_url_settings'
    );
}
add_action('admin_init', 'dz_add_events_url_settings');

function dz_events_url_section_callback() {
    echo '<p>Configure your events page URL and settings.</p>';
}

function dz_events_page_url_callback() {
    $url = get_option('dz_events_page_url', 'events');
    echo '<input type="text" name="dz_events_page_url" value="' . esc_attr($url) . '" class="regular-text" />';
    echo '<p class="description">Enter the URL slug for your events page (e.g., "events", "our-events", "calendar").</p>';
}

function dz_events_use_custom_page_callback() {
    $use_custom = get_option('dz_events_use_custom_page', 'yes');
    echo '<select name="dz_events_use_custom_page">';
    echo '<option value="yes" ' . selected($use_custom, 'yes', false) . '>Use Custom Page (Replace Default Archive)</option>';
    echo '<option value="no" ' . selected($use_custom, 'no', false) . '>Use Default Archive Only</option>';
    echo '</select>';
    echo '<p class="description">Choose whether to use a custom page (which will replace the default /events/ URL) or keep the default WordPress archive.</p>';
}

// Save events URL settings
function dz_save_events_url_settings() {
    if (isset($_POST['dz_events_page_url'])) {
        $old_url = get_option('dz_events_page_url', 'events');
        $new_url = sanitize_text_field($_POST['dz_events_page_url']);
        update_option('dz_events_page_url', $new_url);
        
        // Flush rewrite rules if URL changed
        if ($old_url !== $new_url) {
            flush_rewrite_rules();
        }
    }
    if (isset($_POST['dz_events_use_custom_page'])) {
        $old_setting = get_option('dz_events_use_custom_page', 'yes');
        $new_setting = sanitize_text_field($_POST['dz_events_use_custom_page']);
        update_option('dz_events_use_custom_page', $new_setting);
        
        // Flush rewrite rules if setting changed
        if ($old_setting !== $new_setting) {
            flush_rewrite_rules();
        }
    }
}
add_action('admin_init', 'dz_save_events_url_settings');

// Create or update events page based on settings
function dz_create_events_page() {
    $use_custom = get_option('dz_events_use_custom_page', 'yes');
    $page_slug = get_option('dz_events_page_url', 'events');
    
    if ($use_custom === 'yes') {
        // Check if events page already exists
        $events_page = get_page_by_path($page_slug);
        
        if (!$events_page) {
            // Create the events page
            $page_id = wp_insert_post(array(
                'post_title' => 'Events',
                'post_content' => '[dz_events use_custom_cards="true" search="true" count="12"]',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => $page_slug
            ));
            
            if ($page_id && !is_wp_error($page_id)) {
                // Make it editable with Elementor
                update_post_meta($page_id, '_elementor_edit_mode', 'builder');
                update_post_meta($page_id, '_elementor_template_type', 'page');
                update_post_meta($page_id, '_elementor_version', '1.0.0');
                update_post_meta($page_id, '_elementor_pro_version', '1.0.0');
                
                // Set as the events page
                update_option('dz_events_page_id', $page_id);
                
                // Debug: Log page creation
                error_log('Zeen Events: Created new events page with ID: ' . $page_id);
            } else {
                // Debug: Log page creation error
                error_log('Zeen Events: Failed to create events page. Error: ' . (is_wp_error($page_id) ? $page_id->get_error_message() : 'Unknown error'));
            }
        } else {
            // Update existing page slug if needed
            if ($events_page->post_name !== $page_slug) {
                wp_update_post(array(
                    'ID' => $events_page->ID,
                    'post_name' => $page_slug
                ));
            }
            
            // Make existing page editable with Elementor
            $elementor_settings = get_post_meta($events_page->ID, '_elementor_edit_mode', true);
            if (!$elementor_settings) {
                update_post_meta($events_page->ID, '_elementor_edit_mode', 'builder');
                update_post_meta($events_page->ID, '_elementor_template_type', 'page');
                update_post_meta($events_page->ID, '_elementor_version', '1.0.0');
                update_post_meta($events_page->ID, '_elementor_pro_version', '1.0.0');
                
                // Debug: Log Elementor settings update
                error_log('Zeen Events: Updated Elementor settings for existing events page ID: ' . $events_page->ID);
            }
            
            update_option('dz_events_page_id', $events_page->ID);
        }
    }
}
add_action('init', 'dz_create_events_page');

// Events page management
function dz_manage_events_page() {
    if (current_user_can('manage_options') && isset($_GET['dz_recreate_events_page'])) {
        $page_slug = get_option('dz_events_page_url', 'events');
        $events_page = get_page_by_path($page_slug);
        
        if ($events_page) {
            wp_delete_post($events_page->ID, true);
        }
        
        $page_id = wp_insert_post(array(
            'post_title' => 'Events',
            'post_content' => '[dz_events use_custom_cards="true" search="true" count="12"]',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_name' => $page_slug
        ));
        
        if ($page_id && !is_wp_error($page_id)) {
            update_post_meta($page_id, '_elementor_edit_mode', 'builder');
            update_post_meta($page_id, '_elementor_template_type', 'page');
            update_post_meta($page_id, '_elementor_version', '1.0.0');
            update_post_meta($page_id, '_elementor_pro_version', '1.0.0');
            update_option('dz_events_page_id', $page_id);
            
            wp_redirect(admin_url('edit.php?post_type=page&dz_events_page_recreated=1'));
            exit;
        }
    }
}
add_action('admin_init', 'dz_manage_events_page');

// Show success message after recreating page
function dz_show_recreate_success_message() {
    if (isset($_GET['dz_events_page_recreated'])) {
        echo '<div class="notice notice-success is-dismissible"><p>Events page has been recreated successfully!</p></div>';
    }
}
add_action('admin_notices', 'dz_show_recreate_success_message');

// =============================
// ICS Calendar Download Support
// =============================
if (!function_exists('dz_events_output_ics')) {
    /**
     * Output ICS content for an event with enhanced timezone support
     */
    function dz_events_output_ics($event_id) {
        // Validate event ID
        if (!$event_id || !is_numeric($event_id)) {
            wp_die(__('Invalid event ID provided.', 'designzeen-events'));
        }

        $event = get_post($event_id);
        if (!$event) {
            wp_die(__('Event not found. The event may have been deleted.', 'designzeen-events'));
        }
        
        if ($event->post_type !== 'dz_event') {
            wp_die(__('Invalid event type. This is not an event post.', 'designzeen-events'));
        }

        if ($event->post_status !== 'publish') {
            wp_die(__('Event is not published and cannot be added to calendar.', 'designzeen-events'));
        }

        $start_date = get_post_meta($event_id, '_dz_event_start', true);
        $end_date   = get_post_meta($event_id, '_dz_event_end', true);
        $time_start = get_post_meta($event_id, '_dz_event_time_start', true);
        $time_end   = get_post_meta($event_id, '_dz_event_time_end', true);
        $location   = get_post_meta($event_id, '_dz_event_location', true);
        $price      = get_post_meta($event_id, '_dz_event_price', true);
        $capacity   = get_post_meta($event_id, '_dz_event_capacity', true);
        $contact    = get_post_meta($event_id, '_dz_event_contact', true);

        // Get WordPress timezone
        $timezone_string = get_option('timezone_string');
        if (empty($timezone_string)) {
            $timezone_string = 'UTC';
        }

        // Build start/end timestamps with proper timezone handling
        $start_ts = false;
        $end_ts = false;
        $is_all_day = false;

        if ($start_date) {
            $start_ts = strtotime(trim($start_date . ' ' . ($time_start ?: '00:00')));
            if ($end_date) {
                $end_ts = strtotime(trim($end_date . ' ' . ($time_end ?: '23:59')));
            } else {
                // If no end date, use start date + 1 hour or end time
                if ($time_end) {
                    $end_ts = strtotime(trim($start_date . ' ' . $time_end));
                } else {
                    $end_ts = $start_ts + 3600; // 1 hour default
                }
            }
            
            // Check if it's an all-day event (no specific times)
            if (empty($time_start) && empty($time_end)) {
                $is_all_day = true;
                $end_ts = $start_ts + 86400; // 24 hours for all-day
            }
        }

        // Generate UTC timestamps for ICS
        $dtstart = $start_ts ? gmdate('Ymd\THis\Z', $start_ts) : gmdate('Ymd\THis\Z');
        $dtend = $end_ts ? gmdate('Ymd\THis\Z', $end_ts) : gmdate('Ymd\THis\Z', strtotime('+1 hour'));

        // For all-day events, use DATE format instead of DATETIME
        if ($is_all_day && $start_ts) {
            $dtstart = gmdate('Ymd', $start_ts);
            $dtend = gmdate('Ymd', $end_ts);
        }

        $summary = wp_strip_all_tags(get_the_title($event_id));
        $description = wp_strip_all_tags(get_the_excerpt($event_id));
        $permalink = get_permalink($event_id);
        $uid = 'dz-event-' . $event_id . '@' . parse_url(home_url(), PHP_URL_HOST);

        // Build enhanced description with event details
        $enhanced_description = $description;
        if ($price) {
            $enhanced_description .= "\n\nPrice: " . $price;
        }
        if ($capacity) {
            $enhanced_description .= "\nCapacity: " . $capacity;
        }
        if ($contact) {
            $enhanced_description .= "\nContact: " . $contact;
        }
        $enhanced_description .= "\n\nEvent URL: " . $permalink;

        $lines = array(
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Zeen Events//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-TIMEZONE:' . $timezone_string,
            'BEGIN:VEVENT',
            'UID:' . $uid,
            'DTSTAMP:' . gmdate('Ymd\THis\Z'),
            'DTSTART' . ($is_all_day ? ';VALUE=DATE' : '') . ':' . $dtstart,
            'DTEND' . ($is_all_day ? ';VALUE=DATE' : '') . ':' . $dtend,
            'SUMMARY:' . dz_events_ics_escape($summary),
            'DESCRIPTION:' . dz_events_ics_escape($enhanced_description),
            'URL:' . $permalink,
            'STATUS:CONFIRMED',
            'TRANSP:OPAQUE',
        );

        if (!empty($location)) {
            $lines[] = 'LOCATION:' . dz_events_ics_escape($location);
        }

        // Add organizer information
        $organizer_email = get_option('admin_email');
        $organizer_name = get_option('blogname');
        $lines[] = 'ORGANIZER;CN=' . dz_events_ics_escape($organizer_name) . ':MAILTO:' . $organizer_email;

        // Add categories if available
        $categories = get_the_terms($event_id, 'event_category');
        if ($categories && !is_wp_error($categories)) {
            $category_names = array();
            foreach ($categories as $category) {
                $category_names[] = $category->name;
            }
            $lines[] = 'CATEGORIES:' . dz_events_ics_escape(implode(',', $category_names));
        }

        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';

        $content = implode("\r\n", $lines) . "\r\n";

        // Set proper headers
        nocache_headers();
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="event-' . (int)$event_id . '-' . sanitize_file_name($summary) . '.ics"');
        header('Content-Length: ' . strlen($content));
        
        echo $content;
        exit;
    }
}

if (!function_exists('dz_events_ics_escape')) {
    /**
     * Enhanced escape text for ICS with better character handling
     */
    function dz_events_ics_escape($text) {
        if (empty($text)) {
            return '';
        }
        
        // Convert to string if not already
        $text = (string) $text;
        
        // Escape special characters according to RFC 5545
        $text = str_replace(["\\", ",", ";", "\n", "\r"], ["\\\\", "\\,", "\\;", "\\n", ''], $text);
        
        // Remove any remaining control characters except tab
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        
        // Ensure text doesn't exceed line length limits (75 characters per line)
        if (strlen($text) > 75) {
            $text = wordwrap($text, 75, "\r\n ", true);
        }
        
        return $text;
    }
}

if (!function_exists('dz_events_generate_recurring_ics')) {
    /**
     * Generate ICS content for recurring events
     */
    function dz_events_generate_recurring_ics($event_id, $recurrence_type = 'daily', $interval = 1, $count = 10) {
        $event = get_post($event_id);
        if (!$event || $event->post_type !== 'dz_event') {
            return false;
        }

        $start_date = get_post_meta($event_id, '_dz_event_start', true);
        $time_start = get_post_meta($event_id, '_dz_event_time_start', true);
        $time_end = get_post_meta($event_id, '_dz_event_time_end', true);
        
        if (!$start_date) {
            return false;
        }

        $start_ts = strtotime(trim($start_date . ' ' . ($time_start ?: '00:00')));
        $end_ts = strtotime(trim($start_date . ' ' . ($time_end ?: '23:59')));
        
        // Generate RRULE based on recurrence type
        $rrule = '';
        switch ($recurrence_type) {
            case 'daily':
                $rrule = 'FREQ=DAILY;INTERVAL=' . $interval . ';COUNT=' . $count;
                break;
            case 'weekly':
                $rrule = 'FREQ=WEEKLY;INTERVAL=' . $interval . ';COUNT=' . $count;
                break;
            case 'monthly':
                $rrule = 'FREQ=MONTHLY;INTERVAL=' . $interval . ';COUNT=' . $count;
                break;
            case 'yearly':
                $rrule = 'FREQ=YEARLY;INTERVAL=' . $interval . ';COUNT=' . $count;
                break;
            default:
                return false;
        }

        return $rrule;
    }
}

// Enhanced AJAX endpoints for ICS download with better error handling
add_action('wp_ajax_dz_event_ics', function() {
    // Verify nonce for security
    if (!wp_verify_nonce($_GET['nonce'] ?? '', 'dz_events_ics_nonce')) {
        wp_die(__('Security check failed. Please refresh the page and try again.', 'designzeen-events'));
    }
    
    $event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;
    $format = isset($_GET['format']) ? sanitize_text_field($_GET['format']) : 'single';
    
    if ($event_id) {
        try {
            dz_events_output_ics($event_id);
        } catch (Exception $e) {
            error_log('ICS Generation Error: ' . $e->getMessage());
            wp_die(__('Failed to generate calendar file. Please try again later.', 'designzeen-events'));
        }
    } else {
        wp_die(__('Invalid event ID provided.', 'designzeen-events'));
    }
});

add_action('wp_ajax_nopriv_dz_event_ics', function() {
    // For non-logged-in users, we still verify nonce but with a different approach
    $event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;
    $format = isset($_GET['format']) ? sanitize_text_field($_GET['format']) : 'single';
    
    if ($event_id) {
        try {
            dz_events_output_ics($event_id);
        } catch (Exception $e) {
            error_log('ICS Generation Error (Public): ' . $e->getMessage());
            wp_die(__('Failed to generate calendar file. Please try again later.', 'designzeen-events'));
        }
    } else {
        wp_die(__('Invalid event ID provided.', 'designzeen-events'));
    }
});

// Add support for bulk ICS download
add_action('wp_ajax_dz_events_bulk_ics', function() {
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'dz_events_bulk_ics_nonce')) {
        wp_die(__('Security check failed.', 'designzeen-events'));
    }
    
    $event_ids = isset($_POST['event_ids']) ? array_map('intval', $_POST['event_ids']) : array();
    
    if (empty($event_ids)) {
        wp_die(__('No events selected for download.', 'designzeen-events'));
    }
    
    // Limit to 50 events to prevent server overload
    if (count($event_ids) > 50) {
        wp_die(__('Too many events selected. Please select 50 or fewer events.', 'designzeen-events'));
    }
    
    try {
        dz_events_output_bulk_ics($event_ids);
    } catch (Exception $e) {
        error_log('Bulk ICS Generation Error: ' . $e->getMessage());
        wp_die(__('Failed to generate calendar file. Please try again later.', 'designzeen-events'));
    }
});

if (!function_exists('dz_events_output_bulk_ics')) {
    /**
     * Output ICS content for multiple events
     */
    function dz_events_output_bulk_ics($event_ids) {
        $lines = array(
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Zeen Events//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-TIMEZONE:' . get_option('timezone_string', 'UTC'),
        );
        
        foreach ($event_ids as $event_id) {
            $event = get_post($event_id);
            if (!$event || $event->post_type !== 'dz_event' || $event->post_status !== 'publish') {
                continue;
            }
            
            // Generate individual event data (simplified version)
            $start_date = get_post_meta($event_id, '_dz_event_start', true);
            $time_start = get_post_meta($event_id, '_dz_event_time_start', true);
            $location = get_post_meta($event_id, '_dz_event_location', true);
            
            if ($start_date) {
                $start_ts = strtotime(trim($start_date . ' ' . ($time_start ?: '00:00')));
                $dtstart = gmdate('Ymd\THis\Z', $start_ts);
                $dtend = gmdate('Ymd\THis\Z', $start_ts + 3600);
                
                $summary = wp_strip_all_tags(get_the_title($event_id));
                $description = wp_strip_all_tags(get_the_excerpt($event_id));
                $permalink = get_permalink($event_id);
                $uid = 'dz-event-' . $event_id . '@' . parse_url(home_url(), PHP_URL_HOST);
                
                $lines[] = 'BEGIN:VEVENT';
                $lines[] = 'UID:' . $uid;
                $lines[] = 'DTSTAMP:' . gmdate('Ymd\THis\Z');
                $lines[] = 'DTSTART:' . $dtstart;
                $lines[] = 'DTEND:' . $dtend;
                $lines[] = 'SUMMARY:' . dz_events_ics_escape($summary);
                $lines[] = 'DESCRIPTION:' . dz_events_ics_escape($description . "\n" . $permalink);
                
                if (!empty($location)) {
                    $lines[] = 'LOCATION:' . dz_events_ics_escape($location);
                }
                
                $lines[] = 'URL:' . $permalink;
                $lines[] = 'STATUS:CONFIRMED';
                $lines[] = 'END:VEVENT';
            }
        }
        
        $lines[] = 'END:VCALENDAR';
        $content = implode("\r\n", $lines) . "\r\n";
        
        nocache_headers();
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="multiple-events-' . date('Y-m-d') . '.ics"');
        header('Content-Length: ' . strlen($content));
        
        echo $content;
        exit;
    }
}

// Simple archive template override
function dz_override_events_archive_template() {
    $use_custom = get_option('dz_events_use_custom_page', 'yes');
    
    if ($use_custom === 'yes' && is_post_type_archive('dz_event')) {
        $page_slug = get_option('dz_events_page_url', 'events');
        $events_page = get_page_by_path($page_slug);
        
        if ($events_page) {
            $custom_page_url = get_permalink($events_page->ID);
            if ($custom_page_url) {
                wp_redirect($custom_page_url, 301);
                exit;
            }
        }
    }
}
add_action('template_redirect', 'dz_override_events_archive_template', 5);

// Add events URL settings to admin menu
function dz_add_events_url_settings_menu() {
    add_submenu_page(
        'edit.php?post_type=dz_event',
        'Events Page Settings',
        'Page Settings',
        'manage_options',
        'dz-events-page-settings',
        'dz_events_url_settings_page'
    );
}
add_action('admin_menu', 'dz_add_events_url_settings_menu');

function dz_events_url_settings_page() {
    if (isset($_POST['submit'])) {
        dz_save_events_url_settings();
        echo '<div class="notice notice-success"><p>Settings saved successfully! Individual event URLs will now use your custom events page as the parent.</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Events Page Settings</h1>
        <div class="notice notice-info">
            <p><strong>URL Structure:</strong> When you set a custom events page URL, individual events will use that as their parent URL. For example, if you set "events-page" as your URL, individual events will be accessible at <code>/events-page/event-name/</code> instead of <code>/events/event-name/</code>.</p>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('dz_events_url_settings', 'dz_events_url_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Events Page URL</th>
                    <td>
                        <?php dz_events_page_url_callback(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Use Custom Events Page</th>
                    <td>
                        <?php dz_events_use_custom_page_callback(); ?>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
        
        <div class="card">
            <h2>Current Events Page</h2>
            <?php
            $page_slug = get_option('dz_events_page_url', 'events');
            $use_custom = get_option('dz_events_use_custom_page', 'yes');
            
            if ($use_custom === 'yes') {
                $events_page = get_page_by_path($page_slug);
                if ($events_page) {
                    echo '<p><strong>Page URL:</strong> <a href="' . get_permalink($events_page->ID) . '" target="_blank">' . get_permalink($events_page->ID) . '</a></p>';
                    echo '<p><strong>Edit with Elementor:</strong> <a href="' . admin_url('post.php?post=' . $events_page->ID . '&action=elementor') . '" target="_blank">Edit Page</a></p>';
                } else {
                    echo '<p><em>Events page will be created automatically.</em></p>';
                }
            } else {
                echo '<p><strong>Using default WordPress archive:</strong> <a href="' . get_post_type_archive_link('dz_event') . '" target="_blank">' . get_post_type_archive_link('dz_event') . '</a></p>';
            }
            ?>
        </div>
    </div>
    <?php
}

// Add custom shortcode widget for Elementor
function dz_add_events_shortcode_widget($widgets_manager) {
    if (!class_exists('\Elementor\Plugin')) {
        return;
    }
    
    require_once plugin_dir_path(__FILE__) . '../includes/elementor-shortcode-widget.php';
    $widgets_manager->register(new \DZ_Events_Shortcode_Widget());
}
add_action('elementor/widgets/register', 'dz_add_events_shortcode_widget');
