<template>
    <component
        :is="as"
        :class="[
            'tw-bg-white',
            'tw-shadow-sm',
            'tw-mb-4',
            !noPadding && 'tw-p-6',
            'tw-border',
            'tw-rounded-sm',
            'tw-border-solid',
            'tw-border-gray-200',
        ]"
    >
        <slot name="header">
            <Heading as="h4" size="xs" class="tw-mb-2.5" v-if="title">{{ title }}</Heading>
        </slot>
        <slot name="default" />
    </component>
</template>
<script lang="ts">
import Heading from '../Heading/Heading.vue';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';

type Tag = 'div' | 'section' | 'article';
export default defineComponent({
    props: {
        as: { type: String as PropType<Tag>, default: 'div' },
        title: { type: String },
        noPadding: {
            type: Boolean,
            default: false,
        },
    },
    components: {
        Heading,
    },
});
</script>
