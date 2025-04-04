<?php
/*
Plugin Name:  Interoil Reports
Version: 1.0
Description: Output a list of reports from Interoil.
Author: Denisa Gerez
Author URI: https://github.com/DenkiDenki/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: interoil-reports
*/
 
// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente
}

/********** */
/**
 * Activate the plugin.
 */
function plugin_interoil_reports_activate() { 
	// Trigger our function that registers the custom post type plugin.
	//pluginprefix_setup_post_type(); 
	// Clear the permalinks after the post type has been registered.
	flush_rewrite_rules(); 
}
register_activation_hook( __FILE__, 'plugin_interoil_reports_activate' );


/**
 * Deactivate the plugin.
 * This function is called when the plugin is deactivated.
 * It unregisters the custom post type and flushes the rewrite rules.
 *
 * @return void
 */

function plugin_interoil_reports_deactivate() {
	// Unregister the post type, so the rules are no longer in memory.
	//unregister_post_type( 'book' );
	// Clear the permalinks to remove our post type's rules from the database.
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'plugin_interoil_reports_deactivate' );

/**
 * Shortcode: [interoil_reports api_url='' reports_num='']
 * Generates an accordion widget of reports from an API.
 */
function interoil_reports_shortcode($atts) {
    // Default attributes
    $atts = shortcode_atts(
        array(
            'api_url' => 'https://rss.globenewswire.com/Hexmlreportfeed/organization/dBwf4frPXJHvuGJ2iT_UgA==/',
            'reports_num' => 10,
        ),
        $atts,
        'interoil_reports'
    );

    // Validate API URL
    if (empty($atts['api_url']) || !filter_var($atts['api_url'], FILTER_VALIDATE_URL)) {
        return 'Invalid API URL.';
    }

    // Validate number of reports
    if (!is_numeric($atts['reports_num']) || $atts['reports_num'] <= 0) {
        return 'Invalid number of reports.';
    }

    $output = '
    <div class="interoil-reports">
        <style>
        .reports-container h3 {
            color: #1C6C8E;
            text-align: left;
            padding: 5px;
            margin: 0;
        }
    
        .reports-container h2 {
            color: #1C6C8E;
            text-align: left;
            margin: 0;
            padding: 10px;
        }
    
        #listNews {
            color: #1C6C8E;
        }
    
        .reports-container a {
            color: #1C6C8E;
            text-decoration: none;
        }
    
        .reports-container a:hover {
            text-decoration: underline;
        }
    
        .reports-container {
            margin: auto;
            background: white;
            padding: 20px;
        }
    
        .reports-container table {
            width: 70%;
            border-collapse: collapse;
            font-weight: 300;
            font-family: "Acumin", Sans-serif;
        }
    
        th, td {
            text-align: left;
            padding: 8px!important;
            border: none!important;
        }
    
        th {
            color: #1C6C8E;
        }
    
        td:first-child {
            min-width: 70%;
        }
    
        table tbody>tr:nth-child(odd)>td {
            background-color: #fff!important;
        }
    
        @media (max-width: 600px) {
            td, th {
                display: block;
                width: 100%;
            }
        }
        </style>
    
        <div class="reports-container">
            <h3>Financial Calendar</h3>
            <h2>Interoil Exploration and Production ASAs financial calendar for 2024</h2>
            <table>
                <thead>
                    <tr>
                        <th>EVENT</th>
                        <th>DATE</th>
                    </tr>
                </thead>
                <tbody id="listNews">
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
    
                                    const tr = document.createElement("tr");
                                    tr.innerHTML = `<td><a href="${locationHref}">${headline1}</a></td><td>${date}</td>`;
                                    document.getElementById("listNews").appendChild(tr);
                                }
                            } catch (error) {
                                console.error("Error al obtener el XML:", error);
                            }
                        }
    
                        obtenerYConvertirXML();
                    </script>
                </tbody>
            </table>
        </div>
    </div>';
    

    return $output;
}

function reports_init(){
    // Register shortcode
    add_shortcode('interoil_reports', 'interoil_reports_shortcode');
}
add_action( 'init', 'reports_init' );