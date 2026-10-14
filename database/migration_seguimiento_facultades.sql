-- Seguimiento por facultad (semestre 1 y semestre 2) sobre la "Distribución por facultades"
-- de cada indicador. Se registra desde el modal de Seguimiento del Módulo 144.
-- Ejecutar una sola vez. Si tu MySQL no soporta "IF NOT EXISTS" en ADD COLUMN, quítalo
-- (y si la columna ya existe, sáltate esa línea).

ALTER TABLE `formulacion_distribucion_facultad_144`
  ADD COLUMN IF NOT EXISTS `seguimiento_sem1` decimal(12,2) DEFAULT NULL AFTER `distribucion`,
  ADD COLUMN IF NOT EXISTS `seguimiento_sem2` decimal(12,2) DEFAULT NULL AFTER `seguimiento_sem1`;
