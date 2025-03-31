<?php

class ExampleClass {
    public function sayHello() {
        return "Hello, World!";
    }

    public function add($a, $b) {
        return $a + $b;
    }
}

// URL del archivo PDF
$pdfUrl = "https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf";

// Carpeta donde guardar el archivo
$folderPath = "downloads";
$fileName = "archivo.pdf";
$filePath = $folderPath . "/" . $fileName;

// Crear la carpeta si no existe
if (!is_dir($folderPath)) {
    mkdir($folderPath, 0777, true);
}

// Descargar el archivo y guardarlo
$fileContent = file_get_contents($pdfUrl);
if ($fileContent !== false) {
    file_put_contents($filePath, $fileContent);
    echo "✅ PDF descargado y guardado en: " . $filePath;
} else {
    echo "❌ Error al descargar el PDF.";
}
?>
