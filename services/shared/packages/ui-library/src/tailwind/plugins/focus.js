const plugin = require('tailwindcss/plugin');

function focusStyles({ addUtilities, theme }) {
    addUtilities({
        '.focus-outline': {
            outline: 'solid',
            ['outline-width']: '2px',
            ['outline-offset']: '2px',
            ['outline-color']: theme('colors.violet.700'),
            ['border-radius']: theme('borderRadius.DEFAULT'),
        },
    });
}

module.exports = plugin(focusStyles);
