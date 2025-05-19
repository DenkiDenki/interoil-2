<?php
function interoil_read_and_store_reports($newReports) {
    global $wpdb;
    $table_pdfs = $wpdb->prefix . "interoil_pdfs";
    $table_categories = $wpdb->prefix . "interoil_categories";

    if (!empty($newReports)) {
        $upload_dir = wp_upload_dir();
        $destination_folder = trailingslashit($upload_dir['basedir']) . 'pdfs/reports/';

        foreach ($newReports as $report) {
            $title = sanitize_text_field($report['title']);
            $link = esc_url_raw($report['link']);
            $date = sanitize_text_field($report['date']);
            $category = sanitize_text_field($report['category'] ?? 'Reports and Presentations');

            if (!file_exists($destination_folder)) {
                wp_mkdir_p($destination_folder);
            }

            $file_name = convert_name_to_slug_pdf($title);
            $full_route = trailingslashit($destination_folder) . $file_name;
            $upload_url = trailingslashit($upload_dir['baseurl']) . 'pdfs/reports/' . $file_name;

            if (file_exists($full_route)) {
                interoil_crear_txt_en_uploads('log-reporte', "⚠️ El archivo ya existe: " . $full_route);
                continue;
            }

            if (!filter_var($link, FILTER_VALIDATE_URL)) {
                interoil_crear_txt_en_uploads('log-reporte', "❌ Enlace no válido: " . $link);
                continue;
            }

            $headers = get_headers($link, 1);
            if ($headers === false || strpos($headers[0], '200') === false) {
                interoil_crear_txt_en_uploads('log-reporte', "❌ Enlace no accesible: " . $link);
                continue;
            }

            if (!isset($headers['Content-Type']) || strpos($headers['Content-Type'], 'application/pdf') === false) {
                interoil_crear_txt_en_uploads('log-reporte', "❌ El enlace no apunta a un PDF: " . $link);
                continue;
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

            $real_mime = mime_content_type($full_route);
            if ($real_mime !== 'application/pdf') {
                interoil_crear_txt_en_uploads('log-reporte', "❌ Archivo descargado no es un PDF: $real_mime");
                unlink($full_route);
                continue;
            }

            $category_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_categories WHERE name = %s",
                $category
            ));

            if (!$category_id) {
                $wpdb->insert($table_categories, [
                    'name' => $category,
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
                interoil_crear_txt_en_uploads('log-reporte', "✅ Reporte guardado: $title");
            } else {
                interoil_crear_txt_en_uploads('log-reporte', "⚠️ Ya existe en la base de datos: $link");
            }
        }
    } else {
        interoil_crear_txt_en_uploads('log-reporte', "⚠️ No se recibieron datos válidos.");
    }
}

/**
 * Convierte el nombre a un slug para el archivo PDF.
 */
function convert_name_to_slug_pdf($original_name) {
    $slug = sanitize_title($original_name); 
    return $slug . '.pdf';
}

/**
 * Crea un archivo TXT en el directorio de uploads.
 */
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

/**
 * Encola un script JS y pasa datos al mismo.
 */
function interoil_reports_js() {
    wp_enqueue_script(
        'interoil-reports-xml',
        plugin_dir_url(__FILE__) . 'js/fetch-xml.js',
        [],
        filemtime(plugin_dir_path(__FILE__) . 'js/fetch-xml.js'),
        true
    );

    wp_localize_script('interoil-reports-xml', 'reports_object', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('interoil-reports')
    ]);

}
add_action('wp_enqueue_scripts', 'interoil_reports_js');