<?php
function interoil_fetch_and_store_reports() {
    // Un solo reporte
    $url = "https://ml-eu.globenewswire.com/Resource/Download/4076aaed-a0b8-4cf5-8922-683fd92fcfd5";
    // Obtener nombre desde la URL o generar uno
    $nombreArchivo = basename(parse_url($url, PHP_URL_PATH)) ?: 'reporte_' . time() . '.pdf';

    // Usamos la carpeta de uploads de WordPress
    $upload_dir = wp_upload_dir();
    $carpeta_destino = $upload_dir['basedir'] . '/pdfs';

    // Crear carpeta si no existe
    if (!file_exists($carpeta_destino)) {
        wp_mkdir_p($carpeta_destino);
    }

    // Ruta completa para guardar
    $ruta_completa = trailingslashit($carpeta_destino) . $nombreArchivo;

    // Descargar con cURL
    $ch = curl_init($url);
    $fp = fopen($ruta_completa, 'w+');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_exec($ch);

    // Validación de errores
    if (curl_errno($ch)) {
        fclose($fp);
        return interoil_crear_txt_en_uploads('log-reporte', "No se pudo descargar.");
    }

    curl_close($ch);
    fclose($fp);

    // Guardar en log txt
    return interoil_crear_txt_en_uploads('log-reporte', "Se creó el archivo: " . $ruta_completa);
}

function interoil_procesar_feed_xml() {
    $url = "https://rss.globenewswire.com/Hexmlreportfeed/organization/dBwf4frPXJHvuGJ2iT_UgA==/";

    // Obtener el XML
    $xmlContent = file_get_contents($url);

    if ($xmlContent === false) {
        error_log("❌ No se pudo obtener el XML.");
        return;
    }

    // Parsear XML
    $xml = simplexml_load_string($xmlContent);

    if (!$xml) {
        error_log("❌ Error al parsear el XML.");
        return;
    }

    // Namespace requerido para acceder a los nodos <report>
    $xml->registerXPathNamespace('g', 'http://www.globenewswire.com/');
    $reportes = $xml->xpath('//g:report');

    if (!$reportes || count($reportes) === 0) {
        error_log("ℹ️ No se encontraron reportes.");
        return;
    }

    // Mostrar resultados (puedes omitir esto en producción)
    echo "<table><thead><tr><th>Título</th><th>Fecha</th></tr></thead><tbody>";

    foreach ($reportes as $reporte) {
        $headline  = (string) $reporte->file_headline;
        $location  = (string) $reporte->location['href'];
        $fecha_raw = (string) $reporte->published['date'];
        $fecha     = explode('T', $fecha_raw)[0];

        echo "<tr><td><a href='$location' target='_blank'>" . esc_html($headline) . "</a></td><td>$fecha</td></tr>";
    }

    echo "</tbody></table>";
}

function interoil_crear_txt_en_uploads($nombre_archivo, $contenido) {
    // Obtener la ruta base del directorio de uploads
    $upload_dir = wp_upload_dir();
    $carpeta = $upload_dir['basedir'] . '/pdfs';

    // Crear carpeta si no existe
    if (!file_exists($carpeta)) {
        wp_mkdir_p($carpeta);
    }

    // Ruta completa del archivo
    $ruta_archivo = trailingslashit($carpeta) . sanitize_file_name($nombre_archivo) . '.txt';

    // Crear y escribir el archivo
    $resultado = file_put_contents($ruta_archivo, $contenido);

    if ($resultado === false) {
        return "❌ Error al crear el archivo.";
    }

    return "✅ Archivo creado correctamente en: " . $ruta_archivo;
}

function interoil_reports_front_shortcode($atts) {
    // Default attributes
    $atts = shortcode_atts(
        array(
            'api_url' => 'https://rss.globenewswire.com/Hexmlreportfeed/organization/dBwf4frPXJHvuGJ2iT_UgA==/',
            'reports_num' => 10,
        ),
        $atts,
        'interoil_reports'
    );

    // Validate API URL
    if (empty($atts['api_url']) || !filter_var($atts['api_url'], FILTER_VALIDATE_URL)) {
        return 'Invalid API URL.';
    }

    // Validate number of reports
    if (!is_numeric($atts['reports_num']) || $atts['reports_num'] <= 0) {
        return 'Invalid number of reports.';
    }

    $output = '
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
                border-top: 1px solid #b5d9e9!important;
            }
            .reports-container table {
                width: 70%;
                border-collapse: collapse;
                font-weight: 300;
                font-family: "Acumin", Sans-serif;
            }
            th, td {
                text-align: left;
                padding: 8px!important;
                border: none!important;
            }
            th {
                color: #1C6C8E;
            }
            td:first-child {
                min-width: 70%;
            }
            table tbody>tr:nth-child(odd)>td {
                background-color: #fff!important;
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
                background-color: #ffffff!important;
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
                color: #1C6C8E!important;
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
                <button class="accordion-header" onclick="toggleAccordion()">
                    <h3 class="left-content">Financial Calendar</h3> 
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
                            <script>
                                async function obtenerYConvertirXML() {
                                    try {
                                        const response = await fetch("https://rss.globenewswire.com/Hexmlreportfeed/organization/dBwf4frPXJHvuGJ2iT_UgA==/");
                                        const xmlText = await response.text();
                                        const parser = new DOMParser();
                                        const xmlDoc = parser.parseFromString(xmlText, "application/xml");

                                        let noticias = xmlDoc.getElementsByTagName("report");

                                        for (let i = 0; i < noticias.length; i++) {
                                            let headline = xmlDoc.getElementsByTagName("file_headline")[i];
                                            let headline1 = headline.textContent.trim();
                                            let locationNode = xmlDoc.getElementsByTagName("location")[i];
                                            let locationHref = locationNode.getAttribute("href");
                                            let publishedDate = xmlDoc.getElementsByTagName("published")[i];
                                            let dateAndTime = publishedDate.getAttribute("date");
                                            let date = dateAndTime.split("T")[0];
                                            let partes = date.split("-");
                                            let year = partes[0];
                                            let month = partes[1];
                                            let day = partes[2];

                                            let formatDate = `${day}.${month}.${year}`;

                                            const tr = document.createElement("tr");
                                            tr.innerHTML = `<td><a href="${locationHref}">${headline1}</a></td><td>${formatDate}</td>`;
                                            document.getElementById("listNews").appendChild(tr);
                                        }
                                    } catch (error) {
                                        console.error("Error al obtener el XML:", error);
                                    }
                                }

                                obtenerYConvertirXML();

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
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>';

    return $output;
}

function reports_init() {
    // Register shortcode
    add_shortcode('interoil_reports_front', 'interoil_reports_front_shortcode');
}
add_action('init', 'reports_init');
