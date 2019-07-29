const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management (this is borrowed from Laravel)
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

/*  eslint-disable indent */
mix.sass('assets/scss/admin-pickup-page.scss', 'public/css')
    .sass('assets/scss/pickup-page.scss', 'public/css')
    .sass('assets/scss/feedback-page.scss', 'public/css')
    .sass('assets/scss/admin-global.scss', 'public/css')
    .scripts([
      'node_modules/materialize-css/dist/js/materialize.min.js',
      'assets/js/all.js'
      ], 'public/js/all.js')
    .scripts([
      'node_modules/materialize-css/dist/js/materialize.min.js',
      'assets/js/feedback-page.js'
      ], 'public/js/feedback-page.js')
    .react('assets/js/app.js', 'public/js/app.v16.js')
    .js('assets/js/admin.js', 'public/js')
    .copyDirectory('assets/images/', 'public/images/')
    .browserSync({
      proxy: 'wp.localhost',
      files: ['*.php', 'lib/', 'public/'],
      open: false,
      ghostMode: false,
    })
    .options({
      processCssUrls: false,
    })
    .sourceMaps(false, 'eval-source-map');
