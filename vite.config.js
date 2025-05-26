import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import { resolve } from "path";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/sass/main.scss", "resources/js/app.js"],
            refresh: true,
        }),
        vue(),
    ],

    resolve: {
        alias: {
            "@": resolve(__dirname, "resources/js"),
            "~": resolve(__dirname, "resources"),
        },
    },

    build: {
        outDir: "public/build",
        manifest: true,
        rollupOptions: {
            input: {
                main: resolve(__dirname, "resources/js/app.js"),
            },
        },
    },

    server: {
        host: true,
        port: 5173,
        hmr: { host: "localhost" },
        cors: true,
    },

    css: {
        preprocessorOptions: {
            scss: {
                additionalData: `@import "sass/variables";`,
            },
        },
    },

    define: {
        __VUE_OPTIONS_API__: true,
        __VUE_PROD_DEVTOOLS__: false,
    },
});
