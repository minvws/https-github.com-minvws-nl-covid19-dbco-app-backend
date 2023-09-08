<template>
    <SpinnerSvg v-tw-merge class="tw-block tw-m-auto" :class="styles" role="progressbar" />
</template>

<script lang="ts">
import type { Extends, Sizes } from '../../types';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import SpinnerSvg from './spinner.svg?vue';

type Size = Extends<Sizes, 'sm' | 'md' | 'lg'>;

const Styles: Record<Size, string[]> = {
    sm: ['tw-w-4', 'tw-h-4'],
    md: ['tw-w-5', 'tw-h-5'],
    lg: ['tw-w-7', 'tw-h-7'],
};

export default defineComponent({
    props: {
        size: { type: String as PropType<Size>, default: 'sm' },
    },
    components: {
        SpinnerSvg,
    },
    setup({ size }) {
        return {
            styles: Styles[size],
        };
    },
});
</script>
