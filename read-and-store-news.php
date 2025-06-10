<?php
function save_news_ajax() {
    check_ajax_referer('interoil-news', 'security');

    if (empty($_POST['news'])) {
        wp_send_json_error(['message' => 'Data not received - NEWS.']);
        wp_die();
    }

    $newPosts = json_decode(stripslashes($_POST['news']), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error(['message' => 'JSON news malformed.']);
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
        'message'  => 'Data processed correctly.',
        'response' => $response
    ]);

    wp_die();
}

add_action('wp_ajax_guardar_news', 'save_news_ajax');
add_action('wp_ajax_nopriv_guardar_news', 'save_news_ajax');


function interoil_insert_news_item($data) {
    global $wpdb;
    $table_news = $wpdb->prefix . "interoil_newsposts";
    $allowed_tags = array(
        'p'      => array('align' => array(), 'id' => array(), 'style' => array()),
        'strong' => array(),
        'u'      => array(),
        'ul'     => array('id' => array(), 'style' => array()),
        'li'     => array(),
        'a'      => array(
            'href'   => array(),
            'title'  => array(),
            'rel'    => array(),
            'target' => array()
        ),
        'img'    => array(
            'src'            => array(),
            'alt'            => array(),
            'referrerpolicy' => array(),
            'style'          => array()
        ),
        'br'     => array(),
        'em'     => array(),
        'i'      => array(),
        'b'      => array(),
        'h1'     => array(),
        'h2'     => array(),
        'h3'     => array(),
        'h4'     => array(),
        'h5'     => array(),
        'h6'     => array(),
        'blockquote' => array(),
        'div'    => array(),
        'span'   => array(),
        'table'  => array(
            'border'      => array(),
            'cellpadding' => array(),
            'cellspacing' => array(),
            'width'       => array(),
            'style'       => array(),
        ),
        'tr'     => array('style' => array()),
        'td'     => array(
            'style'   => array(),
            'colspan' => array(),
            'align'   => array(),
            'valign'  => array(),
            'width'   => array()
        ),
        'th'     => array(),
        'thead'  => array(),
        'tbody'  => array(),
        'tfoot'  => array(),
        'code'   => array(),
        'pre'    => array(),
        'hr'     => array(),
    );

    $title   = sanitize_text_field($data['title'] ?? '');
    $link    = esc_url_raw($data['link'] ?? '');
    $date    = sanitize_text_field($data['date'] ?? '');
    $content = str_replace( array( '<![CDATA[', ']]>' ), '', $data['post_body'] );
    $content = wp_kses($content, $allowed_tags);

    if (empty($title) || empty($link)) {
        return ['status' => 'error', 'message' => "Data are missing in the news item."];
    }

    $permalink = sanitize_title($title);

    //interoil_crear_txt_en_uploads('log-news', "Title: $title, Link: $link, Date: $date", "Content: $content");

    $existe = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_news WHERE location_url = %s",
        $link
    ));

    if ($existe > 0) {
        //interoil_crear_txt_en_uploads('log-news', "⏩ Existing news item is omitted: $title");
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
        interoil_crear_txt_en_uploads('log-news', "❌ Error inserting new: $title - Error: " . $wpdb->last_error);
        return ['status' => 'error', 'message' => "Error inserting $title"];
    }

    interoil_crear_txt_en_uploads('log-news', "✅ Save new: $title");
    return ['status' => 'saved'];
}

function interoil_news_js() {
    if ( did_action( 'elementor/frontend/after_register_scripts' ) ) {
        wp_enqueue_script(
            'interoil-news-xml',
            plugin_dir_url(__FILE__) . 'js/fetch-news.js',
            ['elementor-frontend','jquery'],
            filemtime(plugin_dir_path(__FILE__) . 'js/fetch-news.js'),
            true
        );

        wp_localize_script('interoil-news-xml', 'news_object', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('interoil-news')
        ]);
    }
}
add_action('wp_enqueue_scripts', 'interoil_news_js', 20);

function interoil_add_rewrite_rules() {
    add_rewrite_rule('^news/([^/]+)/?', 'index.php?interoil_news_permalink=$matches[1]', 'top');
}
add_action('init', 'interoil_add_rewrite_rules');

function interoil_add_query_vars($vars) {
    $vars[] = 'interoil_news_permalink';
    return $vars;
}
add_filter('query_vars', 'interoil_add_query_vars');

function interoil_fetch_and_parse_news() {
    $url = 'https://rss.globenewswire.com/HexmlFeed/organization/dBwf4frPXJHvuGJ2iT_UgA==/';
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        interoil_crear_txt_en_uploads('log-news', '❌ Error getting the XML: ' . $response->get_error_message());
        return [];
    }

    $xml = wp_remote_retrieve_body($response);

    if (empty($xml)) {
        interoil_crear_txt_en_uploads('log-news', '❌ Empty XML.');
        return [];
    }
    
    $upload_dir = wp_upload_dir();
    $target_dir = trailingslashit($upload_dir['basedir']) . 'pdfs/';

    if (!file_exists($target_dir)) {
        wp_mkdir_p($target_dir);
    }

	$tempLocalPath = $target_dir . "/data-news.xml";
	file_put_contents($tempLocalPath, $xml);
    
    $xml = file_get_contents($tempLocalPath);

    if (!$xml) {
        interoil_crear_txt_en_uploads('log-news', '❌ The XML could not be parsed.');
        return [];
    }
	
	preg_match_all('/<press_release([^>]*)>(.*?)<\/press_release>/s', $xml, $releases, PREG_SET_ORDER);
    if (empty($releases)) {
        interoil_crear_txt_en_uploads('log-news', '❌ No reports were found in the XML.');
        return [];
    }
	
	$newReleases = [];
	foreach ($releases as $post) {
        $attrString = $post[1];  // <report ...>
        $content = $post[2];     // <report> ... </report>
    
        // $attrString
        preg_match_all('/(\w+)\s*=\s*"([^"]*)"/', $attrString, $attrs, PREG_SET_ORDER);
        $attributes = [];
        foreach ($attrs as $attr) {
            $attributes[$attr[1]] = $attr[2];
        }
        
        // <headline><![CDATA[...]]></headline>
        preg_match('/<headline><!\[CDATA\[(.*?)\]\]><\/headline>/', $content, $headlineMatch);
        $fileHeadline = $headlineMatch[1] ?? null;

        //<published date="2025-04-28T08:09:29 CEST" />
        //<link href="https://ml-eu.globenewswire.com/Resource/Download/4076aaed-a0b8-4cf5-8922-683fd92fcfd5" />
        preg_match('/<published\s+date="([^"]+)"\s*\/>/', $content, $publishedMatch);
        $publishedDate = $publishedMatch[1] ?? null;
        $date = explode("T", $publishedDate)[0];

        preg_match('/<location\s+href="([^"]+)"\s*\/>/', $content, $linkMatch);
        $linkHref = $linkMatch[1] ?? null;

        $urlPost = $linkHref;
        $responsePost = wp_remote_get($urlPost);

        if (is_wp_error($responsePost)) {
            interoil_crear_txt_en_uploads('log-news', '❌ Error getting the XML: ' . $responsePost->get_error_message());
            return;
        }

        $xmlPost = wp_remote_retrieve_body($responsePost);
        interoil_crear_txt_en_uploads('log-news', print_r($xmlPost, true));

        preg_match('/<main>(.*?)<\/main>/s', $xmlPost, $mainContentMatch);
        $mainContent = $mainContentMatch[1] ?? '';

        $newReleases[] = [
            'title' => $fileHeadline,
            'link'  => $linkHref,
            'date'  => $date,
            'post_body' => $mainContent,
        ];
	}
	
    return $newReleases;
}
//cron call back
function interoil_cron_task_news() {

    $newReleases = interoil_fetch_and_parse_news();

    foreach ($newReleases as $release) {
        $result = interoil_insert_news_item($release);
    }
    
    interoil_crear_txt_en_uploads('log-news', "✅ Cron executed correctly with " . count($newReleases) . " news.");
    interoil_crear_txt_en_uploads('log-news', print_r($newReleases, true));
}