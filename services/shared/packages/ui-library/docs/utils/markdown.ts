/**
 * Create comma-separated string of values wrapped in backticks
 */
const toCodeString = (values: string[]) => values.map((x) => `\`${x}\``).join(', ');

export const markdown = {
    toCodeString,
};
