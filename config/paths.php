<?php
/**
 * Definición de rutas del proyecto
 */

// Ruta raíz del proyecto
define('ROOT_PATH', dirname(__DIR__));

// Rutas de aplicación
define('APP_PATH', ROOT_PATH . '/app');
define('CONTROLLERS_PATH', APP_PATH . '/controllers');
define('VIEWS_PATH', APP_PATH . '/views');

// Rutas públicas
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('CSS_PATH', PUBLIC_PATH . '/css');
define('IMAGES_PATH', PUBLIC_PATH . '/images');

// Rutas de recursos
define('TEMPLATES_PATH', ROOT_PATH . '/templates');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');

// Rutas de librerías
define('VENDOR_PATH', ROOT_PATH . '/vendor');
define('LIB_PATH', ROOT_PATH . '/lib');

// URLs relativas (ajustar según configuración)
define('BASE_URL', '/reportes');
define('CSS_URL', BASE_URL . '/public/css');
define('IMAGES_URL', BASE_URL . '/public/images');

/**
 * Helper para generar rutas de archivos
 */
function asset($path) {
    return BASE_URL . '/public/' . ltrim($path, '/');
}

/**
 * Helper para generar rutas absolutas
 */
function base_path($path = '') {
    return ROOT_PATH . '/' . ltrim($path, '/');
}
?>
