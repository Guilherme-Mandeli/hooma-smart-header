<?php
defined('HOOMA_PATH') || exit;

// Retrieve built file info
$bundle_path = dirname(dirname(__DIR__)) . '/assets/js/dist/hooma-smart-header.min.js';
$bundle_exists = file_exists($bundle_path);
$bundle_size = $bundle_exists ? size_format(filesize($bundle_path)) : 'N/A';
$bundle_date = $bundle_exists ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), filemtime($bundle_path)) : 'N/A';

// Check environment
$node_version = shell_exec('node -v');
$npm_version = shell_exec('npm -v');
$is_node_available = !empty($node_version);

?>

<div class="wrap hooma-sh-wrap hooma-sh-build-page">
    <h1>Optimización y Build</h1>

    <div class="hooma-sh-section">
        <div class="hooma-sh-section-header">
            <h2>Estado del Bundle (Frontend)</h2>
        </div>
        <div class="hooma-sh-content">
            <div class="hsh-row">
                <div class="hsh-row-label">
                    <strong>Estado</strong>
                </div>
                <div class="hsh-row-control">
                    <?php if ($bundle_exists) : ?>
                        <span class="hooma-sh-status-pill success">Generado</span>
                    <?php else : ?>
                        <span class="hooma-sh-status-pill warning">No encontrado (Usando fallback)</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($bundle_exists) : ?>
                <div class="hsh-row">
                    <div class="hsh-row-label">
                        <strong>Tamaño</strong>
                    </div>
                    <div class="hsh-row-control">
                        <code><?php echo esc_html($bundle_size); ?></code>
                    </div>
                </div>

                <div class="hsh-row">
                    <div class="hsh-row-label">
                        <strong>Última Build</strong>
                    </div>
                    <div class="hsh-row-control">
                        <?php echo esc_html($bundle_date); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="hooma-sh-section">
        <div class="hooma-sh-section-header">
            <h2>Gestión de Build</h2>
        </div>
        <div class="hooma-sh-content">
            <div class="hsh-row-content">
                <p style="margin-bottom: 20px;">Este proceso unifica todos los archivos JavaScript del frontend en un único archivo minificado. 
                   Además, excluye inteligentemente el código de las funciones que tengas desactivadas.</p>
                
                <?php if (!$is_node_available) : ?>
                    <div class="notice notice-error inline" style="margin: 0;">
                        <p><strong>Node.js no detectado:</strong> El servidor requiere Node.js instalado para realizar la build. 
                           Si estás en un entorno local, asegúrate de que <code>node</code> esté en el PATH.</p>
                    </div>
                <?php else : ?>
                    <p><strong>Entorno:</strong> Node <?php echo esc_html(trim($node_version)); ?> y NPM <?php echo esc_html(trim($npm_version)); ?> detectados.</p>
                    <div class="hooma-sh-build-actions">
                        <button type="button" id="hsh-start-build" class="button button-primary button-large">
                            Generar Nueva Build
                        </button>
                        <span class="spinner" id="hsh-build-spinner"></span>
                    </div>
                    <div id="hsh-build-console" class="hsh-build-console"></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="hooma-sh-section">
        <div class="hooma-sh-section-header">
            <h2>Detalles Técnicos</h2>
        </div>
        <div class="hooma-sh-content">
            <div class="hsh-row-content">
                <p class="description" style="margin-bottom: 10px;">Al generar la build, se aplican los siguientes filtros basados en tu configuración:</p>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><strong>Tree-shaking:</strong> Eliminación de código muerto.</li>
                    <li><strong>Minificación:</strong> Ofuscación y compresión.</li>
                    <li><strong>Vanilla JS:</strong> Cero dependencias de jQuery.</li>
                    <li><strong>Feature Flags:</strong> Exclusión de módulos desactivados.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.hooma-sh-status-pill {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}
.hooma-sh-status-pill.success { background: #d4edda; color: #155724; }
.hooma-sh-status-pill.warning { background: #fff3cd; color: #856404; }
.hooma-sh-build-actions { display: flex; align-items: center; gap: 10px; margin-top: 20px; }
#hsh-build-spinner { float: none; margin: 0; }
</style>
