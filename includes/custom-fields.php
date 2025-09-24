<?php
/**
 * Custom Fields Management for Events
 * Allows adding custom fields with icons via WordPress dashboard
 */

// Add custom fields menu
function dz_add_custom_fields_menu() {
    add_submenu_page(
        'edit.php?post_type=dz_event',
        'Custom Fields',
        'Custom Fields',
        'manage_options',
        'dz-custom-fields',
        'dz_custom_fields_page'
    );
}
add_action('admin_menu', 'dz_add_custom_fields_menu');

// Register custom fields settings
function dz_register_custom_fields_settings() {
    register_setting('dz_custom_fields_settings', 'dz_custom_fields_options', 'dz_custom_fields_sanitize_options');
    
    add_settings_section(
        'dz_custom_fields_management',
        'Custom Fields Management',
        'dz_custom_fields_section_callback',
        'dz_custom_fields_settings'
    );
}
add_action('admin_init', 'dz_register_custom_fields_settings');

// Custom fields management page
function dz_custom_fields_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    // Handle form submission
    if (isset($_POST['add_custom_field']) && wp_verify_nonce($_POST['custom_field_nonce'], 'dz_add_custom_field')) {
        dz_add_custom_field();
    }
    
    if (isset($_POST['delete_custom_field']) && wp_verify_nonce($_POST['delete_field_nonce'], 'dz_delete_custom_field')) {
        dz_delete_custom_field();
    }
    
    $custom_fields = get_option('dz_custom_fields', array());
    
    ?>
    <div class="wrap">
        <h1>Event Custom Fields</h1>
        <p>Add custom fields with icons to your event cards. These fields will appear alongside the standard event information.</p>
        
        <div class="dz-custom-fields-container">
            <div class="dz-add-field-section">
                <h2>Add New Custom Field</h2>
                <form method="post" action="">
                    <?php wp_nonce_field('dz_add_custom_field', 'custom_field_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">Field Name</th>
                            <td>
                                <input type="text" name="field_name" class="regular-text" placeholder="e.g., Speaker, Organizer, Website" required />
                                <p class="description">The display name for this field (e.g., "Speaker", "Organizer")</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Field Key</th>
                            <td>
                                <input type="text" name="field_key" class="regular-text" placeholder="e.g., _dz_event_speaker" required />
                                <p class="description">The meta key for storing this data (must start with _dz_event_)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Icon</th>
                            <td>
                                <div class="dz-icon-selection">
                                    <div class="dz-icon-options">
                                        <label>
                                            <input type="radio" name="icon_type" value="fontawesome" checked />
                                            Font Awesome Icon
                                        </label>
                                        <label>
                                            <input type="radio" name="icon_type" value="svg" />
                                            Upload SVG
                                        </label>
                                        <label>
                                            <input type="radio" name="icon_type" value="none" />
                                            No Icon
                                        </label>
                                    </div>
                                    
                                    <div class="dz-fontawesome-icons" id="fontawesome-icons">
                                        <select name="field_icon" class="regular-text">
                                            <option value="fas fa-user">👤 User</option>
                                            <option value="fas fa-microphone">🎤 Microphone</option>
                                            <option value="fas fa-building">🏢 Building</option>
                                            <option value="fas fa-globe">🌐 Globe</option>
                                            <option value="fas fa-phone">📞 Phone</option>
                                            <option value="fas fa-envelope">✉️ Email</option>
                                            <option value="fas fa-map-marker-alt">📍 Location</option>
                                            <option value="fas fa-calendar-alt">📅 Calendar</option>
                                            <option value="fas fa-clock">🕐 Clock</option>
                                            <option value="fas fa-tag">🏷️ Tag</option>
                                            <option value="fas fa-users">👥 Users</option>
                                            <option value="fas fa-star">⭐ Star</option>
                                            <option value="fas fa-trophy">🏆 Trophy</option>
                                            <option value="fas fa-certificate">📜 Certificate</option>
                                            <option value="fas fa-graduation-cap">🎓 Graduation Cap</option>
                                            <option value="fas fa-briefcase">💼 Briefcase</option>
                                            <option value="fas fa-heart">❤️ Heart</option>
                                            <option value="fas fa-music">🎵 Music</option>
                                            <option value="fas fa-camera">📷 Camera</option>
                                            <option value="fas fa-video">📹 Video</option>
                                            <option value="fas fa-laptop">💻 Laptop</option>
                                            <option value="fas fa-mobile-alt">📱 Mobile</option>
                                            <option value="fas fa-wifi">📶 WiFi</option>
                                            <option value="fas fa-utensils">🍴 Utensils</option>
                                            <option value="fas fa-car">🚗 Car</option>
                                            <option value="fas fa-plane">✈️ Plane</option>
                                            <option value="fas fa-hotel">🏨 Hotel</option>
                                            <option value="fas fa-credit-card">💳 Credit Card</option>
                                            <option value="fas fa-gift">🎁 Gift</option>
                                            <option value="fas fa-ticket-alt">🎫 Ticket</option>
                                            <option value="fas fa-qrcode">📱 QR Code</option>
                                        </select>
                                    </div>
                                    
                                    <div class="dz-svg-upload" id="svg-upload" style="display: none;">
                                        <input type="file" name="field_svg" accept=".svg" class="regular-text" />
                                        <p class="description">Upload an SVG file for this field</p>
                                        <div class="dz-svg-preview" style="display: none;">
                                            <p>Preview:</p>
                                            <div class="dz-svg-preview-container"></div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Field Type</th>
                            <td>
                                <select name="field_type">
                                    <option value="text">Text</option>
                                    <option value="url">URL/Link</option>
                                    <option value="email">Email</option>
                                    <option value="phone">Phone</option>
                                    <option value="number">Number</option>
                                    <option value="textarea">Text Area</option>
                                </select>
                                <p class="description">The type of data this field will store</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Show on Cards</th>
                            <td>
                                <input type="checkbox" name="show_on_cards" value="1" checked />
                                <label>Display this field on event cards</label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Show on Single Event</th>
                            <td>
                                <input type="checkbox" name="show_on_single" value="1" checked />
                                <label>Display this field on single event pages</label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Required Field</th>
                            <td>
                                <input type="checkbox" name="required_field" value="1" />
                                <label>Make this field required when creating events</label>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button('Add Custom Field', 'primary', 'add_custom_field'); ?>
                </form>
            </div>
            
            <div class="dz-existing-fields-section">
                <h2>Existing Custom Fields</h2>
                <?php if (!empty($custom_fields)) : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Field Name</th>
                                <th>Field Key</th>
                                <th>Icon</th>
                                <th>Icon Type</th>
                                <th>Type</th>
                                <th>Show on Cards</th>
                                <th>Show on Single</th>
                                <th>Required</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($custom_fields as $key => $field) : ?>
                                <tr>
                                    <td><strong><?php echo esc_html($field['name']); ?></strong></td>
                                    <td><code><?php echo esc_html($field['key']); ?></code></td>
                                    <td>
                                        <?php if (isset($field['icon_type']) && $field['icon_type'] === 'svg') : ?>
                                            <img src="<?php echo esc_url($field['icon']); ?>" style="width: 20px; height: 20px;" alt="Custom SVG" />
                                        <?php elseif (isset($field['icon_type']) && $field['icon_type'] === 'none') : ?>
                                            <span style="color: #999; font-style: italic;">No Icon</span>
                                        <?php else : ?>
                                            <i class="<?php echo esc_attr($field['icon']); ?>"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo isset($field['icon_type']) ? esc_html(ucfirst($field['icon_type'])) : 'FontAwesome'; ?></td>
                                    <td><?php echo esc_html(ucfirst($field['type'])); ?></td>
                                    <td><?php echo $field['show_on_cards'] ? '✅' : '❌'; ?></td>
                                    <td><?php echo $field['show_on_single'] ? '✅' : '❌'; ?></td>
                                    <td><?php echo $field['required'] ? '✅' : '❌'; ?></td>
                                    <td>
                                        <form method="post" style="display: inline;">
                                            <?php wp_nonce_field('dz_delete_custom_field', 'delete_field_nonce'); ?>
                                            <input type="hidden" name="field_key" value="<?php echo esc_attr($key); ?>" />
                                            <input type="submit" name="delete_custom_field" value="Delete" class="button button-small" onclick="return confirm('Are you sure you want to delete this custom field?');" />
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p>No custom fields have been created yet. Add your first custom field above.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="dz-custom-fields-help">
            <h2>How to Use Custom Fields</h2>
            <ol>
                <li><strong>Add Custom Fields:</strong> Use the form above to add new fields with icons</li>
                <li><strong>Edit Events:</strong> When editing events, you'll see your custom fields in the Event Details meta box</li>
                <li><strong>Display on Cards:</strong> Custom fields will appear on event cards with their icons</li>
                <li><strong>Field Keys:</strong> Must start with "_dz_event_" (e.g., "_dz_event_speaker")</li>
                <li><strong>Icons:</strong> Choose from Font Awesome icons for visual appeal</li>
            </ol>
            
            <h3>Example Custom Fields:</h3>
            <ul>
                <li><strong>Speaker:</strong> _dz_event_speaker (👤 User icon)</li>
                <li><strong>Organizer:</strong> _dz_event_organizer (🏢 Building icon)</li>
                <li><strong>Website:</strong> _dz_event_website (🌐 Globe icon)</li>
                <li><strong>Contact Person:</strong> _dz_event_contact_person (📞 Phone icon)</li>
                <li><strong>Registration Deadline:</strong> _dz_event_registration_deadline (📅 Calendar icon)</li>
            </ul>
        </div>
    </div>
    
    <style>
    .dz-custom-fields-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin: 20px 0;
    }
    
    .dz-add-field-section,
    .dz-existing-fields-section {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
    }
    
    .dz-custom-fields-help {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        margin-top: 20px;
    }
    
    .dz-custom-fields-help ul,
    .dz-custom-fields-help ol {
        margin-left: 20px;
    }
    
    .dz-custom-fields-help li {
        margin-bottom: 5px;
    }
    
    .dz-icon-selection {
        margin-top: 10px;
    }
    
    .dz-icon-options {
        margin-bottom: 15px;
    }
    
    .dz-icon-options label {
        margin-right: 20px;
        font-weight: 500;
    }
    
    .dz-svg-upload {
        margin-top: 15px;
    }
    
    .dz-svg-preview-container {
        width: 32px;
        height: 32px;
        border: 1px solid #ddd;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f9f9f9;
        margin-top: 10px;
    }
    
    .dz-svg-preview-container svg {
        width: 20px;
        height: 20px;
        fill: #666;
    }
    
    @media (max-width: 768px) {
        .dz-custom-fields-container {
            grid-template-columns: 1fr;
        }
    }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        // Handle icon type switching
        $('input[name="icon_type"]').on('change', function() {
            var iconType = $(this).val();
            
            if (iconType === 'fontawesome') {
                $('#fontawesome-icons').show();
                $('#svg-upload').hide();
            } else if (iconType === 'svg') {
                $('#fontawesome-icons').hide();
                $('#svg-upload').show();
            } else if (iconType === 'none') {
                $('#fontawesome-icons').hide();
                $('#svg-upload').hide();
            }
        });
        
        // Handle SVG file upload and preview
        $('input[name="field_svg"]').on('change', function() {
            var file = this.files[0];
            if (file && file.type === 'image/svg+xml') {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('.dz-svg-preview-container').html(e.target.result);
                    $('.dz-svg-preview').show();
                };
                reader.readAsText(file);
            }
        });
    });
    </script>
    <?php
}

// Add custom field function
function dz_add_custom_field() {
    $field_name = sanitize_text_field($_POST['field_name']);
    $field_key = sanitize_text_field($_POST['field_key']);
    $field_type = sanitize_text_field($_POST['field_type']);
    $show_on_cards = isset($_POST['show_on_cards']) ? true : false;
    $show_on_single = isset($_POST['show_on_single']) ? true : false;
    $required = isset($_POST['required_field']) ? true : false;
    
    // Handle icon based on type
    $icon_type = sanitize_text_field($_POST['icon_type']);
    $field_icon = '';
    
    if ($icon_type === 'fontawesome') {
        $field_icon = sanitize_text_field($_POST['field_icon']);
    } elseif ($icon_type === 'svg') {
        // Handle SVG upload
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        $uploadedfile = $_FILES['field_svg'];
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            $field_icon = $movefile['url'];
        } else {
            add_action('admin_notices', function() use ($movefile) {
                echo '<div class="notice notice-error"><p>SVG upload failed: ' . esc_html($movefile['error']) . '</p></div>';
            });
            return;
        }
    } elseif ($icon_type === 'none') {
        $field_icon = '';
    }
    
    // Validate field key
    if (!str_starts_with($field_key, '_dz_event_')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>Field key must start with "_dz_event_"</p></div>';
        });
        return;
    }
    
    $custom_fields = get_option('dz_custom_fields', array());
    
    $custom_fields[$field_key] = array(
        'name' => $field_name,
        'key' => $field_key,
        'icon' => $field_icon,
        'icon_type' => $icon_type,
        'type' => $field_type,
        'show_on_cards' => $show_on_cards,
        'show_on_single' => $show_on_single,
        'required' => $required
    );
    
    update_option('dz_custom_fields', $custom_fields);
    
    add_action('admin_notices', function() use ($field_name) {
        echo '<div class="notice notice-success"><p>Custom field "' . esc_html($field_name) . '" added successfully!</p></div>';
    });
}

// Delete custom field function
function dz_delete_custom_field() {
    $field_key = sanitize_text_field($_POST['field_key']);
    
    $custom_fields = get_option('dz_custom_fields', array());
    
    if (isset($custom_fields[$field_key])) {
        $field_name = $custom_fields[$field_key]['name'];
        unset($custom_fields[$field_key]);
        update_option('dz_custom_fields', $custom_fields);
        
        add_action('admin_notices', function() use ($field_name) {
            echo '<div class="notice notice-success"><p>Custom field "' . esc_html($field_name) . '" deleted successfully!</p></div>';
        });
    }
}

// Add custom fields to event meta box
function dz_add_custom_fields_to_meta_box() {
    $custom_fields = get_option('dz_custom_fields', array());
    
    if (empty($custom_fields)) {
        return;
    }
    
    global $post;
    
    echo '<tr><td colspan="2"><h4>Custom Fields</h4></td></tr>';
    
    foreach ($custom_fields as $field_key => $field) {
        $value = get_post_meta($post->ID, $field_key, true);
        $required = $field['required'] ? ' required' : '';
        
        echo '<tr>';
        echo '<th><label for="' . esc_attr($field_key) . '">' . esc_html($field['name']) . '</label></th>';
        echo '<td>';
        
        switch ($field['type']) {
            case 'textarea':
                echo '<textarea id="' . esc_attr($field_key) . '" name="' . esc_attr($field_key) . '" rows="3" cols="50"' . $required . '>' . esc_textarea($value) . '</textarea>';
                break;
            case 'number':
                echo '<input type="number" id="' . esc_attr($field_key) . '" name="' . esc_attr($field_key) . '" value="' . esc_attr($value) . '"' . $required . ' />';
                break;
            case 'email':
                echo '<input type="email" id="' . esc_attr($field_key) . '" name="' . esc_attr($field_key) . '" value="' . esc_attr($value) . '"' . $required . ' />';
                break;
            case 'url':
                echo '<input type="url" id="' . esc_attr($field_key) . '" name="' . esc_attr($field_key) . '" value="' . esc_attr($value) . '"' . $required . ' />';
                break;
            default:
                echo '<input type="text" id="' . esc_attr($field_key) . '" name="' . esc_attr($field_key) . '" value="' . esc_attr($value) . '" class="regular-text"' . $required . ' />';
                break;
        }
        
        echo '</td>';
        echo '</tr>';
    }
}
add_action('dz_event_details_meta_box', 'dz_add_custom_fields_to_meta_box');

// Save custom fields
function dz_save_custom_fields($post_id) {
    $custom_fields = get_option('dz_custom_fields', array());
    
    foreach ($custom_fields as $field_key => $field) {
        if (isset($_POST[$field_key])) {
            $value = '';
            
            switch ($field['type']) {
                case 'email':
                    $value = sanitize_email($_POST[$field_key]);
                    break;
                case 'url':
                    $value = esc_url_raw($_POST[$field_key]);
                    break;
                case 'number':
                    $value = intval($_POST[$field_key]);
                    break;
                case 'textarea':
                    $value = sanitize_textarea_field($_POST[$field_key]);
                    break;
                default:
                    $value = sanitize_text_field($_POST[$field_key]);
                    break;
            }
            
            update_post_meta($post_id, $field_key, $value);
        }
    }
}
add_action('save_post', 'dz_save_custom_fields');

// Section callback
function dz_custom_fields_section_callback() {
    echo '<p>Manage custom fields for your events. These fields will appear in the event editor and can be displayed on event cards.</p>';
}

// Sanitize options
function dz_custom_fields_sanitize_options($input) {
    // This function can be expanded if needed for settings validation
    return $input;
}
