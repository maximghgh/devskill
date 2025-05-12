import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import path from "path";

export default defineConfig({
    plugins: [
        laravel({
            input: ["/resources/css/app.css", "resources/js/app.js"], // Добавлен CSS
            refresh: true,
        }),
        vue(),
    ],
    resolve: {
        alias: {
            vue: "vue/dist/vue.esm-bundler.js", // Исправляет проблему с Vue runtime
            "@": path.resolve(__dirname, "resources/"), // Добавлен alias для удобства
            "/img1/": path.resolve(__dirname, "public/img"),
        },
    },
});
