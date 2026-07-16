-- Migración: tabla de gestión semestral independiente por facultad (Módulo 144)
-- Ejecutar una sola vez contra la base de datos existente (phpMyAdmin / consola MySQL).

CREATE TABLE IF NOT EXISTS `gestion_facultad_144` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `formulacion_id` int(11) NOT NULL,
  `facultad_id` int(11) NOT NULL,
  `sem1` varchar(255) DEFAULT NULL,
  `sem2` varchar(255) DEFAULT NULL,
  `vigencia` varchar(50) DEFAULT NULL,
  `seguimiento_sem1` varchar(255) DEFAULT NULL,
  `seguimiento_sem2` varchar(255) DEFAULT NULL,
  `descripcion_gestion` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `formulacion_facultad` (`formulacion_id`,`facultad_id`),
  KEY `facultad_id` (`facultad_id`),
  CONSTRAINT `gestion_facultad_144_ibfk_1` FOREIGN KEY (`formulacion_id`) REFERENCES `formulacion_144` (`id`) ON DELETE CASCADE,
  CONSTRAINT `gestion_facultad_144_ibfk_2` FOREIGN KEY (`facultad_id`) REFERENCES `facultades` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
