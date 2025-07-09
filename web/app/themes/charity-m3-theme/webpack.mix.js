const mix = require('laravel-mix');
const path = require('path');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

// Set the public path for the theme.
// This is where compiled assets will be output.
// Acorn's asset() helper will look in `public/` by default relative to theme root.
mix.setPublicPath('public'); // Output to theme_root/public/

// Set resource root for easier pathing
mix.setResourceRoot('../'); // Relative to public path, so it points to theme_root/

mix.js('resources/scripts/main.js', 'scripts') // Output: public/scripts/main.js
   .js('app/Blocks/NewsletterSignup/newsletter-signup.editor.js', 'scripts/blocks') // Block specific JS
   .sass('resources/styles/main.scss', 'styles') // Output: public/styles/main.css
   // If you don't use SASS, and prefer plain CSS or PostCSS:
   // .postCss('resources/styles/main.css', 'styles', [
   //   require('postcss-import'),
   //   require('tailwindcss'), // if you were to use Tailwind
   //   require('autoprefixer'),
   // ])
   .sourceMaps(true, 'source-map') // Enable source maps for development
   .version(); // Enable versioning for cache busting in production

// Alias for @material/web if necessary, though Webpack should resolve it from project root node_modules.
// If issues arise, you might need to configure Webpack further.
// mix.alias({
//     '@material/web': path.join(__dirname, '../../../../node_modules/@material/web'),
// });

// Configure Webpack to resolve modules from project root node_modules
// This helps ensure that @material/web (installed in project root) is found.
mix.webpackConfig({
  resolve: {
    modules: [
      path.resolve(__dirname, '../../../../node_modules'), // Project root node_modules
      'node_modules' // Theme's own node_modules
    ],
    // If using Lit (which MWC does), you might need this for deduplication or specific handling.
    // dedupe: ['lit', 'lit-html', '@lit/reactive-element'],
  },
  // MWC components are distributed as JS modules.
  // Some components might need specific loaders if there are issues,
  // but Mix should handle standard JS/TS well.
  module: {
    rules: [
      // Add any specific loaders here if needed
      // For example, if MWC components had CSS inside JS that needed extraction
    ]
  }
});

// Copy images and fonts
mix.copyDirectory('resources/images', 'public/images');
mix.copyDirectory('resources/fonts', 'public/fonts'); // If you have theme-specific fonts

// Options for Mix
mix.options({
  processCssUrls: true, // Process/optimize relative URLs in CSS
  // terser: { // Terser options for JS minification
  //   extractComments: false, // Don't extract comments to a separate file
  // },
  // postCss: [ // Global PostCSS plugins
  //   require('autoprefixer'),
  // ],
});

// If you want to use BrowserSync
// mix.browserSync({
//   proxy: 'https://mycharitysite.local', // Your local development URL
//   files: [
//     'app/**/*.php',
//     'resources/views/**/*.php',
//     'public/styles/**/*.css',
//     'public/scripts/**/*.js'
//   ],
//   injectChanges: true,
// });
