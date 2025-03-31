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
 * [interoil_reports api_url='' reports_num=''] returns a accordeon widget of reports.
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

function interoil_reports_func(api_url, reports_num){
	echo '<h1>Últimas Noticias</h1> 
    <ul id="listNews"></ul>

    <script>
        async function obtenerYConvertirXML() {
            try {
                const response = await fetch("https://rss.globenewswire.com/Hexmlreportfeed/organization/dBwf4frPXJHvuGJ2iT_UgA==/");
                const xmlText = await response.text();
                const parser = new DOMParser();
                const xmlDoc = parser.parseFromString(xmlText, "application/xml");

                let noticias = xmlDoc.getElementsByTagName("report");

                for (let i = 0; i < noticias.length; i++) {
                    let headline = xmlDoc.getElementsByTagName("file_headline")[i];
                    let headline1 = headline.textContent.trim();
                    let locationNode = xmlDoc.getElementsByTagName("location")[i];
                    let locationHref = locationNode.getAttribute("href");
                    let publishedDate = xmlDoc.getElementsByTagName("published")[i];
                    let dateAndTime = publishedDate.getAttribute("date");
                    let date = dateAndTime.split("T")[0];

                    const li = document.createElement("li");
                    li.innerHTML = `<strong>${headline1}</strong> - <a href="${locationHref}" target="_blank">${locationHref}</a> - ${date}`;
                    document.getElementById("listNews").appendChild(li);
                }

            } catch (error) {
                console.error("Error al obtener el XML:", error);
            }
        }

        obtenerYConvertirXML();
    </script>';
	
}

?>
