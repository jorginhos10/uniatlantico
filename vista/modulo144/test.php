<?php
// test_completo.php - Test completo de líneas estratégicas y estrategias
require_once 'config/config.php';
require_once 'modelo/Modulo144Model.php';

// Función para ejecutar consulta SQL directa
function ejecutarConsultaSQL($sql) {
    try {
        $dsn = "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=" . Config::DB_CHARSET;
        $db = new PDO($dsn, Config::DB_USER, Config::DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

$model = new Modulo144Model();
$basePath = Config::getBasePath();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Completo - Líneas Estratégicas y Estrategias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background: #f8f9fa; }
        .card { margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card-header { font-weight: bold; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; max-height: 400px; overflow: auto; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        table { font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">🧪 Test Completo - Líneas Estratégicas y Estrategias</h1>
        
        <!-- Pestañas de navegación -->
        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="db-tab" data-bs-toggle="tab" data-bs-target="#db" type="button">Base de Datos</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ajax-tab" data-bs-toggle="tab" data-bs-target="#ajax" type="button">Test AJAX</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="sql-tab" data-bs-toggle="tab" data-bs-target="#sql" type="button">SQL Completo</button>
            </li>
        </ul>
        
        <div class="tab-content">
            <!-- Pestaña 1: Base de Datos -->
            <div class="tab-pane fade show active" id="db">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">📋 1. Líneas Estratégicas</div>
                            <div class="card-body">
                                <?php
                                try {
                                    $lineas = $model->getLineasEstrategicas();
                                    if (empty($lineas)) {
                                        echo '<div class="alert alert-warning">❌ No hay líneas estratégicas en la base de datos</div>';
                                    } else {
                                        echo '<div class="alert alert-success">✅ ' . count($lineas) . ' líneas encontradas</div>';
                                        echo '<table class="table table-sm table-bordered">';
                                        echo '<tr><th>ID</th><th>Código</th><th>Nombre</th></tr>';
                                        foreach ($lineas as $linea) {
                                            echo '<tr>';
                                            echo '<td>' . $linea['id'] . '</td>';
                                            echo '<td>' . $linea['codigo'] . '</td>';
                                            echo '<td>' . $linea['nombre'] . '</td>';
                                            echo '</tr>';
                                        }
                                        echo '</table>';
                                    }
                                } catch (Exception $e) {
                                    echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">📋 2. Estrategias por Línea</div>
                            <div class="card-body">
                                <?php
                                try {
                                    $lineas = $model->getLineasEstrategicas();
                                    if (!empty($lineas)) {
                                        foreach ($lineas as $linea) {
                                            echo '<div class="mb-3 p-2 border rounded">';
                                            echo '<strong>Línea ' . $linea['codigo'] . ':</strong> ' . $linea['nombre'] . '<br>';
                                            $estrategias = $model->getEstrategiasPorLinea($linea['id']);
                                            if (empty($estrategias)) {
                                                echo '<span class="warning">⚠️ Sin estrategias</span>';
                                            } else {
                                                echo '<span class="success">✅ ' . count($estrategias) . ' estrategias</span>';
                                                echo '<ul class="mt-2">';
                                                foreach ($estrategias as $e) {
                                                    echo '<li><small>' . $e['descripcion'] . '</small></li>';
                                                }
                                                echo '</ul>';
                                            }
                                            echo '</div>';
                                        }
                                    }
                                } catch (Exception $e) {
                                    echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pestaña 2: Test AJAX -->
            <div class="tab-pane fade" id="ajax">
                <div class="card">
                    <div class="card-header">🌐 Test del Endpoint AJAX</div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="linea_id" class="form-label">ID de Línea a probar:</label>
                                <input type="number" class="form-control" id="linea_id" value="1" min="1">
                            </div>
                            <div class="col-md-8 d-flex align-items-end">
                                <button class="btn btn-primary me-2" onclick="testEndpoint()">Probar Endpoint</button>
                                <button class="btn btn-success me-2" onclick="testTodasLasLineas()">Probar Todas (1-4)</button>
                                <button class="btn btn-info" onclick="testDirecto()">URL Directa</button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">URL del Endpoint:</label>
                            <input type="text" class="form-control" id="endpoint_url" readonly value="<?php echo $basePath; ?>/modulo144/getEstrategiasPorLinea?linea_id=1">
                        </div>
                        
                        <div class="card mt-3">
                            <div class="card-header">Resultado:</div>
                            <div class="card-body">
                                <pre id="ajax_resultado" style="min-height: 200px;">Haz clic en un botón para probar...</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pestaña 3: SQL Completo -->
            <div class="tab-pane fade" id="sql">
                <div class="card">
                    <div class="card-header">📝 SQL para crear/rellenar tablas</div>
                    <div class="card-body">
                        <pre style="background: #2d2d2d; color: #f8f8f2; padding: 15px;">-- Crear tabla lineas_estrategicas
CREATE TABLE IF NOT EXISTS `lineas_estrategicas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `objetivo` text NOT NULL,
  `activo` tinyint(4) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar líneas estratégicas
INSERT INTO `lineas_estrategicas` (`codigo`, `nombre`, `objetivo`) VALUES
('L1', 'Formación Académica Integral', 'Garantizar la formación en educación superior de manera integral, flexible e interdisciplinar con diversas modalidades, articulada a procesos de enseñanza y aprendizaje innovadores en sus diferentes niveles y campos de formación.'),
('L2', 'Investigación y Redes de conocimiento para el desarrollo', 'Línea estratégica orientada a la investigación y creación de redes de conocimiento que impulsen el desarrollo regional y nacional.'),
('L3', 'Impacto regional, nacional e internacional de la Universidad', 'Línea estratégica enfocada en generar impacto a través de la proyección social, extensión y cooperación internacional.'),
('L4', 'Bienestar Universitario, Salud mental positiva y Cultura del Cuidado', 'Línea estratégica centrada en el bienestar de la comunidad universitaria, promoviendo la salud mental y una cultura del cuidado.');

-- Crear tabla estrategias
CREATE TABLE IF NOT EXISTS `estrategias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `linea_id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `activo` tinyint(4) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `linea_id` (`linea_id`),
  CONSTRAINT `estrategias_ibfk_1` FOREIGN KEY (`linea_id`) REFERENCES `lineas_estrategicas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar estrategias de ejemplo (14 para L1)
INSERT INTO `estrategias` (`linea_id`, `descripcion`) VALUES
(1, 'Fortalecer la oferta académica de manera flexible e interdisciplinar con diversas modalidades, articulada a procesos de enseñanza y aprendizaje innovadores en sus diferentes niveles y campos de formación.'),
(1, 'Consolidar la estructura y el funcionamiento del Sistema de Aseguramiento Interno de la Calidad, de acuerdo a la normatividad y lineamientos vigentes del Ministerio de Educación Nacional.'),
(1, 'Fortalecer los procesos de internacionalización, movilidad, multilingüismo e interculturalidad que visibilicen la Universidad a nivel nacional e internacional, y alcanzar un posicionamiento académico.'),
(1, 'Asegurar la sostenibilidad de la Política de Enseñanza, Aprendizaje y Evaluación de Lenguas Extranjeras de la Universidad del Atlántico.'),
(1, 'Fortalecer políticas curriculares con pedagogías adecuadas que atiendan al principio de calidad académica con enfoque inclusivo a través del desarrollo de las disciplinas y las profesiones, la integración de saberes, el fortalecimiento de competencias digitales, la solución de problemas y la inserción de los egresados al mercado laboral.'),
(1, 'Integrar los procesos académicos y administrativos que conlleve de manera funcional a un orden adecuado de interacciones, soportadas en tecnologías de la información, de acuerdo a la normatividad vigente.'),
(1, 'Fortalecer la metodología de investigación y la participación activa en proyectos de investigación y desarrollo de productos tecnológicos para lograr un mayor reconocimiento regional y la competitividad nacional e internacional.'),
(1, 'Implementar iniciativas de colaboración entre instituciones de educación superior y universidades, incluidas las cooperativas de investigación y desarrollo de productos tecnológicos, así como la creación de espacios de intercambio de experiencias y conocimientos.'),
(1, 'Fomentar el uso y apropiación de las tecnologías de la información y comunicación (TIC) integradas a los procesos misionales, fortaleciendo las competencias digitales de la comunidad universitaria, la oferta académica de excelencia, la innovación pedagógica y la interacción con diferentes actores a nivel global, nacional y local.'),
(1, 'Promover la formación y desarrollo de la cultura de la diversidad cultural y lingüística.'),
(1, 'Desarrollar programas académicos con calidad y pertinencia para contribuir a la necesidad de contexto y la región Caribe.'),
(1, 'Articular la investigación y la extensión a la docencia para contribuir a la mejora de la calidad de la enseñanza y el conocimiento.'),
(1, 'Aumentar la formación de la población rural en programas académicos de la Universidad para cerrar la brecha urbano – rural.'),
(1, 'Fortalecer la vinculación del sector productivo y/o otros actores regionales para el fortalecimiento de oferta académica en región.'),

-- Insertar estrategias para L2
(2, 'Fortalecer los grupos de investigación y su clasificación en MinCiencias.'),
(2, 'Promover la formación doctoral de los docentes investigadores.'),
(2, 'Establecer alianzas estratégicas con centros de investigación nacionales e internacionales.'),
(2, 'Fomentar la publicación en revistas indexadas de alto impacto.'),
(2, 'Impulsar la transferencia de conocimiento y tecnología al sector productivo.'),

-- Insertar estrategias para L3
(3, 'Fortalecer los programas de extensión y proyección social.'),
(3, 'Establecer convenios de cooperación internacional para movilidad académica.'),
(3, 'Promover la participación en redes académicas internacionales.'),
(3, 'Desarrollar proyectos de cooperación con entidades gubernamentales y no gubernamentales.'),
(3, 'Visibilizar los logros y avances de la universidad a nivel nacional e internacional.'),

-- Insertar estrategias para L4
(4, 'Implementar programas de promoción de la salud mental y prevención de riesgos psicosociales.'),
(4, 'Fortalecer los servicios de bienestar universitario y acompañamiento estudiantil.'),
(4, 'Promover una cultura del cuidado y el autocuidado en la comunidad universitaria.'),
(4, 'Desarrollar programas de deporte, recreación y cultura para el bienestar integral.'),
(4, 'Establecer rutas de atención y seguimiento para casos de vulnerabilidad psicosocial.');</pre>
                        
                        <div class="mt-3">
                            <button class="btn btn-warning" onclick="copiarSQL()">Copiar SQL al portapapeles</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="<?php echo $basePath; ?>/modulo144?id=1" class="btn btn-secondary">Volver al Módulo 144</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const basePath = '<?php echo $basePath; ?>';
        
        function testEndpoint() {
            const lineaId = document.getElementById('linea_id').value;
            const url = basePath + '/modulo144/getEstrategiasPorLinea?linea_id=' + lineaId;
            
            document.getElementById('endpoint_url').value = url;
            document.getElementById('ajax_resultado').innerHTML = 'Cargando... URL: ' + url;
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                return response.text().then(text => {
                    let html = '📊 STATUS: ' + response.status + ' ' + response.statusText + '\n';
                    html += '📍 URL: ' + url + '\n';
                    html += '══════════════════════════════════════════════════\n\n';
                    html += '📥 RESPUESTA RAW:\n' + text + '\n\n';
                    
                    try {
                        const json = JSON.parse(text);
                        html += '📦 JSON PARSED:\n' + JSON.stringify(json, null, 2);
                        
                        if (json.success && json.estrategias) {
                            html += '\n\n📋 ESTRATEGIAS ENCONTRADAS: ' + json.estrategias.length;
                        }
                    } catch (e) {
                        html += '❌ ERROR AL PARSEAR JSON: ' + e.message;
                    }
                    
                    document.getElementById('ajax_resultado').innerHTML = html;
                });
            })
            .catch(error => {
                document.getElementById('ajax_resultado').innerHTML = '❌ ERROR: ' + error;
            });
        }
        
        function testTodasLasLineas() {
            const lineasIds = [1, 2, 3, 4];
            let resultados = [];
            let html = '📊 PROBANDO LÍNEAS 1-4\n';
            html += '══════════════════════════════════════════════════\n\n';
            
            Promise.all(lineasIds.map(id => 
                fetch(basePath + '/modulo144/getEstrategiasPorLinea?linea_id=' + id)
                    .then(res => res.text())
                    .then(text => {
                        try {
                            return { id, data: JSON.parse(text), raw: text };
                        } catch (e) {
                            return { id, error: e.message, raw: text };
                        }
                    })
            )).then(results => {
                results.forEach(r => {
                    html += `🔷 LÍNEA ID: ${r.id}\n`;
                    if (r.error) {
                        html += `   ❌ Error: ${r.error}\n`;
                    } else {
                        html += `   ✅ Success: ${r.data.success}\n`;
                        if (r.data.success) {
                            html += `   📋 Estrategias: ${r.data.estrategias ? r.data.estrategias.length : 0}\n`;
                            if (r.data.estrategias && r.data.estrategias.length > 0) {
                                r.data.estrategias.forEach((e, i) => {
                                    html += `      ${i+1}. ${e.descripcion.substring(0, 50)}...\n`;
                                });
                            }
                        } else {
                            html += `   ❌ Mensaje: ${r.data.message}\n`;
                        }
                    }
                    html += '\n';
                });
                document.getElementById('ajax_resultado').innerHTML = html;
            });
        }
        
        function testDirecto() {
            window.open(basePath + '/modulo144/getEstrategiasPorLinea?linea_id=1', '_blank');
        }
        
        function copiarSQL() {
            const sql = document.querySelector('#sql pre').innerText;
            navigator.clipboard.writeText(sql).then(() => {
                alert('SQL copiado al portapapeles');
            });
        }

        // Probar automáticamente al cargar la página
        window.onload = function() {
            setTimeout(testEndpoint, 500);
        };
    </script>
</body>
</html>