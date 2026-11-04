<?php
// vista/FOR-DE-144/reporte_seguimiento.php — estado de los indicadores del Plan de Acción AL TRIMESTRE elegido.
// El seguimiento se aprueba por trimestre: aquí «Aprobado» es el trimestre aprobado por la Oficina de Planeación.
if (!isset($formulario)) {
    header('Location: ' . Config::getBasePath() . '/FOR-DE-144');
    exit;
}
$h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$basePath = Config::getBasePath();
$indicadores = $reporte['indicadores'] ?? [];
$facultadesRep = $reporte['facultades'] ?? [];
$tituloF = $h($formulario['titulo'] ?? '');
$anio    = $h($formulario['anio'] ?? date('Y'));
$tituloAnio = (strpos($tituloF, (string)$anio) !== false) ? $tituloF : $tituloF . ' (' . $anio . ')'; // si el título ya trae el año, no se repite
$trimestre = isset($trimestre) ? max(1, min(4, (int)$trimestre)) : 1;

$etapas = [1 => 'Gestor de Metas', 2 => 'Líder de Metas', 3 => 'Vicerrectoría', 4 => 'Oficina de Planeación'];
$tieneValor = function ($v) { return $v !== null && trim((string)$v) !== ''; };

$filas = [];
$kpi = ['total' => 0, 'publicados' => 0, 'revision' => 0, 'devueltos' => 0, 'construccion' => 0, 'sinreportar' => 0];
$porFacultad = []; // id => ['nombre', 'total', y un conteo por estado]
foreach ($indicadores as $r) {
    $esLimite = ($r['tipo_medicion'] ?? '') === 'Límite';
    if ($esLimite && $trimestre <= 2) continue; // en «Límite» el semestre 1 (T1 y T2) no se registra: no entra en este trimestre

    // Estado de cada trimestre del indicador: ap = aprobado, rev = en revisión, dev = devuelto, con = en construcción, na = no aplica
    $tri = [];
    foreach ([1, 2, 3, 4] as $n) {
        $e = (int)($r['t' . $n . '_estado'] ?? 0); $s = (int)($r['t' . $n . '_sol'] ?? 0);
        $v = $r['trimestre' . $n . '_seguimiento'] ?? null;
        $tri[$n] = [
            'v' => $tieneValor($v) ? $v : null,
            'e' => ($esLimite && $n <= 2) ? 'na' : ($e === 2 ? 'ap' : ($s === 1 ? 'rev' : ($s === 2 ? 'dev' : 'con'))),
        ];
    }
    $etapa = (int)($r['t' . $trimestre . '_etapa'] ?? 0);
    $conValor = $tri[$trimestre]['v'] !== null;

    switch ($tri[$trimestre]['e']) {
        case 'ap':  $estado = 'publicado';    $estadoTxt = 'Aprobado';                          $kpi['publicados']++;   break;
        case 'rev': $estado = 'revision';     $estadoTxt = 'En revisión · ' . ($etapas[min(4, $etapa + 1)] ?? ''); $kpi['revision']++; break;
        case 'dev': $estado = 'devuelto';     $estadoTxt = 'Devuelto para ajustes';             $kpi['devueltos']++;    break;
        default:    if ($conValor) { $estado = 'construccion'; $estadoTxt = 'En construcción'; $kpi['construccion']++; }
                    else { $estado = 'sinreportar'; $estadoTxt = 'Sin reportar'; $kpi['sinreportar']++; }
    }
    $kpi['total']++;

    $nombre = trim((string)($r['formula_medicion'] ?? '')) !== '' ? $r['formula_medicion'] : ($r['nombre_indicador'] ?: $r['nombre_borrador']);
    $filas[] = [
        'id' => (int)$r['id'], 'nombre' => $nombre,
        'lmp' => implode(' · ', array_filter([$r['linea_codigo'], $r['motor_codigo'], $r['proyecto_codigo']])),
        'responsable' => $r['responsable_formulacion'] ?? '', 'tri' => $tri,
        'estado' => $estado, 'estadoTxt' => $estadoTxt, 'revisa' => $estado === 'revision' ? min(4, $etapa + 1) : 0,
        'unidad' => $r['unidad_medida'] ?? '',
    ];
    // Por facultad: concertación con la vicerrectoría del semestre al que pertenece el trimestre elegido.
    //  conc  = la vicerrectoría puso su valor y se dio clic en «Concertar»
    //  espera = la facultad puso un valor y todavía no se ha concertado
    //  nada  = la facultad no ha puesto ningún valor
    $semConc = $trimestre <= 2 ? 1 : 2;
    if (!($esLimite && $semConc === 1)) { // en «Límite» el semestre 1 no se registra
        $pc = ($r['unidad_medida'] ?? '') === 'Porcentaje' ? '%' : '';
        foreach ($facultadesRep[(int)$r['id']] ?? [] as $fx) {
            $c = $concertaciones[(int)$r['id']][(int)$fx['id']][$semConc] ?? null;
            $vf = $c['vf'] ?? null; $vv = $c['vv'] ?? null;
            // Concertado: la vicerrectoría puso su valor y se dio clic en «Concertar» (queda enviada o aprobada)
            if ($c && ($c['aprobado'] || ($vv !== null && $c['enviado']))) $ec = 'conc';
            elseif ($vf !== null) $ec = 'espera';
            else $ec = 'nada';
            if (!isset($porFacultad[$fx['id']])) $porFacultad[$fx['id']] = ['nombre' => $fx['nombre'], 'total' => 0, 'conc' => 0, 'espera' => 0, 'nada' => 0, 'items' => []];
            $porFacultad[$fx['id']]['total']++;
            $porFacultad[$fx['id']][$ec]++;
            $porFacultad[$fx['id']]['items'][] = [
                'nombre' => $nombre, 'lmp' => implode(' · ', array_filter([$r['linea_codigo'], $r['motor_codigo'], $r['proyecto_codigo']])),
                'vf' => $vf === null ? '—' : rtrim(rtrim(number_format($vf, 2, ',', '.'), '0'), ',') . $pc,
                'vv' => $vv === null ? '—' : rtrim(rtrim(number_format($vv, 2, ',', '.'), '0'), ',') . $pc,
                'ec' => $ec,
            ];
        }
    }
}
uasort($porFacultad, function ($a, $b) { return strcoll($a['nombre'], $b['nombre']); });
$semConc = $trimestre <= 2 ? 1 : 2;
// Dependencias responsables (un indicador puede tener varias separadas por coma o punto y coma)
$dependencias = [];
foreach ($filas as $i => $f) {
    $lista = array_values(array_unique(array_filter(array_map('trim', preg_split('/\s*[;,\/\n]\s*/u', (string)$f['responsable'])))));
    $filas[$i]['deps'] = $lista;
    foreach ($lista as $d) $dependencias[mb_strtolower($d, 'UTF-8')] = $d;
}
uasort($dependencias, function ($a, $b) { return strcoll($a, $b); });
$porDep = [];
foreach ($filas as $i => $f) {
    foreach (array_unique($f['deps']) as $d) {
        $k = mb_strtolower($d, 'UTF-8');
        if (!isset($porDep[$k])) $porDep[$k] = ['nombre' => $d, 'total' => 0, 'publicado' => 0, 'revision' => 0, 'devuelto' => 0, 'construccion' => 0, 'sinreportar' => 0, 'idx' => []];
        $porDep[$k]['total']++; $porDep[$k][$f['estado']]++; $porDep[$k]['idx'][] = $i;
    }
}
uasort($porDep, function ($a, $b) { return strcoll($a['nombre'], $b['nombre']); });
$pctDe = function ($n, $t) { return $t > 0 ? round($n / $t * 100) : 0; };
$fmt = function ($v) { return rtrim(rtrim(number_format((float)$v, 2, ',', '.'), '0'), ','); };
$urlTrim = function ($t) use ($basePath, $formulario) { return $basePath . '/FOR-DE-144?action=reporteSeguimiento&id=' . (int)$formulario['id'] . '&t=' . (int)$t; };
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reporte de seguimiento · <?php echo $tituloAnio; ?></title>
<style>
:root{
    --navy:#143163; --navy-d:#0d2147; --blue:#1D71B8; --orange:#D85819; --amber:#F9B233; --grey:#706F6F;
    --bg:#f3f5f9; --panel:#fff; --fg:#17203a; --muted:#5b6479; --line:#dde2ec;
    --soft:#e7ecf5; --blue-soft:#e4eef8; --amber-soft:#fdf1d6; --amber-fg:#8a5a00; --ok:#1f8a4c; --ok-soft:#e3f4ea;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;background:var(--bg);color:var(--fg);font-size:14px;-webkit-font-smoothing:antialiased}
a{color:inherit}
.wrap{max-width:1280px;margin:0 auto;padding:20px 16px 64px;display:grid;gap:16px}
.hero{background:linear-gradient(135deg,var(--navy) 0%,#1D4E8F 100%);color:#fff;border-radius:18px;padding:22px 24px;display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;align-items:flex-end}
.hero small{opacity:.8;letter-spacing:.08em;text-transform:uppercase;font-size:11px;font-weight:700}
.hero h1{font-size:26px;margin-top:4px;line-height:1.2}
.hero p{opacity:.85;margin-top:4px;font-size:13px}
.hero-acc{display:flex;gap:8px;flex-wrap:wrap}
.btn{display:inline-flex;align-items:center;gap:6px;border:1px solid rgba(255,255,255,.4);color:#fff;background:rgba(255,255,255,.12);padding:8px 14px;border-radius:9px;font-weight:600;font-size:13px;text-decoration:none}
.btn:hover{background:rgba(255,255,255,.22)}
.kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px}
.kpi{background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:14px 16px;border-top:4px solid var(--navy)}
.kpi.o{border-top-color:var(--orange)}.kpi.b{border-top-color:var(--blue)}.kpi.a{border-top-color:var(--amber)}.kpi.g{border-top-color:var(--grey)}
.kpi small{color:var(--muted);font-weight:600}
.kpi .v{font-size:30px;font-weight:800;margin-top:2px;font-variant-numeric:tabular-nums}
.kpi .s{color:var(--muted);font-size:12px;margin-top:2px}
.grid2{display:grid;grid-template-columns:minmax(0,3fr) minmax(0,2fr);gap:16px}
.box{background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:16px 18px;min-width:0}
.box h2{font-size:14px;letter-spacing:.04em;text-transform:uppercase;color:var(--navy);margin-bottom:12px}
.bar-row{display:grid;grid-template-columns:minmax(120px,190px) minmax(0,1fr) 54px;gap:10px;align-items:center;margin-bottom:11px;font-size:13px}
.bar-row .n{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.bar-row .n small{color:var(--muted)}
.trk{height:12px;background:var(--soft);border-radius:99px;position:relative}
.trk i{display:block;height:100%;border-radius:99px;background:var(--blue)}
.trk i.cam{background:var(--amber)}
.trk::after{content:"";position:absolute;left:80%;top:-4px;bottom:-4px;width:2px;background:var(--orange)}
.tsel{display:flex;gap:4px;background:rgba(255,255,255,.12);border-radius:10px;padding:3px}
.tsel a{color:#fff;text-decoration:none;font-weight:700;font-size:13px;padding:5px 11px;border-radius:8px;opacity:.8}
.tsel a:hover{opacity:1}.tsel a.on{background:#fff;color:var(--navy);opacity:1}
.tc{font-variant-numeric:tabular-nums;font-weight:700;white-space:nowrap}
.tc i{display:inline-block;width:8px;height:8px;border-radius:50%;margin-right:5px;background:#c3c9d6}
.tc.ap i{background:var(--ok)}.tc.rev i{background:var(--amber)}.tc.dev i{background:var(--blue)}
.tc.na{color:var(--muted);font-weight:500}.tc.na i{display:none}
.bar-row b{text-align:right;font-variant-numeric:tabular-nums}
.leg{display:flex;gap:14px;flex-wrap:wrap;color:var(--muted);font-size:12px;margin-top:6px}
.leg i{display:inline-block;width:10px;height:10px;border-radius:3px;margin-right:5px;vertical-align:-1px}
.sem{display:grid;gap:12px}
.sem-row .t{display:flex;justify-content:space-between;font-weight:600;margin-bottom:5px}
.sem-row .t span{color:var(--muted);font-weight:400}
.est{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}
.chip{padding:5px 12px;border-radius:99px;font-size:12px;font-weight:700}
.c-publicado{background:var(--ok-soft);color:var(--ok)}.c-revision{background:var(--amber-soft);color:var(--amber-fg)}
.c-devuelto{background:var(--blue-soft);color:var(--blue)}.c-construccion{background:var(--soft);color:var(--muted)}
.c-sinreportar{background:#fff;color:var(--muted);box-shadow:inset 0 0 0 1px #c3c9d6}
.tools{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:12px}
.tools input,.tools select{font:inherit;border:1px solid var(--line);border-radius:9px;padding:8px 11px;min-height:38px;background:#fff;color:var(--fg);min-width:0}
.tools input{flex:1 1 240px}
.tw{overflow-x:auto;border:1px solid var(--line);border-radius:12px}
table{border-collapse:collapse;width:100%;min-width:980px}
th,td{padding:10px 12px;border-bottom:1px solid var(--line);text-align:left;vertical-align:middle}
th{background:var(--soft);font-size:11px;letter-spacing:.05em;text-transform:uppercase;color:var(--muted);position:sticky;top:0}
tr:last-child td{border-bottom:0}
.nm b{display:block;font-weight:600;line-height:1.3}.nm small{color:var(--muted)}
.lmp{display:inline-block;background:var(--blue-soft);color:var(--blue);font-weight:700;font-size:12px;border-radius:99px;padding:3px 10px;white-space:nowrap}
.sv{font-variant-numeric:tabular-nums;font-weight:700}.sv small{display:block;color:var(--muted);font-weight:400}
.nd{color:var(--muted)}
.ev{display:inline-flex;gap:6px}.ev span{background:var(--blue-soft);color:var(--blue);border-radius:99px;padding:2px 8px;font-size:12px;font-weight:700}.ev span.z{background:var(--soft);color:var(--muted);font-weight:500}
.pct{display:grid;grid-template-columns:70px 44px;gap:8px;align-items:center;font-variant-numeric:tabular-nums;font-weight:700}
.sem-circ{display:inline-flex;gap:3px}.sem-circ i{font-style:normal;width:22px;height:22px;border-radius:50%;background:var(--soft);color:var(--muted);font-size:11px;font-weight:700;display:grid;place-items:center}.sem-circ i.on{background:var(--blue);color:#fff}
.vacio{text-align:center;color:var(--muted);padding:26px}
.pie{color:var(--muted);font-size:12px;text-align:center}
@media (max-width:900px){.grid2{grid-template-columns:minmax(0,1fr)}.bar-row{grid-template-columns:minmax(90px,130px) minmax(0,1fr) 48px}.hero h1{font-size:21px}}
@media print{.tools,.tabs,.hero-acc,.kpis,.pie{display:none!important}body{background:#fff}.hero{background:none!important;color:var(--fg);padding:0}.hero small,.hero p{color:var(--muted)}.box{border:0;padding:0}.acc{break-inside:avoid}.tp[hidden]{display:none!important}}
.fbar{margin-left:auto;display:flex;gap:8px;align-items:center;padding-bottom:6px}
.fbar[hidden],.acc-chips .chip[hidden]{display:none}
#dlgPdf{margin:auto;border:0;border-radius:16px;padding:22px 24px;max-width:420px;width:calc(100% - 32px);box-shadow:0 20px 60px rgba(20,49,99,.35);color:var(--fg)}
#dlgPdf::backdrop{background:rgba(13,33,71,.45)}
#dlgPdf h3{font-size:17px;color:var(--navy);margin-bottom:6px}
#dlgPdf p{color:var(--muted);font-size:13px;margin-bottom:16px}
#dlgPdf .opc{display:grid;gap:10px}
#dlgPdf .opc button{font:inherit;text-align:left;border:1px solid var(--line);background:#fff;border-radius:12px;padding:12px 14px;cursor:pointer}
#dlgPdf .opc button:hover{border-color:var(--blue);background:var(--blue-soft)}
#dlgPdf .opc b{display:block;color:var(--navy)}#dlgPdf .opc span{color:var(--muted);font-size:12px}
#dlgPdf .cancelar{margin-top:12px;border:0;background:none;color:var(--muted);font:inherit;cursor:pointer;float:right}
@media print{#dlgPdf{display:none!important}}
.fbar select,.fbar button{font:inherit;border:1px solid var(--line);border-radius:9px;padding:7px 11px;min-height:36px;background:#fff;color:var(--fg);cursor:pointer}
.fbar button{font-weight:700;color:var(--navy)}.fbar button:hover{border-color:var(--blue)}
.tabs .fbar button{border-bottom:1px solid var(--line);margin-bottom:0;padding:7px 11px}
.fc{white-space:nowrap}.fcn{display:block;font-size:.82rem;color:var(--fg)}.fcn b{color:var(--navy);margin-right:4px}
.tp[hidden]{display:none!important}
.kpi-btn{font:inherit;color:inherit;text-align:left;cursor:pointer;width:100%}
.kpi-btn:hover{border-color:var(--blue);box-shadow:0 2px 10px rgba(20,49,99,.12)}
.kpi-btn:focus-visible{outline:2px solid var(--blue);outline-offset:2px}
.kpi-btn[aria-pressed="true"]{border-color:var(--orange);box-shadow:0 0 0 2px rgba(216,88,25,.25)}
.tabs{display:flex;gap:6px;flex-wrap:wrap;margin:0 0 14px;border-bottom:2px solid var(--line)}
.tabs button{border:0;background:none;padding:10px 16px;font:inherit;font-weight:700;color:var(--muted);cursor:pointer;border-bottom:3px solid transparent;margin-bottom:-2px}
.tabs button span{display:inline-block;min-width:20px;padding:0 6px;border-radius:10px;background:#e8ebf3;color:var(--navy);font-size:.75rem;text-align:center;margin-left:4px}
.tabs button.on{color:var(--navy);border-bottom-color:var(--orange)}
.acc{border:1px solid var(--line);border-radius:10px;margin-bottom:8px;background:#fff}
.acc summary{display:grid;grid-template-columns:minmax(160px,1.2fr) minmax(170px,.9fr) minmax(80px,1fr) auto;gap:12px;align-items:center;padding:12px 14px 12px 28px;cursor:pointer;list-style:none;position:relative}
.acc summary::-webkit-details-marker{display:none}
.acc summary::before{content:'▸';position:absolute;left:11px;color:var(--muted)}
.acc[open] summary::before{content:'▾'}
.acc[open] summary{border-bottom:1px solid var(--line)}
.acc-n{font-weight:700;color:var(--navy)}.acc-c{font-weight:800;text-align:right}.acc-c{text-align:left}.trk.stk{display:flex;overflow:hidden}.trk.stk::after{display:none}
.trk.stk i{border-radius:0;flex:none}
.trk.stk .s-ok{background:var(--ok)}.trk.stk .s-rev{background:var(--amber)}.trk.stk .s-dev{background:var(--blue)}.trk.stk .s-con{background:#c3c9d6}.trk.stk .s-sin{background:#e6e9f0}
tr.resalta td{background:var(--amber-soft)}
.acc-c small{display:block;font-weight:600;color:var(--muted);font-size:11px}
.acc-chips{display:flex;gap:4px}
.acc .tw{padding:0 6px 6px}
.acc-ayuda{margin:-4px 0 12px}
@media(max-width:700px){.acc summary{grid-template-columns:1fr 50px}.acc summary .trk,.acc-chips{grid-column:1/-1}}
</style>
</head>
<body>
<div class="wrap">

    <header class="hero">
        <div>
            <small>Reporte de seguimiento · FOR-DE-144</small>
            <h1><?php echo $tituloAnio; ?></h1>
            <p>Estado del trimestre <?php echo $trimestre; ?> · actualizado <?php echo date('d/m/Y H:i'); ?></p>
        </div>
        <div class="hero-acc">
            <nav class="tsel" aria-label="Trimestre">
                <?php foreach ([1, 2, 3, 4] as $tn): ?><a href="<?php echo $urlTrim($tn); ?>" class="<?php echo $tn === $trimestre ? 'on' : ''; ?>"<?php echo $tn === $trimestre ? ' aria-current="page"' : ''; ?>>T<?php echo $tn; ?></a><?php endforeach; ?>
            </nav>
            <a class="btn" href="<?php echo $basePath; ?>/FOR-DE-144">‹ Volver</a>
            <a class="btn" href="<?php echo $basePath; ?>/FOR-DE-144?action=reporteCumplidos&id=<?php echo (int)$formulario['id']; ?>&t=<?php echo $trimestre; ?>">Estado por dependencia</a>
            <a class="btn" href="<?php echo $basePath; ?>/modulo144?id=<?php echo (int)$formulario['id']; ?>">Plan de acción</a>
        </div>
    </header>

    <section class="kpis" aria-label="Resumen">
        <div class="kpi b"><small>Reportados</small><div class="v"><?php echo $kpi['publicados']; ?> <span style="font-size:.5em;font-weight:600;color:var(--muted)">de <?php echo $kpi['total']; ?></span></div><div class="s">con el T<?php echo $trimestre; ?> aprobado por la Oficina de Planeación</div></div>
        <button type="button" class="kpi kpi-btn" id="abrirRev" aria-pressed="false"><small>En revisión</small><div class="v"><?php echo $kpi['revision']; ?> <span style="font-size:.5em;font-weight:600;color:var(--muted)">de <?php echo $kpi['total']; ?> · <?php echo $pctDe($kpi['revision'], $kpi['total']); ?>%</span></div><div class="s">esperando aprobación en el semáforo · clic para resaltarlos en el listado</div></button>
    </section>

    <nav class="tabs" role="tablist">
        <button type="button" role="tab" class="on" data-tp="tp-dep">Por dependencia <span><?php echo count($porDep); ?></span></button>
        <button type="button" role="tab" data-tp="tp-fac">Facultades <span><?php echo count($porFacultad); ?></span></button>
        <div class="fbar" id="fbarDep">
            <select id="dFiltro" aria-label="Filtrar dependencias por estado">
                <option value="">Todos los estados</option>
                <option value="publicado">Aprobado</option>
                <option value="revision">En revisión</option>
                <option value="devuelto">Devuelto</option>
                <option value="construccion">En construcción</option>
                <option value="sinreportar">Sin reportar</option>
                <option value="pos">Parcial positivo (con algo reportado)</option>
                <option value="neg">Parcial negativo (sin nada reportado)</option>
            </select>
            <select id="dSub" aria-label="Filtrar por quién está revisando" hidden>
                <option value="">Todos los revisores</option>
                <?php foreach ($etapas as $num => $nombreEtapa): ?><option value="<?php echo (int)$num; ?>"><?php echo $h($nombreEtapa); ?></option><?php endforeach; ?>
            </select>
            <button type="button" id="dPdf" title="Abre el diálogo de impresión: elige «Guardar como PDF»">Exportar PDF</button>
        </div>
        <div class="fbar" id="fbar" hidden>
            <select id="fFiltro" aria-label="Filtrar por estado de concertación">
                <option value="">Todos los estados</option>
                <option value="conc">Concertado con vicerrectoría</option>
                <option value="espera">En espera de concertación</option>
                <option value="nada">Sin declarar</option>
                <option value="pos">Parcial positivo (con verdes o naranjas)</option>
                <option value="neg">Parcial negativo (sin nada reportado)</option>
            </select>
            <button type="button" id="fPdf" title="Abre el diálogo de impresión: elige «Guardar como PDF»">Exportar PDF</button>
        </div>
    </nav>

    <?php
    // Desplegables: cada grupo muestra cuántos indicadores están aprobados y, al abrirlo, el detalle
    $desplegables = function ($grupos, $vacio) use ($h, $pctDe, $filas, $fmt) {
        if (!$grupos) { echo '<p class="vacio">' . $h($vacio) . '</p>'; return; }
        foreach ($grupos as $g):
            // Avance sobre TODOS los indicadores del grupo: reportado = seguimiento aprobado por la Oficina de Planeación
            $cPct = $pctDe($g['publicado'], $g['total']);
        ?>
        <details class="acc" data-t="<?php echo (int)$g['total']; ?>" data-a="<?php echo (int)$g['publicado']; ?>" data-r="<?php echo (int)$g['revision']; ?>" data-publicado="<?php echo (int)$g['publicado']; ?>" data-revision="<?php echo (int)$g['revision']; ?>" data-devuelto="<?php echo (int)$g['devuelto']; ?>" data-construccion="<?php echo (int)$g['construccion']; ?>" data-sinreportar="<?php echo (int)$g['sinreportar']; ?>">
            <summary>
                <span class="acc-n"><?php echo $h($g['nombre']); ?></span>
                <span class="acc-c" data-orig="<?php echo $cPct; ?>% reportados<small><?php echo $g['publicado']; ?> de <?php echo $g['total']; ?> aprobados</small>" title="Reportados = seguimiento aprobado por la Oficina de Planeación, sobre los <?php echo $g['total']; ?> indicadores de este grupo"><?php echo $cPct; ?>% reportados<small><?php echo $g['publicado']; ?> de <?php echo $g['total']; ?> aprobados</small></span>
                <span class="trk stk" title="Aprobados · en revisión · devueltos · en construcción · sin reportar">
                    <i class="s-ok" style="width:<?php echo $pctDe($g['publicado'], $g['total']); ?>%"></i><i class="s-rev" style="width:<?php echo $pctDe($g['revision'], $g['total']); ?>%"></i><i class="s-dev" style="width:<?php echo $pctDe($g['devuelto'], $g['total']); ?>%"></i><i class="s-con" style="width:<?php echo $pctDe($g['construccion'], $g['total']); ?>%"></i><i class="s-sin" style="width:<?php echo $pctDe($g['sinreportar'], $g['total']); ?>%"></i>
                </span>
                <span class="acc-chips">
                    <span class="chip c-publicado" data-k="publicado" title="Aprobados"><?php echo $g['publicado']; ?></span>
                    <span class="chip c-revision" data-k="revision" title="En revisión"><?php echo $g['revision']; ?></span>
                    <span class="chip c-devuelto" data-k="devuelto" title="Devueltos"><?php echo $g['devuelto']; ?></span>
                    <span class="chip c-construccion" data-k="construccion" title="En construcción: con valor, sin enviar a revisión"><?php echo $g['construccion']; ?></span>
                    <span class="chip c-sinreportar" data-k="sinreportar" title="Sin reportar: ningún valor en este trimestre"><?php echo $g['sinreportar']; ?></span>
                </span>
            </summary>
            <div class="tw"><table>
                <thead><tr><th>Indicador</th><th>Línea · Motor · Proyecto</th><th>T1</th><th>T2</th><th>T3</th><th>T4</th><th>Estado T<?php echo (int)$trimestre; ?></th></tr></thead>
                <tbody>
                <?php foreach ($g['idx'] as $i): $f = $filas[$i]; $pc = $f['unidad'] === 'Porcentaje' ? '%' : ''; ?>
                <tr data-estado="<?php echo $h($f['estado']); ?>" data-rev="<?php echo (int)$f['revisa']; ?>">
                    <td class="nm"><b><?php echo $h($f['nombre']); ?></b></td>
                    <td><span class="lmp"><?php echo $h($f['lmp'] ?: '—'); ?></span></td>
                    <?php foreach ([1, 2, 3, 4] as $tn): $c = $f['tri'][$tn]; ?>
                    <td class="tc <?php echo $h($c['e']); ?>" title="<?php echo ['ap' => 'Aprobado', 'rev' => 'En revisión', 'dev' => 'Devuelto', 'con' => 'En construcción', 'na' => 'No aplica'][$c['e']]; ?>"><i></i><?php echo $c['e'] === 'na' ? 'No aplica' : ($c['v'] !== null ? $h($fmt($c['v'])) . $pc : '—'); ?></td>
                    <?php endforeach; ?>
                    <td><span class="chip c-<?php echo $h($f['estado']); ?>"><?php echo $h($f['estadoTxt']); ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </details>
        <?php endforeach;
    }; ?>

    <section class="box tp" id="tp-dep">
        <h2>Por dependencia</h2>
        <?php $desplegables($porDep, 'Todavía no hay indicadores en este trimestre.'); ?>
    </section>

    <section class="box tp" id="tp-fac" hidden>
        <h2>Facultades</h2>
        <?php if (!$porFacultad): ?>
        <p class="vacio">Ninguna facultad tiene metas en los indicadores de este semestre.</p>
        <?php else: $txtEc = ['conc' => 'Concertado', 'espera' => 'En espera de concertación', 'nada' => 'Sin declarar']; ?>
        <p class="vacio acc-ayuda">Concertación del semestre <?php echo $semConc; ?> (T<?php echo $trimestre; ?>) entre cada facultad y la vicerrectoría. Concertado: la vicerrectoría puso su valor y se dio clic en Concertar · En espera: la facultad puso un valor y aún no se ha concertado · Sin declarar: la facultad no ha puesto ningún valor.</p>
        <?php foreach ($porFacultad as $g): ?>
        <details class="acc facc" data-t="<?php echo (int)$g['total']; ?>" data-conc="<?php echo (int)$g['conc']; ?>" data-espera="<?php echo (int)$g['espera']; ?>" data-nada="<?php echo (int)$g['nada']; ?>">
            <summary>
                <span class="acc-n"><?php echo $h($g['nombre']); ?></span>
                <span class="acc-c"><?php echo $pctDe($g['conc'], $g['total']); ?>% concertado<small><?php echo $g['conc']; ?> de <?php echo $g['total']; ?> indicadores</small></span>
                <span class="trk stk" title="Concertado · en espera · sin declarar">
                    <i class="s-ok" style="width:<?php echo $pctDe($g['conc'], $g['total']); ?>%"></i><i class="s-rev" style="width:<?php echo $pctDe($g['espera'], $g['total']); ?>%"></i><i class="s-con" style="width:<?php echo $pctDe($g['nada'], $g['total']); ?>%"></i>
                </span>
                <span class="acc-chips">
                    <span class="chip c-publicado" data-k="conc" title="Concertado con la vicerrectoría"><?php echo $g['conc']; ?></span>
                    <span class="chip c-revision" data-k="espera" title="En espera de concertación"><?php echo $g['espera']; ?></span>
                    <span class="chip c-construccion" data-k="nada" title="Sin declarar"><?php echo $g['nada']; ?></span>
                </span>
            </summary>
            <div class="tw"><table>
                <thead><tr><th>Indicador</th><th>Línea · Motor · Proyecto</th><th>Facultad</th><th>Vicerrectoría</th><th>Estado</th></tr></thead>
                <tbody>
                <?php foreach ($g['items'] as $it): ?>
                <tr data-ec="<?php echo $h($it['ec']); ?>">
                    <td class="nm"><b><?php echo $h($it['nombre']); ?></b></td>
                    <td><span class="lmp"><?php echo $h($it['lmp'] ?: '—'); ?></span></td>
                    <td class="sv"><?php echo $h($it['vf']); ?></td>
                    <td class="sv"><?php echo $h($it['vv']); ?></td>
                    <td><span class="chip c-<?php echo ['conc' => 'publicado', 'espera' => 'revision', 'nada' => 'construccion'][$it['ec']]; ?>"><?php echo $h($txtEc[$it['ec']]); ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </details>
        <?php endforeach; endif; ?>
    </section>

    <p class="pie">La barra suma aprobados (verde), en revisión (ámbar), devueltos (azul) y en construcción (gris) del T<?php echo $trimestre; ?>. Para cambiar un dato, ábrelo en el Plan de acción.</p>
</div>
<dialog id="dlgPdf" aria-labelledby="dlgPdfT">
    <h3 id="dlgPdfT">Exportar PDF</h3>
    <p id="dlgPdfP">¿Cómo quieres el reporte?</p>
    <div class="opc">
        <button type="button" data-desglose="1"><b>Desglosado</b><span>Cada grupo con sus indicadores abiertos</span></button>
        <button type="button" data-desglose="0"><b>Sin desglosar</b><span>Solo los grupos, sin abrir sus indicadores</span></button>
    </div>
    <button type="button" class="cancelar" id="dlgPdfCancelar">Cancelar</button>
</dialog>
<script>
(function () {
    // La tarjeta «En revisión» filtra las listas de abajo (Por dependencia y Facultades): solo ese estado, con los desplegables abiertos
    var kRev = document.getElementById('abrirRev');
    // Con la tarjeta activa cada grupo muestra el avance en revisión sobre TODOS sus indicadores y queda cerrado,
    // para ver de un vistazo cuántos han cumplido (aprobados) y cuántos no
    function filtrarRevision(on) {
        kRev.setAttribute('aria-pressed', on ? 'true' : 'false');
        Array.prototype.forEach.call(document.querySelectorAll('details.acc:not(.facc)'), function (d) {
            var t = parseInt(d.getAttribute('data-t'), 10) || 0, a = parseInt(d.getAttribute('data-a'), 10) || 0, r = parseInt(d.getAttribute('data-r'), 10) || 0;
            var c = d.querySelector('.acc-c');
            c.innerHTML = on
                ? (t ? Math.round(r / t * 100) : 0) + '% en revisión<small>' + r + ' en revisión · ' + a + ' aprobados · ' + (t - a) + ' sin aprobar · de ' + t + '</small>'
                : c.getAttribute('data-orig');
            Array.prototype.forEach.call(d.querySelectorAll('tbody tr'), function (row) {
                row.classList.toggle('resalta', on && row.getAttribute('data-estado') === 'revision');
            });
            d.open = false;
        });
        if (on) { var tab = document.querySelector('.tabs button[data-tp="tp-dep"]'); if (tab) tab.click(); }
    }
    kRev.addEventListener('click', function () { filtrarRevision(kRev.getAttribute('aria-pressed') !== 'true'); });
    var botones = Array.prototype.slice.call(document.querySelectorAll('.tabs button[data-tp]'));
    var fbar = document.getElementById('fbar'), fbarDep = document.getElementById('fbarDep');
    botones.forEach(function (b) {
        b.addEventListener('click', function () {
            botones.forEach(function (x) { x.classList.toggle('on', x === b); });
            Array.prototype.forEach.call(document.querySelectorAll('.tp'), function (p) { p.hidden = p.id !== b.getAttribute('data-tp'); });
            fbar.hidden = b.getAttribute('data-tp') !== 'tp-fac'; // cada pestaña tiene su propio filtro y su PDF
            fbarDep.hidden = b.getAttribute('data-tp') !== 'tp-dep';
        });
    });

    // Facultades: filtra por estado de concertación; el número del medio sigue al filtro
    var ETIQUETA = { conc: 'concertado', espera: 'en espera', nada: 'sin declarar', pos: 'con reporte', neg: 'sin reporte' };
    var fFiltro = document.getElementById('fFiltro');
    // Parcial positivo: la facultad tiene algo reportado (verdes o naranjas). Parcial negativo: todo en gris, no ha hecho nada.
    function filtrarFacultades() {
        var e = fFiltro.value;
        var porEstado = e === 'conc' || e === 'espera' || e === 'nada';
        Array.prototype.forEach.call(document.querySelectorAll('details.facc'), function (d) {
            var t = parseInt(d.getAttribute('data-t'), 10) || 0;
            var conc = parseInt(d.getAttribute('data-conc'), 10) || 0, espera = parseInt(d.getAttribute('data-espera'), 10) || 0, nada = parseInt(d.getAttribute('data-nada'), 10) || 0;
            var n = e === '' ? conc : (e === 'conc' ? conc : (e === 'espera' ? espera : (e === 'nada' ? nada : (e === 'pos' ? conc + espera : nada))));
            var c = d.querySelector('.acc-c');
            c.innerHTML = (t ? Math.round(n / t * 100) : 0) + '% ' + (e ? ETIQUETA[e] : 'concertado') + '<small>' + n + ' de ' + t + ' indicadores</small>';
            Array.prototype.forEach.call(d.querySelectorAll('tbody tr'), function (r) {
                var ec = r.getAttribute('data-ec');
                r.hidden = porEstado ? ec !== e : (e === 'pos' ? ec === 'nada' : false);
            });
            // Con un estado activo solo queda la bolita de ese estado; con «Todos» o con los parciales, las tres
            Array.prototype.forEach.call(d.querySelectorAll('.acc-chips .chip[data-k]'), function (b) { b.hidden = porEstado && b.getAttribute('data-k') !== e; });
            // Se listan solo las facultades que cumplen el filtro
            d.hidden = (porEstado && n === 0) || (e === 'pos' && conc + espera === 0) || (e === 'neg' && conc + espera > 0);
        });
    }
    fFiltro.addEventListener('change', filtrarFacultades);

    // Por dependencia: filtra por estado del trimestre; el número del medio sigue al filtro (parcial positivo/negativo igual que en Facultades)
    var ETQ_DEP = { publicado: 'aprobados', revision: 'en revisión', devuelto: 'devueltos', construccion: 'en construcción', sinreportar: 'sin reportar', pos: 'con reporte', neg: 'sin reporte' };
    var dFiltro = document.getElementById('dFiltro'), dSub = document.getElementById('dSub');
    function filtrarDependencias() {
        var e = dFiltro.value;
        var porEstado = !!e && e !== 'pos' && e !== 'neg';
        Array.prototype.forEach.call(document.querySelectorAll('#tp-dep details.acc'), function (d) {
            var t = parseInt(d.getAttribute('data-t'), 10) || 0;
            var v = function (k) { return parseInt(d.getAttribute('data-' + k), 10) || 0; };
            var conAlgo = t - v('sinreportar');
            var n = porEstado ? v(e) : (e === 'pos' ? conAlgo : (e === 'neg' ? v('sinreportar') : 0));
            // En revisión con un revisor elegido: solo cuentan los indicadores que está revisando esa etapa
            var sub = e === 'revision' ? dSub.value : '';
            if (sub) {
                n = 0;
                Array.prototype.forEach.call(d.querySelectorAll('tbody tr'), function (r) {
                    if (r.getAttribute('data-estado') === 'revision' && r.getAttribute('data-rev') === sub) n++;
                });
            }
            var c = d.querySelector('.acc-c');
            var quien = sub ? ' · ' + dSub.options[dSub.selectedIndex].text : '';
            c.innerHTML = e ? (t ? Math.round(n / t * 100) : 0) + '% ' + ETQ_DEP[e] + quien + '<small>' + n + ' de ' + t + ' indicadores</small>' : c.getAttribute('data-orig');
            Array.prototype.forEach.call(d.querySelectorAll('tbody tr'), function (r) {
                var est = r.getAttribute('data-estado');
                r.hidden = porEstado ? (est !== e || (sub !== '' && r.getAttribute('data-rev') !== sub)) : (e === 'pos' ? est === 'sinreportar' : false);
            });
            Array.prototype.forEach.call(d.querySelectorAll('.acc-chips .chip[data-k]'), function (b) {
                b.hidden = porEstado && b.getAttribute('data-k') !== e;
                // La bolita de «En revisión» sigue al revisor elegido
                if (b.getAttribute('data-k') === 'revision') {
                    if (b.getAttribute('data-orig') === null) b.setAttribute('data-orig', b.textContent);
                    b.textContent = sub ? n : b.getAttribute('data-orig');
                }
            });
            d.hidden = (porEstado && n === 0) || (e === 'pos' && conAlgo === 0) || (e === 'neg' && conAlgo > 0);
        });
    }
    dFiltro.addEventListener('change', function () {
        dSub.hidden = dFiltro.value !== 'revision'; // el subfiltro solo existe con «En revisión»
        if (dSub.hidden) dSub.value = '';
        filtrarDependencias();
    });
    dSub.addEventListener('change', filtrarDependencias);

    // Exportar PDF (en cualquiera de las dos pestañas): el pop up pregunta si va desglosado (con los indicadores abiertos) o sin desglosar.
    // Se imprime solo la pestaña activa, con lo que el filtro deja ver.
    var dlg = document.getElementById('dlgPdf');
    function abrirPdf() { dlg.showModal(); }
    document.getElementById('fPdf').addEventListener('click', abrirPdf);
    document.getElementById('dPdf').addEventListener('click', abrirPdf);
    document.getElementById('dlgPdfCancelar').addEventListener('click', function () { dlg.close(); });
    Array.prototype.forEach.call(dlg.querySelectorAll('[data-desglose]'), function (b) {
        b.addEventListener('click', function () {
            var desglosado = b.getAttribute('data-desglose') === '1';
            dlg.close();
            var enFac = !document.getElementById('tp-fac').hidden;
            var filtro = enFac ? fFiltro.value : dFiltro.value;
            var etiqueta = enFac ? ETIQUETA : ETQ_DEP;
            var abiertos = [];
            Array.prototype.forEach.call(document.querySelectorAll(enFac ? '#tp-fac details.facc' : '#tp-dep details.acc'), function (d) { abiertos.push([d, d.open]); d.open = desglosado && !d.hidden; });
            var tituloAntes = document.title;
            document.title = (enFac ? 'Concertación por facultad' : 'Estado por dependencia') + ' · T<?php echo (int)$trimestre; ?>' + (filtro ? ' · ' + etiqueta[filtro] : '') + (!enFac && dSub.value ? ' · ' + dSub.options[dSub.selectedIndex].text : '') + (desglosado ? ' · desglosado' : '');
            window.onafterprint = function () {
                document.title = tituloAntes;
                abiertos.forEach(function (a) { a[0].open = a[1]; });
                window.onafterprint = null;
            };
            setTimeout(function () { window.print(); }, 50); // deja que el pop up termine de cerrarse antes de imprimir
        });
    });
})();
</script>
</body>
</html>
