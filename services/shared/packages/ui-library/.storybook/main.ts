import type { StorybookConfig } from '@storybook/vue-vite';
import vue from '@vitejs/plugin-vue2';
import path from 'path';
import { UserConfig, buildErrorMessage, mergeConfig } from 'vite';
import commonjs from 'vite-plugin-commonjs';
import { vueSvg } from '../src/vite-plugin-vue2-svg';

const config: StorybookConfig = {
    stories: ['../**/*.stories.@(js|jsx|ts|tsx|mdx)'],
    addons: [
        '@storybook/addon-links',
        '@storybook/addon-essentials',
        '@storybook/addon-interactions',
        // 'storybook-addon-designs' is temporarily disabled awaiting `storybook` V7 support
        // @see: https://github.com/storybookjs/storybook/issues/20529
    ],
    framework: {
        name: '@storybook/vue-vite',
        options: {},
    },
    core: {
        disableTelemetry: true,
    },
    docs: {
        autodocs: true,
    },
    async viteFinal(config) {
        const overrideConfig: UserConfig = {
            css: {
                postcss: path.resolve(__dirname, '../src/postcss.config.js'),
            },
            plugins: [vue(), vueSvg(), commonjs()],
            build: {
                commonjsOptions: {
                    include: [/node_modules/, /tailwind/],
                },
            },
        };

        return mergeConfig(config, overrideConfig);
    },
};
export default config;
