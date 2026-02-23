<?php
// test_ajax.php - Depurador de peticiones AJAX
?>
<!DOCTYPE html>
<html>
<head>
    <title>Depurador AJAX - FOR-DE-144</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #569cd6; }
        .card { background: #252526; border-radius: 8px; padding: 20px; margin-bottom: 20px; border: 1px solid #3e3e42; }
        button { background: #0e639c; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 14px; margin: 5px; }
        button:hover { background: #1177bb; }
        pre { background: #1e1e1e; padding: 15px; border-radius: 5px; border: 1px solid #3e3e42; overflow: auto; max-height: 400px; }
        .success { color: #6a9955; }
        .error { color: #f48771; }
        .info { color: #9cdcfe; }
        .url { color: #ce9178; }
        .response-headers { background: #2d2d2d; padding: 10px; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Depurador de Peticiones AJAX - FOR-DE-144</h1>
        
        <div class="card">
            <h2>1. Probar URL del Controlador</h2>
            <button onclick="testUrl('crear')">Probar acción 'crear'</button>
            <button onclick="testUrl('obtenerFormularios')">Probar 'obtenerFormularios'</button>
            <button onclick="testUrl('index')">Probar vista 'index'</button>
            <button onclick="testRaw()">Ver respuesta RAW</button>
            <div id="urlResult" style="margin-top: 15px;"></div>
        </div>

        <div class="card">
            <h2>2. Probar Inserción Directa</h2>
            <button onclick="testInsert()">Probar Insertar Formulario</button>
            <div id="insertResult" style="margin-top: 15px;"></div>
        </div>

        <div class="card">
            <h2>3. Verificar Conexión a BD</h2>
            <button onclick="testDB()">Probar Base de Datos</button>
            <div id="dbResult" style="margin-top: 15px;"></div>
        </div>

        <div class="card">
            <h2>4. Log de Errores PHP</h2>
            <button onclick="checkErrors()">Verificar Errores PHP</button>
            <div id="errorLog" style="margin-top: 15px;"></div>
        </div>
    </div>

    <script>
        const baseUrl = window.location.pathname.includes('index.php') ? '' : 'index.php';

        async function testUrl(action) {
            const resultDiv = document.getElementById('urlResult');
            resultDiv.innerHTML = '⏳ Cargando...';
            
            try {
                const url = `${baseUrl}?controller=FORDE144&action=${action}`;
                resultDiv.innerHTML += `<div class="info">📡 URL: ${url}</div>`;
                
                const response = await fetch(url, {
                    method: action === 'crear' ? 'POST' : 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                resultDiv.innerHTML += `<div>📊 Status: ${response.status} ${response.statusText}</div>`;
                
                const headers = {};
                response.headers.forEach((value, key) => {
                    headers[key] = value;
                });
                resultDiv.innerHTML += `<div class="response-headers">📋 Headers: <pre>${JSON.stringify(headers, null, 2)}</pre></div>`;
                
                const text = await response.text();
                resultDiv.innerHTML += `<div>📄 Respuesta (primeros 500 caracteres):</div>`;
                resultDiv.innerHTML += `<pre class="${text.startsWith('<') ? 'error' : 'success'}">${escapeHtml(text.substring(0, 500))}${text.length > 500 ? '...' : ''}</pre>`;
                
                if (text.startsWith('<')) {
                    resultDiv.innerHTML += `<div class="error">❌ ERROR: El servidor devolvió HTML en lugar de JSON</div>`;
                    
                    // Intentar extraer mensaje de error PHP
                    const errorMatch = text.match(/<b>(?:Fatal error|Warning|Parse error)<\/b>:\s*([^<]+)/i);
                    if (errorMatch) {
                        resultDiv.innerHTML += `<div class="error">🔴 PHP Error: ${errorMatch[1]}</div>`;
                    }
                } else {
                    try {
                        const json = JSON.parse(text);
                        resultDiv.innerHTML += `<div class="success">✅ JSON válido</div>`;
                        resultDiv.innerHTML += `<pre class="success">${JSON.stringify(json, null, 2)}</pre>`;
                    } catch (e) {
                        resultDiv.innerHTML += `<div class="error">❌ JSON inválido: ${e.message}</div>`;
                    }
                }
            } catch (error) {
                resultDiv.innerHTML += `<div class="error">❌ Error: ${error.message}</div>`;
            }
        }

        async function testRaw() {
            const resultDiv = document.getElementById('urlResult');
            resultDiv.innerHTML = '⏳ Obteniendo respuesta RAW...';
            
            try {
                const response = await fetch(`${baseUrl}?controller=FORDE144&action=obtenerFormularios`);
                const text = await response.text();
                
                resultDiv.innerHTML = `<div>📄 Respuesta COMPLETA (${text.length} caracteres):</div>`;
                resultDiv.innerHTML += `<pre class="${text.startsWith('<') ? 'error' : 'success'}">${escapeHtml(text)}</pre>`;
            } catch (error) {
                resultDiv.innerHTML += `<div class="error">❌ Error: ${error.message}</div>`;
            }
        }

        async function testInsert() {
            const resultDiv = document.getElementById('insertResult');
            resultDiv.innerHTML = '⏳ Insertando formulario de prueba...';
            
            const formData = new FormData();
            formData.append('titulo', 'Test ' + new Date().toLocaleTimeString());
            formData.append('descripcion', 'Formulario de prueba');
            formData.append('tipo_tiempo', 'sin_restricciones');
            
            try {
                const response = await fetch(`${baseUrl}?controller=FORDE144&action=crear`, {
                    method: 'POST',
                    body: formData
                });
                
                const text = await response.text();
                
                if (text.startsWith('<')) {
                    resultDiv.innerHTML = `<div class="error">❌ El servidor devolvió HTML:</div>`;
                    resultDiv.innerHTML += `<pre class="error">${escapeHtml(text.substring(0, 500))}</pre>`;
                    
                    // Buscar error PHP
                    const errorMatch = text.match(/<b>(?:Fatal error|Warning|Parse error)<\/b>:\s*([^<]+)/i);
                    if (errorMatch) {
                        resultDiv.innerHTML += `<div class="error">🔴 PHP Error Detectado: ${errorMatch[1]}</div>`;
                    }
                } else {
                    try {
                        const json = JSON.parse(text);
                        resultDiv.innerHTML = `<pre class="success">${JSON.stringify(json, null, 2)}</pre>`;
                    } catch (e) {
                        resultDiv.innerHTML = `<div class="error">❌ JSON inválido: ${e.message}</div>`;
                        resultDiv.innerHTML += `<pre>${escapeHtml(text)}</pre>`;
                    }
                }
            } catch (error) {
                resultDiv.innerHTML = `<div class="error">❌ Error: ${error.message}</div>`;
            }
        }

        async function testDB() {
            const resultDiv = document.getElementById('dbResult');
            resultDiv.innerHTML = '⏳ Verificando conexión a BD...';
            
            try {
                const response = await fetch('test_conexion.php');
                const text = await response.text();
                
                if (text.includes('✅')) {
                    resultDiv.innerHTML = `<div class="success">✅ Conexión a BD OK</div>`;
                    resultDiv.innerHTML += `<pre class="success">${escapeHtml(text.substring(0, 500))}</pre>`;
                } else {
                    resultDiv.innerHTML = `<div class="error">❌ Problema con BD:</div>`;
                    resultDiv.innerHTML += `<pre class="error">${escapeHtml(text)}</pre>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<div class="error">❌ Error: ${error.message}</div>`;
            }
        }

        async function checkErrors() {
            const resultDiv = document.getElementById('errorLog');
            resultDiv.innerHTML = '⏳ Verificando logs...';
            
            try {
                const response = await fetch('error_log.php');
                const text = await response.text();
                
                if (text.includes('No errors') || text.trim() === '') {
                    resultDiv.innerHTML = `<div class="success">✅ No hay errores PHP recientes</div>`;
                } else {
                    resultDiv.innerHTML = `<div class="error">⚠️ Errores encontrados:</div>`;
                    resultDiv.innerHTML += `<pre class="error">${escapeHtml(text)}</pre>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<div class="error">❌ Error al leer logs: ${error.message}</div>`;
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>