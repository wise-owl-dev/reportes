<?php
/**
 * Configuración de Base de Datos
 * Sistema de Reportes IMSS
 */

// Configuración de desarrollo
define('DB_HOST', 'localhost');
define('DB_NAME', 'reportes');
define('DB_USER', 'root');
define('DB_PASS', 'admin');
define('DB_CHARSET', 'utf8mb4');

/**
 * Obtener conexión PDO
 * @return PDO
 */
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

/**
 * Obtener conexión MySQLi
 * @return mysqli
 */
function getMySQLiConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }
    
    if (!$conn->set_charset(DB_CHARSET)) {
        die("Error configurando charset: " . $conn->error);
    }
    
    return $conn;
}
?>
