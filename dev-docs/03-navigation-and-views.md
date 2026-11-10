# Navegación y Vistas de Administración

Hooma Core simplifica la creación de páginas de administración y el diseño de la interfaz de usuario mediante una estructura de menús automatizada y el uso de la clase constructora visual `Hooma_UI`.

---

## 1. Menús y Pestañas Automáticas (`config/navigation.php`)

Si deseas que tu módulo tenga una pantalla de administración integrada, no es necesario registrar páginas de menú a mano. Solo debes crear el archivo de configuración `config/navigation.php`.

Al activarse el módulo, el Core leerá este archivo y creará automáticamente un submenú para el módulo dentro del menú principal **"Hooma"** del WordPress admin.

### Esquema del Archivo de Navegación:
```php
<?php
defined( 'HOOMA_PATH' ) || exit;

return array(
    'menu_title' => 'Gestión de Reservas', // Título visible del menú lateral
    
    // Enlaces de "Acceso rápido" en la vista de detalle del módulo en el Core Dashboard (Opcional)
    'quick_links' => array(
        array(
            'label'  => 'Documentación Externa',
            'url'    => 'https://ejemplo.com/docs',
            'target' => '_blank', // Opcional (_blank, _self)
            'class'  => 'button button-secondary', // Opcional
        ),
    ),

    'tabs'       => array(
        'index'    => array(
            'label' => 'Inicio',           // Carga la vista admin/views/index.php
        ),
        'settings' => array(
            'label' => 'Configuración',    // Carga la vista admin/views/settings.php
        ),
        'reports'  => array(
            'label' => 'Reportes',
            'view'  => 'mi-vista-custom', // Opcional: Carga admin/views/mi-vista-custom.php
        )
    ),
);
```

### Enlaces de Acceso Rápido (Quick Access Card)
Si defines la clave `'quick_links'`, Hooma Core mostrará una tarjeta lateral especial llamada **"Acceso rápido"** cuando los administradores hagan clic en la vista detallada de tu módulo en el Dashboard.

También puedes inyectar o modificar dinámicamente estos enlaces mediante el filtro PHP de WordPress `hooma_module_quick_links`.

---

## 2. Enrutamiento y Envoltorio Automático (Wrapper)

Cuando el Core carga el panel administrativo de tu módulo basándose en la configuración de `config/navigation.php`:

1.  Determina qué pestaña está activa analizando el parámetro `$_GET['tab']`.
2.  Busca el archivo correspondiente en `admin/views/{tab_slug}.php`.
3.  **Encapsula y renderiza de forma transparente la interfaz general:**

```text
[Hooma_UI::container_start]
     ↓
[Hooma_UI::header( 'menu_title', 'version' )]
     ↓
[Hooma_UI::tabs( tabs_config, current_tab )]
     ↓
[Contenedor HTML con fondo blanco y bordes de pestaña]
     ↓
¡AQUÍ SE INCLUYE TU ARCHIVO DE VISTA DE LA PESTAÑA!
     ↓
[Hooma_UI::footer]
     ↓
[Hooma_UI::container_end]
```

> [!IMPORTANT]
> **No dupliques el envoltorio visual en tus archivos de vistas.**
> Dado que el Core se encarga del header, las pestañas de navegación y el pie de página, tus archivos en `admin/views/` (ej: `index.php` o `settings.php`) **solo deben contener el código HTML/PHP del formulario o contenido propio** que va dentro de la pestaña.

---

## 3. Uso Manual de `Hooma_UI`

Si decides omitir `config/navigation.php` para registrar tu propia página de administración manual mediante código personalizado de WordPress (ej: `add_menu_page`), puedes utilizar la clase auxiliar `Hooma_UI` para estructurar la maquetación y mantener la misma línea visual premium de la plataforma.

### Métodos Disponibles en `Hooma_UI`:

*   `Hooma_UI::container_start()`: Imprime el contenedor envolvente de WordPress y Hooma (`<div class="wrap hooma-container">`).
*   `Hooma_UI::container_end()`: Cierra el contenedor (`</div>`).
*   `Hooma_UI::header( $title, $subtitle )`: Renderiza un unificado encabezado con estilo Hooma.
*   `Hooma_UI::tabs( $tabs, $current )`: Genera la barra superior de pestañas.
*   `Hooma_UI::footer()`: Renderiza el pie de página indicando la versión actual del plugin.

### Ejemplo de maquetación en una Vista Manual:
```php
<?php
// Vista manual (fuera del sistema de enrutamiento del Core)
defined( 'HOOMA_PATH' ) || exit;
?>
<div class="hooma-ui-wrapper">
    <?php Hooma_UI::header( 'Reservas Especiales', 'v1.0.0' ); ?>

    <div class="hooma-ui-container">
        <h2>Panel de Configuración</h2>
        <p>Introduce las llaves del servicio:</p>
        
        <form method="POST" action="">
            <!-- Campos del formulario... -->
        </form>
    </div>

    <?php Hooma_UI::footer(); ?>
</div>
```
