const plugin = require('tailwindcss/plugin');

function form({ addUtilities }) {
    addUtilities({
        '.hidden-input': {
            position: 'fixed',
            opacity: 0,
            ['pointer-events']: 'none',
        },
    });
}

module.exports = plugin(form);
