<?php
global $interoil_db_version;
$interoil_db_version = '1.0';

function interoil_install() {
    global $wpdb;

    $table_name = $wpdb->prefix . "interoil_pdfs";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT(10) NOT NULL AUTO_INCREMENT,
        published_date VARCHAR(50) NOT NULL,
        file_name VARCHAR(150) NOT NULL,
        category VARCHAR(50) NOT NULL,
        location_url VARCHAR(150) NOT NULL,
        upload_dir VARCHAR(150) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    add_option('interoil_db_version', $interoil_db_version);
}

function interoil_create_upload_folder() {
    global $wpdb;

    $upload_dir = wp_upload_dir();
    $target_dir = $upload_dir['basedir'] . '/pdfs/';

    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    /*
    $table_name = $wpdb->prefix . "interoil_pdfs";

    $wpdb->insert( 
        $table_name, 
        array( 
            'headline' => $headline,
            'location_url' => $location_url, 
            'published_date' => $published_date, 
        ) 
    );
    */

    /*
    $api_url = "https://rss.globenewswire.com/Hexmlreportfeed/organization/dBwf4frPXJHvuGJ2iT_UgA==/";

    $xml_content = file_get_contents($api_url);
    if ($xml_content === false) {
        die("Error al obtener el XML.");
    }

    $xml = simplexml_load_string($xml_content);
    if ($xml === false) {
        die("Error al analizar el XML.");
    }

    foreach ($xml->report as $report) {
        $headline = trim((string)$report->file_headline);
        $location_url = trim((string)$report->location['href']);
        $published_date = trim((string)$report->published['date']);

        // Descargar el archivo PDF
        $file_name = basename($location_url);
        $file_path = $target_dir . $file_name;
        file_put_contents($file_path, file_get_contents($location_url));

        // Insertar en la base de datos
        $wpdb->insert( 
            $table_name, 
            array( 
                'published_date' => $published_date,
                'file_name' => $file_name,
                'location_url' => $location_url,
                'upload_dir' => $target_dir,
            ) 
        );
    }
    */
}

register_activation_hook(__FILE__, 'interoil_install');
register_activation_hook(__FILE__, 'interoil_create_upload_folder');
