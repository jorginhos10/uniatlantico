-- Snapshots (Configuraciones > Snapshot): copia completa del estado de la base de datos en un
-- momento dado, tabla por tabla en JSON (mismo mecanismo que new-ua).
-- Se puede ejecutar varias veces.

CREATE TABLE IF NOT EXISTS `snapshots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `snapshots_datos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `snapshot_id` int(11) NOT NULL,
  `tabla` varchar(100) NOT NULL,
  `filas` int(11) NOT NULL DEFAULT 0,
  `datos` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `snapshot_id` (`snapshot_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
