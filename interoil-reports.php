<?php
/**
 * Plugin Name:  Interoil Reports
 * Version: 1.0.29
 * Description: Output a list of reports from Interoil.
 * Author: Denisa Gerez
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: interoil-reports. This plugin provides functionalities to fetch, view, and storage reports
 * for Interoil operations. It is designed to integrate seamlessly with
 * WordPress.
*/

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'db-setup.php';
require_once plugin_dir_path(__FILE__) . 'fetch-reports.php';
require_once plugin_dir_path(__FILE__) . 'shortcode-display.php';

function plugin_interoil_reports_activate() {
    interoil_install();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'plugin_interoil_reports_activate');

function plugin_interoil_reports_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'plugin_interoil_reports_deactivate');