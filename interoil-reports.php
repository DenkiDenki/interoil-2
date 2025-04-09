<?php
/*
Plugin Name:  Interoil Reports
Version: 1.0.12
Description: Output a list of reports from Interoil.
Author: Denisa Gerez
Author URI: https://github.com/DenkiDenki/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: interoil-reports
*/

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Incluir archivos necesarios
require_once plugin_dir_path(__FILE__) . 'db-setup.php';
require_once plugin_dir_path(__FILE__) . 'fetch-reports.php';
require_once plugin_dir_path(__FILE__) . 'shortcode-display.php';

/**
 * Función al activar el plugin
 */
function plugin_interoil_reports_activate() {
    interoil_install();
    interoil_create_upload_folder();
    //interoil_fetch_and_store_reports();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'plugin_interoil_reports_activate');

/**
 * Función al desactivar el plugin
 */
function plugin_interoil_reports_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'plugin_interoil_reports_deactivate');