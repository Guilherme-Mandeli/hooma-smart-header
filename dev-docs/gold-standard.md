# Plantilla de Módulo de Hooma ("Gold Standard")

Este repositorio es el punto de partida oficial para la creación de nuevos módulos en el ecosistema **Hooma**. Contiene la estructura de archivos mínima obligatoria y la documentación de referencia técnica oficial.

---

## Directorios de la Plantilla

*   **`index.php`**: El bootstrap y punto de inicio del módulo (Identity Injection).
*   **`config/navigation.php`**: Configuración de menús y pestañas del administrador.
*   **`includes/`**: Carpeta para tus clases PHP lógicas. El cargador automático de Hooma buscará aquí basándose en la especificación PSR-4.
    -   `Lifecycle.php`: Manejo del ciclo de vida del módulo (Install, Update, Uninstall).
*   **`admin/views/`**: Plantillas HTML de administración (Views).
    -   `index.php`: Vista por defecto cargada para la pestaña principal del módulo.
*   **`assets/`**: Hojas de estilo y scripts de frontend públicos del módulo (`css/`, `js/`).
*   **`docs/`**: Reservada exclusivamente para los documentos propios del módulo (visibles en `.hooma-detail-main`).
*   **`dev-docs/`**: Guías técnicas detalladas de desarrollo de Hooma Core (excluidas de `.hooma-detail-main`).

---

## Índice de Documentación Técnica (`dev-docs/`)

Para comprender el funcionamiento interno del framework y cómo desarrollar tu lógica, consulta las guías de la carpeta `dev-docs/`:

### Guías de Inicio Rápido (Locales)
1.  **[Estructura e Identidad](dev-docs/01-structure-and-identity.md)**
    - Convención de nombres de carpetas y archivos.
    - Cómo funciona la inyección de variables de identidad (`$module_slug`, `$module_namespace`, etc.).
    - Reglas de autocarga PSR-4 (namespaces mapped to `includes/`).
    - Paso a paso para renombrar esta plantilla y crear tu nuevo módulo.
2.  **[Ciclo de Vida del Módulo](dev-docs/02-lifecycle.md)**
    - Implementación de la clase `Lifecycle`.
    - Cómo gestionar la activación del módulo (`install()`) para bases de datos u opciones iniciales.
    - Controlar las migraciones de versiones (`update()`).
    - Limpieza estricta de base de datos en desinstalación (`uninstall()`).
3.  **[Navegación y Vistas](dev-docs/03-navigation-and-views.md)**
    - Configuración del enrutamiento automático mediante `navigation.php`.
    - Comportamiento del envoltorio visual automático del Core.
    - Uso y métodos de la clase constructora visual `Hooma_UI` (headers, tabs, footers).
4.  **[SDK de Servicios del Core](dev-docs/04-core-apis-sdk.md)**
    - Referencia rápida del SDK integrado a través del objeto estático `Hooma::`.
    - Uso de persistencia con `Settings`, consultas seguras con `Database`, cliente `HTTP` con excepciones, bus de `Events` desacoplado, control de permisos con `Auth`, y encolado de `Assets` con cache-busting automático.
5.  **[Buenas Prácticas y Arquitectura](dev-docs/05-best-practices.md)**
    - Reglas de aislamiento entre módulos para evitar fallos cruzados.
    - Separación de responsabilidades y modularidad limpia.
    - Medidas de seguridad fundamentales (Sanitización, Escapado y Nonces contra CSRF).
    - Encolamiento de assets condicional para optimizar el rendimiento de WordPress.

---

### Manual Oficial de Hooma Core (Copiado del Core)

*   **[Serie de Tutoriales Progresivos](dev-docs/tutorials/02-first-module.md)**: Una serie paso a paso para dominar Hooma Core.
*   **[Cookbook (Recetario)](dev-docs/cookbook/custom-database-table.md)**: Soluciones prácticas y directas a problemas habituales del día a día (Base de datos, HTTP, tareas programadas, shortcodes, etc.).
*   **[Referencia de API](dev-docs/api/settings.md)**: Consulta rápida de las firmas y parámetros de todos los servicios disponibles en el framework.
*   **[Conceptos Fundamentales](dev-docs/concepts/framework.md)**: Entiende la filosofía del Thin Core y las responsabilidades del sistema.
*   **[Guía del Desarrollador del Core](dev-docs/modules/development-guide.md)**: Guía física de cómo estructurar módulos.
*   **[Sistema de Packages](dev-docs/packages/packages-system.md)**: Cómo consumir dependencias y recursos de infraestructura (como Vue, Alpine o Stripe SDK) desde tus módulos.
*   **[Arquitectura del Core](dev-docs/architecture/bootstrap-lifecycle.md)**: Bootstrap, ciclo de vida global y funcionamiento de Fachadas/Service Container.
*   **[Architecture Decision Records (ADR)](dev-docs/adr/0001-modules-outside-wp-plugins.md)**: Historial de por qué se tomaron las decisiones de diseño clave.
*   **[Preguntas Frecuentes (FAQ)](dev-docs/faq.md)**: Resolución de dudas comunes y compatibilidad con WordPress.
*   **[Changelog](dev-docs/changelog/changelog.md)**: Historial de versiones y cambios del Core.

---

## Inicio Rápido en 3 Pasos para Desarrollar

1.  Duplica esta carpeta bajo el directorio de módulos de Hooma y renómbrala a tu slug en minúsculas (ej. `hooma-mi-modulo`).
2.  Edita los comentarios de cabecera de `index.php` con el nombre y descripción del módulo.
3.  **Cambia obligatoriamente** la declaración del `namespace` en [includes/Lifecycle.php](../includes/Lifecycle.php) (ej. cambiar `namespace HoomaModules\HoomaEmptyModule;` a `namespace HoomaModules\HoomaMiModulo;` donde `HoomaMiModulo` es el nombre de la carpeta de tu módulo en StudlyCase).

> [!WARNING]
> Si no actualizas este namespace en `Lifecycle.php`, el cargador automático PSR-4 no podrá cargar la clase `Lifecycle` y las tareas de instalación, desinstalación y actualización fallarán.

¡Eso es todo! Ya puedes activar tu módulo en el gestor de Hooma y empezar a programar.
