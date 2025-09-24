<?php
// Admin Settings Page for Zeen Events Plugin

// Add admin menu
function dz_events_admin_menu() {
    add_options_page(
        'Zeen Events Settings',
        'Zeen Events',
        'manage_options',
        'dz-events-settings',
        'dz_events_settings_page'
    );
}
add_action('admin_menu', 'dz_events_admin_menu');

// Register settings
function dz_events_register_settings() {
    register_setting('dz_events_settings', 'dz_events_options', 'dz_events_sanitize_options');
    
    add_settings_section(
        'dz_events_general',
        'General Settings',
        'dz_events_general_section_callback',
        'dz_events_settings'
    );
    
    add_settings_field(
        'default_layout',
        'Default Layout',
        'dz_events_default_layout_callback',
        'dz_events_settings',
        'dz_events_general'
    );
    
    add_settings_field(
        'default_count',
        'Default Event Count',
        'dz_events_default_count_callback',
        'dz_events_settings',
        'dz_events_general'
    );
    
    add_settings_field(
        'show_past_events',
        'Show Past Events by Default',
        'dz_events_show_past_callback',
        'dz_events_settings',
        'dz_events_general'
    );
    
    add_settings_section(
        'dz_events_display',
        'Display Settings',
        'dz_events_display_section_callback',
        'dz_events_settings'
    );
    
    add_settings_field(
        'primary_color',
        'Primary Color',
        'dz_events_primary_color_callback',
        'dz_events_settings',
        'dz_events_display'
    );
    
    add_settings_field(
        'custom_css',
        'Custom CSS',
        'dz_events_custom_css_callback',
        'dz_events_settings',
        'dz_events_display'
    );
}
add_action('admin_init', 'dz_events_register_settings');

// Settings page HTML
function dz_events_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    $options = get_option('dz_events_options', array());
    ?>
    <div class="wrap">
        <h1>Zeen Events Settings</h1>
        
        <div class="dz-events-admin-header">
            <div class="dz-events-stats">
                <?php
                $total_events = wp_count_posts('dz_event');
                $upcoming_events = get_posts(array(
                    'post_type' => 'dz_event',
                    'meta_query' => array(
                        array(
                            'key' => '_dz_event_start',
                            'value' => date('Y-m-d'),
                            'compare' => '>='
                        )
                    ),
                    'numberposts' => -1
                ));
                ?>
                <div class="dz-stat-box">
                    <h3><?php echo $total_events->publish; ?></h3>
                    <p>Total Events</p>
                </div>
                <div class="dz-stat-box">
                    <h3><?php echo count($upcoming_events); ?></h3>
                    <p>Upcoming Events</p>
                </div>
                <div class="dz-stat-box">
                    <h3><?php echo wp_count_terms('dz_event_category'); ?></h3>
                    <p>Categories</p>
                </div>
            </div>
        </div>
        
        <form method="post" action="options.php">
            <?php
            settings_fields('dz_events_settings');
            do_settings_sections('dz_events_settings');
            submit_button();
            ?>
        </form>
        
        <div class="dz-events-shortcode-help">
            <h2>Shortcode Usage</h2>
            <p>Use the following shortcode to display events on your pages and posts:</p>
            <code>[dz_events]</code>
            
            <h3>Available Parameters:</h3>
            <ul>
                <li><strong>count</strong> - Number of events to display (default: 6)</li>
                <li><strong>layout</strong> - Layout style: grid, list, or carousel (default: grid)</li>
                <li><strong>category</strong> - Filter by category slug</li>
                <li><strong>status</strong> - Filter by status: upcoming, ongoing, completed, cancelled</li>
                <li><strong>orderby</strong> - Order by: meta_value, title, date (default: meta_value)</li>
                <li><strong>order</strong> - Order direction: ASC or DESC (default: ASC)</li>
                <li><strong>show_past</strong> - Show past events: true or false (default: false)</li>
                <li><strong>featured</strong> - Show only featured events: true or false (default: false)</li>
                <li><strong>use_custom_cards</strong> - Use custom card design: true or false (default: true)</li>
                <li><strong>search</strong> - Enable search functionality: true or false (default: false)</li>
                <li><strong>search_placeholder</strong> - Custom placeholder text for search input (default: "Search events...")</li>
            </ul>
            
            <h3>Examples:</h3>
            <code>[dz_events count="4" layout="carousel"]</code><br>
            <code>[dz_events category="conference" status="upcoming"]</code><br>
            <code>[dz_events featured="true" layout="list"]</code><br>
            <code>[dz_events search="true" search_placeholder="Find your perfect event..."]</code><br>
            <code>[dz_events search="true" count="12" layout="grid" category="workshop"]</code><br>
            <code>[dz_events search="true" use_custom_cards="true" search_placeholder="Search all events..."]</code>
            
            <h3>Search Feature:</h3>
            <p>The search functionality allows users to filter events in real-time by typing in the search box. It searches across:</p>
            <ul>
                <li>Event titles and descriptions</li>
                <li>Event dates and times</li>
                <li>Event locations</li>
                <li>Event prices and categories</li>
                <li>Custom fields (speaker, organizer, etc.)</li>
            </ul>
            <p><strong>Note:</strong> The search feature is optional and only appears when <code>search="true"</code> is added to the shortcode.</p>
        </div>
    </div>
    
    <style>
    .dz-events-admin-header {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        margin: 20px 0;
    }
    
    .dz-events-stats {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .dz-stat-box {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        min-width: 120px;
    }
    
    .dz-stat-box h3 {
        margin: 0 0 5px 0;
        font-size: 2em;
        color: #0073aa;
    }
    
    .dz-stat-box p {
        margin: 0;
        color: #666;
        font-size: 14px;
    }
    
    .dz-events-shortcode-help {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        margin-top: 20px;
    }
    
    .dz-events-shortcode-help code {
        background: #f1f1f1;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: monospace;
    }
    
    .dz-events-shortcode-help ul {
        margin-left: 20px;
    }
    
    .dz-events-shortcode-help li {
        margin-bottom: 5px;
    }
    </style>
    <?php
}

// Section callbacks
function dz_events_general_section_callback() {
    echo '<p>Configure general settings for the Zeen Events plugin.</p>';
}

function dz_events_display_section_callback() {
    echo '<p>Customize the appearance of your events display.</p>';
}

// Field callbacks
function dz_events_default_layout_callback() {
    $options = get_option('dz_events_options', array());
    $value = isset($options['default_layout']) ? $options['default_layout'] : 'grid';
    ?>
    <select name="dz_events_options[default_layout]">
        <option value="grid" <?php selected($value, 'grid'); ?>>Grid</option>
        <option value="list" <?php selected($value, 'list'); ?>>List</option>
        <option value="carousel" <?php selected($value, 'carousel'); ?>>Carousel</option>
    </select>
    <p class="description">Default layout for events display.</p>
    <?php
}

function dz_events_default_count_callback() {
    $options = get_option('dz_events_options', array());
    $value = isset($options['default_count']) ? $options['default_count'] : 6;
    ?>
    <input type="number" name="dz_events_options[default_count]" value="<?php echo esc_attr($value); ?>" min="1" max="50" />
    <p class="description">Default number of events to display.</p>
    <?php
}

function dz_events_show_past_callback() {
    $options = get_option('dz_events_options', array());
    $value = isset($options['show_past_events']) ? $options['show_past_events'] : false;
    ?>
    <input type="checkbox" name="dz_events_options[show_past_events]" value="1" <?php checked($value, 1); ?> />
    <p class="description">Show past events by default in shortcodes.</p>
    <?php
}

function dz_events_primary_color_callback() {
    $options = get_option('dz_events_options', array());
    $value = isset($options['primary_color']) ? $options['primary_color'] : '#0073aa';
    ?>
    <input type="color" name="dz_events_options[primary_color]" value="<?php echo esc_attr($value); ?>" />
    <p class="description">Primary color for buttons and accents.</p>
    <?php
}

function dz_events_custom_css_callback() {
    $options = get_option('dz_events_options', array());
    $value = isset($options['custom_css']) ? $options['custom_css'] : '';
    ?>
    <textarea name="dz_events_options[custom_css]" rows="10" cols="50" class="large-text code"><?php echo esc_textarea($value); ?></textarea>
    <p class="description">Add custom CSS to override default styles.</p>
    <?php
}

// Sanitize options
function dz_events_sanitize_options($input) {
    $sanitized = array();
    
    if (isset($input['default_layout'])) {
        $allowed_layouts = array('grid', 'list', 'carousel');
        $sanitized['default_layout'] = in_array($input['default_layout'], $allowed_layouts) ? $input['default_layout'] : 'grid';
    }
    
    if (isset($input['default_count'])) {
        $sanitized['default_count'] = max(1, min(50, intval($input['default_count'])));
    }
    
    if (isset($input['show_past_events'])) {
        $sanitized['show_past_events'] = 1;
    } else {
        $sanitized['show_past_events'] = 0;
    }
    
    if (isset($input['primary_color'])) {
        $sanitized['primary_color'] = sanitize_hex_color($input['primary_color']);
    }
    
    if (isset($input['custom_css'])) {
        $sanitized['custom_css'] = wp_strip_all_tags($input['custom_css']);
    }
    
    return $sanitized;
}

// Add custom CSS to frontend
function dz_events_add_custom_css() {
    $options = get_option('dz_events_options', array());
    if (!empty($options['custom_css'])) {
        echo '<style type="text/css">' . $options['custom_css'] . '</style>';
    }
}
add_action('wp_head', 'dz_events_add_custom_css');
