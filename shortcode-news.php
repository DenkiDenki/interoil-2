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
        return "<p>No news available.</p>";
    }
    
    $newsPost = array_map(function($post) {
        return [
            'title' => $post['title'],
            'link' => $post['location_url'],
            'date' => $post['published_date'],
            'permalink' => $post['permalink'],
            'content' => $post['content'],
        ];
    }, $newsPost);

    $items_by_year = [];
    foreach ($newsPost as $item) {
      $year = date('Y', strtotime($item['date']));
      $items_by_year[$year][] = $item;
    }
    krsort($items_by_year);
    $years = array_keys($items_by_year);
    $latest_year = $years[0];
    $latest_items = $items_by_year[$latest_year];
    $item = $latest_items[0];
    
      ob_start();

     // Display the latest news item
    ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
      .news-general-container{
        font-weight: 100!important;
        font-family: "Acumin", Sans-serif;
        color: #1C6C8E;

      }
      .news-container {
        display: grid;
        grid-template-columns: 10% 90%;
        gap: 20px;
        color: #1C6C8E;
      }

      .news-items-container {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px 40px;
        color: #1C6C8E;
      }
      .news-header{
        font-size: 3.5em;
        lineheight: 1.2;
        margin-bottom: 45px;
        color: #1C6C8E;
        font-weight: 300;
        font-family: "Acumin", Sans-serif;
      }

      .news-item {
        background-color: #ffffff;
        padding: 20px 0;
        border-top: 1px solid #1C6C8E36!important;   

      }
      
      .news-item p {
        margin: 10px 0;
      }
      .news-item a {
        color: #1C6C8E;
        text-decoration: none;
      }
      .news-item a:hover {
        text-decoration: underline;
      }
      
      .news-item .align-right {
        text-align: right;
      }
      .news-item .align-left {
        text-align: left;
      }
      .news-item-date{
        font-size: 0.9rem;
        padding-bottom: 10px;
      }
      .news-item-title{
        font-size: 3em;
        margin-bottom: 45px;
        color: #1C6C8E;
        line-height: 1.2;
        font-weight: 100!important;
        font-family: "Acumin", Sans-serif;
      }
      .news-item-permalink{
        font-size: 13px;
      }
      .align-right.link{
        visinle: hidden;
        display: none;
      } 
      .year-list-container {
        margin-bottom: 20px;
        color: #1C6C8E;
      }
      .year-list {
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
      }
      .year-list ul {
        list-style: none;
        padding: 0;
        margin: 0;
      }


      @media (max-width: 768px) {
        .news-container {
          grid-template-columns: repeat(2, 1fr);
        }
        .news-container {
          grid-template-columns: 1fr;
        }
      }

      @media (max-width: 480px) {
        .news-container {
          grid-template-columns: 1fr;
        }
      }
    </style>
<div class="news-general-container" data-latest-year="<?php echo esc_attr($latest_year); ?>">
  <h2 class="news-header">News</h2>
  <div class="news-container">
     
          <div class="year-list-container">
            <div class="year-list">
                <h5>Date</h5>
                <ul>
                    <?php foreach ($years as $year): ?>
                        <li><a href="javascript:void(0);" data-filter-year="<?php echo esc_attr($year); ?>"><?php echo esc_html($year); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
          </div>
          <div class="news-items-container">
            
            <?php foreach ($newsPost as $post): ?>
              <?php $year = date('Y', strtotime($post['date'])); ?>
                <div class="news-item" data-year="<?php echo esc_attr($year); ?>">
                    <p class="align-left news-item-date"><?php echo esc_html($post['date']); ?></p>
                    <a href="<?php echo home_url('/news/' . esc_attr($post['permalink'])); ?>"><h2 class="align-left news-item-title"><?php echo esc_html($post['title']); ?></h2></a>
                    <p class="align-right link"><a href="<?php echo esc_url($post['link']); ?>" target="_blank">link</a></p>
                    <p class="align-right news-item-permalink">
                      <a href="<?php echo esc_url($post['permalink']); ?>">Read More</a>
                      <span class="right-content"><i class="fa fa-plus icon" aria-hidden="true"></i></span>
                  </p>
                </div>
            <?php endforeach; ?>
          </div>
  </div>
 </div>
 <script>
document.addEventListener('DOMContentLoaded', function() {
  // Mostrar contenedor al cargar
  document.querySelector('.news-container').style.display = 'grid';

  const yearLinks = document.querySelectorAll('[data-filter-year]');
  const newsItems = document.querySelectorAll('.news-item');

  yearLinks.forEach(link => {
    link.addEventListener('click', function() {
      const selectedYear = this.getAttribute('data-filter-year');

      newsItems.forEach(item => {
        if (item.getAttribute('data-year') === selectedYear) {
          item.style.display = 'block';
        } else {
          item.style.display = 'none';
        }
      });
    });
  });
});

document.addEventListener("DOMContentLoaded", () => {
  const container = document.querySelector(".news-general-container");
  const latestYear = container.getAttribute("data-latest-year");

  const showNewsByYear = (year) => {
    document.querySelectorAll(".news-item").forEach(item => {
      item.style.display = item.getAttribute("data-year") === year ? "block" : "none";
    });
  };

  // Mostrar noticias del último año al cargar
  showNewsByYear(latestYear);

  // Click en los años
  document.querySelectorAll("#year-list a").forEach(link => {
    link.addEventListener("click", e => {
      e.preventDefault();
      const year = e.target.getAttribute("data-year");
      showNewsByYear(year);
    });
  });
});

</script>
     <?php
    return ob_get_clean();


}
add_shortcode('interoil_news', 'interoil_news_shortcode');