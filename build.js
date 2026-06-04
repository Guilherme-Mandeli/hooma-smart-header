const esbuild = require('esbuild');
const path = require('path');

// Get arguments
const args = process.argv.slice(2);
const isWatch = args.includes('--watch');

// Default feature flags (all on by default if not specified)
const defines = {
    'process.env.BUILD_MODE': JSON.stringify(isWatch ? 'development' : 'production'),
    'HOOMA_SH_LOGO_ENABLED': JSON.stringify(true),
    'HOOMA_SH_INITIAL_ENABLED': JSON.stringify(true),
    'HOOMA_SH_SCROLL_ENABLED': JSON.stringify(true),
    'HOOMA_SH_RESPONSIVE_ENABLED': JSON.stringify(true),
};

// Check for flags passed from WP (e.g., --logo=0)
args.forEach(arg => {
    if (arg.startsWith('--logo=')) defines['HOOMA_SH_LOGO_ENABLED'] = JSON.stringify(arg.split('=')[1] === '1');
    if (arg.startsWith('--initial=')) defines['HOOMA_SH_INITIAL_ENABLED'] = JSON.stringify(arg.split('=')[1] === '1');
    if (arg.startsWith('--scroll=')) defines['HOOMA_SH_SCROLL_ENABLED'] = JSON.stringify(arg.split('=')[1] === '1');
    if (arg.startsWith('--responsive=')) defines['HOOMA_SH_RESPONSIVE_ENABLED'] = JSON.stringify(arg.split('=')[1] === '1');
});

const config = {
    entryPoints: ['assets/js/hooma-smart-header.js'],
    bundle: true,
    format: 'iife',
    minify: !isWatch,
    sourcemap: isWatch,
    outfile: 'assets/js/dist/hooma-smart-header.min.js',
    target: ['es2015'],
    define: defines,
    logLevel: 'info'
};

if (isWatch) {
    esbuild.context(config).then(ctx => {
        console.log('Watching for changes...');
        ctx.watch();
    });
} else {
    esbuild.build(config).catch(() => process.exit(1));
}
