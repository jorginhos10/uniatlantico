<?php
// vista/organigrama/index.php
// Misma pantalla (maquetación y estilos) que Jerarquías en new-ua.
require_once __DIR__ . '/../../config/security.php';

$titulo       = 'Organigrama';
$paginaActual = 'organigrama';
$basePath     = Config::getBasePath();
$baseUrl      = Config::getBaseUrl();

ob_start();
?>
<style>
    .org-ua {
        --color-primario: #0071e3;
        --color-primario-oscuro: #0058b0;
        --color-primario-suave: #e8f2ff;
        --color-texto: #1d1d1f;
        --color-texto-secundario: #424245;
        --color-texto-tenue: #86868b;
        --color-fondo: #f5f5f7;
        --color-borde: #d2d2d7;
        --color-borde-hover: #aeaeb2;
        --color-superficie: #ffffff;
        --color-exito-fondo: #e3f8e9;
        --color-exito-texto: #1f8a3d;
        --color-error-fondo: #ffebe9;
        --color-error-texto: #d70015;
        --radio: 10px;
        --radio-chico: 6px;
        --sombra: 0 1px 2px rgba(0, 0, 0, 0.04), 0 1px 6px rgba(0, 0, 0, 0.05);
        --transicion: 0.15s ease;
        color: var(--color-texto);
    }
    .org-ua, .org-ua * { box-sizing: border-box; }
    .org-ua h1, .org-ua h2 { color: var(--color-texto); margin: 0 0 0.5rem; font-weight: 700; }
    .org-ua h2 { font-size: 1.5rem; }
    .org-ua p { margin: 0 0 1rem; }

    .org-ua .tarjeta {
        background: var(--color-superficie);
        padding: 1.75rem 2rem;
        border-radius: var(--radio);
        box-shadow: var(--sombra);
    }
    .org-ua .tarjeta + .tarjeta { margin-top: 1.5rem; }
    .org-ua .tarjeta h1 { font-size: 1.3rem; }
    .org-ua .texto-atenuado { color: var(--color-texto-tenue); }

    .org-ua .cabecera-modulo {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    .org-ua .cabecera-modulo h1 { margin: 0; }
    .org-ua .cabecera-modulo p { margin: 0; }

    .org-ua .selector-modo-vista {
        display: flex;
        gap: 0.3rem;
        background: var(--color-fondo);
        padding: 0.25rem;
        border-radius: 999px;
        flex-shrink: 0;
    }
    .org-ua .selector-modo-vista-boton {
        border: none;
        background: none;
        font-family: inherit;
        padding: 0.4rem 0.9rem;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--color-texto-secundario);
        cursor: pointer;
        white-space: nowrap;
        transition: background var(--transicion), color var(--transicion);
    }
    .org-ua .selector-modo-vista-boton.activo {
        background: var(--color-superficie);
        color: var(--color-primario);
        box-shadow: var(--sombra);
    }

    .org-ua .mensaje-error,
    .org-ua .mensaje-exito {
        padding: 0.6rem 0.75rem;
        border-radius: var(--radio-chico);
        font-size: 0.85rem;
        text-align: left;
        margin-bottom: 1rem;
    }
    .org-ua .mensaje-error { background: var(--color-error-fondo); color: var(--color-error-texto); }
    .org-ua .mensaje-exito { background: var(--color-exito-fondo); color: var(--color-exito-texto); }

    /* Formulario */
    .org-ua .form-necesidad {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.1rem;
        margin-bottom: 2rem;
    }
    .org-ua .campo { display: flex; flex-direction: column; min-width: 0; }
    .org-ua .campo-ancho { grid-column: span 3; }
    .org-ua .campo label {
        margin-bottom: 0.35rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--color-texto-secundario);
    }
    .org-ua .campo input,
    .org-ua .campo select {
        width: 100%;
        min-width: 0;
        padding: 0.55rem 0.7rem;
        border: 1px solid var(--color-borde);
        border-radius: var(--radio-chico);
        font-family: inherit;
        font-size: 0.9rem;
        color: var(--color-texto);
        background-color: #fff;
        transition: border-color var(--transicion), box-shadow var(--transicion);
    }
    .org-ua .campo input::placeholder { color: var(--color-texto-tenue); }
    .org-ua .campo select {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2386868b'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z' clip-rule='evenodd'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.65rem center;
        background-size: 1.1rem;
        padding-right: 2.2rem;
        cursor: pointer;
    }
    .org-ua .campo input:hover,
    .org-ua .campo select:hover { border-color: var(--color-borde-hover); }
    .org-ua .campo input:focus,
    .org-ua .campo select:focus {
        outline: none;
        border-color: var(--color-primario);
        box-shadow: 0 0 0 3px var(--color-primario-suave);
    }

    .org-ua .campo .campo-checkbox,
    .org-ua .campo-checkbox {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
        font-size: 0.9rem;
        font-weight: 400;
        color: var(--color-texto);
        cursor: pointer;
    }
    .org-ua .campo .campo-checkbox input {
        width: 16px;
        height: 16px;
        padding: 0;
        cursor: pointer;
    }
    .org-ua .grupo-checkbox-roles {
        display: flex;
        flex-wrap: wrap;
        column-gap: 1.5rem;
        row-gap: 0.3rem;
        padding: 0.75rem 1rem;
        border: 1px solid var(--color-borde);
        border-radius: var(--radio-chico);
        background: var(--color-fondo);
    }

    .org-ua .boton-enviar {
        grid-column: span 3;
        justify-self: start;
        padding: 0.65rem 1.6rem;
        border: none;
        border-radius: var(--radio-chico);
        background: var(--color-primario);
        color: #fff;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: background var(--transicion);
    }
    .org-ua .boton-enviar:hover { background: var(--color-primario-oscuro); }

    @media (max-width: 900px) {
        .org-ua .form-necesidad { grid-template-columns: 1fr; }
        .org-ua .campo-ancho, .org-ua .boton-enviar { grid-column: span 1; }
    }

    /* Tabla */
    .org-ua .tabla-scroll {
        overflow: auto;
        border: 1px solid var(--color-borde);
        border-radius: var(--radio-chico);
        scrollbar-width: thin;
    }
    .org-ua .tabla-usuarios { width: 100%; border-collapse: collapse; }
    .org-ua .tabla-usuarios th,
    .org-ua .tabla-usuarios td {
        text-align: left;
        padding: 0.7rem 0.75rem;
        border-bottom: 1px solid var(--color-borde);
        font-size: 0.88rem;
    }
    .org-ua .tabla-usuarios th {
        color: var(--color-texto-tenue);
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .org-ua .tabla-usuarios tbody tr:hover { background: #fafafa; }
    .org-ua .tabla-usuarios tbody tr:last-child td { border-bottom: none; }
    .org-ua .celda-fila-menu { width: 2.5rem; text-align: center; }

    .org-ua .fila-menu { position: relative; display: inline-block; }
    .org-ua .fila-menu-boton {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1.1rem;
        line-height: 1;
        color: var(--color-texto-tenue);
        padding: 0.3rem 0.5rem;
        border-radius: var(--radio-chico);
        transition: background var(--transicion), color var(--transicion);
    }
    .org-ua .fila-menu-boton:hover { background: var(--color-fondo); color: var(--color-texto); }
    .org-ua .fila-menu-dropdown {
        display: none;
        position: absolute;
        left: 0;
        top: calc(100% + 0.35rem);
        background: var(--color-superficie);
        border: 1px solid var(--color-borde);
        border-radius: var(--radio-chico);
        box-shadow: var(--sombra);
        min-width: 140px;
        z-index: 10;
        overflow: hidden;
    }
    .org-ua .fila-menu-dropdown.abierto { display: block; }
    .org-ua .fila-menu-dropdown form { margin: 0; }
    .org-ua .fila-menu-editar,
    .org-ua .fila-menu-eliminar {
        display: block;
        width: 100%;
        text-align: left;
        padding: 0.6rem 0.9rem;
        background: none;
        border: none;
        font-size: 0.85rem;
        font-family: inherit;
        color: var(--color-texto-secundario);
        cursor: pointer;
        transition: background var(--transicion);
    }
    .org-ua .fila-menu-editar:hover,
    .org-ua .fila-menu-eliminar:hover { background: var(--color-fondo); }
    .org-ua .fila-menu-eliminar { color: var(--color-error-texto); }

    .org-ua .arbol-nombre { display: flex; align-items: center; }
    .org-ua .arbol-guia {
        display: inline-block;
        width: 20px;
        height: 1.5rem;
        flex-shrink: 0;
        position: relative;
    }
    .org-ua .arbol-guia::before {
        content: '';
        position: absolute;
        left: 9px;
        top: 0;
        bottom: 0;
        border-left: 1.5px solid var(--color-borde);
    }
    .org-ua .arbol-guia-final::before { bottom: 50%; }
    .org-ua .arbol-guia-final::after {
        content: '';
        position: absolute;
        left: 9px;
        top: 50%;
        width: 11px;
        border-top: 1.5px solid var(--color-borde);
        border-bottom-left-radius: 6px;
    }
    .org-ua .arbol-etiqueta { font-size: 0.9rem; color: var(--color-texto); }
    .org-ua .arbol-etiqueta.nivel-0 { font-weight: 700; font-size: 0.95rem; }
    .org-ua .arbol-etiqueta.nivel-1 { font-weight: 600; }
    .org-ua .arbol-etiqueta.nivel-3,
    .org-ua .arbol-etiqueta.nivel-4 { color: var(--color-texto-secundario); }

    .org-ua .form-toggle { display: flex; align-items: center; gap: 0.5rem; margin: 0; }
    .org-ua .interruptor {
        position: relative;
        width: 40px;
        height: 22px;
        border: none;
        border-radius: 999px;
        background: #c7c7cc;
        cursor: pointer;
        padding: 0;
        flex-shrink: 0;
        transition: background var(--transicion);
    }
    .org-ua .interruptor-perilla {
        position: absolute;
        top: 2px;
        left: 2px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
        transition: transform var(--transicion);
    }
    .org-ua .interruptor-activo { background: var(--color-exito-texto); }
    .org-ua .interruptor-activo .interruptor-perilla { transform: translateX(18px); }
    .org-ua .interruptor-etiqueta { font-size: 0.78rem; font-weight: 600; color: var(--color-texto-tenue); }

    /* Organigrama (mapa) */
    .org-ua .organigrama-envoltorio { overflow-x: auto; padding: 1rem 0; }
    .org-ua .organigrama { display: table; margin: 0 auto; padding: 0; text-align: center; }
    .org-ua .organigrama ul {
        display: table;
        margin: 0 auto;
        padding: 24px 0 0;
        position: relative;
    }
    .org-ua .organigrama li {
        display: inline-table;
        text-align: center;
        list-style-type: none;
        position: relative;
        padding: 24px 10px 0 10px;
        vertical-align: top;
    }
    .org-ua .organigrama li::before,
    .org-ua .organigrama li::after {
        content: '';
        position: absolute;
        top: 0;
        right: 50%;
        width: 50%;
        height: 24px;
        border-top: 2px solid var(--color-borde);
    }
    .org-ua .organigrama li::after {
        right: auto;
        left: 50%;
        border-left: 2px solid var(--color-borde);
    }
    .org-ua .organigrama li:only-child { padding-top: 0; }
    .org-ua .organigrama li:only-child::before,
    .org-ua .organigrama li:only-child::after { display: none; }
    .org-ua .organigrama li:first-child::before,
    .org-ua .organigrama li:last-child::after { border: 0 none; }
    .org-ua .organigrama li:last-child::before {
        border-right: 2px solid var(--color-borde);
        border-radius: 0 5px 0 0;
    }
    .org-ua .organigrama li:first-child::after { border-radius: 5px 0 0 0; }
    .org-ua .organigrama ul ul::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        width: 0;
        height: 24px;
        border-left: 2px solid var(--color-borde);
    }
    /* Varias raíces: no cuelgan de nadie, así que van sin líneas encima */
    .org-ua .organigrama > li { padding-top: 0; }
    .org-ua .organigrama > li::before,
    .org-ua .organigrama > li::after { display: none; }

    .org-ua .organigrama-caja {
        position: relative;
        display: inline-flex;
        flex-direction: column;
        align-items: stretch;
        min-width: 150px;
        max-width: none;
        border-radius: 8px;
        background: #1d5b7a;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.18);
        overflow: hidden;
    }
    .org-ua .organigrama-cabecera {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.2rem;
        padding: 0.85rem 1.1rem;
        text-align: center;
    }
    .org-ua .organigrama-nombre { font-weight: 700; font-size: 1rem; color: #fff; word-break: break-word; }
    .org-ua .organigrama-inactivo {
        font-size: 0.68rem;
        color: rgba(255, 255, 255, 0.7);
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .org-ua .organigrama-roles {
        display: flex;
        flex-wrap: nowrap;
        justify-content: center;
        gap: 0.35rem;
        padding: 0.55rem 0.8rem;
        background: rgba(255, 255, 255, 0.94);
        border-top: 1px solid rgba(0, 0, 0, 0.08);
    }
    .org-ua .organigrama-rol-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.68rem;
        font-weight: 600;
        color: var(--color-texto);
        background: var(--color-fondo);
        border-radius: 999px;
        padding: 0.15rem 0.6rem 0.15rem 0.15rem;
        white-space: nowrap;
    }
    .org-ua .organigrama-rol-numero {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 16px;
        height: 16px;
        padding: 0 3px;
        border-radius: 999px;
        font-size: 0.62rem;
        font-weight: 700;
        color: #fff;
        background: #86868b;
        flex-shrink: 0;
    }
    .org-ua .organigrama-rol-numero.nivel-1 { background: #2e9e5b; }
    .org-ua .organigrama-rol-numero.nivel-2 { background: #2f6fed; }
    .org-ua .organigrama-rol-numero.nivel-3 { background: #e8892e; }

    /* Modal */
    .org-ua .modal-fondo {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.55);
        align-items: center;
        justify-content: center;
        padding: 2rem;
        z-index: 2000;
    }
    .org-ua .modal-fondo.abierto { display: flex; }
    .org-ua .modal-caja {
        background: var(--color-superficie);
        border-radius: var(--radio);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        width: 100%;
        max-width: 920px;
        max-height: 90vh;
        overflow-y: auto;
        padding: 1.75rem 2rem;
    }
    .org-ua .modal-cabecera {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: -1.75rem -2rem 0;
        padding: 1.75rem 2rem 1.1rem;
        border-bottom: 1px solid var(--color-borde);
        position: sticky;
        top: 0;
        background: var(--color-superficie);
        border-radius: var(--radio) var(--radio) 0 0;
        z-index: 1;
    }
    .org-ua .modal-cabecera + * { margin-top: 1.5rem; }
    .org-ua .modal-cabecera h2 { margin: 0; font-size: 1.15rem; }
    .org-ua .modal-cerrar {
        background: none;
        border: none;
        font-size: 1.5rem;
        line-height: 1;
        color: var(--color-texto-tenue);
        cursor: pointer;
        padding: 0.15rem 0.5rem;
        border-radius: var(--radio-chico);
        transition: background var(--transicion), color var(--transicion);
    }
    .org-ua .modal-cerrar:hover { background: var(--color-fondo); color: var(--color-texto); }
</style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>

<?php
$esAdminOrganigrama = !empty($esAdminOrganigrama);
$nodosPlanos        = $nodosPlanos ?? [];
$nodosAnidados      = $nodosAnidados ?? [];
$padresDisponibles  = $padresDisponibles ?? [];
$roles              = $roles ?? [];
$usuariosPorRol     = $usuariosPorRol ?? [];
$accionFormulario   = $basePath . '/organigrama';

function org_normalizar($s) {
    $s = mb_strtolower(trim((string)$s), 'UTF-8');
    return strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u']);
}

// Usuarios activos cuyo rol coincide (sin acentos/mayúsculas) con el rol del nodo
function org_buscarUsuariosPorRol($usuariosPorRol, $patron) {
    foreach ($usuariosPorRol as $rol => $usuarios) {
        if (org_normalizar($rol) === org_normalizar($patron)) {
            return $usuarios;
        }
    }
    return [];
}

// Nombre legible de cada rol (el nodo guarda el valor tal como está en usuarios.rol)
$nombresRol = [];
foreach ($roles as $r) {
    $nombresRol[org_normalizar($r['slug'])] = $r['nombre'];
}

// Dibuja un nodo y sus hijos. Los nodos inactivos (y su rama) solo los ve el administrador.
$dibujarNodo = function (array $nodo) use (&$dibujarNodo, $usuariosPorRol, $nombresRol, $esAdminOrganigrama): void {
    echo '<li>';
    echo '<div class="organigrama-caja">';
    echo '<div class="organigrama-cabecera">';
    echo '<span class="organigrama-nombre">' . htmlspecialchars($nodo['nombre']) . '</span>';
    if ((int)$nodo['activo'] !== 1) {
        echo '<span class="organigrama-inactivo">Inactivo</span>';
    }
    echo '</div>';
    if (!empty($nodo['roles'])) {
        echo '<div class="organigrama-roles">';
        foreach ($nodo['roles'] as $i => $rol) {
            // El número es la cantidad de usuarios activos con ese rol; sus nombres van en el título
            $usuarios = org_buscarUsuariosPorRol($usuariosPorRol, $rol);
            $nombres  = $usuarios ? implode(', ', array_column($usuarios, 'nombre')) : 'Sin usuarios asignados';
            echo '<span class="organigrama-rol-chip" title="' . htmlspecialchars($nombres) . '">';
            echo '<span class="organigrama-rol-numero nivel-' . min($i + 1, 3) . '">' . count($usuarios) . '</span>';
            echo htmlspecialchars($nombresRol[org_normalizar($rol)] ?? $rol);
            echo '</span>';
        }
        echo '</div>';
    }
    echo '</div>';

    $hijos = array_values(array_filter($nodo['hijos'], function ($h) use ($esAdminOrganigrama) {
        return $esAdminOrganigrama || (int)$h['activo'] === 1;
    }));
    if (!empty($hijos)) {
        echo '<ul>';
        foreach ($hijos as $hijo) {
            $dibujarNodo($hijo);
        }
        echo '</ul>';
    }
    echo '</li>';
};

$raicesVisibles = array_values(array_filter($nodosAnidados, function ($n) use ($esAdminOrganigrama) {
    return $esAdminOrganigrama || (int)$n['activo'] === 1;
}));
?>

<div class="org-ua">
    <?php if ($esAdminOrganigrama): ?>
    <div class="tarjeta">
        <div class="cabecera-modulo">
            <div>
                <h1>Organigrama</h1>
                <p class="texto-atenuado">Árbol de la estructura organizacional (administrador, vicerrectorías, líderes y gestores).</p>
            </div>
            <div class="selector-modo-vista">
                <button type="button" class="selector-modo-vista-boton activo" data-modo-vista="estructura">Estructural</button>
                <button type="button" class="selector-modo-vista-boton" data-modo-vista="mapa">Mapa</button>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <p class="mensaje-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if (!empty($exito)): ?>
            <p class="mensaje-exito"><?= htmlspecialchars($exito) ?></p>
        <?php endif; ?>

        <div data-vista-jerarquia="estructura">
        <form method="POST" action="<?= htmlspecialchars($accionFormulario) ?>" class="form-necesidad">
            <div class="campo">
                <label for="nombre">Nombre *</label>
                <input type="text" id="nombre" name="nombre" maxlength="150" placeholder="Ej. Líder de Metas" required>
            </div>

            <div class="campo">
                <label for="padre_id">Depende de</label>
                <select id="padre_id" name="padre_id">
                    <option value="0">— Sin padre (raíz) —</option>
                    <?php foreach ($padresDisponibles as $padreOpcion): ?>
                    <option value="<?= (int) $padreOpcion['id'] ?>"><?= htmlspecialchars($padreOpcion['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo">
                <label for="orden">Orden</label>
                <input type="number" id="orden" name="orden" value="0" min="0">
            </div>

            <div class="campo campo-ancho">
                <label>Roles asociados</label>
                <div id="roles" class="grupo-checkbox-roles">
                    <?php foreach ($roles as $rolCatalogo): ?>
                    <label class="campo-checkbox">
                        <input type="checkbox" name="roles[]" value="<?= htmlspecialchars($rolCatalogo['slug']) ?>">
                        <?= htmlspecialchars($rolCatalogo['nombre']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="boton-enviar">Agregar</button>
        </form>

        <div class="tabla-scroll">
            <table class="tabla-usuarios">
                <thead>
                    <tr>
                        <th></th>
                        <th>Nombre</th>
                        <th>Depende de</th>
                        <th>Roles</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($nodosPlanos as $nodo): ?>
                    <?php
                    $rolesLegibles = array_map(function ($rol) use ($nombresRol) {
                        return $nombresRol[org_normalizar($rol)] ?? $rol;
                    }, $nodo['roles']);
                    ?>
                    <tr>
                        <td class="celda-fila-menu">
                            <div class="fila-menu">
                                <button type="button" class="fila-menu-boton" aria-label="Más opciones">⋯</button>
                                <div class="fila-menu-dropdown">
                                    <button
                                        type="button"
                                        class="fila-menu-editar"
                                        data-id="<?= (int) $nodo['id'] ?>"
                                        data-nombre="<?= htmlspecialchars($nodo['nombre']) ?>"
                                        data-padre-id="<?= (int) ($nodo['padre_id'] ?? 0) ?>"
                                        data-orden="<?= (int) $nodo['orden'] ?>"
                                        data-roles="<?= htmlspecialchars(json_encode($nodo['roles'], JSON_UNESCAPED_UNICODE)) ?>"
                                    >Editar</button>
                                    <form method="POST" action="<?= htmlspecialchars($accionFormulario) ?>" class="form-eliminar-jerarquia">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="<?= (int) $nodo['id'] ?>">
                                        <button type="submit" class="fila-menu-eliminar">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="arbol-nombre">
                                <?php for ($i = 0; $i < (int) $nodo['nivel'] - 1; $i++): ?>
                                <span class="arbol-guia"></span>
                                <?php endfor; ?>
                                <?php if ((int) $nodo['nivel'] > 0): ?>
                                <span class="arbol-guia arbol-guia-final"></span>
                                <?php endif; ?>
                                <span class="arbol-etiqueta nivel-<?= min((int) $nodo['nivel'], 4) ?>"><?= htmlspecialchars($nodo['nombre']) ?></span>
                            </div>
                        </td>
                        <td class="texto-atenuado"><?= $nodo['nombre_padre'] !== null ? '↳ ' . htmlspecialchars($nodo['nombre_padre']) : '—' ?></td>
                        <td class="texto-atenuado"><?= !empty($rolesLegibles) ? htmlspecialchars(implode(', ', $rolesLegibles)) : '—' ?></td>
                        <td>
                            <form method="POST" action="<?= htmlspecialchars($accionFormulario) ?>" class="form-toggle">
                                <input type="hidden" name="accion" value="cambiar_estado">
                                <input type="hidden" name="id" value="<?= (int) $nodo['id'] ?>">
                                <button type="submit" class="interruptor <?= (int) $nodo['activo'] === 1 ? 'interruptor-activo' : '' ?>" aria-label="Cambiar estado" title="<?= (int) $nodo['activo'] === 1 ? 'Activo (clic para desactivar)' : 'Inactivo (clic para activar)' ?>">
                                    <span class="interruptor-perilla"></span>
                                </button>
                                <span class="interruptor-etiqueta"><?= (int) $nodo['activo'] === 1 ? 'Activo' : 'Inactivo' ?></span>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($nodosPlanos)): ?>
                    <tr>
                        <td colspan="5">No hay nodos registrados.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="tarjeta" data-vista-jerarquia="mapa" <?= $esAdminOrganigrama ? 'hidden' : '' ?>>
        <h2>Organigrama</h2>

        <?php if (empty($raicesVisibles)): ?>
        <p class="texto-atenuado">No hay nodos registrados.</p>
        <?php else: ?>
        <div class="organigrama-envoltorio"><ul class="organigrama">
            <?php foreach ($raicesVisibles as $raiz) { $dibujarNodo($raiz); } ?>
        </ul></div>
        <?php endif; ?>
    </div>

    <?php if ($esAdminOrganigrama): ?>
    <div id="modal-editar-jerarquia" class="modal-fondo">
        <div class="modal-caja">
            <div class="modal-cabecera">
                <h2>Editar nodo</h2>
                <button type="button" id="boton-cerrar-modal-editar-jerarquia" class="modal-cerrar" aria-label="Cerrar">&times;</button>
            </div>

            <form method="POST" action="<?= htmlspecialchars($accionFormulario) ?>" class="form-necesidad">
                <input type="hidden" name="accion" value="actualizar">
                <input type="hidden" name="id" id="editar-jerarquia-id" value="">

                <div class="campo">
                    <label for="editar-jerarquia-nombre">Nombre *</label>
                    <input type="text" id="editar-jerarquia-nombre" name="nombre" maxlength="150" required>
                </div>

                <div class="campo">
                    <label for="editar-jerarquia-padre">Depende de</label>
                    <select id="editar-jerarquia-padre" name="padre_id">
                        <option value="0">— Sin padre (raíz) —</option>
                        <?php foreach ($padresDisponibles as $padreOpcion): ?>
                        <option value="<?= (int) $padreOpcion['id'] ?>"><?= htmlspecialchars($padreOpcion['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label for="editar-jerarquia-orden">Orden</label>
                    <input type="number" id="editar-jerarquia-orden" name="orden" min="0">
                </div>

                <div class="campo campo-ancho">
                    <label>Roles asociados</label>
                    <div id="editar-jerarquia-roles" class="grupo-checkbox-roles">
                        <?php foreach ($roles as $rolCatalogo): ?>
                        <label class="campo-checkbox">
                            <input type="checkbox" name="roles[]" value="<?= htmlspecialchars($rolCatalogo['slug']) ?>">
                            <?= htmlspecialchars($rolCatalogo['nombre']) ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="boton-enviar">Guardar cambios</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if ($esAdminOrganigrama): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Selector Estructural / Mapa (se recuerda la vista al recargar tras guardar)
    var CLAVE_MODO = 'organigrama_modo';
    var botonesModoVista = document.querySelectorAll('[data-modo-vista]');

    function mostrarModo(modo) {
        botonesModoVista.forEach(function (boton) {
            boton.classList.toggle('activo', boton.dataset.modoVista === modo);
        });
        document.querySelectorAll('[data-vista-jerarquia]').forEach(function (seccion) {
            seccion.hidden = seccion.dataset.vistaJerarquia !== modo;
        });
        sessionStorage.setItem(CLAVE_MODO, modo);
    }

    botonesModoVista.forEach(function (boton) {
        boton.addEventListener('click', function () { mostrarModo(boton.dataset.modoVista); });
    });
    mostrarModo(sessionStorage.getItem(CLAVE_MODO) === 'mapa' ? 'mapa' : 'estructura');

    // Menú "⋯" de cada fila
    var menusFila = document.querySelectorAll('.org-ua .fila-menu');

    function cerrarTodos(exceptoEste) {
        menusFila.forEach(function (menu) {
            if (menu !== exceptoEste) {
                menu.querySelector('.fila-menu-dropdown').classList.remove('abierto');
            }
        });
    }

    menusFila.forEach(function (menu) {
        var dropdown = menu.querySelector('.fila-menu-dropdown');
        menu.querySelector('.fila-menu-boton').addEventListener('click', function (evento) {
            evento.stopPropagation();
            cerrarTodos(menu);
            dropdown.classList.toggle('abierto');
        });
    });

    document.addEventListener('click', function () { cerrarTodos(null); });

    document.querySelectorAll('.form-eliminar-jerarquia').forEach(function (formulario) {
        formulario.addEventListener('submit', function (evento) {
            if (!confirm('¿Eliminar este nodo? Esta acción no se puede deshacer.')) {
                evento.preventDefault();
            }
        });
    });

    // Modal de edición
    var modalEditar = document.getElementById('modal-editar-jerarquia');
    var campoId = document.getElementById('editar-jerarquia-id');
    var campoNombre = document.getElementById('editar-jerarquia-nombre');
    var campoPadre = document.getElementById('editar-jerarquia-padre');
    var campoOrden = document.getElementById('editar-jerarquia-orden');
    var contenedorRoles = document.getElementById('editar-jerarquia-roles');

    function normalizar(texto) {
        return String(texto).trim().toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    }

    document.querySelectorAll('.fila-menu-editar').forEach(function (boton) {
        boton.addEventListener('click', function () {
            cerrarTodos(null);
            campoId.value = boton.dataset.id;
            campoNombre.value = boton.dataset.nombre;
            campoPadre.value = boton.dataset.padreId;
            campoOrden.value = boton.dataset.orden;

            // Un nodo no puede depender de sí mismo
            Array.from(campoPadre.options).forEach(function (opcion) {
                opcion.disabled = opcion.value === boton.dataset.id;
            });

            // Marca los roles del nodo; si alguno ya no está en el catálogo, se agrega para no perderlo
            contenedorRoles.querySelectorAll('[data-rol-extra]').forEach(function (extra) { extra.remove(); });
            var casillas = Array.from(contenedorRoles.querySelectorAll('input[type="checkbox"]'));
            casillas.forEach(function (casilla) { casilla.checked = false; });
            JSON.parse(boton.dataset.roles || '[]').forEach(function (rol) {
                var casilla = casillas.find(function (c) { return normalizar(c.value) === normalizar(rol); });
                if (casilla) {
                    casilla.checked = true;
                    return;
                }
                var etiqueta = document.createElement('label');
                etiqueta.className = 'campo-checkbox';
                etiqueta.dataset.rolExtra = '1';
                var nueva = document.createElement('input');
                nueva.type = 'checkbox';
                nueva.name = 'roles[]';
                nueva.value = rol;
                nueva.checked = true;
                etiqueta.appendChild(nueva);
                etiqueta.appendChild(document.createTextNode(' ' + rol));
                contenedorRoles.appendChild(etiqueta);
            });

            modalEditar.classList.add('abierto');
        });
    });

    document.getElementById('boton-cerrar-modal-editar-jerarquia').addEventListener('click', function () {
        modalEditar.classList.remove('abierto');
    });

    modalEditar.addEventListener('click', function (evento) {
        if (evento.target === modalEditar) {
            modalEditar.classList.remove('abierto');
        }
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            modalEditar.classList.remove('abierto');
        }
    });
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
