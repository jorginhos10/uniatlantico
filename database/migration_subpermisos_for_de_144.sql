-- Migración: arregla los checkboxes de sub-permisos de "Formato FOR-DE-144" que no guardaban.
--
-- Causa real: la tabla `subpermisos` no tenía columna `etiqueta` (el JS la necesita para
-- mostrar el nombre del checkbox) y, más importante, NO TENÍA FILAS para el permiso
-- "for_de_144" (id 2). Sin filas, el frontend caía en un modo de checkboxes de solo
-- apariencia (sin guardado real) que coincide visualmente con los reales, por eso
-- parecía que "no guardaba" pero en realidad nunca eran los checkboxes funcionales.
--
-- Ejecutar una sola vez contra la base de datos existente (phpMyAdmin / consola MySQL).

-- 1) Agregar columna etiqueta (si tu MySQL/MariaDB no soporta "IF NOT EXISTS" en ADD COLUMN,
--    quita esa parte; si la columna ya existe, simplemente saltate esta línea)
ALTER TABLE `subpermisos`
  ADD COLUMN `etiqueta` varchar(100) NOT NULL DEFAULT '' AFTER `nombre`;

-- 2) Evitar duplicados futuros (si ya tenés una restricción similar, saltate esta línea)
ALTER TABLE `subpermisos`
  ADD UNIQUE KEY `permiso_nombre` (`permiso_id`, `nombre`);

-- 3) Sembrar las 6 acciones de "Formato FOR-DE-144" (permiso_id = 2), sin duplicar si ya existen
INSERT INTO `subpermisos` (permiso_id, nombre, etiqueta, estado, orden)
SELECT 2, 'crear', 'Crear', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `subpermisos` WHERE permiso_id = 2 AND nombre = 'crear');

INSERT INTO `subpermisos` (permiso_id, nombre, etiqueta, estado, orden)
SELECT 2, 'editar', 'Editar', 1, 2
WHERE NOT EXISTS (SELECT 1 FROM `subpermisos` WHERE permiso_id = 2 AND nombre = 'editar');

INSERT INTO `subpermisos` (permiso_id, nombre, etiqueta, estado, orden)
SELECT 2, 'eliminar', 'Eliminar', 1, 3
WHERE NOT EXISTS (SELECT 1 FROM `subpermisos` WHERE permiso_id = 2 AND nombre = 'eliminar');

INSERT INTO `subpermisos` (permiso_id, nombre, etiqueta, estado, orden)
SELECT 2, 'ver', 'Ver', 1, 4
WHERE NOT EXISTS (SELECT 1 FROM `subpermisos` WHERE permiso_id = 2 AND nombre = 'ver');

INSERT INTO `subpermisos` (permiso_id, nombre, etiqueta, estado, orden)
SELECT 2, 'informe', 'Informe', 1, 5
WHERE NOT EXISTS (SELECT 1 FROM `subpermisos` WHERE permiso_id = 2 AND nombre = 'informe');

INSERT INTO `subpermisos` (permiso_id, nombre, etiqueta, estado, orden)
SELECT 2, 'configurar', 'Configurar', 1, 6
WHERE NOT EXISTS (SELECT 1 FROM `subpermisos` WHERE permiso_id = 2 AND nombre = 'configurar');

-- 4) Si ya existían filas con `nombre` pero sin `etiqueta`, completarla a partir del nombre
UPDATE `subpermisos`
SET `etiqueta` = CONCAT(UPPER(SUBSTRING(nombre,1,1)), SUBSTRING(nombre,2))
WHERE `etiqueta` = '' OR `etiqueta` IS NULL;
