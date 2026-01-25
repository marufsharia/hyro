import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import path from 'path'
import { fileURLToPath } from 'url'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/hyro.js',
                'resources/css/hyro.css',
            ],
            refresh: false,
        }),
    ],

    build: {
        outDir: 'public/build',
        emptyOutDir: true,
        manifest: true,
    },

    css: {
        postcss: path.resolve(__dirname, 'postcss.config.js'),
    },
})
