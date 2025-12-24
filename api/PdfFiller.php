<?php

require_once __DIR__ . '/../vendor/autoload.php';

use setasign\Fpdi\Tcpdf\Fpdi;

class PdfFiller {
    private $pdf;
    
    public function __construct() {
        $this->pdf = new Fpdi();
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        $this->pdf->SetMargins(10, 10, 10); 
        $this->pdf->SetAutoPageBreak(true, 10);
    }
    
    public function generateFromTemplate($file) {
        if (file_exists($file)) {
            $this->pdf->AddPage();  
            $this->pdf->setSourceFile($file);  
            $templateId = $this->pdf->importPage(1);  
            $this->pdf->useTemplate($templateId, 0, 0, null, null, true);  
        } else {
            die("El archivo PDF no se encuentra en la ruta especificada: " . $file);
        }
    }

    private function formatFechaEspanol($fechaInput) {
        if (!$fechaInput) return "";
        $fecha = new DateTime($fechaInput);
        $formateador = new IntlDateFormatter(
            'es_MX',
            IntlDateFormatter::LONG,
            IntlDateFormatter::NONE,
            'America/Mexico_City',
            IntlDateFormatter::GREGORIAN,
            "d 'de' MMMM 'de' y"
        );
        return $formateador->format($fecha);
    }

    public function fillCommonFields($data) {
        $this->pdf->SetFont('Helvetica', '', 9);

        //Nombre
        $this->pdf->SetXY(23, 50);
        $nombre_limitado = mb_strimwidth($data['nombre_del_trabajador'], 0, 65, '...');
        $this->pdf->Write(10, $nombre_limitado);

        //Institución
        $this->pdf->SetXY(129, 52.5);
        $institucion_limitado = mb_strimwidth($data['institucion'], 0, 50, '...');
        $this->pdf->Write(5, $institucion_limitado);

        //Adscripción
        $adscripcion_limitado = mb_strimwidth($data['adscripcion'], 0, 50, '...');
        $this->pdf->SetXY(38, 55.5);
        $this->pdf->Write(10, $adscripcion_limitado);

        //Matrícula
        $this->pdf->SetXY(170, 57.5);
        $this->pdf->Write(6, $data['matricula']);

        //Naturaleza de los bienes
        $this->pdf->SetXY(65, 95);
        $this->pdf->Write(10, $data['naturaleza_bienes']);

        //Identificación
        $identificacion_limitado = mb_strimwidth($data['identificacion'], 0, 45, '..');
        $this->pdf->SetXY(44, 60.5);
        $this->pdf->Write(11, $identificacion_limitado);

        //Teléfono
        $this->pdf->SetXY(167, 60.5);
        $this->pdf->Write(11, $data['telefono']);

        //Área de salida
        $this->pdf->SetXY(55, 73);
        $this->pdf->Write(10, $data['area_de_salida']);

        //Cantidad de bienes
        $this->pdf->SetXY(30, 95);
        $this->pdf->Write(10, $data['cantidad_bienes']);

        //Propósito de los bienes
        $this->pdf->SetXY(42, 129);
        $this->pdf->Write(10, $data['proposito_bien']);

        //Estado de los bienes
        $this->pdf->SetXY(115, 137);
        $this->pdf->Write(10, $data['estado_bienes']);

        //Manejo de la naturaleza de los bienes
        $this->handleNatureOfGoods($data['naturaleza_bienes']);

        $this->handleReturns($data['devolucion_bienes'], $data['fecha_devolucion']);

        //Responsable de la entrega
        $this->pdf->SetXY(120, 191);
        $this->pdf->Write(10, $data['recibe_salida_bienes']);

        //Fecha y lugar
        $this->pdf->SetXY(195, 204); 
        $fechaFormateada = $this->formatFechaEspanol($data['lugar_fecha']);
        $this->pdf->Write(8, "Oaxaca de Juárez, Oaxaca a " . $fechaFormateada);
    }
    
    private function handleNatureOfGoods($naturaleza) {
        $xPosBC = 14;   
        $yPosBC = 232;
        $xPosBMC = 14;   
        $yPosBMC = 241;
        $xPosBMNC = 112;  
        $yPosBMNC = 232;
        $xPosBPS = 112;  
        $yPosBPS = 241;
        $rectWidth = 15;
        $rectHeight = 5;
        $this->pdf->SetFillColor(173, 216, 230);
        $this->pdf->SetAlpha(0.5); 
        switch ($naturaleza) {
            case 'BC':
                $this->pdf->Rect($xPosBC, $yPosBC, $rectWidth, $rectHeight, 'F'); 
                break;
            case 'BMC':
                $this->pdf->Rect($xPosBMC, $yPosBMC, $rectWidth, $rectHeight, 'F');
                break;
            case 'BMNC':
                $this->pdf->Rect($xPosBMNC, $yPosBMNC, $rectWidth, $rectHeight, 'F');
                break;
            case 'BPS':
                $this->pdf->Rect($xPosBPS, $yPosBPS, $rectWidth, $rectHeight, 'F');
                break;
        }
        $this->pdf->SetAlpha(1.0);
    }
    
    private function handleReturns($devolucion, $fechaDevolucion) {
        $xPosSi = 77; 
        $yPos = 144;  
        $xPosNo = 92; 
        if ($devolucion == 'si') {
            $this->pdf->SetXY($xPosSi, $yPos);
            $this->pdf->Write(15, 'X'); 
        } elseif ($devolucion == 'no') {
            $this->pdf->SetXY($xPosNo, $yPos);
            $this->pdf->Write(15, 'X'); 
        }
        if ($devolucion == 'si' && !empty($fechaDevolucion)) {
            $this->pdf->SetXY(115, 146.5); 
            $this->pdf->Write(10, $fechaDevolucion);
        }
    }
    
    public function fillForm1($file, $data) {
        $this->pdf->setSourceFile($file);
        $this->fillCommonFields($data);
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->SetXY(199, 181);
        $this->pdf->Write(10, $data['responsable_entrega']);
        $this->handleDescriptions($data['descripciones']);
    }
    
    private function handleDescriptions($descripciones) {
    $this->pdf->SetFont('helvetica', '', 8);
    $this->pdf->SetTextColor(0, 0, 0);

    $x = 98;          // columna descripción
    $y = 98;          // inicio vertical
    $width = 100;      // ancho REAL del recuadro
    $lineHeight = 5;


    // 🔹 Limitar a máximo 5 descripciones
    $descripciones = array_slice($descripciones, 0, 6);

    foreach ($descripciones as $descripcion) {
        $this->pdf->SetXY($x, $y);
        $this->pdf->MultiCell($width, $lineHeight, $descripcion, 0, 'L');
        $y = $this->pdf->GetY(); // ← posición real después del texto
    }
}

    
    public function fillForm2($file, $data) {
        $this->pdf->SetFont('helvetica', '', 7);
        $this->pdf->SetTextColor(0, 0, 0);

        // Fecha
        $this->pdf->SetXY(111.9, 55); 
        $fechaFormateada = $this->formatFechaEspanol($data['lugar_fecha']);
        $this->pdf->Write(8, "Oaxaca de Juárez, Oaxaca a " . $fechaFormateada);


        //Folio
        $this->pdf->SetXY(176, 54);
        $this->pdf->Write(10, $data['folio_reporte']); 


        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetTextColor(0, 0, 0);

        //Institución
        $this->pdf->SetXY(55, 71);
        $this->pdf->Write(10, $data['institucion']); 

        //Nombre del resguardo
        $this->pdf->SetXY(55, 76);
        $this->pdf->Write(8, $data['nombre_resguardo']); 

        //Cargo del resguardo
        $this->pdf->SetXY(55, 80);   
        $this->pdf->Write(8, $data['cargo_resguardo']); 

        //Cantidad de bienes
        $this->pdf->SetXY(35, 110);
        $this->pdf->Write(8, $data['cantidad_bienes']);

        //Dirección del resguardo
        $this->pdf->SetXY(55, 84);
        $this->pdf->Write(8, $data['direccion_resguardo']);
        
        //Teléfono del resguardo
        $this->pdf->SetXY(55, 88);
        $this->pdf->Write(8, $data['telefono_resguardo']); 

        //Responsable del resguardo
        $this->pdf->SetXY(28, 228);
        $this->pdf->Write(8, $data['nombre_resguardo']); 

        //Cargo del responsable del resguardo
        $this->pdf->SetXY(20, 232);   
        $this->pdf->Write(8, $data['cargo_resguardo']); 

        // Recibe y entrega del resguardo
        $this->pdf->SetXY(118, 228);
        $this->pdf->Write(10, $data['recibe_resguardo']);

        // Entrega del resguardo
        $this->pdf->SetXY(111, 231);   
        $this->pdf->Write(10, $data['entrega_resguardo']); 

        if (count($data['descripciones']) > 3) {
            $this->pdf->SetFont('helvetica', '', 8);
            $this->pdf->SetXY(127, 109);
            $this->pdf->Write(4, 'Información en el anexo');
            $this->pdf->SetXY(127, 116);
            $this->pdf->Write(4, 'Información en el anexo');
            $this->pdf->SetXY(127, 123);
            $this->pdf->Write(4, 'Información en el anexo');
            $this->addDescriptionTable($data['descripciones']);
        } else {
            $this->displayDescriptionsGrouped($data['descripciones']);
        }
    }
    
    private function displayDescriptionsGrouped($descripciones) {
        $this->pdf->SetFont('helvetica', '', 8);
        $yPos = 109; 
        $conceptos = [];
        $marcas = [];
        $numerosSerie = [];
        $descripcionesLargas = []; 
        foreach ($descripciones as $descripcion) { 
            if (strlen($descripcion) > 42) {
                $descripcionesLargas[] = $descripcion;
                $conceptos[] = 'Información en el anexo';
                $marcas[] = 'Información en el anexo';
                $numerosSerie[] = 'Información en el anexo';
                continue;
            }
            $parts = explode(' ', $descripcion, 3);
            $conceptos[] = isset($parts[0]) ? $parts[0] : ''; 
            $marcas[] = isset($parts[1]) ? $parts[1] : '';   
            $numerosSerie[] = isset($parts[2]) ? $parts[2] : ''; 
        }
        $conceptosTexto = implode(', ', $conceptos);
        $marcasTexto = implode(', ', $marcas);
        $numerosSerieTexto = implode(', ', $numerosSerie);
        $this->pdf->SetXY(128, $yPos); 
        $this->pdf->Write(5, '' . $conceptosTexto);
        $yPos += 5; 
        $this->pdf->SetXY(128, $yPos); 
        $this->pdf->Write(8, '' . $marcasTexto);
        $yPos += 5; 
        $this->pdf->SetXY(128, $yPos); 
        $this->pdf->Write(13, '' . $numerosSerieTexto);
        if (!empty($descripcionesLargas)) {
            $this->addDescriptionTable($descripcionesLargas);
        }
    }

    private function addDescriptionTable($descripciones) {
        $this->pdf->AddPage();
        $this->pdf->SetFont('helvetica', '', 8);
        $colWidths = [15, 45, 45, 60];
        $startX = ($this->pdf->GetPageWidth() - array_sum($colWidths)) / 2;
        $y = 20;
        $this->pdf->SetFillColor(230,230,230);
        $headers = ['No.', 'Marca', 'Modelo', 'Número de Serie'];
        $this->pdf->SetXY($startX, $y);
        foreach ($headers as $i => $h) {
            $this->pdf->Cell($colWidths[$i], 8, $h, 1, 0, 'C', true);
        }
        $y += 8;
        foreach ($descripciones as $i => $descripcion) {
            $parts = explode(' ', $descripcion, 3);
            $marca    = isset($parts[0]) ? $parts[0] : '';
            $concepto = isset($parts[1]) ? $parts[1] : '';
            $serie    = isset($parts[2]) ? $parts[2] : '';
            $h1 = $this->pdf->getStringHeight($colWidths[1], $marca);
            $h2 = $this->pdf->getStringHeight($colWidths[2], $concepto);
            $h3 = $this->pdf->getStringHeight($colWidths[3], $serie);
            $rowHeight = max($h1, $h2, $h3, 6);
            if ($y + $rowHeight > $this->pdf->GetPageHeight() - 20) {
                $this->pdf->AddPage();
                $y = 20;
            }
            $this->pdf->SetXY($startX, $y);
            $this->pdf->Cell($colWidths[0], $rowHeight, $i + 1, 1, 0, 'C');
            $this->pdf->Rect($startX + $colWidths[0], $y, $colWidths[1], $rowHeight);
            $this->pdf->Rect($startX + $colWidths[0] + $colWidths[1], $y, $colWidths[2], $rowHeight);
            $this->pdf->Rect(
                $startX + $colWidths[0] + $colWidths[1] + $colWidths[2],
                $y,
                $colWidths[3],
                $rowHeight
            );
            $this->pdf->SetXY($startX + $colWidths[0] + 1, $y + 2);
            $this->pdf->MultiCell($colWidths[1] - 2, 5, $marca, 0, 'L');
            $this->pdf->SetXY($startX + $colWidths[0] + $colWidths[1] + 1, $y + 2);
            $this->pdf->MultiCell($colWidths[2] - 2, 5, $concepto, 0, 'L');
            $this->pdf->SetXY(
                $startX + $colWidths[0] + $colWidths[1] + $colWidths[2] + 1,
                $y + 2
            );
            $this->pdf->MultiCell($colWidths[3] - 2, 5, $serie, 0, 'L');
            $y += $rowHeight;
        }
    }
     
    public function fillForm3($file, $data) {
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->SetXY(110, 20);  
        $this->pdf->Write(10, $data['adscripcion']); 
        $this->pdf->SetXY(77, 51);
        $this->pdf->Write(10, $data['naturaleza_bienes']);
        $xPosBC = 3;
        $yPosBC = 231;
        $xPosBMC = 3;
        $yPosBMC = 235;
        $xPosBMNC = 108;
        $yPosBMNC = 231;
        $xPosBPS = 108;
        $yPosBPS = 235;
        $rectWidth = 13;
        $rectHeight = 3;
        $this->pdf->SetFillColor(173, 216, 230); 
        $this->pdf->SetAlpha(0.4); 
        if ($data['naturaleza_bienes'] == 'BC') {
            $this->pdf->Rect($xPosBC, $yPosBC, $rectWidth, $rectHeight, 'F');
        } elseif ($data['naturaleza_bienes'] == 'BMC') {
            $this->pdf->Rect($xPosBMC, $yPosBMC, $rectWidth, $rectHeight, 'F');
        } elseif ($data['naturaleza_bienes'] == 'BMNC') {
            $this->pdf->Rect($xPosBMNC, $yPosBMNC, $rectWidth, $rectHeight, 'F');
        } elseif ($data['naturaleza_bienes'] == 'BPS') {
            $this->pdf->Rect($xPosBPS, $yPosBPS, $rectWidth, $rectHeight, 'F');
        }

        //Cantidad de bienes
        $this->pdf->SetAlpha(1.0);
        $this->pdf->SetXY(26, 51);
        $this->pdf->Write(10, $data['cantidad_bienes']);

        //Propósito de los bienes
        $this->pdf->SetXY(68, 153);
        $this->pdf->Write(10, $data['estado_bienes']);

        //Responsable que recibe los bienes
        $this->pdf->SetXY(8, 194);
        $this->pdf->Write(10, $data['recibe_prestamos_bienes']);

        //Responsable de la coordinación
        $this->pdf->SetXY(5, 203);
        $this->pdf->Write(10, $data['matricula_coordinacion']);

        //Responsable del control administrativo
        $this->pdf->SetXY(108, 194);
        $this->pdf->Write(10, $data['responsable_control_administrativo']);

        //Matrícula del administrativo
        $this->pdf->SetXY(110, 203);
        $this->pdf->Write(10, $data['matricula_administrativo']);

        //Fecha y lugar
        $this->pdf->SetXY(40, 216);
        $fechaFormateada = $this->formatFechaEspanol($data['lugar_fecha']);
        $this->pdf->Write(8, "Oaxaca de Juárez, Oaxaca a " . $fechaFormateada);

        //Departamento
        $this->pdf->SetXY(110,167);
        $this->pdf->Write(10,$data['departamento_per']);
        $this->displayDescriptionsLineByLine($data['descripciones']);
    }
    
    private function displayDescriptionsLineByLine($descripciones) {
        $this->pdf->SetFont('helvetica', '', 8);
        $yPos = 54;
        foreach ($descripciones as $descripcion) {
            $lineas = str_split($descripcion, 50);
            foreach ($lineas as $linea) {
                $this->pdf->SetXY(120, $yPos); 
                $this->pdf->Write(5, $linea); 
                $yPos += 4; 
            }
            $yPos += 0;
        }
    }
    
    public function output($filename = 'documento.pdf') {
        ob_end_clean();
        $this->pdf->Output($filename, 'I');   
    }
}