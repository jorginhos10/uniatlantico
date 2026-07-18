-- Migración: semáforo de aprobación secuencial (Módulo 144)
-- 0=nadie, 1=Gestor de metas, 2=+Líder de metas, 3=+Responsable de línea, 4=+Sub administrador
-- Independiente por módulo (formulación / seguimiento).
-- Ejecutar una sola vez contra la base de datos existente (phpMyAdmin / consola MySQL).

ALTER TABLE `formulacion_144`
  ADD COLUMN `semaforo_etapa_formulacion` tinyint(1) NOT NULL DEFAULT 0 AFTER `solicitud_estado_seguimiento`,
  ADD COLUMN `semaforo_etapa_seguimiento` tinyint(1) NOT NULL DEFAULT 0 AFTER `semaforo_etapa_formulacion`;
