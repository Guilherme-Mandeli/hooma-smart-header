# Hooma Smart Header

**Hooma Smart Header** es un módulo de referencia premium y de alto rendimiento para el ecosistema Hooma. Está diseñado para proporcionar un control avanzado, flexible e instantáneo sobre la cabecera (Header) de cualquier sitio web en WordPress, optimizando al máximo las métricas de rendimiento de velocidad y de experiencia de usuario (Core Web Vitals).

El plugin ofrece funcionalidades completas de **Scroll Reveal**, cabecera flotante (**Sticky Header**), comportamiento inicial retrasado, compensaciones de altura responsivas mediante ResizeObservers, intercambio fluido de logotipos (Cross-fade) y optimización agresiva para evitar el cambio de diseño acumulativo (**Zero CLS**) y mejorar la métrica **LCP (Largest Contentful Paint)**.

---

## 🛠️ Funcionalidades del Sistema (Para el Usuario)

- **Logotipos Inteligentes por Estado (Logo State Switcher):** Supervisa cualquier elemento de la página y cambia automáticamente el logotipo cuando detecta una clase CSS definida por ti. Perfecto para actualizar el logo de una cabecera transparente al hacer scroll, adaptarlo a cambios visuales del menú o reaccionar a cualquier estado dinámico de tu sitio de forma automática y fluida.
- **Logotipos Optimizados por Dispositivo (Logo Switcher):** Muestra el logotipo ideal e independiente para Computadora, Tablet y Celular. Compatible con la característica anterior.
- **Foco Máximo en el Contenido (Comportamiento Inicial):** Mantén la cabecera oculta durante el primer tramo de navegación y muéstrala únicamente al realizar scroll o al alcanzar un selector específico de la página. Ideal para quienes desean una primera pantalla limpia o aplicar una cabecera única en ese primer trecho del sitio.
- **Navegación Dinámica e Inteligente (Comportamiento en Scroll):** Oculta la cabecera al hacer scroll hacia abajo y la vuelve a mostrar al desplazarse hacia arriba. El comportamiento se activa de forma inmediata para mantener la navegación fluida sin interferir con la lectura del contenido.
- **Diseño Responsivo Impecable (Responsive & Layout):** Corrige automáticamente la altura del sitio según el dispositivo para evitar espacios en blanco indeseados o saltos en el diseño, ofreciendo una experiencia profesional en cualquier pantalla de forma transparente.
- **Carga en la Velocidad de la Luz (Optimización):** Minimiza al extremo el código final descartando los módulos inactivos del plugin, asegurando que tu sitio sea ultra veloz y alcance las mejores notas en Google PageSpeed.
- **Control por Página y Contenido:** Todas las funcionalidades del plugin pueden activarse o desactivarse de forma independiente en páginas, entradas o tipos de contenido específicos. Esto permite aplicar el comportamiento del header de manera global o limitarlo únicamente a ciertos posts o secciones del sitio según sea necesario.

---

## ⚡ Tecnologías y Métodos Utilizados

- **Sistema de Compilación Híbrido con esbuild:**
  Compila en caliente el bundle de JavaScript aplicando tree-shaking físico según los módulos que el usuario mantenga activos en la administración.
  Ofrece un cargador ES Module nativo de contingencia (fallback) si el servidor no dispone de Node.js, garantizando cero errores 404 en el frontend.
- **Sistema de Prevención de CLS (Cumulative Layout Shift) en Tiempo Real:**
  Inyecta estilos críticos incrustados directamente desde el servidor y ejecuta micro-scripts síncronos inline nada más abrirse el cuerpo de la página.
  Esto reserva el espacio físico exacto de la cabecera antes de que se descarguen los assets estáticos, garantizando un desplazamiento de diseño igual a cero.
  Además, evalúa en servidor (PHP) las reglas de visualización de páginas excluidas antes de inyectar estilos de opacidad crítica, previniendo parpadeos iniciales y asegurando compatibilidad total si falla JS.
- **Puente Responsivo de Altura mediante Cookies de Usuario (Zero AJAX Overhead):**
  Emplea un `ResizeObserver` en el cliente que monitoriza las dimensiones reales del header y las almacena localmente en la cookie `hsh_cached_height` únicamente si estas varían más de 2px.
  El servidor lee esta cookie directamente al generar el CSS crítico en visitas subsiguientes, logrando una sincronización visual perfecta para el usuario sin necesidad de realizar peticiones AJAX al servidor ni escrituras a la base de datos durante visitas públicas.
- **Sistema de Versionado Universal e Invalidación de Caché del Navegador:**
  Genera URLs de assets con parámetros de consulta de versión dinámicos basados en la clave de guardado administrativo `last_saved`.
  Esto fuerza a los navegadores y CDN a renovar su caché instantáneamente tras cambiar un ajuste, manteniendo el archivo físico en disco siempre estático.
- **Precargador de Logotipos Optimizado (Logo Preloader):**
  Inyecta etiquetas de precarga `<link rel="preload">` en la cabecera HTML. Tras guardarse los cambios en el panel de control, el escaneo y parsing del DOM se ejecuta en segundo plano de forma 100% asíncrona mediante un semáforo de bloqueo de transients. Esto elimina bloqueos y hace que guardar la configuración en administración sea instantáneo y fluido.
- **Sincronización Avanzada de Cabecera Secundaria (#top-header):**
  La lógica de reposicionamiento de la barra superior secundaria se integra directamente dentro del ciclo de renderizado de scroll asíncrono principal (`requestAnimationFrame`) y al finalizar el desplazamiento (`scrollend`). Esto previene retrasos de animación, saltos visuales o bloqueos de estado al detenerse.
- **Detección Automática de Tema y Adaptador Divi Seguro:**
  Detecta autónomamente el tema activo para aplicar filtros nativos de logotipos o adaptar el búfer de salida PHP en constructores complejos.
  En Divi, intercepta de forma segura el buffer HTML del servidor con validación de existencia para la extensión `mbstring`, garantizando la correcta codificación de caracteres especiales en cualquier servidor PHP.

---

## 📂 Estructura de Archivos del Módulo

El plugin implementa una arquitectura limpia siguiendo los principios de modularidad, PSR-4 de autoloading, MVC y encapsulamiento de servicios del ecosistema Hooma:

```text
hooma-smart-header/
├── index.php                   # Manifesto y bootstrap del módulo
├── package.json                # Dependencias de desarrollo de JS (esbuild)
├── build.js                    # Script de control del compilador esbuild y feature flags
├── config/
│   └── navigation.php          # Configuración de pestañas del panel administrativo
├── admin/
│   └── views/                  # Vistas PHP del panel de configuración
├── includes/                   # Código de backend (PHP Autoload PSR-4)
│   ├── Lifecycle.php           # Hooks de ciclo de vida (Instalación/Limpieza)
│   ├── Controllers/            # Controladores MVC de Backend (Admin y Frontend)
│   └── Services/               # Servicios auxiliares (Detección de Temas y Compilador)
└── assets/                     # Activos estáticos encolados (CSS y JS modulados)
```

---

## 🚀 Empezar y Configuración Inicial

### Instalación

1. **Descarga:** Descarga el archivo `.zip` del plugin desde el panel de distribución o repositorio correspondiente.
2. **Instalación en Hooma Core (Plugin WordPress):** En el panel de WordPress, ve a Plugins y verifica la existencia y activación de **Hooma Core**. Después ve a a _Hooma → Módulos → Añadir módulo → Selecciona el archivo .zip → Instalar_.
3. **Activación:** Una vez instalado, activa **Hooma Smart Header** desde la lista de plugins.

> Este plugin forma parte del ecosistema Hooma y requiere el plugin base Hooma Core activo para su funcionamiento.

### Auto-Detección

El plugin detecta automáticamente el tema activo (Gutenberg/FSE, temas clásicos o builders como Divi) para aplicar la configuración más adecuada sin intervención manual.

### Configuración del Panel de Control

El panel administrativo del plugin se divide en 6 secciones principales de control:

---

## 🛠️ Sistema de Compilación Híbrido (Build & Fallback)

El plugin implementa un sistema inteligente de compilación híbrida para garantizar el máximo rendimiento del frontend:

### Compilación Activa (Node/NPM disponible)

Si tu servidor cuenta con Node.js y la función de PHP `proc_open` activa, cada vez que guardes los ajustes desde el panel de control, el plugin ejecutará localmente una compilación personalizada mediante `esbuild`:

```bash
node build.js --logo=1 --scroll=1 --initial=0
```

Esto genera un bundle único minificado (`hooma-smart-header.min.js`) aplicando tree-shaking, removiendo físicamente el código de las funciones y módulos que mantengas desactivados, entregando una huella de JS ultra-ligera en el cliente.

> Es posible que necesites guardar tus modificaciones dos veces en tu primer intento para que tu ambiente prepare todo lo necesario para la ejecución de la compilación.

### Mecanismo de Fallback (Sin Node/NPM)

Si tu entorno no soporta la ejecución de Node.js en tiempo real (por ejemplo, hosting compartido limitado), el plugin no fallará. De forma automática, generará un stub liviano que encola un cargador ES Module estándar:

```javascript
// Fallback Loader (compilación no disponible)
import "../hooma-smart-header.js";
```

Dado que los scripts se cargan de forma nativa en navegadores modernos mediante `type="module"`, el frontend cargará dinámicamente y por separado los archivos JS modularizados de tu carpeta `/assets/js/` con total transparencia y libre de errores.

---

## 📚 Referencia Técnica para Desarrolladores

Para una inmersión completa en la estructura del código, flujos de datos PHP-JS, almacenamiento de base de datos, variables CSS inyectadas en tiempo real y guías detalladas para agregar nuevos campos, por favor consulte la **[Guía Técnica del Desarrollador (TECHNICAL_GUIDE.md)](file:///c:/Users/Recepcio/Documents/Guilherme_Mandeli/Plugins/Hooma%20Modules/Hooma%20Smart%20Header%20Control/hooma-smart-header/TECHNICAL_GUIDE.md)** incluida en la raíz de este plugin.

---

_Desarrollado y mantenido con excelencia técnica por el **Hooma Team**._
