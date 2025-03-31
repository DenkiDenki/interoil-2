<?php
/*
Plugin Name:  Salcodes
Version: 1.0
Description: Output the current year in your WordPress site.
Author: Salman Ravoof
Author URI: https://www.salmanravoof.com/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: salcodes
*/
/*require_once __DIR__ . '/classes/ExampleClass.php';

$example = new ExampleClass();
$example->run(); // Assuming run() is a method in ExampleClass that starts the application

*/
/**
 * [current_year] returns the Current Year as a 4-digit string.
 * @return string Current Year
*/

add_shortcode( 'current_year', 'salcodes_year' );
function salcodes_init(){
 function salcodes_year() {
 return getdate()['year'];
 }
}
add_action('init', 'salcodes_init');

/** Always end your PHP files with this closing tag */

add_shortcode( 'interoil_reports', 'interoil_reports_func' );
function reports_init(){
 function interoil_reports_func() {
 return getdate()['year'];
 }
}
add_action('init', 'reports_init');
?>

?>