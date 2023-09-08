import path from 'path';
import vue from '@vitejs/plugin-vue2';

export default {
    plugins: [
        vue({
            template: {
                compilerOptions: { whitespace: 'preserve' },
            },
        }),
    ],
    resolve: {
        alias: {
            // Ensures the same Vue instance is used by different packages
            // @see: https://github.com/vuejs/vue-test-utils/issues/1982#issuecomment-1201131222
            vue: 'vue/dist/vue.runtime.mjs',
        },
    },
    test: {
        include: ['**/?*.spec.ts'],
        globals: true,
        environment: 'jsdom',
        setupFiles: ['./vitest/setup.ts'],
        alias: [
            {
                find: /.*\.svg\?vue$/,
                replacement: path.resolve(__dirname, '../__mocks__/Svg.vue'),
            },
            {
                find: /.*\.(svg|jpe?g|png)$/,
                replacement: path.resolve(__dirname, '../__mocks__/image.ts'),
            },
        ],
    },
};
