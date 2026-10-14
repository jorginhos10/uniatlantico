-- Catálogo "ID Indicador": opciones del select "11. ID INDICADOR" del FOR-DE-144.
-- Se administra desde Configuraciones > Líneas, Motores y Proyectos > ID Indicador.
-- Se puede ejecutar varias veces: no duplica opciones.

CREATE TABLE IF NOT EXISTS `id_indicadores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `id_indicadores` (`nombre`, `activo`) VALUES
('PR', 1),
('PDI', 1),
('PA', 1),
('PR-PDI', 1),
('PR-PA', 1),
('PDI-PA', 1);
