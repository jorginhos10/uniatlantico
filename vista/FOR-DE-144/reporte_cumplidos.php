<?php
// vista/FOR-DE-144/reporte_cumplidos.php — landing compacta: dependencias por estado (aprobados completos, en construcción, sin iniciar)
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
$tieneValor = function ($v) { return $v !== null && trim((string)$v) !== ''; };
$fmt = function ($v) { return rtrim(rtrim(number_format((float)$v, 2, ',', '.'), '0'), ','); };

// Categorías (mismo criterio del reporte general):
//  cumple       = aprobado, con algún valor y sin campos de seguimiento vacíos
//  construccion = sin solicitud de revisión ni aprobación, con algún valor reportado
//  sin          = sin solicitud ni aprobación y sin ningún valor reportado
//  pend         = (aparte, lo usa el filtro por semestre) todos los indicadores de cualquier estado con algún semestre vacío
$cats = ['cumple' => [], 'construccion' => [], 'sin' => [], 'pend' => []];
$conteo = ['cumple' => 0, 'construccion' => 0, 'sin' => 0, 'pend' => 0];
$totalDep = []; // clave => total de indicadores de la dependencia
$nombreDep = [];
$total = 0;
foreach ($indicadores as $r) {
    $total++;
    $esLimite = ($r['tipo_medicion'] ?? '') === 'Límite';
    $s1 = !$esLimite && $tieneValor($r['semestre1_seguimiento'] ?? null);
    $s2 = $tieneValor($r['semestre2_seguimiento'] ?? null);
    $algunValor = $s1 || $s2;
    $completo = $s2 && ($esLimite || $s1);
    $aprobado = (int)($r['estado_seguimiento'] ?? 0) === 2;
    $sol = (int)($r['solicitud_estado_seguimiento'] ?? 0);

    $deps = array_values(array_unique(array_filter(array_map('trim', preg_split('/\s*[;,\/\n]\s*/u', (string)($r['responsable_formulacion'] ?? ''))))));
    foreach ($deps as $d) {
        $k = mb_strtolower($d, 'UTF-8');
        $nombreDep[$k] = $d;
        $totalDep[$k] = ($totalDep[$k] ?? 0) + 1;
    }

    if ($aprobado) { $cat = ($algunValor && $completo) ? 'cumple' : null; $est = 'Aprobado'; }
    elseif ($sol === 0) { $cat = $algunValor ? 'construccion' : 'sin'; $est = $algunValor ? 'En construcción' : 'Sin iniciar'; }
    else { $cat = null; $est = $sol === 1 ? 'En revisión' : 'Devuelto'; } // en revisión o devuelto: no entra en las pestañas
    $pend = (!$esLimite && !$s1) || !$s2; // le falta algún semestre del seguimiento, sea cual sea su estado
    if ($cat === null && !$pend) continue;

    $pc = ($r['unidad_medida'] ?? '') === 'Porcentaje' ? '%' : '';
    $item = [
        'nombre' => trim((string)($r['formula_medicion'] ?? '')) !== '' ? $r['formula_medicion'] : ($r['nombre_indicador'] ?: $r['nombre_borrador']),
        'lmp' => implode(' · ', array_filter([$r['linea_codigo'], $r['motor_codigo'], $r['proyecto_codigo']])),
        'v1' => $esLimite ? 'No aplica' : ($s1 ? $fmt($r['semestre1_seguimiento']) . $pc : '—'),
        'v2' => $s2 ? $fmt($r['semestre2_seguimiento']) . $pc : '—',
        'f1' => !$esLimite && !$s1, 'f2' => !$s2, // semestres sin iniciar (en Límite no aplica el 1)
        'est' => $est,
        'pct' => $tieneValor($r['porcentaje_avance'] ?? null) && $algunValor ? $fmt(min(100, max(0, (float)$r['porcentaje_avance']))) . '%' : '—',
    ];
    if ($cat !== null) {
        $conteo[$cat]++;
        foreach ($deps as $d) $cats[$cat][mb_strtolower($d, 'UTF-8')][] = $item;
    }
    if ($pend) {
        $conteo['pend']++;
        foreach ($deps as $d) $cats['pend'][mb_strtolower($d, 'UTF-8')][] = $item;
    }
}
foreach ($cats as $c => $grupos) {
    uksort($cats[$c], function ($a, $b) use ($nombreDep) { return strcoll($nombreDep[$a], $nombreDep[$b]); });
}
$pestanas = [
    'cumple' => ['Cumplimiento aprobado', 'Indicadores aprobados por Planeación, con valor reportado y sin campos vacíos.'],
    'construccion' => ['En construcción', 'Indicadores con algún valor reportado que todavía no se han enviado a revisión.'],
    'sin' => ['Sin iniciar', 'Indicadores que no tienen ningún valor reportado ni se han enviado a revisión.'],
];
// Sección extra (sin pestaña): aparece al elegir un semestre en el filtro
$secciones = $pestanas + ['pend' => ['Sin iniciar por semestre', 'Todos los indicadores, en cualquier estado, que no tienen valor en el semestre elegido.']];
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
.btn{display:inline-flex;align-items:center;border:1px solid rgba(255,255,255,.4);color:#fff;background:rgba(255,255,255,.12);padding:7px 13px;border-radius:9px;font-weight:600;font-size:13px;text-decoration:none}
.btn:hover{background:rgba(255,255,255,.22)}
.tabs{display:flex;gap:6px;flex-wrap:wrap;border-bottom:2px solid var(--line)}
.tabs button{border:0;background:none;padding:10px 16px;font:inherit;font-weight:700;color:var(--muted);cursor:pointer;border-bottom:3px solid transparent;margin-bottom:-2px}
.tabs button span{display:inline-block;min-width:20px;padding:0 6px;border-radius:10px;background:#e8ebf3;color:var(--navy);font-size:.75rem;text-align:center;margin-left:4px}
.tabs button.on{color:var(--navy);border-bottom-color:var(--orange)}
.ayuda{color:var(--muted);font-size:13px;margin-bottom:10px}
.tools{display:flex;gap:10px;flex-wrap:wrap}
.tools input,.tools select{font:inherit;border:1px solid var(--line);border-radius:9px;padding:8px 11px;min-height:38px;background:#fff;color:var(--fg);min-width:0}
.tools input{flex:1 1 260px}
.nd{color:var(--muted)}
tr[hidden]{display:none}
.lista{display:grid;gap:8px}
.tp[hidden],.acc[hidden]{display:none!important}
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
.c-sin,.c-pend{background:var(--soft);color:var(--muted)}
.tw{overflow-x:auto;padding:0 6px 6px}
table{border-collapse:collapse;width:100%;min-width:620px}
th,td{padding:8px 10px;border-bottom:1px solid var(--line);text-align:left;vertical-align:middle}
th{font-size:11px;letter-spacing:.05em;text-transform:uppercase;color:var(--muted)}
tr:last-child td{border-bottom:0}
.lmp{display:inline-block;background:var(--blue-soft);color:var(--blue);font-weight:700;font-size:12px;border-radius:99px;padding:3px 10px;white-space:nowrap}
.sv{font-variant-numeric:tabular-nums;font-weight:700;white-space:nowrap}
.vacio{text-align:center;color:var(--muted);padding:26px;background:var(--panel);border:1px solid var(--line);border-radius:12px}
.pie{color:var(--muted);font-size:12px;text-align:center}
</style>
</head>
<body>
<div class="wrap">
    <header class="hero">
        <div>
            <small>Reporte de seguimiento · FOR-DE-144</small>
            <h1>Estado por dependencia</h1>
            <p><?php echo $tituloAnio; ?> · <?php echo $total; ?> indicadores · actualizado <?php echo date('d/m/Y H:i'); ?></p>
        </div>
        <a class="btn" href="<?php echo $basePath; ?>/FOR-DE-144?action=reporteSeguimiento&id=<?php echo (int)$formulario['id']; ?>">‹ Reporte general</a>
    </header>

    <nav class="tabs" role="tablist">
        <?php $primera = true; foreach ($pestanas as $c => $p): ?>
        <button type="button" role="tab" class="<?php echo $primera ? 'on' : ''; ?>" data-tp="tp-<?php echo $c; ?>"><?php echo $h($p[0]); ?> <span><?php echo $conteo[$c]; ?></span></button>
        <?php $primera = false; endforeach; ?>
    </nav>

    <div class="tools">
        <input type="search" id="q" placeholder="Buscar dependencia…" aria-label="Buscar dependencia">
        <select id="sem" aria-label="Filtrar por semestre sin iniciar">
            <option value="">Todos los indicadores</option>
            <option value="1">Sin iniciar semestre 1</option>
            <option value="2">Sin iniciar semestre 2</option>
            <option value="12">Sin iniciar ninguno de los dos semestres</option>
        </select>
    </div>

    <?php $primera = true; foreach ($secciones as $c => $p): ?>
    <section class="tp" id="tp-<?php echo $c; ?>" <?php echo $primera ? '' : 'hidden'; ?>>
        <p class="ayuda"><?php echo $h($p[1]); ?> El número de cada dependencia es la cantidad de indicadores en este estado, sobre el total que tiene asignados.</p>
        <?php if (!$cats[$c]): ?>
        <p class="vacio">Ninguna dependencia tiene indicadores en este estado.</p>
        <?php else: ?>
        <div class="lista">
        <?php foreach ($cats[$c] as $k => $items): ?>
        <details class="acc" data-n="<?php echo $h(mb_strtolower($nombreDep[$k], 'UTF-8')); ?>">
            <summary>
                <span class="acc-n"><?php echo $h($nombreDep[$k]); ?></span>
                <span class="chip <?php echo $c === 'cumple' ? '' : 'c-' . $c; ?>"><?php echo count($items); ?> <small>de <?php echo (int)$totalDep[$k]; ?> indicadores</small></span>
            </summary>
            <div class="tw"><table>
                <thead><tr><th>Indicador</th><th>Línea · Motor · Proyecto</th><th>Semestre 1</th><th>Semestre 2</th><th>Estado</th><th>Sin iniciar</th><th>Avance</th></tr></thead>
                <tbody>
                <?php foreach ($items as $it): ?>
                <tr data-f1="<?php echo $it['f1'] ? 1 : 0; ?>" data-f2="<?php echo $it['f2'] ? 1 : 0; ?>">
                    <td><?php echo $h($it['nombre']); ?></td>
                    <td><span class="lmp"><?php echo $h($it['lmp'] ?: '—'); ?></span></td>
                    <td class="sv"><?php echo $h($it['v1']); ?></td>
                    <td class="sv"><?php echo $h($it['v2']); ?></td>
                    <td><?php echo $h($it['est']); ?></td>
                    <td>
                        <?php if ($it['f1']): ?><span class="chip c-sin">Semestre 1</span> <?php endif; ?>
                        <?php if ($it['f2']): ?><span class="chip c-sin">Semestre 2</span><?php endif; ?>
                        <?php if (!$it['f1'] && !$it['f2']): ?><span class="nd">—</span><?php endif; ?>
                    </td>
                    <td class="sv"><?php echo $h($it['pct']); ?></td>
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

    <p class="pie">Los indicadores en revisión o devueltos no aparecen aquí; se ven en el reporte general.</p>
</div>
<script>
(function () {
    var botones = Array.prototype.slice.call(document.querySelectorAll('.tabs button'));
    botones.forEach(function (b) {
        b.addEventListener('click', function () {
            botones.forEach(function (x) { x.classList.toggle('on', x === b); });
            Array.prototype.forEach.call(document.querySelectorAll('.tp'), function (p) { p.hidden = p.id !== b.getAttribute('data-tp'); });
            if (sem.value) { sem.value = ''; filtrar(); } // elegir una pestaña quita el filtro de semestre
        });
    });
    var q = document.getElementById('q'), sem = document.getElementById('sem');
    // Filtra por nombre de dependencia y por semestre sin iniciar; oculta las dependencias que se quedan sin filas
    function filtrar() {
        var t = q.value.trim().toLowerCase(), s = sem.value;
        // Con un semestre elegido se muestra la lista de todos los estados; sin él, vuelve la pestaña activa
        var activa = document.querySelector('.tabs button.on').getAttribute('data-tp');
        Array.prototype.forEach.call(document.querySelectorAll('.tp'), function (p) { p.hidden = p.id !== (s === '' ? activa : 'tp-pend'); });
        document.querySelector('.tabs').style.opacity = s === '' ? '' : '.45';
        Array.prototype.forEach.call(document.querySelectorAll('details.acc'), function (d) {
            var visibles = 0;
            Array.prototype.forEach.call(d.querySelectorAll('tbody tr'), function (r) {
                var f1 = r.getAttribute('data-f1') === '1', f2 = r.getAttribute('data-f2') === '1';
                var ok = s === '' || (s === '1' && f1) || (s === '2' && f2) || (s === '12' && f1 && f2);
                r.hidden = !ok;
                if (ok) visibles++;
            });
            d.hidden = (t !== '' && d.getAttribute('data-n').indexOf(t) === -1) || visibles === 0;
        });
    }
    q.addEventListener('input', filtrar);
    sem.addEventListener('change', filtrar);
})();
</script>
</body>
</html>
