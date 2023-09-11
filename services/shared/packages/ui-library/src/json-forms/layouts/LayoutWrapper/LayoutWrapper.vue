<template>
    <component :is="as" v-if="layout.visible">
        <slot />
    </component>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { LayoutBindings } from '../../types';

type Tag = 'div' | 'fieldset';

export default defineComponent({
    props: {
        as: { type: String as PropType<Tag>, default: 'div' },
        layout: { type: Object as PropType<Pick<LayoutBindings, 'visible'>>, required: true },
    },
    setup() {},
});
</script>
