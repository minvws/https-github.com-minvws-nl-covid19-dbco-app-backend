// @ts-check

const { pxToRemConfig } = require('./utils');

const fontFamily = {
    sans: ['Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
    mono: ['Roboto Mono', 'Helvetica Neue', 'Arial', 'sans-serif'],
};

const fontSize = pxToRemConfig({
    sm: 14,
    md: 16,
    lg: 18,
    xl: 24,
    ['2xl']: 28,
    ['3xl']: 32,
    ['4xl']: 40,
});

const lineHeight = {
    none: 1,
    tight: 1.2,
    normal: 1.5,
};

const letterSpacing = {
    normal: '0.01em',
};

const fontWeight = {
    light: 300,
    normal: 400,
    medium: 500,
    bold: 700,
};

module.exports = {
    fontFamily,
    fontSize,
    fontWeight,
    lineHeight,
    letterSpacing,
};
