-- ============================================================
-- ESQUEMA RECONSTRUIDO DE LA BASE DE DATOS "uniatlantico"
-- Generado a partir del codigo PHP (modelo/*.php), no de un
-- dump real del hosting. Revisar tipos/longitudes antes de
-- ejecutar en produccion.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── USUARIOS Y PERMISOS ──────────────────────────────────────

CREATE TABLE IF NOT EXISTS `cargos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `rol` enum('admin','director','coordinador','jefe','analista','secretario','auxiliar','tecnico','asesor','pasante','cocina','inventario') NOT NULL DEFAULT 'auxiliar',
  `avatar` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `cargo_id` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `cargo_id` (`cargo_id`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `lista_permisos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `detalle_permiso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_permiso` int(11) NOT NULL,
  `fecha_asignacion` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_permiso` (`id_usuario`,`id_permiso`),
  KEY `id_permiso` (`id_permiso`),
  CONSTRAINT `detalle_permiso_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_permiso_ibfk_2` FOREIGN KEY (`id_permiso`) REFERENCES `lista_permisos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `subpermisos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permiso_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `orden` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `permiso_id` (`permiso_id`),
  CONSTRAINT `subpermisos_ibfk_1` FOREIGN KEY (`permiso_id`) REFERENCES `lista_permisos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `detalle_subpermiso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `subpermiso_id` int(11) NOT NULL,
  `fecha_asignacion` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_subpermiso` (`usuario_id`,`subpermiso_id`),
  KEY `subpermiso_id` (`subpermiso_id`),
  CONSTRAINT `detalle_subpermiso_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_subpermiso_ibfk_2` FOREIGN KEY (`subpermiso_id`) REFERENCES `subpermisos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── MENSAJERIA Y NOVEDADES ───────────────────────────────────

CREATE TABLE IF NOT EXISTS `mensajes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asunto` varchar(255) NOT NULL,
  `cuerpo` text NOT NULL,
  `remitente_id` int(11) NOT NULL,
  `tipo_destinatario` enum('usuario','rol','dependencia') NOT NULL,
  `destinatario_id` varchar(50) NOT NULL,
  `fecha_envio` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `remitente_id` (`remitente_id`),
  CONSTRAINT `mensajes_ibfk_1` FOREIGN KEY (`remitente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mensajes_leidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mensaje_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_lectura` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mensaje_usuario` (`mensaje_id`,`usuario_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `mensajes_leidos_ibfk_1` FOREIGN KEY (`mensaje_id`) REFERENCES `mensajes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mensajes_leidos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `novedades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `contenido` text NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `orden` int(11) NOT NULL DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── PROVEEDORES ───────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `proveedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `empresa` varchar(150) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  `nit_rut` varchar(50) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── CATALOGOS PLAN ESTRATEGICO (FOR-DE-144) ─────────────────

CREATE TABLE IF NOT EXISTS `lineas_estrategicas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `objetivo` text NOT NULL,
  `activo` tinyint(4) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `estrategias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `linea_id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `activo` tinyint(4) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `linea_id` (`linea_id`),
  CONSTRAINT `estrategias_ibfk_1` FOREIGN KEY (`linea_id`) REFERENCES `lineas_estrategicas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `motores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `linea_id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `ponderacion` decimal(5,2) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `linea_id` (`linea_id`),
  CONSTRAINT `motores_ibfk_1` FOREIGN KEY (`linea_id`) REFERENCES `lineas_estrategicas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `proyectos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `linea_id` int(11) NOT NULL,
  `motor_id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `linea_id` (`linea_id`),
  KEY `motor_id` (`motor_id`),
  CONSTRAINT `proyectos_ibfk_1` FOREIGN KEY (`linea_id`) REFERENCES `lineas_estrategicas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `proyectos_ibfk_2` FOREIGN KEY (`motor_id`) REFERENCES `motores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `planes_institucionales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `facultades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) DEFAULT NULL,
  `nombre` varchar(255) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Nombre con guiones: requiere comillas invertidas en todas las queries
CREATE TABLE IF NOT EXISTS `ano-for-de-144` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anio` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `orden` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `anio` (`anio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── DATOS DE PONDERACION POR ANIO (graficos de cumplimiento) ──

CREATE TABLE IF NOT EXISTS `data_linea_estrategica` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `linea_id` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `porcentaje` decimal(5,2) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `linea_anio` (`linea_id`,`anio`),
  CONSTRAINT `data_linea_estrategica_ibfk_1` FOREIGN KEY (`linea_id`) REFERENCES `lineas_estrategicas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `data_motores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `motor_id` int(11) NOT NULL,
  `linea_id` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `porcentaje` decimal(5,2) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `motor_anio` (`motor_id`,`anio`),
  KEY `linea_id` (`linea_id`),
  CONSTRAINT `data_motores_ibfk_1` FOREIGN KEY (`motor_id`) REFERENCES `motores` (`id`) ON DELETE CASCADE,
  CONSTRAINT `data_motores_ibfk_2` FOREIGN KEY (`linea_id`) REFERENCES `lineas_estrategicas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `data_proyectos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proyecto_id` int(11) NOT NULL,
  `motor_id` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `porcentaje` decimal(5,2) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `proyecto_anio` (`proyecto_id`,`anio`),
  KEY `motor_id` (`motor_id`),
  CONSTRAINT `data_proyectos_ibfk_1` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `data_proyectos_ibfk_2` FOREIGN KEY (`motor_id`) REFERENCES `motores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── FORMULARIOS Y FORMULACION/SEGUIMIENTO 144 ────────────────

CREATE TABLE IF NOT EXISTS `formularios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo_tiempo` enum('libre','rango') NOT NULL DEFAULT 'libre',
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `anio` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Administradores adicionales por formulario (pestaña "Avanzado" en Editar Formulario)
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

-- Tabla principal: une formulacion + seguimiento (version vigente, usada por Modulo144Model/FORDE144Model)
CREATE TABLE IF NOT EXISTS `formulacion_144` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `formulario_id` int(11) NOT NULL,
  `nombre_borrador` varchar(255) DEFAULT NULL,
  `facultad_id` int(11) DEFAULT NULL,
  `anio` int(11) DEFAULT NULL,
  -- Formulacion
  `linea_estrategica` varchar(255) DEFAULT NULL,
  `objetivo` text DEFAULT NULL,
  `estrategia` text DEFAULT NULL,
  `motor_desarrollo` varchar(255) DEFAULT NULL,
  `proyecto` varchar(255) DEFAULT NULL,
  `meta_resultado` text DEFAULT NULL,
  `ponderacion_proyectos` decimal(5,2) DEFAULT NULL,
  `actividad_proyecto` text DEFAULT NULL,
  `ponderacion_actividades` decimal(5,2) DEFAULT NULL,
  `responsable_formulacion` varchar(255) DEFAULT NULL,
  `id_indicador` varchar(100) DEFAULT NULL,
  `gestionado_facultades` varchar(255) DEFAULT NULL,
  `nombre_indicador` varchar(255) DEFAULT NULL,
  `formula_medicion` text DEFAULT NULL,
  `frecuencia_medicion` varchar(100) DEFAULT NULL,
  `unidad_medida` varchar(100) DEFAULT NULL,
  `tipo_medicion` varchar(100) DEFAULT NULL,
  `descripcion_indicador` text DEFAULT NULL,
  `planes_institucionales` varchar(255) DEFAULT NULL,
  `linea_base_meta` varchar(100) DEFAULT NULL,
  `anio_base_meta` int(11) DEFAULT NULL,
  `meta_s1` varchar(100) DEFAULT NULL,
  `meta_s2` varchar(100) DEFAULT NULL,
  -- Seguimiento
  `indicador` text DEFAULT NULL,
  `meta_programada` varchar(100) DEFAULT NULL,
  `meta_ejecutada` varchar(100) DEFAULT NULL,
  `porcentaje_avance` decimal(5,2) DEFAULT NULL,
  `fecha_seguimiento` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `responsable_seguimiento` varchar(255) DEFAULT NULL,
  `semestre1_seguimiento` decimal(10,2) DEFAULT NULL,
  `semestre2_seguimiento` decimal(10,2) DEFAULT NULL,
  -- Estado de proyectos (seguimiento)
  `logros` text DEFAULT NULL,
  `limites` text DEFAULT NULL,
  `observacion_estado` text DEFAULT NULL,
  `acciones_fortalecimiento` text DEFAULT NULL,
  -- Gestion semestral
  `gestion_sem1` text DEFAULT NULL,
  `gestion_sem2` text DEFAULT NULL,
  `vigencia` varchar(50) DEFAULT NULL,
  `descripcion_gestion` text DEFAULT NULL,
  `tabla_fila1_sem1` varchar(255) DEFAULT NULL,
  `tabla_fila1_sem2` varchar(255) DEFAULT NULL,
  `tabla_fila2_sem1` varchar(255) DEFAULT NULL,
  `tabla_fila2_sem2` varchar(255) DEFAULT NULL,
  `tabla_fila3_sem1` varchar(255) DEFAULT NULL,
  `tabla_fila3_sem2` varchar(255) DEFAULT NULL,
  -- Estado / auditoria
  `estado_formulacion` tinyint(1) NOT NULL DEFAULT 0,
  `estado_seguimiento` tinyint(1) NOT NULL DEFAULT 0,
  `solicitud_estado` tinyint(1) NOT NULL DEFAULT 0, -- 0=Construcción, 1=Solicitado, 2=Rechazado (solo aplica mientras sigue en Borradores)
  `fecha_publicacion_formulacion` datetime DEFAULT NULL,
  `fecha_publicacion_seguimiento` datetime DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `formulario_id` (`formulario_id`),
  KEY `facultad_id` (`facultad_id`),
  KEY `creado_por` (`creado_por`),
  CONSTRAINT `formulacion_144_ibfk_1` FOREIGN KEY (`formulario_id`) REFERENCES `formularios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `formulacion_144_ibfk_2` FOREIGN KEY (`facultad_id`) REFERENCES `facultades` (`id`) ON DELETE SET NULL,
  CONSTRAINT `formulacion_144_ibfk_3` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Gestión semestral por facultad: cada facultad tiene su propia fila de seguimiento
-- para un mismo indicador "gestionado desde facultades" en formulacion_144.
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

-- Tabla legacy: solo referenciada por el modelo/controlador antiguo Formulacion144Model/Controller.
-- Probablemente codigo heredado de una version previa al merge en formulacion_144; verificar si sigue en uso.
CREATE TABLE IF NOT EXISTS `seguimiento_144` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `formulario_id` int(11) NOT NULL,
  `formulacion_id` int(11) NOT NULL,
  `nombre_seguimiento` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 0,
  `avance_fisico` decimal(5,2) DEFAULT NULL,
  `avance_financiero` decimal(5,2) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `formulario_id` (`formulario_id`),
  KEY `formulacion_id` (`formulacion_id`),
  CONSTRAINT `seguimiento_144_ibfk_1` FOREIGN KEY (`formulario_id`) REFERENCES `formularios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seguimiento_144_ibfk_2` FOREIGN KEY (`formulacion_id`) REFERENCES `formulacion_144` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user_filter_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `formulario_id` int(11) NOT NULL,
  `modulo` varchar(50) NOT NULL,
  `tipo_filtro` varchar(50) NOT NULL DEFAULT 'todos',
  `valor_filtro` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_formulario_modulo` (`usuario_id`,`formulario_id`,`modulo`),
  CONSTRAINT `user_filter_preferences_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_filter_preferences_ibfk_2` FOREIGN KEY (`formulario_id`) REFERENCES `formularios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
