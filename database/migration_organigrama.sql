-- Organigrama administrable (mismo esquema de Jerarquías de new-ua): cada nodo depende de otro
-- (padre_id) y puede tener varios roles asociados. La columna `rol` guarda la lista de roles
-- en JSON (ej. ["Lider de meta","Gestor de metas"]); un texto simple se toma como un solo rol.
-- Se administra desde Organigrama > Estructural (solo administradores).
-- Se puede ejecutar varias veces: no duplica los nodos iniciales.

CREATE TABLE IF NOT EXISTS `organigrama_nodos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `padre_id` int(11) DEFAULT NULL,
  `rol` text DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `padre_id` (`padre_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Por si la tabla se creó con la primera versión de esta migración (rol varchar(100))
ALTER TABLE `organigrama_nodos` MODIFY `rol` text DEFAULT NULL;

-- Estructura inicial: la misma que estaba fija en el código
INSERT IGNORE INTO `organigrama_nodos` (`id`, `nombre`, `padre_id`, `rol`, `orden`, `activo`) VALUES
(1, 'Administrador', NULL, 'Administrador', 1, 1),
(2, 'Sub Administrador', 1, 'Sub administrador', 1, 1),
(3, 'Gestor de Metas de Sub-Administrador', 2, 'Gestor de metas de sub-admin', 1, 1),
(4, 'Responsable de Línea', 2, 'Responsable de línea', 2, 1),
(5, 'Gestor de Metas de Responsable de Línea', 4, 'Gestor de metas de responsable de línea', 1, 1),
(6, 'Líder de Metas', 4, 'Lider de meta', 2, 1),
(7, 'Gestor de Metas de Dependencia', 6, 'Gestor de metas', 1, 1),
(8, 'Gestor de Metas de Proyectos', 6, 'Gestor de facultad', 2, 1);
