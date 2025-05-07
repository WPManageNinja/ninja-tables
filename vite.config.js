
import {defineConfig} from 'vite'
import {viteStaticCopy} from 'vite-plugin-static-copy'
import vue from '@vitejs/plugin-vue'
import react from '@vitejs/plugin-react'
import liveReload from 'vite-plugin-live-reload';
import path from "path";
import AutoImport from 'unplugin-auto-import/vite';
import fs from "fs";

const serverConfig = require("./config/vite.json");

const {ElementPlusResolver} = require("unplugin-vue-components/resolvers");
const Components = require("unplugin-vue-components/vite");
// https://vitejs.dev/config/

const moveManifestPlugin = {
  name: "move-manifest",
  configResolved(resolvedConfig) {
    // Store the resolved config for use in writeBundle
    viteConfig = resolvedConfig;
  },
  writeBundle() {
    const outDir = viteConfig.build.outDir;
    const manifestSrc = path.join(outDir, ".vite", "manifest.json");
    const manifestDest = path.resolve(__dirname, serverConfig.manifest_path);
    const viteDir = path.join(outDir, ".vite");

    // Check if the manifest file exists, then move it
    if (fs.existsSync(manifestSrc)) {
      fs.renameSync(manifestSrc, manifestDest); // Move the file
      // Optionally, remove the .vite directory if it's empty
      if (fs.existsSync(viteDir) && fs.readdirSync(viteDir).length === 0) {
        fs.rmSync(viteDir, { recursive: true }); // Remove empty directory
      }
    }
  },
};

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
  base: '/wp-content/plugins/ninja-tables/',
    plugins:
        [
        
            vue(),
            react(),
            liveReload([
                `${__dirname}/**/*\.php`,
            ]),
            viteStaticCopy({
                targets: [
                    {src: 'resources/images', dest: ''},
                    {src: 'resources/icons', dest: ''},
                    {
                      src: 'resources/libs',
                      dest: '',
                      flatten: false
                    },
                    {src: 'resources/fonts', dest: ''},
                    {
                      src: 'resources/libs/icons/*',
                      dest: 'icons',
                    },
                  
                ]
            }),
            AutoImport({
                resolvers: [ElementPlusResolver()],
            }),
            Components({
                resolvers: [ElementPlusResolver()],
                directives: false
            }),
            moveManifestPlugin,
            copyImagesPlugin
        ],

    build: {
        manifest: true,
        outDir: 'assets',
        emptyOutDir: true, // delete the contents of the output directory before each build

        // https://rollupjs.org/guide/en/#big-list-of-options
        rollupOptions: {
            input: inputs,
            output: {
              entryFileNames: "js/[name].js",
              chunkFileNames: "js/[name]-[hash].js",
              // assetFileNames: ({ name }) => {
              //   if (/\.(gif|jpe?g|png|svg)$/.test(name ?? "")) {
              //     return "img/[name][extname]";
              //   }
              //   if (/\.css$/.test(name ?? "")) {
              //     return "css/[name][extname]";
              //   }
              //   return "[name][extname]";
              // },
            },
        },
    },

    resolve: {
      extensions: [".js", ".jsx", ".vue", ".json", ".scss"],
        alias: {
            'vue': 'vue/dist/vue.esm-bundler.js',
            '@': path.resolve(__dirname, 'resources/admin'),
            '@cssicon': path.resolve(__dirname, 'resources/libs/icons'),

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
