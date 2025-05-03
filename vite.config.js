// import { defineConfig } from 'vite'
// import vue from '@vitejs/plugin-vue'
// import path from 'path'

// export default defineConfig({
//     plugins: [vue()],
//     base: '',
//     build: {
//         manifest: true,
//         outDir: 'assets',
//         rollupOptions: {
//             input: {
//                 main: 'resources/admin/main.js'
//             }
//         }
//     },
//     server: {
//         port: 5173,
//         strictPort: true,
//         cors: true,
//         hmr: {
//             host: 'localhost'
//         }
//     },
//     resolve: {
//         extensions: ['.js', '.vue', '.json'],
//         alias: {
//             '@': path.resolve(__dirname, 'resources')
//         }
//     }
// })

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import react from '@vitejs/plugin-react'
import path from 'path'
import { exec } from 'child_process'

export default defineConfig({
    plugins: [
        vue(),
        react(),
    ],
    base: '',
    build: {
        manifest: true,
        outDir: 'assets',
        rollupOptions: {
            input: {
                // Admin scripts
                boot: 'resources/admin/Boot.js',
                main: 'resources/admin/main.js',
                gutenblock: 'resources/admin/gutenblock.js',
                tinymce: 'resources/admin/ninja-table-tinymce-button.js',
                
                // Public scripts
                footable: 'resources/public/js/ninja-tables-footable.js',
                builder: 'resources/public/js/ninja-tables-builder.js',
                
                // Styles
                public: 'resources/public/css/_public.scss',
                table_builder: 'resources/public/css/_table_builder.scss',
                admin: 'resources/admin/css/ninja-tables-admin.scss',
                vendor: 'resources/admin/css/vendor.scss',
                gutenblock_css: 'resources/admin/css/gutenblock.scss',
                preview: 'resources/preview/preview.scss'
            },
            output: {
                entryFileNames: 'js/[name].js',
                chunkFileNames: 'js/[name]-[hash].js',
                assetFileNames: ({name}) => {
                    if (/\.(gif|jpe?g|png|svg)$/.test(name ?? '')) {
                        return 'img/[name][extname]'
                    }
                    if (/\.css$/.test(name ?? '')) {
                        return 'css/[name][extname]'
                    }
                    return '[name][extname]'
                }
            }
        }
    },
    server: {
        port: 5173,
        strictPort: true,
        cors: true,
        origin: 'http://localhost:5173',
        hmr: {
            host: 'localhost',
            protocol: 'ws',
        }
    },
    resolve: {
        extensions: ['.js', '.jsx', '.vue', '.json', '.scss'],
        alias: {
            '@': path.resolve(__dirname, 'resources'),
            '@admin': path.resolve(__dirname, 'resources/admin'),
            '@public': path.resolve(__dirname, 'resources/public')
        }
    },
    optimizeDeps: {
        include: ['vue', 'element-plus']
    }
})