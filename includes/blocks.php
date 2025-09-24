<?php
function dz_register_event_block() {
    wp_register_script(
        'dz-events-block',
        plugin_dir_url(__FILE__) . '../assets/js/events-block.js',
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
        filemtime(plugin_dir_path(__FILE__) . '../assets/js/events-block.js')
    );

    register_block_type('designzeen/events-grid', array(
        'editor_script'   => 'dz-events-block',
        'render_callback' => 'dz_render_events_block',
        'attributes'      => array(
            'count' => array(
                'type'    => 'number',
                'default' => 6
            ),
            'layout' => array(
                'type'    => 'string',
                'default' => 'grid'
            )
        ),
    ));
}
add_action('init', 'dz_register_event_block');

function dz_render_events_block($attributes) {
    $count     = isset($attributes['count']) ? intval($attributes['count']) : 6;
    $layout    = isset($attributes['layout']) ? sanitize_text_field($attributes['layout']) : 'grid';
    $category  = isset($attributes['category']) ? sanitize_text_field($attributes['category']) : '';
    $status    = isset($attributes['status']) ? sanitize_text_field($attributes['status']) : '';
    $orderby   = isset($attributes['orderby']) ? sanitize_text_field($attributes['orderby']) : 'meta_value';
    $order     = isset($attributes['order']) ? sanitize_text_field($attributes['order']) : 'ASC';
    $show_past = isset($attributes['showPast']) ? ($attributes['showPast'] ? 'true' : 'false') : 'false';
    $featured  = isset($attributes['featured']) ? ($attributes['featured'] ? 'true' : 'false') : 'false';

    // Build shortcode attributes
    $shortcode_atts = array(
        'count' => $count,
        'layout' => $layout,
        'orderby' => $orderby,
        'order' => $order,
        'show_past' => $show_past,
        'featured' => $featured
    );

    // Add optional attributes if they have values
    if (!empty($category)) {
        $shortcode_atts['category'] = $category;
    }
    if (!empty($status)) {
        $shortcode_atts['status'] = $status;
    }

    // Build shortcode string
    $shortcode_string = '[dz_events';
    foreach ($shortcode_atts as $key => $value) {
        $shortcode_string .= ' ' . $key . '="' . esc_attr($value) . '"';
    }
    $shortcode_string .= ']';

    return do_shortcode($shortcode_string);
}
