<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "root";  // Cambiar por el usuario real
$password = "";      // Cambiar por la contraseña real
$dbname = "mi_base_de_datos";  // Cambiar por el nombre de tu base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// URL del endpoint
$api_url = "https://rss.globenewswire.com/Hexmlreportfeed/organization/dBwf4frPXJHvuGJ2iT_UgA==/";

// Obtener el XML desde la URL
$xml_content = file_get_contents($api_url);
if ($xml_content === false) {
    die("Error al obtener el XML.");
}

// Convertir el XML en un objeto
$xml = simplexml_load_string($xml_content);
if ($xml === false) {
    die("Error al analizar el XML.");
}

// Recorrer cada reporte en el XML
foreach ($xml->report as $report) {
    $headline = $conn->real_escape_string(trim((string)$report->file_headline));
    $location = $conn->real_escape_string(trim((string)$report->location['href']));
    $published_date = $conn->real_escape_string(trim((string)$report->published['date']));

    // Insertar en la base de datos
    $sql = "INSERT INTO reports (headline, location, published_date) 
            VALUES ('$headline', '$location', '$published_date')";

    if (!$conn->query($sql)) {
        echo "Error al insertar: " . $conn->error;
    }
}

// Cerrar conexión
$conn->close();

echo "Datos guardados exitosamente.";
?>