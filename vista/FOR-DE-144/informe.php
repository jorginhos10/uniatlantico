<?php
// vista/FOR-DE-144/informe.php — standalone dark dashboard
if (!isset($formulario)) {
    header('Location: ' . Config::getBasePath() . '/FOR-DE-144');
    exit;
}
// If informe failed, use safe empty structure so the page still renders
if (!isset($informe) || $informe === null) {
    $informe = ['global_cumplimiento' => 0, 'lineas' => [], 'dependencias' => []];
}
$anio    = htmlspecialchars($formulario['anio']    ?? date('Y'));
$tituloF = htmlspecialchars($formulario['titulo']  ?? '');
$basePath = Config::getBasePath();
$global  = number_format((float)($informe['global_cumplimiento'] ?? 0), 2);
$dbError = $informe['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Informe · <?php echo $tituloF; ?> · <?php echo $anio; ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{
    font-family:-apple-system,BlinkMacSystemFont,'SF Pro Display','Helvetica Neue',Arial,sans-serif;
    background:#0a1628;
    color:#fff;
    min-height:100vh;
    -webkit-font-smoothing:antialiased;
}
.dashboard{max-width:1280px;margin:0 auto;padding:28px 20px 60px}

/* ── Header ── */
.header{
    display:flex;align-items:center;justify-content:space-between;
    flex-wrap:wrap;gap:14px;
    background:rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.1);
    border-radius:18px;
    padding:20px 26px;
    margin-bottom:24px;
    backdrop-filter:blur(10px);
}
.header h1{
    font-size:1.35rem;font-weight:800;letter-spacing:-.3px;
    display:flex;align-items:center;gap:10px;
}
.header h1 i{color:#f39c12;font-size:1.1rem}
.header h1 span{color:rgba(255,255,255,.55);font-size:.9rem;font-weight:400;margin-left:6px}
.header-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}

.btn-back{
    display:inline-flex;align-items:center;gap:7px;
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.14);
    color:#fff;padding:8px 16px;border-radius:20px;
    text-decoration:none;font-size:13px;font-weight:600;
    transition:background .2s;
}
.btn-back:hover{background:rgba(255,255,255,.15);color:#fff}

.btn-dep{
    display:inline-flex;align-items:center;gap:7px;
    background:linear-gradient(135deg,#e67e22,#f39c12);
    border:none;color:#fff;padding:8px 18px;
    border-radius:20px;font-size:13px;font-weight:700;
    cursor:pointer;
    box-shadow:0 4px 14px rgba(230,126,34,.35);
    transition:all .2s;
}
.btn-dep:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(230,126,34,.45)}

/* ── Global cumplimiento ── */
.cumplimiento-global{
    background:rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.1);
    border-radius:18px;padding:28px 32px;
    text-align:center;margin-bottom:24px;
}
.global-label{font-size:.95rem;color:rgba(255,255,255,.65);margin-bottom:10px}
.global-label span{color:#f39c12;font-weight:700}
.global-number{
    font-size:4.2rem;font-weight:900;letter-spacing:-3px;
    background:linear-gradient(135deg,#f39c12,#e67e22);
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;
    background-clip:text;
}
.global-sub{font-size:.8rem;color:rgba(255,255,255,.35);margin-top:6px;text-transform:uppercase;letter-spacing:.5px}

/* ── Lineas grid ── */
.lineas-grid{display:flex;flex-direction:column;gap:14px;margin-bottom:28px}

.linea-card{
    background:rgba(255,255,255,.04);
    border:1px solid rgba(255,255,255,.09);
    border-radius:16px;overflow:hidden;
    transition:border-color .25s;
}
.linea-card:hover{border-color:rgba(243,156,18,.35)}

.linea-header{
    display:grid;
    grid-template-columns:1fr auto auto auto;
    align-items:center;
    gap:16px;
    padding:18px 22px;
    cursor:pointer;
}

.linea-titulo h2{font-size:1rem;font-weight:700;color:#fff;margin-bottom:6px;line-height:1.35}
.linea-badge{
    display:inline-block;padding:3px 11px;border-radius:20px;
    font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;
    color:#fff;
}

.progress-container{display:flex;align-items:center;gap:10px;min-width:180px}
.progress-bar-bg{flex:1;height:8px;background:rgba(255,255,255,.1);border-radius:4px;overflow:hidden}
.progress-fill{
    height:100%;
    background:linear-gradient(90deg,#f39c12,#e67e22);
    border-radius:4px;width:0%;
    transition:width 1.1s cubic-bezier(.4,0,.2,1);
}
.porcentaje-num{font-size:1.15rem;font-weight:800;color:#f39c12;min-width:58px;text-align:right}

.eye-icon{
    font-size:1.1rem;cursor:pointer;
    width:36px;height:36px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    background:rgba(255,255,255,.05);
    transition:background .2s,color .2s;
    border:1px solid rgba(255,255,255,.08);
}
.eye-icon:hover{background:rgba(243,156,18,.18);color:#f39c12}

.expand-icon{
    font-size:.85rem;cursor:pointer;
    width:36px;height:36px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    background:rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.08);
    transition:background .2s,transform .35s;
    color:rgba(255,255,255,.6);
}
.expand-icon:hover{background:rgba(255,255,255,.1)}
.expand-icon.open{transform:rotate(180deg);background:rgba(243,156,18,.15);color:#f39c12}

/* ── Detalle expandido ── */
.detalle-contenido{
    max-height:0;overflow:hidden;
    transition:max-height .45s ease;
    border-top:0px solid rgba(255,255,255,.08);
}
.detalle-contenido.expandido{
    max-height:3000px;
    border-top-width:1px;
}

.micro-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
    gap:12px;
    padding:18px 22px;
}

.motor-card{
    background:rgba(255,255,255,.04);
    border:1px solid rgba(255,255,255,.07);
    border-radius:12px;
    padding:14px 16px;
}
.motor-titulo{
    font-size:.85rem;font-weight:700;color:#f39c12;
    margin-bottom:12px;display:flex;align-items:flex-start;
    gap:8px;flex-wrap:wrap;line-height:1.35;
}
.motor-titulo i{margin-top:2px;flex-shrink:0}
.motor-pct{
    margin-left:auto;font-size:.9rem;font-weight:800;
    white-space:nowrap;flex-shrink:0;
}

.proyecto-item{
    display:flex;justify-content:space-between;align-items:flex-start;
    gap:8px;padding:7px 0;
    border-bottom:1px solid rgba(255,255,255,.05);
}
.proyecto-item:last-child{border-bottom:none}
.proyecto-nombre{font-size:.78rem;color:rgba(255,255,255,.72);flex:1;line-height:1.4}
.proyecto-valor{font-size:.82rem;font-weight:700;white-space:nowrap}
.pv-alto{color:#34C759}
.pv-medio{color:#FF9500}
.pv-bajo{color:#FF3B30}

/* ── Modals ── */
.modal-overlay{
    display:none;position:fixed;inset:0;
    background:rgba(0,0,0,.75);
    z-index:99999;align-items:center;justify-content:center;
    backdrop-filter:blur(6px);
    padding:20px;
}
.modal-overlay.active{display:flex}

.modal-content{
    background:#0d2135;
    border:1px solid rgba(255,255,255,.12);
    border-radius:20px;padding:28px;
    width:100%;max-width:680px;
    max-height:88vh;overflow-y:auto;
    position:relative;
}
.modal-content.dep-modal{max-width:920px}

.modal-close{
    position:absolute;top:14px;right:16px;
    background:rgba(255,255,255,.08);border:none;
    color:rgba(255,255,255,.7);font-size:1.1rem;
    width:30px;height:30px;border-radius:50%;
    cursor:pointer;display:flex;align-items:center;justify-content:center;
    transition:background .2s,color .2s;
}
.modal-close:hover{background:rgba(255,59,48,.25);color:#ff3b30}

.modal-title{font-size:1.05rem;font-weight:700;color:#fff;margin-bottom:20px;padding-right:32px;line-height:1.4}
.chart-container{position:relative;height:240px;margin-bottom:20px}

.modal-stats{display:flex;flex-direction:column;gap:6px;margin-top:8px}
.stat-item{
    display:flex;justify-content:space-between;align-items:center;
    padding:9px 13px;background:rgba(255,255,255,.04);border-radius:8px;
}
.stat-label{font-size:.82rem;color:rgba(255,255,255,.65);flex:1;margin-right:12px}
.stat-value{font-weight:700;font-size:.9rem;color:#f39c12;white-space:nowrap}

/* ── Dependencias table ── */
.dep-table{width:100%;border-collapse:collapse;font-size:.85rem;margin-top:10px}
.dep-table th{
    background:rgba(255,255,255,.08);color:rgba(255,255,255,.9);
    padding:11px 14px;font-weight:700;text-align:center;font-size:.75rem;
    text-transform:uppercase;letter-spacing:.4px;
}
.dep-table th:first-child{text-align:left;border-radius:8px 0 0 0}
.dep-table th:last-child{border-radius:0 8px 0 0}
.dep-table td{padding:10px 14px;border-bottom:1px solid rgba(255,255,255,.05);color:rgba(255,255,255,.82)}
.dep-table td:first-child{color:#fff;font-weight:500}
.dep-table td:not(:first-child){text-align:center}
.dep-table tr:hover td{background:rgba(255,255,255,.03)}
.dep-table .total-row td{border-top:2px solid #f39c12;font-weight:800;color:#f39c12}

.cb{display:inline-block;padding:3px 12px;border-radius:20px;font-weight:700;font-size:.8rem}
.cb-alto{background:rgba(52,199,89,.2);color:#34C759}
.cb-medio{background:rgba(255,149,0,.2);color:#FF9500}
.cb-bajo{background:rgba(255,59,48,.2);color:#FF3B30}

/* ── Footer note ── */
.aclaracion{text-align:center;color:rgba(255,255,255,.3);font-size:.78rem;margin-top:24px;line-height:1.6}

/* ── Responsive ── */
@media(max-width:768px){
    .linea-header{grid-template-columns:1fr auto auto;gap:10px}
    .progress-container{display:none}
    .header h1{font-size:1.05rem}
    .global-number{font-size:3rem}
    .micro-grid{grid-template-columns:1fr}
}
</style>
</head>
<body>
<div class="dashboard">

    <!-- ── Header ── -->
    <div class="header">
        <h1>
            <i class="fas fa-chart-bar"></i>
            Líneas Estratégicas · Cumplimiento <?php echo $anio; ?>
            <span>/ <?php echo $tituloF; ?></span>
        </h1>
        <div class="header-actions">
            <button class="btn-dep" id="depBtn">
                <i class="fas fa-university"></i> Estadísticas por Dependencia
            </button>
            <a href="<?php echo $basePath; ?>/FOR-DE-144" class="btn-back">
                <i class="fas fa-arrow-left" style="font-size:11px"></i> Volver
            </a>
        </div>
    </div>

    <!-- ── Global cumplimiento ── -->
    <div class="cumplimiento-global">
        <div class="global-label">
            <i class="fas fa-bullseye" style="color:#f39c12;margin-right:6px"></i>
            Cumplimiento integral del Plan — <span>promedio de todas las líneas estratégicas</span>
        </div>
        <div class="global-number" id="globalNum"><?php echo $global; ?>%</div>
        <div class="global-sub">
            <?php
                $n = count($informe['lineas']);
                echo $n . ' línea' . ($n != 1 ? 's' : '') . ' estratégica' . ($n != 1 ? 's' : '') . ' · ' . $anio;
            ?>
        </div>
    </div>

    <!-- ── DB error banner (only shows when SQL fails) ── -->
    <?php if ($dbError): ?>
    <div style="background:rgba(255,59,48,.15);border:1px solid rgba(255,59,48,.35);border-radius:12px;padding:16px 20px;margin-bottom:20px;color:#ff6b6b;font-size:.85rem">
        <strong><i class="fas fa-exclamation-triangle" style="margin-right:8px"></i>Error al cargar datos:</strong>
        <code style="display:block;margin-top:6px;font-size:.78rem;color:#ffaaaa;word-break:break-all"><?php echo htmlspecialchars($dbError); ?></code>
        <p style="margin-top:8px;color:rgba(255,255,255,.6);font-size:.78rem">Verifica que la tabla <code>formulacion_144</code> tenga las columnas <code>porcentaje_avance</code>, <code>linea_estrategica</code>, <code>motor_desarrollo</code> y <code>proyecto</code>.</p>
    </div>
    <?php endif; ?>

    <!-- ── Lineas grid ── -->
    <div class="lineas-grid" id="lineasContainer"></div>

    <div class="aclaracion">
        <i class="fas fa-eye" style="margin-right:4px"></i> Clic en el ojo para ver gráfica por motores &nbsp;·&nbsp;
        <i class="fas fa-chevron-down" style="margin-right:4px"></i> para expandir motores y proyectos<br>
        * El porcentaje de avance corresponde al valor registrado por cada dependencia en el seguimiento
    </div>
</div>

<!-- ── Modal gráfica ── -->
<div class="modal-overlay" id="chartModal">
    <div class="modal-content">
        <button class="modal-close" id="chartClose"><i class="fas fa-times"></i></button>
        <div class="modal-title" id="chartTitle">Línea Estratégica</div>
        <div class="chart-container">
            <canvas id="barChart"></canvas>
        </div>
        <div class="modal-stats" id="chartStats"></div>
    </div>
</div>

<!-- ── Modal dependencias ── -->
<div class="modal-overlay" id="depModal">
    <div class="modal-content dep-modal">
        <button class="modal-close" id="depClose"><i class="fas fa-times"></i></button>
        <div class="modal-title">
            <i class="fas fa-university" style="color:#f39c12;margin-right:8px"></i>
            Estadísticas por Dependencia · <?php echo $anio; ?>
        </div>
        <div style="overflow-x:auto;max-height:60vh;overflow-y:auto">
            <table class="dep-table">
                <thead>
                    <tr>
                        <th>Dependencia</th>
                        <th>Total Indicadores</th>
                        <th>Indicadores ≥80%</th>
                        <th>Cumplimiento (%)</th>
                    </tr>
                </thead>
                <tbody id="depBody"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function () {
'use strict';

/* ── Data from PHP ── */
const lineasData   = <?php echo json_encode($informe['lineas'],       JSON_UNESCAPED_UNICODE); ?>;
const depData      = <?php echo json_encode($informe['dependencias'],  JSON_UNESCAPED_UNICODE); ?>;

/* ── Color palette ── */
const LINEA_COLORS = ['#007AFF','#AF52DE','#34C759','#FF9500','#FF3B30','#32ADE6','#5856D6','#FF2D55'];
const CHART_COLORS = ['#f39c12','#e67e22','#d35400','#f1c40f','#e74c3c','#3498db','#2ecc71','#9b59b6'];

function pctColor(p) {
    if (p >= 80) return '#34C759';
    if (p >= 60) return '#FF9500';
    return '#FF3B30';
}
function cbClass(p) {
    if (p >= 80) return 'cb-alto';
    if (p >= 60) return 'cb-medio';
    return 'cb-bajo';
}
function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

/* ── Render lineas ── */
const container = document.getElementById('lineasContainer');

function renderLineas() {
    if (!lineasData.length) {
        container.innerHTML = '<div style="text-align:center;padding:60px;color:rgba(255,255,255,.4)"><i class="fas fa-inbox fa-2x" style="margin-bottom:14px;display:block"></i>No se encontraron líneas estratégicas con datos registrados.</div>';
        return;
    }

    lineasData.forEach(function(linea, idx) {
        const color = LINEA_COLORS[idx % LINEA_COLORS.length];
        const pct   = parseFloat(linea.cumplimiento).toFixed(2);

        const card = document.createElement('div');
        card.className = 'linea-card';
        card.id = 'linea-card-' + idx;

        card.innerHTML =
            '<div class="linea-header" id="lh-' + idx + '">' +
                '<div class="linea-titulo">' +
                    '<h2>' + esc(linea.titulo) + '</h2>' +
                    '<span class="linea-badge" style="background:' + color + '">' + esc(linea.codigo) + '</span>' +
                '</div>' +
                '<div class="eye-icon" id="eye-' + idx + '" title="Ver gráfica de motores"><i class="fas fa-eye"></i></div>' +
                '<div class="progress-container">' +
                    '<div class="progress-bar-bg"><div class="progress-fill" id="pf-' + idx + '" data-target="' + Math.min(pct,100) + '"></div></div>' +
                    '<div class="porcentaje-num">' + pct + '%</div>' +
                '</div>' +
                '<div class="expand-icon" id="ex-' + idx + '" title="Expandir detalle"><i class="fas fa-chevron-down"></i></div>' +
            '</div>' +
            '<div class="detalle-contenido" id="det-' + idx + '">' +
                buildDetalle(linea) +
            '</div>';

        container.appendChild(card);

        /* Expand/collapse */
        document.getElementById('ex-' + idx).addEventListener('click', function(e) {
            e.stopPropagation();
            var det = document.getElementById('det-' + idx);
            var ex  = document.getElementById('ex-' + idx);
            if (det.classList.contains('expandido')) {
                det.classList.remove('expandido');
                ex.classList.remove('open');
            } else {
                det.classList.add('expandido');
                ex.classList.add('open');
            }
        });

        /* Eye → chart modal */
        document.getElementById('eye-' + idx).addEventListener('click', function(e) {
            e.stopPropagation();
            openChartModal(linea, color);
        });
    });

    /* Animate progress bars */
    setTimeout(function() {
        document.querySelectorAll('.progress-fill').forEach(function(el) {
            el.style.width = (el.dataset.target || 0) + '%';
        });
    }, 120);
}

function buildDetalle(linea) {
    if (!linea.motores || !linea.motores.length) return '<div style="padding:18px 22px;color:rgba(255,255,255,.4);font-size:.85rem">Sin motores de desarrollo registrados para esta línea.</div>';

    var html = '<div class="micro-grid">';
    linea.motores.forEach(function(motor) {
        var mp = parseFloat(motor.cumplimiento);
        html +=
            '<div class="motor-card">' +
                '<div class="motor-titulo">' +
                    '<i class="fas fa-cog"></i>' +
                    '<span style="flex:1">' + esc(motor.nombre) + '</span>' +
                    '<span class="motor-pct" style="color:' + pctColor(mp) + '">' + mp.toFixed(2) + '%</span>' +
                '</div>';

        if (motor.proyectos && motor.proyectos.length) {
            motor.proyectos.forEach(function(proy) {
                var pp  = parseFloat(proy.cumplimiento);
                var cls = pctColor(pp);
                html +=
                    '<div class="proyecto-item">' +
                        '<span class="proyecto-nombre">' + esc(proy.nombre) + '</span>' +
                        '<span class="proyecto-valor" style="color:' + cls + '">' + pp.toFixed(2) + '%</span>' +
                    '</div>';
            });
        }
        html += '</div>';
    });
    html += '</div>';
    return html;
}

/* ── Chart modal ── */
var barChartInstance = null;

function openChartModal(linea, color) {
    var modal  = document.getElementById('chartModal');
    var title  = document.getElementById('chartTitle');
    var stats  = document.getElementById('chartStats');
    var canvas = document.getElementById('barChart');

    title.textContent = linea.titulo;

    var labels = [], data = [], colors = [];
    var statsHtml = '<div class="stat-item"><span class="stat-label">Cumplimiento de la línea</span><span class="stat-value">' + parseFloat(linea.cumplimiento).toFixed(2) + '%</span></div>';

    (linea.motores || []).forEach(function(m, i) {
        var nombre = m.nombre || 'Sin nombre';
        var shortName = nombre.length > 28 ? nombre.substring(0, 26) + '…' : nombre;
        var mp = parseFloat(m.cumplimiento);
        labels.push(shortName);
        data.push(mp.toFixed(2));
        colors.push(CHART_COLORS[i % CHART_COLORS.length]);
        statsHtml += '<div class="stat-item"><span class="stat-label">' + esc(nombre) + '</span><span class="stat-value">' + mp.toFixed(2) + '%</span></div>';
    });

    stats.innerHTML = statsHtml;

    if (barChartInstance) { barChartInstance.destroy(); barChartInstance = null; }
    if (labels.length) {
        var ctx = canvas.getContext('2d');
        barChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Avance (%)',
                    data: data,
                    backgroundColor: colors,
                    borderRadius: 8,
                    borderSkipped: false,
                    barPercentage: .65
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function(c) { return 'Avance: ' + c.parsed.y + '%'; } } }
                },
                scales: {
                    y: {
                        beginAtZero: true, max: 100,
                        grid: { color: 'rgba(255,255,255,.07)' },
                        ticks: { color: 'rgba(255,255,255,.6)', callback: function(v) { return v + '%'; } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: 'rgba(255,255,255,.6)', font: { size: 11 } }
                    }
                }
            }
        });
    }

    modal.classList.add('active');
}

document.getElementById('chartClose').addEventListener('click', function() {
    document.getElementById('chartModal').classList.remove('active');
    if (barChartInstance) { barChartInstance.destroy(); barChartInstance = null; }
});
document.getElementById('chartModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.remove('active');
        if (barChartInstance) { barChartInstance.destroy(); barChartInstance = null; }
    }
});

/* ── Dependencias modal ── */
function renderDepTable() {
    var tbody = document.getElementById('depBody');
    if (!depData || !depData.length) {
        tbody.innerHTML = '<tr><td colspan="4" style="padding:24px;text-align:center;color:rgba(255,255,255,.4)">No se encontraron registros por dependencia.</td></tr>';
        return;
    }
    var html = '';
    var totInd = 0, tot80 = 0;
    depData.forEach(function(d) {
        var p  = parseFloat(d.cumplimiento || 0);
        var cb = cbClass(p);
        totInd += parseInt(d.total_indicadores || 0);
        tot80  += parseInt(d.indicadores_80  || 0);
        html += '<tr>' +
            '<td>' + esc(d.dependencia) + '</td>' +
            '<td>' + (d.total_indicadores || 0) + '</td>' +
            '<td><span style="background:rgba(52,199,89,.15);color:#34C759;padding:2px 10px;border-radius:20px;font-size:.78rem;font-weight:700">' + (d.indicadores_80 || 0) + '</span></td>' +
            '<td><span class="cb ' + cb + '">' + p.toFixed(2) + '%</span></td>' +
        '</tr>';
    });
    var totalPct = totInd > 0 ? (tot80 / totInd * 100).toFixed(2) : '0.00';
    html += '<tr class="total-row">' +
        '<td>TOTAL CONSOLIDADO</td>' +
        '<td>' + totInd + '</td>' +
        '<td>' + tot80 + '</td>' +
        '<td><span class="cb ' + cbClass(parseFloat(totalPct)) + '">' + totalPct + '%</span></td>' +
    '</tr>';
    tbody.innerHTML = html;
}

document.getElementById('depBtn').addEventListener('click', function() {
    renderDepTable();
    document.getElementById('depModal').classList.add('active');
});
document.getElementById('depClose').addEventListener('click', function() {
    document.getElementById('depModal').classList.remove('active');
});
document.getElementById('depModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('active');
});

/* Esc closes modals */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('chartModal').classList.remove('active');
        document.getElementById('depModal').classList.remove('active');
        if (barChartInstance) { barChartInstance.destroy(); barChartInstance = null; }
    }
});

/* ── Init ── */
renderLineas();

}());
</script>
</body>
</html>
