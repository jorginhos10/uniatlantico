-- Migración: estado de solicitud de aprobación, independiente por módulo (Formulación / Seguimiento)
-- 0 = Construcción (nunca solicitado), 1 = Solicitado, 2 = Rechazado
-- Ejecutar una sola vez contra la base de datos existente (phpMyAdmin / consola MySQL).

-- Si ya habías ejecutado una versión anterior de esta migración que agregó una sola
-- columna `solicitud_estado`, corré primero esta línea antes del ALTER de abajo:
-- ALTER TABLE `formulacion_144` DROP COLUMN `solicitud_estado`;

ALTER TABLE `formulacion_144`
  ADD COLUMN `solicitud_estado_formulacion` tinyint(1) NOT NULL DEFAULT 0 AFTER `estado_seguimiento`,
  ADD COLUMN `solicitud_estado_seguimiento` tinyint(1) NOT NULL DEFAULT 0 AFTER `solicitud_estado_formulacion`;
