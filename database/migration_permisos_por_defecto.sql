-- Activa Dashboard y FOR-DE-144 a TODOS los usuarios existentes.
-- Se puede ejecutar varias veces: no duplica permisos ya asignados.

INSERT IGNORE INTO `detalle_permiso` (`id_usuario`, `id_permiso`, `fecha_asignacion`)
SELECT u.`id`, lp.`id`, NOW()
FROM `usuarios` u
JOIN `lista_permisos` lp
  ON lp.`estado` = 1
 AND lp.`nombre` IN ('ver_dashboard', 'for_de_144', 'gestionar_recetas');

-- Sub-permiso "ver" de esos permisos (igual que al habilitarlos desde la app)
INSERT IGNORE INTO `detalle_subpermiso` (`usuario_id`, `subpermiso_id`, `fecha_asignacion`)
SELECT dp.`id_usuario`, s.`id`, NOW()
FROM `detalle_permiso` dp
JOIN `lista_permisos` lp ON lp.`id` = dp.`id_permiso`
 AND lp.`nombre` IN ('ver_dashboard', 'for_de_144', 'gestionar_recetas')
JOIN `subpermisos` s ON s.`permiso_id` = dp.`id_permiso` AND s.`nombre` = 'ver' AND s.`estado` = 1;
