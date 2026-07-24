-- Migración: visibilidad por rango de fechas y apertura automática al iniciar sesión (Novedades)
-- Ejecutar una sola vez contra la base de datos existente (phpMyAdmin / consola MySQL).

ALTER TABLE `novedades`
  ADD COLUMN `visible_desde` DATE NULL DEFAULT NULL AFTER `activo`,
  ADD COLUMN `visible_hasta` DATE NULL DEFAULT NULL AFTER `visible_desde`,
  ADD COLUMN `auto_abrir` TINYINT(1) NOT NULL DEFAULT 0 AFTER `visible_hasta`;
