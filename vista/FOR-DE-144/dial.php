<?php
// diagnostico_final.php - Diagnóstico COMPLETO del sistema
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Capturar todos los errores en un buffer
ob_start();

echo "=== DIAGNÓSTICO COMPLETO DEL SISTEMA FOR-DE-144 ===\n\n";

// 1. Verificar archivos necesarios
echo "1. VERIFICANDO ARCHIVOS:\n";
$archivos = [
    'config/config.php',
    'modelo/FORDE144Model.php',
    'controlador/FORDE144Controller.php',
    'vista/FOR-DE-144/index.php'
];

foreach ($archivos as $archivo) {
    if (file_exists($archivo)) {
        echo "✅ $archivo - OK\n";
    } else {
        echo "❌ $archivo - NO ENCONTRADO\n";
    }
}
echo "\n";

// 2. Verificar configuración
echo "2. VERIFICANDO CONFIGURACIÓN:\n";
if (file_exists('config/config.php')) {
    require_once 'config/config.php';
    echo "✅ Configuración cargada\n";
    echo "   DB_HOST: " . Config::DB_HOST . "\n";
    echo "   DB_NAME: " . Config::DB_NAME . "\n";
    echo "   DB_USER: " . Config::DB_USER . "\n";
    echo "   DB_CHARSET: " . Config::DB_CHARSET . "\n";
}
echo "\n";

// 3. Probar conexión a BD directamente
echo "3. PROBANDO CONEXIÓN DIRECTA A BD:\n";
try {
    $dsn = "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=" . Config::DB_CHARSET;
    $pdo = new PDO($dsn, Config::DB_USER, Config::DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión PDO exitosa\n";
    
    // Verificar si la tabla existe
    $result = $pdo->query("SHOW TABLES LIKE 'formularios'");
    if ($result->rowCount() > 0) {
        echo "✅ Tabla 'formularios' existe\n";
        
        // Mostrar estructura
        $columns = $pdo->query("DESCRIBE formularios");
        echo "   Estructura de la tabla:\n";
        while ($col = $columns->fetch(PDO::FETCH_ASSOC)) {
            echo "   - {$col['Field']}: {$col['Type']}\n";
        }
    } else {
        echo "❌ Tabla 'formularios' NO existe\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
}
echo "\n";

// 4. Verificar el modelo
echo "4. PROBANDO EL MODELO:\n";
if (file_exists('modelo/FORDE144Model.php')) {
    try {
        require_once 'modelo/FORDE144Model.php';
        $model = new FORDE144Model();
        echo "✅ Modelo instanciado correctamente\n";
        
        // Probar getAll
        $formularios = $model->getAll();
        echo "✅ getAll() ejecutado - " . count($formularios) . " formularios encontrados\n";
        
        // Probar crear
        $testData = [
            'titulo' => 'TEST ' . date('Y-m-d H:i:s'),
            'descripcion' => 'Prueba de diagnóstico',
            'estado' => 1,
            'fecha_inicio' => null,
            'fecha_cierre' => null
        ];
        
        $resultado = $model->create($testData);
        if ($resultado) {
            $id = $model->getLastInsertId();
            echo "✅ create() exitoso - ID generado: $id\n";
        } else {
            echo "❌ create() falló\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Error en modelo: " . $e->getMessage() . "\n";
        echo "   Trace: " . $e->getTraceAsString() . "\n";
    }
}
echo "\n";

// 5. Verificar el controlador
echo "5. PROBANDO EL CONTROLADOR:\n";
if (file_exists('controlador/FORDE144Controller.php')) {
    try {
        require_once 'controlador/FORDE144Controller.php';
        $controller = new FORDE144Controller();
        echo "✅ Controlador instanciado correctamente\n";
        
        // Verificar que los métodos existen
        $metodos = ['crear', 'editar', 'eliminar', 'obtenerFormularios'];
        foreach ($metodos as $metodo) {
            if (method_exists($controller, $metodo)) {
                echo "✅ Método '$metodo' existe\n";
            } else {
                echo "❌ Método '$metodo' NO existe\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Error en controlador: " . $e->getMessage() . "\n";
    }
}
echo "\n";

// 6. Verificar rutas en index.php
echo "6. VERIFICANDO RUTAS EN INDEX.PHP:\n";
if (file_exists('index.php')) {
    $contenido = file_get_contents('index.php');
    
    // Buscar la sección de FOR-DE-144
    if (preg_match("/case 'FOR-DE-144':(.*?)break;/s", $contenido, $matches)) {
        echo "✅ Sección FOR-DE-144 encontrada\n";
        echo "   Acciones encontradas:\n";
        
        if (strpos($matches[1], "case 'crear'") !== false) {
            echo "   ✅ case 'crear' - OK\n";
        } else {
            echo "   ❌ case 'crear' - NO ENCONTRADO\n";
        }
        
        if (strpos($matches[1], "case 'editar'") !== false) {
            echo "   ✅ case 'editar' - OK\n";
        } else {
            echo "   ❌ case 'editar' - NO ENCONTRADO\n";
        }
        
        if (strpos($matches[1], "case 'eliminar'") !== false) {
            echo "   ✅ case 'eliminar' - OK\n";
        } else {
            echo "   ❌ case 'eliminar' - NO ENCONTRADO\n";
        }
        
    } else {
        echo "❌ Sección FOR-DE-144 NO encontrada en index.php\n";
    }
}
echo "\n";

// 7. Simular una petición AJAX
echo "7. SIMULANDO PETICIÓN AJAX:\n";
echo "   Creando un formulario de prueba...\n";

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'titulo' => 'Test AJAX ' . date('H:i:s'),
    'descripcion' => 'Prueba de simulación',
    'tipo_tiempo' => 'sin_restricciones'
];

// Capturar la salida del método crear
try {
    ob_clean();
    require_once 'controlador/FORDE144Controller.php';
    $controller = new FORDE144Controller();
    
    // Llamar al método crear
    $controller->crear();
    
    // Obtener la salida
    $output = ob_get_clean();
    
    // Verificar si es JSON válido
    $json = json_decode($output, true);
    if ($json) {
        echo "✅ Respuesta JSON válida:\n";
        echo "   " . json_encode($json, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "❌ Respuesta NO es JSON válido:\n";
        echo "   Primeros 200 caracteres: " . substr($output, 0, 200) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error en simulación: " . $e->getMessage() . "\n";
    $output = ob_get_clean();
    if ($output) {
        echo "   Salida: " . substr($output, 0, 200) . "\n";
    }
}

// 8. Mostrar errores PHP actuales
echo "\n8. ERRORES PHP RECIENTES:\n";
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    $lines = file($errorLog);
    $lastLines = array_slice($lines, -20);
    foreach ($lastLines as $line) {
        if (strpos($line, 'FORDE144') !== false || strpos($line, 'formularios') !== false) {
            echo "   🔴 " . $line;
        }
    }
} else {
    echo "   No se pudo leer el log de errores\n";
}

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
?>