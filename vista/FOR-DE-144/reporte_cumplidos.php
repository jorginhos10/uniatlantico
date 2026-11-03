<?php
// vista/FOR-DE-144/reporte_cumplidos.php — «Estado por dependencia»: qué dependencias tienen aprobado, en construcción o pendiente el trimestre elegido
if (!isset($formulario)) {
    header('Location: ' . Config::getBasePath() . '/FOR-DE-144');
    exit;
}
$h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$basePath = Config::getBasePath();
$indicadores = $reporte['indicadores'] ?? [];
$tituloF = $h($formulario['titulo'] ?? '');
$anio    = $h($formulario['anio'] ?? date('Y'));
$tituloAnio = (strpos($tituloF, (string)$anio) !== false) ? $tituloF : $tituloF . ' (' . $anio . ')';
$trimestre = isset($trimestre) ? max(1, min(4, (int)$trimestre)) : 1;
$tieneValor = function ($v) { return $v !== null && trim((string)$v) !== ''; };
$fmt = function ($v) { return rtrim(rtrim(number_format((float)$v, 2, ',', '.'), '0'), ','); };
$urlTrim = function ($t) use ($basePath, $formulario) { return $basePath . '/FOR-DE-144?action=reporteCumplidos&id=' . (int)$formulario['id'] . '&t=' . (int)$t; };

// Estado de cada indicador en el trimestre elegido (el seguimiento se aprueba por trimestre):
//  ap = aprobado · rev = en revisión · dev = devuelto · borrador = con valor y sin enviar · nada = sin ningún valor
// Pestañas, siempre por dependencia:
//  aprobados = trimestre aprobado · construccion = en borrador (con valor, sin enviar) · pendientes = todo lo que no está aprobado
$cats = ['aprobados' => [], 'construccion' => [], 'pendientes' => []];
$totalDep = [];  // clave => indicadores de la dependencia en este trimestre
$nombreDep = [];
$total = 0;
foreach ($indicadores as $r) {
    $esLimite = ($r['tipo_medicion'] ?? '') === 'Límite';
    if ($esLimite && $trimestre <= 2) continue; // en «Límite» el semestre 1 (T1 y T2) no se registra
    $total++;

    $e = (int)($r['t' . $trimestre . '_estado'] ?? 0);
    $s = (int)($r['t' . $trimestre . '_sol'] ?? 0);
    $conValor = $tieneValor($r['trimestre' . $trimestre . '_seguimiento'] ?? null);
    $estado = $e === 2 ? 'ap' : ($s === 1 ? 'rev' : ($s === 2 ? 'dev' : ($conValor ? 'borrador' : 'nada')));
    $txt = ['ap' => 'Aprobado', 'rev' => 'En revisión', 'dev' => 'Devuelto', 'borrador' => 'En construcción', 'nada' => 'Sin iniciar'][$estado];

    $pc = ($r['unidad_medida'] ?? '') === 'Porcentaje' ? '%' : '';
    $tri = [];
    foreach ([1, 2, 3, 4] as $n) {
        $en = (int)($r['t' . $n . '_estado'] ?? 0); $sn = (int)($r['t' . $n . '_sol'] ?? 0);
        $vn = $r['trimestre' . $n . '_seguimiento'] ?? null;
        $tri[$n] = [
            'v' => $tieneValor($vn) ? $fmt($vn) . $pc : '—',
            'e' => ($esLimite && $n <= 2) ? 'na' : ($en === 2 ? 'ap' : ($sn === 1 ? 'rev' : ($sn === 2 ? 'dev' : 'con'))),
        ];
    }
    $item = [
        'nombre' => trim((string)($r['formula_medicion'] ?? '')) !== '' ? $r['formula_medicion'] : ($r['nombre_indicador'] ?: $r['nombre_borrador']),
        'lmp' => implode(' · ', array_filter([$r['linea_codigo'], $r['motor_codigo'], $r['proyecto_codigo']])),
        'tri' => $tri, 'e' => $estado, 'txt' => $txt,
    ];

    $deps = array_values(array_unique(array_filter(array_map('trim', preg_split('/\s*[;,\/\n]\s*/u', (string)($r['responsable_formulacion'] ?? ''))))));
    foreach ($deps as $d) {
        $k = mb_strtolower($d, 'UTF-8');
        $nombreDep[$k] = $d;
        $totalDep[$k] = ($totalDep[$k] ?? 0) + 1;
        if ($estado === 'ap') $cats['aprobados'][$k][] = $item;
        else $cats['pendientes'][$k][] = $item;
        if ($estado === 'borrador') $cats['construccion'][$k][] = $item;
    }
}
foreach ($cats as $c => $grupos) {
    uksort($cats[$c], function ($a, $b) use ($nombreDep) { return strcoll($nombreDep[$a], $nombreDep[$b]); });
}
$pestanas = [
    'aprobados'    => ['Aprobados', 'Dependencias con indicadores que ya tienen aprobado el trimestre ' . $trimestre . '.'],
    'construccion' => ['En construcción', 'Con algún valor en el trimestre ' . $trimestre . ' que todavía no se ha enviado a revisión.'],
    'pendientes'   => ['Pendientes', 'Todo lo que aún no está aprobado en el trimestre ' . $trimestre . '. Filtra por estado.'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Estado por dependencia · <?php echo $tituloAnio; ?></title>
<style>
:root{--navy:#143163;--blue:#1D71B8;--orange:#D85819;--bg:#f3f5f9;--panel:#fff;--fg:#17203a;--muted:#5b6479;--line:#dde2ec;--soft:#e7ecf5;--blue-soft:#e4eef8;--ok:#1f8a4c;--ok-soft:#e3f4ea;--amber-soft:#fdf1d6;--amber-fg:#8a5a00}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;background:var(--bg);color:var(--fg);font-size:14px;-webkit-font-smoothing:antialiased}
.wrap{max-width:1100px;margin:0 auto;padding:20px 16px 48px;display:grid;gap:14px}
.hero{background:linear-gradient(135deg,var(--navy) 0%,#1D4E8F 100%);color:#fff;border-radius:16px;padding:18px 22px;display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap;align-items:flex-end}
.hero small{opacity:.8;letter-spacing:.08em;text-transform:uppercase;font-size:11px;font-weight:700}
.hero h1{font-size:22px;margin-top:3px;line-height:1.2}
.hero p{opacity:.85;margin-top:3px;font-size:13px}
.hero-acc{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.tsel{display:flex;gap:4px;background:rgba(255,255,255,.12);border-radius:10px;padding:3px}
.tsel a{color:#fff;text-decoration:none;font-weight:700;font-size:13px;padding:5px 11px;border-radius:8px;opacity:.8}
.tsel a:hover{opacity:1}.tsel a.on{background:#fff;color:var(--navy);opacity:1}
.btn{display:inline-flex;align-items:center;border:1px solid rgba(255,255,255,.4);color:#fff;background:rgba(255,255,255,.12);padding:7px 13px;border-radius:9px;font-weight:600;font-size:13px;text-decoration:none}
.btn:hover{background:rgba(255,255,255,.22)}
.tabs{display:flex;gap:6px;flex-wrap:wrap;border-bottom:2px solid var(--line)}
.tabs button{border:0;background:none;padding:10px 16px;font:inherit;font-weight:700;color:var(--muted);cursor:pointer;border-bottom:3px solid transparent;margin-bottom:-2px}
.tabs button span{display:inline-block;min-width:20px;padding:0 6px;border-radius:10px;background:#e8ebf3;color:var(--navy);font-size:.75rem;text-align:center;margin-left:4px}
.tabs button.on{color:var(--navy);border-bottom-color:var(--orange)}
.ayuda{color:var(--muted);font-size:13px;margin-bottom:10px}
.tools{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.tools input{font:inherit;border:1px solid var(--line);border-radius:9px;padding:8px 11px;min-height:38px;background:#fff;color:var(--fg);min-width:0;flex:1 1 260px}
.tgs{display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin-bottom:10px}
.tg{font:inherit;font-size:13px;font-weight:600;cursor:pointer;border:1px solid var(--line);background:#fff;color:var(--muted);border-radius:99px;padding:7px 14px}
.tg:hover{border-color:var(--blue)}
.tg[aria-pressed="true"]{background:var(--navy);border-color:var(--navy);color:#fff}
.lista{display:grid;gap:8px}
.tp[hidden],.acc[hidden],tr[hidden]{display:none!important}
.acc{border:1px solid var(--line);border-radius:10px;background:#fff}
.acc summary{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:10px 14px 10px 30px;cursor:pointer;list-style:none;position:relative}
.acc summary::-webkit-details-marker{display:none}
.acc summary::before{content:'▸';position:absolute;left:12px;color:var(--muted)}
.acc[open] summary::before{content:'▾'}
.acc[open] summary{border-bottom:1px solid var(--line)}
.acc-n{font-weight:700;color:var(--navy)}
.chip{padding:4px 11px;border-radius:99px;font-size:12px;font-weight:700;white-space:nowrap;background:var(--ok-soft);color:var(--ok)}
.chip small{font-weight:500;color:var(--muted)}
.c-construccion{background:var(--amber-soft);color:var(--amber-fg)}
.c-pendientes,.c-nada{background:var(--soft);color:var(--muted)}
.c-ap{background:var(--ok-soft);color:var(--ok)}.c-rev{background:var(--blue-soft);color:var(--blue)}.c-dev{background:#fde5e3;color:#a3201a}.c-borrador{background:var(--amber-soft);color:var(--amber-fg)}
.tw{overflow-x:auto;padding:0 6px 6px}
table{border-collapse:collapse;width:100%;min-width:760px}
th,td{padding:8px 10px;border-bottom:1px solid var(--line);text-align:left;vertical-align:middle}
th{font-size:11px;letter-spacing:.05em;text-transform:uppercase;color:var(--muted)}
tr:last-child td{border-bottom:0}
.lmp{display:inline-block;background:var(--blue-soft);color:var(--blue);font-weight:700;font-size:12px;border-radius:99px;padding:3px 10px;white-space:nowrap}
.tc{font-variant-numeric:tabular-nums;font-weight:700;white-space:nowrap}
.tc i{display:inline-block;width:8px;height:8px;border-radius:50%;margin-right:5px;background:#c3c9d6}
.tc.ap i{background:var(--ok)}.tc.rev i{background:#F9B233}.tc.dev i{background:var(--blue)}
.tc.na{color:var(--muted);font-weight:500}.tc.na i{display:none}
.vacio{text-align:center;color:var(--muted);padding:26px;background:var(--panel);border:1px solid var(--line);border-radius:12px}
.pie{color:var(--muted);font-size:12px;text-align:center}
</style>
</head>
<body>
<div class="wrap">
    <header class="hero">
        <div>
            <small>Reporte de seguimiento · FOR-DE-144</small>
            <h1>Estado por dependencia · T<?php echo $trimestre; ?></h1>
            <p><?php echo $tituloAnio; ?> · <?php echo $total; ?> indicadores · actualizado <?php echo date('d/m/Y H:i'); ?></p>
        </div>
        <div class="hero-acc">
            <nav class="tsel" aria-label="Trimestre">
                <?php foreach ([1, 2, 3, 4] as $tn): ?><a href="<?php echo $urlTrim($tn); ?>" class="<?php echo $tn === $trimestre ? 'on' : ''; ?>"<?php echo $tn === $trimestre ? ' aria-current="page"' : ''; ?>>T<?php echo $tn; ?></a><?php endforeach; ?>
            </nav>
            <a class="btn" href="<?php echo $basePath; ?>/FOR-DE-144?action=reporteSeguimiento&id=<?php echo (int)$formulario['id']; ?>&t=<?php echo $trimestre; ?>">‹ Reporte general</a>
        </div>
    </header>

    <nav class="tabs" role="tablist">
        <?php $primera = true; foreach ($pestanas as $c => $p): ?>
        <button type="button" role="tab" class="<?php echo $primera ? 'on' : ''; ?>" data-tp="tp-<?php echo $c; ?>" title="<?php echo $h($p[1]); ?>"><?php echo $h($p[0]); ?> <span><?php echo count($cats[$c]); ?></span></button>
        <?php $primera = false; endforeach; ?>
    </nav>

    <div class="tools"><input type="search" id="q" placeholder="Buscar dependencia…" aria-label="Buscar dependencia"></div>

    <?php $primera = true; foreach ($pestanas as $c => $p): ?>
    <section class="tp" id="tp-<?php echo $c; ?>" <?php echo $primera ? '' : 'hidden'; ?>>
        <p class="ayuda"><?php echo $h($p[1]); ?></p>
        <?php if ($c === 'pendientes' && $cats[$c]): ?>
        <div class="tgs" role="group" aria-label="Filtrar por estado">
            <button type="button" class="tg" data-e="dev" aria-pressed="false">Devueltos</button>
            <button type="button" class="tg" data-e="rev" aria-pressed="false">En revisión</button>
            <button type="button" class="tg" data-e="borrador" aria-pressed="false">En construcción</button>
            <button type="button" class="tg" data-e="nada" aria-pressed="false">Sin iniciar nada</button>
        </div>
        <?php endif; ?>
        <?php if (!$cats[$c]): ?>
        <p class="vacio">Ninguna dependencia tiene indicadores en este estado.</p>
        <?php else: ?>
        <div class="lista">
        <?php foreach ($cats[$c] as $k => $items): ?>
        <details class="acc" data-n="<?php echo $h(mb_strtolower($nombreDep[$k], 'UTF-8')); ?>">
            <summary>
                <span class="acc-n"><?php echo $h($nombreDep[$k]); ?></span>
                <span class="chip <?php echo $c === 'aprobados' ? '' : 'c-' . $c; ?>"><span class="n"><?php echo count($items); ?></span> <small>de <?php echo (int)$totalDep[$k]; ?> indicadores</small></span>
            </summary>
            <div class="tw"><table>
                <thead><tr><th>Indicador</th><th>Línea · Motor · Proyecto</th><th>T1</th><th>T2</th><th>T3</th><th>T4</th><th>Estado T<?php echo $trimestre; ?></th></tr></thead>
                <tbody>
                <?php foreach ($items as $it): ?>
                <tr data-e="<?php echo $h($it['e']); ?>">
                    <td><?php echo $h($it['nombre']); ?></td>
                    <td><span class="lmp"><?php echo $h($it['lmp'] ?: '—'); ?></span></td>
                    <?php foreach ([1, 2, 3, 4] as $tn): $cel = $it['tri'][$tn]; ?>
                    <td class="tc <?php echo $h($cel['e']); ?>"><i></i><?php echo $cel['e'] === 'na' ? 'No aplica' : $h($cel['v']); ?></td>
                    <?php endforeach; ?>
                    <td><span class="chip c-<?php echo $h($it['e']); ?>"><?php echo $h($it['txt']); ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </details>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
    <?php $primera = false; endforeach; ?>

    <p class="pie">Cada pestaña cuenta dependencias; dentro de cada una, «X de Y» son los indicadores de esa dependencia en este estado sobre el total que tiene.</p>
</div>
<script>
(function () {
    var botones = Array.prototype.slice.call(document.querySelectorAll('.tabs button'));
    var q = document.getElementById('q');
    var tgs = Array.prototype.slice.call(document.querySelectorAll('.tg'));

    // El número del chip de cada dependencia es el de filas visibles con los filtros activos
    function filtrar() {
        var t = q.value.trim().toLowerCase();
        var on = tgs.filter(function (b) { return b.getAttribute('aria-pressed') === 'true'; })[0];
        var e = on ? on.getAttribute('data-e') : '';
        Array.prototype.forEach.call(document.querySelectorAll('details.acc'), function (d) {
            var enPendientes = !!d.closest('#tp-pendientes');
            var visibles = 0;
            Array.prototype.forEach.call(d.querySelectorAll('tbody tr'), function (r) {
                var ok = !enPendientes || e === '' || r.getAttribute('data-e') === e;
                r.hidden = !ok;
                if (ok) visibles++;
            });
            var n = d.querySelector('.chip .n');
            if (n) n.textContent = visibles;
            d.hidden = (t !== '' && d.getAttribute('data-n').indexOf(t) === -1) || visibles === 0;
        });
    }

    botones.forEach(function (b) {
        b.addEventListener('click', function () {
            botones.forEach(function (x) { x.classList.toggle('on', x === b); });
            Array.prototype.forEach.call(document.querySelectorAll('.tp'), function (p) { p.hidden = p.id !== b.getAttribute('data-tp'); });
        });
    });
    q.addEventListener('input', filtrar);
    tgs.forEach(function (b) {
        b.addEventListener('click', function () {
            // Solo uno activo a la vez; volver a pulsar el activo lo quita
            var estaba = b.getAttribute('aria-pressed') === 'true';
            tgs.forEach(function (x) { x.setAttribute('aria-pressed', 'false'); });
            if (!estaba) b.setAttribute('aria-pressed', 'true');
            filtrar();
        });
    });
})();
</script>
</body>
</html>
