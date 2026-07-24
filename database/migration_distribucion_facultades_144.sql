-- Migración: distribución de la meta anual entre facultades (Módulo 144, pestaña FORMULACIÓN)
-- Se usa cuando el indicador está marcado como "gestionado desde las facultades": permite
-- repartir el total (14.4 VALOR AÑO) entre facultades, con una observación por facultad.
-- Ejecutar una sola vez contra la base de datos existente (phpMyAdmin / consola MySQL).

CREATE TABLE IF NOT EXISTS `formulacion_distribucion_facultad_144` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `formulacion_id` int(11) NOT NULL,
  `facultad_id` int(11) NOT NULL,
  `observacion` text DEFAULT NULL,
  `distribucion` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `formulacion_facultad_dist` (`formulacion_id`, `facultad_id`),
  KEY `facultad_id` (`facultad_id`),
  CONSTRAINT `formulacion_dist_fac_ibfk_1` FOREIGN KEY (`formulacion_id`) REFERENCES `formulacion_144` (`id`) ON DELETE CASCADE,
  CONSTRAINT `formulacion_dist_fac_ibfk_2` FOREIGN KEY (`facultad_id`) REFERENCES `facultades` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
