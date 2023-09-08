// @ts-check

const path = require('path');
const theme = require('./tailwind/theme');
const plugins = require('./tailwind/plugins');

const config = {
    prefix: 'tw-',
    content: [
        path.resolve(__dirname, './composables/**/*.(vue|ts)'),
        path.resolve(__dirname, './components/**/*.(vue|ts)'),
        path.resolve(__dirname, './directives/**/*.(vue|ts)'),
        path.resolve(__dirname, './json-forms/**/*.(vue|ts)'),
        path.resolve(__dirname, '../docs/**/*.(mdx|vue)'),
    ],
    corePlugins: {
        preflight: false,
    },
    plugins,
    theme,
};

module.exports = config;
