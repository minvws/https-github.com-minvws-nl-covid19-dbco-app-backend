<template>
    <component :is="svgComponent" />
</template>

<script lang="ts">
/* c8 ignore start */
/* The dynamic import can not be tested via vitest.  */

import type { VueConstructor } from 'vue';
import type Vue from 'vue';
import { defineComponent, onBeforeMount, ref } from 'vue';

/**
 * @deprecated SvgVue is deprecated
 * This is a temporary component to provide backwards compatibilty that used to be provided by laravel `svg-vue`.
 * Since the direct importing of SVG as Vue components is introduced, this is now the preferred way of injecting SVG's.
 * @see: https://vue-svg-loader.js.org/
 */
export default defineComponent({
    props: {
        icon: {
            type: String,
            required: true,
        },
    },
    setup({ icon }) {
        const svgComponent = ref<string | VueConstructor<Vue>>('span');

        onBeforeMount(async () => {
            try {
                const svg = await import(`../../../../svg/${icon}.svg?vue`);
                svgComponent.value = svg.default;
            } catch (error) {
                console.error(error); // eslint-disable-line no-console
            }
        });

        return { svgComponent };
    },
});
</script>
