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
mix.sass('assets/scss/admin-plugin-page.scss', 'public/css')
   .sass('assets/scss/admin-global.scss', 'public/css')
  //  .react('assets/js/admin.js', 'public/js')
   .copyDirectory('assets/images/', 'public/images/')
   .options({
     processCssUrls: false,
   })
   .sourceMaps(false, 'eval-source-map');
