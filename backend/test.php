<?php
/**
 * Script de prueba de la instalación
 * Verifica que la base de datos y las APIs funcionan correctamente
 */

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test de Instalación - Gestión de Gastos</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 20px; }
        .test { margin: 15px 0; padding: 15px; border-left: 4px solid #ccc; background: #f9f9f9; }
        .test.success { border-color: #10b981; background: #ecfdf5; }
        .test.error { border-color: #ef4444; background: #fef2f2; }
        .test.warning { border-color: #f59e0b; background: #fffbeb; }
        .test h3 { margin-bottom: 10px; }
        .test.success h3 { color: #10b981; }
        .test.error h3 { color: #ef4444; }
        .test.warning h3 { color: #f59e0b; }
        .test p { color: #666; line-height: 1.6; }
        .icon { font-size: 24px; margin-right: 10px; }
        .details { background: white; padding: 10px; margin-top: 10px; border-radius: 5px; font-size: 14px; }
        .btn { display: inline-block; margin-top: 20px; padding: 12px 24px; background: #6366f1; color: white; text-decoration: none; border-radius: 8px; }
        .btn:hover { background: #4f46e5; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 Test de Instalación - Gestión de Gastos</h1>";

$tests_passed = 0;
$tests_failed = 0;

// Test 1: Verificar configuración PHP
echo "<div class='test success'>
    <h3><span class='icon'>✅</span>1. Configuración PHP</h3>
    <p>PHP está corriendo correctamente</p>
    <div class='details'>
        <strong>Versión de PHP:</strong> " . phpversion() . "<br>
        <strong>Servidor:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "
    </div>
</div>";
$tests_passed++;

// Test 2: Verificar extensión MySQL
if (extension_loaded('mysqli')) {
    echo "<div class='test success'>
        <h3><span class='icon'>✅</span>2. Extensión MySQL</h3>
        <p>La extensión mysqli está habilitada</p>
    </div>";
    $tests_passed++;
} else {
    echo "<div class='test error'>
        <h3><span class='icon'>❌</span>2. Extensión MySQL</h3>
        <p>La extensión mysqli NO está disponible. Verifica tu instalación de PHP.</p>
    </div>";
    $tests_failed++;
}

// Test 3: Verificar conexión a la base de datos
require_once 'config.php';

try {
    $conn = getDBConnection();
    echo "<div class='test success'>
        <h3><span class='icon'>✅</span>3. Conexión a la Base de Datos</h3>
        <p>Conexión exitosa a MySQL</p>
        <div class='details'>
            <strong>Host:</strong> " . DB_HOST . "<br>
            <strong>Base de datos:</strong> " . DB_NAME . "<br>
            <strong>Usuario:</strong> " . DB_USER . "
        </div>
    </div>";
    $tests_passed++;
    
    // Test 4: Verificar tablas
    $tables = ['usuarios', 'presupuesto', 'movimientos'];
    $tables_found = [];
    $tables_missing = [];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            $tables_found[] = $table;
        } else {
            $tables_missing[] = $table;
        }
    }
    
    if (empty($tables_missing)) {
        echo "<div class='test success'>
            <h3><span class='icon'>✅</span>4. Tablas de la Base de Datos</h3>
            <p>Todas las tablas necesarias están presentes</p>
            <div class='details'>
                <strong>Tablas encontradas:</strong> " . implode(', ', $tables_found) . "
            </div>
        </div>";
        $tests_passed++;
    } else {
        echo "<div class='test error'>
            <h3><span class='icon'>❌</span>4. Tablas de la Base de Datos</h3>
            <p>Faltan algunas tablas. Ejecuta el archivo database/schema.sql</p>
            <div class='details'>
                <strong>Tablas encontradas:</strong> " . implode(', ', $tables_found) . "<br>
                <strong>Tablas faltantes:</strong> " . implode(', ', $tables_missing) . "
            </div>
        </div>";
        $tests_failed++;
    }
    
    // Test 5: Verificar datos de ejemplo
    $result = $conn->query("SELECT COUNT(*) as count FROM usuarios");
    if ($result) {
        $row = $result->fetch_assoc();
        if ($row['count'] > 0) {
            echo "<div class='test success'>
                <h3><span class='icon'>✅</span>5. Datos de Ejemplo</h3>
                <p>Se encontraron datos iniciales en la base de datos</p>
                <div class='details'>
                    <strong>Usuarios:</strong> " . $row['count'] . "
                </div>
            </div>";
            $tests_passed++;
        } else {
            echo "<div class='test warning'>
                <h3><span class='icon'>⚠️</span>5. Datos de Ejemplo</h3>
                <p>No se encontraron usuarios. Es recomendable ejecutar el archivo schema.sql completo.</p>
            </div>";
        }
    }
    
    // Test 6: Verificar API de movimientos
    $test_query = "SELECT COUNT(*) as count FROM movimientos";
    $result = $conn->query($test_query);
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<div class='test success'>
            <h3><span class='icon'>✅</span>6. API de Movimientos</h3>
            <p>La tabla de movimientos está accesible</p>
            <div class='details'>
                <strong>Movimientos registrados:</strong> " . $row['count'] . "
            </div>
        </div>";
        $tests_passed++;
    } else {
        echo "<div class='test error'>
            <h3><span class='icon'>❌</span>6. API de Movimientos</h3>
            <p>Error al acceder a la tabla de movimientos</p>
        </div>";
        $tests_failed++;
    }
    
    // Test 7: Verificar API de presupuesto
    $test_query = "SELECT COUNT(*) as count FROM presupuesto";
    $result = $conn->query($test_query);
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<div class='test success'>
            <h3><span class='icon'>✅</span>7. API de Presupuesto</h3>
            <p>La tabla de presupuesto está accesible</p>
            <div class='details'>
                <strong>Presupuestos configurados:</strong> " . $row['count'] . "
            </div>
        </div>";
        $tests_passed++;
    } else {
        echo "<div class='test error'>
            <h3><span class='icon'>❌</span>7. API de Presupuesto</h3>
            <p>Error al acceder a la tabla de presupuesto</p>
        </div>";
        $tests_failed++;
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<div class='test error'>
        <h3><span class='icon'>❌</span>3. Conexión a la Base de Datos</h3>
        <p>No se pudo conectar a la base de datos</p>
        <div class='details'>
            <strong>Error:</strong> " . $e->getMessage() . "<br>
            <strong>Solución:</strong> Verifica que MySQL esté corriendo y que las credenciales en config.php sean correctas.
        </div>
    </div>";
    $tests_failed++;
}

// Test 8: Verificar archivos frontend
$frontend_files = [
    '../frontend/index.html',
    '../frontend/app.js'
];

$files_ok = true;
$missing_files = [];

foreach ($frontend_files as $file) {
    if (!file_exists($file)) {
        $files_ok = false;
        $missing_files[] = basename($file);
    }
}

if ($files_ok) {
    echo "<div class='test success'>
        <h3><span class='icon'>✅</span>8. Archivos Frontend</h3>
        <p>Todos los archivos del frontend están presentes</p>
    </div>";
    $tests_passed++;
} else {
    echo "<div class='test error'>
        <h3><span class='icon'>❌</span>8. Archivos Frontend</h3>
        <p>Faltan algunos archivos del frontend</p>
        <div class='details'>
            <strong>Archivos faltantes:</strong> " . implode(', ', $missing_files) . "
        </div>
    </div>";
    $tests_failed++;
}

// Resumen
$total_tests = $tests_passed + $tests_failed;
$class = $tests_failed == 0 ? 'success' : ($tests_passed > $tests_failed ? 'warning' : 'error');

echo "<div class='test $class' style='margin-top: 30px; border-width: 3px;'>
    <h3><span class='icon'>📊</span>Resumen de Tests</h3>
    <div class='details'>
        <strong>Tests ejecutados:</strong> $total_tests<br>
        <strong>Tests exitosos:</strong> $tests_passed<br>
        <strong>Tests fallidos:</strong> $tests_failed
    </div>
</div>";

if ($tests_failed == 0) {
    echo "<a href='../frontend/index.html' class='btn'>🚀 Ir a la Aplicación</a>";
} else {
    echo "<p style='margin-top: 20px; color: #ef4444;'>
        <strong>⚠️ Corrige los errores antes de usar la aplicación.</strong><br>
        Consulta el archivo INSTALACION.md para más ayuda.
    </p>";
}

echo "
    </div>
</body>
</html>";
?>
