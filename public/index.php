<?php
/**
 * Punto de entrada principal
 * Redirecciona al menú
 */

// Cargar configuración
require_once __DIR__ . '/../config/paths.php';

// Detectar protocolo
if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
    $uri = 'https://';
} else {
    $uri = 'http://';
}

$uri .= $_SERVER['HTTP_HOST'];

// Redirigir al menú
header('Location: ' . $uri . BASE_URL . '/app/views/menu.php');
exit;
?>
