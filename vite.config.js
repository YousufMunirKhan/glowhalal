import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

// Fonts are self-hosted from public/fonts and declared in resources/css/fonts.css.
// The laravel-vite-plugin `bunny()` helper is deliberately NOT used: a
// third-party font host is a second DNS lookup and TLS handshake on a congested
// Pakistani cell, and it is the most common cause of a 4-second first paint.
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
