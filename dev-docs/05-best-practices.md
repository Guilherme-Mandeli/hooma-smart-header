# Buenas Prácticas y Arquitectura de Módulos

Para asegurar la escalabilidad, estabilidad y coherencia visual del ecosistema de Hooma, todos los desarrolladores de módulos deben seguir estas directrices de diseño y desarrollo de software.

---

## 1. Reglas de Aislamiento y Desacoplamiento (Límites de Negocio)

### NO Cruces Namespaces entre Módulos
Nunca instancies clases pertenecientes a otros módulos (ej. instanciar una clase del módulo de facturación desde el módulo de reservas). Esto genera acoplamiento rígido; si el usuario desactiva un módulo, el otro se romperá.

### Usa el Bus de Eventos para Comunicarte
Si el módulo de reservas necesita notificar al de facturación que se ha completado una cita, dispara un evento:
```php
Hooma::events()->dispatch( 'reserva.completada', array( 'reserva_id' => $id ) );
```
De esta forma, cualquier otro módulo interesado puede escuchar y reaccionar sin acoplamiento duro.

### NO Escribas en Base de Datos de forma Dispersa
Evita usar las funciones crudas de WordPress como `update_option()` o llamar a `$wpdb` para tareas que el Core puede optimizar.
- Utiliza **`Hooma::settings()`** para configuraciones (se encargará de gestionar el caché e indexar correctamente bajo el namespace).
- Utiliza **`Hooma::database()`** para consultas a tablas.

---

## 2. Organización Limpia del Código (Clean Architecture)

### Principio de Responsabilidad Única (SRP)
- **`index.php` (Bootstrap)**: Mantén este archivo lo más limpio posible. Debe limitarse exclusivamente a leer los metadatos y a enganchar las clases controladoras a los hooks de WordPress. No escribas funciones procedimentales largas aquí.
- **Controladores (`includes/Controllers/`)**: Contienen la lógica para capturar peticiones, verificar permisos y mediar entre los modelos y las vistas.
- **Vistas (`admin/views/` o carpetas de frontend)**: Contienen única y exclusivamente marcado HTML/PHP de presentación. No calcules lógica de negocio ni realices queries de base de datos dentro de las vistas.

### NO Utilices `require_once` de forma Innecesaria
Dado que Hooma Core incorpora un cargador automático PSR-4, la instanciación de cualquier clase en tu namespace cargará de forma automática su archivo PHP correspondiente. Usar `require` o `include` manual rompe esta funcionalidad, ralentiza PHP e incrementa el consumo innecesario de memoria RAM en el servidor.

---

## 3. Seguridad y Robustez en el Servidor

### Sanitiza y Escapa Siempre
Asegúrate de proteger tu código contra vulnerabilidades comunes:
- **Sanitización al recibir**: Usa funciones nativas de WordPress como `sanitize_text_field()`, `absint()`, `sanitize_email()` o `sanitize_textarea_field()` antes de almacenar cualquier valor del POST en base de datos.
- **Escapado al renderizar**: Usa `esc_html()`, `esc_attr()`, `esc_url()` o `wp_kses_post()` para imprimir variables en las vistas y prevenir Cross-Site Scripting (XSS).
- **Protección CSRF**: Agrega siempre campos Nonce en tus formularios administrativos y valídalos antes de procesar cualquier POST:
  ```php
  // En la vista:
  wp_nonce_field( 'mi_accion_modulo', 'mi_nonce_field' );
  
  // En el controlador que procesa el POST:
  if ( ! isset( $_POST['mi_nonce_field'] ) || ! wp_verify_nonce( $_POST['mi_nonce_field'], 'mi_accion_modulo' ) ) {
      wp_die( 'Petición no autorizada.' );
  }
  ```

---

## 4. Optimización de Rendimiento

### Encolamiento Condicional de Assets
No encoles tus hojas de estilos ni tus scripts JS en todas las páginas de la administración de WordPress. Ello incrementa el tiempo de carga y puede causar conflictos con otros plugins.
- Comprueba siempre que el usuario se encuentra dentro de la pantalla de tu módulo antes de llamar a `Hooma::assets()`:

```php
add_action( 'admin_enqueue_scripts', function( $hook ) use ( $module_slug, $modules_url ) {
    // Comprobar que el hook corresponde a la pantalla del módulo
    if ( $hook !== 'hooma_page_' . $module_slug ) {
        return;
    }
    
    // Encolar assets específicos
    $js_url = $modules_url . $module_slug . '/assets/js/admin.js';
    Hooma::assets()->enqueue_script( 'mi-modulo-admin-js', $js_url );
});
```
