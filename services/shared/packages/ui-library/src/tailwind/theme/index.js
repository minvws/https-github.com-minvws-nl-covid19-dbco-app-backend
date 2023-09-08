// @ts-check

const { fontFamily, fontSize, fontWeight, lineHeight, letterSpacing } = require('./typography');
const { colors } = require('./colors');
const { spacing } = require('./spacing');

module.exports = {
    extend: {},

    colors,
    spacing,

    fontFamily,
    fontSize,
    fontWeight,
    lineHeight,
    letterSpacing,

    screens: {
        // matched to what is currently used in the variables.scss
        sm: '576px',
        md: '768px',
        lg: '992px',
        xl: '1200px',
        '2xl': '1400px',
    },

    borderRadius: {
        none: '0',
        sm: '4px',
        DEFAULT: '4px',
        md: '8px',
        lg: '16px',
        full: '9999px',
    },

    boxShadow: {
        none: '0',
        inner: 'inset 0 1px rgba(230, 230, 239, 1), inset 0 2px 4px rgba(0, 0, 0, 0.06)',
        sm: '0px 2px 4px rgba(0, 30, 73, 0.075)',
        DEFAULT: '0px 2px 4px rgba(0, 30, 73, 0.075)',
        md: '0px 8px 16px rgba(0, 30, 73, 0.15)',
        lg: '0px 16px 48px rgba(0, 30, 73, 0.175)',
        focus: '0px 0px 8px rgba(86,22,255,0.25)',
    },
};
