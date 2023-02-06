const mix = require('laravel-mix');
const exec = require('child_process').exec;
const min = '';
const assetVersion = '3.1.0';
mix.setPublicPath('assets');
mix.setResourceRoot('../');

mix.js('resources/admin/gutenblock.js', `assets/js/ninja-tables-gutenblock.js`).react();

mix.js('resources/admin/Boot.js', `assets/js/ninja-tables-boot.js`)
    .js('resources/admin/main.js', `assets/js/ninja-tables-admin.js`)
    .js('resources/public/js/ninja-tables-footable.js', `assets/js/ninja-tables-footable.${assetVersion}.js`)
    .js('resources/admin/ninja-table-tinymce-button.js', `assets/js/ninja-table-tinymce-button.js`)
    .js('resources/public/js/ninja-tables-builder.js', `assets/js/ninja-table-builder-public.js`)

    .vue({
        version: 2
    })
    .sass('resources/public/css/_public.scss', `assets/css/ninjatables-public.css`)
    .sass('resources/public/css/_table_builder.scss', `assets/css/ninja-table-builder-public.css`)
    .sass('resources/admin/css/ninja-tables-admin.scss', `assets/css/ninja-tables-admin.css`)
    .sass('resources/admin/css/vendor.scss', 'assets/css/ninja-tables-vendor.css')
    .sass('resources/admin/css/gutenblock.scss', 'assets/css/ninja-tables-gutenblock.css')
    .sass('resources/preview/preview.scss', 'assets/css/ninja-tables-preview.css')
    .sourceMaps(false);

mix.then(() => {
    exec('rtlcss ./assets/css/ninja-tables-vendor.css ./assets/css/ninja-tables-vendor-rtl.css', (error) => {
        if (error) {
            console.error(`exec error: ${error}`);
            return;
        }
    });

    exec('rtlcss ./assets/css/ninjatables-public.css ./assets/css/ninjatables-public-rtl.css', (error) => {
        if (error) {
            console.error(`exec error: ${error}`);
            return;
        }
    });
});
