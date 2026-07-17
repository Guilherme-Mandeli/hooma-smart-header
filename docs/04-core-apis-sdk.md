# SDK de Servicios de Hooma Core

Hooma Core provee una fachada estática centralizada (`Hooma`) que expone todos los servicios del sistema de manera limpia, segura y desacoplada de la API global de WordPress.

Para utilizar cualquier servicio en tus clases lógicas, importa la fachada al inicio del archivo PHP:
```php
use Hooma;
```

---

## 1. Referencia Rápida de Servicios

| Fachada / Método | Instancia de Retorno | Descripción |
| :--- | :--- | :--- |
| `Hooma::settings()` | `SettingsService` | Persistencia y recuperación segura de opciones bajo el namespace del módulo. |
| `Hooma::database()` | `DatabaseService` | Consultas seguras sobre la base de datos (con escape automático contra SQLi). |
| `Hooma::http()` | `HttpService` | Cliente HTTP unificado para consumo de APIs con gestión automática de excepciones. |
| `Hooma::logger()` | `LoggerService` | Escritura de logs agrupados por canal e identificador de módulo en archivos diarios. |
| `Hooma::events()` | `EventsService` | Bus de eventos desacoplado para la interacción no rígida entre módulos. |
| `Hooma::auth()` | `AuthService` | Verificación de roles y permisos semánticos personalizados. |
| `Hooma::assets()` | `AssetsService` | Registro seguro de JS/CSS con cache busting automático. |
| `Hooma::notices()` | `NoticesService` | Generación de avisos en el dashboard administrativo (admin notices). |
| `Hooma::cache()` | `CacheService` | Gestión de caché rápida con expiraciones usando Transients protegidos. |

---

## 2. Guía Detallada por Servicio

### Settings (`Hooma::settings()`)
Unifica y encapsula los ajustes en la base de datos.
- **`get( string $key, $default = null )`**: Recupera una opción. El formato es `{modulo}.{opcion}` (ej. `'reservas.api_key'`).
- **`set( string $key, $value )`**: Guarda o actualiza un ajuste de forma persistente.
- **`delete( string $key )`**: Elimina un ajuste específico.
- **`delete_namespace( string $namespace )`**: Elimina todos los ajustes del namespace especificado (ideal para el método `uninstall()` del ciclo de vida).

```php
// Guardar y obtener
Hooma::settings()->set( 'mi_modulo.token', 'xyz123' );
$token = Hooma::settings()->get( 'mi_modulo.token', 'default-value' );
```

---

### Database (`Hooma::database()`)
Realiza consultas a la base de datos de manera segura. Hooma se encarga de escapar y preparar las consultas SQL automáticamente.
- **`get_results( string $query, array $args = array() )`**: Retorna múltiples filas de resultados.
- **`get_row( string $query, array $args = array() )`**: Retorna una única fila de resultados.
- **`get_var( string $query, array $args = array() )`**: Retorna un valor único.
- **`query( string $query, array $args = array() )`**: Ejecuta una consulta genérica (como `INSERT`, `UPDATE` o `DELETE`).

```php
// Consulta segura con placeholders
$results = Hooma::database()->get_results(
    "SELECT * FROM %swp_users WHERE user_email = %s AND ID = %d",
    array( 'cliente@correo.com', 5 )
);
```

---

### HTTP Client (`Hooma::http()`)
Cliente unificado que evita el uso manual de `wp_remote_get` y la validación manual de `WP_Error`. Lanza excepciones en caso de error HTTP o de red.
- **`get( string $url, array $args = array() )`**: Envía una petición GET. Retorna un objeto `Response`.
- **`post( string $url, array $data = array(), array $args = array() )`**: Envía una petición POST.
- **Métodos del objeto `Response`**:
  - `body()`: El contenido crudo en formato string.
  - `json()`: Decodifica automáticamente el contenido JSON a un array de PHP.
  - `status()`: El código de respuesta HTTP (ej: 200, 404).

```php
try {
    $response = Hooma::http()->get( 'https://api.servidor.com/v1/datos' );
    $datos = $response->json();
} catch ( \RuntimeException $e ) {
    Hooma::logger()->error( 'Error consultando API externa: ' . $e->getMessage() );
}
```

---

### Logger (`Hooma::logger()`)
Escribe logs limpios agrupados y controlados. Los logs se almacenan en carpetas protegidas en `uploads/hooma/logs/`.
- **`debug / info / warning / error / critical( string $message, array $context = array() )`**: Métodos para los distintos niveles de severidad de logs.

```php
Hooma::logger()->error( 'Fallo crítico al conectar con la pasarela de pagos', array(
    'pedido_id' => 241,
    'usuario'   => 'Juan Pérez'
));
```

---

### Events (`Hooma::events()`)
Permite la comunicación totalmente desacoplada entre distintos módulos.
- **`dispatch( string $event_name, $payload = null )`**: Dispara un evento del sistema.
- **`listen( string $event_name, callable $callback )`**: Escucha un evento de forma activa para ejecutar una lógica.

```php
// Disparar evento
Hooma::events()->dispatch( 'reserva.creada', array( 'reserva_id' => 12 ) );

// Escuchar evento (por ejemplo en el index.php de otro módulo)
Hooma::events()->listen( 'reserva.creada', function( $payload ) {
    // Código que reacciona a la creación de la reserva
});
```

---

### Auth/Permissions (`Hooma::auth()`)
Gestiona capacidades semánticas específicas del negocio en lugar de usar ganchos planos de WordPress.
- **`can( string $capability ): bool`**: Comprueba si el usuario actual posee los permisos para realizar una acción de negocio.

```php
if ( ! Hooma::auth()->can( 'booking.edit' ) ) {
    wp_die( 'Acceso denegado' );
}
```

---

### Assets (`Hooma::assets()`)
Encola scripts y estilos de forma optimizada. El Core analiza la fecha de modificación física de los archivos locales en el servidor (`filemtime`) e inyecta la versión de forma automática en la URL (`?ver=170000000`), evitando problemas de caché en el navegador del cliente.

- **`enqueue_style( string $handle, string $src, array $deps = array(), $ver = false, string $media = 'all' )`**
- **`enqueue_script( string $handle, string $src, array $deps = array(), $ver = false, bool $in_footer = true )`**

> [!IMPORTANT]
> El argumento `$src` debe ser una **URL completa**.
> Utiliza las variables inyectadas del Core (`$modules_url` y `$module_slug`) para construir la dirección del archivo:

```php
$css_url = $modules_url . $module_slug . '/assets/css/admin.css';
Hooma::assets()->enqueue_style( 'mi-modulo-admin', $css_url );
```

---

### Notices (`Hooma::notices()`)
Permite mostrar avisos emergentes (admin notices) en el panel administrativo de WordPress tras redirecciones o acciones.
- **`success / error / warning / info( string $message, bool $dismissible = true )`**

```php
Hooma::notices()->success( 'La configuración del módulo se ha guardado exitosamente.' );
```

---

### Cache (`Hooma::cache()`)
Acceso rápido al almacenamiento en caché temporal (Transient API) con empaquetado para evitar la expiración prematura y el problema del falso booleano.
- **`get( string $key, $default = null )`**: Recupera un valor de la caché.
- **`set( string $key, $value, ?int $ttl = null )`**: Almacena un valor. El TTL se define en segundos.
- **`delete( string $key )`**: Elimina una clave de caché.
- **`remember( string $key, ?int $ttl, callable $callback )`**: Recupera el valor si existe. Si no, ejecuta el callback, lo almacena en caché y lo retorna.

```php
$datos_clima = Hooma::cache()->remember( 'clima_localidad', 3600, function() {
    $response = Hooma::http()->get( 'https://api.clima.com/datos' );
    return $response->json();
});
```
