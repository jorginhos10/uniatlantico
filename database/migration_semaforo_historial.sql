-- Migración: historial de aprobaciones/rechazos del semáforo (Módulo 144)
-- Guarda cada acción (aprobado/rechazado) con quién la realizó, en qué etapa y cuándo,
-- para poder mostrar el listado de "quién aprobó" / "quién rechazó" al hacer clic en el badge de Estado.
-- Ejecutar una sola vez contra la base de datos existente (phpMyAdmin / consola MySQL).

CREATE TABLE IF NOT EXISTS `semaforo_historial_144` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modulo` varchar(20) NOT NULL,
  `formulacion_id` int(11) NOT NULL,
  `etapa` tinyint(1) NOT NULL,
  `accion` enum('aprobado','rechazado') NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `usuario_nombre` varchar(150) NOT NULL,
  `rol` varchar(100) DEFAULT NULL,
  `motivo` text DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_modulo_formulacion` (`modulo`, `formulacion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
