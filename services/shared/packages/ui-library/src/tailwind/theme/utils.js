// @ts-check

const _ = require('lodash');

/**
 * Rems are relative to the font-size defined on the root element
 * @param {number} value
 * @returns {string} rem
 */
const pxToRem = (value, baseFontSize = 16) => `${_.round(value / baseFontSize, 3)}rem`;

/**
 * Converts all the number values into rem.
 * @template {Record<string, number>} T
 * @param {T} config
 * @returns {Record<keyof T, string>} config
 */
const pxToRemConfig = (config) => {
    // @ts-ignore
    return Object.entries(config).reduce((acc, [key, value]) => {
        // @ts-ignore
        acc[key] = pxToRem(value);
        return acc;
    }, {});
};

module.exports = { pxToRem, pxToRemConfig };
