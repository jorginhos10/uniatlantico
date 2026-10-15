<?php
// controlador/Plantilla144Controller.php
// Exportar / importar la plantilla Excel del Plan de Acción (Módulo 144).
// La plantilla tiene dos hojas: "Formulación" y "Seguimiento". Trae los registros que el usuario
// puede editar (para corregirlos) y se pueden agregar filas nuevas en Formulación (columna ID vacía).
require_once 'modelo/Modulo144Model.php';
require_once 'config/ExcelLibro.php';

class Plantilla144Controller {
    private $model;

    const HOJA_FORMULACION = 'Formulación';
    const HOJA_SEGUIMIENTO = 'Seguimiento';
    const FILAS_VACIAS = 30; // filas libres al final de "Formulación" para agregar indicadores nuevos

    // Formato institucional de la hoja Formulación (mismo orden y títulos que el formato FOR-DE-144).
    // clave => [título, ancho, opciones]. Después se agregan una columna por plan institucional (21.)
    // y una por facultad (22.), que se marcan con X.
    private $colsFormulacion = [
        'id'                      => ['N°', 8, ['sombreado' => true]],
        'anio'                    => ['AÑO', 9],
        'linea_estrategica'       => ['5. LÍNEA ESTRATÉGICA', 22],
        'objetivo'                => ['6. OBJETIVO', 46, ['sombreado' => true]],
        'estrategia'              => ['7. ESTRATEGIA', 30],
        'motor_desarrollo'        => ['8. MOTOR DE DESARROLLO', 24],
        'meta_resultado'          => ['9. META DE RESULTADO', 50],
        'proyecto'                => ['10. PROYECTO', 30],
        'ponderacion_proyectos'   => ['11. PONDERACIÓN DE LOS PROYECTOS', 18, ['formato' => 'pct0']],
        'actividad_proyecto'      => ['12. ACTIVIDAD DEL PROYECTO', 40],
        'ponderacion_actividades' => ['13. PONDERACIÓN DE LA ACTIVIDADES POR PROYECTO', 18, ['formato' => 'pct0']],
        'responsable_formulacion' => ['14. RESPONSABLE', 22],
        'id_indicador'            => ['ID.INDICADOR', 7, ['vertical' => true]],
        'gestionado_facultades'   => ['15. GESTIONADO EN FACULTADES', 9, ['vertical' => true]],
        'nombre_indicador'        => ['16.1 NOMBRE DEL INDICADOR', 32, ['grupo' => '16. INDICADOR DE RESULTADO']],
        'formula_medicion'        => ['16.2 FÓRMULA DE LA MEDICIÓN', 36, ['grupo' => '16. INDICADOR DE RESULTADO']],
        'frecuencia_medicion'     => ['16.3 FRECUENCIA DE MEDICIÓN', 16, ['grupo' => '16. INDICADOR DE RESULTADO']],
        'unidad_medida'           => ['16.4. UNIDAD DE MEDIDA', 16, ['grupo' => '16. INDICADOR DE RESULTADO']],
        'descripcion_indicador'   => ['DESCRIPCIÓN', 46, ['grupo' => '16. INDICADOR DE RESULTADO']],
        'tipo_medicion'           => ['16.5 TIPO DE MEDICIÓN', 18, ['grupo' => '16. INDICADOR DE RESULTADO']],
        'linea_base_meta'         => ['17.1 LÍNEA BASE', 13, ['grupo' => '17. META PROPUESTA']],
        'meta_s1'                 => ['17.2 SEMESTRE 1', 13, ['grupo' => '17. META PROPUESTA']],
        'meta_s2'                 => ['17.3 SEMESTRE 2', 13, ['grupo' => '17. META PROPUESTA']],
        'anio_base_meta'          => ['17.4 AÑO', 13, ['grupo' => '17. META PROPUESTA', 'sombreado' => true]],
        'propuestas_rectorales'   => ['19. Propuestas rectorales', 18],
        'plan_mejoramiento'       => ['20. Plan de Mejoramiento Institucional', 18],
    ];

    // Columnas que el sistema calcula o que no se guardan: se exportan pero no se importan
    private $soloLectura = ['anio', 'objetivo', 'ponderacion_proyectos', 'anio_base_meta', 'propuestas_rectorales', 'plan_mejoramiento'];

    const GRUPO_PLANES     = '21. PLANES INSTITUCIONALES DECRETO 612 DE 2018';
    const GRUPO_FACULTADES = '22. GESTIÓN POR FACULTADES';

    // Formato institucional de la hoja Seguimiento. Lo sombreado viene de la formulación (solo lectura);
    // se llenan Semestre 1, Semestre 2 y Estado de proyectos.
    private $colsSeguimiento = [
        'id'                       => ['N°', 8, ['sombreado' => true]],
        'anio'                     => ['AÑO', 9, ['sombreado' => true]],
        'linea_estrategica'        => ['LÍNEA ESTRATÉGICA', 20, ['sombreado' => true]],
        'objetivo'                 => ['OBJETIVO', 40, ['sombreado' => true]],
        'estrategia'               => ['ESTRATEGIA', 28, ['sombreado' => true]],
        'motor_desarrollo'         => ['MOTOR DE DESARROLLO', 20, ['sombreado' => true]],
        'meta_resultado'           => ['META DE RESULTADO', 44, ['sombreado' => true]],
        'proyecto'                 => ['PROYECTO', 38, ['sombreado' => true]],
        'ponderacion_proyectos'    => ['PONDERACIÓN DE LOS PROYECTOS', 18, ['sombreado' => true, 'formato' => 'pct0']],
        'actividad_proyecto'       => ['ACTIVIDAD DEL PROYECTO', 20, ['sombreado' => true]],
        'ponderacion_actividades'  => ['PONDERACIÓN DE LA ACTIVIDADES POR PROYECTO', 16, ['sombreado' => true, 'formato' => 'pct0']],
        'responsable_formulacion'  => ['RESPONSABLE', 20, ['sombreado' => true]],
        'id_indicador'             => ['ID.INDICADOR', 7, ['sombreado' => true, 'vertical' => true]],
        'nombre_indicador'         => ['NOMBRE DEL INDICADOR', 24, ['sombreado' => true, 'grupo' => 'INDICADOR DE RESULTADO']],
        'formula_medicion'         => ['FÓRMULA DE LA MEDICIÓN', 30, ['sombreado' => true, 'grupo' => 'INDICADOR DE RESULTADO']],
        'frecuencia_medicion'      => ['FRECUENCIA DE MEDICIÓN', 10, ['sombreado' => true, 'grupo' => 'INDICADOR DE RESULTADO']],
        'unidad_medida'            => ['UNIDAD DE MEDIDA', 12, ['sombreado' => true, 'grupo' => 'INDICADOR DE RESULTADO']],
        'descripcion_indicador'    => ['DESCRIPCIÓN', 24, ['sombreado' => true, 'grupo' => 'INDICADOR DE RESULTADO']],
        'tipo_medicion'            => ['TIPO DE MEDICIÓN', 15, ['sombreado' => true, 'grupo' => 'INDICADOR DE RESULTADO']],
        'anio_base_meta'           => ['META P.A.', 10, ['sombreado' => true, 'grupo' => 'INDICADOR DE RESULTADO']],
        'linea_base_meta'          => ['LÍNEA BASE', 13, ['sombreado' => true, 'grupo' => 'INDICADOR DE RESULTADO']],
        'semestre1_seguimiento'    => ['SEMESTRE 1', 16, ['grupo' => 'REPORTE DE LA META']],
        'semestre2_seguimiento'    => ['SEMESTRE 2', 15, ['grupo' => 'REPORTE DE LA META']],
        'reporte_anio'             => ['AÑO', 10, ['sombreado' => true, 'grupo' => 'REPORTE DE LA META']],
        'seg_fac'                  => ['SEG. FAC', 11, ['sombreado' => true]],
        'logros'                   => ['LOGROS', 60, ['grupo' => 'ESTADO DE PROYECTOS', 'encabezadoClaro' => true]],
        'limites'                  => ['LÍMITES', 34, ['grupo' => 'ESTADO DE PROYECTOS', 'encabezadoClaro' => true]],
        'observacion_estado'       => ['OBSERVACIÓN', 34, ['grupo' => 'ESTADO DE PROYECTOS', 'encabezadoClaro' => true]],
        'acciones_fortalecimiento' => ['ACCIONES DE FORTALECIMIENTO', 64, ['grupo' => 'ESTADO DE PROYECTOS', 'encabezadoClaro' => true]],
    ];

    private $opcionesFrecuencia = ['Mensual', 'Bimestral', 'Trimestral', 'Semestral', 'Anual'];
    private $opcionesUnidad     = ['Unidad', 'Porcentaje'];
    private $opcionesTipo       = ['Acumulado', 'Nuevo gestionado durante la vigencia', 'Promedio', 'Último valor reportado', 'Límite'];

    public function __construct() {
        $this->model = new Modulo144Model();
    }

    // ════════════════════════ EXPORTAR ════════════════════════

    public function exportar() {
        $formulario = $this->model->verificarFormulario((int)($_GET['id'] ?? 0));
        if (!$formulario) {
            http_response_code(404);
            echo 'Formulario no encontrado';
            return;
        }

        $catalogos = $this->catalogos();
        $distribucion = $this->model->getFacultadesConDistribucion((int)$formulario['id']);
        $registros = array_values(array_filter($this->model->getRegistrosParaPlantilla((int)$formulario['id']), [$this, 'puedeEditar']));
        $columnas = $this->columnasFormulacion($catalogos, count($registros));

        // El objetivo es el de la línea estratégica: si un registro lo trae vacío, se completa desde el catálogo
        $objetivoPorLinea = [];
        foreach ($catalogos['lineas'] as $l) {
            $n = $this->normalizar($l['nombre']);
            if (trim((string)($l['objetivo'] ?? '')) !== '' || !isset($objetivoPorLinea[$n])) $objetivoPorLinea[$n] = (string)($l['objetivo'] ?? '');
        }
        foreach ($registros as &$reg) {
            if (trim((string)($reg['objetivo'] ?? '')) === '') {
                $reg['objetivo'] = $objetivoPorLinea[$this->normalizar($reg['linea_estrategica'] ?? '')] ?? '';
            }
        }
        unset($reg);

        $filasF = [];
        $filasS = [];
        foreach ($registros as $r) {
            $planes = json_decode((string)($r['planes_institucionales'] ?? ''), true);
            $planes = is_array($planes) ? array_map([$this, 'normalizar'], $planes) : [];
            $facultadesConValor = $distribucion[(int)$r['id']] ?? [];
            // Las metas se muestran como porcentaje cuando la unidad de medida es "Porcentaje"
            $fmtMeta = ($r['unidad_medida'] ?? '') === 'Porcentaje' ? 'pct2' : 'num2';
            $conCodigo = function ($codigo, $nombre) {
                $nombre = (string)$nombre;
                return $nombre !== '' && $codigo ? $codigo . ' - ' . $nombre : $nombre;
            };

            $fila = [];
            foreach ($columnas as $k => $col) {
                if (strpos($k, 'plan:') === 0) {
                    $fila[] = in_array($this->normalizar(substr($k, 5)), $planes, true) ? 'X' : '';
                    continue;
                }
                if (strpos($k, 'fac:') === 0) {
                    $fila[] = in_array((int)substr($k, 4), $facultadesConValor, true) ? 'X' : '';
                    continue;
                }
                switch ($k) {
                    case 'linea_estrategica': $fila[] = $conCodigo($r['linea_codigo'] ?? '', $r[$k] ?? ''); break;
                    case 'motor_desarrollo':  $fila[] = $conCodigo($r['motor_codigo'] ?? '', $r[$k] ?? ''); break;
                    case 'proyecto':          $fila[] = $conCodigo($r['proyecto_codigo'] ?? '', $r[$k] ?? ''); break;
                    case 'gestionado_facultades': $fila[] = (int)($r[$k] ?? 0) === 1 ? 'X' : ''; break;
                    case 'responsable_formulacion': $fila[] = implode('; ', $this->separarResponsables($r[$k] ?? '')); break;
                    case 'linea_base_meta': case 'meta_s1': case 'meta_s2': case 'anio_base_meta':
                        $fila[] = ['v' => $r[$k] ?? '', 'formato' => $fmtMeta]; break;
                    case 'propuestas_rectorales': case 'plan_mejoramiento': $fila[] = ''; break;
                    default: $fila[] = $r[$k] ?? '';
                }
            }
            $filasF[] = $fila;

            // En Seguimiento solo van las formulaciones publicadas
            if ((int)$r['estado_formulacion'] === 2) {
                $filaS = [];
                foreach (array_keys($this->colsSeguimiento) as $k) {
                    switch ($k) {
                        case 'linea_estrategica': $filaS[] = $conCodigo($r['linea_codigo'] ?? '', $r[$k] ?? ''); break;
                        case 'motor_desarrollo':  $filaS[] = $conCodigo($r['motor_codigo'] ?? '', $r[$k] ?? ''); break;
                        case 'proyecto':          $filaS[] = $conCodigo($r['proyecto_codigo'] ?? '', $r[$k] ?? ''); break;
                        case 'responsable_formulacion': $filaS[] = implode('; ', $this->separarResponsables($r[$k] ?? '')); break;
                        case 'anio_base_meta': case 'linea_base_meta': case 'semestre1_seguimiento': case 'semestre2_seguimiento':
                            $filaS[] = ['v' => $r[$k] ?? '', 'formato' => $fmtMeta]; break;
                        case 'reporte_anio':
                            $filaS[] = ['v' => $this->cumplidoAnual($r), 'formato' => $fmtMeta]; break;
                        // Indicador gestionado por facultades: su seguimiento por facultad se registra en el sistema
                        case 'seg_fac': $filaS[] = ((int)($r['gestionado_facultades'] ?? 0) === 1 || $facultadesConValor) ? 'X' : ''; break;
                        default: $filaS[] = $r[$k] ?? '';
                    }
                }
                $filasS[] = $filaS;
            }
        }
        for ($i = 0; $i < self::FILAS_VACIAS; $i++) $filasF[] = [];

        $nombre = 'plantilla_plan_accion_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $formulario['titulo'] ?? 'formulario') . '_' . date('Y-m-d');
        $totalActividades = count($registros);
        ExcelLibro::descargar($nombre, [
            [
                'nombre'   => self::HOJA_FORMULACION,
                'columnas' => $this->columnasExcel($columnas, 0),
                'filas'    => $filasF,
            ],
            [
                'nombre'   => self::HOJA_SEGUIMIENTO,
                'columnas' => $this->columnasExcel($this->colsSeguimiento, $totalActividades),
                'filas'    => $filasS,
            ],
        ]);
    }

    // ════════════════════════ IMPORTAR ════════════════════════

    public function importar() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $formulario = $this->model->verificarFormulario((int)($_POST['formulario_id'] ?? 0));
        if (!$formulario) {
            echo json_encode(['success' => false, 'message' => 'Formulario no encontrado']);
            return;
        }
        $fechas = $this->model->verificarFechaHabil($formulario);
        if (!$fechas['valido'] && !$this->esAdmin()) {
            echo json_encode(['success' => false, 'message' => 'El formulario no está habilitado: ' . strip_tags($fechas['mensaje'])]);
            return;
        }

        $archivo = $_FILES['archivo'] ?? null;
        if (!$archivo || $archivo['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Selecciona el archivo Excel de la plantilla']);
            return;
        }
        if (strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION)) !== 'xlsx') {
            echo json_encode(['success' => false, 'message' => 'El archivo debe ser .xlsx (la plantilla exportada)']);
            return;
        }

        try {
            $libro = ExcelLibro::leer($archivo['tmp_name']);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            return;
        }

        $hojaF = $this->buscarHoja($libro, self::HOJA_FORMULACION);
        $hojaS = $this->buscarHoja($libro, self::HOJA_SEGUIMIENTO);
        if ($hojaF === null && $hojaS === null) {
            echo json_encode(['success' => false, 'message' => 'El archivo no tiene las hojas "Formulación" y "Seguimiento" de la plantilla']);
            return;
        }

        @set_time_limit(300);
        $resumen = [
            'formulacion' => ['creados' => 0, 'actualizados' => 0, 'sin_cambios' => 0],
            'ids_creados' => [], // ids de los indicadores nuevos (borrador), para poder publicarlos después
            'seguimiento' => ['actualizados' => 0, 'sin_cambios' => 0],
            'errores'     => [],
        ];

        $registros = [];
        foreach ($this->model->getRegistrosParaPlantilla((int)$formulario['id']) as $r) $registros[(int)$r['id']] = $r;

        if ($hojaF !== null) $this->importarFormulacion($hojaF, $formulario, $registros, $resumen);
        if ($hojaS !== null) $this->importarSeguimiento($hojaS, $registros, $resumen);

        $f = $resumen['formulacion'];
        $s = $resumen['seguimiento'];
        $total = $f['creados'] + $f['actualizados'] + $s['actualizados'];
        echo json_encode([
            'success' => true,
            'message' => $total === 0 && !$resumen['errores'] ? 'No había cambios para importar' : 'Importación terminada',
            'resumen' => $resumen,
        ]);
    }

    private function importarFormulacion(array $filas, array $formulario, array &$registros, array &$resumen) {
        $catalogos = $this->catalogos();
        $columnas = $this->columnasFormulacion($catalogos, 0);
        [$mapa, $filaInicio] = $this->mapaEncabezados($filas, $columnas);
        if (!isset($mapa['id'])) {
            $resumen['errores'][] = 'Hoja Formulación: no se encontró la columna N°. Usa la plantilla exportada.';
            return;
        }
        // Archivo en formato institucional (FOR-DE-144 Consolidado): el N° es un consecutivo, no un ID del sistema
        $this->importacionInstitucional = $this->filaTitulosBase > 0;
        $columnasPlan = array_filter(array_keys($mapa), function ($k) { return strpos($k, 'plan:') === 0; });
        $columnasFac  = array_filter(array_keys($mapa), function ($k) { return strpos($k, 'fac:') === 0; });

        foreach ($filas as $numFila => $fila) {
            if ($numFila < $filaInicio) continue; // encabezados
            $excelFila = $numFila + 1;
            $celda = function ($k) use ($mapa, $fila) {
                return isset($mapa[$k]) ? trim((string)($fila[$mapa[$k]] ?? '')) : null;
            };
            // Los planes marcados con X se pasan a la validación como una lista, igual que antes
            $val = function ($k) use ($celda, $columnasPlan) {
                if (in_array($k, $this->soloLectura, true)) return null;
                if ($k === 'planes_institucionales') {
                    if (!$columnasPlan) return null;
                    $marcados = [];
                    foreach ($columnasPlan as $cp) if ($this->marcado($celda($cp))) $marcados[] = substr($cp, 5);
                    return implode('; ', $marcados);
                }
                return $celda($k);
            };

            // Fila sin datos editables: se ignora
            $tieneDatos = false;
            foreach (array_keys($mapa) as $k) {
                if ($k !== 'id' && !in_array($k, $this->soloLectura, true) && $celda($k) !== null && $celda($k) !== '') { $tieneDatos = true; break; }
            }
            $id = $this->importacionInstitucional ? 0 : (int)$celda('id');
            if (!$tieneDatos && $id <= 0) continue;

            $pref = "Formulación, fila {$excelFila}: ";
            $actual = [];
            if ($id > 0) {
                $actual = $registros[$id] ?? null;
                if (!$actual) { $resumen['errores'][] = $pref . "el ID {$id} no pertenece a este formulario."; continue; }
                if (!$this->puedeEditar($actual)) { $resumen['errores'][] = $pref . "no tienes permiso para editar el ID {$id}."; continue; }
            }

            [$datos, $errores] = $this->validarFormulacion($val, $catalogos, $formulario);
            $publicado = $id > 0 && (int)$actual['estado_formulacion'] !== 0;
            if ($errores) {
                // Un publicado no se modifica desde la plantilla: sus datos no se validan ni generan errores
                if ($publicado) { $resumen['formulacion']['sin_cambios']++; continue; }
                foreach ($errores as $e) $resumen['errores'][] = $pref . $e;
                continue;
            }
            $this->calcularValorAnual($actual, $datos);

            // Facultades marcadas con X (o con un número = su distribución)
            $distNueva = null;
            if ($columnasFac) {
                [$distNueva, $erroresFac] = $this->leerFacultades($columnasFac, $celda, $id > 0 ? $id : 0);
                if ($erroresFac) { foreach ($erroresFac as $e) $resumen['errores'][] = $pref . $e; continue; }
                if (array_filter($distNueva)) $datos['gestionado_facultades'] = 1;
            }
            $distCambia = $distNueva !== null && $this->distribucionCambia($id, $distNueva);

            // Publicado: no se modifica desde la plantilla (primero hay que devolverlo a borrador)
            if ($id > 0 && (int)$actual['estado_formulacion'] !== 0) {
                if ($this->sinCambios($actual, $datos) && !$distCambia) $resumen['formulacion']['sin_cambios']++;
                else $resumen['errores'][] = $pref . "el ID {$id} ya está publicado; sus cambios de formulación no se aplicaron.";
                continue;
            }

            // Ponderación de actividades: el total del proyecto no puede pasar de 100 %
            if (isset($datos['ponderacion_actividades']) && $datos['ponderacion_actividades'] !== null && ($datos['proyecto'] ?? '') !== '') {
                $acumulado = $this->model->getAcumuladoProyecto((int)$formulario['id'], $datos['proyecto'], $id);
                if ($acumulado + (float)$datos['ponderacion_actividades'] > 100.005) {
                    $resumen['errores'][] = $pref . 'la ponderación de actividades supera el 100 % del proyecto (disponible: ' . max(0, round(100 - $acumulado, 2)) . ' %).';
                    continue;
                }
            }

            if ($id <= 0) {
                $nombre = $datos['nombre_indicador'] ?: ($datos['formula_medicion'] ?: 'Indicador importado');
                $ok = $this->model->crearBorrador('formulacion', (int)$formulario['id'], mb_substr($nombre, 0, 250), (int)($_SESSION['usuario_id'] ?? 1));
                $nuevoId = $ok ? $this->model->ultimoIdInsertado() : 0;
                if ($nuevoId <= 0) { $resumen['errores'][] = $pref . 'no se pudo crear el registro.'; continue; }
                $datos['nombre_borrador'] = mb_substr($nombre, 0, 250);
                $this->model->actualizar('formulacion', $nuevoId, $datos);
                if ($distNueva !== null) $this->guardarDistribucion($nuevoId, $distNueva);
                $registros[$nuevoId] = ['id' => $nuevoId, 'estado_formulacion' => 0, 'creado_por' => $_SESSION['usuario_id'] ?? 0] + $datos;
                $resumen['formulacion']['creados']++;
                $resumen['ids_creados'][] = (int)$nuevoId;
            } else {
                if ($this->sinCambios($registros[$id], $datos) && !$distCambia) { $resumen['formulacion']['sin_cambios']++; continue; }
                if ($this->model->actualizar('formulacion', $id, $datos)) {
                    if ($distCambia) $this->guardarDistribucion($id, $distNueva);
                    $registros[$id] = array_merge($registros[$id], $datos);
                    $resumen['formulacion']['actualizados']++;
                } else {
                    $resumen['errores'][] = $pref . 'no se pudo guardar.';
                }
            }
        }
    }

    // true cuando la hoja Formulación venía en el formato institucional del consolidado (sin IDs del sistema)
    private $importacionInstitucional = false;

    private function importarSeguimiento(array $filas, array $registros, array &$resumen) {
        // La hoja Seguimiento del consolidado tiene otro formato y no trae IDs del sistema: no se importa
        if ($this->importacionInstitucional) return;
        [$mapa, $filaInicio] = $this->mapaEncabezados($filas, $this->colsSeguimiento);
        if (!isset($mapa['id'])) {
            $resumen['errores'][] = 'Hoja Seguimiento: no se encontró la columna ID. Usa la plantilla exportada.';
            return;
        }
        $editables = ['semestre1_seguimiento', 'semestre2_seguimiento', 'logros', 'limites', 'observacion_estado', 'acciones_fortalecimiento'];

        foreach ($filas as $numFila => $fila) {
            if ($numFila < $filaInicio) continue;
            $excelFila = $numFila + 1;
            $id = isset($mapa['id']) ? (int)trim((string)($fila[$mapa['id']] ?? '')) : 0;
            if ($id <= 0) continue;
            $pref = "Seguimiento, fila {$excelFila}: ";

            $actual = $registros[$id] ?? null;
            if (!$actual) { $resumen['errores'][] = $pref . "el ID {$id} no pertenece a este formulario."; continue; }
            if ((int)$actual['estado_formulacion'] !== 2) { $resumen['errores'][] = $pref . "el ID {$id} aún no tiene la formulación publicada."; continue; }
            if (!$this->puedeEditar($actual)) { $resumen['errores'][] = $pref . "no tienes permiso para editar el ID {$id}."; continue; }
            $seguimientoCerrado = (int)($actual['estado_seguimiento'] ?? 0) !== 0;

            $datos = [];
            $errores = [];
            foreach ($editables as $k) {
                if (!isset($mapa[$k])) continue;
                $v = trim((string)($fila[$mapa[$k]] ?? ''));
                if (in_array($k, ['semestre1_seguimiento', 'semestre2_seguimiento'], true)) {
                    $n = $this->numero($v);
                    if ($v !== '' && $n === null) { $errores[] = "\"" . $this->colsSeguimiento[$k][0] . "\" debe ser un número."; continue; }
                    // En "Límite" el semestre 1 no se registra (igual que en la pantalla)
                    if ($k === 'semestre1_seguimiento' && ($actual['tipo_medicion'] ?? '') === 'Límite') continue;
                    $datos[$k] = $n === null ? '' : (string)$n;
                } else {
                    $datos[$k] = $v;
                }
            }
            if ($errores) { foreach ($errores as $e) $resumen['errores'][] = $pref . $e; continue; }
            if ($this->sinCambios($actual, $datos)) { $resumen['seguimiento']['sin_cambios']++; continue; }
            if ($seguimientoCerrado) {
                $resumen['errores'][] = $pref . "el seguimiento del ID {$id} ya está publicado; sus cambios no se aplicaron.";
                continue;
            }

            if ($this->model->actualizar('seguimiento', $id, $datos)) {
                $resumen['seguimiento']['actualizados']++;
            } else {
                $resumen['errores'][] = $pref . 'no se pudo guardar.';
            }
        }
    }

    // Valida y normaliza una fila de Formulación. Devuelve [datos, errores].
    private function validarFormulacion(callable $val, array $cat, array $formulario) {
        $datos = [];
        $errores = [];

        // Línea → motor → proyecto (deben existir en el catálogo y pertenecer entre sí)
        $linea = null; $motor = null; $proyecto = null;
        $vLinea = $val('linea_estrategica');
        if ($vLinea !== null) {
            if ($vLinea === '') {
                $datos['linea_estrategica'] = '';
            } else {
                $linea = $this->buscar($cat['lineas'], $vLinea, ['nombre', 'codigo']);
                if (!$linea) $errores[] = "la línea estratégica \"{$vLinea}\" no existe en el catálogo.";
                else {
                    $datos['linea_estrategica'] = $linea['nombre'];
                    $objetivo = trim((string)($linea['objetivo'] ?? ''));
                    if ($objetivo === '') {
                        // Línea repetida en el catálogo: se toma el objetivo de la que sí lo tiene
                        foreach ($cat['lineas'] as $otra) {
                            if ($this->normalizar($otra['nombre']) === $this->normalizar($linea['nombre']) && trim((string)($otra['objetivo'] ?? '')) !== '') { $objetivo = trim($otra['objetivo']); break; }
                        }
                    }
                    $datos['objetivo'] = $objetivo;
                }
            }
        }
        $vMotor = $val('motor_desarrollo');
        if ($vMotor !== null && $vMotor !== '') {
            if (!$linea) $errores[] = 'para indicar el motor de desarrollo primero debe ir la línea estratégica.';
            else {
                $motor = $this->buscar($this->model->getMotoresPorLinea($linea['id']), $vMotor, ['nombre', 'codigo']);
                if (!$motor) $errores[] = "el motor \"{$vMotor}\" no pertenece a la línea {$linea['codigo']}.";
                else $datos['motor_desarrollo'] = $motor['nombre'];
            }
        } elseif ($vMotor === '') {
            $datos['motor_desarrollo'] = '';
        }
        $vProy = $val('proyecto');
        if ($vProy !== null && $vProy !== '') {
            if (!$motor) $errores[] = 'para indicar el proyecto primero debe ir el motor de desarrollo.';
            else {
                $proyecto = $this->buscar($this->model->getProyectosPorLineaYMotor($linea['id'], $motor['id']), $vProy, ['nombre', 'codigo']);
                if (!$proyecto) $errores[] = "el proyecto \"{$vProy}\" no pertenece al motor {$motor['codigo']}.";
                else {
                    $datos['proyecto'] = $proyecto['nombre'];
                    // La ponderación del proyecto viene de Configuraciones, igual que en la pantalla
                    $pond = $this->model->getPonderacionProyecto($proyecto['id'], $formulario['anio'] ?? date('Y'));
                    $datos['ponderacion_proyectos'] = $pond === null ? '' : (string)$pond;
                }
            }
        } elseif ($vProy === '') {
            $datos['proyecto'] = '';
        }

        $vEstr = $val('estrategia');
        if ($vEstr !== null) {
            if ($vEstr === '') $datos['estrategia'] = '';
            elseif ($linea) {
                $estr = $this->buscar($this->model->getEstrategiasPorLinea($linea['id']), $vEstr, ['descripcion']);
                if (!$estr) $errores[] = "la estrategia \"" . mb_strimwidth($vEstr, 0, 60, '…') . "\" no pertenece a la línea {$linea['codigo']}.";
                else $datos['estrategia'] = $estr['descripcion'];
            } else {
                $errores[] = 'para indicar la estrategia primero debe ir la línea estratégica.';
            }
        }

        // Textos libres
        foreach (['meta_resultado', 'actividad_proyecto', 'nombre_indicador', 'formula_medicion', 'descripcion_indicador'] as $k) {
            if ($val($k) !== null) $datos[$k] = $val($k);
        }

        // Números
        foreach (['ponderacion_actividades', 'linea_base_meta', 'meta_s1', 'meta_s2'] as $k) {
            $v = $val($k);
            if ($v === null) continue;
            $n = $this->numero($v);
            if ($v !== '' && $n === null) { $errores[] = "\"" . $this->colsFormulacion[$k][0] . "\" debe ser un número."; continue; }
            if ($k === 'ponderacion_actividades' && $n !== null && ($n < 0 || $n > 100)) { $errores[] = 'la ponderación de actividades debe estar entre 0 y 100.'; continue; }
            $datos[$k] = $n === null ? '' : (string)$n;
        }

        // Listas cerradas
        foreach (['frecuencia_medicion' => $this->opcionesFrecuencia, 'unidad_medida' => $this->opcionesUnidad,
                  'tipo_medicion' => $this->opcionesTipo, 'id_indicador' => $cat['id_indicadores']] as $k => $opciones) {
            $v = $val($k);
            if ($v === null) continue;
            if ($v === '') { $datos[$k] = ''; continue; }
            $op = $this->buscarTexto($opciones, $v);
            if ($op === null) $errores[] = "\"" . $this->colsFormulacion[$k][0] . "\" no admite \"{$v}\" (opciones: " . implode(', ', $opciones) . ').';
            else $datos[$k] = $op;
        }

        $vGest = $val('gestionado_facultades');
        if ($vGest !== null) {
            $g = $this->normalizar($vGest);
            if (in_array($g, ['si', 's', '1', 'x', 'true'], true)) $datos['gestionado_facultades'] = 1;
            elseif (in_array($g, ['', 'no', 'n', '0', 'false'], true)) $datos['gestionado_facultades'] = 0;
            else $errores[] = '"GESTIONADO EN FACULTADES" debe ser SI o NO.';
        }

        // Responsables: dependencias del catálogo, máximo 3 (igual que en la pantalla)
        $vResp = $val('responsable_formulacion');
        if ($vResp !== null) {
            $nombres = [];
            $partesResp = strpos($vResp, ';') !== false
                ? array_filter(array_map('trim', explode(';', $vResp)), 'strlen')
                : $this->separarResponsables($vResp);
            foreach ($partesResp as $parte) {
                $dep = $this->buscarTexto($cat['cargos'], $parte);
                if ($dep === null) $errores[] = "la dependencia responsable \"{$parte}\" no existe.";
                else $nombres[] = $dep;
            }
            $nombres = array_values(array_unique($nombres));
            if (count($nombres) > 3) $errores[] = 'máximo 3 dependencias responsables.';
            $datos['responsable_formulacion'] = implode(', ', $nombres);
        }

        $vPlanes = $val('planes_institucionales');
        if ($vPlanes !== null) {
            $planes = [];
            foreach (array_filter(array_map('trim', explode(';', $vPlanes)), 'strlen') as $parte) {
                $plan = $this->buscarTexto($cat['planes'], $parte);
                if ($plan === null) $errores[] = "el plan institucional \"" . mb_strimwidth($parte, 0, 60, '…') . "\" no existe.";
                else $planes[] = $plan;
            }
            $datos['planes_institucionales'] = json_encode(array_values(array_unique($planes)), JSON_UNESCAPED_UNICODE);
        }

        // Valor anual calculado igual que en la pantalla (calcularValorAnual)
        if (array_intersect_key($datos, array_flip(['tipo_medicion', 'linea_base_meta', 'meta_s1', 'meta_s2']))) {
            $datos['_recalcular_anual'] = true;
        }

        return [$datos, $errores];
    }

    // ════════════════════════ AYUDAS ════════════════════════

    private function catalogos() {
        $cargos = array_column($this->model->getCargos(), 'nombre');
        $planes = array_column($this->model->getPlanesInstitucionales(), 'nombre');
        return [
            'lineas'         => $this->model->getLineasEstrategicas(),
            'cargos'         => $cargos,
            'planes'         => $planes,
            'id_indicadores' => $this->model->getIdIndicadores(),
            'facultades'     => $this->model->getFacultades(),
        ];
    }

    // Misma regla que la pantalla para editar: administrador, quien lo creó, o gestor de la dependencia responsable
    private function puedeEditar(array $r) {
        if ($this->esAdmin()) return true;
        $uid = (int)($_SESSION['usuario_id'] ?? 0);
        if ($uid > 0 && $uid === (int)($r['creado_por'] ?? 0)) return true;

        static $nivel = null, $dependencias = null;
        if ($nivel === null) {
            $niveles = ['gestor de metas' => 1, 'gestor de metas de responsable de linea' => 1, 'lider de meta' => 2,
                        'gestor de metas de sub-admin' => 2, 'responsable de linea' => 3, 'sub administrador' => 4];
            $nivel = $niveles[$this->normalizar($_SESSION['usuario_rol'] ?? '')] ?? 0;
            // Dependencia principal y las asociadas en "Responsable de"
            $dependencias = array_map([$this, 'normalizar'],
                $this->model->getNombresDependenciasUsuario($_SESSION['usuario_id'] ?? 0, $_SESSION['usuario_cargo_id'] ?? 0));
        }
        if ($nivel > 1 || !$dependencias) return false;
        foreach ($this->separarResponsables($r['responsable_formulacion'] ?? '') as $resp) {
            if (in_array($this->normalizar($resp), $dependencias, true)) return true;
        }
        return false;
    }

    private function esAdmin() {
        $rol = $this->normalizar($_SESSION['usuario_rol'] ?? '');
        return (int)($_SESSION['usuario_id'] ?? 0) === 1 || in_array($rol, ['admin', 'administrador', 'sub administrador'], true);
    }

    // "responsable_formulacion" guarda nombres separados por ", ", pero algunos nombres llevan coma
    // (ej. "Vice de Invest, Ext y Proy Social"): se reconstruyen contra el catálogo de dependencias
    private function separarResponsables($texto) {
        $texto = trim((string)$texto);
        if ($texto === '') return [];
        static $cargos = null;
        if ($cargos === null) $cargos = array_column($this->model->getCargos(), 'nombre');
        $partes = array_map('trim', explode(',', $texto));
        $resultado = [];
        for ($i = 0; $i < count($partes); $i++) {
            $encontrado = false;
            for ($j = count($partes); $j > $i; $j--) {
                $candidato = implode(', ', array_slice($partes, $i, $j - $i));
                if ($this->buscarTexto($cargos, $candidato) !== null) {
                    $resultado[] = $candidato;
                    $i = $j - 1;
                    $encontrado = true;
                    break;
                }
            }
            if (!$encontrado && $partes[$i] !== '') $resultado[] = $partes[$i];
        }
        return $resultado;
    }

    // Columna de cada clave según el texto del encabezado (sin importar orden, mayúsculas, tildes ni el
    // texto entre paréntesis). El encabezado puede ocupar una fila o dos (grupo arriba, título abajo).
    // Devuelve [mapa clave => columna, número de la primera fila de datos].
    // Fila (0 = primera) donde está el título del N° en la última hoja leída; > 0 = formato institucional con membrete
    private $filaTitulosBase = 0;

    private function mapaEncabezados(array $filas, array $columnas) {
        $porTexto = [];
        foreach ($columnas as $k => $col) $porTexto[$this->normalizarEncabezado($col[0])] = $k;

        // La fila de títulos es la primera de las 20 primeras que trae el título del N° / ID. En la plantilla
        // exportada es la fila 0; en el formato institucional (consolidado) va debajo del membrete.
        $base = 0;
        $tituloId = $this->normalizarEncabezado($columnas['id'][0] ?? 'N°');
        foreach ($filas as $numFila => $fila) {
            if ($numFila >= 20) break;
            foreach ($fila as $texto) {
                if ($this->normalizarEncabezado($texto) === $tituloId) { $base = (int)$numFila; break 2; }
            }
        }
        $this->filaTitulosBase = $base;

        $mapa = [];
        $filaInicio = $base + 1;
        foreach ([$base + 1, $base] as $numFila) { // primero la fila de abajo (títulos bajo un grupo)
            foreach ($filas[$numFila] ?? [] as $col => $texto) {
                $k = $porTexto[$this->normalizarEncabezado($texto)] ?? null;
                if ($k === null || isset($mapa[$k]) || in_array($col, $mapa, true)) continue;
                $mapa[$k] = $col;
                if ($numFila === $base + 1) $filaInicio = $base + 2;
            }
        }
        return [$mapa, $filaInicio];
    }

    // Columnas en el formato que espera ExcelLibro. En "ACTIVIDAD DEL PROYECTO" va el total entre paréntesis.
    private function columnasExcel(array $columnas, $totalActividades) {
        $resultado = [];
        foreach ($columnas as $k => $col) {
            $titulo = $col[0];
            if ($k === 'actividad_proyecto' && $totalActividades > 0 && strpos($titulo, '(') === false) $titulo .= ' (' . $totalActividades . ')';
            $resultado[] = ['titulo' => $titulo, 'ancho' => $col[1]] + ($col[2] ?? []);
        }
        return $resultado;
    }

    // Cumplido del año según el tipo de medición (mismo cálculo que la lista del Módulo 144)
    private function cumplidoAnual(array $r) {
        $lb = $this->numero($r['linea_base_meta'] ?? '');
        $s1 = $this->numero($r['semestre1_seguimiento'] ?? '');
        $s2 = $this->numero($r['semestre2_seguimiento'] ?? '');
        if ($s1 === null && $s2 === null) return '';
        switch ($r['tipo_medicion'] ?? '') {
            case 'Acumulado': return ($lb ?? 0) + ($s1 ?? 0) + ($s2 ?? 0);
            case 'Nuevo gestionado durante la vigencia': return ($s1 ?? 0) + ($s2 ?? 0);
            case 'Promedio':
                $vals = array_filter([$s1, $s2], function ($v) { return $v !== null; });
                return array_sum($vals) / count($vals);
            case 'Último valor reportado': return $s2 ?? $s1;
            case 'Límite': return $s2 ?? '';
            default: return '';
        }
    }

    // Columnas de Formulación: las fijas + una por plan institucional + una por facultad
    private function columnasFormulacion(array $catalogos, $nActividades) {
        $columnas = $this->colsFormulacion;
        if ($nActividades > 0) $columnas['actividad_proyecto'][0] .= ' (' . $nActividades . ')';
        foreach ($catalogos['planes'] as $plan) {
            $columnas['plan:' . $plan] = [$plan, 18, ['grupo' => self::GRUPO_PLANES]];
        }
        foreach ($catalogos['facultades'] as $fac) {
            $columnas['fac:' . (int)$fac['id']] = [$fac['nombre'], 16, ['grupo' => self::GRUPO_FACULTADES]];
        }
        return $columnas;
    }

    private function marcado($v) {
        $v = $this->normalizar($v);
        return $v !== '' && !in_array($v, ['no', 'n', '0', 'false'], true);
    }

    // Lee las columnas de facultades de una fila: X = participa (conserva su distribución actual o 1),
    // un número = esa distribución, vacío = no participa. Devuelve [[facultad_id => distribución], errores].
    private function leerFacultades(array $columnasFac, callable $celda, $id) {
        $actual = $id > 0 ? $this->distribucionActual($id) : [];
        $dist = [];
        $errores = [];
        foreach ($columnasFac as $k) {
            $facId = (int)substr($k, 4);
            $v = $celda($k);
            if (!$this->marcado($v)) { $dist[$facId] = 0.0; continue; }
            $n = $this->numero($v);
            if ($n !== null) {
                if ($n < 0) { $errores[] = 'la distribución por facultad no puede ser negativa.'; continue; }
                $dist[$facId] = $n;
            } else {
                $previo = (float)($actual[$facId]['distribucion'] ?? 0);
                $dist[$facId] = $previo > 0 ? $previo : 1.0;
            }
        }
        return [$dist, $errores];
    }

    private function distribucionActual($id) {
        $actual = [];
        foreach ($this->model->getDistribucionFacultades($id) as $d) $actual[(int)$d['facultad_id']] = $d;
        return $actual;
    }

    private function distribucionCambia($id, array $nueva) {
        $actual = $id > 0 ? $this->distribucionActual($id) : [];
        foreach ($nueva as $facId => $valor) {
            if (abs((float)($actual[$facId]['distribucion'] ?? 0) - $valor) > 0.0001) return true;
        }
        return false;
    }

    private function guardarDistribucion($id, array $nueva) {
        $actual = $this->distribucionActual($id);
        foreach ($nueva as $facId => $valor) {
            $previo = (float)($actual[$facId]['distribucion'] ?? 0);
            if (abs($previo - $valor) <= 0.0001) continue;
            if ($valor <= 0 && !isset($actual[$facId])) continue;
            $this->model->guardarDistribucionFacultad($id, $facId, $actual[$facId]['observacion'] ?? '', $valor);
        }
    }

    private function normalizarEncabezado($texto) {
        return trim(preg_replace('/\s+/', ' ', preg_replace('/\(.*?\)/', '', $this->normalizar($texto))));
    }

    private function buscarHoja(array $libro, $nombre) {
        foreach ($libro as $hoja => $filas) {
            if ($this->normalizar($hoja) === $this->normalizar($nombre)) return $filas;
        }
        return null;
    }

    // Busca en una lista de filas por código o nombre (sin mayúsculas ni tildes). Acepta también "L1 - Nombre".
    private function buscar(array $lista, $valor, array $campos) {
        $v = $this->normalizar($valor);
        $sinCodigo = $this->normalizar(preg_replace('/^\s*[A-Za-z]{1,3}\d+\s*[-–.]\s*/u', '', $valor));
        foreach ($lista as $item) {
            foreach ($campos as $c) {
                $n = $this->normalizar($item[$c] ?? '');
                if ($n !== '' && ($n === $v || $n === $sinCodigo)) return $item;
            }
        }
        return null;
    }

    private function buscarTexto(array $opciones, $valor) {
        $v = $this->normalizar($valor);
        foreach ($opciones as $op) {
            if ($this->normalizar($op) === $v) return $op;
        }
        return null;
    }

    // El valor anual (anio_base_meta) se recalcula con los datos finales del registro, como en la pantalla
    private function calcularValorAnual(array $actual, array &$datos) {
        if (empty($datos['_recalcular_anual'])) return;
        unset($datos['_recalcular_anual']);
        $final = array_merge($actual, $datos);
        $datos['anio_base_meta'] = $this->valorAnual($final['tipo_medicion'] ?? '', $final['linea_base_meta'] ?? '', $final['meta_s1'] ?? '', $final['meta_s2'] ?? '');
    }

    private function sinCambios(array $actual, array $datos) {
        foreach ($datos as $k => $v) {
            $a = $actual[$k] ?? '';
            if (is_numeric($a) && is_numeric($v) && (float)$a == (float)$v) continue;
            if ((string)$a !== (string)$v) return false;
        }
        return true;
    }

    private function valorAnual($tipo, $lb, $s1, $s2) {
        $lb = $this->numero($lb); $s1 = $this->numero($s1); $s2 = $this->numero($s2);
        switch ($tipo) {
            case 'Acumulado': return number_format(($lb ?? 0) + ($s1 ?? 0) + ($s2 ?? 0), 2, '.', '');
            case 'Nuevo gestionado durante la vigencia': return number_format(($s1 ?? 0) + ($s2 ?? 0), 2, '.', '');
            case 'Promedio':
                $vals = array_filter([$s1, $s2], function ($v) { return $v !== null; });
                return $vals ? number_format(array_sum($vals) / count($vals), 2, '.', '') : '0.00';
            case 'Último valor reportado':
                $u = $s2 ?? $s1 ?? $lb;
                return $u === null ? '' : number_format($u, 2, '.', '');
            case 'Límite': return $s2 === null ? '' : number_format($s2, 2, '.', '');
            default: return '';
        }
    }

    private function numero($v) {
        $v = trim((string)$v);
        if ($v === '') return null;
        $v = str_replace([' ', '%'], '', $v);
        // "1.234,5" → 1234.5 ; "3,5" → 3.5
        if (strpos($v, ',') !== false && strpos($v, '.') !== false) $v = str_replace('.', '', $v);
        $v = str_replace(',', '.', $v);
        return is_numeric($v) ? (float)$v : null;
    }

    private function normalizar($s) {
        $s = mb_strtolower(trim((string)$s), 'UTF-8');
        $s = strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n']);
        return preg_replace('/\s+/', ' ', $s);
    }
}
