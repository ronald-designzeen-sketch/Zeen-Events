<?php
/**
 * Minimal Single Event - Basic single event functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

// Basic single event template handling
function dz_single_event_template($template) {
    if (is_singular('dz_event')) {
        $custom_template = locate_template(array('single-dz_event.php'));
        if ($custom_template) {
            return $custom_template;
        }
        
        // Use plugin template
        $plugin_template = plugin_dir_path(__FILE__) . '../templates/single-dz_event-minimal.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}
add_filter('template_include', 'dz_single_event_template');

// Basic archive template handling
function dz_archive_event_template($template) {
    if (is_post_type_archive('dz_event')) {
        $custom_template = locate_template(array('archive-dz_event.php'));
        if ($custom_template) {
            return $custom_template;
        }
        
        // Use plugin template
        $plugin_template = plugin_dir_path(__FILE__) . '../templates/archive-dz_event-minimal.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}
add_filter('template_include', 'dz_archive_event_template');

// Basic ICS download functionality
function dz_event_ics_download() {
    if (!isset($_GET['action']) || $_GET['action'] !== 'dz_event_ics') {
        return;
    }
    
    if (!wp_verify_nonce($_GET['nonce'], 'dz_events_ics_nonce')) {
        wp_die('Security check failed');
    }
    
    $event_id = intval($_GET['event_id']);
    $event = get_post($event_id);
    
    if (!$event || $event->post_type !== 'dz_event') {
        wp_die('Event not found');
    }
    
    $start_date = get_post_meta($event_id, '_dz_event_start', true);
    $end_date = get_post_meta($event_id, '_dz_event_end', true);
    $location = get_post_meta($event_id, '_dz_event_location', true);
    
    $ics_content = "BEGIN:VCALENDAR\r\n";
    $ics_content .= "VERSION:2.0\r\n";
    $ics_content .= "PRODID:-//Zeen Events//Event Calendar//EN\r\n";
    $ics_content .= "BEGIN:VEVENT\r\n";
    $ics_content .= "UID:" . $event_id . "@" . get_site_url() . "\r\n";
    $ics_content .= "DTSTART:" . date('Ymd\THis\Z', strtotime($start_date)) . "\r\n";
    if ($end_date) {
        $ics_content .= "DTEND:" . date('Ymd\THis\Z', strtotime($end_date)) . "\r\n";
    }
    $ics_content .= "SUMMARY:" . $event->post_title . "\r\n";
    $ics_content .= "DESCRIPTION:" . strip_tags($event->post_content) . "\r\n";
    if ($location) {
        $ics_content .= "LOCATION:" . $location . "\r\n";
    }
    $ics_content .= "END:VEVENT\r\n";
    $ics_content .= "END:VCALENDAR\r\n";
    
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="event-' . $event_id . '.ics"');
    echo $ics_content;
    exit;
}
add_action('init', 'dz_event_ics_download');
