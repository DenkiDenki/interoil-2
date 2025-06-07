<?php
function interoil_read_and_store_reports($newReports) {
    global $wpdb;
    $table_pdfs = $wpdb->prefix . "interoil_pdfs";
    $table_categories = $wpdb->prefix . "interoil_categories";

    if (!empty($newReports)) {
        $upload_dir = wp_upload_dir();
        $destination_folder = trailingslashit($upload_dir['basedir']) . 'pdfs/reports/';

        if (!file_exists($destination_folder)) {
            wp_mkdir_p($destination_folder);
        }

        foreach ($newReports as $report) {
            $title = sanitize_text_field($report['title']);
            $link = esc_url_raw($report['link']);
            $date = sanitize_text_field($report['date']);
            $category = sanitize_text_field($report['category'] ?? 'Reports and Presentations');

            if (!filter_var($link, FILTER_VALIDATE_URL)) {
                interoil_crear_txt_en_uploads('log-reporte', "❌ Enlace no válido: " . $link);
                continue;
            }

            $headers = get_headers($link, 1);
            if ($headers === false || strpos($headers[0], '200') === false) {
                interoil_crear_txt_en_uploads('log-reporte', "❌ Enlace no accesible: " . $link);
                continue;
            }

            $content_type = $headers['Content-Type'] ?? 'application/octet-stream';
            if (is_array($content_type)) {
                $content_type = end($content_type);
            }

            $extension = get_extension_from_mime($content_type);
            $file_name = sanitize_title($title) . '.' . $extension;
            $full_route = trailingslashit($destination_folder) . $file_name;
            $upload_url = trailingslashit($upload_dir['baseurl']) . 'pdfs/reports/' . $file_name;

            if (file_exists($full_route)) {
                continue;
            }

            if ($content_type !== 'application/pdf') {
                interoil_crear_txt_en_uploads('log-reporte', "⚠️ Descargando archivo no PDF ($content_type): $link");
            }

            $ch = curl_init($link);
            $fp = fopen($full_route, 'w+');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_exec($ch);

            if (curl_errno($ch)) {
                fclose($fp);
                curl_close($ch);
                interoil_crear_txt_en_uploads('log-reporte', "❌ Error al descargar archivo desde: $link");
                continue;
            }

            curl_close($ch);
            fclose($fp);

            $category_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_categories WHERE name = %s",
                $category
            ));

            if (!$category_id) {
                $wpdb->insert($table_categories, [
                    'name'        => $category,
                    'description' => '',
                ]);
                $category_id = $wpdb->insert_id;
            }

            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_pdfs WHERE location_url = %s",
                $link
            ));

            if ($exists == 0) {
                $wpdb->insert($table_pdfs, [
                    'file_name'      => $file_name,
                    'location_url'   => $link,
                    'published_date' => $date,
                    'upload_dir'     => $upload_url,
                    'category_id'    => $category_id,
                ]);
            } else {
                interoil_crear_txt_en_uploads('log-reporte', "⚠️ Ya existe en la base de datos: $link");
            }
        }
    } else {
        interoil_crear_txt_en_uploads('log-reporte', "⚠️ No se recibieron datos válidos.");
    }
}

function get_extension_from_mime($mime_type) {
    $mime_map = [
        'application/pdf'  => 'pdf',
        'image/jpeg'       => 'jpg',
        'image/png'        => 'png',
        'text/plain'       => 'txt',
        'application/zip'  => 'zip',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/json' => 'json',
        'text/html'        => 'html',
    ];

    return $mime_map[$mime_type] ?? 'bin';
}

function convert_name_to_slug_pdf($original_name) {
    $slug = sanitize_title($original_name); 
    return $slug . '.pdf';
}


function interoil_crear_txt_en_uploads($file_name, $content) {
    $upload_dir = wp_upload_dir();
    $target_dir = trailingslashit($upload_dir['basedir']) . 'pdfs/';

    if (!file_exists($target_dir)) {
        wp_mkdir_p($target_dir);
    }

    $path_file = trailingslashit($target_dir) . sanitize_file_name($file_name) . '.txt';

    $content_to_write = "[" . date('Y-m-d H:i:s') . "] " . $content . PHP_EOL;

    $result = file_put_contents($path_file, $content_to_write, FILE_APPEND);

    if ($result === false) {
        error_log("❌ Error al escribir en el archivo de log.");
        return false;
    }

    return "✅ Archivo creado correctamente en: " . $path_file;
}

function interoil_reports_js() {
    
     if ( did_action( 'elementor/frontend/after_register_scripts' ) ) {
        wp_enqueue_script(
            'interoil-reports-xml',
            plugin_dir_url(__FILE__) . 'js/fetch-xml.js',
            ['elementor-frontend', 'jquery'],
            filemtime(plugin_dir_path(__FILE__) . 'js/fetch-xml.js'),
            true
        );

        wp_localize_script('interoil-reports-xml', 'reports_object', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('interoil-reports')
        ]);

    }
}
add_action('wp_enqueue_scripts', 'interoil_reports_js', 20);

function interoil_fetch_and_parse_xml() {
    $url = 'https://rss.globenewswire.com/Hexmlreportfeed/organization/dBwf4frPXJHvuGJ2iT_UgA==/';
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        interoil_crear_txt_en_uploads('log-reporte', '❌ Error al obtener el XML: ' . $response->get_error_message());
        return [];
    }

    $xml = wp_remote_retrieve_body($response);

    if (empty($xml)) {
        interoil_crear_txt_en_uploads('log-reporte', '❌ XML vacío.');
        return [];
    }
    
    $upload_dir = wp_upload_dir();
    $target_dir = trailingslashit($upload_dir['basedir']) . 'pdfs/';

    if (!file_exists($target_dir)) {
        wp_mkdir_p($target_dir);
    }

	$tempLocalPath = $target_dir . "/data.xml";
	file_put_contents($tempLocalPath, $xml);
    
    $xml = file_get_contents($tempLocalPath);

    if (!$xml) {
        interoil_crear_txt_en_uploads('log-reporte', '❌ No se pudo parsear el XML.');
        return [];
    }
	
	preg_match_all('/<report([^>]*)>(.*?)<\/report>/s', $xml, $reports, PREG_SET_ORDER);
    if (empty($reports)) {
        interoil_crear_txt_en_uploads('log-reporte', '❌ No se encontraron reportes en el XML.');
        return [];
    }
	
	$newReports = [];
	foreach ($reports as $file) {
        $attrString = $file[1];  // atributos dentro de <report ...>
        $content = $file[2];     // contenido entre <report> ... </report>
    
        // Extraer atributos del string $attrString
        preg_match_all('/(\w+)\s*=\s*"([^"]*)"/', $attrString, $attrs, PREG_SET_ORDER);
        $attributes = [];
        foreach ($attrs as $attr) {
            $attributes[$attr[1]] = $attr[2];
        }
        
        // Extraer contenido de <file_headline><![CDATA[...]]></file_headline> dentro de $content
        preg_match('/<file_headline><!\[CDATA\[(.*?)\]\]><\/file_headline>/', $content, $headlineMatch);
        $fileHeadline = $headlineMatch[1] ?? null;

        //<published date="2025-04-28T08:09:29 CEST" />
        //<link href="https://ml-eu.globenewswire.com/Resource/Download/4076aaed-a0b8-4cf5-8922-683fd92fcfd5" />
        preg_match('/<published\s+date="([^"]+)"\s*\/>/', $content, $publishedMatch);
        $publishedDate = $publishedMatch[1] ?? null;
        $date = explode("T", $publishedDate)[0];

        preg_match('/<link\s+href="([^"]+)"\s*\/>/', $content, $linkMatch);
        $linkHref = $linkMatch[1] ?? null;

        $newReports[] = [
            'title' => $fileHeadline,
            'link'  => $linkHref,
            'date'  => $date,
        ];

	}
	
	interoil_crear_txt_en_uploads('log-reporte', print_r($newReports, true));

    return $newReports;
}

//cron call back
function interoil_cron_task() {
    // 🔹 Obtener reports como en JS pero desde PHP
    $newReports = interoil_fetch_and_parse_xml();

    // 🔹 Procesarlos
    interoil_read_and_store_reports($newReports);

    // 🔹 Registrar que se ejecutó
    interoil_crear_txt_en_uploads('log-reporte', "✅ Cron ejecutado correctamente con " . count($newReports) . " reportes.");
}

/**  */
add_action('wp_ajax_guardar_reports', 'interoil_handle_ajax_guardar_reports');
add_action('wp_ajax_nopriv_guardar_reports', 'interoil_handle_ajax_guardar_reports');

function interoil_handle_ajax_guardar_reports() {
    check_ajax_referer('interoil-reports', 'security');

    $json_data = stripslashes($_POST['datos'] ?? '');
    $newReports = json_decode($json_data, true);

    if (empty($newReports) || !is_array($newReports)) {
        wp_send_json_error('❌ Datos inválidos.');
        return;
    }

    interoil_read_and_store_reports($newReports);

    wp_send_json_success('✅ Reports procesados correctamente.');
}