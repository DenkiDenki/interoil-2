<?php
function interoil_fetch_and_store_reports($newReports) {
    if (!empty($newReports)) {
        // Obtener la ruta base del directorio de uploads
        $upload_dir = wp_upload_dir();
        $destination_folder = trailingslashit($upload_dir['basedir']) . 'pdfs/reports/';

        foreach ($newReports as $report) {
            $title = sanitize_text_field($report['title']);
            $link = esc_url_raw($report['link']);
            $date = sanitize_text_field($report['date']);

            // Crear la carpeta si no existe
            if (!file_exists($destination_folder)) {
                wp_mkdir_p($destination_folder);
            }

            // Nombre del archivo PDF
            $file_name = convert_name_to_slug_pdf($title);
            // Ruta completa para guardar el archivo PDF
            $full_route = trailingslashit($destination_folder) . $file_name;

            // Verificar si el archivo ya existe
            if (file_exists($full_route)) {
                interoil_crear_txt_en_uploads('log-reporte', "El archivo ya existe: " . $full_route);
                continue;
            }

            // Verificar si el enlace es válido y accesible
            if (!filter_var($link, FILTER_VALIDATE_URL)) {
                interoil_crear_txt_en_uploads('log-reporte', "El enlace no es válido: " . $link);
                continue;
            }

            // Verificar si el enlace es accesible
            $headers = get_headers($link, 1);
            if ($headers === false || strpos($headers[0], '200') === false) {
                interoil_crear_txt_en_uploads('log-reporte', "El enlace no es accesible: " . $link);
                continue;
            }

            // Verificar si el enlace es un PDF
            if (!isset($headers['Content-Type']) || strpos($headers['Content-Type'], 'application/pdf') === false) {
                interoil_crear_txt_en_uploads('log-reporte', "El enlace no es un PDF: " . $link);
                continue;
            }

            // Descargar con cURL
            $ch = curl_init($link);
            $fp = fopen($full_route, 'w+');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_exec($ch);

            // Validación de errores
            if (curl_errno($ch)) {
                fclose($fp);
                interoil_crear_txt_en_uploads('log-reporte', "No se pudo descargar.");
                continue;
            }

            curl_close($ch);
            fclose($fp);
    

            $real_mime = mime_content_type($full_route);
            if ($real_mime !== 'application/pdf') {
                interoil_crear_txt_en_uploads('log-reporte', "⚠️ Archivo no es PDF real, tipo detectado: $real_mime");
                unlink($full_route);
                continue;
            }

            interoil_crear_txt_en_uploads('log-reporte', "✅ Archivo guardado correctamente: " . $full_route);
         }
    } else {
        interoil_crear_txt_en_uploads('log-reporte', "⚠️ No se recibieron datos válidos.");
    }
}

/**
 * Convierte el nombre a un slug para el archivo PDF.
 */
function convert_name_to_slug_pdf($original_name) {
    $slug = sanitize_title($original_name); // Convierte a "mi-informe-importante-2025"
    return $slug . '.pdf';
}

/**
 * Crea un archivo TXT en el directorio de uploads.
 */
function interoil_crear_txt_en_uploads($file_name, $content) {
    // Obtener la ruta base del directorio de uploads
    $upload_dir = wp_upload_dir();
    $target_dir = trailingslashit($upload_dir['basedir']) . 'pdfs/';

    if (!file_exists($target_dir)) {
        wp_mkdir_p($target_dir);
    }

    // Ruta completa del archivo
    $path_file = trailingslashit($target_dir) . sanitize_file_name($file_name) . '.txt';

    // Agregar timestamp para cada entrada y nueva línea
    $content_to_write = "[" . date('Y-m-d H:i:s') . "] " . $content . PHP_EOL;

    // Escribir en modo append
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
function interoil_js() {
    wp_enqueue_script(
        'mi-script-xml',
        plugin_dir_url(__FILE__) . 'js/get-xml.js',
        [],
        filemtime(plugin_dir_path(__FILE__) . 'js/get-xml.js'),
        true
    );

    wp_localize_script('mi-script-xml', 'my_ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('mi_nonce_seguro')
    ]);
}
add_action('wp_enqueue_scripts', 'interoil_js');