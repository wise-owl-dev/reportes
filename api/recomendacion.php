<?php

// Cargar configuración
require_once __DIR__ . '/../config/database.php';

// Usar función de conexión
$conn = getMySQLiConnection();

$suggestions = [];

if (isset($_GET['field']) && isset($_GET['query'])) {
    $field = $conn->real_escape_string($_GET['field']);
    $query = $conn->real_escape_string($_GET['query']);
    
    $sql = "SELECT DISTINCT $field FROM documentos WHERE $field LIKE '%$query%'";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $suggestions[] = $row[$field];
    }
}

echo json_encode($suggestions);
$conn->close();
?>
