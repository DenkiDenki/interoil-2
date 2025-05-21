<?php
global $interoil_db_news_version;
$interoil_db_news_version = '1.0';

function interoil_news_install() {
    global $interoil_db_news_version;

    $installed_ver = get_option('interoil_db_version');

    if ($installed_ver != $interoil_db_news_version) {
        create_db_news();
        update_option('interoil_db_news_version', $interoil_db_news_version);
    }
    
    add_option('interoil_db_news_version', $interoil_db_news_version);
}

function create_db_news() {
    global $interoil_db_news_version, $wpdb, $charset_collate;
    $charset_collate = $wpdb->get_charset_collate();

    $table_news = $wpdb->prefix . "interoil_news";
    // Incluir las funciones de actualización de base de datos de WordPress
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $sql_news = "CREATE TABLE $table_news (
        id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        published_date VARCHAR(50) NULL,
        title VARCHAR(150) NOT NULL,
        location_url VARCHAR(150) NULL,
        permalink TEXT NOT NULL,
        content TEXT NULL,
        KEY title (title)
    ) $charset_collate;";
    dbDelta($sql_news);

}

register_activation_hook(__FILE__, 'interoil_news_install');

/**
 * from ajax
 */
function save_news_ajax() {
    global $wpdb;
    $table_news = $wpdb->prefix . "interoil_news";

    error_log("Nonce recibido: " . ($_POST['security'] ?? 'NULL'));
   
    // Verificar nonce
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'interoil-news')) {
        wp_send_json_error(['message' => 'Nonce inválido.']);
        wp_die();
    }

    // Verificar datos
    if (!isset($_POST['news']) || empty($_POST['news'])) {
        wp_send_json_error(['message' => 'Datos no recibidos - NEWS.']);
        wp_die();
    }

    $newPosts = json_decode(stripslashes($_POST['news']), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error(['message' => 'JSON news mal formado.']);
        wp_die();
    }

    $response = [
        'status' => 'ok',
        'saved' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    // Log en error_log para verificar desde PHP
    error_log(print_r("Nuevos post desde", true));
    error_log(print_r($newPosts, true));

    if (is_array($newPosts)) {

        foreach ($newPosts as $post) {
            $title = sanitize_text_field($post['title']);
            $link = esc_url_raw($post['link']);
            $date = sanitize_text_field($post['date']);
            $slug = sanitize_title($title);
            $permalink = home_url('/news/' . $slug);
            $post_content = sanitize_textarea_field($post['post_body']);

            $existe = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_news WHERE location_url = %s",
                $link
            ));

            if ($existe == 0) {
                $inserted = $wpdb->insert($table_news, [
                    'title' => $title,
                    'location_url' => $link,
                    'published_date' => $date,
                    'permalink' => $permalink,
                    'content' => $post_content,
                ]);

                if ($inserted !== false) {
                    $response['saved']++;
                } else {
                    $response['errors'][] = "Error al insertar la noticia: $title";
                }
            } else {
                $response['skipped']++;
            }
        }

        require_once plugin_dir_path(__FILE__) . 'read-and-store-news.php';
        interoil_read_and_store_news($newPosts);

        // Devolver respuesta a JS
        wp_send_json_success([
            'message' => 'Datos recibidos correctamente.',
            'datos'   => $newPosts,
            'response' => $response
        ]);
        
    } else {
        wp_send_json_error(['message' => 'Datos no válidos o JSON - news mal formado.']);
    }

    wp_die();
}

add_action('wp_ajax_guardar_news', 'save_news_ajax');
add_action('wp_ajax_nopriv_guardar_news', 'save_news_ajax');