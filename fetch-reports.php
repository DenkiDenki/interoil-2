<?php

function interoil_create_upload_folder() {
    $upload_dir = wp_upload_dir();
    $target_dir = trailingslashit($upload_dir['basedir']) . 'pdfs/';

    if (!file_exists($target_dir)) {
        wp_mkdir_p($target_dir);
    }
}
register_activation_hook(__FILE__, 'interoil_create_upload_folder');


function interoil_fetch_and_store_reports($contenido) {
/*// Un solo reporte - agregar
    $url = "https://ml-eu.globenewswire.com/Resource/Download/4076aaed-a0b8-4cf5-8922-683fd92fcfd5";
    // Obtener nombre desde la URL o generar uno
    $nombreArchivo = basename(parse_url($url, PHP_URL_PATH)) ?: 'reporte_' . time() . '.pdf';
    */

    // Usamos la carpeta de uploads de WordPress
    $upload_dir = wp_upload_dir();
    $carpeta_destino = trailingslashit($upload_dir['basedir']) . 'pdfs/';

    // Crear carpeta si no existe
    if (!file_exists($carpeta_destino)) {
        wp_mkdir_p($carpeta_destino);
    }
$nombreArchivo = "reporte_" . time() . ".pdf";
    // URL del archivo a descargar
    // Ruta completa para guardar
    $ruta_completa = trailingslashit($carpeta_destino) . $nombreArchivo;
/* descargar de forma individual - agregar
    // Descargar con cURL
    $ch = curl_init($url);
    $fp = fopen($ruta_completa, 'w+');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_exec($ch);

    // Validación de errores
    if (curl_errno($ch)) {
        fclose($fp);
        return interoil_crear_txt_en_uploads('log-reporte', "No se pudo descargar.");
    }

    curl_close($ch);
    fclose($fp);
*/
    // Guardar en log txt
    //return interoil_crear_txt_en_uploads('log-reporte', "Se creó el archivo: " . $ruta_completa . $contenido );
    return interoil_crear_txt_en_uploads('log-reporte', "Se creó el archivo: " . $ruta_completa . "\nContenido: " . print_r($contenido, true));

}

function interoil_crear_txt_en_uploads($file_name, $content) {
    // Obtener la ruta base del directorio de uploads
    $upload_dir = wp_upload_dir();
    $target_dir = trailingslashit($upload_dir['basedir']) . 'pdfs/';

    /* probar o borrar
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
*/
    if (!file_exists($target_dir)) {
        wp_mkdir_p($target_dir);
    }

    // Ruta completa del archivo
    $path_file = trailingslashit($target_dir) . sanitize_file_name($file_name) . '.txt';

    // Crear y escribir el archivo
    $result = file_put_contents($path_file, $content);

    if ($result === false) {
        error_log("❌ Error al crear el archivo.");
        return;
    }

    return "✅ Archivo creado correctamente en: " . $path_file;
}

//add_action('init', 'reports_init');
function interoil_js() {
    wp_enqueue_script('mi-script-xml', plugin_dir_url(__FILE__) . 'js/get-xml.js', [], filemtime(plugin_dir_path(__FILE__) . 'js/mi-script.js'), true);
    
    wp_localize_script('mi-script-xml', 'my_ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('mi_nonce_seguro')
    ]);
}
add_action('wp_enqueue_scripts', 'interoil_js');