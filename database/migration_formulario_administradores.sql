-- Migración: administradores por formulario (pestaña "Avanzado" en Editar Formulario, FOR-DE-144)
-- Ejecutar una sola vez contra la base de datos existente (phpMyAdmin / consola MySQL).

CREATE TABLE IF NOT EXISTS `formulario_administradores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `formulario_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `formulario_usuario` (`formulario_id`,`usuario_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `formulario_administradores_ibfk_1` FOREIGN KEY (`formulario_id`) REFERENCES `formularios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `formulario_administradores_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
