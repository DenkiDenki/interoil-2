<?php
global $interoil_db_version;
$interoil_db_version = '1.0';

require_once plugin_dir_path(__FILE__) . 'fetch_reports.php';

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

register_activation_hook(__FILE__, 'interoil_install');

function save_reports_ajax() {
    // 🛠️ Log para verificar si el nonce llegó al servidor
    error_log("Nonce recibido: " . ($_POST['security'] ?? 'NULL'));

    check_ajax_referer('mi_nonce_seguro', 'security');

    global $wpdb;

    $table_name = $wpdb->prefix . "interoil_pdfs";

    $datos_json = $_POST['datos'] ?? '';
    error_log("Datos recibidos (JSON): " . $datos_json);

    $newReports = json_decode(stripslashes($datos_json), true); // array PHP

    if (!empty($newReports)) {
        foreach ($newReports as $report) {
            $title = sanitize_text_field($report['title']);
            $link = esc_url_raw($report['link']);
            $date = sanitize_text_field($report['date']);

            // Verificamos si ya existe la report por enlace o título
            $existe = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE location_url = %s",
                $link
            ));

            if ($existe == 0) {
                $wpdb->insert($table_name, [
                    'file_name' => $title,
                    'location_url' => $link,
                    'published_date' => $date,
                    'upload_dir' => $upload_dir['basedir'] . '/pdfs/reports/',
                    'category' => 'reports',
                ]);
            }
        }

        echo 'Noticias guardadas correctamente.';
    } else {
        echo 'No se recibieron datos válidos.';
    }

    // 🛠️ Verificar si el decode fue exitoso
    if (is_array($newReports)) {
        error_log("✅ Decodificación correcta. Total newReports: " . count($newReports));
    } else {
        error_log("❌ Error al decodificar JSON.");
    }
    interoil_fetch_and_store_reports($newReports);

    wp_die();
}

add_action('wp_ajax_guardar_noticias', 'save_reports_ajax');
add_action('wp_ajax_nopriv_guardar_noticias', 'save_reports_ajax');