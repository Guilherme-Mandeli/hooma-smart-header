<?php
/**
 * Clase encastrada de verificar si el entorno permite realizar builds.
 */

namespace HoomaModules\HoomaSmartHeader\Services;

class BuildCondition
{
    /**
     * Verifica si es posible realizar una build en este servidor.
     *
     * @return array ['possible' => bool, 'reason' => string]
     */
    public static function check()
    {
        // 1. Verificar si proc_open está disponible
        if (!function_exists('proc_open')) {
            return [
                'possible' => false,
                'reason' => 'La función PHP "proc_open" está deshabilitada en este servidor.'
            ];
        }

        // 2. Verificar si node está instalado
        $node_version = self::get_command_version('node -v');
        if (!$node_version) {
            return [
                'possible' => false,
                'reason' => 'Node.js no está instalado o no es accesible (node -v falló).'
            ];
        }

        // 3. Verificar si npm está instalado
        $npm_version = self::get_command_version('npm -v');
        if (!$npm_version) {
            return [
                'possible' => false,
                'reason' => 'NPM no está instalado o no es accesible (npm -v falló).'
            ];
        }

        // 4. Verificar permisos de escritura en el directorio de salida
        $root_path = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
        $dist_path = $root_path . 'assets/js/dist';
        if (!is_dir($dist_path)) {
            if (!wp_mkdir_p($dist_path)) {
                return [
                    'possible' => false,
                    'reason' => 'No se pudo crear el directorio assets/js/dist/.'
                ];
            }
        }

        if (!is_writable($dist_path)) {
            return [
                'possible' => false,
                'reason' => 'El directorio assets/js/dist/ no tiene permisos de escritura.'
            ];
        }

        return [
            'possible' => true,
            'reason' => "Entorno listo. Node: $node_version, NPM: $npm_version"
        ];
    }

    /**
     * Intenta obtener la versión de un comando.
     */
    private static function get_command_version($cmd)
    {
        $descriptorspec = [
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];

        $process = @proc_open($cmd, $descriptorspec, $pipes);

        if (is_resource($process)) {
            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            return trim($stdout);
        }

        return false;
    }
}
