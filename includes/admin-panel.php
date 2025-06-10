<?php

// Add admin menu and submenu
function interoil_reports_admin_menu() {
    add_menu_page(
        'Interoil Reports',
        'Interoil Reports',
        'manage_options',
        'interoil-reports-admin',
        'interoil_reports_admin_page',
        'dashicons-media-document',
        30
    );

    add_submenu_page(
        'interoil-reports-admin',
        'Interoil News',
        'Interoil News',
        'manage_options',
        'interoil-news-admin',
        'interoil_news_admin_page'
    );
}
add_action('admin_menu', 'interoil_reports_admin_menu');

// Main page: Interoil Reports
function interoil_reports_admin_page() {
    $log_path = trailingslashit(wp_upload_dir()['basedir']) . 'pdfs/log-reporte.txt';
    $log_content = file_exists($log_path) ? file_get_contents($log_path) : 'No logs available.';

    // Manual cron execution
    if (isset($_POST['cron_reports']) && check_admin_referer('cron_reports_nonce')) {
        if (function_exists('interoil_cron_task')) {
            interoil_cron_task();
            echo '<div class="notice notice-success"><p>✅ Cron executed successfully.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>❌ Function interoil_cron_task() does not exist.</p></div>';
        }
        $log_content = file_exists($log_path) ? file_get_contents($log_path) : 'No logs available.';
    }

    // Delete log
    if (isset($_POST['delete_log']) && check_admin_referer('delete_log_nonce')) {
        if (file_exists($log_path)) {
            unlink($log_path);
            echo '<div class="notice notice-warning"><p>🗑️ Log file deleted successfully.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>⚠️ Log file does not exist.</p></div>';
        }
        $log_content = 'No logs available.';
    }

    ?>
    <div class="wrap">
        <h1>🛠️ Interoil Reports – Manual Control</h1>
        <form method="post">
            <?php wp_nonce_field('cron_reports_nonce'); ?>
            <p><input type="submit" name="cron_reports" class="button button-primary" value="🔁 Run reports cron"></p>
        </form>

        <form method="post">
            <?php wp_nonce_field('delete_log_nonce'); ?>
            <input type="submit" name="delete_log" class="button button-secondary" value="🗑️ Delete log-reporte.txt">
        </form>

        <h2 style="margin-top:2em;">📄 Latest Log</h2>
        <textarea style="width: 100%; height: 400px;" readonly><?php echo esc_textarea($log_content); ?></textarea>
    </div>
    <?php
}

// Submenu: Interoil News
function interoil_news_admin_page() {
    $log_path = trailingslashit(wp_upload_dir()['basedir']) . 'pdfs/log-news.txt';
    $log_news_content = file_exists($log_path) ? file_get_contents($log_path) : 'No logs available.';

    // Manual cron execution
    if (isset($_POST['cron_news']) && check_admin_referer('cron_news_nonce')) {
        if (function_exists('interoil_cron_task_news')) {
            interoil_cron_task_news();
            echo '<div class="notice notice-success"><p>✅ Cron executed successfully.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>❌ Function interoil_cron_task_news() does not exist.</p></div>';
        }
        $log_news_content = file_exists($log_path) ? file_get_contents($log_path) : 'No logs available.';
    }

    // Delete log
    if (isset($_POST['delete_log_news']) && check_admin_referer('delete_log_news_nonce')) {
        if (file_exists($log_path)) {
            unlink($log_path);
            echo '<div class="notice notice-warning"><p>🗑️ Log file deleted successfully.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>⚠️ Log file does not exist.</p></div>';
        }
        $log_news_content = 'No logs available.';
    }

    ?>
    <div class="wrap">
        <h1>🛠️ Interoil News – Manual Control</h1>
        <form method="post">
            <?php wp_nonce_field('cron_news_nonce'); ?>
            <p><input type="submit" name="cron_news" class="button button-primary" value="🔁 Run news cron"></p>
        </form>

        <form method="post">
            <?php wp_nonce_field('delete_log_news_nonce'); ?>
            <input type="submit" name="delete_log_news" class="button button-secondary" value="🗑️ Delete log-news.txt">
        </form>

        <h2 style="margin-top:2em;">📄 Latest Log</h2>
        <textarea style="width: 100%; height: 400px;" readonly><?php echo esc_textarea($log_news_content); ?></textarea>
    </div>
    <?php
}