-- Flujo de información entre dependencias (Dependencias > Flujo de información):
-- la dependencia origen envía su formulación y/o seguimiento a la dependencia destino, que la aprueba.
-- Se puede ejecutar varias veces.

CREATE TABLE IF NOT EXISTS `flujo_dependencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `origen_id` int(11) NOT NULL,
  `destino_id` int(11) NOT NULL,
  `aplica_a` enum('ambos','formulacion','seguimiento') NOT NULL DEFAULT 'ambos',
  `observacion` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `origen_destino_aplica` (`origen_id`, `destino_id`, `aplica_a`),
  KEY `destino_id` (`destino_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Sin llaves foráneas a `cargos` a propósito: al eliminar una dependencia, el modelo borra sus relaciones.
