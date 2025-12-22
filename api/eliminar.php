<?php
// Cargar configuración
require_once __DIR__ . '/../config/database.php';

// Usar función de conexión
$conn = getMySQLiConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM documentos WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "Registro eliminado correctamente.";
    } else {
        echo "Error al eliminar el registro.";
    }
    $stmt->close();
}
$conn->close();
?>
