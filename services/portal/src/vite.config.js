import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue2';
import path from 'path';
import { vueSvg } from '@dbco/ui-library/vite-plugin-vue2-svg';

export const config = {
    plugins: [
        laravel(['resources/scss/app.scss', 'resources/js/app.js']),
        vue({
            template: {
                compilerOptions: { whitespace: 'preserve' },
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        vueSvg(),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm.js',
            '@': path.resolve('/resources/js'),
            '@images': path.resolve('/resources/img'),
            '@icons': path.resolve('/resources/svg'),
        },
        extensions: ['*', '.js', '.jsx', '.vue', '.ts', '.tsx', '.json'],
    },
};
export default defineConfig(config);
