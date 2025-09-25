<?php
/**
 * Plugin Name: Zeen Events
 * Plugin URI: https://designzeen.com
 * Description: Enterprise-grade event management plugin with advanced features including registration system, international & South African payment gateways (PayFast, Yoco, Ozow, etc.), analytics, multi-site support, and innovative Elementor widgets.
 * Version: 2.0.0
 * Author: Ronald @ Design Zeen Agency
 * Author URI: https://designzeen.com  
 * Text Domain: designzeen-events
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: false
 * 
 * Copyright (C) 2024 Design Zeen Agency
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Define plugin constants
define( 'DZ_EVENTS_VERSION', '2.0.0' );
define( 'DZ_EVENTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DZ_EVENTS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

// Load plugin textdomain for translations
function dz_events_load_textdomain() {
    load_plugin_textdomain(
        'designzeen-events',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages'
    );
}
add_action('plugins_loaded', 'dz_events_load_textdomain');

// Check WordPress and PHP version compatibility
function dz_events_check_requirements() {
    global $wp_version;
    
    // Check WordPress version
    if ( version_compare( $wp_version, '5.0', '<' ) ) {
        add_action( 'admin_notices', 'dz_events_wp_version_notice' );
        return false;
    }
    
    // Check PHP version
    if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
        add_action( 'admin_notices', 'dz_events_php_version_notice' );
        return false;
    }
    
    return true;
}

// WordPress version notice
function dz_events_wp_version_notice() {
    echo '<div class="notice notice-error"><p>';
    echo '<strong>Zeen Events</strong> requires WordPress 5.0 or higher. ';
    echo 'You are running WordPress ' . get_bloginfo( 'version' ) . '. ';
    echo 'Please update WordPress to use this plugin.';
    echo '</p></div>';
}

// PHP version notice
function dz_events_php_version_notice() {
    echo '<div class="notice notice-error"><p>';
    echo '<strong>Zeen Events</strong> requires PHP 7.4 or higher. ';
    echo 'You are running PHP ' . PHP_VERSION . '. ';
    echo 'Please contact your hosting provider to update PHP.';
    echo '</p></div>';
}

// Check requirements before loading plugin
if ( ! dz_events_check_requirements() ) {
    return;
}

// Essential includes only - Load core functionality first
require_once plugin_dir_path(__FILE__) . 'includes/post-types.php';
require_once plugin_dir_path(__FILE__) . 'includes/meta-boxes.php';
require_once plugin_dir_path(__FILE__) . 'includes/card-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/enqueue.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/single-event.php';
require_once plugin_dir_path(__FILE__) . 'includes/blocks.php';
require_once plugin_dir_path(__FILE__) . 'includes/custom-fields.php';

// Load advanced features only if needed (commented out to prevent errors)
// require_once plugin_dir_path(__FILE__) . 'includes/class-core.php';
// require_once plugin_dir_path(__FILE__) . 'includes/class-database-manager.php';
// require_once plugin_dir_path(__FILE__) . 'includes/class-rest-api.php';
// require_once plugin_dir_path(__FILE__) . 'includes/class-performance-optimizer.php';
// require_once plugin_dir_path(__FILE__) . 'includes/class-security-manager.php';
// require_once plugin_dir_path(__FILE__) . 'includes/class-analytics-engine.php';
// require_once plugin_dir_path(__FILE__) . 'includes/class-admin-dashboard.php';
// require_once plugin_dir_path(__FILE__) . 'includes/class-registration-system.php';
// require_once plugin_dir_path(__FILE__) . 'includes/class-payment-gateways.php';
// require_once plugin_dir_path(__FILE__) . 'includes/class-multisite-support.php';
// require_once plugin_dir_path(__FILE__) . 'includes/class-advanced-management.php';
// require_once plugin_dir_path(__FILE__) . 'includes/class-form-integration.php';
// require_once plugin_dir_path(__FILE__) . 'includes/class-template-customizer.php';
// require_once plugin_dir_path(__FILE__) . 'includes/class-shortcode-handler.php';
// require_once plugin_dir_path(__FILE__) . 'includes/elementor.php';
// require_once plugin_dir_path(__FILE__) . 'includes/class-elementor-widgets-advanced.php';
// require_once plugin_dir_path(__FILE__) . 'includes/class-elementor-widgets-unique.php';
// require_once plugin_dir_path(__FILE__) . 'includes/class-setup-wizard.php';
