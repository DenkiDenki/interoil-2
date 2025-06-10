<?php
global $interoil_db_version;
$interoil_db_version = '1.0';

function interoil_install() {
    global $interoil_db_version;

    $installed_ver = get_option('interoil_db_version');

    if ($installed_ver != $interoil_db_version) {
        create_db_reports();
        update_option('interoil_db_version', $interoil_db_version);
    }
}

function create_db_reports() {
global $interoil_db_version, $wpdb, $charset_collate;
    $charset_collate = $wpdb->get_charset_collate();
    
    $table_reports = $wpdb->prefix . "interoil_pdfs";
    $table_categories = $wpdb->prefix . "interoil_categories";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    $sql_categories = "CREATE TABLE $table_categories (
        id INT(10) NOT NULL AUTO_INCREMENT,
        name VARCHAR(50) NOT NULL UNIQUE,
        description TEXT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql_categories);

    $sql_reports = "CREATE TABLE $table_reports (
        id INT(10) NOT NULL AUTO_INCREMENT,
        published_date VARCHAR(50) NULL,
        file_name VARCHAR(150) NOT NULL,
        category_id INT(10) NOT NULL,
        location_url VARCHAR(150) NULL,
        upload_dir TEXT NOT NULL,
        description TEXT NULL,
        PRIMARY KEY (id),
        KEY category_id (category_id)
    ) $charset_collate;";
    dbDelta($sql_reports);

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_reports LIKE 'category'");
    if (!empty($column)) {
        
        $existing_reports = $wpdb->get_results("SELECT DISTINCT category FROM $table_reports");

        foreach ($existing_reports as $report) {
            $category_name = esc_sql($report->category);

            $wpdb->query(
                $wpdb->prepare(
                    "INSERT IGNORE INTO $table_categories (name) VALUES (%s)",
                    $category_name
                )
            );
            $category_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM $table_categories WHERE name = %s",
                    $category_name
                )
            );
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $table_reports SET category_id = %d WHERE category = %s",
                    $category_id,
                    $category_name
                )
            );
        }

        $wpdb->query("ALTER TABLE $table_reports DROP COLUMN category");
    }
}

//register_activation_hook(__FILE__, 'interoil_install');

function save_reports_ajax() {
    global $wpdb;

    $table_pdfs = $wpdb->prefix . "interoil_pdfs";
    $table_categories = $wpdb->prefix . "interoil_categories";

    error_log("Nonce received: " . ($_POST['security'] ?? 'NULL'));
  
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'interoil-reports')) {
        wp_send_json_error(['message' => 'Nonce not valid.']);
        wp_die();
    }

    if (!isset($_POST['datos'])) {
        wp_send_json_error(['message' => 'Data not received.']);
        wp_die();
    }

    $newReports = json_decode(stripslashes($_POST['datos']), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error(['message' => 'JSON malformed.']);
        wp_die();
    }

    $response = [
        'status' => 'ok',
        'saved' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    if (is_array($newReports)) {
        $upload_dir = wp_upload_dir();

        foreach ($newReports as $report) {
            $title = sanitize_text_field($report['title']);
            $link = esc_url_raw($report['link']);
            $date = sanitize_text_field($report['date']);
            $category_name = sanitize_text_field($report['category'] ?? 'reports and presentations');
            $description = sanitize_text_field($report['description'] ?? '');

            $category_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_categories WHERE name = %s",
                $category_name
            ));

            if (!$category_id) {
                $wpdb->insert($table_categories, [
                    'name' => $category_name,
                    'description' => $description,
                ]);
                $category_id = $wpdb->insert_id;
            }

            $existe = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_pdfs WHERE location_url = %s",
                $link
            ));

            if ($existe == 0) {
                $inserted = $wpdb->insert($table_pdfs, [
                    'file_name' => $title,
                    'location_url' => $link,
                    'published_date' => $date,
                    'upload_dir' => $upload_dir['baseurl'] . '/pdfs/reports/' . convert_name_to_slug_pdf($title),
                    'category_id' => $category_id,
                ]);

                if ($inserted !== false) {
                    $response['saved']++;
                } else {
                    $response['errors'][] = "Error inserting report: $title";
                }
            } else {
                $response['skipped']++;
            }
        }

        require_once plugin_dir_path(__FILE__) . 'read-and-store-reports.php';
        interoil_read_and_store_reports($newReports);

    } else {
        wp_send_json_error(['message' => 'Datos no válidos o JSON mal formado.']);
    }
    
    wp_die();
}

add_action('wp_ajax_guardar_reports', 'save_reports_ajax');
add_action('wp_ajax_nopriv_guardar_reports', 'save_reports_ajax');