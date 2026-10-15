-- Evidencias del seguimiento (hasta 5 archivos por indicador y por facultad).
-- Los archivos se guardan en la carpeta evidencias/AÑO/ID_INDICADOR[/FACULTAD]/ de la raíz del proyecto.
-- La tabla también se crea sola la primera vez que se usa; este script es opcional.
CREATE TABLE IF NOT EXISTS evidencias_144 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    formulacion_id INT NOT NULL,
    facultad_id INT NOT NULL DEFAULT 0,      -- 0 = evidencias del indicador; > 0 = de esa facultad
    nombre_original VARCHAR(255) NOT NULL,
    ruta VARCHAR(500) NOT NULL,
    tamano INT NOT NULL DEFAULT 0,
    usuario_id INT NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_evidencia_item (formulacion_id, facultad_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
