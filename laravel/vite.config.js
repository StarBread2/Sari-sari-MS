import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react'

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/main.tsx',
            refresh: true,
        }),
        react(),
    ],
    server: {
        host: '0.0.0.0', // <--- bind to all network interfaces
        port: 5173,      // make sure this matches your docker-compose port
        strictPort: true, // fail if 5173 is taken
        hmr: {
            host: 'localhost', // your machine host for HMR
            protocol: 'ws',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
