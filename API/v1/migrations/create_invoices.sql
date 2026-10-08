-- Migration: crear tablas de facturas
CREATE TABLE IF NOT EXISTS `facturas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cita_id` int(11) DEFAULT NULL,
  `cliente_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fecha` date NOT NULL,
  `estado` varchar(50) NOT NULL DEFAULT 'Pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_facturas_cita` (`cita_id`),
  KEY `idx_facturas_cliente` (`cliente_id`),
  CONSTRAINT `fk_facturas_cita` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_facturas_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `factura_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `factura_id` int(11) NOT NULL,
  `servicio_id` int(11) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_item_factura` (`factura_id`),
  KEY `idx_item_servicio` (`servicio_id`),
  CONSTRAINT `fk_item_factura` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_item_servicio` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Ejemplo de inserción
INSERT INTO `facturas` (`cita_id`, `cliente_id`, `total`, `fecha`, `estado`) VALUES
(1, 1, 35.00, '2025-05-10', 'Pendiente');

INSERT INTO `factura_items` (`factura_id`, `servicio_id`, `descripcion`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 'Cambio de aceite', 1, 35.00, 35.00);
