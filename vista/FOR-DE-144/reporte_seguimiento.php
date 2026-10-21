<?php
// vista/FOR-DE-144/reporte_seguimiento.php — landing con el estado actual de los indicadores del Plan de Acción
if (!isset($formulario)) {
    header('Location: ' . Config::getBasePath() . '/FOR-DE-144');
    exit;
}
$h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$basePath = Config::getBasePath();
$indicadores = $reporte['indicadores'] ?? [];
$evidencias  = $reporte['evidencias'] ?? [];
$tituloF = $h($formulario['titulo'] ?? '');
$anio    = $h($formulario['anio'] ?? date('Y'));
$META    = 80; // meta de cumplimiento (%)

$etapas = [1 => 'Gestor de Metas', 2 => 'Líder de Metas', 3 => 'Vicerrectoría', 4 => 'Oficina de Planeación'];
$tieneValor = function ($v) { return $v !== null && trim((string)$v) !== ''; };

$filas = [];
$kpi = ['total' => 0, 's1' => 0, 's2' => 0, 'sinReporte' => 0, 'enMeta' => 0, 'enCamino' => 0, 'publicados' => 0, 'revision' => 0, 'devueltos' => 0, 'construccion' => 0];
$porLinea = []; // código => ['nombre' =>, 'n' =>, 'suma' =>, 'con' =>]
foreach ($indicadores as $r) {
    $esLimite = ($r['tipo_medicion'] ?? '') === 'Límite';
    $s1 = !$esLimite && $tieneValor($r['semestre1_seguimiento'] ?? null);
    $s2 = $tieneValor($r['semestre2_seguimiento'] ?? null);
    $reportado = $s1 || $s2;
    $pct = $tieneValor($r['porcentaje_avance'] ?? null) ? min(100.0, max(0.0, (float)$r['porcentaje_avance'])) : null;

    $sol = (int)($r['solicitud_estado_seguimiento'] ?? 0);
    $etapa = (int)($r['semaforo_etapa_seguimiento'] ?? 0);
    if ((int)($r['estado_seguimiento'] ?? 0) === 2) { $estado = 'publicado'; $estadoTxt = 'Aprobado'; $kpi['publicados']++; }
    elseif ($sol === 1) { $estado = 'revision'; $estadoTxt = 'En revisión · ' . ($etapas[min(4, $etapa + 1)] ?? ''); $kpi['revision']++; }
    elseif ($sol === 2) { $estado = 'devuelto'; $estadoTxt = 'Devuelto para ajustes'; $kpi['devueltos']++; }
    else { $estado = 'construccion'; $estadoTxt = $reportado ? 'En construcción' : 'Sin iniciar'; $kpi['construccion']++; }

    $kpi['total']++;
    if ($s1) $kpi['s1']++;
    if ($s2) $kpi['s2']++;
    if (!$reportado) $kpi['sinReporte']++;
    elseif ($pct !== null && $pct >= $META) $kpi['enMeta']++;
    elseif ($reportado) $kpi['enCamino']++;

    $cod = $r['linea_codigo'] ?: '—';
    if (!isset($porLinea[$cod])) $porLinea[$cod] = ['nombre' => (string)($r['linea_estrategica'] ?? ''), 'n' => 0, 'suma' => 0.0, 'con' => 0];
    $porLinea[$cod]['n']++;
    if ($reportado && $pct !== null) { $porLinea[$cod]['suma'] += $pct; $porLinea[$cod]['con']++; }

    $ev = $evidencias[(int)$r['id']] ?? [];
    $nombre = trim((string)($r['formula_medicion'] ?? '')) !== '' ? $r['formula_medicion'] : ($r['nombre_indicador'] ?: $r['nombre_borrador']);
    $filas[] = [
        'id' => (int)$r['id'], 'nombre' => $nombre, 'indicador' => $r['nombre_indicador'] ?: $r['nombre_borrador'],
        'lmp' => implode(' · ', array_filter([$r['linea_codigo'], $r['motor_codigo'], $r['proyecto_codigo']])),
        'linea' => $cod, 'responsable' => $r['responsable_formulacion'] ?? '',
        's1' => $s1, 's2' => $s2, 'limite' => $esLimite,
        'v1' => $s1 ? $r['semestre1_seguimiento'] : null, 'v2' => $s2 ? $r['semestre2_seguimiento'] : null,
        'ev1' => (int)($ev[1] ?? 0), 'ev2' => (int)($ev[2] ?? 0),
        'pct' => $reportado ? $pct : null, 'estado' => $estado, 'estadoTxt' => $estadoTxt, 'etapa' => $etapa, 'sol' => $sol,
        'unidad' => $r['unidad_medida'] ?? '',
    ];
}
ksort($porLinea);
$cumpl = array_filter(array_map(function ($f) { return $f['pct']; }, $filas), function ($v) { return $v !== null; });
$promedio = $cumpl ? array_sum($cumpl) / count($cumpl) : 0;
$pctDe = function ($n, $t) { return $t > 0 ? round($n / $t * 100) : 0; };
$fmt = function ($v) { return rtrim(rtrim(number_format((float)$v, 2, ',', '.'), '0'), ','); };
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reporte de seguimiento · <?php echo $tituloF; ?> · <?php echo $anio; ?></title>
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
.hero .acc{display:flex;gap:8px;flex-wrap:wrap}
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
.trk::after{content:"";position:absolute;left:<?php echo $META; ?>%;top:-4px;bottom:-4px;width:2px;background:var(--orange)}
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
@media print{.acc,.tools{display:none}body{background:#fff}}
</style>
</head>
<body>
<div class="wrap">

    <header class="hero">
        <div>
            <small>Reporte de seguimiento · FOR-DE-144</small>
            <h1><?php echo $tituloF; ?> (<?php echo $anio; ?>)</h1>
            <p>Estado actual de los indicadores aprobados · actualizado <?php echo date('d/m/Y H:i'); ?></p>
        </div>
        <div class="acc">
            <a class="btn" href="<?php echo $basePath; ?>/FOR-DE-144">‹ Volver</a>
            <a class="btn" href="<?php echo $basePath; ?>/modulo144?id=<?php echo (int)$formulario['id']; ?>">Plan de acción</a>
        </div>
    </header>

    <section class="kpis" aria-label="Resumen">
        <div class="kpi"><small>Indicadores</small><div class="v"><?php echo $kpi['total']; ?></div><div class="s">aprobados en el plan</div></div>
        <div class="kpi b"><small>Avance promedio</small><div class="v"><?php echo $fmt($promedio); ?>%</div><div class="s">meta del <?php echo $META; ?>%</div></div>
        <div class="kpi"><small>Cumplen la meta</small><div class="v"><?php echo $kpi['enMeta']; ?></div><div class="s">con avance ≥ <?php echo $META; ?>%</div></div>
        <div class="kpi a"><small>En camino</small><div class="v"><?php echo $kpi['enCamino']; ?></div><div class="s">con reporte, por debajo de la meta</div></div>
        <div class="kpi g"><small>Sin reporte</small><div class="v"><?php echo $kpi['sinReporte']; ?></div><div class="s">aún sin semestre registrado</div></div>
    </section>

    <section class="grid2">
        <div class="box">
            <h2>Avance por línea estratégica</h2>
            <?php if (!$porLinea): ?><p class="vacio">Todavía no hay indicadores aprobados.</p><?php endif; ?>
            <?php foreach ($porLinea as $cod => $l):
                $prom = $l['con'] > 0 ? $l['suma'] / $l['con'] : 0; ?>
            <div class="bar-row">
                <div class="n" title="<?php echo $h($cod . ' - ' . $l['nombre']); ?>"><b><?php echo $h($cod); ?></b> <small><?php echo $h($l['nombre']); ?></small></div>
                <div class="trk"><i class="<?php echo $prom >= $META ? '' : 'cam'; ?>" style="width:<?php echo round($prom); ?>%"></i></div>
                <b><?php echo $l['con'] > 0 ? round($prom) . '%' : '—'; ?></b>
            </div>
            <?php endforeach; ?>
            <div class="leg"><span><i style="background:var(--blue)"></i>Cumple la meta</span><span><i style="background:var(--amber)"></i>En camino</span><span><i style="background:var(--orange)"></i>Meta <?php echo $META; ?>%</span></div>
        </div>
        <div class="box">
            <h2>Reporte por semestre</h2>
            <div class="sem">
                <div class="sem-row"><div class="t">Semestre 1 <span><?php echo $kpi['s1']; ?> de <?php echo $kpi['total']; ?> indicadores</span></div>
                    <div class="trk" style="--x:0"><i style="width:<?php echo $pctDe($kpi['s1'], $kpi['total']); ?>%"></i></div></div>
                <div class="sem-row"><div class="t">Semestre 2 <span><?php echo $kpi['s2']; ?> de <?php echo $kpi['total']; ?> indicadores</span></div>
                    <div class="trk"><i style="width:<?php echo $pctDe($kpi['s2'], $kpi['total']); ?>%"></i></div></div>
            </div>
            <h2 style="margin-top:20px">Estado del seguimiento</h2>
            <div class="est">
                <span class="chip c-publicado">Aprobados · <?php echo $kpi['publicados']; ?></span>
                <span class="chip c-revision">En revisión · <?php echo $kpi['revision']; ?></span>
                <span class="chip c-devuelto">Devueltos · <?php echo $kpi['devueltos']; ?></span>
                <span class="chip c-construccion">En construcción · <?php echo $kpi['construccion']; ?></span>
            </div>
        </div>
    </section>

    <section class="box">
        <h2>Indicadores</h2>
        <div class="tools">
            <input type="search" id="buscar" placeholder="Buscar indicador, dependencia o línea…" aria-label="Buscar">
            <select id="fLinea" aria-label="Línea">
                <option value="">Línea: todas</option>
                <?php foreach (array_keys($porLinea) as $cod): ?><option value="<?php echo $h($cod); ?>"><?php echo $h($cod); ?></option><?php endforeach; ?>
            </select>
            <select id="fEstado" aria-label="Estado">
                <option value="">Estado: todos</option>
                <option value="publicado">Aprobado</option><option value="revision">En revisión</option>
                <option value="devuelto">Devuelto</option><option value="construccion">En construcción / sin iniciar</option>
            </select>
        </div>
        <div class="tw">
            <table id="tabla">
                <thead><tr>
                    <th>Indicador</th><th>Línea · Motor · Proyecto</th><th>Responsable</th>
                    <th>Semestre 1</th><th>Semestre 2</th><th>Evidencias</th><th>Avance</th><th>Estado</th><th>Semáforo</th>
                </tr></thead>
                <tbody>
                <?php foreach ($filas as $f): ?>
                <tr data-linea="<?php echo $h($f['linea']); ?>" data-estado="<?php echo $h($f['estado']); ?>"
                    data-buscar="<?php echo $h(mb_strtolower($f['nombre'] . ' ' . $f['indicador'] . ' ' . $f['responsable'] . ' ' . $f['lmp'], 'UTF-8')); ?>">
                    <td class="nm"><b><?php echo $h($f['nombre']); ?></b></td>
                    <td><span class="lmp"><?php echo $h($f['lmp'] ?: '—'); ?></span></td>
                    <td><?php echo $h($f['responsable'] ?: '—'); ?></td>
                    <td class="sv"><?php echo $f['s1'] ? $h($fmt($f['v1'])) . ($f['unidad'] === 'Porcentaje' ? '%' : '') : '<span class="nd">' . ($f['limite'] ? 'No aplica' : '—') . '</span>'; ?></td>
                    <td class="sv"><?php echo $f['s2'] ? $h($fmt($f['v2'])) . ($f['unidad'] === 'Porcentaje' ? '%' : '') : '<span class="nd">—</span>'; ?></td>
                    <td><span class="ev"><span class="<?php echo $f['ev1'] ? '' : 'z'; ?>" title="Evidencias del semestre 1">S1 · <?php echo $f['ev1']; ?></span><span class="<?php echo $f['ev2'] ? '' : 'z'; ?>" title="Evidencias del semestre 2">S2 · <?php echo $f['ev2']; ?></span></span></td>
                    <td><?php if ($f['pct'] === null): ?><span class="nd">—</span><?php else: ?>
                        <div class="pct"><div class="trk"><i class="<?php echo $f['pct'] >= $META ? '' : 'cam'; ?>" style="width:<?php echo round($f['pct']); ?>%"></i></div><span><?php echo round($f['pct']); ?>%</span></div>
                        <?php endif; ?></td>
                    <td><span class="chip c-<?php echo $h($f['estado']); ?>"><?php echo $h($f['estadoTxt']); ?></span></td>
                    <td><span class="sem-circ" title="Etapas del semáforo de aprobación">
                        <?php foreach (['G', 'L', 'V', 'P'] as $i => $letra): ?><i class="<?php echo ($f['estado'] === 'publicado' || ($f['sol'] === 1 && $i < $f['etapa'])) ? 'on' : ''; ?>"><?php echo $letra; ?></i><?php endforeach; ?>
                    </span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (!$filas): ?><p class="vacio">Todavía no hay indicadores aprobados en este formulario.</p><?php endif; ?>
            <p class="vacio" id="sinResultados" hidden>Ningún indicador coincide con los filtros.</p>
        </div>
    </section>

    <p class="pie">Los indicadores sin reporte no cuentan en el avance promedio. Para cambiar un dato, ábrelo en el Plan de acción.</p>
</div>
<script>
(function () {
    var q = document.getElementById('buscar'), l = document.getElementById('fLinea'), e = document.getElementById('fEstado');
    var filas = Array.prototype.slice.call(document.querySelectorAll('#tabla tbody tr'));
    function filtrar() {
        var t = q.value.trim().toLowerCase(), visibles = 0;
        filas.forEach(function (r) {
            var ok = (!t || r.getAttribute('data-buscar').indexOf(t) !== -1)
                && (!l.value || r.getAttribute('data-linea') === l.value)
                && (!e.value || r.getAttribute('data-estado') === e.value);
            r.hidden = !ok; if (ok) visibles++;
        });
        document.getElementById('sinResultados').hidden = visibles > 0 || !filas.length;
    }
    q.addEventListener('input', filtrar); l.addEventListener('change', filtrar); e.addEventListener('change', filtrar);
})();
</script>
</body>
</html>
