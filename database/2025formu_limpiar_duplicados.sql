-- ═════════════════════════════════════════════════════════════════════════════
-- Limpieza de indicadores DUPLICADOS en "Plan de Acción 2025" (por haber corrido 2025formu.sql más de una vez)
--
-- Síntomas que arregla:
--   · indicadores con la etiqueta L-M-P vacía (los de la primera carga, que usó los nombres del Excel y no los del catálogo)
--   · «La ponderación supera el 100 %» al duplicar o editar (cada indicador estaba dos veces y los pesos de las actividades se sumaban dos veces)
--
-- Qué hace: busca los indicadores repetidos del formulario (mismo nombre, fórmula, actividad, ID y responsable) y deja UNO de cada pareja:
--   1) el que SÍ está bien enlazado al catálogo (línea + motor + proyecto);
--   2) si los dos están igual de enlazados, el más antiguo.
--
-- Cómo usarlo:
--   1. Haz un respaldo de la base de datos.
--   2. Ejecuta el script tal cual: NO borra nada. Revisa los dos SELECT del final (cuántos hay y cuáles se borrarían).
--   3. Cuando estés conforme, cambia  SET @confirmar = 0;  por  SET @confirmar = 1;  y ejecútalo de nuevo.
--
-- Solo toca indicadores del formulario "Plan de Acción 2025" que estén repetidos. Si los editaste a mano después de cargarlos,
-- revisa el SELECT antes de confirmar (mira la columna "se_borra").
-- ═════════════════════════════════════════════════════════════════════════════
SET NAMES utf8mb4;
SET @formulario = (SELECT id FROM formularios WHERE titulo LIKE 'Plan de Acci%n 2025%' ORDER BY id DESC LIMIT 1);
SET @confirmar = 0;

SELECT @formulario AS formulario_id,
       (SELECT COUNT(*) FROM formulacion_144 WHERE formulario_id = @formulario) AS indicadores_en_el_formulario;

DROP TABLE IF EXISTS tmp_res_2025;
DROP TABLE IF EXISTS tmp_res2_2025;
DROP TABLE IF EXISTS tmp_borrar_2025;

-- Cada indicador con su «clave» y si está enlazado al catálogo (línea + motor + proyecto por nombre, como lo hace el sistema)
CREATE TABLE tmp_res_2025 AS
SELECT f.id,
       MD5(CONCAT_WS('|', COALESCE(f.nombre_indicador, ''), COALESCE(f.formula_medicion, ''), COALESCE(f.actividad_proyecto, ''),
                          COALESCE(f.id_indicador, ''), COALESCE(f.responsable_formulacion, ''), COALESCE(f.proyecto, ''))) AS clave,
       EXISTS (SELECT 1
               FROM lineas_estrategicas le
               JOIN motores m   ON m.linea_id = le.id AND m.activo = 1
               JOIN proyectos p ON p.motor_id = m.id AND p.activo = 1
               WHERE le.activo = 1 AND le.nombre = f.linea_estrategica AND m.nombre = f.motor_desarrollo AND p.nombre = f.proyecto) AS enlazado
FROM formulacion_144 f
WHERE f.formulario_id = @formulario;

-- (se copia para poder compararla consigo misma: MySQL no deja usar dos veces la misma tabla en una consulta si es temporal)
CREATE TABLE tmp_res2_2025 AS SELECT * FROM tmp_res_2025;

-- Los que sobran: tienen un «gemelo» y pierden contra él
CREATE TABLE tmp_borrar_2025 AS
SELECT a.id
FROM tmp_res_2025 a
WHERE EXISTS (
    SELECT 1 FROM tmp_res2_2025 b
    WHERE b.clave = a.clave AND b.id <> a.id
      AND (b.enlazado > a.enlazado OR (b.enlazado = a.enlazado AND b.id < a.id))
);

-- 1) Resumen
SELECT (SELECT COUNT(*) FROM tmp_res_2025)                              AS indicadores,
       (SELECT COUNT(DISTINCT clave) FROM tmp_res_2025)                 AS indicadores_distintos,
       (SELECT COUNT(*) FROM tmp_borrar_2025)                           AS sobran_y_se_borrarian,
       (SELECT COUNT(*) FROM tmp_res_2025 WHERE enlazado = 0)           AS sin_enlace_al_catalogo;

-- 2) Detalle de lo que se borraría (revísalo antes de confirmar)
SELECT f.id, f.creado_por, f.fecha_creacion, f.nombre_indicador, f.linea_estrategica, f.motor_desarrollo, f.proyecto, f.ponderacion_actividades,
       'SI' AS se_borra
FROM formulacion_144 f
JOIN tmp_borrar_2025 x ON x.id = f.id
ORDER BY f.nombre_indicador, f.id;

-- 3) Indicadores que, tras la limpieza, seguirán SIN enlace al catálogo: ese código de línea-motor-proyecto no existe en Configuraciones
--    y hay que crearlo ahí (o corregir el nombre) para que se vea la etiqueta L-M-P.
SELECT f.id, f.nombre_indicador, f.linea_estrategica, f.motor_desarrollo, f.proyecto
FROM formulacion_144 f
JOIN tmp_res_2025 r ON r.id = f.id AND r.enlazado = 0
WHERE f.id NOT IN (SELECT id FROM tmp_borrar_2025)
ORDER BY f.linea_estrategica, f.motor_desarrollo, f.proyecto, f.id;

-- ── Borrado (solo si @confirmar = 1) ─────────────────────────────────────────
DELETE FROM formulacion_distribucion_facultad_144 WHERE @confirmar = 1 AND formulacion_id IN (SELECT id FROM tmp_borrar_2025);
DELETE FROM formulacion_144                        WHERE @confirmar = 1 AND id IN (SELECT id FROM tmp_borrar_2025);

SELECT IF(@confirmar = 1, 'Borrado hecho', 'No se borró nada (pon @confirmar = 1 para borrar)') AS resultado,
       (SELECT COUNT(*) FROM formulacion_144 WHERE formulario_id = @formulario) AS indicadores_ahora;

-- Los pesos por proyecto después de limpiar no deben pasar de 100 (no debe devolver filas):
SELECT proyecto, ROUND(SUM(ponderacion_actividades), 2) AS suma_pesos
FROM formulacion_144 WHERE formulario_id = @formulario AND estado_formulacion <> 1
GROUP BY proyecto HAVING SUM(ponderacion_actividades) > 100.005;

DROP TABLE IF EXISTS tmp_res_2025;
DROP TABLE IF EXISTS tmp_res2_2025;
DROP TABLE IF EXISTS tmp_borrar_2025;
