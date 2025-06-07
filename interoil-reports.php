<?php
/**
 * Plugin Name:  Interoil Reports
 * Version: 1.0.52
 * Description: This plugin provides functionalities to fetch, view, and storage reports
 * for Interoil operations. It is designed to integrate seamlessly with
 * WordPress.
 * Author: Denisa Gerez
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: interoil-reports
*/

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'db-setup.php';
require_once plugin_dir_path(__FILE__) . 'db-news-setup.php';
require_once plugin_dir_path(__FILE__) . 'read-and-store-reports.php';
require_once plugin_dir_path(__FILE__) . 'read-and-store-news.php';
require_once plugin_dir_path(__FILE__) . 'shortcode-display.php';
require_once plugin_dir_path(__FILE__) . 'shortcode-news.php';
require_once plugin_dir_path(__FILE__) . 'includes/template-redirect.php';

function plugin_interoil_reports_activate() {
    interoil_install();
    interoil_news_install();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'plugin_interoil_reports_activate');

add_action('wp_enqueue_scripts', function() {
    // Elementor debe cargar su frontend config
    if ( did_action( 'elementor/frontend/after_register_scripts' ) ) {
        wp_enqueue_script( 'elementor-frontend' );
    }
});

function plugin_interoil_reports_deactivate() {
    flush_rewrite_rules();
}

/** cron job */
register_activation_hook(__FILE__, 'interoil_activate_cron');
register_deactivation_hook(__FILE__, 'interoil_deactivate_cron');

function interoil_activate_cron() {
    if (!wp_next_scheduled('interoil_hourly_cron_event')) {
        wp_schedule_event(time(), 'hourly', 'interoil_hourly_cron_event');
    }
}

function interoil_deactivate_cron() {
    wp_clear_scheduled_hook('interoil_hourly_cron_event');
}

add_action('interoil_hourly_cron_event', 'interoil_cron_task');