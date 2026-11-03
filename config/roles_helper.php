<?php
// config/roles_helper.php — única fuente de verdad de «quién es qué»: antes cada pantalla comparaba el rol a su manera
// ('admin', 'administrador', id 1...) y daban resultados distintos.

class Roles {
    // Etapas del semáforo de aprobación del seguimiento: etapa => rol que la aprueba
    const ETAPAS = [
        1 => 'gestor de metas',
        2 => 'lider de meta',
        3 => 'responsable de linea',
        4 => 'sub administrador',
    ];

    // Etapa que el rol «salta» porque es de ese nivel o superior (el creador aprueba su propia etapa)
    const NIVEL = [
        'gestor de metas' => 1,
        'gestor de metas de responsable de linea' => 1,
        'lider de meta' => 2,
        'gestor de metas de sub-admin' => 2,
        'responsable de linea' => 3,
        'sub administrador' => 4,
    ];

    // Minúsculas y sin tildes: 'Líder de meta' y 'lider de meta' son el mismo rol
    public static function normalizar($rol) {
        $rol = mb_strtolower(trim((string)$rol), 'UTF-8');
        return strtr($rol, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);
    }

    public static function rolActual() {
        return self::normalizar($_SESSION['usuario_rol'] ?? '');
    }

    public static function usuarioId() {
        return (int)($_SESSION['usuario_id'] ?? 0);
    }

    // El superadministrador es siempre el usuario 1
    public static function esSuperadmin() {
        return self::usuarioId() === 1;
    }

    // Administración del SISTEMA (usuarios, configuración, copias de seguridad): rol 'admin' o el superadministrador
    public static function esAdminSistema() {
        return self::esSuperadmin() || self::rolActual() === 'admin';
    }

    // Administrador del plan de acción: además de lo anterior, el rol 'administrador'
    public static function esAdmin() {
        return self::esAdminSistema() || self::rolActual() === 'administrador';
    }

    // Ve todo el Módulo 144 sin filtrar por dependencia
    public static function puedeVerTodo144() {
        return self::esAdmin() || self::rolActual() === 'sub administrador';
    }

    // ¿Puede aprobar o rechazar la etapa $etapa (1 a 4) del semáforo?
    // El superadministrador aprueba cualquiera; la etapa final (4) también la aprueban los administradores
    public static function puedeAprobar($etapa) {
        $etapa = (int)$etapa;
        if (!isset(self::ETAPAS[$etapa])) return false;
        if (self::esSuperadmin()) return true;
        if ($etapa === 4 && in_array(self::rolActual(), ['admin', 'administrador', 'sub administrador'], true)) return true;
        return self::rolActual() === self::ETAPAS[$etapa];
    }

    // Nivel de aprobación que ya cubre un rol (0 si no es de los roles del semáforo)
    public static function nivel($rol) {
        return self::NIVEL[self::normalizar($rol)] ?? 0;
    }
}
