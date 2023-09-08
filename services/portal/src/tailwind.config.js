// @ts-check

const path = require('path');
const tailwindConfig = require('@dbco/ui-library/tailwind.config.js');

const config = {
    ...tailwindConfig,
    content: [
        ...tailwindConfig.content,
        path.resolve(__dirname, './resources/js/**/*.vue'),
        path.resolve(__dirname, './resources/views/**/*.php'),
    ],
};

module.exports = config;
