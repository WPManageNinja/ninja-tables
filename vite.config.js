import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import react from "@vitejs/plugin-react";
import path from "path";
import fs from "fs";
import AutoImport from "unplugin-auto-import/vite";
import liveReload from "vite-plugin-live-reload";
import Components from "unplugin-vue-components/vite";
import { ElementPlusResolver } from "unplugin-vue-components/resolvers";
// import { viteStaticCopy } from "vite-plugin-static-copy";

const serverConfig = require("./config/vite.json");

const input = [
  "resources/admin/Boot.js",
  "resources/admin/main.js",
  "resources/admin/gutenblock.js",
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
  plugins: [
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    react({
      // Enable JSX
      jsxRuntime: "automatic",
      babel: {
        plugins: ["@babel/plugin-transform-react-jsx"],
      },
    }),
    liveReload([`${__dirname}/**/*\.php`]),
    // viteStaticCopy({
    //   targets: [
    //     {
    //       src: "resources/img",
    //       dest: "",
    //     },
    //   ],
    // }),
    // AutoImport({
    //   resolvers: [],
    // }),
    // Components({
    //   resolvers: [],
    //   directives: false,
    // }),
    moveManifestPlugin,
    copyImagesPlugin
  ],
  base: "",
  build: {
    manifest: true,
    outDir: "assets",
    rollupOptions: {
      input,
      output: {
        entryFileNames: "js/[name].js",
        chunkFileNames: "js/[name]-[hash].js",
        assetFileNames: ({ name }) => {
          if (/\.(gif|jpe?g|png|svg)$/.test(name ?? "")) {
            return "img/[name][extname]";
          }
          if (/\.css$/.test(name ?? "")) {
            return "css/[name][extname]";
          }
          return "[name][extname]";
        },
      },
    },
  },
  server: {
    port: serverConfig.port,
    strictPort: serverConfig.strict_port,
    hmr: {
      port: serverConfig.port,
      host: serverConfig.host,
      protocol: serverConfig.vite_protocol,
    },
    cors: {
      origin: "*",
      methods: ["GET"],
      allowedHeaders: ["Content-Type", "Authorization"],
    },
  },
  resolve: {
    extensions: [".js", ".jsx", ".vue", ".json", ".scss"],
    alias: {
      "@": path.resolve(__dirname, "resources"),
      "@admin": path.resolve(__dirname, "resources/admin"),
      "@public": path.resolve(__dirname, "resources/public"),
      // 'vue': 'vue/dist/vue.esm-bundler.js',
    },
  },
  esbuild: {
    loader: "jsx",
    include: /src\/.*\.jsx?$/,
    exclude: [],
  },
  optimizeDeps: {
    esbuildOptions: {
      loader: {
        ".js": "jsx",
      },
    },
    include: ["vue", "element-plus"],
  },
});
