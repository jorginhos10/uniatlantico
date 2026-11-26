-- ═════════════════════════════════════════════════════════════════════════════
-- Borra la PRIMERA carga de 2025formu.sql cuando el formulario "Plan de Acción 2025" quedó con los 207 indicadores dos veces (414).
-- Los ids de cada carga son consecutivos: la primera carga son los 207 ids más bajos del formulario y la segunda, los 207 más altos.
-- Se deja la segunda (la que toma línea, motor y proyecto del catálogo).
--
-- Cómo usarlo:
--   1. Haz un respaldo de la base de datos.
--   2. Ejecútalo tal cual: NO borra nada. Debe mostrar 414 indicadores, 207 a borrar y 207 a conservar.
--   3. Si los números son esos, cambia  SET @confirmar = 0;  por  SET @confirmar = 1;  y ejecútalo otra vez.
--
-- Si el formulario NO tiene exactamente 414 indicadores el script se niega a borrar: pásame los números que muestra.
-- ═════════════════════════════════════════════════════════════════════════════
SET NAMES utf8mb4;
SET @formulario = (SELECT id FROM formularios WHERE titulo LIKE 'Plan de Acci%n 2025%' ORDER BY id DESC LIMIT 1);
SET @confirmar = 0;
SET @por_carga = 207;

SET @total = (SELECT COUNT(*) FROM formulacion_144 WHERE formulario_id = @formulario);
SET @id_corte = (SELECT MAX(id) FROM (SELECT id FROM formulacion_144 WHERE formulario_id = @formulario ORDER BY id LIMIT 207) t); -- último id de la primera carga

SELECT @formulario AS formulario_id, @total AS indicadores_en_el_formulario,
       (SELECT COUNT(*) FROM formulacion_144 WHERE formulario_id = @formulario AND id <= @id_corte) AS a_borrar_primera_carga,
       (SELECT COUNT(*) FROM formulacion_144 WHERE formulario_id = @formulario AND id >  @id_corte) AS a_conservar_segunda_carga,
       @id_corte AS ultimo_id_de_la_primera_carga;

-- Muestra de lo que se borra y de lo que se conserva (los primeros de cada carga)
(SELECT 'SE BORRA' AS que, id, nombre_indicador, linea_estrategica, proyecto FROM formulacion_144 WHERE formulario_id = @formulario AND id <= @id_corte ORDER BY id LIMIT 5)
UNION ALL
(SELECT 'SE CONSERVA', id, nombre_indicador, linea_estrategica, proyecto FROM formulacion_144 WHERE formulario_id = @formulario AND id > @id_corte ORDER BY id LIMIT 5);

-- Borrado: solo si @confirmar = 1 y hay exactamente 414 indicadores
DELETE FROM formulacion_distribucion_facultad_144 WHERE @confirmar = 1 AND @total = 414 AND formulacion_id IN (SELECT id FROM formulacion_144 WHERE formulario_id = @formulario AND id <= @id_corte);
DELETE FROM formulacion_144 WHERE @confirmar = 1 AND @total = 414 AND formulario_id = @formulario AND id <= @id_corte;

SELECT IF(@confirmar = 1 AND @total = 414, 'Borrado hecho', 'No se borró nada') AS resultado,
       (SELECT COUNT(*) FROM formulacion_144 WHERE formulario_id = @formulario) AS indicadores_ahora;
