<template>
    <svg viewBox="0 0 100 20" xmlns="http://www.w3.org/2000/svg">
        <text>This is a mock svg component for icon {{ icon }}</text>
    </svg>
</template>

<script lang="ts">
/* c8 ignore start */
/* The dynamic import can not be tested via vitest.  */

import { defineComponent } from 'vue';

/**
 * @deprecated SvgVue is deprecated
 * This is a temporary component to provide backwards compatibilty that used to be provided by laravel `svg-vue`.
 * Since the direct importing of SVG as Vue components is introduced, this is now the preferred way of injecting SVG's.
 * @see: https://vue-svg-loader.js.org/
 */
export default defineComponent({
    name: 'SvgVue',
    props: {
        icon: {
            type: String,
            required: true,
        },
    },
    setup({ icon }) {
        return { icon };
    },
});
</script>
