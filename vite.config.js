
import {defineConfig} from 'vite'
// import {viteStaticCopy} from 'vite-plugin-static-copy'
import vue from '@vitejs/plugin-vue'
import react from '@vitejs/plugin-react'
import liveReload from 'vite-plugin-live-reload';
import path from "path";
import AutoImport from 'unplugin-auto-import/vite';
import fs from "fs";

const {ElementPlusResolver} = require("unplugin-vue-components/resolvers");
const Components = require("unplugin-vue-components/vite");
// https://vitejs.dev/config/

//Add All css and js here
//Important: Key must be output filepath without extension, and value will be the file source
const inputs = [
  "resources/admin/Boot.js",
  "resources/admin/main.js",
  "resources/admin/gutenblock.jsx",
  "resources/admin/ninja-table-tinymce-button.js",
  "resources/public/js/ninja-tables-footable.js",
  "resources/public/js/ninja-tables-builder.js",
  "resources/public/css/_public.scss",
  "resources/public/css/_table_builder.scss",
  "resources/admin/css/ninja-tables-admin.scss",
  "resources/admin/css/vendor.scss",
  "resources/admin/css/gutenblock.scss",
  "resources/preview/preview.scss",
];

const copyImagesPlugin = {
  name: "copy-images",
  writeBundle() {
    const imgSrc = path.resolve(__dirname, "resources/img");
    const imgDest = path.resolve(__dirname, "assets/img");

    // Create img directory if it doesn't exist
    if (!fs.existsSync(imgDest)) {
      fs.mkdirSync(imgDest, { recursive: true });
    }

    // Copy all files from resources/img to assets/img
    if (fs.existsSync(imgSrc)) {
      fs.readdirSync(imgSrc).forEach((file) => {
        const srcPath = path.join(imgSrc, file);
        const destPath = path.join(imgDest, file);
        fs.copyFileSync(srcPath, destPath);
      });
    }
  },
};

export default defineConfig({
    plugins:
        [
            vue(),
            react(),
            liveReload([
                `${__dirname}/**/*\.php`,
            ]),
            // viteStaticCopy({
            //     targets: [
            //         {src: 'resources/images', dest: ''},
            //         {src: 'resources/icons', dest: ''},
            //         {src: 'resources/libs', dest: ''},
            //     ]
            // }),
            AutoImport({
                resolvers: [ElementPlusResolver()],
            }),
            Components({
                resolvers: [ElementPlusResolver()],
                directives: false
            }),
            copyImagesPlugin
        ],

    build: {
        manifest: true,
        outDir: 'assets',
        //assetsDir: '',
        publicDir: 'assets',
        //root: '/',
        emptyOutDir: true, // delete the contents of the output directory before each build

        // https://rollupjs.org/guide/en/#big-list-of-options
        rollupOptions: {
            input: inputs,
            output: {
                chunkFileNames: '[name].js',
                entryFileNames: '[name].js',
            },
        },
    },

    resolve: {
      extensions: [".js", ".jsx", ".vue", ".json", ".scss"],
        alias: {
            'vue': 'vue/dist/vue.esm-bundler.js',
            '@': path.resolve(__dirname, 'resources/admin'),
        },
    },

    server: {
        port: 8880,
        strictPort: true,
        cors:{
            origin: '*',
        },
        hmr: {
            port: 8880,
            host: 'localhost',
            protocol: 'ws',
        }
    },
    esbuild: {
        loader: "jsx",
    }
})
