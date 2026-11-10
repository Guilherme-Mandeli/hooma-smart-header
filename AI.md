# Guía de Contexto e Índice de Documentación para Inteligencias Artificiales (AI.md)

Este documento sirve como entrypoint de contexto y mapa de navegación para cualquier Inteligencia Artificial (IA) o agente de programación que trabaje en un módulo de **Hooma Core**.

---

## 1. Contexto del Repositorio

Este repositorio corresponde a un **módulo de Hooma Core**, un framework de infraestructura para WordPress basado en una arquitectura modular.

Hooma Core proporciona una capa común de servicios, herramientas y convenciones para el desarrollo de módulos independientes. La lógica de negocio **no pertenece al Core**; cada funcionalidad debe implementarse dentro de su propio módulo.

### Principios de Arquitectura

- Hooma Core sigue la filosofía **Thin Core**. El Core únicamente proporciona infraestructura reutilizable; nunca implementa reglas de negocio.
- Los módulos son completamente independientes entre sí. No deben instanciar clases ni acceder directamente a los archivos de otros módulos.
- La comunicación entre módulos debe realizarse mediante el **Events Service** (`Hooma::events()`), manteniendo un bajo acoplamiento.
- Todas las funcionalidades comunes deben consumirse a través de la API pública de Hooma (`Hooma::...()`), evitando el uso directo de APIs nativas de WordPress cuando exista un servicio equivalente.
- Las dependencias compartidas (Vue, esbuild, Stripe SDK, etc.) se distribuyen mediante el **Packages System** y deben obtenerse exclusivamente mediante `Hooma::packages()`.

### Objetivo de este repositorio

Cuando trabajes sobre este proyecto debes asumir que estás desarrollando un **módulo de negocio**, no el framework.

Por tanto:

- Implementa únicamente la funcionalidad propia del módulo.
- Reutiliza los servicios existentes antes de crear nuevas abstracciones.
- Sigue las convenciones, estructura de directorios y estándares definidos por Hooma Core.
- Si detectas que una funcionalidad es reutilizable por múltiples módulos, propón moverla al Core como un nuevo Service o Package en lugar de implementarla localmente.

Antes de escribir código, consulta el índice de documentación incluido en este archivo para localizar la guía correspondiente.
---

## 2. Acceso a Servicios del Core

Todos los servicios provistos por Hooma Core se acceden a través de la fachada estática central `Hooma` ([includes/Hooma.php](includes/Hooma.php)).

| Servicio | Acceso en Fachada | Propósito / Responsabilidad |
| :--- | :--- | :--- |
| **Database** | `Hooma::db()` | Consultas seguras, migraciones y queries cacheadas en la DB. |
| **HTTP Client** | `Hooma::http()` | Consumir APIs REST externas y registrar endpoints de forma segura. |
| **Cache** | `Hooma::cache()` | Almacenamiento rápido en memoria (transients / object cache). |
| **Assets** | `Hooma::assets()` | Encolado y registro de scripts (JS) y estilos (CSS) en admin/front. |
| **Scheduler** | `Hooma::scheduler()` | Registro y ejecución de tareas programadas (Cron jobs). |
| **Events** | `Hooma::events()` | Sistema interno de eventos de tipo Pub/Sub (Publish/Subscribe). |
| **Settings** | `Hooma::settings()` | Gestión centralizada de ajustes y opciones en base de datos. |
| **Logger** | `Hooma::logger()` | Registro estructurado de trazas y depuración en archivos locales. |
| **Filesystem** | `Hooma::fs()` | Manipulación segura de archivos y subidas de ficheros al servidor. |
| **Modules** | `Hooma::modules()` | Servicio interno para descubrir y administrar módulos activos. |
| **Packages** | `Hooma::packages()` | Servicio interno para consultar librerías externas o assets compilados. |
| **Auth** | `Hooma::auth()` | Validación de permisos, roles y tokens de seguridad. |
| **Notices** | `Hooma::notices()` | Mostrar banners y avisos administrativos flotantes. |

---

## 3. Índice de Documentación por Casos de Uso (AI Directory Map)

> **Separación de Documentación (`/dev-docs` vs `/docs`)**:
> - **`/dev-docs`**: Contiene la documentación técnica del framework Hooma Core y guías de desarrollo (no se muestra en `.hooma-detail-main`).
> - **`/docs`**: Queda reservada exclusivamente para la documentación propia y funcional del módulo (visible en `.hooma-detail-main`).

Si tienes que programar o modificar una funcionalidad, utiliza esta tabla para encontrar rápidamente la documentación técnica dentro de la carpeta [dev-docs/](dev-docs):

### 🚀 Primeros Pasos y Conceptos
- **¿Cómo se inicializa el plugin y cuál es el ciclo de vida?** &rarr; [dev-docs/architecture/bootstrap-lifecycle.md](dev-docs/architecture/bootstrap-lifecycle.md) y [dev-docs/concepts/lifecycle.md](dev-docs/concepts/lifecycle.md)
- **¿Cómo funciona el Contenedor de Servicios?** &rarr; [dev-docs/architecture/service-container.md](dev-docs/architecture/service-container.md)
- **Guía de instalación inicial** &rarr; [dev-docs/tutorials/01-installation.md](dev-docs/tutorials/01-installation.md)
- **Preguntas frecuentes generales (FAQ)** &rarr; [dev-docs/faq.md](dev-docs/faq.md)

### 📦 Desarrollo de Módulos
- **Estructura y reglas para crear tu primer módulo** &rarr; [dev-docs/tutorials/02-first-module.md](dev-docs/tutorials/02-first-module.md) y [dev-docs/modules/development-guide.md](dev-docs/modules/development-guide.md)
- **Buenas prácticas de diseño en módulos** &rarr; [dev-docs/modules/best-practices.md](dev-docs/modules/best-practices.md)
- **Antipatrones críticos a evitar** &rarr; [dev-docs/modules/anti-patterns.md](dev-docs/modules/anti-patterns.md)

### 🖥️ Interfaz de Usuario y WordPress Admin
- **Crear una página o menú en el WP Admin** &rarr; [dev-docs/cookbook/admin-page.md](dev-docs/cookbook/admin-page.md) y [dev-docs/tutorials/03-admin-page.md](dev-docs/tutorials/03-admin-page.md)
- **Navegación y Enlaces de Acceso Rápido (`quick_links` en `config/navigation.php`)** &rarr; [dev-docs/03-navigation-and-views.md](dev-docs/03-navigation-and-views.md)
- **Uso de la UI Kit unificada (`Hooma_UI`)** &rarr; [dev-docs/concepts/ui-kit.md](dev-docs/concepts/ui-kit.md)
- **Gestión de Assets (CSS/JS)** &rarr; [dev-docs/tutorials/05-assets.md](dev-docs/tutorials/05-assets.md) y [dev-docs/api/assets.md](dev-docs/api/assets.md)
- **Crear Shortcodes en WordPress** &rarr; [dev-docs/cookbook/shortcodes.md](dev-docs/cookbook/shortcodes.md)

### 💾 Base de Datos y Caché
- **Uso del servicio Database** &rarr; [dev-docs/tutorials/08-database.md](dev-docs/tutorials/08-database.md) y [dev-docs/api/database.md](dev-docs/api/database.md)
- **Crear tablas personalizadas (custom tables)** &rarr; [dev-docs/cookbook/custom-database-table.md](dev-docs/cookbook/custom-database-table.md)
- **Cómo cachear queries de forma óptima** &rarr; [dev-docs/cookbook/cached-queries.md](dev-docs/cookbook/cached-queries.md) y [dev-docs/tutorials/07-cache.md](dev-docs/tutorials/07-cache.md)

### 📡 Integración y Red
- **Registrar y consumir endpoints HTTP** &rarr; [dev-docs/tutorials/06-http.md](dev-docs/tutorials/06-http.md) y [dev-docs/api/http.md](dev-docs/api/http.md)
- **Consumo de APIs REST paso a paso** &rarr; [dev-docs/cookbook/consume-rest-api.md](dev-docs/cookbook/consume-rest-api.md)

### ⏰ Eventos y Tareas Programadas
- **Sistema de Eventos (Pub/Sub)** &rarr; [dev-docs/tutorials/09-events.md](dev-docs/tutorials/09-events.md) y [dev-docs/cookbook/events.md](dev-docs/cookbook/events.md)
- **Scheduler (Tareas programadas - Cron)** &rarr; [dev-docs/tutorials/10-scheduler.md](dev-docs/tutorials/10-scheduler.md) y [dev-docs/cookbook/scheduled-tasks.md](dev-docs/cookbook/scheduled-tasks.md)

### 📦 Integración de Paquetes Externos (esbuild, Vue, Stripe)
- **Sistema de Packages de Hooma** &rarr; [dev-docs/packages/packages-system.md](dev-docs/packages/packages-system.md)
- **Compilar assets con esbuild sin dependencias Node** &rarr; [dev-docs/packages/esbuild.md](dev-docs/packages/esbuild.md)
- **Uso de Vue.js en WordPress a través de Hooma** &rarr; [dev-docs/packages/vue.md](dev-docs/packages/vue.md)

---

## 4. Requisitos Mínimos para que un Módulo Funcione (Quickstart)

Para que Hooma Core reconozca, cargue y ejecute un nuevo módulo, deben cumplirse estrictamente los siguientes requisitos:

1. **Ubicación del Directorio**:
   - El módulo debe ubicarse en la ruta física: `wp-content/uploads/hooma/modules/{slug-del-modulo}/` (no en la carpeta de plugins habitual de WordPress).

2. **Archivo Principal (`index.php`)**:
   - Debe existir un archivo `index.php` en la raíz de la carpeta del módulo con la cabecera de metadatos mínimos:
     ```php
     <?php
     /**
      * Module Name: Nombre descriptivo de tu módulo
      * Description: Breve descripción de la funcionalidad.
      * Version: 1.0.0
      */
     defined( 'HOOMA_PATH' ) || exit;
     ```

3. **Clase y Namespace de Ciclo de Vida (`includes/Lifecycle.php`)**:
   - Debe existir un archivo [includes/Lifecycle.php](includes/Lifecycle.php) con una clase llamada `Lifecycle` que implemente la interfaz `Hooma_Module_Lifecycle_Interface`.
   - **CRÍTICO**: El namespace debe corresponder exactamente al prefijo de autocarga de Hooma Modules (`HoomaModules`) seguido del nombre de la carpeta del módulo en StudlyCase.
     - *Ejemplo*: Si la carpeta del módulo es `mi-primer-modulo`, el namespace en `Lifecycle.php` debe ser obligatoriamente:
       ```php
       namespace HoomaModules\MiPrimerModulo;
       ```
     - Si el namespace no coincide o se deja por defecto (`HoomaModules\HoomaEmptyModule`), Hooma Core no podrá cargar la clase y el módulo fallará silenciosamente al activarse o desactivarse.

4. **Activación desde la Administración**:
   - Accede al panel administrativo de WordPress, navega a **Hooma Core** &rarr; **Módulos**, localiza tu módulo y haz clic en **Activar**.

---

## 5. Instrucciones para la IA (System Rules)

Al proponer cambios o redactar código en este repositorio:
1. **No agregues lógica de negocio al Core**: Si el usuario te pide implementar una regla de facturación, pasarela de pago o lógica específica, guíalo para crear o modificar un archivo bajo un módulo independiente en `wp-content/hooma/modules/{nombre-modulo}/`.
2. **Sigue las convenciones de namespaces**: Declara siempre namespaces que coincidan con la estructura física y no requieras archivos manualmente si la clase puede ser cargada por el autoloader inteligente.
3. **Utiliza la API unificada**: Prefiere usar las llamadas estáticas de `Hooma` (ej: `Hooma::db()`) en lugar de interactuar con la variable global `$wpdb` de WordPress directamente o realizar peticiones con `curl`/`file_get_contents` nativos (usa `Hooma::http()`).
4. **Respeta los idiomas de codificación**: Escribe el código en **Inglés** y añade comentarios de funcionamiento detallado en **Español**.
