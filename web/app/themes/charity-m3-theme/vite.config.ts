import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import stylexPlugin from '@stylexjs/vite-plugin';
import liveReload from 'vite-plugin-live-reload';
import path from 'path';

export default defineConfig(({ command }) => ({
    base: command === 'serve' ? '' : '/public/', // Set base for dev vs. build
    publicDir: 'resources/static', // Static assets to copy to public dir

    build: {
        outDir: 'public',
        assetsDir: 'assets',
        manifest: true, // Generate manifest.json
        rollupOptions: {
            input: {
                // Main theme assets
                main: 'resources/scripts/main.ts',
                styles: 'resources/styles/main.scss',
                // Gutenberg Block Editor Scripts
                'newsletter-signup-editor': 'app/Blocks/NewsletterSignup/newsletter-signup.editor.js',
                'featured-callout-editor': 'app/Blocks/FeaturedCallout/edit.js',
                'card-grid-editor': 'app/Blocks/CardGrid/edit.js',
                'card-item-editor': 'app/Blocks/CardItem/edit.js',
                'donation-form-editor': 'app/Blocks/DonationForm/edit.js',
                'carousel-editor': 'app/Blocks/Carousel/edit.js',
                'impact-stat-editor': 'app/Blocks/ImpactStat/edit.js',
                'testimonial-editor': 'app/Blocks/Testimonial/edit.js',
                'post-list-editor': 'app/Blocks/PostList/edit.js',
                // Admin Page & Plugin Scripts
                'campaign-sidebar': 'app/Blocks/CampaignSidebar/index.js',
                'analytics-dashboard': 'resources/scripts/admin/analytics-dashboard-loader.ts',
            },
        },
    },

    server: {
        // Required for HMR to work with DDEV/Lando/etc.
        host: '0.0.0.0',
        port: 3000, // Or any port you prefer
        strictPort: true,
        // Generate a 'hot' file to let our PHP helper know the dev server is running
        hmr: {
            host: 'localhost', // Or your local dev URL
        },
    },

    plugins: [
        // Use React plugin to handle JSX transforms in Gutenberg JS files
        react({
             // Configure Babel if needed for StyleXJS or other transforms
             babel: {
                presets: ['@babel/preset-env', '@babel/preset-typescript'],
                plugins: [
                    // Note: StyleXJS Vite plugin should ideally handle this,
                    // but sometimes explicit Babel config is needed.
                    // This configuration assumes the Vite plugin is sufficient.
                ],
            },
        }),
        // StyleXJS Vite Plugin
        stylexPlugin(),
        // Live reload for Blade/PHP files
        liveReload([
            path.resolve(__dirname, 'app/**/*.php'),
            path.resolve(__dirname, 'resources/views/**/*.blade.php'),
        ]),
    ],

    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/scripts'),
            // Add other aliases if needed
        },
    },
}));
