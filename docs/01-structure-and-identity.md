# Estructura e Identidad del Módulo

Este documento describe la arquitectura física y el mecanismo de descubrimiento automático que utiliza Hooma Core para cargar y gestionar los módulos.

---

## 1. Estructura de Directorios Recomendada

La estructura mínima y limpia de un módulo es la siguiente:

```text
hooma-mi-modulo/
├── index.php                 # Entrada obligatoria del módulo (Manifest y Bootstrap)
├── config/
│   └── navigation.php        # Configuración de menús y pestañas de administración (Opcional)
├── includes/                 # Código PHP lógico (PSR-4 Autoloading)
│   ├── Lifecycle.php         # Gestor de instalación, actualización y desinstalación
│   └── ...                   # Clases lógicas organizadas (Controllers, Models, Services)
├── admin/
│   └── views/                # Archivos de vista (HTML/PHP de presentación administrativa)
│       └── index.php         # Vista cargada por defecto para la pestaña 'Inicio'
├── assets/                   # Recursos estáticos de frontend
│   ├── css/
│   │   ├── admin.css         # Estilos específicos del admin
│   │   └── frontend.css      # Estilos específicos del lado público
│   └── js/
│       ├── admin.js          # Scripts específicos del admin
│       └── frontend.js       # Scripts específicos del lado público
└── docs/                     # Documentación interna del módulo
```

---

## 2. Inyección de Identidad Dinámica

Para evitar el acoplamiento rígido de rutas físicas o namespaces hardcodeados, Hooma Core calcula la identidad de cada módulo basándose únicamente en el nombre del directorio físico (slug) en tiempo de ejecución.

Cuando el Core carga el archivo principal `index.php` de tu módulo, inyecta las siguientes variables en su ámbito local de ejecución:

*   **`$module_slug`**: El slug del módulo (nombre de su carpeta, ej: `hooma-mi-modulo`).
*   **`$module_namespace`**: El namespace base calculado en formato StudlyCase (ej: `HoomaMiModulo`).
*   **`$module_version`**: La versión declarada en el encabezado de comentarios del archivo principal.
*   **`$modules_path`**: La ruta física absoluta de la carpeta contenedora de todos los módulos (`wp-content/uploads/hooma/modules/` o similar).
*   **`$modules_url`**: La URL pública de la carpeta contenedora de todos los módulos.

### Buenas Prácticas al Usar Variables de Identidad
Dado que estas variables solo existen en el ámbito local de `index.php`, si registras funciones anónimas o instancias clases desde allí, debes pasarlas explícitamente usando la directiva `use` en los closures:

```php
// En index.php
add_action( 'admin_enqueue_scripts', function() use ( $module_slug, $module_version, $modules_url ) {
    $css_url = $modules_url . $module_slug . '/assets/css/admin.css';
    Hooma::assets()->enqueue_style( 'mi-modulo-admin-styles', $css_url, array(), $module_version );
});
```

---

## 3. Autocarga de Clases (PSR-4 Compliant)

Hooma Core incorpora un cargador automático (`Hooma_Autoloader`) que mapea automáticamente los namespaces PHP con los archivos físicos de la carpeta `includes/`.

### Regla General de Mapeo
Cualquier namespace que empiece con el prefijo raíz HoomaModules o directamente con el namespace del módulo se resolverá de la siguiente forma:

$$\text{Clase: }\mathbf{HoomaModules\setminus\{NombreDelModulo\}\setminus\{Ruta\}\setminus\{NombreClase\}}$$
$$\text{Ruta del Archivo: }\mathbf{hooma-modules/\{slug-del-modulo\}/includes/\{Ruta\}/\{NombreClase\}.php}$$

*   **Nota**: Los nombres en CamelCase se convierten a minúsculas con guiones (kebab-case) para buscar la carpeta física del módulo.

### Ejemplos Prácticos de Mapeo:

| Nombre Completo de la Clase (Namespace + Clase) | Ruta Física del Archivo en el Servidor |
| :--- | :--- |
| `HoomaModules\HoomaEmptyModule\Lifecycle` | `includes/Lifecycle.php` |
| `HoomaModules\HoomaEmptyModule\Controllers\AdminController` | `includes/Controllers/AdminController.php` |
| `HoomaModules\HoomaEmptyModule\Models\DatabaseHelper` | `includes/Models/DatabaseHelper.php` |

---

## 4. Guía para Crear un Nuevo Módulo a partir de esta Plantilla

Si vas a desarrollar un nuevo módulo utilizando esta plantilla, sigue estos sencillos pasos:

1.  **Duplicar la Carpeta**: Copia la carpeta completa de `hooma-empty-module` y renómbrala en minúsculas y con guiones (ej. `mi-nuevo-modulo`).
2.  **Modificar Metadatos**: Abre `index.php` y edita la cabecera de comentarios:
    - Cambia `Module Name` a tu nombre descriptivo (ej: `Gestión de Reservas`).
    - Actualiza el `Text Domain` y otros metadatos informativos.
3.  **Actualizar Namespaces**:
    - Si creas clases en `includes/`, asegúrate de que usen el namespace adecuado. 
    - Por ejemplo, si tu carpeta se llama `mi-nuevo-modulo`, el namespace sugerido por el cargador será:
      `namespace HoomaModules\MiNuevoModulo;`
    - Abre `includes/Lifecycle.php` y actualiza la declaración del `namespace` a `HoomaModules\MiNuevoModulo`.
4.  **Configurar Navegación**: Abre `config/navigation.php` y actualiza el valor de `menu_title` con el título del menú de tu módulo.
