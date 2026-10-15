<?php
// database/convertir_consolidado.php
// Convierte la hoja "FORMULACIÓN" de "FOR-DE-144 Consolidado. 2026.xlsx" en una plantilla lista para el botón
// Importar de Formulación: mismas columnas que la plantilla del sistema y los N° en blanco, para que cada fila
// se cree como un indicador nuevo en BORRADOR.
//
// Uso (desde la raíz del proyecto):  php database/convertir_consolidado.php [archivo.xlsx] [salida.xlsx]
// No usa la base de datos.

if (PHP_SAPI !== 'cli') { http_response_code(403); exit("Ejecutar solo por línea de comandos.\n"); }
chdir(dirname(__DIR__));
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
require_once 'config/ExcelLibro.php';

$origen = $argv[1] ?? 'FOR-DE-144 Consolidado. 2026.xlsx';
$salida = $argv[2] ?? 'plantilla_consolidado_2026.xlsx';
if (!is_file($origen)) exit("No se encontró el archivo: $origen\n");

// Columna del consolidado (índice) para cada columna de la plantilla
$origenCol = [
    'id' => 0, 'anio' => 1, 'linea_estrategica' => 2, 'objetivo' => 3, 'estrategia' => 4, 'motor_desarrollo' => 5,
    'meta_resultado' => 6, 'proyecto' => 7, 'ponderacion_proyectos' => 8, 'actividad_proyecto' => 9,
    'ponderacion_actividades' => 10, 'responsable_formulacion' => 11, 'id_indicador' => 12, 'gestionado_facultades' => 13,
    'nombre_indicador' => 14, 'formula_medicion' => 15, 'frecuencia_medicion' => 16, 'unidad_medida' => 17,
    'descripcion_indicador' => 18, 'tipo_medicion' => 19, 'linea_base_meta' => 20, 'meta_s1' => 21, 'meta_s2' => 22,
    'anio_base_meta' => 24, 'propuestas_rectorales' => 25, 'plan_mejoramiento' => 26,
];
const PRIMERA_COL_PLANES = 27;

// Responsable del consolidado → dependencia del catálogo del sistema (tabla cargos)
$dependencias = [
    'Director Consultorio Jurídico y Centro de Conciliación' => 'Consultorio Jurídico y Centro de Conciliación',
    'Director de Comunicaciones' => 'Dirección de Comunicaciones',
    'Director de Regionalización nivel Sede' => 'Dirección de Regionalización nivel sede',
    'Director del Museo de Antropología' => 'Museo de Antropología',
    'Jefe Departamento de Desarrollo Humano' => 'Dpto. de Desarrollo Humano',
    'Jefe de Oficina de Egresados' => 'Oficina de Egresados',
    'Jefe de Oficina de Gestión Tecnológica e Información' => 'Oficina de Gest. Tec. e Información',
    'Jefe de Oficina de Planeación' => 'Oficina de Planeación',
    'Jefe de Oficina de Relaciones Interinstitucionales e Internacionales' => 'Oficina de Rel. Interinst. e Internacionales',
    'Jefe del Departamento Calidad Academica' => 'Dpto. Calidad Académica',
    'Jefe del Departamento Extensión y Proyección Social' => 'Dpto. de Extens. y Proy. Social',
    'Jefe del Departamento de Admisiones y Registro' => 'Dpto. de Admisiones y Registro',
    'Jefe del Departamento de Biblioteca' => 'Departamento de Biblioteca',
    'Jefe del Departamento de Educación Virtual, Medios Educativos y Audiovisuales' => 'Dpto. de Edu. Virt. Medios Educ y Audiov',
    'Jefe del Departamento de Fomento y Apoyo a la Investigación' => 'Dpto. de Fomento y Apoyo a la Investigación',
    'Jefe del Departamento de Gestión Financiera' => 'Dpto. de Gestión Financiera',
    'Jefe del Departamento de Gestión de Talento Humano' => 'Dpto. de Gestión de Talento Humano',
    'Jefe del Departamento de Infraestructura Física y Servicios Generales' => 'Dpto. de Inf Física y Servicios Generales',
    'Jefe del Departamento de Postgrado' => 'Dpto. de Postgrados',
    'Secretario General' => 'Secretaría General',
    'Vicerrector De Docencia' => 'Vicerrectoría de Docencia',
    'Vicerrector de Bienestar Universitario' => 'Vice de Bienestar Universitario',
];
$sinEquivalencia = [];

$libro = ExcelLibro::leer($origen);
$hoja = null;
foreach ($libro as $nombre => $filas) {
    if (mb_strtoupper(trim($nombre), 'UTF-8') === 'FORMULACIÓN') { $hoja = $filas; break; }
}
if ($hoja === null) exit("El archivo no tiene la hoja FORMULACIÓN.\n");

// Columnas de la plantilla: las de Formulación + una por plan institucional (títulos de la fila bajo el grupo)
require_once 'config/config.php';
require_once 'modelo/Modulo144Model.php';
require_once 'controlador/Plantilla144Controller.php';
$props = (new ReflectionClass('Plantilla144Controller'))->getDefaultProperties();
$cols = $props['colsFormulacion'];

$filaTitulos = null;
$datos = [];
$planes = []; // índice de columna => nombre
foreach ($hoja as $i => $fila) {
    if ($filaTitulos === null) {
        if (trim((string)($fila[0] ?? '')) === 'N°') $filaTitulos = $i;
        continue;
    }
    if ($i === $filaTitulos + 1) {
        foreach ($fila as $c => $t) {
            $t = trim((string)$t);
            if ((int)$c >= PRIMERA_COL_PLANES && $t !== '') $planes[(int)$c] = $t;
        }
        continue;
    }
    if (!ctype_digit(trim((string)($fila[0] ?? '')))) continue;
    $datos[] = $fila;
}
if ($filaTitulos === null) exit("No se encontró la fila de títulos (columna N°).\n");

$columnas = [];
foreach ($cols as $k => $c) $columnas[$k] = ['titulo' => $c[0], 'ancho' => $c[1]] + ($c[2] ?? []);
foreach ($planes as $c => $nombre) $columnas['plan:' . $c] = ['titulo' => $nombre, 'ancho' => 18, 'grupo' => '21. PLANES INSTITUCIONALES DECRETO 612 DE 2018'];

$filas = [];
foreach ($datos as $fila) {
    $f = [];
    foreach ($columnas as $k => $col) {
        if ($k === 'id') { $f[] = ''; continue; }                                     // N° en blanco = indicador nuevo
        $idx = strpos($k, 'plan:') === 0 ? (int)substr($k, 5) : ($origenCol[$k] ?? null);
        $v = $idx === null ? '' : trim((string)($fila[$idx] ?? ''));
        if ($k === 'responsable_formulacion' && $v !== '') {
            if (isset($dependencias[$v])) $v = $dependencias[$v];
            else $sinEquivalencia[$v] = true;
        }
        $f[] = $v;
    }
    $filas[] = $f;
}

ob_start();
ExcelLibro::descargar('x', [['nombre' => 'Formulación', 'columnas' => array_values($columnas), 'filas' => $filas]]);
$bin = ob_get_clean();
file_put_contents($salida, $bin);
foreach (array_keys($sinEquivalencia) as $r) echo "Sin equivalencia de dependencia: $r\n";
echo "Indicadores: " . count($filas) . "\nPlanes institucionales (columnas): " . count($planes) . "\nArchivo: $salida\n";
