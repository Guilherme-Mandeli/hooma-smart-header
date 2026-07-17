<?php
/**
 * Clase encastrada de verificar si el entorno permite realizar builds.
 */

namespace HoomaModules\HoomaSmartHeader\Services;

use Hooma;

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

        // 2. Verificar si el paquete binario de esbuild está disponible en Hooma Core
        if (!Hooma::packages()->exists('esbuild')) {
            return [
                'possible' => false,
                'reason' => 'El paquete binario "esbuild" de Hooma Core no está registrado o disponible.'
            ];
        }

        $esbuild = Hooma::packages()->get('esbuild');
        $esbuild_bin = $esbuild->get_entry_path('bin');

        if (empty($esbuild_bin) || !Hooma::filesystem()->exists($esbuild_bin)) {
            return [
                'possible' => false,
                'reason' => 'El archivo binario de esbuild no existe en la ruta esperada: ' . $esbuild_bin
            ];
        }

        // 3. Cuidado especial de permisos para binarios en Unix
        if (DIRECTORY_SEPARATOR === '/' && !is_executable($esbuild_bin)) {
            @chmod($esbuild_bin, 0755);
            if (!is_executable($esbuild_bin)) {
                return [
                    'possible' => false,
                    'reason' => 'El binario de esbuild no tiene permisos de ejecución y no se pudieron asignar automáticamente.'
                ];
            }
        }

        // 4. Verificar permisos de escritura en el directorio de salida
        $root_path = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
        $dist_path = $root_path . 'assets/js/dist';
        if (!Hooma::filesystem()->exists($dist_path)) {
            if (!Hooma::filesystem()->mkdir($dist_path)) {
                return [
                    'possible' => false,
                    'reason' => 'No se pudo crear el directorio assets/js/dist/.'
                ];
            }
        }

        return [
            'possible' => true,
            'reason' => "Entorno listo. Binario esbuild: " . basename($esbuild_bin)
        ];
    }
}
