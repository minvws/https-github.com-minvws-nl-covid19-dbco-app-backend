const plugin = require('tailwindcss/plugin');

function textStyles({ addComponents, theme }) {
    const baseStyle = {
        'font-family': theme('fontFamily.sans'),
        'line-height': theme('lineHeight.normal'),
        'letter-spacing': theme('letterSpacing.normal'),
    };

    const bodySm = {
        ...baseStyle,
        'font-size': theme('fontSize.sm'),
    };

    const bodyMd = {
        ...baseStyle,
        'font-size': theme('fontSize.md'),
    };

    const bodyLg = {
        ...baseStyle,
        'font-size': theme('fontSize.lg'),
    };

    addComponents({
        '.body-sm': bodySm,
        '.body-md': bodyMd,
        '.body-lg': bodyLg,
    });
}

module.exports = plugin(textStyles);
