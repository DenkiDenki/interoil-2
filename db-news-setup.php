<?php
global $interoil_db_news_version;
$interoil_db_news_version = '1.0';

function interoil_news_install() {
    global $interoil_db_news_version;

    $installed_ver = get_option('interoil_db_news_version');

    if ($installed_ver != $interoil_db_news_version) {
        create_db_news();
        update_option('interoil_db_news_version', $interoil_db_news_version);
    }
    
}

function create_db_news() {
    global $interoil_db_news_version, $wpdb;
   
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $charset_collate = $wpdb->get_charset_collate();

    $table_news = $wpdb->prefix . "interoil_newsposts";
    
    $sql_news = "
    CREATE TABLE {$table_news} (
        id INT(10) NOT NULL AUTO_INCREMENT,
        published_date VARCHAR(50) NULL,
        title VARCHAR(500) NOT NULL,
        location_url VARCHAR(150) NULL,
        permalink TEXT NOT NULL,
        content TEXT NULL,
        PRIMARY KEY  (id),
        KEY title (title)
    ) $charset_collate;
    ";

    //error_log("SQL for table creation: " . $sql_news); // temporal
    dbDelta($sql_news);

    if($wpdb->get_var("SHOW TABLES LIKE '$table_news'") != $table_news) {
        error_log("Error: Table was not created $table_news");
    }
    
}