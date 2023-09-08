const plugin = require('tailwindcss/plugin');

function resetStyles({ addComponents }) {
    const fieldset = {
        border: 'none',
        margin: 0,
        padding: 0,
    };

    addComponents({
        '.reset-fieldset': fieldset,
    });
}

module.exports = plugin(resetStyles);
