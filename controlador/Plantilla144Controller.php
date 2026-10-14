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

    // clave => [encabezado en el Excel, ancho]
    private $colsFormulacion = [
        'id'                      => ['ID (no modificar · vacío = nuevo)', 14],
        'estado'                  => ['ESTADO (solo lectura)', 14],
        'linea_estrategica'       => ['LÍNEA ESTRATÉGICA', 34],
        'estrategia'              => ['ESTRATEGIA', 34],
        'motor_desarrollo'        => ['MOTOR DE DESARROLLO', 34],
        'proyecto'                => ['PROYECTO', 34],
        'meta_resultado'          => ['META DE RESULTADO', 34],
        'actividad_proyecto'      => ['ACTIVIDAD DEL PROYECTO', 34],
        'ponderacion_actividades' => ['PONDERACIÓN ACTIVIDADES (%)', 16],
        'responsable_formulacion' => ['RESPONSABLE (dependencias separadas por ;)', 34],
        'id_indicador'            => ['ID INDICADOR', 14],
        'gestionado_facultades'   => ['GESTIONADO EN FACULTADES (SI/NO)', 16],
        'nombre_indicador'        => ['NOMBRE DEL INDICADOR', 34],
        'formula_medicion'        => ['FÓRMULA DEL INDICADOR', 34],
        'descripcion_indicador'   => ['DESCRIPCIÓN DEL INDICADOR', 34],
        'frecuencia_medicion'     => ['FRECUENCIA DE MEDICIÓN', 18],
        'unidad_medida'           => ['UNIDAD DE MEDIDA', 16],
        'tipo_medicion'           => ['TIPO DE MEDICIÓN', 26],
        'linea_base_meta'         => ['LÍNEA BASE', 12],
        'meta_s1'                 => ['META SEMESTRE 1', 14],
        'meta_s2'                 => ['META SEMESTRE 2', 14],
        'planes_institucionales'  => ['PLANES INSTITUCIONALES (separados por ;)', 34],
    ];

    private $colsSeguimiento = [
        'id'                       => ['ID (no modificar)', 12],
        'ref_indicador'            => ['INDICADOR (referencia)', 40],
        'ref_lmp'                  => ['L - M - P (referencia)', 16],
        'ref_tipo'                 => ['TIPO DE MEDICIÓN (referencia)', 24],
        'semestre1_seguimiento'    => ['SEGUIMIENTO SEMESTRE 1', 16],
        'semestre2_seguimiento'    => ['SEGUIMIENTO SEMESTRE 2', 16],
        'logros'                   => ['LOGROS', 34],
        'limites'                  => ['LÍMITES', 34],
        'observacion_estado'       => ['OBSERVACIÓN', 34],
        'acciones_fortalecimiento' => ['ACCIONES DE FORTALECIMIENTO', 34],
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

        $filasF = [];
        $filasS = [];
        foreach ($this->model->getRegistrosParaPlantilla((int)$formulario['id']) as $r) {
            if (!$this->puedeEditar($r)) continue;

            $estadoF = (int)$r['estado_formulacion'] === 2 ? 'Publicado' : 'Borrador';
            $planes = json_decode((string)($r['planes_institucionales'] ?? ''), true);
            $fila = [];
            foreach (array_keys($this->colsFormulacion) as $k) {
                switch ($k) {
                    case 'estado': $fila[] = $estadoF; break;
                    case 'gestionado_facultades': $fila[] = (int)($r[$k] ?? 0) === 1 ? 'SI' : 'NO'; break;
                    case 'responsable_formulacion': $fila[] = implode('; ', $this->separarResponsables($r[$k] ?? '')); break;
                    case 'planes_institucionales': $fila[] = is_array($planes) ? implode('; ', $planes) : ''; break;
                    default: $fila[] = $r[$k] ?? '';
                }
            }
            $filasF[] = $fila;

            // En Seguimiento solo van las formulaciones publicadas
            if ((int)$r['estado_formulacion'] === 2) {
                $lmp = implode(' - ', array_filter([$r['linea_codigo'] ?? '', $r['motor_codigo'] ?? '', $r['proyecto_codigo'] ?? '']));
                $filaS = [];
                foreach (array_keys($this->colsSeguimiento) as $k) {
                    switch ($k) {
                        case 'ref_indicador': $filaS[] = trim((string)($r['formula_medicion'] ?? '')) !== '' ? $r['formula_medicion'] : ($r['nombre_borrador'] ?? ''); break;
                        case 'ref_lmp': $filaS[] = $lmp; break;
                        case 'ref_tipo': $filaS[] = $r['tipo_medicion'] ?? ''; break;
                        default: $filaS[] = $r[$k] ?? '';
                    }
                }
                $filasS[] = $filaS;
            }
        }
        for ($i = 0; $i < self::FILAS_VACIAS; $i++) $filasF[] = [];

        $nombre = 'plantilla_plan_accion_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $formulario['titulo'] ?? 'formulario') . '_' . date('Y-m-d');
        ExcelLibro::descargar($nombre, [
            [
                'nombre'      => self::HOJA_FORMULACION,
                'encabezados' => array_column($this->colsFormulacion, 0),
                'anchos'      => array_column($this->colsFormulacion, 1),
                'filas'       => $filasF,
            ],
            [
                'nombre'      => self::HOJA_SEGUIMIENTO,
                'encabezados' => array_column($this->colsSeguimiento, 0),
                'anchos'      => array_column($this->colsSeguimiento, 1),
                'filas'       => $filasS,
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
        $mapa = $this->mapaEncabezados($filas, $this->colsFormulacion);
        if (!isset($mapa['id'])) {
            $resumen['errores'][] = 'Hoja Formulación: no se encontró la columna ID. Usa la plantilla exportada.';
            return;
        }
        $catalogos = $this->catalogos();

        foreach ($filas as $numFila => $fila) {
            if ($numFila === 0) continue; // encabezados
            $excelFila = $numFila + 1;
            $val = function ($k) use ($mapa, $fila) {
                return isset($mapa[$k]) ? trim((string)($fila[$mapa[$k]] ?? '')) : null;
            };

            // Fila sin datos editables: se ignora
            $tieneDatos = false;
            foreach (array_keys($this->colsFormulacion) as $k) {
                if (!in_array($k, ['id', 'estado'], true) && $val($k) !== null && $val($k) !== '') { $tieneDatos = true; break; }
            }
            $id = (int)$val('id');
            if (!$tieneDatos && $id <= 0) continue;

            $pref = "Formulación, fila {$excelFila}: ";
            $actual = [];
            if ($id > 0) {
                $actual = $registros[$id] ?? null;
                if (!$actual) { $resumen['errores'][] = $pref . "el ID {$id} no pertenece a este formulario."; continue; }
                if (!$this->puedeEditar($actual)) { $resumen['errores'][] = $pref . "no tienes permiso para editar el ID {$id}."; continue; }
            }

            [$datos, $errores] = $this->validarFormulacion($val, $catalogos, $formulario);
            if ($errores) {
                foreach ($errores as $e) $resumen['errores'][] = $pref . $e;
                continue;
            }
            $this->calcularValorAnual($actual, $datos);

            // Publicado: no se modifica desde la plantilla (primero hay que devolverlo a borrador)
            if ($id > 0 && (int)$actual['estado_formulacion'] !== 0) {
                if ($this->sinCambios($actual, $datos)) $resumen['formulacion']['sin_cambios']++;
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
                $registros[$nuevoId] = ['id' => $nuevoId, 'estado_formulacion' => 0, 'creado_por' => $_SESSION['usuario_id'] ?? 0] + $datos;
                $resumen['formulacion']['creados']++;
            } else {
                if ($this->sinCambios($registros[$id], $datos)) { $resumen['formulacion']['sin_cambios']++; continue; }
                if ($this->model->actualizar('formulacion', $id, $datos)) {
                    $registros[$id] = array_merge($registros[$id], $datos);
                    $resumen['formulacion']['actualizados']++;
                } else {
                    $resumen['errores'][] = $pref . 'no se pudo guardar.';
                }
            }
        }
    }

    private function importarSeguimiento(array $filas, array $registros, array &$resumen) {
        $mapa = $this->mapaEncabezados($filas, $this->colsSeguimiento);
        if (!isset($mapa['id'])) {
            $resumen['errores'][] = 'Hoja Seguimiento: no se encontró la columna ID. Usa la plantilla exportada.';
            return;
        }
        $editables = ['semestre1_seguimiento', 'semestre2_seguimiento', 'logros', 'limites', 'observacion_estado', 'acciones_fortalecimiento'];

        foreach ($filas as $numFila => $fila) {
            if ($numFila === 0) continue;
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
                    $datos['objetivo'] = $linea['objetivo'] ?? '';
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
            foreach (array_filter(array_map('trim', explode(';', $vResp)), 'strlen') as $parte) {
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
        ];
    }

    // Misma regla que la pantalla para editar: administrador, quien lo creó, o gestor de la dependencia responsable
    private function puedeEditar(array $r) {
        if ($this->esAdmin()) return true;
        $uid = (int)($_SESSION['usuario_id'] ?? 0);
        if ($uid > 0 && $uid === (int)($r['creado_por'] ?? 0)) return true;

        static $nivel = null, $cargo = null;
        if ($nivel === null) {
            $niveles = ['gestor de metas' => 1, 'gestor de metas de responsable de linea' => 1, 'lider de meta' => 2,
                        'gestor de metas de sub-admin' => 2, 'responsable de linea' => 3, 'sub administrador' => 4];
            $nivel = $niveles[$this->normalizar($_SESSION['usuario_rol'] ?? '')] ?? 0;
            $cargo = $this->normalizar($this->model->getCargoNombre($_SESSION['usuario_cargo_id'] ?? 0));
        }
        if ($nivel > 1 || $cargo === '') return false;
        foreach ($this->separarResponsables($r['responsable_formulacion'] ?? '') as $resp) {
            if ($this->normalizar($resp) === $cargo) return true;
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

    // Columna de cada clave según el texto del encabezado (sin importar orden, mayúsculas, tildes ni el texto entre paréntesis)
    private function mapaEncabezados(array $filas, array $columnas) {
        $encabezados = $filas[0] ?? [];
        $porTexto = [];
        foreach ($columnas as $k => [$titulo]) $porTexto[$this->normalizarEncabezado($titulo)] = $k;
        $mapa = [];
        foreach ($encabezados as $col => $texto) {
            $k = $porTexto[$this->normalizarEncabezado($texto)] ?? null;
            if ($k !== null && !isset($mapa[$k])) $mapa[$k] = $col;
        }
        return $mapa;
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
