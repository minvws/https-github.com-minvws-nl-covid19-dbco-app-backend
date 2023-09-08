import theme from '../../src/tailwind/theme/colors';

export const singleColors = Object.entries(theme.colors)
    .filter(([name, colors]) => typeof colors === 'string')
    .map(([name, color]) => [name, { [name]: color }]);

export const colorSwatches = Object.entries(theme.colors).filter(([name, colors]) => typeof colors !== 'string');
