-- Base de datos para Gestión de Gastos Personales
-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS gestion_gastos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_gastos;

-- Tabla de usuarios (preparado para futuras expansiones)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de presupuestos mensuales
CREATE TABLE IF NOT EXISTS presupuesto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT DEFAULT 1,
    mes INT NOT NULL,
    anio INT NOT NULL,
    monto_total DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_presupuesto (usuario_id, mes, anio),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_mes_anio (mes, anio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de movimientos (gastos e ingresos)
CREATE TABLE IF NOT EXISTS movimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT DEFAULT 1,
    tipo ENUM('gasto', 'ingreso') NOT NULL DEFAULT 'gasto',
    monto DECIMAL(12, 2) NOT NULL,
    fecha DATE NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    categoria VARCHAR(50) DEFAULT 'otros',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_fecha (usuario_id, fecha),
    INDEX idx_tipo (tipo),
    INDEX idx_categoria (categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar un usuario por defecto
INSERT INTO usuarios (nombre, email) VALUES ('Usuario Demo', 'demo@gestiongastos.com');

-- Insertar un presupuesto de ejemplo para el mes actual
INSERT INTO presupuesto (usuario_id, mes, anio, monto_total) 
VALUES (1, MONTH(CURDATE()), YEAR(CURDATE()), 100000.00)
ON DUPLICATE KEY UPDATE monto_total = 100000.00;

-- Insertar algunos movimientos de ejemplo
INSERT INTO movimientos (usuario_id, tipo, monto, fecha, descripcion, categoria) VALUES
(1, 'gasto', 1500.00, CURDATE(), 'Supermercado del mes', 'comida'),
(1, 'gasto', 500.00, CURDATE() - INTERVAL 1 DAY, 'Transporte público', 'transporte'),
(1, 'ingreso', 5000.00, CURDATE() - INTERVAL 2 DAY, 'Freelance', 'trabajo'),
(1, 'gasto', 2000.00, CURDATE() - INTERVAL 3 DAY, 'Cena con amigos', 'ocio');
