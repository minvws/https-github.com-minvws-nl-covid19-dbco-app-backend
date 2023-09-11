// @ts-check

/** @type {import('svgo').OptimizeOptions} */
const config = {
    plugins: [
        {
            name: 'preset-default',
            params: {
                overrides: {
                    removeViewBox: false,
                },
            },
        },
    ],
};

module.exports = config;
