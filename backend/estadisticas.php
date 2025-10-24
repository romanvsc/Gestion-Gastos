<?php
/**
 * API para obtener estadísticas y resúmenes
 */

require_once 'config.php';

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    obtenerEstadisticas($conn);
} else {
    sendResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

/**
 * Obtener estadísticas del mes
 */
function obtenerEstadisticas($conn) {
    $usuarioId = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 1;
    $mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('n');
    $anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
    
    // Obtener movimientos agrupados por categoría
    $stmtCategorias = $conn->prepare("
        SELECT 
            categoria,
            tipo,
            COUNT(*) as cantidad,
            SUM(monto) as total
        FROM movimientos 
        WHERE usuario_id = ? AND MONTH(fecha) = ? AND YEAR(fecha) = ?
        GROUP BY categoria, tipo
        ORDER BY total DESC
    ");
    $stmtCategorias->bind_param("iii", $usuarioId, $mes, $anio);
    $stmtCategorias->execute();
    $resultCategorias = $stmtCategorias->get_result();
    
    $categorias = [];
    while ($row = $resultCategorias->fetch_assoc()) {
        $categorias[] = $row;
    }
    
    // Obtener movimientos agrupados por día
    $stmtDias = $conn->prepare("
        SELECT 
            DATE(fecha) as fecha,
            SUM(CASE WHEN tipo = 'gasto' THEN monto ELSE 0 END) as gastos_dia,
            SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE 0 END) as ingresos_dia
        FROM movimientos 
        WHERE usuario_id = ? AND MONTH(fecha) = ? AND YEAR(fecha) = ?
        GROUP BY DATE(fecha)
        ORDER BY fecha ASC
    ");
    $stmtDias->bind_param("iii", $usuarioId, $mes, $anio);
    $stmtDias->execute();
    $resultDias = $stmtDias->get_result();
    
    $movimientosPorDia = [];
    while ($row = $resultDias->fetch_assoc()) {
        $movimientosPorDia[$row['fecha']] = [
            'gastos' => floatval($row['gastos_dia']),
            'ingresos' => floatval($row['ingresos_dia'])
        ];
    }
    
    // Obtener totales del mes
    $stmtTotales = $conn->prepare("
        SELECT 
            SUM(CASE WHEN tipo = 'gasto' THEN monto ELSE 0 END) as total_gastos,
            SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE 0 END) as total_ingresos,
            COUNT(CASE WHEN tipo = 'gasto' THEN 1 END) as num_gastos,
            COUNT(CASE WHEN tipo = 'ingreso' THEN 1 END) as num_ingresos,
            AVG(CASE WHEN tipo = 'gasto' THEN monto END) as promedio_gasto,
            MAX(CASE WHEN tipo = 'gasto' THEN monto END) as gasto_maximo
        FROM movimientos 
        WHERE usuario_id = ? AND MONTH(fecha) = ? AND YEAR(fecha) = ?
    ");
    $stmtTotales->bind_param("iii", $usuarioId, $mes, $anio);
    $stmtTotales->execute();
    $resultTotales = $stmtTotales->get_result();
    $totales = $resultTotales->fetch_assoc();
    
    sendResponse([
        'success' => true,
        'data' => [
            'categorias' => $categorias,
            'movimientos_por_dia' => $movimientosPorDia,
            'totales' => [
                'total_gastos' => floatval($totales['total_gastos'] ?? 0),
                'total_ingresos' => floatval($totales['total_ingresos'] ?? 0),
                'num_gastos' => intval($totales['num_gastos'] ?? 0),
                'num_ingresos' => intval($totales['num_ingresos'] ?? 0),
                'promedio_gasto' => floatval($totales['promedio_gasto'] ?? 0),
                'gasto_maximo' => floatval($totales['gasto_maximo'] ?? 0)
            ]
        ]
    ]);
}
