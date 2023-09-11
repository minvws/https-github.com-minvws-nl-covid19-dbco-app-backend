<template>
    <component v-tw-merge :is="IconComponent" class="tw-inline-block tw-shrink-0" />
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent, computed, toRef } from 'vue';

import type { IconName } from './icons';
import { iconMap } from './icons';

export default defineComponent({
    props: {
        name: { type: String as PropType<IconName>, required: true },
    },
    setup(props) {
        const name = toRef(props, 'name');
        const IconComponent = computed(() => iconMap[name.value]);
        return {
            IconComponent,
        };
    },
});
</script>
