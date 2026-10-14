-- ============================================================
-- Carga masiva de usuarios (gestores, lideres y responsables)
-- Contraseña temporal para todos: Uniatlantico2026*
-- Permisos por defecto: Chat, Dashboard y FOR-DE-144
-- Se puede ejecutar varias veces: no duplica cargos, usuarios ni permisos.
-- ============================================================

SET NAMES utf8mb4;

DROP TEMPORARY TABLE IF EXISTS `tmp_usuarios_metas`;
CREATE TEMPORARY TABLE `tmp_usuarios_metas` (
  `email` varchar(150) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `dependencia` varchar(150) NOT NULL,
  `rol` varchar(100) NOT NULL
);

INSERT INTO `tmp_usuarios_metas` (`email`, `nombre`, `dependencia`, `rol`) VALUES
('leonilaatencio@mail.uniatlantico.edu.co', 'Leonila Atencio', 'Consultorio Jurídico y Centro de Conciliación', 'Gestor de metas'),
('consultoriojuridico@mail.uniatlantico.edu.co', 'Consultorio Juridico', 'Consultorio Jurídico y Centro de Conciliación', 'Lider de meta'),
('dianavillamizar@mail.uniatlantico.edu.co', 'Diana Villamizar', 'Dpto. Calidad Académica', 'Gestor de metas'),
('calidaddocencia@mail.uniatlantico.edu.co', 'Fidel Llinas Zurita', 'Dpto. Calidad Académica', 'Lider de meta'),
('jeclavijo@mail.uniatlantico.edu.co', 'Javier Clavijo', 'Dpto. de Admisiones y Registro', 'Gestor de metas'),
('comunicacionesadmisiones@mail.uniatlantico.edu.co', 'Departamento de Admisiones', 'Dpto. de Admisiones y Registro', 'Lider de meta'),
('biblioteca@mail.uniatlantico.edu.co', 'Julian Cardona Toro', 'Departamento de Biblioteca', 'Lider de meta'),
('dignoraperez@mail.uniatlantico.edu.co', 'Dignora Pérez Benitez', 'Departamento de Biblioteca', 'Gestor de metas'),
('rociovarela@mail.uniatlantico.edu.co', 'Rocío Varela Arregoces', 'Dpto. de Edu. Virt. Medios Educ y Audiov', 'Gestor de metas'),
('juanmejia@mail.uniatlantico.edu.co', 'Juan Mejia', 'Dpto. de Edu. Virt. Medios Educ y Audiov', 'Gestor de metas'),
('virtualizacion@mail.uniatlantico.edu.co', 'Roger Sepulveda', 'Dpto. de Edu. Virt. Medios Educ y Audiov', 'Lider de meta'),
('calidadextension@mail.uniatlantico.edu.co', 'Jhon Donado', 'Dpto. de Extens. y Proy. Social', 'Gestor de metas'),
('extension@mail.uniatlantico.edu.co', 'Jorge Duarte', 'Dpto. de Extens. y Proy. Social', 'Lider de meta'),
('diliajaraba@mail.uniatlantico.edu.co', 'Dilia Jaraba Sanchez', 'Dpto. de Fomento y Apoyo a la Investigación', 'Gestor de metas'),
('dptoinvestigaciones@mail.uniatlantico.edu.co', 'Roger Valle', 'Dpto. de Fomento y Apoyo a la Investigación', 'Lider de meta'),
('jeimymunzon@mail.uniatlantico.edu.co', 'Jeimy Munzon', 'Dpto. de Gestión de Talento Humano', 'Gestor de metas'),
('talentohumano@mail.uniatlantico.edu.co', 'Salomon Mejia', 'Dpto. de Gestión de Talento Humano', 'Lider de meta'),
('leisdianaalguero@mail.uniatlantico.edu.co', 'Leisdiana Alguero', 'Dpto. de Gestión Financiera', 'Gestor de metas'),
('financiera@mail.uniatlantico.edu.co', 'Elvira Ricardo Rodriguez', 'Dpto. de Gestión Financiera', 'Lider de meta'),
('juancaraballo@mail.uniatlantico.edu.co', 'Juan Caraballo', 'Dpto. de Inf Física y Servicios Generales', 'Gestor de metas'),
('serviciosgenerales@mail.uniatlantico.edu.co', 'Jose David Redondo Ochoa', 'Dpto. de Inf Física y Servicios Generales', 'Lider de meta'),
('academicopostgrados@mail.uniatlantico.edu.co', 'Elena Serrano', 'Dpto. de Postgrados', 'Gestor de metas'),
('postgrados@mail.uniatlantico.edu.co', 'Edimer Leonardo Latorre Iglesia', 'Dpto. de Postgrados', 'Lider de meta'),
('juliapaez@mail.uniatlantico.edu.co', 'Julia Paez Sanjuan', 'Departamento de Regionalización', 'Lider de meta'),
('desarrollohumano@mail.uniatlantico.edu.co', 'Lidia Romero Santiago', 'Dpto. de Desarrollo Humano', 'Lider de meta'),
('calidadbienestar@mail.uniatlantico.edu.co', 'Desarrollo Humano', 'Dpto. de Desarrollo Humano', 'Gestor de metas'),
('dinaariza@mail.uniatlantico.edu.co', 'Dina Luz Ariza Junco', 'Dirección de Comunicaciones', 'Gestor de metas'),
('comunicaciones@mail.uniatlantico.edu.co', 'Said Gomez', 'Dirección de Comunicaciones', 'Lider de meta'),
('epreciado@mail.uniatlantico.edu.co', 'Eliecer Preciado', 'Dirección de Regionalización nivel sede', 'Lider de meta'),
('inclusion@mail.uniatlantico.edu.co', 'Oris Mercado', 'Vice de Bienestar Universitario', 'Gestor de metas de responsable de línea'),
('liriartelopez@mail.uniatlantico.edu.co', 'Luz Adriana Iriarte', 'Facultad de Arquitectura', 'Gestor de facultad'),
('arquitectura@mail.uniatlantico.edu.co', 'Dalmiro Garcia Estrada', 'Facultad de Arquitectura', 'Lider de meta'),
('aydamarenco@mail.uniatlantico.edu.co', 'Ayda Marenco', 'Facultad de Bellas Artes', 'Gestor de facultad'),
('bellasartes@mail.uniatlantico.edu.co', 'Juan David Gonzalez Betancur', 'Facultad de Bellas Artes', 'Lider de meta'),
('shirleyestrada@mail.uniatlantico.edu.co', 'Shirley Estrada', 'Facultad de Ciencias Básicas', 'Gestor de facultad'),
('cienciasbasicas@mail.uniatlantico.edu.co', 'Carlos Enrique Diaz Uribe', 'Facultad de Ciencias Básicas', 'Lider de meta'),
('michelimanjarres@mail.uniatlantico.edu.co', 'Micheli Manjarres Macea', 'Facultad de Ciencias de la Educación', 'Gestor de facultad'),
('educacion@mail.uniatlantico.edu.co', 'Liliana Selene Martinez Chima', 'Facultad de Ciencias de la Educación', 'Lider de meta'),
('cienciasdelasalud@mail.uniatlantico.edu.co', 'Amparo Bañuelo', 'Facultad de Ciencias de la Salud', 'Gestor de facultad'),
('decanaturacienciasdelasalud@mail.uniatlantico.edu.co', 'Esperanza Florez', 'Facultad de Ciencias de la Salud', 'Lider de meta'),
('malejandromaldonado@mail.uniatlantico.edu.co', 'Manuel Maldonado', 'Facultad de Ciencias Económicas', 'Gestor de facultad'),
('kellyortiz@mail.uniatlantico.edu.co', 'Kelly Ortiz Garcia', 'Facultad de Ciencias Económicas', 'Gestor de facultad'),
('cienciaseconomicas@mail.uniatlantico.edu.co', 'Elcira Solano Benavides', 'Facultad de Ciencias Económicas', 'Lider de meta'),
('maloryalvarez@mail.uniatlantico.edu.co', 'Mallory Alvarez', 'Facultad de Ciencias Humanas', 'Gestor de facultad'),
('cienciashumanas@mail.uniatlantico.edu.co', 'Muriel Jiménez Ortega', 'Facultad de Ciencias Humanas', 'Lider de meta'),
('ajuniorcardozo@mail.uniatlantico.edu.co', 'Asdruval Cardozo', 'Facultad de Ciencias Jurídicas', 'Gestor de facultad'),
('cienciasjuridicas@mail.uniatlantico.edu.co', 'Claudia Rodriguez Albor', 'Facultad de Ciencias Jurídicas', 'Lider de meta'),
('calidad.ingenieria@mail.uniatlantico.edu.co', 'Andrea Carolina Heredia Berdugo', 'Facultad de Ingeniería', 'Gestor de facultad'),
('ingenieria@mail.uniatlantico.edu.co', 'Milton Fabian Coba Salcedo', 'Facultad de Ingeniería', 'Lider de meta'),
('winstonfontalvo@mail.uniatlantico.edu.co', 'Winston Fontalvo', 'Facultad de Química y Farmacia', 'Gestor de facultad'),
('kbrochado@mail.uniatlantico.edu.co', 'Karen Brochado Ariza', 'Facultad de Química y Farmacia', 'Gestor de facultad'),
('quimicayfarmacia@mail.uniatlantico.edu.co', 'Óscar Camacho Romero', 'Facultad de Química y Farmacia', 'Lider de meta'),
('abrahamadie@mail.uniatlantico.edu.co', 'Abraham Adie', 'Museo de Antropología', 'Gestor de metas'),
('antropologia@mail.uniatlantico.edu.co', 'Alvaro Martes Ortega', 'Museo de Antropología', 'Lider de meta'),
('elaineramirez@mail.uniatlantico.edu.co', 'Elaine Ramirez Jan', 'Oficina de Egresados', 'Gestor de metas'),
('egresados@mail.uniatlantico.edu.co', 'Renzo Tamara', 'Oficina de Egresados', 'Lider de meta'),
('informatica@mail.uniatlantico.edu.co', 'Olga Marin', 'Oficina de Gest. Tec. e Información', 'Lider de meta'),
('alexandranunez@mail.uniatlantico.edu.co', 'Alexandra Nuñez Orozco', 'Oficina de Planeación', 'Gestor de metas'),
('angielandinez@mail.uniatlantico.edu.co', 'Anggie Landinez', 'Oficina de Planeación', 'Gestor de metas'),
('celiaurdaneta@mail.uniatlantico.edu.co', 'Celia Urdaneta', 'Oficina de Planeación', 'Gestor de metas'),
('consuelocamargo@mail.uniatlantico.edu.co', 'Consuelo Camargo', 'Oficina de Planeación', 'Gestor de metas'),
('josepachecoq@mail.uniatlantico.edu.co', 'Jose Pacheco', 'Oficina de Planeación', 'Gestor de metas'),
('sdguzman@mail.uniatlantico.edu.co', 'Samuel Guzman', 'Oficina de Planeación', 'Gestor de metas'),
('karenthdelgado@mail.uniatlantico.edu.co', 'Karenth Delgado', 'Oficina de Planeación', 'Gestor de metas'),
('planeacion@mail.uniatlantico.edu.co', 'Carlos Hernandez Kligman', 'Oficina de Planeación', 'Lider de meta'),
('jamuriel@mail.uniatlantico.edu.co', 'Julio Muriel', 'Oficina de Planeación', 'Gestor de metas'),
('gracielangulo@mail.uniatlantico.edu.co', 'Graciela Angulo', 'Oficina de Planeación', 'Gestor de metas'),
('juanpaez@mail.uniatlantico.edu.co', 'Juan Sebastian Paez', 'Oficina de Planeación', 'Gestor de metas'),
('layzconsuegra@mail.uniatlantico.edu.co', 'Lays Consuegra', 'Oficina de Rel. Interinst. e Internacionales', 'Gestor de metas'),
('internacionales@mail.uniatlantico.edu.co', 'José Alejandro Comas Limas', 'Oficina de Rel. Interinst. e Internacionales', 'Lider de meta'),
('smparra@mail.uniatlantico.edu.co', 'Sandra Parra', 'Secretaría General', 'Gestor de metas'),
('secretariageneral@mail.uniatlantico.edu.co', 'Josefa Cassiani Perez', 'Secretaría General', 'Lider de meta'),
('viceadministrativa@mail.uniatlantico.edu.co', 'Maryluz Stevenson Del Vecchio', 'Vicerrectoría Administrativa y Financiera', 'Responsable de línea'),
('linabarros@mail.uniatlantico.edu.co', 'Lina Barros', 'Vice de Bienestar Universitario', 'Gestor de metas de responsable de línea'),
('bienestar@mail.uniatlantico.edu.co', 'Alvaro Gonzales Aguilar', 'Vice de Bienestar Universitario', 'Responsable de línea'),
('karenhernandeza@mail.uniatlantico.edu.co', 'Karen Hernandez', 'Vicerrectoría de Docencia', 'Gestor de metas de responsable de línea'),
('greysvillanueva@mail.uniatlantico.edu.co', 'Greys Villanueva', 'Vicerrectoría de Docencia', 'Gestor de metas de responsable de línea'),
('vicedocencia@mail.uniatlantico.edu.co', 'Alejandro Urieles', 'Vicerrectoría de Docencia', 'Responsable de línea'),
('investigaciones@mail.uniatlantico.edu.co', 'Wendell Archibold Barrios', 'Vice de Invest, Ext y Proy Social', 'Responsable de línea'),
('rosanajimenez@mail.uniatlantico.edu.co', 'Rosana Jiménez Nuñez', 'Vicerrectoría de Regional y desarrollo Territorial', 'Gestor de metas de responsable de línea'),
('vicerrectoriaderegionalizacion@mail.uniatlantico.edu.co', 'Wilson Quimbayo Ospina', 'Vicerrectoría de Regional y desarrollo Territorial', 'Responsable de línea');

-- Dependencias (cargos) que aun no existan
INSERT INTO `cargos` (`nombre`, `activo`, `fecha_creacion`)
SELECT DISTINCT t.`dependencia`, 1, NOW()
FROM `tmp_usuarios_metas` t
WHERE NOT EXISTS (SELECT 1 FROM `cargos` c WHERE c.`nombre` = t.`dependencia`);

-- Usuarios (se omiten los que ya existan por username o email)
INSERT INTO `usuarios` (`username`, `password`, `nombre`, `email`, `rol`, `avatar`, `activo`, `cargo_id`)
SELECT t.`email`,
       '$2y$10$iL115rAQuRZnnc1eyJ8Z2esJZPN7eiOmzlsrfJMFnPIl.FW5I2CF6',
       t.`nombre`,
       t.`email`,
       t.`rol`,
       NULL,
       1,
       (SELECT MIN(c.`id`) FROM `cargos` c WHERE c.`nombre` = t.`dependencia`)
FROM `tmp_usuarios_metas` t
WHERE NOT EXISTS (
  SELECT 1 FROM `usuarios` u WHERE u.`username` = t.`email` OR u.`email` = t.`email`
);

-- Permisos por defecto: Chat, Dashboard y FOR-DE-144
INSERT IGNORE INTO `detalle_permiso` (`id_usuario`, `id_permiso`, `fecha_asignacion`)
SELECT u.`id`, lp.`id`, NOW()
FROM `usuarios` u
JOIN `tmp_usuarios_metas` t ON t.`email` = u.`email`
JOIN `lista_permisos` lp
  ON lp.`estado` = 1
 AND (lp.`nombre` IN ('ver_dashboard', 'for_de_144', 'gestionar_recetas') OR lp.`nombre` LIKE '%chat%');

-- Sub-permiso "ver" de los permisos asignados (igual que al habilitarlos desde la app)
INSERT IGNORE INTO `detalle_subpermiso` (`usuario_id`, `subpermiso_id`, `fecha_asignacion`)
SELECT dp.`id_usuario`, s.`id`, NOW()
FROM `detalle_permiso` dp
JOIN `usuarios` u ON u.`id` = dp.`id_usuario`
JOIN `tmp_usuarios_metas` t ON t.`email` = u.`email`
JOIN `subpermisos` s ON s.`permiso_id` = dp.`id_permiso` AND s.`nombre` = 'ver' AND s.`estado` = 1;

DROP TEMPORARY TABLE IF EXISTS `tmp_usuarios_metas`;
