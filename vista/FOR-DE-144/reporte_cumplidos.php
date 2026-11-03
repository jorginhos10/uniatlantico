<?php
// vista/FOR-DE-144/reporte_cumplidos.php — landing compacta: dependencias con indicadores reportados, completos y aprobados
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

// Un indicador cumple si: tiene algún valor reportado, no deja campos de seguimiento vacíos y su seguimiento está aprobado
$total = 0; $cumplen = 0;
$porDep = []; // clave => ['nombre', 'total', 'items' => []]
foreach ($indicadores as $r) {
    $total++;
    $esLimite = ($r['tipo_medicion'] ?? '') === 'Límite';
    $s1 = $tieneValor($r['semestre1_seguimiento'] ?? null);
    $s2 = $tieneValor($r['semestre2_seguimiento'] ?? null);
    $algunValor = $s1 || $s2;
    $completo = $s2 && ($esLimite || $s1); // los de tipo Límite no tienen semestre 1
    $aprobado = (int)($r['estado_seguimiento'] ?? 0) === 2;

    $deps = array_values(array_unique(array_filter(array_map('trim', preg_split('/\s*[;,\/\n]\s*/u', (string)($r['responsable_formulacion'] ?? ''))))));
    foreach ($deps as $d) {
        $k = mb_strtolower($d, 'UTF-8');
        if (!isset($porDep[$k])) $porDep[$k] = ['nombre' => $d, 'total' => 0, 'items' => []];
        $porDep[$k]['total']++;
    }
    if (!($algunValor && $completo && $aprobado)) continue;

    $cumplen++;
    $pc = ($r['unidad_medida'] ?? '') === 'Porcentaje' ? '%' : '';
    $item = [
        'nombre' => trim((string)($r['formula_medicion'] ?? '')) !== '' ? $r['formula_medicion'] : ($r['nombre_indicador'] ?: $r['nombre_borrador']),
        'lmp' => implode(' · ', array_filter([$r['linea_codigo'], $r['motor_codigo'], $r['proyecto_codigo']])),
        'v1' => $esLimite ? 'No aplica' : $fmt($r['semestre1_seguimiento']) . $pc,
        'v2' => $fmt($r['semestre2_seguimiento']) . $pc,
        'pct' => $tieneValor($r['porcentaje_avance'] ?? null) ? $fmt(min(100, max(0, (float)$r['porcentaje_avance']))) . '%' : '—',
    ];
    foreach ($deps as $d) $porDep[mb_strtolower($d, 'UTF-8')]['items'][] = $item;
}
// Solo las dependencias que tienen al menos un indicador que cumple
$porDep = array_filter($porDep, function ($g) { return $g['items']; });
uasort($porDep, function ($a, $b) { return strcoll($a['nombre'], $b['nombre']); });
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cumplimiento aprobado · <?php echo $tituloAnio; ?></title>
<style>
:root{--navy:#143163;--blue:#1D71B8;--orange:#D85819;--bg:#f3f5f9;--panel:#fff;--fg:#17203a;--muted:#5b6479;--line:#dde2ec;--soft:#e7ecf5;--blue-soft:#e4eef8;--ok:#1f8a4c;--ok-soft:#e3f4ea}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;background:var(--bg);color:var(--fg);font-size:14px;-webkit-font-smoothing:antialiased}
.wrap{max-width:1100px;margin:0 auto;padding:20px 16px 48px;display:grid;gap:14px}
.hero{background:linear-gradient(135deg,var(--navy) 0%,#1D4E8F 100%);color:#fff;border-radius:16px;padding:18px 22px;display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap;align-items:flex-end}
.hero small{opacity:.8;letter-spacing:.08em;text-transform:uppercase;font-size:11px;font-weight:700}
.hero h1{font-size:22px;margin-top:3px;line-height:1.2}
.hero p{opacity:.85;margin-top:3px;font-size:13px}
.hero-acc{display:flex;gap:8px;flex-wrap:wrap}
.btn{display:inline-flex;align-items:center;border:1px solid rgba(255,255,255,.4);color:#fff;background:rgba(255,255,255,.12);padding:7px 13px;border-radius:9px;font-weight:600;font-size:13px;text-decoration:none}
.btn:hover{background:rgba(255,255,255,.22)}
.res{display:flex;gap:10px;flex-wrap:wrap;align-items:center;background:var(--panel);border:1px solid var(--line);border-radius:12px;padding:12px 16px}
.res b{font-size:22px;font-variant-numeric:tabular-nums}
.res span{color:var(--muted)}
.crit{margin-left:auto;display:flex;gap:6px;flex-wrap:wrap}
.crit i{font-style:normal;font-size:12px;font-weight:700;background:var(--ok-soft);color:var(--ok);border-radius:99px;padding:4px 10px}
.tools input{font:inherit;width:100%;border:1px solid var(--line);border-radius:9px;padding:8px 11px;min-height:38px;background:#fff;color:var(--fg)}
.acc{border:1px solid var(--line);border-radius:10px;background:#fff}
.acc summary{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:10px 14px 10px 30px;cursor:pointer;list-style:none;position:relative}
.acc summary::-webkit-details-marker{display:none}
.acc summary::before{content:'▸';position:absolute;left:12px;color:var(--muted)}
.acc[open] summary::before{content:'▾'}
.acc[open] summary{border-bottom:1px solid var(--line)}
.acc-n{font-weight:700;color:var(--navy)}
.chip{padding:4px 11px;border-radius:99px;font-size:12px;font-weight:700;background:var(--ok-soft);color:var(--ok);white-space:nowrap}
.chip small{font-weight:500;color:var(--muted)}
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
            <h1>Cumplimiento aprobado</h1>
            <p><?php echo $tituloAnio; ?> · actualizado <?php echo date('d/m/Y H:i'); ?></p>
        </div>
        <div class="hero-acc">
            <a class="btn" href="<?php echo $basePath; ?>/FOR-DE-144?action=reporteSeguimiento&id=<?php echo (int)$formulario['id']; ?>">‹ Reporte general</a>
        </div>
    </header>

    <section class="res">
        <div><b><?php echo count($porDep); ?></b> <span>dependencias</span></div>
        <div><b><?php echo $cumplen; ?></b> <span>de <?php echo $total; ?> indicadores</span></div>
        <div class="crit"><i>Con valor reportado</i><i>Sin campos vacíos</i><i>Aprobado</i></div>
    </section>

    <?php if ($porDep): ?>
    <div class="tools"><input type="search" id="q" placeholder="Buscar dependencia…" aria-label="Buscar dependencia"></div>
    <section id="lista" style="display:grid;gap:8px">
        <?php foreach ($porDep as $g): ?>
        <details class="acc" data-n="<?php echo $h(mb_strtolower($g['nombre'], 'UTF-8')); ?>">
            <summary>
                <span class="acc-n"><?php echo $h($g['nombre']); ?></span>
                <span class="chip"><?php echo count($g['items']); ?> <small>de <?php echo (int)$g['total']; ?> indicadores</small></span>
            </summary>
            <div class="tw"><table>
                <thead><tr><th>Indicador</th><th>Línea · Motor · Proyecto</th><th>Semestre 1</th><th>Semestre 2</th><th>Avance</th></tr></thead>
                <tbody>
                <?php foreach ($g['items'] as $it): ?>
                <tr>
                    <td><?php echo $h($it['nombre']); ?></td>
                    <td><span class="lmp"><?php echo $h($it['lmp'] ?: '—'); ?></span></td>
                    <td class="sv"><?php echo $h($it['v1']); ?></td>
                    <td class="sv"><?php echo $h($it['v2']); ?></td>
                    <td class="sv"><?php echo $h($it['pct']); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </details>
        <?php endforeach; ?>
    </section>
    <?php else: ?>
    <p class="vacio">Todavía ninguna dependencia tiene indicadores con valor reportado, sin campos vacíos y aprobados.</p>
    <?php endif; ?>

    <p class="pie">El número de cada dependencia es la cantidad de indicadores que cumplen los tres criterios, sobre el total que tiene asignados.</p>
</div>
<script>
(function () {
    var q = document.getElementById('q');
    if (!q) return;
    q.addEventListener('input', function () {
        var t = q.value.trim().toLowerCase();
        Array.prototype.forEach.call(document.querySelectorAll('#lista details'), function (d) {
            d.hidden = t !== '' && d.getAttribute('data-n').indexOf(t) === -1;
        });
    });
})();
</script>
</body>
</html>
