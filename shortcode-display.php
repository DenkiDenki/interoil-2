<?php
function interoil_reports_shortcode($atts) {
    global $wpdb;
    $table_name = $wpdb->prefix . "interoil_pdfs";

    $atts = shortcode_atts(
        array('reports_num' => 10),
        $atts,
        'interoil_reports'
    );
    $reports_num = intval($atts['reports_num']);

    $reports = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT published_date, file_name, category, upload_dir FROM $table_name ORDER BY published_date DESC LIMIT %d",
            $reports_num
        ),
        ARRAY_A
    );

    if (!$reports) {
        return "<p>No hay informes disponibles.</p>";
    }

    // Agrupar por categoría
    $pdfs_by_category = [];
    foreach ($reports as $fila) {
        $category = $fila['category'];
        $pdfs_by_category[$category][] = $fila;
    }

    ob_start();
    ?>
    <div class="interoil-reports">
        <style>
            .reports-container h3 {
                color: #1C6C8E;
                text-align: left;
                padding: 5px;
                margin: 0;
                text-transform: uppercase;
                font-size: 14px;
            }
            .reports-container h2 {
                color: #1C6C8E;
                text-align: left;
                margin: 0;
                padding: 10px;
                padding-bottom: 40px;
                font-size: 24px;
            }
            #listNews {
                color: #1C6C8E;
            }
            .reports-container a {
                color: #1C6C8E;
                text-decoration: none;
            }
            .reports-container a:hover {
                text-decoration: underline;
            }
            .reports-container {
                margin: auto;
                margin-top: 40px;
                background: white;
                padding: 20px;
                border-top: 1px solid #b5d9e9 !important;
            }
            .reports-container table {
                width: 70%;
                border-collapse: collapse;
                font-weight: 300;
                font-family: "Acumin", Sans-serif;
            }
            th, td {
                text-align: left;
                padding: 8px !important;
                border: none !important;
            }
            th {
                color: #1C6C8E;
            }
            td:first-child {
                min-width: 70%;
            }
            table tbody > tr:nth-child(odd) > td {
                background-color: #fff !important;
            }
            @media (max-width: 600px) {
                td:first-child {
                    min-width: 100%;
                }
                td, th {
                    width: 100%;
                }
                .left-content {
                    font-size: 1.2em;
                }
                .right-content {
                    font-size: 1rem;
                }
                .accordion-content {
                    font-size: 1.2em;
                }
                .reports-container {
                    padding: 0;
                }
                .reports-container h3 {
                    color: #1C6C8E;
                    text-align: left;
                    padding: 0;
                    margin: 0;
                    font-size: 16px;
                    text-transform: uppercase;
                }
            }
            .accordion {
                width: 100%;
                border-radius: 8px;
                overflow: hidden;
                font-family: "Acumin", Sans-serif;
            }
            .accordion-header {
                background-color: #ffffff !important;
                cursor: pointer;
                padding: 1em;
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-weight: 500;
                border: none;
                width: 100%;
                text-align: left;
                outline: none;
                color: #1C6C8E !important;
                font-size: 1.2em;
            }
            .accordion-content {
                display: none;
                padding: 1em;
                background-color: #fff;
                transition: max-height 0.4s ease, padding 0.3s ease;
            }
            .accordion.open .accordion-content {
                display: block;
            }
            .left-content {
                flex: 1;
                text-align: left;
            }
            .right-content {
                text-align: right;
            }
        </style>
        <div class="reports-container">
            <div class="accordion" id="myAccordion">
                <?php foreach ($pdfs_by_category as $category => $items): ?>
                    <button class="accordion-header" onclick="toggleAccordion()">
                        <h3 class="left-content"><?php echo esc_html($category); ?></h3>
                        <span class="right-content"><span id="accordion-icon">+</span></span>
                    </button>
                    <div class="accordion-content">
                        <h2>Interoil Exploration and Production ASAs financial calendar for 2024</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>EVENT</th>
                                    <th>DATE</th>
                                </tr>
                            </thead>
                            <tbody id="listNews">
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><a href="<?php echo esc_url($item['upload_dir']); ?>" target="_blank"><?php echo esc_html($item['file_name']); ?></a></td>
                                        <td><?php echo esc_html($item['published_date']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <script>
        function toggleAccordion() {
            const accordion = document.getElementById("myAccordion");
            const icon = document.getElementById("accordion-icon");

            accordion.classList.toggle("open");

            if (accordion.classList.contains("open")) {
                icon.textContent = "-";
            } else {
                icon.textContent = "+";
            }
        }
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode('interoil_reports', 'interoil_reports_shortcode');