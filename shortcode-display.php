<?php
function interoil_reports_shortcode($atts) {
    global $wpdb;
    $table_name = $wpdb->prefix . "interoil_pdfs";
    $first = false;

    $atts = shortcode_atts(
        array(
            'api_url' => 'https://rss.globenewswire.com/Hexmlreportfeed/organization/dBwf4frPXJHvuGJ2iT_UgA==/',
        ),
        $atts,
        'interoil_reports'
    );

     // Validate API URL
     if (empty($atts['api_url']) || !filter_var($atts['api_url'], FILTER_VALIDATE_URL)) {
        return 'Invalid API URL.';
    }

    $reports = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT published_date, file_name, category, upload_dir FROM $table_name ORDER BY published_date DESC",
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <div class="interoil-reports">
        <style>
            .interoil-reports{
                margin-left: 20px;
                margin-right: 20px;
            }
            .reports-container h3 {
                color: #1C6C8E;
                text-align: left;
                margin: 0;
                text-transform: uppercase;
                font-size: 15px;
            }
            .reports-container h2 {
                color: #1C6C8E;
                text-align: left;
                margin: 0;
                padding-bottom: 40px;
                font-size: 24px;
            }
            #listNews {
                color: #1C6C8E;
            }
            #listNews td, #listNews th {
                background-color: #fff!important;
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
            }
            .reports-container .reports-table {
                width: 30%;
                border-collapse: collapse;
                font-weight: 300;
                font-family: "Acumin", Sans-serif;
            }
            .reports-table th, .reports-table td {
                text-align: left;
                border: none!important;
                padding-left: 0;
            }
            .reports-table th {
                color: #1C6C8E;
                font-size: 13px;
            }
            .reports-table td {
                font-size: 15.4px;
            }
            .reports-table td:first-child {
                min-width: 60%;
            }
            .reports-table tbody>tr:nth-child(odd)>td {
                background-color: #fff!important;
            }

            @media (min-width: 600px) {
                .date-mobile {
                    display: none;
                }
            }
            
            @media (max-width: 600px) {
                .interoil-reports{
                margin-left: 0;
                margin-right: 0;
                }
                .reports-container .reports-table {
                    width: 100%;
                }   
                .left-content {
                    font-size: 1.2em;
                }
                .right-content {
                    font-size: 0.8rem;
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
                    font-size: 12px;
                    text-transform: uppercase;
                }
                .reports-table th, .reports-table td{
                    font-size: 0.8em;
                }
                .date-desktop {
                    display: none;
                }
                .date-mobile {
                    display: block;
                    font-size: 0.85em;
                    margin-top: 5px;
                    color: #444;
                }
                .date-title{
                    display: none;
                }
            }
            .accordion-report {
                width: 100%;
                overflow: hidden;
                font-family: "Acumin", Sans-serif;
                border-bottom: 1px solid #1C6C8E36!important;
            }
            .accordion-header {
                background-color: #ffffff!important;
                cursor: pointer;
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-weight: 500;
                border: none;
                width: 100%;
                text-align: left;
                outline: none;
                color: #1C6C8E!important;
                padding: 0.9em 0em 0.8em;
                font-size: 1.2em;
                border-top: 1px solid #1C6C8E36!important;   
            }
            .accordion-content {
                max-height: 0;
                overflow: hidden;
                background-color: #fff;
                transition: max-height 0.7s ease;
            }
            .accordion-content h2 {
                padding-left: 0;
            }
            .accordion-content.open {
                max-height: 1000px; 
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
            <div class="accordion-report" id="accordion-report">
                <?php foreach ($pdfs_by_category as $category => $items): ?>
                    <div class="accordion-header" onclick="toggleAccordion(this)">
                        <h3 class="left-content"><?php echo esc_html($category); ?></h3>
                        <span class="right-content">
                            <i class="fa <?php echo $first ? 'fa-minus' : 'fa-plus'; ?> icon" aria-hidden="true"></i></span>
                    </div>
                    <div class="accordion-content <?php echo $first ? 'open' : ''; ?>">
                        <!--h2>Interoil Exploration and Production ASAs financial calendar for 2024</h2-->
                        <table class="reports-table">
                            <thead>
                                <tr>
                                    <th>EVENT</th>
                                    <th class="date-title">DATE</th>
                                </tr>
                            </thead>
                            <tbody id="listNews">
                                <?php foreach ($items as $item): ?>
                                    <tr class="report-row">
                                        <td class="event-cell">
                                            <a href="<?php echo esc_url($item['upload_dir']); ?>" target="_blank"><?php echo esc_html($item['file_name']); ?></a>
                                            <div class="reportDate date-mobile"><?php echo esc_html($item['published_date']); ?></div>
                                        </td>
                                        <td class="reportDate date-desktop"><?php echo esc_html($item['published_date']); ?></td>
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
     function toggleAccordion(clickedHeader) {
        const accordion = document.getElementById("accordion-report");
        const currentContent = clickedHeader.nextElementSibling;
        const currentIcon = clickedHeader.querySelector(".icon");
        const isOpen = currentContent.classList.contains("open");

        // Cerrar todos los paneles
        const allContents = accordion.querySelectorAll(".accordion-content");
        const allIcons = accordion.querySelectorAll(".accordion-header .icon");
        allContents.forEach(content => {
            content.classList.remove("open");
            content.style.maxHeight = null;
        });
        allIcons.forEach(icon => {
            icon.classList.remove("fa-minus");
            icon.classList.add("fa-plus");
        });

        // Si el panel clicado estaba cerrado, lo abrimos (si ya estaba abierto, se queda cerrado)
        if (!isOpen) {
            currentContent.classList.add("open");
            currentContent.style.maxHeight = currentContent.scrollHeight + "px";
            currentIcon.classList.remove("fa-plus");
            currentIcon.classList.add("fa-minus");
        }
        document.addEventListener("DOMContentLoaded", function () {
            const firstOpen = document.querySelector(".accordion-content.open");
            if (firstOpen) {
                firstOpen.style.maxHeight = firstOpen.scrollHeight + "px";
            }
        });
    }      
    const reportDate = document.querySelectorAll(".reportDate"); 
        reportDate.forEach(publishedDate => {
            let dateAndTime = publishedDate.innerText;
            let date = dateAndTime.split("T")[0];
            let partes = date.split("-");
            let year = partes[0];
            let month = partes[1];
            let day = partes[2];

            let formatDate = `${day}.${month}.${year}`;
            publishedDate.innerText = formatDate;
        });  
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode('interoil_reports', 'interoil_reports_shortcode');