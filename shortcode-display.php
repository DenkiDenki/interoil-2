<?php
function interoil_reports_shortcode($atts) {
    global $wpdb;
    $table_name = $wpdb->prefix . "interoil_pdfs";

    $atts = shortcode_atts(array('reports_num' => 10), $atts, 'interoil_reports');
    $reports_num = intval($atts['reports_num']);

    $reports = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY published_date DESC LIMIT %d",
        $reports_num
    ));

    if (!$reports) {
        return "<p>No hay informes disponibles.</p>";
    }

    $output = '<div class="interoil-reports"><table>';
    $output .= '<tr><th>Evento</th><th>Fecha</th></tr>';

    foreach ($reports as $report) {
        $output .= "<tr><td><a href='{$report->upload_dir}'>{$report->file_name}</a></td><td>{$report->published_date}</td></tr>";
    }

    $output .= '</table></div>';
    return $output;
}

add_shortcode('interoil_reports', 'interoil_reports_shortcode');