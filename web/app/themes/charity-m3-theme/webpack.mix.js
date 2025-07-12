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

// Main JavaScript/TypeScript entry point (will import Lit components, StyleXJS styles etc.)
mix.ts('resources/scripts/main.ts', 'public/scripts') // Output: public/scripts/main.js (or .ts if source maps link it)
   // Gutenberg block editor scripts
   .js('app/Blocks/NewsletterSignup/newsletter-signup.editor.js', 'public/scripts/blocks/newsletter-signup-editor.js')
   .js('app/Blocks/FeaturedCallout/edit.js', 'public/scripts/blocks/featured-callout-editor.js')
   .js('app/Blocks/CardGrid/edit.js', 'public/scripts/blocks/card-grid-editor.js')
   .js('app/Blocks/CardItem/edit.js', 'public/scripts/blocks/card-item-editor.js')
   .js('app/Blocks/DonationForm/edit.js', 'public/scripts/blocks/donation-form-editor.js') // New: Donation Form editor script
   .sass('resources/styles/main.scss', 'public/styles/main.css') // For global M3 tokens & minimal base styles
   .options({
       postCss: [
           // Remove Tailwind, keep Autoprefixer if needed for global styles
           require('autoprefixer'),
       ],
       // Ensure ts-loader is used for .ts files if not default with mix.ts()
       // processCssUrls: true, // Already in global options
   })
   .babelConfig({ // Point to our babel.config.js
        configFile: path.resolve(__dirname, 'babel.config.js'),
   })
   .sourceMaps(true, 'source-map')
   .version();

// Configure Webpack for StyleXJS and TypeScript
mix.webpackConfig({
  resolve: {
    extensions: ['.js', '.jsx', '.ts', '.tsx', '.json'], // Ensure .ts and .tsx are resolved
    modules: [
      path.resolve(__dirname, '../../../../node_modules'), // Project root node_modules
      path.resolve(__dirname, 'node_modules'), // Theme's own node_modules
    ],
    // alias: { // Optional: if you need aliases for tokens or components
    //   '@tokens': path.resolve(__dirname, 'resources/scripts/tokens'),
    //   '@components': path.resolve(__dirname, 'resources/scripts/components'),
    // }
  },
  module: {
    rules: [
      // TypeScript loader (Mix's default for .ts() should handle this, but explicit for clarity)
      // {
      //   test: /\.tsx?$/,
      //   loader: 'ts-loader',
      //   exclude: /node_modules/,
      //   options: {
      //     // appendTsSuffixTo: [/\.vue$/], // Example if using Vue with TS
      //   }
      // },
      // Babel loader for JS/TS files (to process StyleXJS plugin, etc.)
      // Mix applies Babel to JS/TS output by default. Ensure it uses our babel.config.js.
      // The .babelConfig() call above should ensure this.

      // CSS loader for .stylex.css files (if StyleXJS outputs them separately and they are imported in JS/TS)
      // This setup assumes StyleXJS generated CSS is imported in JS/TS and handled by standard CSS processing.
      // If StyleXJS babel plugin is configured to output a single main CSS file, ensure that file
      // is either imported by main.scss or included as another entry point for Mix.
      // For now, we assume component-level CSS imports.
    ]
  }
});

// Copy images and fonts
mix.copyDirectory('resources/images', 'public/images');
mix.copyDirectory('resources/fonts', 'public/fonts');

// Global Mix options
mix.options({
  processCssUrls: true,
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
