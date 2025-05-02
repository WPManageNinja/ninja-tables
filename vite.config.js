import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
    plugins: [vue()],
    base: '',
    build: {
        manifest: true,
        outDir: 'assets',
        rollupOptions: {
            input: {
                main: 'resources/admin/main.js'
            }
        }
    },
    server: {
        port: 5173,
        strictPort: true,
        cors: true,
        hmr: {
            host: 'localhost'
        }
    },
    resolve: {
        extensions: ['.js', '.vue', '.json'],
        alias: {
            '@': path.resolve(__dirname, 'resources')
        }
    }
})