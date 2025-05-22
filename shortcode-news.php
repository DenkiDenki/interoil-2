<?php
function interoil_news_shortcode($atts) {
    global $wpdb;
    $table_news = $wpdb->prefix . "interoil_newsposts";
    
    $atts = shortcode_atts(
        array(
            'api_url' => 'https://rss.globenewswire.com/HexmlFeed/organization/dBwf4frPXJHvuGJ2iT_UgA==/',
        ),
        $atts,
        'interoil_news'
    ); 
    if (empty($atts['api_url']) || !filter_var($atts['api_url'], FILTER_VALIDATE_URL)) {
        return 'Invalid API URL.';
    }

    $newsPost = $wpdb->get_results(
        "SELECT * FROM $table_news",
        ARRAY_A
    );

    if (!$newsPost) {
        return "<p>No hay noticias disponibles.</p>";
    }
    
    $newsPost = array_map(function($post) {
        return [
            'title' => $post['title'],
            'link' => $post['location_url'],
            'date' => $post['published_date'],
            'permalink' => $post['permalink'],
        ];
    }, $newsPost);



    ob_start();
    ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
    .news-container {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
    }

    .item {
      background-color: #ffffff;
      padding: 20px;
    }

    @media (max-width: 768px) {
      .news-container {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 480px) {
      .news-container {
        grid-template-columns: 1fr;
      }
    }
    </style>

    <h1>Listado de Elementos</h1>
  
    <div class="news-container">
        <?php foreach ($newsPost as $post): ?>
            <div class="item">
                <h2><?php echo esc_html($post['title']); ?></h2>
                <p><a href="<?php echo esc_url($post['link']); ?>" target="_blank">Ver noticia</a></p>
                <p>Fecha: <?php echo esc_html($post['date']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>

     <?php
    return ob_get_clean();


}
add_shortcode('interoil_news', 'interoil_news_shortcode');