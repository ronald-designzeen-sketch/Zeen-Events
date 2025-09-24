<?php
// Register Events Post Type
function dz_register_event_post_type() {
    $labels = array(
        'name'               => __('Events', 'designzeen-events'),
        'singular_name'      => __('Event', 'designzeen-events'),
        'menu_name'          => __('Events', 'designzeen-events'),
        'name_admin_bar'     => __('Event', 'designzeen-events'),
        'add_new'            => __('Add New Event', 'designzeen-events'),
        'add_new_item'       => __('Add New Event', 'designzeen-events'),
        'new_item'           => __('New Event', 'designzeen-events'),
        'edit_item'          => __('Edit Event', 'designzeen-events'),
        'view_item'          => __('View Event', 'designzeen-events'),
        'all_items'          => __('All Events', 'designzeen-events'),
        'search_items'       => __('Search Events', 'designzeen-events'),
    );

    // Get custom events page URL for rewrite base
    $custom_events_url = get_option('dz_events_page_url', 'events');
    $use_custom_page = get_option('dz_events_use_custom_page', 'yes');
    
    // Use custom page URL as base if custom page is enabled
    $rewrite_slug = ($use_custom_page === 'yes') ? $custom_events_url : 'events';
    
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_nav_menus'  => true,
        'show_in_admin_bar'  => true,
        'has_archive'        => true,
        'rewrite'            => array( 'slug' => $rewrite_slug, 'with_front' => false ),
        'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'page-attributes' ),
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-calendar-alt',
        'capability_type'    => 'post',
        'hierarchical'       => false,
        'query_var'          => true,
        'can_export'         => true,
        'delete_with_user'   => false,
    );

    register_post_type( 'dz_event', $args );
}
add_action( 'init', 'dz_register_event_post_type' );

// Register Event Registration Post Type
function dz_register_event_registration_post_type() {
    $labels = array(
        'name'               => __('Event Registrations', 'designzeen-events'),
        'singular_name'      => __('Event Registration', 'designzeen-events'),
        'menu_name'          => __('Registrations', 'designzeen-events'),
        'name_admin_bar'     => __('Registration', 'designzeen-events'),
        'add_new'            => __('Add New Registration', 'designzeen-events'),
        'add_new_item'       => __('Add New Registration', 'designzeen-events'),
        'new_item'           => __('New Registration', 'designzeen-events'),
        'edit_item'          => __('Edit Registration', 'designzeen-events'),
        'view_item'          => __('View Registration', 'designzeen-events'),
        'all_items'          => __('All Registrations', 'designzeen-events'),
        'search_items'       => __('Search Registrations', 'designzeen-events'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => 'edit.php?post_type=dz_event',
        'show_in_nav_menus'  => false,
        'show_in_admin_bar'  => false,
        'has_archive'        => false,
        'rewrite'            => false,
        'supports'           => array( 'title', 'custom-fields' ),
        'show_in_rest'       => false,
        'menu_icon'          => 'dashicons-groups',
        'capability_type'    => 'post',
        'hierarchical'       => false,
        'query_var'          => false,
        'can_export'         => true,
        'delete_with_user'   => false,
    );

    register_post_type( 'dz_event_registration', $args );
}
add_action( 'init', 'dz_register_event_registration_post_type' );

// Flush rewrite rules on plugin activation
function dz_flush_rewrite_rules() {
    dz_register_event_post_type();
    dz_register_event_taxonomy();
    flush_rewrite_rules();
}
register_activation_hook(plugin_dir_path(__FILE__) . '../zeen-events.php', 'dz_flush_rewrite_rules');

// Add a function to manually flush rewrite rules
function dz_manual_flush_rewrite_rules() {
    if (current_user_can('manage_options') && isset($_GET['dz_flush_rules'])) {
        flush_rewrite_rules();
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success"><p>Rewrite rules flushed successfully!</p></div>';
        });
    }
}
add_action('admin_init', 'dz_manual_flush_rewrite_rules');

// Add rewrite rules flush to admin menu
function dz_add_flush_rules_to_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=dz_event',
        __('Flush Rewrite Rules', 'designzeen-events'),
        __('Flush Rules', 'designzeen-events'),
        'manage_options',
        'dz-flush-rules',
        'dz_flush_rules_page'
    );
}
add_action('admin_menu', 'dz_add_flush_rules_to_admin_menu');

function dz_flush_rules_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    ?>
    <div class="wrap">
        <h1><?php _e('Flush Rewrite Rules', 'designzeen-events'); ?></h1>
        <p><?php _e("If you're experiencing 404 errors with your events, try flushing the rewrite rules.", 'designzeen-events'); ?></p>
        
        <a href="<?php echo admin_url('edit.php?post_type=dz_event&page=dz-flush-rules&dz_flush_rules=1'); ?>" class="button button-primary">
            <?php _e('Flush Rewrite Rules Now', 'designzeen-events'); ?>
        </a>
        
        <h2><?php _e('Alternative Methods:', 'designzeen-events'); ?></h2>
        <ol>
            <li><?php _e('Go to <strong>Settings → Permalinks</strong> and click "Save Changes"', 'designzeen-events'); ?></li>
            <li><?php _e('Deactivate and reactivate the plugin', 'designzeen-events'); ?></li>
            <li><?php _e('Use the WordPress CLI: <code>wp rewrite flush</code>', 'designzeen-events'); ?></li>
        </ol>
    </div>
    <?php
}

// Register Category for Events
function dz_register_event_taxonomy() {
    register_taxonomy(
        'dz_event_category',
        'dz_event',
        array(
            'label'        => __('Event Categories', 'designzeen-events'),
            'rewrite'      => array( 'slug' => 'event-category' ),
            'hierarchical' => true,
            'show_in_rest' => true,
        )
    );
}
add_action( 'init', 'dz_register_event_taxonomy' );

// Admin columns for events list
function dz_events_admin_columns_setup() {
    // Only run on events admin page
    if (!is_admin() || !isset($_GET['post_type']) || $_GET['post_type'] !== 'dz_event') {
        return;
    }
    
    // Force remove any existing filters that might be interfering
    remove_all_filters('manage_dz_event_posts_columns');
    remove_all_actions('manage_dz_event_posts_custom_column');
    
    // Force add our admin columns with highest priority
    add_filter('manage_dz_event_posts_columns', function($columns) {
        // Create new columns array
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['event_date'] = __('Event Date', 'designzeen-events');
        $new_columns['event_location'] = __('Location', 'designzeen-events');
        $new_columns['event_price'] = __('Price', 'designzeen-events');
        $new_columns['date'] = __('Published', 'designzeen-events');
        return $new_columns;
    }, 9999);
    
    // Force add column content with highest priority
    add_action('manage_dz_event_posts_custom_column', function($column, $post_id) {
        switch ($column) {
            case 'event_date':
                $start_date = get_post_meta($post_id, '_dz_event_start', true);
                if ($start_date) {
                    echo date('M j, Y', strtotime($start_date));
                } else {
                    echo '<span style="color: #999;">Not set</span>';
                }
                break;
                
            case 'event_location':
                $location = get_post_meta($post_id, '_dz_event_location', true);
                if ($location) {
                    echo esc_html($location);
                } else {
                    echo '<span style="color: #999;">Not set</span>';
                }
                break;
                
            case 'event_price':
                $price = get_post_meta($post_id, '_dz_event_price', true);
                if ($price) {
                    echo esc_html($price);
                } else {
                    echo '<span style="color: #999;">Free</span>';
                }
                break;
        }
    }, 10, 2);
    
    // Force add CSS to ensure columns display
    add_action('admin_head', function() {
        ?>
        <style>
        /* Force admin columns to display */
        .wp-list-table .column-event_date { 
            width: 120px !important; 
            display: table-cell !important;
        }
        .wp-list-table .column-event_location { 
            width: 200px !important; 
            display: table-cell !important;
        }
        .wp-list-table .column-event_price { 
            width: 80px !important; 
            display: table-cell !important;
        }
        .wp-list-table th.column-event_date,
        .wp-list-table th.column-event_location,
        .wp-list-table th.column-event_price {
            display: table-cell !important;
        }
        .wp-list-table td.column-event_date,
        .wp-list-table td.column-event_location,
        .wp-list-table td.column-event_price {
            display: table-cell !important;
        }
        </style>
        <?php
    });
}
add_action('admin_init', 'dz_events_admin_columns_setup', 1);

// Admin notice
function dz_events_admin_notice() {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'dz_event') {
        echo '<div class="notice notice-success"><p>';
        echo '<strong>Zeen Events:</strong> Your events are now displayed with proper information columns.';
        echo '</p></div>';
    }
}
add_action('admin_notices', 'dz_events_admin_notice');
