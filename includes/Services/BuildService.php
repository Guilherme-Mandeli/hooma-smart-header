<?php
/**
 * Servicio encargado de la ejecución de la build y gestión del fallback.
 */

namespace HoomaModules\HoomaSmartHeader\Services;

use Hooma;

class BuildService
{
    private $root_path;

    public function __construct()
    {
        // El root es dos niveles arriba de includes/Services/
        $this->root_path = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
    }

    /**
     * Ejecuta la build completa.
     * 
     * @return array Resultado de la operación.
     */
    public function execute()
    {
        // 1. Verificación previa
        $check = BuildCondition::check();
        if (!$check['possible']) {
            $this->activate_fallback();
            return [
                'success' => false,
                'message' => 'El entorno del servidor no dispone de esbuild. Los scripts y estilos se cargarán en modo de compatibilidad nativa (sin optimizaciones avanzadas).',
                'output' => 'Detalle técnico: ' . $check['reason'],
                'fallback' => true
            ];
        }

        // 2. Obtener el binario de esbuild de Hooma Core
        $esbuild = Hooma::packages()->get('esbuild');
        $esbuild_bin = $esbuild->get_entry_path('bin');

        // 3. Preparar las opciones y los flags basados en las opciones actuales
        $options = Hooma::settings()->get('hooma_smart_header.settings', []);
        $flags = $this->prepare_flags($options);

        // 4. Configurar rutas de origen y destino
        $input_file  = $this->root_path . 'assets/js/hooma-smart-header.js';
        $output_file = $this->root_path . 'assets/js/dist/hooma-smart-header.min.js';

        // 5. Construir el comando para ejecutar esbuild binario directamente
        $command_parts = [
            escapeshellarg($esbuild_bin),
            escapeshellarg($input_file),
            '--bundle',
            '--minify',
            '--format=iife',
            '--target=es2015',
            '--outfile=' . escapeshellarg($output_file)
        ];

        foreach ($flags as $flag) {
            $command_parts[] = $flag;
        }

        $command_parts[] = '2>&1';
        $command = implode(' ', $command_parts);

        // 6. Ejecutar compilación
        $build_res = $this->run_command($command);

        $bundle_exists = Hooma::filesystem()->exists($output_file);

        if ($build_res['success'] && $bundle_exists) {
            return [
                'success' => true,
                'message' => 'Build completada con éxito mediante binario esbuild.',
                'output' => $build_res['output'],
                'fallback' => false
            ];
        } else {
            $this->activate_fallback();
            return [
                'success' => false,
                'message' => 'El entorno del servidor no dispone de esbuild. Los scripts y estilos se cargarán en modo de compatibilidad nativa (sin optimizaciones avanzadas).',
                'output' => $build_res['output'],
                'fallback' => true
            ];
        }
    }

    /**
     * Escribe un stub ES module en el archivo minificado para que el frontend
     * nunca reciba un 404. El stub simplemente re-importa el entry point sin minificar.
     */
    private function activate_fallback()
    {
        $dist_dir    = $this->root_path . 'assets/js/dist/';
        $bundle_path = $dist_dir . 'hooma-smart-header.min.js';

        // Stub: importa el entry point desde la carpeta padre (ruta relativa segura)
        $stub = "// Hooma Smart Header — Fallback Loader (build no disponible)\n";
        $stub .= "import '../hooma-smart-header.js';\n";

        Hooma::filesystem()->write($bundle_path, $stub);
    }

    /**
     * Prepara los flags para build.js
     */
    private function prepare_flags($options)
    {
        $flags = [];
        
        $logo_enabled       = (isset($options['logo_switcher']['enabled']) && $options['logo_switcher']['enabled'] === '1') ? 'true' : 'false';
        $initial_enabled    = (isset($options['behavior']['hide_on_scroll']) && $options['behavior']['hide_on_scroll'] === '1') ? 'true' : 'false';
        $scroll_enabled     = (isset($options['scroll_behavior']['enabled']) && $options['scroll_behavior']['enabled'] === '1') ? 'true' : 'false';
        $responsive_enabled = 'true';
        
        $flags[] = '--define:process.env.BUILD_MODE="production"';
        $flags[] = '--define:HOOMA_SH_LOGO_ENABLED=' . $logo_enabled;
        $flags[] = '--define:HOOMA_SH_INITIAL_ENABLED=' . $initial_enabled;
        $flags[] = '--define:HOOMA_SH_SCROLL_ENABLED=' . $scroll_enabled;
        $flags[] = '--define:HOOMA_SH_RESPONSIVE_ENABLED=' . $responsive_enabled;
        
        return $flags;
    }

    /**
     * Ejecuta un comando del sistema y captura la salida.
     */
    private function run_command($command)
    {
        $descriptorspec = [
            0 => ["pipe", "r"], // stdin
            1 => ["pipe", "w"], // stdout
            2 => ["pipe", "w"]  // stderr
        ];

        $process = proc_open($command, $descriptorspec, $pipes, $this->root_path);

        $stdout = "";
        $stderr = "";

        if (is_resource($process)) {
            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            $return_value = proc_close($process);
            $success = ($return_value === 0);
            
            return [
                'success' => $success,
                'output' => $stdout . $stderr,
                'return_code' => $return_value
            ];
        }

        return [
            'success' => false,
            'output' => 'No se pudo iniciar el proceso.',
            'return_code' => -1
        ];
    }


}
