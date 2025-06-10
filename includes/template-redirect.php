<?php
function interoil_template_redirect() {
    if (get_query_var('interoil_news_permalink')) {
        global $wpdb;

        $permalink = sanitize_text_field(get_query_var('interoil_news_permalink'));
        $table_news = $wpdb->prefix . "interoil_newsposts";

        get_header();

        $news = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_news WHERE permalink = %s", $permalink)
        );

        if ($news) {
            // Enqueue Elementor styles/scripts if available
            if (function_exists('elementor_frontend')) {
                elementor_frontend()->enqueue_styles();
                elementor_frontend()->enqueue_scripts();
            }
            if (function_exists('ElementorPro\Plugin')) {
                ElementorPro\Plugin::instance()->enqueue_styles();
                ElementorPro\Plugin::instance()->enqueue_scripts();
            }
            ?>
            <script src="https://interoil2dev.wpenginepowered.com/wp-content/plugins/elementor/assets/js/frontend-modules.min.js"></script>
            <style>
                .news-single {
                    font-weight: 100!important;
                    font-family: "Acumin", Sans-serif;
                    color: #1C6C8E;
                    max-width: 700px;
                    margin: 5%;
                    padding: 20px;
                }
                .news-single h1 {
                    font-size: 3em;
                    margin-bottom: 10px;
                }
                .news-single p {
                    padding-top: 30px;
                    margin-bottom: 0;
                }
                .news-content > p:first-child {
                    font-size: 1.5em;
                    font-weight: 300;
                    padding-bottom: 40px;
                    border-bottom: 1px solid #1C6C8E;
                }
                .news-content > p:nth-child(2) {
                    padding-top: 30px;
                }
                .news-single a {
                    color: #0073aa;
                    text-decoration: none;
                }
                .news-single a:hover {
                    text-decoration: underline;
                }
                .news-single table {
                    padding-top: 20px;
                    border: 1px solid #1C6C8E;
                }
                .interoil-footer {
                    display: flex;
                }
                .interoil-footer-2 {
                    display: flex;
                    justify-content: space-around;
                    align-items: center;
                    font-size: 13px;
                    text-transform: uppercase;
                    padding-bottom: 30px;
                    color: #1C6C8E;
                }
                .interoil-footer-left {
                    flex: 0 0 50%;
                }
                .interoil-footer-left-2 {
                    margin-right: auto;
                }
                .interoil-footer-right {
                    margin-left: 10px;
                }
                .interoil-footer-right > p > span {
                    display: block;
                }
                .interoil-footer-right > span {
                    margin-left: 20px;
                }
                @media (max-width: 768px) {
                    .interoil-footer, .interoil-footer-2 {
                        display: flex;
                        flex-direction: column;
                        text-align: center;
                    }
                }
                /* Menu styles */
                [data-elementor-type="header"] {
                    display: none;
                }
                .nav-container {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 15px 20px;
                    background: #fff;
                    position: relative;
                    z-index: 999;
                    border-bottom: 1px solid #eee;
                }
                .logo-interoil img {
                    height: auto;
                    width: 160px;
                }
                .interoil-footer-left img {
                    height: auto;
                    width: 160px;
                    margin-left: 30px;
                }
                .interoil-footer{
                    color: #1C6C8E;
                }
                .menu-interoil {
                    display: flex;
                    gap: 20px;
                }
                .menu-interoil a {
                    text-decoration: none;
                    color: #1C6C8E;
                    font-size: 14px;
                    transition: color 0.3s;
                }
                .menu-interoil a:hover {
                    color: #1C6C8E;
                }
                .menu-interoil-toggle {
                    display: none;
                    flex-direction: column;
                    justify-content: space-between;
                    width: 22px;
                    height: 16px;
                    cursor: pointer;
                }
                .menu-interoil-toggle span {
                    height: 3px;
                    background: #333;
                    border-radius: 2px;
                    transition: all 0.4s ease;
                }
                @media (max-width: 768px) {
                    .menu-interoil {
                        position: absolute;
                        top: 80px;
                        left: 0;
                        right: 0;
                        background: #f1f1f1;
                        flex-direction: column;
                        overflow: hidden;
                        max-height: 0;
                        transition: max-height 0.4s ease;
                    }
                    .menu-interoil a {
                        padding: 15px 25px;
                        color: #1C6C8E;
                    }
                    .menu-interoil-toggle {
                        display: flex;
                    }
                    .menu-interoil.open {
                        max-height: 500px;
                    }
                    .menu-interoil-toggle.open span:nth-child(1) {
                        transform: rotate(45deg) translate(5px, 5px);
                    }
                    .menu-interoil-toggle.open span:nth-child(2) {
                        opacity: 0;
                    }
                    .menu-interoil-toggle.open span:nth-child(3) {
                        transform: rotate(-45deg) translate(5px, -5px);
                    }
                    .interoil-footer-left img {
                        width: 160px;
                    }
                }
            </style>
            <div class="nav-container">
                <div class="logo-interoil">
                    <?php the_custom_logo(); ?>
                </div>
                <div class="menu-interoil-toggle" id="menu-interoil-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <nav class="menu-interoil" id="menu-interoil">
                    <?php
                    $menu_items = wp_get_nav_menu_items(4);
                    foreach ($menu_items as $item) {
                        echo '<a href="' . esc_url($item->url) . '">' . esc_html($item->title) . '</a>';
                    }
                    $menu_contact = wp_get_nav_menu_items(6);
                    foreach ($menu_contact as $item) {
                        echo '<a href="' . esc_url($item->url) . '">' . esc_html($item->title) . '</a>';
                    }
                    ?>
                </nav>
            </div>
            <div class="news-single">
                <h1><?php echo esc_html($news->title); ?></h1>
                <div class="news-content">
                    <?php echo wp_kses_post(wpautop($news->content)); ?>
                </div>
            </div>
            <div class="interoil-footer">
                <div class="interoil-footer-left">
                    <?php the_custom_logo(); ?>
                </div>
                <div class="interoil-footer-right">
                    <p>
                        Interoil Main Office
                        <span>c/ o Advokatfirmaet Schjødt AS</span>
                        <span>Tordenskiolds gate 12</span>
                        <span>NO-0160 Oslo, Norway</span>
                    </p>
                </div>
            </div>
            <div class="interoil-footer-2">
                <div class="interoil-footer-left">
                    <span>© 2025 Interoil Exploration and Production ASA.</span>
                </div>
                <div class="interoil-footer-right">
                    <span>Disclaimer</span>
                    <span>Privacy & Cookies</span>
                </div>
            </div>
            <script>
                const toggle = document.getElementById('menu-interoil-toggle');
                const menu = document.getElementById('menu-interoil');
                toggle.addEventListener('click', () => {
                    toggle.classList.toggle('open');
                    menu.classList.toggle('open');
                });
            </script>
            <?php
            exit;
        } else {
            interoil_crear_txt_en_uploads('log-reporte', "❌ News not found: $permalink - $news");
            wp_redirect(home_url('/404'));
            exit;
        }
    }
}
add_action('template_redirect', 'interoil_template_redirect');