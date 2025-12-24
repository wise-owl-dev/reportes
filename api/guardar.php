<?php
ob_start();

// Cargar autoloader de Composer
require __DIR__ . '/../vendor/autoload.php';

// Cargar configuración
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/paths.php';
require_once __DIR__ . '/PdfFiller.php';

use setasign\Fpdi\Tcpdf\Fpdi;

// Usar función de conexión
$conn = getMySQLiConnection();

$data = [
    'nombre_del_trabajador' => isset($_POST['nombre_del_trabajador']) ? $_POST['nombre_del_trabajador'] : '',
    'institucion' => isset($_POST['institucion']) ? $_POST['institucion'] : '',
    'adscripcion' => isset($_POST['adscripcion']) ? $_POST['adscripcion'] : '',
    'matricula' => isset($_POST['matricula']) ? $_POST['matricula'] : '',
    'identificacion' => isset($_POST['identificacion']) ? $_POST['identificacion'] : '',
    'telefono' => isset($_POST['telefono']) ? $_POST['telefono'] : '',
    'area_de_salida' => isset($_POST['area_de_salida']) ? $_POST['area_de_salida'] : '',
    'cantidad_bienes' => isset($_POST['cantidad_bienes']) ? $_POST['cantidad_bienes'] : 0,
    'naturaleza_bienes' => isset($_POST['naturaleza_bienes']) ? $_POST['naturaleza_bienes'] : '',
    'descripciones' => isset($_POST['descripciones_json']) ? json_decode($_POST['descripciones_json'], true) : [],
    'proposito_bien' => isset($_POST['proposito_bien']) ? $_POST['proposito_bien'] : '',
    'estado_bienes' => isset($_POST['estado_bienes']) ? $_POST['estado_bienes'] : '',
    'devolucion_bienes' => isset($_POST['devolucion_bienes']) ? $_POST['devolucion_bienes'] : '',
    'fecha_devolucion' => isset($_POST['fecha_devolucion']) ? $_POST['fecha_devolucion'] : '',
    'responsable_entrega' => isset($_POST['responsable_entrega']) ? $_POST['responsable_entrega'] : '',
    'recibe_salida_bienes' => isset($_POST['recibe_salida_bienes']) ? $_POST['recibe_salida_bienes'] : '',
    'lugar_fecha' => isset($_POST['lugar_fecha']) ? $_POST['lugar_fecha'] : '',
    'folio_reporte' => isset($_POST['folio_reporte']) ? $_POST['folio_reporte'] : '',
    'nombre_resguardo' => isset($_POST['nombre_resguardo']) ? $_POST['nombre_resguardo'] : '',
    'cargo_resguardo' => isset($_POST['cargo_resguardo']) ? $_POST['cargo_resguardo'] : '',
    'direccion_resguardo' => isset($_POST['direccion_resguardo']) ? $_POST['direccion_resguardo'] : '',
    'telefono_resguardo' => isset($_POST['telefono_resguardo']) ? $_POST['telefono_resguardo'] : '',
    'recibe_resguardo' => isset($_POST['recibe_resguardo']) ? $_POST['recibe_resguardo'] : '',
    'entrega_resguardo' => isset($_POST['entrega_resguardo']) ? $_POST['entrega_resguardo'] : '',
    'recibe_prestamos_bienes' => isset($_POST['recibe_prestamos_bienes']) ? $_POST['recibe_prestamos_bienes'] : '',
    'matricula_coordinacion' => isset($_POST['matricula_coordinacion']) ? $_POST['matricula_coordinacion'] : '',
    'responsable_control_administrativo' => isset($_POST['responsable_control_administrativo']) ? $_POST['responsable_control_administrativo'] : '',
    'matricula_administrativo' => isset($_POST['matricula_administrativo']) ? $_POST['matricula_administrativo'] : '',
    'departamento_per' => isset($_POST['departamento_per']) ? $_POST['departamento_per'] : '',
];

$stmt = $conn->prepare("INSERT INTO documentos (
    nombre_del_trabajador, institucion, adscripcion, matricula, identificacion,
    telefono, area_de_salida, cantidad_bienes, naturaleza_bienes, descripciones_json, proposito_bien, 
    estado_bienes, devolucion_bienes, fecha_devolucion, responsable_entrega, recibe_salida_bienes, lugar_fecha,
    folio_reporte, nombre_resguardo, cargo_resguardo, direccion_resguardo, telefono_resguardo, recibe_resguardo, 
    entrega_resguardo, recibe_prestamos_bienes, matricula_coordinacion, responsable_control_administrativo, matricula_administrativo
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

$stmt->bind_param(
    "ssssssssssssssssssssssssssss", 
    $data['nombre_del_trabajador'], $data['institucion'], $data['adscripcion'], $data['matricula'], $data['identificacion'],
    $data['telefono'], $data['area_de_salida'], $data['cantidad_bienes'], $data['naturaleza_bienes'], json_encode($data['descripciones']), $data['proposito_bien'], 
    $data['estado_bienes'], $data['devolucion_bienes'], $data['fecha_devolucion'], $data['responsable_entrega'], $data['recibe_salida_bienes'], $data['lugar_fecha'],
    $data['folio_reporte'], $data['nombre_resguardo'], $data['cargo_resguardo'], $data['direccion_resguardo'], $data['telefono_resguardo'], $data['recibe_resguardo'], 
    $data['entrega_resguardo'], $data['recibe_prestamos_bienes'], $data['matricula_coordinacion'], $data['responsable_control_administrativo'], $data['matricula_administrativo']
);

if ($stmt->execute()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $documentos = [
            1 => TEMPLATES_PATH . '/salidaBiene.pdf',
            2 => TEMPLATES_PATH . '/resguardo1.pdf',
            3 => TEMPLATES_PATH . '/prestamo1.pdf'
        ];

        $errors = [];
        $pdfFiller = new PdfFiller();
        
        if (isset($_POST['tipo_documento'])) {
            foreach ($_POST['tipo_documento'] as $opcion) {
                if (array_key_exists($opcion, $documentos)) {
                    $file = $documentos[$opcion];
                    if (!file_exists($file)) {
                        $errors[] = "El archivo '{$file}' no se encuentra en la ruta especificada.";
                        continue;
                    }
                    
                    try {
                        // IMPORTANTE: Primero generar la página desde la plantilla
                        $pdfFiller->generateFromTemplate($file);
                        
                        // Luego llenar el formulario correspondiente
                        switch ($opcion) {
                            case 1:
                                $pdfFiller->fillForm1($file, $data);
                                break;
                            case 2:
                                $pdfFiller->fillForm2($file, $data);
                                break;
                            case 3:
                                $pdfFiller->fillForm3($file, $data);
                                break;
                        }
                    } catch (Exception $e) {
                        $errors[] = "Error al procesar el archivo '{$file}': " . $e->getMessage();
                    }
                }
            }
            
            if (empty($errors)) {
                ob_end_clean();
                $pdfFiller->output('documento_combinado.pdf');
            } else {
                foreach ($errors as $error) {
                    echo $error . '<br>';
                }
            }
        } else {
            echo 'No se seleccionó ningún tipo de documento.';
        }
    }
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>