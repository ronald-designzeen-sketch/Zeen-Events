<?php
/**
 * Event Card Display Settings
 * Allows customization of event cards without coding
 */

// Add card settings menu
function dz_add_card_settings_menu() {
    add_submenu_page(
        'edit.php?post_type=dz_event',
        'Card Display Settings',
        'Card Settings',
        'manage_options',
        'dz-card-settings',
        'dz_card_settings_page_new'
    );
}
add_action('admin_menu', 'dz_add_card_settings_menu');

// Register card settings
function dz_register_card_settings() {
    register_setting('dz_card_settings', 'dz_card_options', 'dz_card_sanitize_options');
    
    // Card Layout Section
    add_settings_section(
        'dz_card_layout',
        'Card Layout & Design',
        'dz_card_layout_section_callback',
        'dz_card_settings'
    );
    
    // Card Style Section
    add_settings_section(
        'dz_card_style',
        'Card Styling',
        'dz_card_style_section_callback',
        'dz_card_settings'
    );
    
    // Display Fields Section
    add_settings_section(
        'dz_card_fields',
        'Display Fields',
        'dz_card_fields_section_callback',
        'dz_card_settings'
    );
    
    // Button Settings Section
    add_settings_section(
        'dz_card_buttons',
        'Button Settings',
        'dz_card_buttons_section_callback',
        'dz_card_settings'
    );
    
    // Add all the fields
    dz_add_card_settings_fields();
}
add_action('admin_init', 'dz_register_card_settings');

function dz_add_card_settings_fields() {
    // Layout Fields
    add_settings_field('card_layout', 'Card Layout', 'dz_card_layout_callback', 'dz_card_settings', 'dz_card_layout');
    add_settings_field('image_size', 'Image Size', 'dz_card_image_size_callback', 'dz_card_settings', 'dz_card_layout');
    add_settings_field('image_position', 'Image Position', 'dz_card_image_position_callback', 'dz_card_settings', 'dz_card_layout');
    add_settings_field('card_columns', 'Columns per Row', 'dz_card_columns_callback', 'dz_card_settings', 'dz_card_layout');
    
    // Style Fields
    add_settings_field('card_border_radius', 'Border Radius', 'dz_card_border_radius_callback', 'dz_card_settings', 'dz_card_style');
    add_settings_field('card_shadow', 'Card Shadow', 'dz_card_shadow_callback', 'dz_card_settings', 'dz_card_style');
    add_settings_field('card_hover_effect', 'Hover Effect', 'dz_card_hover_effect_callback', 'dz_card_settings', 'dz_card_style');
    add_settings_field('primary_color', 'Primary Color', 'dz_card_primary_color_callback', 'dz_card_settings', 'dz_card_style');
    add_settings_field('text_color', 'Text Color', 'dz_card_text_color_callback', 'dz_card_settings', 'dz_card_style');
    add_settings_field('background_color', 'Background Color', 'dz_card_background_color_callback', 'dz_card_settings', 'dz_card_style');
    
    // Display Fields
    add_settings_field('show_title', 'Show Title', 'dz_card_show_title_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('show_excerpt', 'Show Excerpt', 'dz_card_show_excerpt_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('show_date', 'Show Date', 'dz_card_show_date_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('show_time', 'Show Time', 'dz_card_show_time_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('show_location', 'Show Location', 'dz_card_show_location_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('show_price', 'Show Price', 'dz_card_show_price_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('show_category', 'Show Category', 'dz_card_show_category_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('show_capacity', 'Show Capacity', 'dz_card_show_capacity_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('show_custom_fields', 'Show Custom Fields', 'dz_card_show_custom_fields_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('show_category_tag', 'Show Category Tag', 'dz_card_show_category_tag_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('category_tag_position', 'Category Tag Position', 'dz_card_category_tag_position_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('show_status_badge', 'Show Status Badge', 'dz_card_show_status_badge_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('show_featured_badge', 'Show Featured Badge', 'dz_card_show_featured_badge_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('show_icons', 'Show Icons', 'dz_card_show_icons_callback', 'dz_card_settings', 'dz_card_fields');
    
    // Icon Color Fields
    add_settings_field('icon_date_color', 'Date Icon Color', 'dz_card_icon_date_color_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('icon_time_color', 'Time Icon Color', 'dz_card_icon_time_color_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('icon_location_color', 'Location Icon Color', 'dz_card_icon_location_color_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('icon_price_color', 'Price Icon Color', 'dz_card_icon_price_color_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('icon_category_color', 'Category Icon Color', 'dz_card_icon_category_color_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('icon_capacity_color', 'Capacity Icon Color', 'dz_card_icon_capacity_color_callback', 'dz_card_settings', 'dz_card_fields');
    
    // Search Color Fields
    add_settings_field('search_background_color', 'Search Background Color', 'dz_card_search_background_color_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('search_border_color', 'Search Border Color', 'dz_card_search_border_color_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('search_button_color', 'Search Button Color', 'dz_card_search_button_color_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('search_text_color', 'Search Text Color', 'dz_card_search_text_color_callback', 'dz_card_settings', 'dz_card_fields');
    
    // Past Events Fields
    add_settings_field('past_events_handling', 'Past Events Handling', 'dz_card_past_events_handling_callback', 'dz_card_settings', 'dz_card_fields');
    add_settings_field('past_events_show_days', 'Show Past Events For (Days)', 'dz_card_past_events_show_days_callback', 'dz_card_settings', 'dz_card_fields');
    
    // Button Fields
    add_settings_field('button_text', 'Button Text', 'dz_card_button_text_callback', 'dz_card_settings', 'dz_card_buttons');
    add_settings_field('button_style', 'Button Style', 'dz_card_button_style_callback', 'dz_card_settings', 'dz_card_buttons');
    add_settings_field('button_color', 'Button Color', 'dz_card_button_color_callback', 'dz_card_settings', 'dz_card_buttons');
    add_settings_field('button_text_color', 'Button Text Color', 'dz_card_button_text_color_callback', 'dz_card_settings', 'dz_card_buttons');
    add_settings_field('button_size', 'Button Size', 'dz_card_button_size_callback', 'dz_card_settings', 'dz_card_buttons');
}

// Settings page
function dz_card_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    $options = get_option('dz_card_options', dz_get_default_card_options());
    ?>
    <div class="wrap">
        <h1>Event Card Display Settings</h1>
        <p>Customize how your event cards appear on the events page and in shortcodes. Changes will be applied immediately.</p>
        
        <div class="dz-card-preview">
            <h2>Live Preview</h2>
            <div class="dz-preview-container">
                <div class="dz-preview-card" id="dz-preview-card">
                    <!-- Preview will be generated by JavaScript -->
                </div>
            </div>
        </div>
        
        <form method="post" action="options.php" id="dz-card-settings-form">
            <?php
            settings_fields('dz_card_settings');
            do_settings_sections('dz_card_settings');
            submit_button('Save Card Settings');
            ?>
        </form>
        
        <div class="dz-card-shortcode-help">
            <h2>Shortcode Usage</h2>
            <p>Use these shortcodes to display events with your custom card settings:</p>
            <code>[dz_events]</code> - Display events with current card settings<br>
            <code>[dz_events layout="grid" count="6"]</code> - Override specific settings<br>
            <code>[dz_events category="conference"]</code> - Filter by category
        </div>
    </div>
    
    <style>
    .dz-card-preview {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        margin: 20px 0;
    }
    
    .dz-preview-container {
        display: flex;
        justify-content: center;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-top: 15px;
    }
    
    .dz-preview-card {
        max-width: 300px;
        width: 100%;
    }
    
    .dz-card-shortcode-help {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        margin-top: 20px;
    }
    
    .dz-card-shortcode-help code {
        background: #f1f1f1;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: monospace;
    }
    
    .form-table th {
        width: 200px;
    }
    
    .dz-color-picker {
        width: 60px;
        height: 30px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    
    .dz-range-slider {
        width: 200px;
    }
    
    .dz-preview-field {
        margin: 5px 0;
        font-size: 14px;
    }
    
    .dz-preview-field i {
        margin-right: 8px;
        color: #666;
    }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        // Live preview functionality
        function updatePreview() {
            var formData = $('#dz-card-settings-form').serializeArray();
            var options = {};
            
            $.each(formData, function(i, field) {
                if (field.name.startsWith('dz_card_options[')) {
                    var key = field.name.replace('dz_card_options[', '').replace(']', '');
                    options[key] = field.value;
                }
            });
            
            // Generate preview HTML
            var previewHtml = generatePreviewHTML(options);
            $('#dz-preview-card').html(previewHtml);
        }
        
        function generatePreviewHTML(options) {
            var html = '<div class="dz-event-card-preview" style="';
            html += 'border-radius: ' + (options.card_border_radius || '12') + 'px;';
            html += 'background: ' + (options.background_color || '#fff') + ';';
            html += 'color: ' + (options.text_color || '#333') + ';';
            html += 'box-shadow: ' + (options.card_shadow === 'none' ? 'none' : '0 4px 15px rgba(0,0,0,0.1)') + ';';
            html += 'overflow: hidden; border: 1px solid #eee;">';
            
            // Image
            html += '<div class="dz-event-thumb-preview" style="';
            html += 'height: ' + (options.image_size === 'small' ? '150px' : options.image_size === 'large' ? '250px' : '200px') + ';';
            html += 'background: linear-gradient(45deg, #f0f0f0, #e0e0e0);';
            html += 'display: flex; align-items: center; justify-content: center;';
            html += 'color: #999; font-size: 14px; position: relative;">';
            html += 'Event Image';
            
            // Add category tag preview
            if (options.show_category_tag !== 'no') {
                var tagPosition = options.category_tag_position === 'top-right' ? 'top: 10px; right: 10px;' : 'top: 10px; left: 10px;';
                html += '<span style="position: absolute; ' + tagPosition + ' background: rgba(0,0,0,0.7); color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Conference</span>';
            }
            
            html += '</div>';
            
            // Content
            html += '<div class="dz-event-content-preview" style="padding: 20px;">';
            
            // Title
            if (options.show_title !== 'no') {
                html += '<h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600;">Sample Event Title</h3>';
            }
            
            // Fields
            if (options.show_date !== 'no') {
                html += '<div class="dz-preview-field"><i class="fas fa-calendar"></i> Dec 25, 2024</div>';
            }
            
            if (options.show_time !== 'no') {
                html += '<div class="dz-preview-field"><i class="fas fa-clock"></i> 2:00 PM - 4:00 PM</div>';
            }
            
            if (options.show_location !== 'no') {
                html += '<div class="dz-preview-field"><i class="fas fa-map-marker"></i> Convention Center</div>';
            }
            
            if (options.show_price !== 'no') {
                html += '<div class="dz-preview-field"><i class="fas fa-tag"></i> $50</div>';
            }
            
            if (options.show_category !== 'no') {
                html += '<div class="dz-preview-field"><i class="fas fa-folder"></i> Conference</div>';
            }
            
            if (options.show_custom_fields !== 'no') {
                html += '<div class="dz-preview-field"><i class="fas fa-user"></i> John Doe (Speaker)</div>';
                html += '<div class="dz-preview-field"><i class="fas fa-globe"></i> example.com</div>';
            }
            
            if (options.show_excerpt !== 'no') {
                html += '<p style="margin: 15px 0; color: #666; font-size: 14px; line-height: 1.4;">This is a sample event description that shows how the excerpt will appear on your event cards.</p>';
            }
            
            // Button
            html += '<div style="margin-top: 20px;">';
            html += '<a href="#" style="';
            html += 'display: inline-block; padding: ' + (options.button_size === 'small' ? '8px 16px' : options.button_size === 'large' ? '14px 28px' : '10px 20px') + ';';
            html += 'background: ' + (options.button_color || '#0073aa') + ';';
            html += 'color: ' + (options.button_text_color || '#fff') + ';';
            html += 'text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px;';
            html += 'border: none; cursor: pointer;">';
            html += (options.button_text || 'View Details');
            html += '</a>';
            html += '</div>';
            
            html += '</div></div>';
            
            return html;
        }
        
        // Update preview on form change
        $('#dz-card-settings-form input, #dz-card-settings-form select').on('change input', function() {
            updatePreview();
        });
        
        // Initial preview
        updatePreview();
    });
    </script>
    <?php
}

// Section callbacks
function dz_card_layout_section_callback() {
    echo '<p>Configure the basic layout and structure of your event cards.</p>';
}

function dz_card_style_section_callback() {
    echo '<p>Customize the visual appearance and colors of your event cards.</p>';
}

function dz_card_fields_section_callback() {
    echo '<p>Choose which information to display on your event cards.</p>';
}

function dz_card_buttons_section_callback() {
    echo '<p>Customize the appearance and text of the action button.</p>';
}

// Field callbacks
function dz_card_layout_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['card_layout'] ?? 'vertical';
    ?>
    <select name="dz_card_options[card_layout]">
        <option value="vertical" <?php selected($value, 'vertical'); ?>>Vertical (Image on top)</option>
        <option value="horizontal" <?php selected($value, 'horizontal'); ?>>Horizontal (Image on left)</option>
        <option value="minimal" <?php selected($value, 'minimal'); ?>>Minimal (Text only)</option>
    </select>
    <p class="description">Choose the basic layout structure for your event cards.</p>
    <?php
}

function dz_card_image_size_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['image_size'] ?? 'medium';
    ?>
    <select name="dz_card_options[image_size]">
        <option value="small" <?php selected($value, 'small'); ?>>Small (150px)</option>
        <option value="medium" <?php selected($value, 'medium'); ?>>Medium (200px)</option>
        <option value="large" <?php selected($value, 'large'); ?>>Large (250px)</option>
        <option value="auto" <?php selected($value, 'auto'); ?>>Auto (Responsive)</option>
    </select>
    <p class="description">Set the height of event images. All images will be cropped to the same size.</p>
    <?php
}

function dz_card_image_position_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['image_position'] ?? 'top';
    ?>
    <select name="dz_card_options[image_position]">
        <option value="top" <?php selected($value, 'top'); ?>>Top</option>
        <option value="left" <?php selected($value, 'left'); ?>>Left</option>
        <option value="right" <?php selected($value, 'right'); ?>>Right</option>
    </select>
    <p class="description">Position of the image relative to the content (for horizontal layout).</p>
    <?php
}

function dz_card_columns_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['card_columns'] ?? '3';
    ?>
    <select name="dz_card_options[card_columns]">
        <option value="1" <?php selected($value, '1'); ?>>1 Column</option>
        <option value="2" <?php selected($value, '2'); ?>>2 Columns</option>
        <option value="3" <?php selected($value, '3'); ?>>3 Columns</option>
        <option value="4" <?php selected($value, '4'); ?>>4 Columns</option>
        <option value="auto" <?php selected($value, 'auto'); ?>>Auto (Responsive)</option>
    </select>
    <p class="description">Number of cards per row on desktop screens.</p>
    <?php
}

function dz_card_border_radius_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['card_border_radius'] ?? '12';
    ?>
    <input type="range" name="dz_card_options[card_border_radius]" min="0" max="30" value="<?php echo esc_attr($value); ?>" class="dz-range-slider" />
    <span><?php echo esc_html($value); ?>px</span>
    <p class="description">Roundness of the card corners.</p>
    <?php
}

function dz_card_shadow_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['card_shadow'] ?? 'medium';
    ?>
    <select name="dz_card_options[card_shadow]">
        <option value="none" <?php selected($value, 'none'); ?>>No Shadow</option>
        <option value="light" <?php selected($value, 'light'); ?>>Light Shadow</option>
        <option value="medium" <?php selected($value, 'medium'); ?>>Medium Shadow</option>
        <option value="heavy" <?php selected($value, 'heavy'); ?>>Heavy Shadow</option>
    </select>
    <p class="description">Depth effect for the cards.</p>
    <?php
}

function dz_card_hover_effect_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['card_hover_effect'] ?? 'lift';
    ?>
    <select name="dz_card_options[card_hover_effect]">
        <option value="none" <?php selected($value, 'none'); ?>>No Effect</option>
        <option value="lift" <?php selected($value, 'lift'); ?>>Lift Up</option>
        <option value="scale" <?php selected($value, 'scale'); ?>>Scale Up</option>
        <option value="glow" <?php selected($value, 'glow'); ?>>Glow Effect</option>
    </select>
    <p class="description">Animation when hovering over cards.</p>
    <?php
}

function dz_card_primary_color_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['primary_color'] ?? '#0073aa';
    ?>
    <input type="color" name="dz_card_options[primary_color]" value="<?php echo esc_attr($value); ?>" class="dz-color-picker" />
    <p class="description">Primary color for accents and highlights.</p>
    <?php
}

function dz_card_text_color_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['text_color'] ?? '#333333';
    ?>
    <input type="color" name="dz_card_options[text_color]" value="<?php echo esc_attr($value); ?>" class="dz-color-picker" />
    <p class="description">Color for text content.</p>
    <?php
}

function dz_card_background_color_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['background_color'] ?? '#ffffff';
    ?>
    <input type="color" name="dz_card_options[background_color]" value="<?php echo esc_attr($value); ?>" class="dz-color-picker" />
    <p class="description">Background color of the cards.</p>
    <?php
}

// Display field callbacks
function dz_card_show_title_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['show_title'] ?? 'yes';
    ?>
    <select name="dz_card_options[show_title]">
        <option value="yes" <?php selected($value, 'yes'); ?>>Show</option>
        <option value="no" <?php selected($value, 'no'); ?>>Hide</option>
    </select>
    <?php
}

function dz_card_show_excerpt_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['show_excerpt'] ?? 'yes';
    ?>
    <select name="dz_card_options[show_excerpt]">
        <option value="yes" <?php selected($value, 'yes'); ?>>Show</option>
        <option value="no" <?php selected($value, 'no'); ?>>Hide</option>
    </select>
    <?php
}

function dz_card_show_date_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['show_date'] ?? 'yes';
    ?>
    <select name="dz_card_options[show_date]">
        <option value="yes" <?php selected($value, 'yes'); ?>>Show</option>
        <option value="no" <?php selected($value, 'no'); ?>>Hide</option>
    </select>
    <?php
}

function dz_card_show_time_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['show_time'] ?? 'yes';
    ?>
    <select name="dz_card_options[show_time]">
        <option value="yes" <?php selected($value, 'yes'); ?>>Show</option>
        <option value="no" <?php selected($value, 'no'); ?>>Hide</option>
    </select>
    <?php
}

function dz_card_show_location_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['show_location'] ?? 'yes';
    ?>
    <select name="dz_card_options[show_location]">
        <option value="yes" <?php selected($value, 'yes'); ?>>Show</option>
        <option value="no" <?php selected($value, 'no'); ?>>Hide</option>
    </select>
    <?php
}

function dz_card_show_price_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['show_price'] ?? 'yes';
    ?>
    <select name="dz_card_options[show_price]">
        <option value="yes" <?php selected($value, 'yes'); ?>>Show</option>
        <option value="no" <?php selected($value, 'no'); ?>>Hide</option>
    </select>
    <?php
}

function dz_card_show_category_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['show_category'] ?? 'yes';
    ?>
    <select name="dz_card_options[show_category]">
        <option value="yes" <?php selected($value, 'yes'); ?>>Show</option>
        <option value="no" <?php selected($value, 'no'); ?>>Hide</option>
    </select>
    <?php
}

function dz_card_show_capacity_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['show_capacity'] ?? 'no';
    ?>
    <select name="dz_card_options[show_capacity]">
        <option value="yes" <?php selected($value, 'yes'); ?>>Show</option>
        <option value="no" <?php selected($value, 'no'); ?>>Hide</option>
    </select>
    <?php
}

function dz_card_show_custom_fields_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['show_custom_fields'] ?? 'yes';
    ?>
    <select name="dz_card_options[show_custom_fields]">
        <option value="yes" <?php selected($value, 'yes'); ?>>Show</option>
        <option value="no" <?php selected($value, 'no'); ?>>Hide</option>
    </select>
    <p class="description">Display custom fields that have been configured to show on cards.</p>
    <?php
}

function dz_card_show_category_tag_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['show_category_tag'] ?? 'yes';
    ?>
    <select name="dz_card_options[show_category_tag]">
        <option value="yes" <?php selected($value, 'yes'); ?>>Show</option>
        <option value="no" <?php selected($value, 'no'); ?>>Hide</option>
    </select>
    <p class="description">Display category as a tag overlay on the event image.</p>
    <?php
}

function dz_card_category_tag_position_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['category_tag_position'] ?? 'top-left';
    ?>
    <select name="dz_card_options[category_tag_position]">
        <option value="top-left" <?php selected($value, 'top-left'); ?>>Top Left</option>
        <option value="top-right" <?php selected($value, 'top-right'); ?>>Top Right</option>
    </select>
    <p class="description">Position of the category tag on the event image.</p>
    <?php
}

function dz_card_show_status_badge_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['show_status_badge'] ?? 'yes';
    ?>
    <select name="dz_card_options[show_status_badge]">
        <option value="yes" <?php selected($value, 'yes'); ?>>Show</option>
        <option value="no" <?php selected($value, 'no'); ?>>Hide</option>
    </select>
    <?php
}

function dz_card_show_featured_badge_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['show_featured_badge'] ?? 'yes';
    ?>
    <select name="dz_card_options[show_featured_badge]">
        <option value="yes" <?php selected($value, 'yes'); ?>>Show</option>
        <option value="no" <?php selected($value, 'no'); ?>>Hide</option>
    </select>
    <?php
}

function dz_card_show_icons_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['show_icons'] ?? 'yes';
    ?>
    <select name="dz_card_options[show_icons]">
        <option value="yes" <?php selected($value, 'yes'); ?>>Show Icons</option>
        <option value="no" <?php selected($value, 'no'); ?>>Hide Icons</option>
    </select>
    <p class="description">Toggle icons on/off for all event fields. When disabled, text will be left-aligned without gaps.</p>
    <?php
}

// Icon color callback functions
function dz_card_icon_date_color_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['icon_date_color'] ?? '#0073aa';
    ?>
    <input type="color" name="dz_card_options[icon_date_color]" value="<?php echo esc_attr($value); ?>" class="dz-color-picker" />
    <p class="description">Color for the calendar icon (Date field).</p>
    <?php
}

function dz_card_icon_time_color_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['icon_time_color'] ?? '#ffc107';
    ?>
    <input type="color" name="dz_card_options[icon_time_color]" value="<?php echo esc_attr($value); ?>" class="dz-color-picker" />
    <p class="description">Color for the clock icon (Time field).</p>
    <?php
}

function dz_card_icon_location_color_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['icon_location_color'] ?? '#6f42c1';
    ?>
    <input type="color" name="dz_card_options[icon_location_color]" value="<?php echo esc_attr($value); ?>" class="dz-color-picker" />
    <p class="description">Color for the map marker icon (Location field).</p>
    <?php
}

function dz_card_icon_price_color_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['icon_price_color'] ?? '#17a2b8';
    ?>
    <input type="color" name="dz_card_options[icon_price_color]" value="<?php echo esc_attr($value); ?>" class="dz-color-picker" />
    <p class="description">Color for the tag icon (Price field).</p>
    <?php
}

function dz_card_icon_category_color_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['icon_category_color'] ?? '#fd7e14';
    ?>
    <input type="color" name="dz_card_options[icon_category_color]" value="<?php echo esc_attr($value); ?>" class="dz-color-picker" />
    <p class="description">Color for the folder icon (Category field).</p>
    <?php
}

function dz_card_icon_capacity_color_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['icon_capacity_color'] ?? '#fd7e14';
    ?>
    <input type="color" name="dz_card_options[icon_capacity_color]" value="<?php echo esc_attr($value); ?>" class="dz-color-picker" />
    <p class="description">Color for the users icon (Capacity field).</p>
    <?php
}

// Search color callback functions
function dz_card_search_background_color_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['search_background_color'] ?? '#f8f9fa';
    ?>
    <input type="color" name="dz_card_options[search_background_color]" value="<?php echo esc_attr($value); ?>" class="dz-color-picker" />
    <p class="description">Background color for the search container.</p>
    <?php
}

function dz_card_search_border_color_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['search_border_color'] ?? '#e9ecef';
    ?>
    <input type="color" name="dz_card_options[search_border_color]" value="<?php echo esc_attr($value); ?>" class="dz-color-picker" />
    <p class="description">Border color for search inputs and selects.</p>
    <?php
}

function dz_card_search_button_color_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['search_button_color'] ?? '#0073aa';
    ?>
    <input type="color" name="dz_card_options[search_button_color]" value="<?php echo esc_attr($value); ?>" class="dz-color-picker" />
    <p class="description">Background color for search and clear buttons.</p>
    <?php
}

function dz_card_search_text_color_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['search_text_color'] ?? '#333333';
    ?>
    <input type="color" name="dz_card_options[search_text_color]" value="<?php echo esc_attr($value); ?>" class="dz-color-picker" />
    <p class="description">Text color for search inputs and labels.</p>
    <?php
}

function dz_card_past_events_handling_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['past_events_handling'] ?? 'hide';
    ?>
    <select name="dz_card_options[past_events_handling]">
        <option value="hide" <?php selected($value, 'hide'); ?>>Hide Past Events</option>
        <option value="show" <?php selected($value, 'show'); ?>>Show Past Events</option>
        <option value="show_with_status" <?php selected($value, 'show_with_status'); ?>>Show with "Past Event" Status</option>
    </select>
    <p class="description">How to handle events that have already passed.</p>
    <?php
}

function dz_card_past_events_show_days_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['past_events_show_days'] ?? '7';
    ?>
    <input type="number" name="dz_card_options[past_events_show_days]" value="<?php echo esc_attr($value); ?>" min="0" max="365" />
    <p class="description">Number of days to show past events (0 = hide immediately, 365 = show for a year).</p>
    <?php
}

// Button field callbacks
function dz_card_button_text_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['button_text'] ?? 'View Details';
    ?>
    <input type="text" name="dz_card_options[button_text]" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <p class="description">Text displayed on the action button.</p>
    <?php
}

function dz_card_button_style_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['button_style'] ?? 'filled';
    ?>
    <select name="dz_card_options[button_style]">
        <option value="filled" <?php selected($value, 'filled'); ?>>Filled</option>
        <option value="outline" <?php selected($value, 'outline'); ?>>Outline</option>
        <option value="minimal" <?php selected($value, 'minimal'); ?>>Minimal</option>
    </select>
    <p class="description">Visual style of the button.</p>
    <?php
}

function dz_card_button_color_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['button_color'] ?? '#0073aa';
    ?>
    <input type="color" name="dz_card_options[button_color]" value="<?php echo esc_attr($value); ?>" class="dz-color-picker" />
    <p class="description">Background color of the button.</p>
    <?php
}

function dz_card_button_text_color_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['button_text_color'] ?? '#ffffff';
    ?>
    <input type="color" name="dz_card_options[button_text_color]" value="<?php echo esc_attr($value); ?>" class="dz-color-picker" />
    <p class="description">Text color of the button.</p>
    <?php
}

function dz_card_button_size_callback() {
    $options = get_option('dz_card_options', dz_get_default_card_options());
    $value = $options['button_size'] ?? 'medium';
    ?>
    <select name="dz_card_options[button_size]">
        <option value="small" <?php selected($value, 'small'); ?>>Small</option>
        <option value="medium" <?php selected($value, 'medium'); ?>>Medium</option>
        <option value="large" <?php selected($value, 'large'); ?>>Large</option>
    </select>
    <p class="description">Size of the button.</p>
    <?php
}

// Default options
function dz_get_default_card_options() {
    return array(
        'card_layout' => 'vertical',
        'image_size' => 'medium',
        'image_position' => 'top',
        'card_columns' => '3',
        'card_border_radius' => '12',
        'card_shadow' => 'medium',
        'card_hover_effect' => 'lift',
        'primary_color' => '#0073aa',
        'text_color' => '#333333',
        'background_color' => '#ffffff',
        'show_title' => 'yes',
        'show_excerpt' => 'yes',
        'show_date' => 'yes',
        'show_time' => 'yes',
        'show_location' => 'yes',
        'show_price' => 'yes',
        'show_category' => 'yes',
        'show_capacity' => 'no',
        'show_custom_fields' => 'yes',
        'show_category_tag' => 'yes',
        'show_icons' => 'yes',
        'icon_date_color' => '#0073aa',
        'icon_time_color' => '#ffc107',
        'icon_location_color' => '#6f42c1',
        'icon_price_color' => '#17a2b8',
        'icon_category_color' => '#fd7e14',
        'icon_capacity_color' => '#fd7e14',
        'search_background_color' => '#f8f9fa',
        'search_border_color' => '#e9ecef',
        'search_button_color' => '#0073aa',
        'search_text_color' => '#333333',
        'category_tag_position' => 'top-left',
        'show_status_badge' => 'yes',
        'show_featured_badge' => 'yes',
        'button_text' => 'View Details',
        'button_style' => 'filled',
        'button_color' => '#0073aa',
        'button_text_color' => '#ffffff',
        'button_size' => 'medium',
        'past_events_handling' => 'hide',
        'past_events_show_days' => '7'
    );
}

// Sanitize options
function dz_card_sanitize_options($input) {
    $sanitized = array();
    $defaults = dz_get_default_card_options();
    
    foreach ($defaults as $key => $default_value) {
        if (isset($input[$key])) {
            switch ($key) {
                case 'card_border_radius':
                    $sanitized[$key] = max(0, min(30, intval($input[$key])));
                    break;
                case 'primary_color':
                case 'text_color':
                case 'background_color':
                case 'button_color':
                case 'button_text_color':
                    $sanitized[$key] = sanitize_hex_color($input[$key]);
                    break;
                case 'button_text':
                    $sanitized[$key] = sanitize_text_field($input[$key]);
                    break;
                default:
                    $sanitized[$key] = sanitize_text_field($input[$key]);
                    break;
            }
        } else {
            $sanitized[$key] = $default_value;
        }
    }
    
    return $sanitized;
}

// New redesigned settings page
function dz_card_settings_page_new() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    $options = get_option('dz_card_options', dz_get_default_card_options());
    ?>
    <div class="wrap dz-card-settings-wrapper">
        <div class="dz-settings-header">
            <h1>🎨 Event Card Display Settings</h1>
            <p>Customize how your event cards appear on the events page and in shortcodes. Changes will be applied immediately.</p>
        </div>
        
        <!-- Live Preview Section -->
        <div class="dz-preview-section">
            <div class="dz-preview-header">
                <h2>📱 Live Preview</h2>
                <div class="dz-preview-controls">
                    <button type="button" class="dz-preview-toggle" data-device="desktop">🖥️ Desktop</button>
                    <button type="button" class="dz-preview-toggle active" data-device="mobile">📱 Mobile</button>
                </div>
            </div>
            <div class="dz-preview-container">
                <div class="dz-preview-card" id="dz-preview-card">
                    <!-- Preview will be generated by JavaScript -->
                </div>
            </div>
        </div>
        
        <!-- Tabbed Navigation -->
        <div class="dz-settings-tabs">
            <nav class="dz-tab-nav">
                <button type="button" class="dz-tab-btn active" data-tab="layout">🏗️ Layout & Design</button>
                <button type="button" class="dz-tab-btn" data-tab="fields">👁️ Show/Hide Fields</button>
                <button type="button" class="dz-tab-btn" data-tab="colors">🎨 Colors & Icons</button>
                <button type="button" class="dz-tab-btn" data-tab="buttons">🔘 Buttons & Actions</button>
                <button type="button" class="dz-tab-btn" data-tab="search">🔍 Search & Filters</button>
                <button type="button" class="dz-tab-btn" data-tab="advanced">⚙️ Advanced</button>
            </nav>
            
            <form method="post" action="options.php" id="dz-card-settings-form">
                <?php settings_fields('dz_card_settings'); ?>
                
                <!-- Layout & Design Tab -->
                <div class="dz-tab-content active" id="tab-layout">
                    <div class="dz-settings-grid">
                        <div class="dz-settings-card">
                            <h3>📐 Card Layout</h3>
                            <div class="dz-form-group">
                                <label>Card Structure</label>
                                <?php dz_card_layout_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Columns per Row</label>
                                <?php dz_card_columns_callback(); ?>
                            </div>
                        </div>
                        
                        <div class="dz-settings-card">
                            <h3>🖼️ Image Settings</h3>
                            <div class="dz-form-group">
                                <label>Image Size</label>
                                <?php dz_card_image_size_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Image Position</label>
                                <?php dz_card_image_position_callback(); ?>
                            </div>
                        </div>
                        
                        <div class="dz-settings-card">
                            <h3>🎭 Visual Effects</h3>
                            <div class="dz-form-group">
                                <label>Border Radius</label>
                                <?php dz_card_border_radius_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Card Shadow</label>
                                <?php dz_card_shadow_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Hover Effect</label>
                                <?php dz_card_hover_effect_callback(); ?>
                            </div>
                        </div>
                        
                        <div class="dz-settings-card">
                            <h3>🎨 Basic Colors</h3>
                            <div class="dz-form-group">
                                <label>Primary Color</label>
                                <?php dz_card_primary_color_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Text Color</label>
                                <?php dz_card_text_color_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Background Color</label>
                                <?php dz_card_background_color_callback(); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Show/Hide Fields Tab -->
                <div class="dz-tab-content" id="tab-fields">
                    <div class="dz-settings-grid">
                        <div class="dz-settings-card">
                            <h3>📝 Content Fields</h3>
                            <div class="dz-form-group">
                                <label>Show Title</label>
                                <?php dz_card_show_title_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Show Excerpt</label>
                                <?php dz_card_show_excerpt_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Show Icons</label>
                                <?php dz_card_show_icons_callback(); ?>
                            </div>
                        </div>
                        
                        <div class="dz-settings-card">
                            <h3>📅 Date & Time</h3>
                            <div class="dz-form-group">
                                <label>Show Date</label>
                                <?php dz_card_show_date_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Show Time</label>
                                <?php dz_card_show_time_callback(); ?>
                            </div>
                        </div>
                        
                        <div class="dz-settings-card">
                            <h3>📍 Location & Details</h3>
                            <div class="dz-form-group">
                                <label>Show Location</label>
                                <?php dz_card_show_location_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Show Price</label>
                                <?php dz_card_show_price_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Show Capacity</label>
                                <?php dz_card_show_capacity_callback(); ?>
                            </div>
                        </div>
                        
                        <div class="dz-settings-card">
                            <h3>🏷️ Categories & Tags</h3>
                            <div class="dz-form-group">
                                <label>Show Category</label>
                                <?php dz_card_show_category_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Show Category Tag</label>
                                <?php dz_card_show_category_tag_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Category Tag Position</label>
                                <?php dz_card_category_tag_position_callback(); ?>
                            </div>
                        </div>
                        
                        <div class="dz-settings-card">
                            <h3>🏆 Status & Badges</h3>
                            <div class="dz-form-group">
                                <label>Show Status Badge</label>
                                <?php dz_card_show_status_badge_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Show Featured Badge</label>
                                <?php dz_card_show_featured_badge_callback(); ?>
                            </div>
                        </div>
                        
                        <div class="dz-settings-card">
                            <h3>🔧 Custom Fields</h3>
                            <div class="dz-form-group">
                                <label>Show Custom Fields</label>
                                <?php dz_card_show_custom_fields_callback(); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Colors & Icons Tab -->
                <div class="dz-tab-content" id="tab-colors">
                    <div class="dz-settings-grid">
                        <div class="dz-settings-card">
                            <h3>📅 Date & Time Icons</h3>
                            <div class="dz-form-group">
                                <label>Date Icon Color</label>
                                <?php dz_card_icon_date_color_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Time Icon Color</label>
                                <?php dz_card_icon_time_color_callback(); ?>
                            </div>
                        </div>
                        
                        <div class="dz-settings-card">
                            <h3>📍 Location & Price Icons</h3>
                            <div class="dz-form-group">
                                <label>Location Icon Color</label>
                                <?php dz_card_icon_location_color_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Price Icon Color</label>
                                <?php dz_card_icon_price_color_callback(); ?>
                            </div>
                        </div>
                        
                        <div class="dz-settings-card">
                            <h3>📂 Category & Capacity Icons</h3>
                            <div class="dz-form-group">
                                <label>Category Icon Color</label>
                                <?php dz_card_icon_category_color_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Capacity Icon Color</label>
                                <?php dz_card_icon_capacity_color_callback(); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Buttons & Actions Tab -->
                <div class="dz-tab-content" id="tab-buttons">
                    <div class="dz-settings-grid">
                        <div class="dz-settings-card">
                            <h3>🔘 Button Text & Style</h3>
                            <div class="dz-form-group">
                                <label>Button Text</label>
                                <?php dz_card_button_text_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Button Style</label>
                                <?php dz_card_button_style_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Button Size</label>
                                <?php dz_card_button_size_callback(); ?>
                            </div>
                        </div>
                        
                        <div class="dz-settings-card">
                            <h3>🎨 Button Colors</h3>
                            <div class="dz-form-group">
                                <label>Button Color</label>
                                <?php dz_card_button_color_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Button Text Color</label>
                                <?php dz_card_button_text_color_callback(); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Search & Filters Tab -->
                <div class="dz-tab-content" id="tab-search">
                    <div class="dz-settings-grid">
                        <div class="dz-settings-card">
                            <h3>🔍 Search Colors</h3>
                            <div class="dz-form-group">
                                <label>Search Background</label>
                                <?php dz_card_search_background_color_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Search Border</label>
                                <?php dz_card_search_border_color_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Search Button</label>
                                <?php dz_card_search_button_color_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Search Text</label>
                                <?php dz_card_search_text_color_callback(); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Advanced Tab -->
                <div class="dz-tab-content" id="tab-advanced">
                    <div class="dz-settings-grid">
                        <div class="dz-settings-card">
                            <h3>⏰ Past Events</h3>
                            <div class="dz-form-group">
                                <label>Past Events Handling</label>
                                <?php dz_card_past_events_handling_callback(); ?>
                            </div>
                            <div class="dz-form-group">
                                <label>Show Past Events For (Days)</label>
                                <?php dz_card_past_events_show_days_callback(); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="dz-settings-footer">
                    <?php submit_button('💾 Save Card Settings', 'primary large', 'submit', false); ?>
                    <button type="button" class="button button-secondary" id="dz-reset-settings">🔄 Reset to Defaults</button>
                </div>
            </form>
        </div>
        
        <!-- Shortcode Help -->
        <div class="dz-shortcode-help">
            <h2>📋 Shortcode Usage</h2>
            <div class="dz-shortcode-examples">
                <div class="dz-shortcode-example">
                    <h4>Basic Usage</h4>
                    <code>[dz_events]</code>
                    <p>Display events with current card settings</p>
                </div>
                <div class="dz-shortcode-example">
                    <h4>With Parameters</h4>
                    <code>[dz_events layout="grid" count="6"]</code>
                    <p>Override specific settings</p>
                </div>
                <div class="dz-shortcode-example">
                    <h4>Filter by Category</h4>
                    <code>[dz_events category="conference"]</code>
                    <p>Show only events from specific category</p>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    /* Modern Card Settings Styles */
    .dz-card-settings-wrapper {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .dz-settings-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        text-align: center;
    }
    
    .dz-settings-header h1 {
        margin: 0 0 10px 0;
        font-size: 28px;
        font-weight: 600;
    }
    
    .dz-settings-header p {
        margin: 0;
        opacity: 0.9;
        font-size: 16px;
    }
    
    /* Preview Section */
    .dz-preview-section {
        background: white;
        border: 1px solid #e1e5e9;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .dz-preview-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f8f9fa;
    }
    
    .dz-preview-header h2 {
        margin: 0;
        color: #2c3e50;
        font-size: 20px;
    }
    
    .dz-preview-controls {
        display: flex;
        gap: 10px;
    }
    
    .dz-preview-toggle {
        padding: 8px 16px;
        border: 2px solid #e1e5e9;
        background: white;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .dz-preview-toggle.active {
        background: #0073aa;
        color: white;
        border-color: #0073aa;
    }
    
    .dz-preview-container {
        display: flex;
        justify-content: center;
        padding: 30px;
        background: #f8f9fa;
        border-radius: 12px;
        min-height: 200px;
        align-items: center;
    }
    
    .dz-preview-card {
        max-width: 300px;
        width: 100%;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .dz-preview-card:hover {
        transform: translateY(-5px);
    }
    
    /* Tab Navigation */
    .dz-settings-tabs {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .dz-tab-nav {
        display: flex;
        background: #f8f9fa;
        border-bottom: 1px solid #e1e5e9;
        overflow-x: auto;
    }
    
    .dz-tab-btn {
        flex: 1;
        padding: 15px 20px;
        border: none;
        background: transparent;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        color: #6c757d;
        transition: all 0.3s ease;
        white-space: nowrap;
        min-width: 150px;
    }
    
    .dz-tab-btn:hover {
        background: #e9ecef;
        color: #495057;
    }
    
    .dz-tab-btn.active {
        background: white;
        color: #0073aa;
        border-bottom: 3px solid #0073aa;
    }
    
    /* Tab Content */
    .dz-tab-content {
        display: none;
        padding: 30px;
    }
    
    .dz-tab-content.active {
        display: block;
    }
    
    /* Settings Grid */
    .dz-settings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
    }
    
    .dz-settings-card {
        background: #f8f9fa;
        border: 1px solid #e1e5e9;
        border-radius: 12px;
        padding: 25px;
        transition: all 0.3s ease;
    }
    
    .dz-settings-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .dz-settings-card h3 {
        margin: 0 0 20px 0;
        color: #2c3e50;
        font-size: 16px;
        font-weight: 600;
        padding-bottom: 10px;
        border-bottom: 2px solid #e1e5e9;
    }
    
    /* Form Groups */
    .dz-form-group {
        margin-bottom: 20px;
    }
    
    .dz-form-group:last-child {
        margin-bottom: 0;
    }
    
    .dz-form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #495057;
        font-size: 14px;
    }
    
    .dz-form-group select,
    .dz-form-group input[type="text"],
    .dz-form-group input[type="number"] {
        width: 100%;
        padding: 10px 12px;
        border: 2px solid #e1e5e9;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.3s ease;
    }
    
    .dz-form-group select:focus,
    .dz-form-group input[type="text"]:focus,
    .dz-form-group input[type="number"]:focus {
        outline: none;
        border-color: #0073aa;
        box-shadow: 0 0 0 3px rgba(0,115,170,0.1);
    }
    
    .dz-color-picker {
        width: 60px;
        height: 40px;
        border: 2px solid #e1e5e9;
        border-radius: 8px;
        cursor: pointer;
        transition: border-color 0.3s ease;
    }
    
    .dz-color-picker:hover {
        border-color: #0073aa;
    }
    
    .dz-range-slider {
        width: 100%;
        margin: 10px 0;
    }
    
    .dz-form-group .description {
        margin-top: 5px;
        font-size: 12px;
        color: #6c757d;
        font-style: italic;
    }
    
    /* Settings Footer */
    .dz-settings-footer {
        padding: 25px 30px;
        background: #f8f9fa;
        border-top: 1px solid #e1e5e9;
        display: flex;
        gap: 15px;
        align-items: center;
    }
    
    .dz-settings-footer .button {
        padding: 12px 24px;
        font-size: 14px;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .dz-settings-footer .button-primary {
        background: #0073aa;
        border-color: #0073aa;
    }
    
    .dz-settings-footer .button-primary:hover {
        background: #005a87;
        border-color: #005a87;
        transform: translateY(-1px);
    }
    
    .dz-settings-footer .button-secondary {
        background: white;
        border-color: #6c757d;
        color: #6c757d;
    }
    
    .dz-settings-footer .button-secondary:hover {
        background: #6c757d;
        color: white;
        transform: translateY(-1px);
    }
    
    /* Shortcode Help */
    .dz-shortcode-help {
        background: white;
        border: 1px solid #e1e5e9;
        border-radius: 12px;
        padding: 25px;
        margin-top: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .dz-shortcode-help h2 {
        margin: 0 0 20px 0;
        color: #2c3e50;
        font-size: 20px;
    }
    
    .dz-shortcode-examples {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .dz-shortcode-example {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #0073aa;
    }
    
    .dz-shortcode-example h4 {
        margin: 0 0 10px 0;
        color: #2c3e50;
        font-size: 14px;
        font-weight: 600;
    }
    
    .dz-shortcode-example code {
        display: block;
        background: #2c3e50;
        color: #ecf0f1;
        padding: 10px;
        border-radius: 4px;
        font-family: 'Courier New', monospace;
        font-size: 13px;
        margin-bottom: 10px;
    }
    
    .dz-shortcode-example p {
        margin: 0;
        font-size: 13px;
        color: #6c757d;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .dz-settings-grid {
            grid-template-columns: 1fr;
        }
        
        .dz-tab-nav {
            flex-direction: column;
        }
        
        .dz-tab-btn {
            min-width: auto;
            text-align: left;
        }
        
        .dz-preview-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }
        
        .dz-settings-footer {
            flex-direction: column;
            align-items: stretch;
        }
        
        .dz-shortcode-examples {
            grid-template-columns: 1fr;
        }
    }
    
    /* Animation for tab switching */
    .dz-tab-content {
        animation: fadeIn 0.3s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        // Tab switching functionality
        $('.dz-tab-btn').on('click', function() {
            var tabId = $(this).data('tab');
            
            // Remove active class from all tabs and content
            $('.dz-tab-btn').removeClass('active');
            $('.dz-tab-content').removeClass('active');
            
            // Add active class to clicked tab and corresponding content
            $(this).addClass('active');
            $('#tab-' + tabId).addClass('active');
        });
        
        // Preview toggle functionality
        $('.dz-preview-toggle').on('click', function() {
            $('.dz-preview-toggle').removeClass('active');
            $(this).addClass('active');
            
            var device = $(this).data('device');
            var $previewCard = $('#dz-preview-card');
            
            if (device === 'mobile') {
                $previewCard.css('max-width', '250px');
            } else {
                $previewCard.css('max-width', '300px');
            }
        });
        
        // Live preview functionality
        function updatePreview() {
            var formData = $('#dz-card-settings-form').serializeArray();
            var options = {};
            
            $.each(formData, function(i, field) {
                if (field.name.startsWith('dz_card_options[')) {
                    var key = field.name.replace('dz_card_options[', '').replace(']', '');
                    options[key] = field.value;
                }
            });
            
            // Generate preview HTML
            var previewHtml = generatePreviewHTML(options);
            $('#dz-preview-card').html(previewHtml);
        }
        
        function generatePreviewHTML(options) {
            var html = '<div class="dz-event-card-preview" style="';
            html += 'border-radius: ' + (options.card_border_radius || '12') + 'px;';
            html += 'background: ' + (options.background_color || '#fff') + ';';
            html += 'color: ' + (options.text_color || '#333') + ';';
            html += 'box-shadow: ' + (options.card_shadow === 'none' ? 'none' : '0 4px 15px rgba(0,0,0,0.1)') + ';';
            html += 'overflow: hidden; border: 1px solid #eee;">';
            
            // Image
            html += '<div class="dz-event-thumb-preview" style="';
            html += 'height: ' + (options.image_size === 'small' ? '150px' : options.image_size === 'large' ? '250px' : '200px') + ';';
            html += 'background: linear-gradient(45deg, #f0f0f0, #e0e0e0);';
            html += 'display: flex; align-items: center; justify-content: center;';
            html += 'color: #999; font-size: 14px; position: relative;">';
            html += 'Event Image';
            
            // Add category tag preview
            if (options.show_category_tag !== 'no') {
                var tagPosition = options.category_tag_position === 'top-right' ? 'top: 10px; right: 10px;' : 'top: 10px; left: 10px;';
                html += '<span style="position: absolute; ' + tagPosition + ' background: rgba(0,0,0,0.7); color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Conference</span>';
            }
            
            html += '</div>';
            
            // Content
            html += '<div class="dz-event-content-preview" style="padding: 20px;">';
            
            // Title
            if (options.show_title !== 'no') {
                html += '<h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600;">Sample Event Title</h3>';
            }
            
            // Fields
            if (options.show_date !== 'no') {
                html += '<div class="dz-preview-field"><i class="fas fa-calendar"></i> Dec 25, 2024</div>';
            }
            
            if (options.show_time !== 'no') {
                html += '<div class="dz-preview-field"><i class="fas fa-clock"></i> 2:00 PM - 4:00 PM</div>';
            }
            
            if (options.show_location !== 'no') {
                html += '<div class="dz-preview-field"><i class="fas fa-map-marker"></i> Convention Center</div>';
            }
            
            if (options.show_price !== 'no') {
                html += '<div class="dz-preview-field"><i class="fas fa-tag"></i> $50</div>';
            }
            
            if (options.show_category !== 'no') {
                html += '<div class="dz-preview-field"><i class="fas fa-folder"></i> Conference</div>';
            }
            
            if (options.show_custom_fields !== 'no') {
                html += '<div class="dz-preview-field"><i class="fas fa-user"></i> John Doe (Speaker)</div>';
                html += '<div class="dz-preview-field"><i class="fas fa-globe"></i> example.com</div>';
            }
            
            if (options.show_excerpt !== 'no') {
                html += '<p style="margin: 15px 0; color: #666; font-size: 14px; line-height: 1.4;">This is a sample event description that shows how the excerpt will appear on your event cards.</p>';
            }
            
            // Button
            html += '<div style="margin-top: 20px;">';
            html += '<a href="#" style="';
            html += 'display: inline-block; padding: ' + (options.button_size === 'small' ? '8px 16px' : options.button_size === 'large' ? '14px 28px' : '10px 20px') + ';';
            html += 'background: ' + (options.button_color || '#0073aa') + ';';
            html += 'color: ' + (options.button_text_color || '#fff') + ';';
            html += 'text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px;';
            html += 'border: none; cursor: pointer;">';
            html += (options.button_text || 'View Details');
            html += '</a>';
            html += '</div>';
            
            html += '</div></div>';
            
            return html;
        }
        
        // Update preview on form change
        $('#dz-card-settings-form input, #dz-card-settings-form select').on('change input', function() {
            updatePreview();
        });
        
        // Reset settings functionality
        $('#dz-reset-settings').on('click', function() {
            if (confirm('Are you sure you want to reset all settings to defaults? This cannot be undone.')) {
                // Reset form to default values
                var defaults = <?php echo json_encode(dz_get_default_card_options()); ?>;
                
                $.each(defaults, function(key, value) {
                    var $field = $('[name="dz_card_options[' + key + ']"]');
                    if ($field.length) {
                        $field.val(value);
                    }
                });
                
                updatePreview();
                alert('Settings have been reset to defaults. Click "Save Card Settings" to apply changes.');
            }
        });
        
        // Initial preview
        updatePreview();
    });
    </script>
    <?php
}
