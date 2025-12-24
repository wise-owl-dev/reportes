<?php
ob_start();

// Cargar autoloader
require __DIR__ . '/../vendor/autoload.php';

// Cargar configuración
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/paths.php';
require_once __DIR__ . '/PdfFiller.php';

use setasign\Fpdi\Tcpdf\Fpdi;

$dsn = 'mysql:host=localhost;dbname=reportes;charset=utf8mb4';
$username = 'root';
$password = 'admin';
try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $pdo->prepare("SELECT nombre_del_trabajador, institucion, adscripcion, matricula, identificacion, telefono, area_de_salida, cantidad_bienes, naturaleza_bienes, descripciones_json, proposito_bien, estado_bienes, devolucion_bienes, fecha_devolucion, responsable_entrega, recibe_salida_bienes, lugar_fecha, folio_reporte, nombre_resguardo, cargo_resguardo,direccion_resguardo,telefono_resguardo,recibe_resguardo,entrega_resguardo,recibe_prestamos_bienes,matricula_coordinacion,responsable_control_administrativo,matricula_administrativo FROM documentos WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
          
            $data['descripciones'] = json_decode($data['descripciones_json'], true);
        
            // ANTES: $files = ['D:\\xampp\\htdocs\\Reportes\\salidaBiene.pdf', ...];
           // DESPUÉS:
            $files = [
              TEMPLATES_PATH . '/salidaBiene.pdf',
              TEMPLATES_PATH . '/resguardo1.pdf',
              TEMPLATES_PATH . '/prestamo1.pdf'
            ];

            $pdfFiller = new PdfFiller();
            $pdfFiller->generateFromTemplate($files[0]);
            $pdfFiller->fillForm1($files[0], $data); 
            $pdfFiller->generateFromTemplate($files[1]);
            $pdfFiller->fillForm2($files[1], $data); 
            $pdfFiller->generateFromTemplate($files[2]);
            $pdfFiller->fillForm3($files[2], $data); 
            $pdfFiller->output('documento.pdf');
        } else {
            echo "No se encontraron datos para el ID especificado.";
        }
    } else {
        echo "No se ha especificado ningún ID.";
    }
} catch (PDOException $e) {
    echo "Error en la conexión a la base de datos: " . $e->getMessage();
}

?>
