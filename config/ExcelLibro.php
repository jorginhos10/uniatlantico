<?php
// config/ExcelLibro.php
// Escribe y lee archivos .xlsx con varias hojas, sin librerías externas (usa ZipArchive y SimpleXML).

class ExcelLibro {

    /**
     * Envía al navegador un .xlsx con varias hojas.
     * $hojas = [ ['nombre' => 'Formulación', 'encabezados' => [...], 'filas' => [[...], ...], 'anchos' => [...]], ... ]
     * Los números (int/float o texto numérico) se guardan como número; el resto como texto.
     */
    public static function descargar($nombreArchivo, array $hojas) {
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('El servidor no tiene la extensión ZIP de PHP, necesaria para generar el Excel.');
        }

        $cab    = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $nsMain = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $nsRel  = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
        $nsPkg  = 'http://schemas.openxmlformats.org/package/2006';
        $esc    = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_XML1, 'UTF-8'); };

        $archivos = [];
        $tipos = '';
        $hojasWb = '';
        $relsWb = '';

        foreach (array_values($hojas) as $i => $hoja) {
            $n = $i + 1;
            $filas = $hoja['filas'] ?? [];
            array_unshift($filas, $hoja['encabezados']);

            $xmlFilas = '';
            foreach ($filas as $r => $fila) {
                $xmlFilas .= '<row r="' . ($r + 1) . '">';
                foreach (array_values($fila) as $c => $valor) {
                    if ($valor === null || $valor === '') continue;
                    $ref = self::columna($c) . ($r + 1);
                    $estilo = $r === 0 ? ' s="1"' : ' s="2"';
                    if ($r > 0 && self::esNumero($valor)) {
                        $xmlFilas .= '<c r="' . $ref . '"' . $estilo . '><v>' . (0 + $valor) . '</v></c>';
                    } else {
                        $xmlFilas .= '<c r="' . $ref . '"' . $estilo . ' t="inlineStr"><is><t xml:space="preserve">' . $esc($valor) . '</t></is></c>';
                    }
                }
                $xmlFilas .= '</row>';
            }

            $xmlCols = '';
            foreach (array_values($hoja['anchos'] ?? []) as $c => $ancho) {
                $xmlCols .= '<col min="' . ($c + 1) . '" max="' . ($c + 1) . '" width="' . (float)$ancho . '" customWidth="1"/>';
            }
            if ($xmlCols !== '') $xmlCols = '<cols>' . $xmlCols . '</cols>';

            // Primera fila (encabezados) fija al desplazarse
            $vista = '<sheetViews><sheetView workbookViewId="0"' . ($i === 0 ? ' tabSelected="1"' : '') . '>'
                   . '<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';

            $archivos["xl/worksheets/sheet{$n}.xml"] = $cab . '<worksheet xmlns="' . $nsMain . '">'
                . $vista . $xmlCols . '<sheetData>' . $xmlFilas . '</sheetData></worksheet>';

            // Excel no admite más de 31 caracteres ni : \ / ? * [ ] en el nombre de la hoja
            $nombre = mb_substr(str_replace([':', '\\', '/', '?', '*', '[', ']'], ' ', $hoja['nombre']), 0, 31, 'UTF-8');
            $tipos   .= '<Override PartName="/xl/worksheets/sheet' . $n . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
            $hojasWb .= '<sheet name="' . $esc($nombre) . '" sheetId="' . $n . '" r:id="rId' . $n . '"/>';
            $relsWb  .= '<Relationship Id="rId' . $n . '" Type="' . $nsRel . '/worksheet" Target="worksheets/sheet' . $n . '.xml"/>';
        }
        $idEstilos = count($hojas) + 1;

        $archivos['[Content_Types].xml'] = $cab . '<Types xmlns="' . $nsPkg . '/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . $tipos
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
        $archivos['_rels/.rels'] = $cab . '<Relationships xmlns="' . $nsPkg . '/relationships">'
            . '<Relationship Id="rId1" Type="' . $nsRel . '/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
        $archivos['xl/workbook.xml'] = $cab . '<workbook xmlns="' . $nsMain . '" xmlns:r="' . $nsRel . '">'
            . '<sheets>' . $hojasWb . '</sheets></workbook>';
        $archivos['xl/_rels/workbook.xml.rels'] = $cab . '<Relationships xmlns="' . $nsPkg . '/relationships">'
            . $relsWb
            . '<Relationship Id="rId' . $idEstilos . '" Type="' . $nsRel . '/styles" Target="styles.xml"/>'
            . '</Relationships>';
        // Estilos: 0 = normal, 1 = encabezado (negrita blanca sobre azul), 2 = celda con texto ajustado
        $archivos['xl/styles.xml'] = $cab . '<styleSheet xmlns="' . $nsMain . '">'
            . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts>'
            . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF0071E3"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="3"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
            . '</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';

        $tmp = tempnam(sys_get_temp_dir(), 'xls');
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::OVERWRITE);
        foreach ($archivos as $ruta => $contenido) {
            $zip->addFromString($ruta, $contenido);
        }
        $zip->close();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '.xlsx"');
        header('Content-Length: ' . filesize($tmp));
        header('Cache-Control: max-age=0');
        readfile($tmp);
        unlink($tmp);
    }

    /**
     * Lee un .xlsx y devuelve [nombreHoja => [fila => [columna => valor texto]]], con filas y columnas desde 0.
     * Las filas totalmente vacías se omiten pero conservan su número (clave) para reportar errores.
     */
    public static function leer($ruta) {
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('El servidor no tiene la extensión ZIP de PHP, necesaria para leer el Excel.');
        }
        $zip = new ZipArchive();
        if ($zip->open($ruta) !== true) {
            throw new RuntimeException('El archivo no es un Excel válido (.xlsx).');
        }

        $leerXml = function ($nombre) use ($zip) {
            $contenido = $zip->getFromName($nombre);
            if ($contenido === false) return null;
            $xml = @simplexml_load_string($contenido);
            return $xml === false ? null : $xml;
        };

        // Textos compartidos
        $compartidos = [];
        $sst = $leerXml('xl/sharedStrings.xml');
        if ($sst) {
            foreach ($sst->si as $si) {
                $compartidos[] = self::textoDe($si);
            }
        }

        // Hojas y sus archivos
        $wb = $leerXml('xl/workbook.xml');
        $rels = $leerXml('xl/_rels/workbook.xml.rels');
        if (!$wb || !$rels) {
            $zip->close();
            throw new RuntimeException('El archivo no es un Excel válido (.xlsx).');
        }
        $destinos = [];
        foreach ($rels->Relationship as $rel) {
            $destinos[(string)$rel['Id']] = (string)$rel['Target'];
        }

        $resultado = [];
        foreach ($wb->sheets->sheet as $sheet) {
            $atrsR = $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $destino = $destinos[(string)$atrsR['id']] ?? '';
            $destino = ltrim($destino, '/');
            if (strpos($destino, 'xl/') !== 0) $destino = 'xl/' . $destino;

            $xmlHoja = $leerXml($destino);
            $filas = [];
            if ($xmlHoja && isset($xmlHoja->sheetData)) {
                foreach ($xmlHoja->sheetData->row as $row) {
                    $numFila = (int)$row['r'] - 1;
                    $fila = [];
                    foreach ($row->c as $c) {
                        $col = self::indiceColumna(preg_replace('/\d+/', '', (string)$c['r']));
                        $tipo = (string)$c['t'];
                        if ($tipo === 's') {
                            $valor = $compartidos[(int)$c->v] ?? '';
                        } elseif ($tipo === 'inlineStr') {
                            $valor = self::textoDe($c->is);
                        } elseif ($tipo === 'b') {
                            $valor = ((string)$c->v) === '1' ? 'SI' : 'NO';
                        } else {
                            $valor = (string)$c->v;
                            // Evita "0.30000000000000004" de los números con decimales
                            if ($valor !== '' && is_numeric($valor) && strpos($valor, '.') !== false) {
                                $valor = (string)round((float)$valor, 6);
                            }
                        }
                        $fila[$col] = trim($valor);
                    }
                    if (implode('', $fila) !== '') $filas[$numFila] = $fila;
                }
            }
            $resultado[(string)$sheet['name']] = $filas;
        }
        $zip->close();
        return $resultado;
    }

    private static function textoDe($nodo) {
        if ($nodo === null) return '';
        if (isset($nodo->t)) {
            $texto = '';
            // Un texto puede venir partido en varios "runs" con formato distinto
            if (isset($nodo->r)) {
                foreach ($nodo->r as $run) $texto .= (string)$run->t;
                return $texto !== '' ? $texto : (string)$nodo->t;
            }
            return (string)$nodo->t;
        }
        $texto = '';
        foreach ($nodo->r as $run) $texto .= (string)$run->t;
        return $texto;
    }

    private static function esNumero($valor) {
        if (is_int($valor) || is_float($valor)) return true;
        if (!is_string($valor) || $valor === '' || !is_numeric($valor)) return false;
        // "007" o "1e5" se dejan como texto para no alterarlos
        return !preg_match('/^0\d/', $valor) && stripos($valor, 'e') === false;
    }

    // Índice de columna (0 = A, 25 = Z, 26 = AA…)
    public static function columna($i) {
        $letras = '';
        for ($i++; $i > 0; $i = intdiv($i - 1, 26)) {
            $letras = chr(65 + ($i - 1) % 26) . $letras;
        }
        return $letras;
    }

    private static function indiceColumna($letras) {
        $n = 0;
        foreach (str_split(strtoupper($letras)) as $l) {
            $n = $n * 26 + (ord($l) - 64);
        }
        return $n - 1;
    }
}
