-- Migración: marca persistente de rechazo por etapa del semáforo (Módulo 144)
-- 0 = sin rechazo pendiente; 1-4 = la etapa que rechazó por última vez (se pinta en rojo
-- hasta que el creador corrige y vuelve a solicitar aprobación).
-- Ejecutar una sola vez contra la base de datos existente (phpMyAdmin / consola MySQL).

ALTER TABLE `formulacion_144`
  ADD COLUMN `semaforo_rechazo_etapa_formulacion` tinyint(1) NOT NULL DEFAULT 0 AFTER `semaforo_etapa_seguimiento`,
  ADD COLUMN `semaforo_rechazo_etapa_seguimiento` tinyint(1) NOT NULL DEFAULT 0 AFTER `semaforo_rechazo_etapa_formulacion`;
