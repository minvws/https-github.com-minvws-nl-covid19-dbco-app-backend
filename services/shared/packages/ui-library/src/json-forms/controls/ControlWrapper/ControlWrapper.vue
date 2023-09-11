<template>
    <component :is="as" v-if="control.visible">
        <slot />
    </component>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { ControlBindings } from '../../types';

type Tag = 'div' | 'fieldset';

export default defineComponent({
    props: {
        as: { type: String as PropType<Tag>, default: 'div' },
        control: { type: Object as PropType<ControlBindings>, required: true },
    },
    setup() {},
});
</script>
