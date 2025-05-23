import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import { resolve } from "path";

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [vue()],

    resolve: {
        alias: {
            "@": resolve(__dirname, "resources/js"),
            "~": resolve(__dirname, "resources"),
        },
    },

    build: {
        outDir: "public/build",
        rollupOptions: {
            input: {
                main: resolve(__dirname, "resources/js/app.js"),
            },
        },
        manifest: true,
    },

    server: {
        host: true,
        port: 5173,
        hmr: {
            host: "localhost",
        },
        cors: true,
    },

    css: {
        preprocessorOptions: {
            scss: {
                additionalData: `@import "@/assets/scss/variables.scss";`,
            },
        },
    },

    define: {
        __VUE_OPTIONS_API__: true,
        __VUE_PROD_DEVTOOLS__: false,
    },
});
