-- Seguimiento por facultad (semestre 1 y semestre 2) sobre la "Distribución por facultades"
-- de cada indicador. Se registra desde el modal de Seguimiento del Módulo 144.
-- Compatible con MySQL (no usa "ADD COLUMN IF NOT EXISTS", que solo existe en MariaDB):
-- cada columna se agrega únicamente si todavía no existe, así que se puede ejecutar varias veces.

SET @col := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'formulacion_distribucion_facultad_144'
               AND COLUMN_NAME = 'seguimiento_sem1');
SET @sql := IF(@col = 0,
    'ALTER TABLE `formulacion_distribucion_facultad_144` ADD COLUMN `seguimiento_sem1` decimal(12,2) DEFAULT NULL AFTER `distribucion`',
    'SELECT ''seguimiento_sem1 ya existe''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'formulacion_distribucion_facultad_144'
               AND COLUMN_NAME = 'seguimiento_sem2');
SET @sql := IF(@col = 0,
    'ALTER TABLE `formulacion_distribucion_facultad_144` ADD COLUMN `seguimiento_sem2` decimal(12,2) DEFAULT NULL AFTER `seguimiento_sem1`',
    'SELECT ''seguimiento_sem2 ya existe''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Descripción del seguimiento de cada facultad (al lado de los semestres)
SET @col := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'formulacion_distribucion_facultad_144'
               AND COLUMN_NAME = 'seguimiento_descripcion');
SET @sql := IF(@col = 0,
    'ALTER TABLE `formulacion_distribucion_facultad_144` ADD COLUMN `seguimiento_descripcion` text DEFAULT NULL AFTER `seguimiento_sem2`',
    'SELECT ''seguimiento_descripcion ya existe''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
