<?php
// modelo/Formulacion144Model.php
require_once 'config/config.php';

class Formulacion144Model {
    private $db;
    private $table = 'formulacion_144';
    private $table_seguimiento = 'seguimiento_144'; // Nueva tabla

    public function __construct() {
        $this->conectarDB();
    }

    // ============= CONEXIÓN A BASE DE DATOS =============
    public function conectarDB() {
        try {
            $dsn = "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=" . Config::DB_CHARSET;
            $this->db = new PDO($dsn, Config::DB_USER, Config::DB_PASS);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $this->db;
        } catch (PDOException $e) {
            error_log("Error de conexión: " . $e->getMessage());
            return false;
        }
    }

    // ============= VERIFICAR FORMULARIO PADRE CON FECHAS =============
    public function verificarFormulario($id) {
        try {
            $stmt = $this->db->prepare("SELECT *, 
                                        NOW() as fecha_actual,
                                        TIMESTAMPDIFF(SECOND, NOW(), fecha_cierre) as segundos_restantes,
                                        CASE 
                                            WHEN fecha_inicio IS NOT NULL AND NOW() < fecha_inicio THEN 'no_iniciado'
                                            WHEN fecha_cierre IS NOT NULL AND NOW() > fecha_cierre THEN 'expirado'
                                            WHEN fecha_inicio IS NOT NULL AND fecha_cierre IS NOT NULL AND NOW() BETWEEN fecha_inicio AND fecha_cierre THEN 'vigente'
                                            WHEN fecha_inicio IS NULL AND fecha_cierre IS NULL THEN 'sin_fechas'
                                            ELSE 'vigente'
                                        END as estado_fecha
                                        FROM formularios 
                                        WHERE id = :id AND estado = 1");
            $stmt->execute([':id' => $id]);
            $resultado = $stmt->fetch();
            
            if ($resultado) {
                return $resultado;
            } else {
                return null;
            }
        } catch (PDOException $e) {
            error_log("Error verificarFormulario: " . $e->getMessage());
            return null;
        }
    }

    // ============= VERIFICAR SI EL FORMULARIO ESTÁ EN FECHA HÁBIL =============
    public function verificarFechaHabil($formulario) {
        if (!$formulario) {
            return [
                'valido' => false,
                'mensaje' => 'Formulario no encontrado',
                'clase' => 'error'
            ];
        }

        $fecha_actual = date('Y-m-d H:i:s');
        
        // Caso 1: No tiene fechas definidas
        if (empty($formulario['fecha_inicio']) && empty($formulario['fecha_cierre'])) {
            return [
                'valido' => true,
                'mensaje' => 'Formulario sin restricción de fechas',
                'clase' => 'sin-fechas'
            ];
        }

        // Caso 2: Tiene fecha de inicio pero no fecha de cierre
        if (!empty($formulario['fecha_inicio']) && empty($formulario['fecha_cierre'])) {
            if ($fecha_actual >= $formulario['fecha_inicio']) {
                return [
                    'valido' => true,
                    'mensaje' => 'Formulario vigente desde ' . date('d/m/Y H:i', strtotime($formulario['fecha_inicio'])),
                    'clase' => 'vigente'
                ];
            } else {
                return [
                    'valido' => false,
                    'mensaje' => 'Formulario disponible a partir del ' . date('d/m/Y H:i', strtotime($formulario['fecha_inicio'])),
                    'clase' => 'no-iniciado'
                ];
            }
        }

        // Caso 3: Tiene fecha de cierre pero no fecha de inicio
        if (empty($formulario['fecha_inicio']) && !empty($formulario['fecha_cierre'])) {
            if ($fecha_actual <= $formulario['fecha_cierre']) {
                return [
                    'valido' => true,
                    'mensaje' => 'Formulario vigente hasta ' . date('d/m/Y H:i', strtotime($formulario['fecha_cierre'])),
                    'clase' => 'vigente'
                ];
            } else {
                return [
                    'valido' => false,
                    'mensaje' => '⚠️ FORMULARIO EXPIRADO el ' . date('d/m/Y H:i', strtotime($formulario['fecha_cierre'])),
                    'clase' => 'expirado'
                ];
            }
        }

        // Caso 4: Tiene ambas fechas
        if (!empty($formulario['fecha_inicio']) && !empty($formulario['fecha_cierre'])) {
            if ($fecha_actual < $formulario['fecha_inicio']) {
                return [
                    'valido' => false,
                    'mensaje' => 'Formulario disponible a partir del ' . date('d/m/Y H:i', strtotime($formulario['fecha_inicio'])),
                    'clase' => 'no-iniciado'
                ];
            } elseif ($fecha_actual > $formulario['fecha_cierre']) {
                return [
                    'valido' => false,
                    'mensaje' => '⚠️ FORMULARIO EXPIRADO - Fecha límite: ' . date('d/m/Y H:i', strtotime($formulario['fecha_cierre'])),
                    'clase' => 'expirado'
                ];
            } else {
                $dias_restantes = floor((strtotime($formulario['fecha_cierre']) - strtotime($fecha_actual)) / 86400);
                $horas_restantes = floor(((strtotime($formulario['fecha_cierre']) - strtotime($fecha_actual)) % 86400) / 3600);
                
                return [
                    'valido' => true,
                    'mensaje' => '✓ Formulario vigente hasta: ' . date('d/m/Y H:i', strtotime($formulario['fecha_cierre'])),
                    'detalle' => "Tiempo restante: {$dias_restantes} días, {$horas_restantes} horas",
                    'clase' => 'vigente'
                ];
            }
        }
    }

    // ============= TEST DE CONEXIÓN =============
    public function testConexion() {
        try {
            $stmt = $this->db->prepare("SELECT 1");
            $stmt->execute();
            return ['success' => true, 'message' => '✅ Conexión a BD exitosa'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => '❌ Error de conexión: ' . $e->getMessage()];
        }
    }

    // ============= TEST DE TABLA FORMULARIOS =============
    public function testTablaFormularios() {
        try {
            $stmt = $this->db->prepare("SHOW TABLES LIKE 'formularios'");
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => '✅ Tabla formularios existe'];
            } else {
                return ['success' => false, 'message' => '❌ Tabla formularios NO existe'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => '❌ Error: ' . $e->getMessage()];
        }
    }

    // ============= TEST DE TABLA FORMULACION_144 =============
    public function testTabla() {
        try {
            $stmt = $this->db->prepare("SHOW TABLES LIKE 'formulacion_144'");
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => '✅ Tabla formulacion_144 existe'];
            } else {
                return ['success' => false, 'message' => '❌ Tabla formulacion_144 NO existe'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => '❌ Error: ' . $e->getMessage()];
        }
    }

    // ============= TEST DE TABLA SEGUIMIENTO_144 =============
    public function testTablaSeguimiento() {
        try {
            $stmt = $this->db->prepare("SHOW TABLES LIKE 'seguimiento_144'");
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => '✅ Tabla seguimiento_144 existe'];
            } else {
                return ['success' => false, 'message' => '❌ Tabla seguimiento_144 NO existe'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => '❌ Error: ' . $e->getMessage()];
        }
    }

    // ============= TEST DE FORMULARIO =============
    public function testFormulario($id) {
        try {
            $stmt = $this->db->prepare("SELECT id, titulo, fecha_inicio, fecha_cierre FROM formularios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $formulario = $stmt->fetch();
            if ($formulario) {
                $mensaje = '✅ Formulario ID ' . $id . ': ' . $formulario['titulo'];
                if ($formulario['fecha_inicio'] && $formulario['fecha_cierre']) {
                    $mensaje .= ' | Vigente: ' . date('d/m/Y', strtotime($formulario['fecha_inicio'])) . ' - ' . date('d/m/Y', strtotime($formulario['fecha_cierre']));
                }
                return [
                    'success' => true, 
                    'message' => $mensaje,
                    'data' => $formulario
                ];
            } else {
                return ['success' => false, 'message' => '❌ Formulario ID ' . $id . ' NO existe'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => '❌ Error: ' . $e->getMessage()];
        }
    }

    // ============= TEST COMPLETO =============
    public function testCompleto($formulario_id = null) {
        $resultados = [];
        $resultados[] = $this->testConexion();
        $resultados[] = $this->testTablaFormularios();
        $resultados[] = $this->testTabla();
        $resultados[] = $this->testTablaSeguimiento();
        
        if ($formulario_id) {
            $resultados[] = $this->testFormulario($formulario_id);
            
            try {
                $borradores = $this->getBorradores($formulario_id);
                $resultados[] = ['success' => true, 'message' => '✅ Borradores encontrados: ' . count($borradores)];
                
                $publicados = $this->getPublicados($formulario_id);
                $resultados[] = ['success' => true, 'message' => '✅ Publicados encontrados: ' . count($publicados)];
                
                $cancelados = $this->getCancelados($formulario_id);
                $resultados[] = ['success' => true, 'message' => '✅ Cancelados encontrados: ' . count($cancelados)];
                
                $seguimientos = $this->getSeguimientos($formulario_id);
                $resultados[] = ['success' => true, 'message' => '✅ Seguimientos encontrados: ' . count($seguimientos)];
            } catch (Exception $e) {
                $resultados[] = ['success' => false, 'message' => '❌ Error al obtener datos: ' . $e->getMessage()];
            }
        }
        
        return $resultados;
    }

    // ============= OBTENER POR ESTADO =============
    public function getByEstado($formulario_id, $estado) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM " . $this->table . " 
                                        WHERE formulario_id = :formulario_id AND estado = :estado 
                                        ORDER BY fecha_creacion DESC");
            $stmt->execute([
                ':formulario_id' => $formulario_id,
                ':estado' => $estado
            ]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getByEstado: " . $e->getMessage());
            return [];
        }
    }

    public function getBorradores($formulario_id) {
        return $this->getByEstado($formulario_id, 0);
    }

    public function getCancelados($formulario_id) {
        return $this->getByEstado($formulario_id, 1);
    }

    public function getPublicados($formulario_id) {
        return $this->getByEstado($formulario_id, 2);
    }

    // ============= OBTENER SEGUIMIENTOS =============
    public function getSeguimientos($formulario_id) {
        try {
            $stmt = $this->db->prepare("SELECT s.*, f.nombre_borrador, f.anio, f.linea_estrategica, 
                                        f.objetivo, f.estrategia, f.motor_desarrollo, f.meta_resultado,
                                        f.proyecto, f.ponderacion_proyectos, f.actividad_proyecto,
                                        f.ponderacion_actividades, f.responsable
                                        FROM " . $this->table_seguimiento . " s
                                        INNER JOIN " . $this->table . " f ON s.formulacion_id = f.id
                                        WHERE s.formulario_id = :formulario_id 
                                        ORDER BY s.fecha_creacion DESC");
            $stmt->execute([':formulario_id' => $formulario_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getSeguimientos: " . $e->getMessage());
            return [];
        }
    }

    // ============= CREAR BORRADOR (MODIFICADO - AHORA CREA TAMBIÉN SEGUIMIENTO) =============
    public function crearBorrador($formulario_id, $nombre_borrador, $creado_por = 1) {
        try {
            $formulario = $this->verificarFormulario($formulario_id);
            if (!$formulario) {
                return false;
            }
            
            $this->db->beginTransaction();
            
            // Insertar en formulacion_144
            $stmt = $this->db->prepare("INSERT INTO " . $this->table . " 
                                        (formulario_id, nombre_borrador, estado, creado_por) 
                                        VALUES (:formulario_id, :nombre, 0, :creado_por)");
            $result = $stmt->execute([
                ':formulario_id' => $formulario_id,
                ':nombre' => $nombre_borrador,
                ':creado_por' => $creado_por
            ]);
            
            if ($result) {
                $formulacion_id = $this->db->lastInsertId();
                
                // Insertar en seguimiento_144 con el mismo nombre
                $stmt2 = $this->db->prepare("INSERT INTO " . $this->table_seguimiento . " 
                                            (formulario_id, formulacion_id, nombre_seguimiento, estado) 
                                            VALUES (:formulario_id, :formulacion_id, :nombre, 0)");
                $result2 = $stmt2->execute([
                    ':formulario_id' => $formulario_id,
                    ':formulacion_id' => $formulacion_id,
                    ':nombre' => $nombre_borrador
                ]);
                
                if ($result2) {
                    $this->db->commit();
                    return true;
                } else {
                    $this->db->rollBack();
                    return false;
                }
            } else {
                $this->db->rollBack();
                return false;
            }
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error crearBorrador: " . $e->getMessage());
            return false;
        }
    }

    // ============= OBTENER POR ID (INCLUYE SEGUIMIENTO) =============
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT f.*, s.id as seguimiento_id, s.avance_fisico, 
                                        s.avance_financiero, s.observaciones, s.estado as estado_seguimiento
                                        FROM " . $this->table . " f
                                        LEFT JOIN " . $this->table_seguimiento . " s ON f.id = s.formulacion_id
                                        WHERE f.id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getById: " . $e->getMessage());
            return null;
        }
    }

    // ============= ACTUALIZAR FORMULARIO (MODIFICADO - SINCRONIZA NOMBRE CON SEGUIMIENTO) =============
    public function actualizar($id, $data) {
        try {
            $this->db->beginTransaction();
            
            // Actualizar formulacion_144
            $sql = "UPDATE " . $this->table . " SET
                nombre_borrador = :nombre_borrador,
                anio = :anio,
                linea_estrategica = :linea_estrategica,
                objetivo = :objetivo,
                estrategia = :estrategia,
                motor_desarrollo = :motor_desarrollo,
                meta_resultado = :meta_resultado,
                proyecto = :proyecto,
                ponderacion_proyectos = :ponderacion_proyectos,
                actividad_proyecto = :actividad_proyecto,
                ponderacion_actividades = :ponderacion_actividades,
                responsable = :responsable,
                fecha_actualizacion = NOW()
                WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':id' => $id,
                ':nombre_borrador' => $data['nombre_borrador'],
                ':anio' => $data['anio'] ?? null,
                ':linea_estrategica' => $data['linea_estrategica'] ?? null,
                ':objetivo' => $data['objetivo'] ?? null,
                ':estrategia' => $data['estrategia'] ?? null,
                ':motor_desarrollo' => $data['motor_desarrollo'] ?? null,
                ':meta_resultado' => $data['meta_resultado'] ?? null,
                ':proyecto' => $data['proyecto'] ?? null,
                ':ponderacion_proyectos' => $data['ponderacion_proyectos'] ?? null,
                ':actividad_proyecto' => $data['actividad_proyecto'] ?? null,
                ':ponderacion_actividades' => $data['ponderacion_actividades'] ?? null,
                ':responsable' => $data['responsable'] ?? null
            ]);
            
            if ($result) {
                // Sincronizar nombre en seguimiento_144
                $stmt2 = $this->db->prepare("UPDATE " . $this->table_seguimiento . " 
                                            SET nombre_seguimiento = :nombre 
                                            WHERE formulacion_id = :formulacion_id");
                $result2 = $stmt2->execute([
                    ':formulacion_id' => $id,
                    ':nombre' => $data['nombre_borrador']
                ]);
                
                if ($result2) {
                    $this->db->commit();
                    return true;
                } else {
                    $this->db->rollBack();
                    return false;
                }
            } else {
                $this->db->rollBack();
                return false;
            }
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error actualizar: " . $e->getMessage());
            return false;
        }
    }

    // ============= ACTUALIZAR SOLO SEGUIMIENTO =============
    public function actualizarSeguimiento($id, $data) {
        try {
            $sql = "UPDATE " . $this->table_seguimiento . " SET
                avance_fisico = :avance_fisico,
                avance_financiero = :avance_financiero,
                observaciones = :observaciones,
                fecha_actualizacion = NOW()
                WHERE formulacion_id = :formulacion_id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':formulacion_id' => $id,
                ':avance_fisico' => $data['avance_fisico'] ?? null,
                ':avance_financiero' => $data['avance_financiero'] ?? null,
                ':observaciones' => $data['observaciones'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Error actualizarSeguimiento: " . $e->getMessage());
            return false;
        }
    }

    // ============= CAMBIAR ESTADO (MODIFICADO - TAMBIÉN CAMBIA EN SEGUIMIENTO) =============
    public function cambiarEstado($id, $estado) {
        try {
            $this->db->beginTransaction();
            
            $stmt = $this->db->prepare("UPDATE " . $this->table . " SET estado = :estado, fecha_actualizacion = NOW() WHERE id = :id");
            $result = $stmt->execute([
                ':id' => $id,
                ':estado' => $estado
            ]);
            
            if ($result) {
                $stmt2 = $this->db->prepare("UPDATE " . $this->table_seguimiento . " SET estado = :estado, fecha_actualizacion = NOW() WHERE formulacion_id = :formulacion_id");
                $result2 = $stmt2->execute([
                    ':formulacion_id' => $id,
                    ':estado' => $estado
                ]);
                
                if ($result2) {
                    $this->db->commit();
                    return true;
                } else {
                    $this->db->rollBack();
                    return false;
                }
            } else {
                $this->db->rollBack();
                return false;
            }
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error cambiarEstado: " . $e->getMessage());
            return false;
        }
    }

    // ============= ELIMINAR (MODIFICADO - TAMBIÉN ELIMINA SEGUIMIENTO) =============
    public function eliminar($id) {
        try {
            $this->db->beginTransaction();
            
            $stmt2 = $this->db->prepare("DELETE FROM " . $this->table_seguimiento . " WHERE formulacion_id = :formulacion_id");
            $result2 = $stmt2->execute([':formulacion_id' => $id]);
            
            if ($result2) {
                $stmt = $this->db->prepare("DELETE FROM " . $this->table . " WHERE id = :id");
                $result = $stmt->execute([':id' => $id]);
                
                if ($result) {
                    $this->db->commit();
                    return true;
                } else {
                    $this->db->rollBack();
                    return false;
                }
            } else {
                $this->db->rollBack();
                return false;
            }
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error eliminar: " . $e->getMessage());
            return false;
        }
    }

    // ============= DUPLICAR BORRADOR (MODIFICADO - TAMBIÉN DUPLICA SEGUIMIENTO) =============
    public function duplicar($id, $nuevo_nombre, $creado_por = 1) {
        try {
            $original = $this->getById($id);
            if (!$original) return false;
            
            $this->db->beginTransaction();
            
            // Duplicar en formulacion_144
            $stmt = $this->db->prepare("INSERT INTO " . $this->table . " 
                (formulario_id, nombre_borrador, estado, creado_por, anio, linea_estrategica, 
                 objetivo, estrategia, motor_desarrollo, meta_resultado, proyecto, 
                 ponderacion_proyectos, actividad_proyecto, ponderacion_actividades, responsable) 
                VALUES 
                (:formulario_id, :nombre, 0, :creado_por, :anio, :linea_estrategica, 
                 :objetivo, :estrategia, :motor_desarrollo, :meta_resultado, :proyecto, 
                 :ponderacion_proyectos, :actividad_proyecto, :ponderacion_actividades, :responsable)");
            
            $result = $stmt->execute([
                ':formulario_id' => $original['formulario_id'],
                ':nombre' => $nuevo_nombre,
                ':creado_por' => $creado_por,
                ':anio' => $original['anio'],
                ':linea_estrategica' => $original['linea_estrategica'],
                ':objetivo' => $original['objetivo'],
                ':estrategia' => $original['estrategia'],
                ':motor_desarrollo' => $original['motor_desarrollo'],
                ':meta_resultado' => $original['meta_resultado'],
                ':proyecto' => $original['proyecto'],
                ':ponderacion_proyectos' => $original['ponderacion_proyectos'],
                ':actividad_proyecto' => $original['actividad_proyecto'],
                ':ponderacion_actividades' => $original['ponderacion_actividades'],
                ':responsable' => $original['responsable']
            ]);
            
            if ($result) {
                $nuevo_id = $this->db->lastInsertId();
                
                // Duplicar en seguimiento_144
                $stmt2 = $this->db->prepare("INSERT INTO " . $this->table_seguimiento . " 
                                            (formulario_id, formulacion_id, nombre_seguimiento, estado) 
                                            VALUES (:formulario_id, :formulacion_id, :nombre, 0)");
                $result2 = $stmt2->execute([
                    ':formulario_id' => $original['formulario_id'],
                    ':formulacion_id' => $nuevo_id,
                    ':nombre' => $nuevo_nombre
                ]);
                
                if ($result2) {
                    $this->db->commit();
                    return true;
                } else {
                    $this->db->rollBack();
                    return false;
                }
            } else {
                $this->db->rollBack();
                return false;
            }
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error duplicar: " . $e->getMessage());
            return false;
        }
    }

    // ============= OBTENER TODOS LOS FORMULARIOS =============
    public function obtenerTodosLosFormularios() {
        try {
            $stmt = $this->db->prepare("SELECT id, titulo, descripcion, fecha_inicio, fecha_cierre FROM formularios WHERE estado = 1 ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error obtenerTodosLosFormularios: " . $e->getMessage());
            return [];
        }
    }
}
?>