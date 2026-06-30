<?php
// modelo/config144Model.php
require_once 'config/config.php';

class config144Model {
    private $db;

    public function __construct() {
        $this->conectarDB();
    }

    private function conectarDB() {
        try {
            $dsn = "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=" . Config::DB_CHARSET;
            $this->db = new PDO($dsn, Config::DB_USER, Config::DB_PASS);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $this->db;
        } catch (PDOException $e) {
            error_log("Error de conexión en config144Model: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los años
     */
    public function getAnos() {
        try {
            $stmt = $this->db->prepare("SELECT id, anio, activo, orden, created_at FROM `ano-for-de-144` ORDER BY orden ASC, anio ASC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getAnos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener años activos para selects
     */
    public function getAnosActivos() {
        try {
            $stmt = $this->db->prepare("SELECT id, anio FROM `ano-for-de-144` WHERE activo = 1 ORDER BY orden ASC, anio ASC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getAnosActivos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener un año por ID
     */
    public function getAnoById($id) {
        try {
            $stmt = $this->db->prepare("SELECT id, anio, activo, orden, created_at FROM `ano-for-de-144` WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getAnoById: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear un nuevo año
     */
    public function crearAno($anio, $activo = 1, $orden = null) {
        try {
            if ($orden === null || $orden === '') {
                $stmt = $this->db->query("SELECT MAX(orden) as max_orden FROM `ano-for-de-144`");
                $result = $stmt->fetch();
                $orden = ($result['max_orden'] ?? 0) + 1;
            }

            $stmt = $this->db->prepare("
                INSERT INTO `ano-for-de-144` (anio, activo, orden, created_at) 
                VALUES (:anio, :activo, :orden, NOW())
            ");
            
            $resultado = $stmt->execute([
                ':anio' => $anio,
                ':activo' => $activo,
                ':orden' => $orden
            ]);

            if ($resultado) {
                $id = $this->db->lastInsertId();
                
                $anioAnterior = $this->getUltimoAnioConDistribucion($anio);
                
                if ($anioAnterior) {
                    $copia = $this->copiarDistribucion($anioAnterior, $anio);
                    if ($copia['success']) {
                        return [
                            'success' => true, 
                            'message' => "Año creado exitosamente. Se copió la distribución del año $anioAnterior",
                            'id' => $id,
                            'distribucion_copiada' => true,
                            'anio_origen' => $anioAnterior
                        ];
                    } else {
                        return [
                            'success' => true, 
                            'message' => "Año creado exitosamente, pero no se pudo copiar la distribución del año anterior",
                            'id' => $id,
                            'distribucion_copiada' => false
                        ];
                    }
                } else {
                    return [
                        'success' => true, 
                        'message' => 'Año creado exitosamente. No hay años anteriores con distribución para copiar.',
                        'id' => $id,
                        'distribucion_copiada' => false
                    ];
                }
            } else {
                return ['success' => false, 'message' => 'Error al crear el año'];
            }
        } catch (PDOException $e) {
            error_log("Error crearAno: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Actualizar un año
     */
    public function actualizarAno($id, $anio, $activo, $orden = null) {
        try {
            $sql = "UPDATE `ano-for-de-144` SET anio = :anio, activo = :activo";
            $params = [':id' => $id, ':anio' => $anio, ':activo' => $activo];
            
            if ($orden !== null && $orden !== '') {
                $sql .= ", orden = :orden";
                $params[':orden'] = $orden;
            }
            
            $sql .= " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute($params);

            if ($resultado) {
                return ['success' => true, 'message' => 'Año actualizado exitosamente'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar el año'];
            }
        } catch (PDOException $e) {
            error_log("Error actualizarAno: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Cambiar estado de un año
     */
    public function cambiarEstado($id, $activo) {
        try {
            $stmt = $this->db->prepare("UPDATE `ano-for-de-144` SET activo = :activo WHERE id = :id");
            $resultado = $stmt->execute([':id' => $id, ':activo' => $activo]);

            if ($resultado) {
                $estado = $activo ? 'activado' : 'desactivado';
                return ['success' => true, 'message' => "Año $estado exitosamente"];
            } else {
                return ['success' => false, 'message' => 'Error al cambiar el estado'];
            }
        } catch (PDOException $e) {
            error_log("Error cambiarEstado: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Eliminar un año
     */
    public function eliminarAno($id) {
        try {
            $checkStmt = $this->db->prepare("SELECT COUNT(*) as total FROM data_linea_estrategica WHERE anio = (SELECT anio FROM `ano-for-de-144` WHERE id = :id)");
            $checkStmt->execute([':id' => $id]);
            $result = $checkStmt->fetch();
            
            if ($result['total'] > 0) {
                return ['success' => false, 'message' => 'No se puede eliminar el año porque tiene datos asociados en líneas estratégicas'];
            }

            $stmt = $this->db->prepare("DELETE FROM `ano-for-de-144` WHERE id = :id");
            $resultado = $stmt->execute([':id' => $id]);

            if ($resultado && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Año eliminado exitosamente'];
            } else {
                return ['success' => false, 'message' => 'Error al eliminar el año o el año no existe'];
            }
        } catch (PDOException $e) {
            error_log("Error eliminarAno: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Reordenar años
     */
    public function reordenarAnos($ordenes) {
        try {
            $this->db->beginTransaction();
            
            foreach ($ordenes as $item) {
                $stmt = $this->db->prepare("UPDATE `ano-for-de-144` SET orden = :orden WHERE id = :id");
                $stmt->execute([
                    ':id' => $item['id'],
                    ':orden' => $item['orden']
                ]);
            }
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Orden actualizado exitosamente'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error reordenarAnos: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al reordenar: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener líneas estratégicas
     */
    public function getLineasEstrategicas() {
        try {
            $stmt = $this->db->prepare("SELECT id, codigo, nombre, objetivo FROM lineas_estrategicas WHERE activo = 1 ORDER BY codigo");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getLineasEstrategicas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener motores por línea estratégica
     */
    public function getMotoresPorLinea($linea_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nombre, ponderacion
                FROM motores 
                WHERE linea_id = :linea_id AND activo = 1 
                ORDER BY id
            ");
            $stmt->execute([':linea_id' => $linea_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getMotoresPorLinea: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener proyectos por motor
     */
    public function getProyectosPorMotor($motor_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, codigo, nombre 
                FROM proyectos 
                WHERE motor_id = :motor_id AND activo = 1 
                ORDER BY codigo
            ");
            $stmt->execute([':motor_id' => $motor_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getProyectosPorMotor: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener datos de líneas estratégicas por año
     */
    public function getDataLineasEstrategicasPorAnio($anio) {
        try {
            $stmt = $this->db->prepare("
                SELECT linea_id, porcentaje 
                FROM data_linea_estrategica 
                WHERE anio = :anio
            ");
            $stmt->execute([':anio' => $anio]);
            $resultados = $stmt->fetchAll();
            
            $datos = [];
            foreach ($resultados as $item) {
                $datos[$item['linea_id']] = $item['porcentaje'];
            }
            
            return $datos;
        } catch (PDOException $e) {
            error_log("Error getDataLineasEstrategicasPorAnio: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener datos de motores por línea y año
     */
    public function getDataMotoresPorLineaYAnio($linea_id, $anio) {
        try {
            $stmt = $this->db->prepare("
                SELECT motor_id, porcentaje 
                FROM data_motores 
                WHERE linea_id = :linea_id AND anio = :anio
            ");
            $stmt->execute([':linea_id' => $linea_id, ':anio' => $anio]);
            $resultados = $stmt->fetchAll();
            
            $datos = [];
            foreach ($resultados as $item) {
                $datos[$item['motor_id']] = $item['porcentaje'];
            }
            
            return $datos;
        } catch (PDOException $e) {
            error_log("Error getDataMotoresPorLineaYAnio: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener datos de proyectos por motor y año
     */
    public function getDataProyectosPorMotorYAnio($motor_id, $anio) {
        try {
            $stmt = $this->db->prepare("
                SELECT proyecto_id, porcentaje 
                FROM data_proyectos 
                WHERE motor_id = :motor_id AND anio = :anio
            ");
            $stmt->execute([':motor_id' => $motor_id, ':anio' => $anio]);
            $resultados = $stmt->fetchAll();
            
            $datos = [];
            foreach ($resultados as $item) {
                $datos[$item['proyecto_id']] = $item['porcentaje'];
            }
            
            return $datos;
        } catch (PDOException $e) {
            error_log("Error getDataProyectosPorMotorYAnio: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Guardar datos de línea estratégica
     */
    public function guardarDataLineaEstrategica($linea_id, $anio, $porcentaje) {
        try {
            $checkStmt = $this->db->prepare("SELECT id FROM data_linea_estrategica WHERE linea_id = :linea_id AND anio = :anio");
            $checkStmt->execute([':linea_id' => $linea_id, ':anio' => $anio]);
            
            if ($checkStmt->fetch()) {
                $stmt = $this->db->prepare("
                    UPDATE data_linea_estrategica 
                    SET porcentaje = :porcentaje, updated_at = NOW()
                    WHERE linea_id = :linea_id AND anio = :anio
                ");
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO data_linea_estrategica (linea_id, anio, porcentaje, created_at, updated_at) 
                    VALUES (:linea_id, :anio, :porcentaje, NOW(), NOW())
                ");
            }
            
            return $stmt->execute([
                ':linea_id' => $linea_id,
                ':anio' => $anio,
                ':porcentaje' => $porcentaje
            ]);
        } catch (PDOException $e) {
            error_log("Error guardarDataLineaEstrategica: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Guardar datos de motor
     */
    public function guardarDataMotor($motor_id, $linea_id, $anio, $porcentaje) {
        try {
            $checkStmt = $this->db->prepare("SELECT id FROM data_motores WHERE motor_id = :motor_id AND anio = :anio");
            $checkStmt->execute([':motor_id' => $motor_id, ':anio' => $anio]);
            
            if ($checkStmt->fetch()) {
                $stmt = $this->db->prepare("
                    UPDATE data_motores 
                    SET porcentaje = :porcentaje, linea_id = :linea_id, updated_at = NOW()
                    WHERE motor_id = :motor_id AND anio = :anio
                ");
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO data_motores (motor_id, linea_id, anio, porcentaje, created_at, updated_at) 
                    VALUES (:motor_id, :linea_id, :anio, :porcentaje, NOW(), NOW())
                ");
            }
            
            return $stmt->execute([
                ':motor_id' => $motor_id,
                ':linea_id' => $linea_id,
                ':anio' => $anio,
                ':porcentaje' => $porcentaje
            ]);
        } catch (PDOException $e) {
            error_log("Error guardarDataMotor: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Guardar datos de proyecto
     */
    public function guardarDataProyecto($proyecto_id, $motor_id, $anio, $porcentaje) {
        try {
            $checkStmt = $this->db->prepare("SELECT id FROM data_proyectos WHERE proyecto_id = :proyecto_id AND anio = :anio");
            $checkStmt->execute([':proyecto_id' => $proyecto_id, ':anio' => $anio]);
            
            if ($checkStmt->fetch()) {
                $stmt = $this->db->prepare("
                    UPDATE data_proyectos 
                    SET porcentaje = :porcentaje, motor_id = :motor_id, updated_at = NOW()
                    WHERE proyecto_id = :proyecto_id AND anio = :anio
                ");
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO data_proyectos (proyecto_id, motor_id, anio, porcentaje, created_at, updated_at) 
                    VALUES (:proyecto_id, :motor_id, :anio, :porcentaje, NOW(), NOW())
                ");
            }
            
            return $stmt->execute([
                ':proyecto_id' => $proyecto_id,
                ':motor_id' => $motor_id,
                ':anio' => $anio,
                ':porcentaje' => $porcentaje
            ]);
        } catch (PDOException $e) {
            error_log("Error guardarDataProyecto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Guardar todos los datos de motores de una línea
     */
    public function guardarDataMotores($anio, $linea_id, $datos) {
        try {
            $this->db->beginTransaction();
            
            foreach ($datos as $item) {
                $this->guardarDataMotor($item['motor_id'], $linea_id, $anio, $item['porcentaje']);
            }
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Datos de motores guardados exitosamente'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error guardarDataMotores: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()];
        }
    }

    /**
     * Guardar todos los datos de proyectos de un motor
     */
    public function guardarDataProyectos($anio, $motor_id, $datos) {
        try {
            $this->db->beginTransaction();
            
            foreach ($datos as $item) {
                $this->guardarDataProyecto($item['proyecto_id'], $motor_id, $anio, $item['porcentaje']);
            }
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Datos de proyectos guardados exitosamente'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error guardarDataProyectos: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener datos de motores por año y línea (para API)
     */
    public function getDataMotores($anio, $linea_id) {
        return $this->getDataMotoresPorLineaYAnio($linea_id, $anio);
    }

    /**
     * Obtener datos de proyectos por año y motor (para API)
     */
    public function getDataProyectos($anio, $motor_id) {
        return $this->getDataProyectosPorMotorYAnio($motor_id, $anio);
    }

    /**
     * Guardar distribución de datos de líneas estratégicas
     */
    public function guardarDataDistribucion($anio, $distribucion) {
        try {
            $this->db->beginTransaction();
            
            foreach ($distribucion as $item) {
                $this->guardarDataLineaEstrategica($item['linea_id'], $anio, $item['porcentaje']);
            }
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Distribución guardada exitosamente'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error guardarDataDistribucion: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()];
        }
    }

    /**
     * Verificar si un año tiene datos guardados
     */
    public function verificarDatosPorAnio($anio) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM data_linea_estrategica 
                WHERE anio = :anio
            ");
            $stmt->execute([':anio' => $anio]);
            $result = $stmt->fetch();
            
            return $result['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error verificarDatosPorAnio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener el año anterior más cercano con distribución
     */
    public function getUltimoAnioConDistribucion($anioActual) {
        try {
            $stmt = $this->db->prepare("
                SELECT DISTINCT anio 
                FROM data_linea_estrategica 
                WHERE anio < :anio_actual 
                ORDER BY anio DESC 
                LIMIT 1
            ");
            $stmt->execute([':anio_actual' => $anioActual]);
            $result = $stmt->fetch();
            
            return $result ? $result['anio'] : null;
        } catch (PDOException $e) {
            error_log("Error getUltimoAnioConDistribucion: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Copiar distribución de un año a otro
     */
    public function copiarDistribucion($anioOrigen, $anioDestino) {
        try {
            $this->db->beginTransaction();
            
            $stmt = $this->db->prepare("
                INSERT INTO data_linea_estrategica (linea_id, anio, porcentaje, created_at, updated_at)
                SELECT linea_id, :anio_destino, porcentaje, NOW(), NOW()
                FROM data_linea_estrategica
                WHERE anio = :anio_origen
                ON DUPLICATE KEY UPDATE 
                    porcentaje = VALUES(porcentaje),
                    updated_at = NOW()
            ");
            $stmt->execute([
                ':anio_origen' => $anioOrigen,
                ':anio_destino' => $anioDestino
            ]);
            
            $stmt = $this->db->prepare("
                INSERT INTO data_motores (motor_id, linea_id, anio, porcentaje, created_at, updated_at)
                SELECT motor_id, linea_id, :anio_destino, porcentaje, NOW(), NOW()
                FROM data_motores
                WHERE anio = :anio_origen
                ON DUPLICATE KEY UPDATE 
                    porcentaje = VALUES(porcentaje),
                    linea_id = VALUES(linea_id),
                    updated_at = NOW()
            ");
            $stmt->execute([
                ':anio_origen' => $anioOrigen,
                ':anio_destino' => $anioDestino
            ]);
            
            $stmt = $this->db->prepare("
                INSERT INTO data_proyectos (proyecto_id, motor_id, anio, porcentaje, created_at, updated_at)
                SELECT proyecto_id, motor_id, :anio_destino, porcentaje, NOW(), NOW()
                FROM data_proyectos
                WHERE anio = :anio_origen
                ON DUPLICATE KEY UPDATE 
                    porcentaje = VALUES(porcentaje),
                    motor_id = VALUES(motor_id),
                    updated_at = NOW()
            ");
            $stmt->execute([
                ':anio_origen' => $anioOrigen,
                ':anio_destino' => $anioDestino
            ]);
            
            $this->db->commit();
            return ['success' => true, 'message' => "Distribución copiada desde el año $anioOrigen"];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error copiarDistribucion: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al copiar distribución: ' . $e->getMessage()];
        }
    }

    public function duplicarAno($idOrigen, $anioDestino) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM `ano-for-de-144` WHERE id = :id");
            $stmt->execute([':id' => $idOrigen]);
            $origen = $stmt->fetch();
            if (!$origen) return ['success' => false, 'message' => 'Año origen no encontrado'];

            // Verificar que el año destino no exista ya
            $stmt = $this->db->prepare("SELECT id FROM `ano-for-de-144` WHERE anio = :anio");
            $stmt->execute([':anio' => $anioDestino]);
            if ($stmt->fetch()) return ['success' => false, 'message' => "Ya existe un registro para el año $anioDestino"];

            $stmt = $this->db->query("SELECT MAX(orden) as max_orden FROM `ano-for-de-144`");
            $res  = $stmt->fetch();
            $nuevoOrden = ($res['max_orden'] ?? 0) + 1;

            $stmt = $this->db->prepare("
                INSERT INTO `ano-for-de-144` (anio, activo, orden, created_at)
                VALUES (:anio, :activo, :orden, NOW())
            ");
            $stmt->execute([':anio' => $anioDestino, ':activo' => $origen['activo'], ':orden' => $nuevoOrden]);

            $copia = $this->copiarDistribucion($origen['anio'], $anioDestino);

            return [
                'success' => true,
                'message' => "Año duplicado como $anioDestino" . ($copia['success'] ? " con distribución copiada de {$origen['anio']}" : " (sin distribución)"),
                'id'      => $this->db->lastInsertId(),
            ];
        } catch (PDOException $e) {
            error_log("Error duplicarAno: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al duplicar: ' . $e->getMessage()];
        }
    }
}
?>