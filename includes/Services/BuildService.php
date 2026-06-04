<?php
/**
 * Servicio encargado de la ejecución de la build y gestión del fallback.
 */

namespace HoomaModules\HoomaSmartHeader\Services;

class BuildService
{
    private $root_path;
    private $option_name = 'hooma_smart_header_settings';

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
                'message' => 'Build no posible: ' . $check['reason'],
                'output' => 'Fallback activado (usando JS sin minificar).',
                'fallback' => true
            ];
        }

        // 2. Preparar los flags basados en las opciones actuales
        $options = get_option($this->option_name);
        $flags = $this->prepare_flags($options);

        // 3. Verificar/Instalar dependencias si faltan
        if (!file_exists($this->root_path . 'node_modules/esbuild')) {
            $install_res = $this->run_command("npm install");
            if (!$install_res['success'] || !file_exists($this->root_path . 'node_modules/esbuild')) {
                $this->activate_fallback();
                return [
                    'success' => false,
                    'message' => 'Error al instalar dependencias.',
                    'output' => $install_res['output'],
                    'fallback' => true
                ];
            }
        }

        // 4. Ejecutar el comando de build
        $command = "node build.js " . implode(' ', $flags);
        $build_res = $this->run_command($command);

        // Sanación automática de plataforma para esbuild (ej: desarrollo local en Windows subido a hosting Linux)
        if (!$build_res['success'] && strpos($build_res['output'], 'another platform') !== false) {
            $node_modules_dir = $this->root_path . 'node_modules';
            if (is_dir($node_modules_dir)) {
                $this->delete_directory($node_modules_dir);
            }
            $install_res = $this->run_command("npm install");
            if ($install_res['success']) {
                $build_res = $this->run_command($command);
            }
        }

        $bundle_exists = file_exists($this->root_path . 'assets/js/dist/hooma-smart-header.min.js');

        if ($build_res['success'] && $bundle_exists) {
            return [
                'success' => true,
                'message' => 'Build completada con éxito.',
                'output' => $build_res['output'],
                'fallback' => false
            ];
        } else {
            $this->activate_fallback();
            return [
                'success' => false,
                'message' => 'Error durante la construcción.',
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

        // Asegurar que el directorio existe
        if (!is_dir($dist_dir)) {
            @mkdir($dist_dir, 0755, true);
        }

        // Stub: importa el entry point desde la carpeta padre (ruta relativa segura)
        $stub = "// Hooma Smart Header — Fallback Loader (build no disponible)\n";
        $stub .= "import '../hooma-smart-header.js';\n";

        @file_put_contents($bundle_path, $stub);
    }

    /**
     * Prepara los flags para build.js
     */
    private function prepare_flags($options)
    {
        $flags = [];
        $flags[] = '--logo=' . (isset($options['logo_switcher']['enabled']) && $options['logo_switcher']['enabled'] === '1' ? '1' : '0');
        $flags[] = '--initial=' . (isset($options['behavior']['hide_on_scroll']) && $options['behavior']['hide_on_scroll'] === '1' ? '1' : '0');
        $flags[] = '--scroll=' . (isset($options['scroll_behavior']['enabled']) && $options['scroll_behavior']['enabled'] === '1' ? '1' : '0');
        $flags[] = '--responsive=1';
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

    /**
     * Elimina un directorio y su contenido de forma recursiva.
     */
    private function delete_directory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->delete_directory($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}
