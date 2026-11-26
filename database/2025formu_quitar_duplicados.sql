-- ═════════════════════════════════════════════════════════════════════════════
-- DEFINITIVO: deja los 207 indicadores de 2025formu.sql y borra la carga repetida. Sin banderas ni confirmaciones:
-- al ejecutarlo borra de verdad. Haz un respaldo antes.
--
-- Ejecuta TODO el archivo de una vez (en phpMyAdmin: pegarlo completo y pulsar Continuar), no línea por línea,
-- porque usa variables (@algo) que solo viven dentro de la misma conexión.
--
-- Cómo decide: busca el formulario que tiene indicadores del año 2025 cargados por el usuario 1 (el de la carga), toma los 207 indicadores
-- con id MÁS ALTO (la segunda carga, la que sí enlaza con el catálogo) y borra los demás de esa misma carga.
-- ═════════════════════════════════════════════════════════════════════════════
SET NAMES utf8mb4;

-- Formulario donde está la carga (el que más indicadores de 2025 del usuario 1 tiene)
SET @f = (SELECT formulario_id FROM formulacion_144 WHERE anio = 2025 AND creado_por = 1
          GROUP BY formulario_id ORDER BY COUNT(*) DESC LIMIT 1);

-- Primer id que SE CONSERVA: el menor de los 207 ids más altos
SET @conservar_desde = (SELECT MIN(id) FROM (SELECT id FROM formulacion_144 WHERE formulario_id = @f AND anio = 2025 AND creado_por = 1
                                             ORDER BY id DESC LIMIT 207) t);

SELECT @f AS formulario_id,
       (SELECT titulo FROM formularios WHERE id = @f) AS titulo,
       (SELECT COUNT(*) FROM formulacion_144 WHERE formulario_id = @f AND anio = 2025 AND creado_por = 1) AS indicadores_antes,
       (SELECT COUNT(*) FROM formulacion_144 WHERE formulario_id = @f AND anio = 2025 AND creado_por = 1 AND id < @conservar_desde) AS se_borran,
       @conservar_desde AS se_conservan_desde_el_id;

-- Borrado (distribución por facultades primero)
DELETE FROM formulacion_distribucion_facultad_144
WHERE formulacion_id IN (SELECT id FROM (SELECT id FROM formulacion_144
                                         WHERE formulario_id = @f AND anio = 2025 AND creado_por = 1 AND id < @conservar_desde) x);

DELETE FROM formulacion_144
WHERE formulario_id = @f AND anio = 2025 AND creado_por = 1 AND id < @conservar_desde;

-- Resultado: debe decir 207
SELECT COUNT(*) AS indicadores_ahora FROM formulacion_144 WHERE formulario_id = @f AND anio = 2025 AND creado_por = 1;

-- Los pesos por proyecto ya no deben pasar de 100 (no debe devolver filas):
SELECT proyecto, ROUND(SUM(ponderacion_actividades), 2) AS suma_pesos
FROM formulacion_144 WHERE formulario_id = @f AND estado_formulacion <> 1
GROUP BY proyecto HAVING SUM(ponderacion_actividades) > 100.005;
