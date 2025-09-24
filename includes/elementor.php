<?php
// Elementor Support for Zeen Events Plugin

// Check if Elementor is active
if (!did_action('elementor/loaded')) {
    return;
}

// Register Elementor category
function dz_add_elementor_widget_categories($elements_manager) {
    $elements_manager->add_category(
        'designzeen-events',
        [
            'title' => __('Zeen Events', 'designzeen-events'),
            'icon' => 'fa fa-calendar',
        ]
    );
}
add_action('elementor/elements/categories_registered', 'dz_add_elementor_widget_categories');

// Register Elementor widgets
function dz_register_elementor_widgets($widgets_manager) {
    require_once plugin_dir_path(__FILE__) . 'elementor-widgets.php';
    
    $widgets_manager->register(new \DZ_Events_List_Widget());
    $widgets_manager->register(new \DZ_Event_Social_Share_Widget());
    $widgets_manager->register(new \DZ_Event_Actions_Widget());
    $widgets_manager->register(new \DZ_Event_Details_Table_Widget());
}
add_action('elementor/widgets/register', 'dz_register_elementor_widgets');

// Add Elementor support for single event template
function dz_add_elementor_support() {
    add_post_type_support('dz_event', 'elementor');
}
add_action('init', 'dz_add_elementor_support');

// Ensure post type is registered before Elementor tries to access it
function dz_ensure_post_type_for_elementor() {
    if (!post_type_exists('dz_event')) {
        // Include the post types file if not already loaded
        if (!function_exists('dz_register_event_post_type')) {
            require_once plugin_dir_path(__FILE__) . 'post-types.php';
        }
        dz_register_event_post_type();
    }
}
add_action('elementor/init', 'dz_ensure_post_type_for_elementor');

// Add Elementor location for single event template
function dz_register_elementor_locations($elementor_theme_manager) {
    $elementor_theme_manager->register_location(
        'single-event',
        [
            'hook' => 'dz_single_event_content',
            'remove_hooks' => ['dz_single_event_default_content'],
        ]
    );
}
add_action('elementor/theme/register_locations', 'dz_register_elementor_locations');

// Add custom CSS for Elementor compatibility
function dz_elementor_custom_css() {
    ?>
    <style>
    .elementor-widget-dz-events-list .dz-events-wrapper {
        margin: 0;
    }
    
    .elementor-widget-dz-event-social-share .dz-social-share {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .elementor-widget-dz-event-actions .dz-event-actions {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .elementor-widget-dz-event-actions .dz-event-actions .dz-event-btn {
        flex: 1;
        min-width: 150px;
    }
    
    .elementor-widget-dz-event-details-table .dz-event-details-widget {
        margin: 0;
    }
    
    .elementor-widget-dz-event-details-table .dz-event-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    
    .elementor-widget-dz-event-details-table .dz-event-action-btn {
        flex: 1;
        min-width: 120px;
        text-align: center;
    }
    
    @media (max-width: 768px) {
        .elementor-widget-dz-event-actions .dz-event-actions {
            flex-direction: column;
        }
        
        .elementor-widget-dz-event-actions .dz-event-actions .dz-event-btn {
            flex: none;
            min-width: auto;
        }
        
        .elementor-widget-dz-event-details-table .dz-event-actions {
            flex-direction: column;
        }
        
        .elementor-widget-dz-event-details-table .dz-event-action-btn {
            flex: none;
            min-width: auto;
        }
    }
    </style>
    <?php
}
add_action('wp_head', 'dz_elementor_custom_css');
