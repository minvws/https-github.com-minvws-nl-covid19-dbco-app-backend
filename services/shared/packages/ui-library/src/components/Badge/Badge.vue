<template>
    <div
        v-tw-merge
        :class="[
            [
                'tw-text-sm',
                'tw-py-1',
                'tw-px-2',
                'tw-gap-2',
                'tw-uppercase',
                'tw-font-sans',
                'tw-font-medium',
                'tw-leading-normal',
                'tw-tracking-normal',
                'tw-rounded',
                'tw-border',
                'tw-border-white',
                'tw-border-solid',
                'tw-inline-flex',
                'tw-shrink-0',
                'tw-items-center',
                'tw-justify-center',
                ...colorStyles[color],
            ],
        ]"
    >
        <Icon v-if="iconLeft" :name="iconLeft" aria-hidden="true" :class="iconStyle" />
        <slot />
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent, toRefs } from 'vue';
import type { Extends, ThemeColor } from '../../types';
import type { IconName } from '../Icon/icons';
import Icon from '../Icon/Icon.vue';

type Color = Extends<ThemeColor, 'gray' | 'violet' | 'blue' | 'green' | 'yellow' | 'red' | 'seaGreen'>;

const colorStyles: Record<Color, string[]> = {
    ['gray']: ['tw-bg-gray-100', 'tw-text-gray-800'],
    ['violet']: ['tw-bg-violet-100', 'tw-text-violet-800'],
    ['blue']: ['tw-bg-blue-100', 'tw-text-blue-800'],
    ['green']: ['tw-bg-green-100', 'tw-text-green-800'],
    ['yellow']: ['tw-bg-yellow-100', 'tw-text-yellow-800'],
    ['red']: ['tw-bg-red-100', 'tw-text-red-800'],
    ['seaGreen']: ['tw-bg-seaGreen-100', 'tw-text-seaGreen-800'],
};

const iconStyle = ['tw-inline-block tw-m-0 tw-w-[20px] tw-h-[20px] tw-shrink-0'];

export default defineComponent({
    props: {
        color: { type: String as PropType<Color>, default: 'gray' },
        iconLeft: { type: String as PropType<IconName> },
    },
    components: { Icon },
    setup(props) {
        let { iconLeft } = toRefs(props);

        return {
            colorStyles,
            iconLeft,
            iconStyle,
        };
    },
});
</script>
