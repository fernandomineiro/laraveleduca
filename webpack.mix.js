let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
    .styles([
        // 'resources/views/_assets/css/bootstrap.min.css', // Bootstrap v3.0.3
        // 'resources/views/_assets/css/font-awesome.min.css', // Font Awesome 4.7.0
        'resources/views/_assets/css/ionicons.min.css', // Ionicons, v2.0.0
        'resources/views/_assets/css/bootstrap-datepicker.css',
        'resources/views/_assets/css/jquery-ui.css',
        'resources/views/_assets/css/jquery-confirm.min.css',
        'resources/views/_assets/css/sweetalert2.min.css',
        'resources/views/_assets/css/AdminLTE.css',
        'resources/views/_assets/css/wickedpicker.min.css',
        'resources/views/_assets/css/app.css',
        'resources/views/_assets/css/select2/select2.min.css',

        'resources/views/_assets/css/from-CDN/daterangepicker.css',

        'resources/views/_assets/css/from-CDN/ionicons.min.css', // Ionicons, v4.5.9-1

        'resources/views/_assets/css/from-CDN/bootstrap.min.css', // Bootstrap v3.3.1
        'resources/views/_assets/css/from-CDN/font-awesome.min.css', // Font Awesome 4.3.0
        'resources/views/_assets/css/from-CDN/bootstrap-datetimepicker.css',

        'resources/views/_assets/css/datatables.net-bs/css/dataTables.bootstrap.min.css',

        'resources/views/_assets/css/bootstrap-wysihtml5/bootstrap3-wysihtml5.css',
        'resources/views/_assets/css/from-CDN/jquery.dataTables.min.css',
        'resources/views/_assets/css/from-CDN/responsive.dataTables.min.css',
        'resources/views/_assets/css/from-CDN/fixedHeader.dataTables.min.css',
        'resources/views/_assets/css/toastr/build/toastr.min.css',

        'resources/views/_assets/css/styles.css',
        'resources/views/_assets/css/fixed-header.css',
    ], 'public/assets/css/vendors.css')

    .scripts([
        'resources/views/_assets/js/jquery.js',

        'resources/views/_assets/js/from-CDN/bootstrap.min.js', // Bootstrap v3.3.1

        'resources/views/_assets/js/from-CDN/jquery.dataTables.min.js',
        'resources/views/_assets/js/from-CDN/dataTables.responsive.min.js',
        'resources/views/_assets/js/from-CDN/dataTables.fixedHeader.min.js',

        'resources/views/_assets/js/jquery-confirm.min.js',
        'resources/views/_assets/js/sweetalert2.all.min.js',

        'resources/views/_assets/css/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.js',

        'resources/views/_assets/js/validarCNPJCPF.js',

        // 'resources/views/_assets/js/from-CDN/ckeditor.js', // version:"4.8.0 (Basic)"
        'resources/views/_assets/js/from-CDN/moment-with-locales.min.js',
        'resources/views/_assets/js/from-CDN/jszip.min.js',
        'resources/views/_assets/js/from-CDN/buttons.colVis.min.js',
        'resources/views/_assets/js/from-CDN/buttons.print.min.js',
        // 'resources/views/_assets/js/from-CDN/additional-methods.min.js',
        'resources/views/_assets/js/thenBy.min.js',
        'resources/views/_assets/css/toastr/build/toastr.min.js',
        'resources/views/_assets/js/jquery.disableAutoFill.min.js',

        // 'resources/views/_assets/js/bootstrap.min.js',  // Bootstrap v3.3.7
        'resources/views/_assets/js/adminlte.min.js',
        'resources/views/_assets/js/functions.js',
        'resources/views/_assets/js/app.js',
        'resources/views/_assets/js/init.js',

        'resources/views/_assets/js/select2/select2.min.js',
        'resources/views/_assets/js/from-CDN/jquery.validate.min.js',
        'resources/views/_assets/js/wickedpicker.min.js',

        'resources/views/_assets/js/bootstrap-datepicker.js',
        'resources/views/_assets/js/from-CDN/bootstrap-datetimepicker.min.js',
        'resources/views/_assets/js/from-CDN/daterangepicker.min.js',
        'resources/views/_assets/js/bootstrap-datepicker.pt-BR.min.js',

        'resources/views/_assets/js/jquery.mask.js',
        'resources/views/_assets/js/jquery.maskMoney.min.js',
        'resources/views/_assets/js/input-mask/jquery.inputmask.js',
        'resources/views/_assets/js/input-mask/jquery.inputmask.date.extensions.js',
        'resources/views/_assets/js/input-mask/jquery.inputmask.extensions.js',

        'resources/views/_assets/js/datatables.net/js/jquery.dataTables.min.js',
        'resources/views/_assets/css/datatables.net-bs/js/dataTables.bootstrap.min.js',

        'resources/views/_assets/js/jquery-confirm-v3.3.4/dist/jquery-confirm.min.js',
        'resources/views/_assets/js/jquery-ui.js',
    ], 'public/assets/js/app.js')

    .copyDirectory('resources/views/_assets/css/fonts', 'public/assets/fonts')
    .copyDirectory('resources/views/_assets/images', 'public/assets/images')
    .copyDirectory('resources/views/_assets/js/ckeditor', 'public/assets/plugins/ckeditor')
    .copy('resources/views/_assets/css/toastr/build/toastr.js.map', 'public/assets/js/toastr.js.map')
    .options({
        processCssUrls: false
    })
    .version()
;
