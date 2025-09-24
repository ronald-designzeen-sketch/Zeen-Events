<?php
function dz_add_event_meta_boxes() {
    add_meta_box(
        'dz_event_details',
        'Event Details',
        'dz_event_details_callback',
        'dz_event',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'dz_add_event_meta_boxes');

function dz_event_details_callback($post) {
    // Add nonce field for security
    wp_nonce_field('dz_event_meta_box', 'dz_event_meta_box_nonce');
    
    $date_start = get_post_meta($post->ID, '_dz_event_start', true);
    $date_end   = get_post_meta($post->ID, '_dz_event_end', true);
    $time_start = get_post_meta($post->ID, '_dz_event_time_start', true);
    $time_end   = get_post_meta($post->ID, '_dz_event_time_end', true);
    $price      = get_post_meta($post->ID, '_dz_event_price', true);
    $location   = get_post_meta($post->ID, '_dz_event_location', true);
    $capacity   = get_post_meta($post->ID, '_dz_event_capacity', true);
    $contact    = get_post_meta($post->ID, '_dz_event_contact', true);
    $status     = get_post_meta($post->ID, '_dz_event_status', true);
    $external_url = get_post_meta($post->ID, '_dz_event_external_url', true);

    ?>
    <style>
        .dz-meta-box-icons th {
            position: relative;
            padding-left: 30px !important;
        }
        .dz-meta-box-icons th i {
            position: absolute;
            left: 5px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
        }
    </style>
    
    <table class="form-table dz-meta-box-icons">
        <tr>
            <th><i class="fas fa-calendar-alt" style="color: #0073aa;"></i><label for="dz_event_start">Start Date *</label></th>
            <td><input type="date" id="dz_event_start" name="dz_event_start" value="<?php echo esc_attr($date_start); ?>" required></td>
        </tr>
        <tr>
            <th><i class="fas fa-calendar-check" style="color: #28a745;"></i><label for="dz_event_end">End Date *</label></th>
            <td><input type="date" id="dz_event_end" name="dz_event_end" value="<?php echo esc_attr($date_end); ?>" required></td>
        </tr>
        <tr>
            <th><i class="fas fa-clock" style="color: #ffc107;"></i><label for="dz_event_time_start">Start Time</label></th>
            <td><input type="time" id="dz_event_time_start" name="dz_event_time_start" value="<?php echo esc_attr($time_start); ?>"></td>
        </tr>
        <tr>
            <th><i class="fas fa-stopwatch" style="color: #dc3545;"></i><label for="dz_event_time_end">End Time</label></th>
            <td><input type="time" id="dz_event_time_end" name="dz_event_time_end" value="<?php echo esc_attr($time_end); ?>"></td>
        </tr>
        <tr>
            <th><i class="fas fa-tag" style="color: #17a2b8;"></i><label for="dz_event_price">Price</label></th>
            <td><input type="text" id="dz_event_price" name="dz_event_price" value="<?php echo esc_attr($price); ?>" placeholder="e.g. $50 or Free"></td>
        </tr>
        <tr>
            <th><i class="fas fa-map-marker-alt" style="color: #6f42c1;"></i><label for="dz_event_location">Location</label></th>
            <td><input type="text" id="dz_event_location" name="dz_event_location" value="<?php echo esc_attr($location); ?>" placeholder="e.g. Convention Center, New York"></td>
        </tr>
        <tr>
            <th><i class="fas fa-users" style="color: #fd7e14;"></i><label for="dz_event_capacity">Capacity</label></th>
            <td><input type="number" id="dz_event_capacity" name="dz_event_capacity" value="<?php echo esc_attr($capacity); ?>" placeholder="e.g. 100" min="1"></td>
        </tr>
        <tr>
            <th><i class="fas fa-phone" style="color: #20c997;"></i><label for="dz_event_contact">Contact Info</label></th>
            <td><input type="text" id="dz_event_contact" name="dz_event_contact" value="<?php echo esc_attr($contact); ?>" placeholder="e.g. events@company.com or +1-555-0123"></td>
        </tr>
        <tr>
            <th><i class="fas fa-info-circle" style="color: #6c757d;"></i><label for="dz_event_status">Event Status</label></th>
            <td>
                <select id="dz_event_status" name="dz_event_status">
                    <option value="upcoming" <?php selected($status, 'upcoming'); ?>>Upcoming</option>
                    <option value="ongoing" <?php selected($status, 'ongoing'); ?>>Ongoing</option>
                    <option value="completed" <?php selected($status, 'completed'); ?>>Completed</option>
                    <option value="cancelled" <?php selected($status, 'cancelled'); ?>>Cancelled</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><i class="fas fa-external-link-alt" style="color: #007cba;"></i><label for="dz_event_external_url">External URL</label></th>
            <td><input type="url" id="dz_event_external_url" name="dz_event_external_url" value="<?php echo esc_attr($external_url); ?>" placeholder="https://example.com/tickets"></td>
        </tr>
        <tr>
            <th><i class="fas fa-star" style="color: #ffc107;"></i><label for="dz_event_featured">Featured Event</label></th>
            <td>
                <input type="checkbox" id="dz_event_featured" name="dz_event_featured" value="1" <?php checked(get_post_meta($post->ID, '_dz_event_featured', true), '1'); ?>>
                <label for="dz_event_featured">Mark this event as featured</label>
            </td>
        </tr>
    </table>
    
    <?php
    // Hook for custom fields
    do_action('dz_event_details_meta_box');
    ?>
    <?php
}

function dz_save_event_details($post_id) {
    // Check if nonce is set and valid
    if (!isset($_POST['dz_event_meta_box_nonce']) || !wp_verify_nonce($_POST['dz_event_meta_box_nonce'], 'dz_event_meta_box')) {
        return;
    }

    // Check if user has permission to edit this post
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check if this is the correct post type
    if (get_post_type($post_id) !== 'dz_event') {
        return;
    }

    // Sanitize and save meta fields
    $fields = array(
        '_dz_event_start' => 'dz_event_start',
        '_dz_event_end' => 'dz_event_end',
        '_dz_event_time_start' => 'dz_event_time_start',
        '_dz_event_time_end' => 'dz_event_time_end',
        '_dz_event_price' => 'dz_event_price',
        '_dz_event_location' => 'dz_event_location',
        '_dz_event_capacity' => 'dz_event_capacity',
        '_dz_event_contact' => 'dz_event_contact',
        '_dz_event_status' => 'dz_event_status',
        '_dz_event_external_url' => 'dz_event_external_url',
        '_dz_event_featured' => 'dz_event_featured'
    );

    foreach ($fields as $meta_key => $post_key) {
        if (isset($_POST[$post_key])) {
            $value = '';
            
            switch ($post_key) {
                case 'dz_event_start':
                case 'dz_event_end':
                    $value = sanitize_text_field($_POST[$post_key]);
                    // Validate date format
                    if (!empty($value) && !strtotime($value)) {
                        $value = '';
                    }
                    break;
                case 'dz_event_capacity':
                    $value = intval($_POST[$post_key]);
                    if ($value < 0) $value = 0;
                    break;
                case 'dz_event_external_url':
                    $value = esc_url_raw($_POST[$post_key]);
                    break;
                case 'dz_event_status':
                    $allowed_statuses = array('upcoming', 'ongoing', 'completed', 'cancelled');
                    $value = sanitize_text_field($_POST[$post_key]);
                    if (!in_array($value, $allowed_statuses)) {
                        $value = 'upcoming';
                    }
                    break;
                case 'dz_event_featured':
                    $value = '1'; // Checkbox is checked
                    break;
                default:
                    $value = sanitize_text_field($_POST[$post_key]);
                    break;
            }
            
            update_post_meta($post_id, $meta_key, $value);
        } else {
            // Handle unchecked checkboxes
            if ($post_key === 'dz_event_featured') {
                update_post_meta($post_id, $meta_key, '0');
            }
        }
    }

    // Auto-update status based on dates if not manually set
    $start_date = get_post_meta($post_id, '_dz_event_start', true);
    $end_date = get_post_meta($post_id, '_dz_event_end', true);
    $current_status = get_post_meta($post_id, '_dz_event_status', true);
    
    if (!empty($start_date) && !empty($end_date) && $current_status !== 'cancelled') {
        $today = date('Y-m-d');
        $start_timestamp = strtotime($start_date);
        $end_timestamp = strtotime($end_date);
        $today_timestamp = strtotime($today);
        
        if ($today_timestamp < $start_timestamp) {
            update_post_meta($post_id, '_dz_event_status', 'upcoming');
        } elseif ($today_timestamp >= $start_timestamp && $today_timestamp <= $end_timestamp) {
            update_post_meta($post_id, '_dz_event_status', 'ongoing');
        } elseif ($today_timestamp > $end_timestamp) {
            update_post_meta($post_id, '_dz_event_status', 'completed');
        }
    }
}
add_action('save_post', 'dz_save_event_details');
