<?php
/**
 * Script de prueba de conexión
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

// Información de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gestion_gastos');

$response = [
    'success' => false,
    'message' => '',
    'checks' => []
];

// 1. Verificar extensión mysqli
if (extension_loaded('mysqli')) {
    $response['checks'][] = '✓ Extensión mysqli cargada';
} else {
    $response['checks'][] = '✗ Extensión mysqli NO disponible';
    $response['message'] = 'La extensión mysqli no está disponible';
    echo json_encode($response);
    exit;
}

// 2. Intentar conexión al servidor MySQL
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        $response['checks'][] = '✗ No se puede conectar a MySQL: ' . $conn->connect_error;
        $response['message'] = 'Error de conexión a MySQL';
        echo json_encode($response);
        exit;
    }
    
    $response['checks'][] = '✓ Conexión a MySQL exitosa';
    
    // 3. Verificar si existe la base de datos
    $result = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    
    if ($result->num_rows > 0) {
        $response['checks'][] = '✓ Base de datos "' . DB_NAME . '" existe';
        
        // 4. Seleccionar la base de datos
        if ($conn->select_db(DB_NAME)) {
            $response['checks'][] = '✓ Base de datos seleccionada correctamente';
            
            // 5. Verificar tablas
            $tables = ['usuarios', 'presupuesto', 'movimientos'];
            foreach ($tables as $table) {
                $result = $conn->query("SHOW TABLES LIKE '$table'");
                if ($result->num_rows > 0) {
                    $response['checks'][] = "✓ Tabla '$table' existe";
                } else {
                    $response['checks'][] = "✗ Tabla '$table' NO existe";
                }
            }
            
            // 6. Verificar usuario por defecto
            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = 1");
            if ($stmt) {
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $response['checks'][] = '✓ Usuario por defecto (ID=1) existe';
                } else {
                    $response['checks'][] = '✗ Usuario por defecto (ID=1) NO existe';
                }
                $stmt->close();
            }
            
            $response['success'] = true;
            $response['message'] = 'Todas las verificaciones completadas';
            
        } else {
            $response['checks'][] = '✗ No se pudo seleccionar la base de datos';
            $response['message'] = 'Error al seleccionar la base de datos';
        }
        
    } else {
        $response['checks'][] = '✗ Base de datos "' . DB_NAME . '" NO existe';
        $response['message'] = 'La base de datos no existe. Debes crearla primero.';
        $response['checks'][] = '';
        $response['checks'][] = 'SOLUCIÓN:';
        $response['checks'][] = '1. Abre phpMyAdmin (http://localhost/phpmyadmin)';
        $response['checks'][] = '2. Crea una nueva base de datos llamada "gestion_gastos"';
        $response['checks'][] = '3. Importa el archivo database/schema.sql';
    }
    
    $conn->close();
    
} catch (Exception $e) {
    $response['checks'][] = '✗ Error: ' . $e->getMessage();
    $response['message'] = 'Error en la verificación';
}

echo json_encode($response, JSON_PRETTY_PRINT);
