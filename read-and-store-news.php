<?php
function interoil_read_and_store_news($newPosts) {
    global $wpdb;
    $table_news = $wpdb->prefix . "interoil_news";

    if (!empty($newPosts)) {
        //$upload_dir = wp_upload_dir();
        //$destination_folder = trailingslashit($upload_dir['basedir']) . 'pdfs/reports/';

        foreach ($newPosts as $report) {
            $title = sanitize_text_field($report['title']);
            $link = esc_url_raw($report['link']);
            $date = sanitize_text_field($report['date']);
            $content = sanitize_textarea_field($report['post_body'] ?? '');
            interoil_crear_txt_en_uploads('log-reporte', "Título: $title, Enlace: $link, Fecha: $date", "Contenido: $content");
        }
    } else {
        interoil_crear_txt_en_uploads('log-reporte', "⚠️ No se recibieron datos válidos.");
    }
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