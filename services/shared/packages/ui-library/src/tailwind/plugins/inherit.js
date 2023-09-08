const plugin = require('tailwindcss/plugin');

/**
 *  This plugin adds `inherit` options for tailwind
 *  Even though Tailwind contains a lot, things such as `initial` or `inherit` are still missing
 * @see: https://github.com/tailwindlabs/tailwindcss/discussions/1361
 */
function inherit({ addUtilities }) {
    addUtilities({
        '.inherit-font-family': {
            ['font-family']: 'inherit',
        },
        '.inherit-font-size': {
            ['font-size']: 'inherit',
        },
        '.inherit-font-weight': {
            ['font-weight']: 'inherit',
        },
        '.inherit-color': {
            ['color']: 'inherit',
        },
        '.inherit-text-align': {
            ['text-align']: 'inherit',
        },
        '.inherit-line-height': {
            ['line-height']: 'inherit',
        },
    });
}

module.exports = plugin(inherit);
