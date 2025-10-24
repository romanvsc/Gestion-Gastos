<?php
/**
 * API para gestión de presupuestos mensuales
 */

require_once 'config.php';

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        obtenerPresupuesto($conn);
        break;
    case 'POST':
        crearOActualizarPresupuesto($conn);
        break;
    default:
        sendResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

/**
 * Obtener presupuesto de un mes específico
 */
function obtenerPresupuesto($conn) {
    $usuarioId = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 1;
    $mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('n');
    $anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
    
    $stmt = $conn->prepare("SELECT * FROM presupuesto WHERE usuario_id = ? AND mes = ? AND anio = ?");
    $stmt->bind_param("iii", $usuarioId, $mes, $anio);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Calcular días del mes y presupuesto diario
        $diasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
        $presupuestoDiario = floatval($row['monto_total']) / $diasMes;
        
        // Obtener gastos e ingresos del mes
        $stmtMovimientos = $conn->prepare("
            SELECT 
                SUM(CASE WHEN tipo = 'gasto' THEN monto ELSE 0 END) as total_gastos,
                SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE 0 END) as total_ingresos
            FROM movimientos 
            WHERE usuario_id = ? AND MONTH(fecha) = ? AND YEAR(fecha) = ?
        ");
        $stmtMovimientos->bind_param("iii", $usuarioId, $mes, $anio);
        $stmtMovimientos->execute();
        $resultMovimientos = $stmtMovimientos->get_result();
        $movimientos = $resultMovimientos->fetch_assoc();
        
        $totalGastos = floatval($movimientos['total_gastos'] ?? 0);
        $totalIngresos = floatval($movimientos['total_ingresos'] ?? 0);
        $presupuestoTotal = floatval($row['monto_total']);
        $saldoRestante = $presupuestoTotal + $totalIngresos - $totalGastos;
        $porcentajeGastado = $presupuestoTotal > 0 ? ($totalGastos / $presupuestoTotal) * 100 : 0;
        
        // Calcular presupuesto por día
        $diaActual = date('j');
        $diasTranscurridos = ($mes == date('n') && $anio == date('Y')) ? $diaActual : $diasMes;
        $presupuestoGastado = $diasTranscurridos * $presupuestoDiario;
        
        sendResponse([
            'success' => true,
            'data' => [
                'id' => $row['id'],
                'mes' => $row['mes'],
                'anio' => $row['anio'],
                'monto_total' => $presupuestoTotal,
                'presupuesto_diario' => round($presupuestoDiario, 2),
                'dias_mes' => $diasMes,
                'total_gastos' => $totalGastos,
                'total_ingresos' => $totalIngresos,
                'saldo_restante' => $saldoRestante,
                'porcentaje_gastado' => round($porcentajeGastado, 2),
                'dias_transcurridos' => $diasTranscurridos,
                'presupuesto_esperado_gastado' => round($presupuestoGastado, 2),
                'diferencia' => round($presupuestoGastado - $totalGastos, 2)
            ]
        ]);
    } else {
        // No hay presupuesto configurado para este mes
        sendResponse([
            'success' => true,
            'data' => null,
            'message' => 'No hay presupuesto configurado para este mes'
        ]);
    }
}

/**
 * Crear o actualizar presupuesto mensual
 */
function crearOActualizarPresupuesto($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar campos requeridos
    $missing = validateRequired($input, ['monto_total']);
    if (!empty($missing)) {
        sendResponse([
            'success' => false,
            'message' => 'Campos requeridos faltantes: ' . implode(', ', $missing)
        ], 400);
    }
    
    $montoTotal = floatval($input['monto_total']);
    if ($montoTotal < 0) {
        sendResponse(['success' => false, 'message' => 'El monto no puede ser negativo'], 400);
    }
    
    $usuarioId = isset($input['usuario_id']) ? intval($input['usuario_id']) : 1;
    $mes = isset($input['mes']) ? intval($input['mes']) : date('n');
    $anio = isset($input['anio']) ? intval($input['anio']) : date('Y');
    
    // Usar INSERT ... ON DUPLICATE KEY UPDATE para crear o actualizar
    $sql = "INSERT INTO presupuesto (usuario_id, mes, anio, monto_total) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE monto_total = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiidd", $usuarioId, $mes, $anio, $montoTotal, $montoTotal);
    
    if ($stmt->execute()) {
        // Obtener el presupuesto actualizado
        $stmt = $conn->prepare("SELECT * FROM presupuesto WHERE usuario_id = ? AND mes = ? AND anio = ?");
        $stmt->bind_param("iii", $usuarioId, $mes, $anio);
        $stmt->execute();
        $result = $stmt->get_result();
        $presupuesto = $result->fetch_assoc();
        
        sendResponse([
            'success' => true,
            'message' => 'Presupuesto guardado exitosamente',
            'data' => $presupuesto
        ], 201);
    } else {
        sendResponse([
            'success' => false,
            'message' => 'Error al guardar el presupuesto',
            'error' => $stmt->error
        ], 500);
    }
}
