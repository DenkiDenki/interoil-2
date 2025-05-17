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
/*
function crear_custom_post_type() {
  $labels = array(
      'name'                  => _x('Proyectos', 'Post Type General Name', 'textdomain'),
      'singular_name'         => _x('Proyecto', 'Post Type Singular Name', 'textdomain'),
      'menu_name'             => __('Proyectos', 'textdomain'),
      'name_admin_bar'        => __('Proyecto', 'textdomain'),
      'add_new_item'          => __('Añadir nuevo proyecto', 'textdomain'),
      'edit_item'             => __('Editar proyecto', 'textdomain'),
      'new_item'              => __('Nuevo proyecto', 'textdomain'),
      'view_item'             => __('Ver proyecto', 'textdomain'),
      'search_items'          => __('Buscar proyectos', 'textdomain'),
      'not_found'             => __('No se encontraron proyectos', 'textdomain'),
      'not_found_in_trash'    => __('No se encontraron proyectos en la papelera', 'textdomain'),
  );

  $args = array(
      'label'                 => __('Proyecto', 'textdomain'),
      'description'           => __('Custom Post Type para proyectos', 'textdomain'),
      'labels'                => $labels,
      'supports'              => array('title', 'editor', 'thumbnail', 'excerpt'),
      'hierarchical'          => false,
      'public'                => true,
      'show_in_menu'          => true,
      'menu_position'         => 5,
      'menu_icon'             => 'dashicons-portfolio',
      'show_in_admin_bar'     => true,
      'show_in_nav_menus'     => true,
      'can_export'            => true,
      'has_archive'           => true,
      'exclude_from_search'   => false,
      'publicly_queryable'    => true,
      'show_in_rest'          => true, // importante para Gutenberg y Elementor
      'rewrite'               => array('slug' => 'proyectos'),
  );

  register_post_type('proyecto', $args);
}
add_action('init', 'crear_custom_post_type', 0);
*/