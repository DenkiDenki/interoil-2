<?php
function interoil_template_redirect() {
    if (get_query_var('interoil_news_permalink')) {
        global $wpdb;
        error_log('✅ Entrando a template_redirect con permalink: ' . get_query_var('interoil_news_permalink'));
        $permalink = sanitize_text_field(get_query_var('interoil_news_permalink'));
        $table_news = $wpdb->prefix . "interoil_newsposts";

        $news = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_news WHERE permalink = %s", $permalink));

        if ($news) {
            get_header(); ?>
            <div class="news-single">
                <h1><?php echo esc_html($news->title); ?></h1>
                <p><em><?php echo esc_html($news->published_date); ?></em></p>
                <div class="news-content">
                    <?php echo wp_kses_post(wpautop($news->content)); ?>
                </div>
                <p><a href="<?php echo esc_url($news->location_url); ?>" target="_blank">more</a></p>
            </div>
            <?php
            get_footer();
            exit;
        } else {
            interoil_crear_txt_en_uploads('log-reporte', "❌ Noticia no encontrada: $permalink - $news");
            wp_redirect(home_url('/404'));
            exit;
        }
    }
}
add_action('template_redirect', 'interoil_template_redirect');