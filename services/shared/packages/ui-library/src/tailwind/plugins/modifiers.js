const plugin = require('tailwindcss/plugin');

function modifiers({ addVariant }) {
    /**
     * 2 reasons for overruling the `read-only` variant:
     *  - also apply to `aria-readonly` elements
     *  - don't apply to `disabled` elements (which the default `:read-only` pseudo-class does)
     *
     * 2 reasons for overruling the `enabled` variant:
     *  - also apply to `aria-disabled`
     *  - being able to use the `:enabled` modifier on non- button and input elements
     */
    addVariant('read-only', ['&[readonly]', '&[aria-readonly]']);
    addVariant('enabled', ['&:not([disabled]):not([aria-disabled])']);
}

module.exports = plugin(modifiers);
