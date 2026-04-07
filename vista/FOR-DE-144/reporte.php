<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Líneas Estratégicas 2025 · Dashboard Dinámico</title>
    <link rel="stylesheet" href="estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* Estilos adicionales para el botón de dependencias */
        .dependencias-btn {
            background: linear-gradient(135deg, #e67e22, #f39c12);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 10px rgba(230, 126, 34, 0.3);
            margin-left: 0.5rem;
        }

        .dependencias-btn:hover {
            background: linear-gradient(135deg, #d35400, #e67e22);
            transform: scale(1.05);
            box-shadow: 0 6px 15px rgba(230, 126, 34, 0.4);
        }

        .dependencias-btn i {
            font-size: 1.2rem;
        }

        /* Modal de dependencias */
        .dependencias-modal {
            max-width: 900px;
            width: 95%;
        }

        .dependencias-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }

        .dependencias-table th {
            background: #1f3a5f;
            color: white;
            padding: 1rem;
            font-weight: 600;
            text-align: center;
        }

        .dependencias-table th:first-child {
            border-radius: 15px 0 0 0;
        }

        .dependencias-table th:last-child {
            border-radius: 0 15px 0 0;
        }

        .dependencias-table td {
            padding: 0.8rem 1rem;
            border-bottom: 1px solid rgba(60, 110, 160, 0.2);
        }

        .dependencias-table tr:hover td {
            background: rgba(255,255,255,0.5);
        }

        .dependencias-table .total-row {
            background: rgba(230, 126, 34, 0.1);
            font-weight: 700;
        }

        .dependencias-table .total-row td {
            border-top: 2px solid #e67e22;
        }

        .cumplimiento-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .cumplimiento-alto {
            background: #2ecc71;
            color: white;
        }

        .cumplimiento-medio {
            background: #f39c12;
            color: white;
        }

        .cumplimiento-bajo {
            background: #e74c3c;
            color: white;
        }
    </style>
</head>
<body>
<div class="dashboard">
    <div class="header">
        <h1>📊 Líneas Estratégicas · Cumplimiento 2025</h1>
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <select class="year-selector" id="yearSelector">
                <option value="general">📋 General</option>
                <option value="2022">📅 2022</option>
                <option value="2023">📅 2023</option>
                <option value="2024">📅 2024</option>
                <option value="2025" selected>📅 2025</option>
            </select>
            <button class="dependencias-btn" id="dependenciasBtn">
                <i>📋</i> Estadísticas por Dependencia
            </button>
            <button class="refresh-btn" onclick="window.location.reload()">🔄 Recargar</button>
        </div>
    </div>

    <div class="cumplimiento-global">
        <div class="global-label">
            🏆 Cumplimiento integral <span>promedio líneas</span>
        </div>
        <div class="global-number" id="globalTotals">75.89%</div>
    </div>

    <div class="lineas-grid" id="lineasContainer"></div>

    <div class="aclaracion">
        <div class="footer-note">👁️ Click en el ojo para ver gráfica de barras · ▼ para expandir proyectos</div>
        <p style="margin-top:0.5rem">* Los porcentajes de cumplimiento se calculan como (seguimiento / formulado) * 100</p>
    </div>
</div>

<!-- Modal para gráfica de líneas estratégicas -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal-content">
        <div class="modal-close" id="modalClose">×</div>
        <div class="modal-title" id="modalTitle">Línea Estratégica</div>
        <div class="chart-container">
            <canvas id="barChart"></canvas>
        </div>
        <div class="modal-stats" id="modalStats"></div>
    </div>
</div>

<!-- Modal para estadísticas por dependencia -->
<div class="modal-overlay" id="dependenciasModalOverlay">
    <div class="modal-content dependencias-modal">
        <div class="modal-close" id="dependenciasModalClose">×</div>
        <div class="modal-title">
            📋 Estadísticas por Dependencia 2025
            <span style="font-size: 1rem; color: #e67e22; margin-left: 1rem;"></span>
        </div>
        <div style="overflow-x: auto; max-height: 60vh; overflow-y: auto;">
            <table class="dependencias-table">
                <thead>
                    <tr>
                        <th>Dependencia</th>
                        <th>Total Indicadores</th>
                        <th>Indicadores >80%</th>
                        <th>Cumplimiento (%)</th>
                    </tr>
                </thead>
                <tbody id="dependenciasTableBody">
                    <!-- Se llena con JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="años.js"></script>
<script>
    // Script específico para el botón de dependencias (solo se ejecuta en 2025)
    (function() {
        // Datos de dependencias
        const datosDependencias = [
            { nombre: "Director Consultorio Jurídico y Centro de Conciliación", total: 187, indicadores80: 0, cumplimiento: 0.00 },
            { nombre: "Director de Comunicaciones", total: 2, indicadores80: 2, cumplimiento: 100.00 },
            { nombre: "Director de Regionalización nivel Sede", total: 5, indicadores80: 5, cumplimiento: 100.00 },
            { nombre: "Director del Museo de Antropología", total: 5, indicadores80: 5, cumplimiento: 100.00 },
            { nombre: "Jefe de Oficina de Egresados", total: 2, indicadores80: 2, cumplimiento: 100.00 },
            { nombre: "Jefe de Oficina de Gestión Tecnológica e Información", total: 8, indicadores80: 7, cumplimiento: 87.50 },
            { nombre: "Jefe de Oficina de Planeación", total: 14, indicadores80: 12, cumplimiento: 85.71 },
            { nombre: "Jefe de Oficina de Relaciones Interinstitucionales e Internacionales", total: 15, indicadores80: 12, cumplimiento: 80.00 },
            { nombre: "Jefe del Departamento Calidad Académica", total: 9, indicadores80: 8, cumplimiento: 88.89 },
            { nombre: "Jefe del Departamento de Admisiones y Registro", total: 1, indicadores80: 1, cumplimiento: 100.00 },
            { nombre: "Jefe del Departamento de Biblioteca", total: 2, indicadores80: 2, cumplimiento: 100.00 },
            { nombre: "Jefe del Departamento de Educación Virtual, Medios Educativos y Audiovisuales", total: 7, indicadores80: 4, cumplimiento: 57.14 },
            { nombre: "Jefe del Departamento de Fomento y Apoyo a la Investigación", total: 38, indicadores80: 31, cumplimiento: 81.58 },
            { nombre: "Jefe del Departamento de Gestión de Talento Humano", total: 8, indicadores80: 7, cumplimiento: 87.50 },
            { nombre: "Jefe del Departamento de Gestión Financiera", total: 4, indicadores80: 3, cumplimiento: 75.00 },
            { nombre: "Jefe del Departamento de Infraestructura Física y Servicios Generales", total: 4, indicadores80: 2, cumplimiento: 50.00 },
            { nombre: "Jefe del Departamento de Postgrado", total: 3, indicadores80: 3, cumplimiento: 100.00 },
            { nombre: "Jefe del Departamento Extensión y Proyección Social", total: 31, indicadores80: 14, cumplimiento: 45.16 },
            { nombre: "Jefe Departamento de Desarrollo Humano", total: 10, indicadores80: 5, cumplimiento: 50.00 },
            { nombre: "Secretario General", total: 4, indicadores80: 3, cumplimiento: 75.00 },
            { nombre: "Vicerrector de Bienestar Universitario", total: 11, indicadores80: 6, cumplimiento: 54.55 },
            { nombre: "Vicerrector De Docencia", total: 20, indicadores80: 13, cumplimiento: 65.00 }
        ];

        // Calcular totales
        const totalIndicadores = datosDependencias.reduce((sum, d) => sum + d.total, 0);
        const totalIndicadores80 = datosDependencias.reduce((sum, d) => sum + d.indicadores80, 0);
        const totalCumplimiento = (totalIndicadores80 / totalIndicadores * 100).toFixed(2);

        // Función para obtener clase de color según cumplimiento
        function getCumplimientoClass(cumplimiento) {
            if (cumplimiento >= 80) return "cumplimiento-alto";
            if (cumplimiento >= 60) return "cumplimiento-medio";
            return "cumplimiento-bajo";
        }

        // Renderizar tabla de dependencias
        function renderDependenciasTable() {
            let html = '';
            
            // Ordenar por cumplimiento descendente
            const datosOrdenados = [...datosDependencias].sort((a, b) => b.cumplimiento - a.cumplimiento);
            
            datosOrdenados.forEach(d => {
                const claseCumplimiento = getCumplimientoClass(d.cumplimiento);
                html += `
                    <tr>
                        <td>${d.nombre}</td>
                        <td style="text-align: center; font-weight: 600;">${d.total}</td>
                        <td style="text-align: center; font-weight: 600;">${d.indicadores80}</td>
                        <td style="text-align: center;">
                            <span class="cumplimiento-badge ${claseCumplimiento}">
                                ${d.cumplimiento.toFixed(2)}%
                            </span>
                        </td>
                    </tr>
                `;
            });

            // Fila de totales
            html += `
                <tr class="total-row">
                    <td style="font-weight: 800;">TOTAL GENERAL</td>
                    <td style="text-align: center; font-weight: 800;">${totalIndicadores}</td>
                    <td style="text-align: center; font-weight: 800;">${totalIndicadores80}</td>
                    <td style="text-align: center;">
                        <span class="cumplimiento-badge cumplimiento-medio">
                           
                        </span>
                    </td>
                </tr>
            `;

            document.getElementById('dependenciasTableBody').innerHTML = html;
        }

        // Configurar modal de dependencias
        const dependenciasBtn = document.getElementById('dependenciasBtn');
        const dependenciasModal = document.getElementById('dependenciasModalOverlay');
        const dependenciasModalClose = document.getElementById('dependenciasModalClose');

        if (dependenciasBtn) {
            dependenciasBtn.addEventListener('click', () => {
                renderDependenciasTable();
                dependenciasModal.classList.add('active');
            });
        }

        if (dependenciasModalClose) {
            dependenciasModalClose.addEventListener('click', () => {
                dependenciasModal.classList.remove('active');
            });
        }

        if (dependenciasModal) {
            dependenciasModal.addEventListener('click', (e) => {
                if (e.target === dependenciasModal) {
                    dependenciasModal.classList.remove('active');
                }
            });
        }
    })();
</script>
</body>
</html>