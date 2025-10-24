<?php
/**
 * API para gestión de movimientos (gastos e ingresos)
 */

require_once 'config.php';

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        obtenerMovimientos($conn);
        break;
    case 'POST':
        crearMovimiento($conn);
        break;
    case 'PUT':
        actualizarMovimiento($conn);
        break;
    case 'DELETE':
        eliminarMovimiento($conn);
        break;
    default:
        sendResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

/**
 * Obtener movimientos con filtros opcionales
 */
function obtenerMovimientos($conn) {
    $usuarioId = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 1;
    $mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('n');
    $anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
    $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : null;
    $categoria = isset($_GET['categoria']) ? $_GET['categoria'] : null;
    
    $sql = "SELECT * FROM movimientos WHERE usuario_id = ? AND MONTH(fecha) = ? AND YEAR(fecha) = ?";
    $params = [$usuarioId, $mes, $anio];
    $types = "iii";
    
    if ($tipo) {
        $sql .= " AND tipo = ?";
        $params[] = $tipo;
        $types .= "s";
    }
    
    if ($categoria) {
        $sql .= " AND categoria = ?";
        $params[] = $categoria;
        $types .= "s";
    }
    
    $sql .= " ORDER BY fecha DESC, fecha_creacion DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $movimientos = [];
    while ($row = $result->fetch_assoc()) {
        $movimientos[] = $row;
    }
    
    // Calcular totales
    $totalGastos = 0;
    $totalIngresos = 0;
    foreach ($movimientos as $mov) {
        if ($mov['tipo'] === 'gasto') {
            $totalGastos += floatval($mov['monto']);
        } else {
            $totalIngresos += floatval($mov['monto']);
        }
    }
    
    sendResponse([
        'success' => true,
        'data' => $movimientos,
        'resumen' => [
            'total_gastos' => $totalGastos,
            'total_ingresos' => $totalIngresos,
            'saldo' => $totalIngresos - $totalGastos,
            'cantidad' => count($movimientos)
        ]
    ]);
}

/**
 * Crear un nuevo movimiento
 */
function crearMovimiento($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar campos requeridos
    $missing = validateRequired($input, ['tipo', 'monto', 'fecha', 'descripcion']);
    if (!empty($missing)) {
        sendResponse([
            'success' => false,
            'message' => 'Campos requeridos faltantes: ' . implode(', ', $missing)
        ], 400);
    }
    
    // Validaciones adicionales
    if (!in_array($input['tipo'], ['gasto', 'ingreso'])) {
        sendResponse(['success' => false, 'message' => 'Tipo debe ser "gasto" o "ingreso"'], 400);
    }
    
    $monto = floatval($input['monto']);
    if ($monto <= 0) {
        sendResponse(['success' => false, 'message' => 'El monto debe ser mayor a 0'], 400);
    }
    
    $usuarioId = isset($input['usuario_id']) ? intval($input['usuario_id']) : 1;
    $tipo = $input['tipo'];
    $fecha = $input['fecha'];
    $descripcion = trim($input['descripcion']);
    $categoria = isset($input['categoria']) ? trim($input['categoria']) : 'otros';
    
    $sql = "INSERT INTO movimientos (usuario_id, tipo, monto, fecha, descripcion, categoria) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isdsss", $usuarioId, $tipo, $monto, $fecha, $descripcion, $categoria);
    
    if ($stmt->execute()) {
        $nuevoId = $conn->insert_id;
        
        // Obtener el movimiento recién creado
        $stmt = $conn->prepare("SELECT * FROM movimientos WHERE id = ?");
        $stmt->bind_param("i", $nuevoId);
        $stmt->execute();
        $result = $stmt->get_result();
        $movimiento = $result->fetch_assoc();
        
        sendResponse([
            'success' => true,
            'message' => 'Movimiento creado exitosamente',
            'data' => $movimiento
        ], 201);
    } else {
        sendResponse([
            'success' => false,
            'message' => 'Error al crear el movimiento',
            'error' => $stmt->error
        ], 500);
    }
}

/**
 * Actualizar un movimiento existente
 */
function actualizarMovimiento($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || empty($input['id'])) {
        sendResponse(['success' => false, 'message' => 'ID del movimiento es requerido'], 400);
    }
    
    $id = intval($input['id']);
    $updates = [];
    $params = [];
    $types = "";
    
    if (isset($input['tipo'])) {
        if (!in_array($input['tipo'], ['gasto', 'ingreso'])) {
            sendResponse(['success' => false, 'message' => 'Tipo debe ser "gasto" o "ingreso"'], 400);
        }
        $updates[] = "tipo = ?";
        $params[] = $input['tipo'];
        $types .= "s";
    }
    
    if (isset($input['monto'])) {
        $monto = floatval($input['monto']);
        if ($monto <= 0) {
            sendResponse(['success' => false, 'message' => 'El monto debe ser mayor a 0'], 400);
        }
        $updates[] = "monto = ?";
        $params[] = $monto;
        $types .= "d";
    }
    
    if (isset($input['fecha'])) {
        $updates[] = "fecha = ?";
        $params[] = $input['fecha'];
        $types .= "s";
    }
    
    if (isset($input['descripcion'])) {
        $updates[] = "descripcion = ?";
        $params[] = trim($input['descripcion']);
        $types .= "s";
    }
    
    if (isset($input['categoria'])) {
        $updates[] = "categoria = ?";
        $params[] = trim($input['categoria']);
        $types .= "s";
    }
    
    if (empty($updates)) {
        sendResponse(['success' => false, 'message' => 'No hay datos para actualizar'], 400);
    }
    
    $sql = "UPDATE movimientos SET " . implode(", ", $updates) . " WHERE id = ?";
    $params[] = $id;
    $types .= "i";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Obtener el movimiento actualizado
            $stmt = $conn->prepare("SELECT * FROM movimientos WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $movimiento = $result->fetch_assoc();
            
            sendResponse([
                'success' => true,
                'message' => 'Movimiento actualizado exitosamente',
                'data' => $movimiento
            ]);
        } else {
            sendResponse(['success' => false, 'message' => 'No se encontró el movimiento'], 404);
        }
    } else {
        sendResponse([
            'success' => false,
            'message' => 'Error al actualizar el movimiento',
            'error' => $stmt->error
        ], 500);
    }
}

/**
 * Eliminar un movimiento
 */
function eliminarMovimiento($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || empty($input['id'])) {
        sendResponse(['success' => false, 'message' => 'ID del movimiento es requerido'], 400);
    }
    
    $id = intval($input['id']);
    
    $stmt = $conn->prepare("DELETE FROM movimientos WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            sendResponse([
                'success' => true,
                'message' => 'Movimiento eliminado exitosamente'
            ]);
        } else {
            sendResponse(['success' => false, 'message' => 'No se encontró el movimiento'], 404);
        }
    } else {
        sendResponse([
            'success' => false,
            'message' => 'Error al eliminar el movimiento',
            'error' => $stmt->error
        ], 500);
    }
}
