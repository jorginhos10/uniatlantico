-- Dependencias de las que un usuario es responsable (además de su dependencia principal, usuarios.cargo_id).
-- Se asignan en Usuarios > Crear/Editar > "Responsable de (dependencias)".
-- El usuario ve y gestiona en el Plan de Acción los indicadores cuyo responsable sea cualquiera de ellas.
-- Se puede ejecutar varias veces.

CREATE TABLE IF NOT EXISTS `usuario_dependencias` (
  `usuario_id` int(11) NOT NULL,
  `cargo_id` int(11) NOT NULL,
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`usuario_id`, `cargo_id`),
  KEY `cargo_id` (`cargo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
