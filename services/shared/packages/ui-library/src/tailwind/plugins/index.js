module.exports = [
    require('./inherit'),
    require('./body-text'),
    require('./focus'),
    require('./form'),
    require('./modifiers'),
    require('./reset'),
    // @see: https://github.com/tailwindlabs/tailwindcss-forms
    require('@tailwindcss/forms')({
        strategy: 'class',
    }),
];
