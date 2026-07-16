-- Migración: campos de "Estado de proyectos" en el módulo de Seguimiento 144
-- Ejecutar una sola vez contra la base de datos existente (phpMyAdmin / consola MySQL).

ALTER TABLE `formulacion_144`
  ADD COLUMN `semestre1_seguimiento` decimal(10,2) DEFAULT NULL AFTER `responsable_seguimiento`,
  ADD COLUMN `semestre2_seguimiento` decimal(10,2) DEFAULT NULL AFTER `semestre1_seguimiento`,
  ADD COLUMN `logros` text DEFAULT NULL AFTER `semestre2_seguimiento`,
  ADD COLUMN `limites` text DEFAULT NULL AFTER `logros`,
  ADD COLUMN `observacion_estado` text DEFAULT NULL AFTER `limites`,
  ADD COLUMN `acciones_fortalecimiento` text DEFAULT NULL AFTER `observacion_estado`;
