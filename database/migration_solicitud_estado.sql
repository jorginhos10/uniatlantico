-- Migración: estado de solicitud de aprobación por borrador (Módulo 144)
-- 0 = Construcción (nunca solicitado), 1 = Solicitado, 2 = Rechazado
-- Ejecutar una sola vez contra la base de datos existente (phpMyAdmin / consola MySQL).

ALTER TABLE `formulacion_144`
  ADD COLUMN `solicitud_estado` tinyint(1) NOT NULL DEFAULT 0 AFTER `estado_seguimiento`;
