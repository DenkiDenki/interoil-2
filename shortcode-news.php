<?php
function interoil_news_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'api_url' => 'https://rss.globenewswire.com/HexmlFeed/organization/dBwf4frPXJHvuGJ2iT_UgA==/',
        ),
        $atts,
        'interoil_news'
    );  ob_start();
    ?>
    <style>
    .container {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
    }

    .item {
      background-color: #ffffff;
      padding: 20px;
    }

    @media (max-width: 768px) {
      .container {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 480px) {
      .container {
        grid-template-columns: 1fr;
      }
    }
    </style>

    <h1>Listado de Elementos</h1>
  
    <div class="container">
        <div class="item">Elemento 1</div>
        <div class="item">Elemento 2</div>
        <div class="item">Elemento 3</div>
        <div class="item">Elemento 4</div>
        <div class="item">Elemento 5</div>
        <div class="item">Elemento 6</div>
    </div>

     <?php
    return ob_get_clean();


}
add_shortcode('interoil_news', 'interoil_news_shortcode');
