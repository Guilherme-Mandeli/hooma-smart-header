# Ciclo de Vida del Módulo

Todos los módulos integrados con Hooma Core pueden implementar hooks de activación, actualización y desinstalación para realizar tareas del lado del servidor.

---

## 1. La Clase `Lifecycle` y la Interfaz

Para que un módulo responda a eventos de su ciclo de vida, debe cumplir dos condiciones físicas:

1.  Existir una clase llamada `Lifecycle` bajo el namespace raíz de tu módulo, en la ubicación física [includes/Lifecycle.php](../includes/Lifecycle.php).
2.  Implementar la interfaz global `Hooma_Module_Lifecycle_Interface`.

> [!IMPORTANT]
> **Modificar el namespace al crear tu módulo**:
> El namespace `HoomaModules\MiModulo` es ilustrativo. Al duplicar la plantilla `hooma-empty-module`, **debes actualizar obligatoriamente** la línea `namespace HoomaModules\HoomaEmptyModule;` de [Lifecycle.php](../includes/Lifecycle.php) por el de tu módulo (por ejemplo: `namespace HoomaModules\MiNuevoModulo;`). Si no se modifica, Hooma Core no podrá encontrar la clase para ejecutar la instalación/desinstalación.

### Estructura Mínima
```php
<?php

namespace HoomaModules\MiModulo;

use Hooma_Module_Lifecycle_Interface;

defined( 'HOOMA_PATH' ) || exit;

class Lifecycle implements Hooma_Module_Lifecycle_Interface {
    
    public static function install( array $manifest ) {
        // Ejecutado al instalar/activar
    }

    public static function update( array $old_manifest, array $new_manifest ) {
        // Ejecutado al subir una nueva versión física
    }

    public static function uninstall( array $manifest ) {
        // Ejecutado al borrar permanentemente
    }
}
```

---

## 2. Los Métodos del Ciclo de Vida

### A. Método `install` (Instalación)
Se ejecuta automáticamente una sola vez cuando el administrador hace clic en **Activar** en el gestor de módulos de Hooma Core.

*   **Propósito**: Preparación de infraestructura inicial.
*   **Tareas Recomendadas**:
    - Crear tablas personalizadas en la base de datos (con `dbDelta` o scripts SQL).
    - Establecer las opciones de configuración predeterminadas.
    - Registrar capacidades de seguridad en el sistema de roles de WordPress.

#### Ejemplo de creación de tablas en la instalación:
```php
public static function install( array $manifest ) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'hooma_mi_tabla';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
    
    // Guardar opción inicial por defecto
    Hooma::settings()->set( 'mi_modulo.default_limit', '10' );
}
```

---

### B. Método `update` (Actualización)
Se ejecuta de forma asíncrona cuando se instala una versión física del módulo (por ejemplo, subiendo un archivo `.zip` actualizado) que tiene un número de versión mayor que el registrado anteriormente en la base de datos de WordPress.

*   **Propósito**: Migraciones progresivas.
*   **Parámetros**:
    - `$old_manifest`: Array de metadatos de la versión del módulo antes del reemplazo.
    - `$new_manifest`: Array de metadatos de la nueva versión del módulo que se acaba de subir.
*   **Tareas Recomendadas**:
    - Alterar estructuras de tablas existentes (agregar nuevas columnas).
    - Mapear viejas opciones del módulo a nuevas configuraciones.

#### Ejemplo de actualización segura:
```php
public static function update( array $old_manifest, array $new_manifest ) {
    $old_version = $old_manifest['version'];
    $new_version = $new_manifest['version'];

    if ( version_compare( $old_version, '1.2.0', '<' ) ) {
        // Tareas de migración requeridas solo si la versión antigua era previa a la 1.2.0
        global $wpdb;
        $table_name = $wpdb->prefix . 'hooma_mi_tabla';
        
        // Agregar columna adicional de forma segura
        $wpdb->query( "ALTER TABLE $table_name ADD COLUMN status varchar(50) DEFAULT 'active' NOT NULL;" );
    }
}
```

---

### C. Método `uninstall` (Desinstalación)
Se ejecuta únicamente cuando el usuario hace clic en el botón de **Eliminar** (después de haberlo desactivado) en la pantalla de gestión de módulos de Hooma Core.

> [!WARNING]
> **Es fundamental realizar una limpieza estricta (Cleanup).** No dejes datos huérfanos o basura en las tablas globales de WordPress o del Core si el usuario decide deshacerse del módulo.

*   **Propósito**: Retorno de la base de datos al estado original previa instalación.
*   **Tareas Recomendadas**:
    - Eliminar tablas de base de datos creadas por el módulo.
    - Borrar todas las opciones del módulo de la tabla `wp_options`.
    - Eliminar roles o capacidades personalizadas y tareas programadas en el scheduler.

#### Ejemplo de limpieza completa en desinstalación:
```php
public static function uninstall( array $manifest ) {
    global $wpdb;
    
    // 1. Eliminar tablas personalizadas
    $table_name = $wpdb->prefix . 'hooma_mi_tabla';
    $wpdb->query( "DROP TABLE IF EXISTS $table_name;" );

    // 2. Limpiar todas las opciones del módulo guardadas
    // Si usaste Hooma::settings() con el namespace 'mi_modulo':
    Hooma::settings()->delete_namespace( 'mi_modulo' );
}
```
