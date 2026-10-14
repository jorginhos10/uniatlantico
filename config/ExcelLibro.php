<?php
// config/ExcelLibro.php
// Escribe y lee archivos .xlsx con varias hojas, sin librerías externas (usa ZipArchive y SimpleXML).

class ExcelLibro {

    /**
     * Envía al navegador un .xlsx con varias hojas, con el formato institucional
     * (encabezado azul oscuro, bordes, filtros, primera(s) fila(s) fijas).
     *
     * Cada hoja: ['nombre' => ..., 'columnas' => [...], 'filas' => [[...], ...]]
     *   columna: ['titulo' => '16.1 NOMBRE DEL INDICADOR', 'grupo' => '16. INDICADOR DE RESULTADO' | null,
     *             'ancho' => 30, 'vertical' => false, 'formato' => null|'num2'|'pct0'|'pct2', 'sombreado' => false]
     *   Si alguna columna tiene "grupo", el encabezado ocupa dos filas: el grupo arriba (celdas combinadas)
     *   y el título debajo; las columnas sin grupo combinan sus dos filas.
     *   Un valor de celda puede ser un escalar o ['v' => valor, 'formato' => 'pct2'] para cambiar su formato.
     * También acepta el formato simple ['encabezados' => [...], 'anchos' => [...], 'filas' => [...]].
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

        // Estilos que se van creando según se usan: clave "tipo|formato|sombreado" → índice en cellXfs
        $estilos = [];
        $estilo = function ($tipo, $formato = null, $sombreado = false) use (&$estilos) {
            $clave = $tipo . '|' . ($formato ?? '') . '|' . ($sombreado ? 1 : 0);
            if (!isset($estilos[$clave])) $estilos[$clave] = count($estilos) + 1; // 0 = estilo por defecto
            return $estilos[$clave];
        };

        $tipoEncabezado = function (array $col) {
            if (!empty($col['vertical'])) return 'encV';
            return !empty($col['encabezadoClaro']) ? 'encC' : 'enc';
        };

        $archivos = [];
        $tipos = '';
        $hojasWb = '';
        $relsWb = '';
        $nombresDefinidos = '';

        foreach (array_values($hojas) as $i => $hoja) {
            $n = $i + 1;
            $columnas = $hoja['columnas'] ?? null;
            if ($columnas === null) {
                $columnas = [];
                foreach (array_values($hoja['encabezados']) as $c => $titulo) {
                    $columnas[] = ['titulo' => $titulo, 'ancho' => $hoja['anchos'][$c] ?? 14];
                }
            }
            $columnas = array_values($columnas);
            $nCols = count($columnas);
            $ultimaCol = self::columna($nCols - 1);
            $conGrupos = (bool)array_filter($columnas, function ($c) { return !empty($c['grupo']); });
            $filasEnc = $conGrupos ? 2 : 1;

            // ── Encabezados ──
            $xml = '';
            $combinadas = [];
            if ($conGrupos) {
                $fila1 = '<row r="1" ht="34" customHeight="1">';
                $fila2 = '<row r="2" ht="78" customHeight="1">';
                $c = 0;
                while ($c < $nCols) {
                    $col = $columnas[$c];
                    $tipoEnc = $tipoEncabezado($col);
                    if (empty($col['grupo'])) {
                        $fila1 .= self::celdaTexto(self::columna($c) . '1', $col['titulo'], $estilo($tipoEnc), $esc);
                        $fila2 .= '<c r="' . self::columna($c) . '2" s="' . $estilo($tipoEnc) . '"/>';
                        $combinadas[] = self::columna($c) . '1:' . self::columna($c) . '2';
                        $c++;
                        continue;
                    }
                    // Columnas seguidas con el mismo grupo
                    $fin = $c;
                    while ($fin + 1 < $nCols && ($columnas[$fin + 1]['grupo'] ?? null) === $col['grupo']) $fin++;
                    for ($k = $c; $k <= $fin; $k++) {
                        $fila1 .= $k === $c
                            ? self::celdaTexto(self::columna($k) . '1', $col['grupo'], $estilo('enc'), $esc)
                            : '<c r="' . self::columna($k) . '1" s="' . $estilo('enc') . '"/>';
                        $tipoK = $tipoEncabezado($columnas[$k]);
                        $fila2 .= self::celdaTexto(self::columna($k) . '2', $columnas[$k]['titulo'], $estilo($tipoK), $esc);
                    }
                    if ($fin > $c) $combinadas[] = self::columna($c) . '1:' . self::columna($fin) . '1';
                    $c = $fin + 1;
                }
                $xml .= $fila1 . '</row>' . $fila2 . '</row>';
            } else {
                $xml .= '<row r="1" ht="42" customHeight="1">';
                foreach ($columnas as $c => $col) {
                    $xml .= self::celdaTexto(self::columna($c) . '1', $col['titulo'], $estilo($tipoEncabezado($col)), $esc);
                }
                $xml .= '</row>';
            }

            // ── Datos ──
            $r = $filasEnc;
            foreach ($hoja['filas'] ?? [] as $fila) {
                $r++;
                $fila = array_values($fila);
                $xml .= '<row r="' . $r . '"' . ($fila ? ' ht="96" customHeight="1"' : '') . '>';
                for ($c = 0; $c < $nCols; $c++) {
                    $col = $columnas[$c];
                    $valor = $fila[$c] ?? null;
                    $formato = $col['formato'] ?? null;
                    if (is_array($valor)) {
                        $formato = array_key_exists('formato', $valor) ? $valor['formato'] : $formato;
                        $valor = $valor['v'] ?? null;
                    }
                    $s = $estilo('dato', $formato, !empty($col['sombreado']));
                    $ref = self::columna($c) . $r;
                    if ($valor === null || $valor === '') {
                        $xml .= '<c r="' . $ref . '" s="' . $s . '"/>';
                    } elseif (self::esNumero($valor)) {
                        $xml .= '<c r="' . $ref . '" s="' . $s . '"><v>' . (0 + $valor) . '</v></c>';
                    } else {
                        $xml .= self::celdaTexto($ref, $valor, $s, $esc);
                    }
                }
                $xml .= '</row>';
            }

            $xmlCols = '';
            foreach ($columnas as $c => $col) {
                $xmlCols .= '<col min="' . ($c + 1) . '" max="' . ($c + 1) . '" width="' . (float)($col['ancho'] ?? 14) . '" customWidth="1"/>';
            }

            $panel = '<pane ySplit="' . $filasEnc . '" topLeftCell="A' . ($filasEnc + 1) . '" activePane="bottomLeft" state="frozen"/>';
            $vista = '<sheetViews><sheetView workbookViewId="0"' . ($i === 0 ? ' tabSelected="1"' : '') . '>' . $panel . '</sheetView></sheetViews>';
            $rangoFiltro = 'A' . $filasEnc . ':' . $ultimaCol . max($r, $filasEnc);
            $xmlCombinadas = $combinadas
                ? '<mergeCells count="' . count($combinadas) . '">' . implode('', array_map(function ($m) { return '<mergeCell ref="' . $m . '"/>'; }, $combinadas)) . '</mergeCells>'
                : '';

            $archivos["xl/worksheets/sheet{$n}.xml"] = $cab . '<worksheet xmlns="' . $nsMain . '" xmlns:r="' . $nsRel . '">'
                . $vista . '<sheetFormatPr defaultRowHeight="15"/><cols>' . $xmlCols . '</cols>'
                . '<sheetData>' . $xml . '</sheetData>'
                . '<autoFilter ref="' . $rangoFiltro . '"/>' . $xmlCombinadas
                . '</worksheet>';

            // Excel no admite más de 31 caracteres ni : \ / ? * [ ] en el nombre de la hoja
            $nombre = mb_substr(str_replace([':', '\\', '/', '?', '*', '[', ']'], ' ', $hoja['nombre']), 0, 31, 'UTF-8');
            $tipos   .= '<Override PartName="/xl/worksheets/sheet' . $n . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
            $hojasWb .= '<sheet name="' . $esc($nombre) . '" sheetId="' . $n . '" r:id="rId' . $n . '"/>';
            $relsWb  .= '<Relationship Id="rId' . $n . '" Type="' . $nsRel . '/worksheet" Target="worksheets/sheet' . $n . '.xml"/>';
            // El filtro necesita su nombre definido para que Excel lo reconozca
            $nombresDefinidos .= '<definedName name="_xlnm._FilterDatabase" localSheetId="' . $i . '" hidden="1">\''
                . $esc(str_replace("'", "''", $nombre)) . '\'!$A$' . $filasEnc . ':$' . $ultimaCol . '$' . max($r, $filasEnc) . '</definedName>';
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
            . '<sheets>' . $hojasWb . '</sheets><definedNames>' . $nombresDefinidos . '</definedNames></workbook>';
        $archivos['xl/_rels/workbook.xml.rels'] = $cab . '<Relationships xmlns="' . $nsPkg . '/relationships">'
            . $relsWb
            . '<Relationship Id="rId' . $idEstilos . '" Type="' . $nsRel . '/styles" Target="styles.xml"/>'
            . '</Relationships>';
        $archivos['xl/styles.xml'] = self::hojaEstilos($estilos, $cab, $nsMain);

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

    private static function celdaTexto($ref, $texto, $s, callable $esc) {
        return '<c r="' . $ref . '" s="' . $s . '" t="inlineStr"><is><t xml:space="preserve">' . $esc($texto) . '</t></is></c>';
    }

    // Formato institucional: encabezado azul oscuro con letra blanca en negrita, datos centrados con borde azul
    private static function hojaEstilos(array $estilos, $cab, $nsMain) {
        $formatos = ['num2' => 164, 'pct0' => 165, 'pct2' => 166];
        $numFmts = '<numFmts count="3">'
            . '<numFmt numFmtId="164" formatCode="0.00"/>'
            . '<numFmt numFmtId="165" formatCode="0&quot;%&quot;"/>'
            . '<numFmt numFmtId="166" formatCode="0.00&quot;%&quot;"/>'
            . '</numFmts>';
        $fonts = '<fonts count="3"><font><sz val="10"/><name val="Arial"/></font>'
               . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Arial"/></font>'
               . '<font><b/><sz val="11"/><color rgb="FF14254A"/><name val="Arial"/></font></fonts>';
        // 0 none, 1 gray125 (obligatorios), 2 azul oscuro (encabezado), 3 azul claro (columnas calculadas)
        $fills = '<fills count="4"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
               . '<fill><patternFill patternType="solid"><fgColor rgb="FF14254A"/><bgColor indexed="64"/></patternFill></fill>'
               . '<fill><patternFill patternType="solid"><fgColor rgb="FFDCEEF5"/><bgColor indexed="64"/></patternFill></fill></fills>';
        $bordeAzul = '<left style="thin"><color rgb="FF1F4E9C"/></left><right style="thin"><color rgb="FF1F4E9C"/></right>'
                   . '<top style="thin"><color rgb="FF1F4E9C"/></top><bottom style="thin"><color rgb="FF1F4E9C"/></bottom><diagonal/>';
        $borders = '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border>' . $bordeAzul . '</border></borders>';

        $xfs = '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>';
        foreach ($estilos as $clave => $indice) {
            [$tipo, $formato, $sombreado] = explode('|', $clave);
            $numFmt = $formatos[$formato] ?? 0;
            if ($tipo === 'enc' || $tipo === 'encV' || $tipo === 'encC') {
                $giro = $tipo === 'encV' ? ' textRotation="90"' : '';
                $fuente = $tipo === 'encC' ? 2 : 1;
                $relleno = $tipo === 'encC' ? 3 : 2;
                $xfs .= '<xf numFmtId="0" fontId="' . $fuente . '" fillId="' . $relleno . '" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">'
                      . '<alignment horizontal="center" vertical="center" wrapText="1"' . $giro . '/></xf>';
            } else {
                $xfs .= '<xf numFmtId="' . $numFmt . '" fontId="0" fillId="' . ($sombreado ? 3 : 0) . '" borderId="1" xfId="0"'
                      . ($numFmt ? ' applyNumberFormat="1"' : '') . ($sombreado ? ' applyFill="1"' : '') . ' applyBorder="1" applyAlignment="1">'
                      . '<alignment horizontal="center" vertical="center" wrapText="1"/></xf>';
            }
        }
        return $cab . '<styleSheet xmlns="' . $nsMain . '">' . $numFmts . $fonts . $fills . $borders
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="' . (count($estilos) + 1) . '">' . $xfs . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
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

        // Estilos cuyo formato es porcentaje real de Excel (ej. 0%, 0.00%): la celda guarda 0,2 para "20%"
        $estiloPorcentaje = self::estilosPorcentaje($leerXml('xl/styles.xml'));

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
                            // "20%" escrito en Excel se guarda como 0.2: se devuelve 20, como los valores del sistema
                            if ($valor !== '' && is_numeric($valor) && !empty($estiloPorcentaje[(int)$c['s']])) {
                                $valor = (string)((float)$valor * 100);
                            }
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

    // [índice de estilo => true] para los estilos con formato de porcentaje real (no el "%" entre comillas)
    private static function estilosPorcentaje($estilos) {
        if (!$estilos) return [];
        $codigos = [];
        if (isset($estilos->numFmts)) {
            foreach ($estilos->numFmts->numFmt as $f) $codigos[(int)$f['numFmtId']] = (string)$f['formatCode'];
        }
        $resultado = [];
        if (isset($estilos->cellXfs)) {
            $i = 0;
            foreach ($estilos->cellXfs->xf as $xf) {
                $id = (int)$xf['numFmtId'];
                if ($id === 9 || $id === 10) {
                    $resultado[$i] = true;
                } elseif (isset($codigos[$id])) {
                    // Se quitan los textos entre comillas y los escapados (\%) antes de buscar el %
                    $codigo = preg_replace('/"[^"]*"|\\./', '', $codigos[$id]);
                    if (strpos($codigo, '%') !== false) $resultado[$i] = true;
                }
                $i++;
            }
        }
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
