<?php
/**
 * Manejo AJAX para guardar news
 */
function save_news_ajax() {
    check_ajax_referer('interoil-news', 'security');

    if (empty($_POST['news'])) {
        wp_send_json_error(['message' => 'Datos no recibidos - NEWS.']);
        wp_die();
    }

    $newPosts = json_decode(stripslashes($_POST['news']), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error(['message' => 'JSON news mal formado.']);
        wp_die();
    }

    $response = [
        'status'  => 'ok',
        'saved'   => 0,
        'skipped' => 0,
        'errors'  => [],
    ];

    foreach ($newPosts as $post) {
        $result = interoil_insert_news_item($post);

        if ($result['status'] === 'saved') {
            $response['saved']++;
        } elseif ($result['status'] === 'skipped') {
            $response['skipped']++;
        } else {
            $response['errors'][] = $result['message'];
        }
    }

    wp_send_json_success([
        'message'  => 'Datos procesados correctamente.',
        'response' => $response
    ]);

    wp_die();
}

add_action('wp_ajax_guardar_news', 'save_news_ajax');
add_action('wp_ajax_nopriv_guardar_news', 'save_news_ajax');

/**
 * Inserta una noticia, loguea y devuelve el resultado
 */
function interoil_insert_news_item($data) {
    global $wpdb;
    $table_news = $wpdb->prefix . "interoil_newsposts";

    $title   = sanitize_text_field($data['title'] ?? '');
    $link    = esc_url_raw($data['link'] ?? '');
    $date    = sanitize_text_field($data['date'] ?? '');
    $content = sanitize_textarea_field($data['post_body'] ?? '');

    if (empty($title) || empty($link)) {
        return ['status' => 'error', 'message' => "Faltan datos en la noticia."];
    }

    $permalink = sanitize_title($title);

    // Log básico
    interoil_crear_txt_en_uploads('log-reporte', "Título: $title, Enlace: $link, Fecha: $date", "Contenido: $content");

    $existe = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_news WHERE location_url = %s",
        $link
    ));

    if ($existe > 0) {
        interoil_crear_txt_en_uploads('log-reporte', "⏩ Noticia ya existente, se omite: $title");
        return ['status' => 'skipped'];
    }

    $inserted = $wpdb->insert(
        $table_news,
        [
            'published_date' => $date,
            'title'          => $title,
            'location_url'   => $link,
            'permalink'      => $permalink,
            'content'        => $content
        ],
        ['%s', '%s', '%s', '%s', '%s']
    );

    if ($inserted === false) {
        interoil_crear_txt_en_uploads('log-reporte', "❌ Error al insertar noticia: $title - Error: " . $wpdb->last_error);
        return ['status' => 'error', 'message' => "Error al insertar $title"];
    }

    interoil_crear_txt_en_uploads('log-reporte', "✅ Noticia guardada: $title");
    return ['status' => 'saved'];
}

/**
 * Encola un script JS y pasa datos al mismo.
 */
function interoil_news_js() {
    
    wp_enqueue_script(
        'interoil-news-xml',
        plugin_dir_url(__FILE__) . 'js/fetch-news.js',
        [],
        filemtime(plugin_dir_path(__FILE__) . 'js/fetch-news.js'),
        true
    );

    wp_localize_script('interoil-news-xml', 'news_object', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('interoil-news')
    ]);
}
add_action('wp_enqueue_scripts', 'interoil_news_js');

function interoil_add_rewrite_rules() {
    add_rewrite_rule('^news/([^/]+)/?', 'index.php?interoil_news_permalink=$matches[1]', 'top');
}
add_action('init', 'interoil_add_rewrite_rules');

function interoil_add_query_vars($vars) {
    $vars[] = 'interoil_news_permalink';
    return $vars;
}
add_filter('query_vars', 'interoil_add_query_vars');