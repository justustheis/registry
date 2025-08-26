import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [vue()],
    publicDir: false,
    server: {
        port: 5174,
        strictPort: true,
        hmr: { port: 5174 },
    },
    build: {
        outDir: 'public',
        emptyOutDir: true,
        manifest: 'manifest.json',
        rollupOptions: {
            input: 'resources/js/registry.js',
        },
    },
});
