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
 * [interoil_reports] returns a accordeon widget of reports.
 * @return string Current Year
*/
add_shortcode( 'interoil_reports', 'interoil_reports_shortcode' );
function reports_init(){
// Add Shortcode
function interoil_reports_shortcode( $atts ) {

	// Attributes
	$atts = shortcode_atts(
		array(
			'api_url' => 'https://rss.globenewswire.com/Hexmlreportfeed/organization/dBwf4frPXJHvuGJ2iT_UgA==/',
			'reports_num' => '10',
		),
		$atts,
		'interoil_reports'
	);

	// Return 
	return interoil_reports_func( $atts['api_url'] );
 }
}
add_action('init', 'reports_init');

?>
