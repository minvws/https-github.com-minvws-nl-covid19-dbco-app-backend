<template>
    <div class="tw-relative tw-inline-block tw-z-[1020] tw-align-middle tw-leading-none">
        <button
            :class="[...styles, 'tw-bg-transparent', 'tw-border-0', 'tw-inline-flex', 'tw-text-inherit']"
            type="button"
            @mouseover="show = true"
            @focus="show = true"
            @mouseleave="show = false"
            @blur="show = false"
            :aria-describedby="tooltipId"
        >
            <Icon
                :class="['tw-m-0', ...styles]"
                :name="icon ? icon : 'question-mark-tooltip'"
                role="img"
                title="tooltip"
            />
        </button>
        <div
            :class="[
                show
                    ? [
                          'tw-bg-gray-900',
                          'tw-whitespace-nowrap',
                          'tw-shadow-lg',
                          'tw-normal-case',
                          'tw-font-normal',
                          'tw-text-white',
                          'tw-p-2',
                          'tw-rounded',
                          'tw-content-start',
                          'tw-absolute',
                          'tw-z-[1022]',
                          'tw-bottom-8',
                          'tw-leading-normal',
                          [
                              `before:tw-content-['_']`,
                              'before:tw-bg-gray-900',
                              'before:tw-absolute',
                              'before:tw-w-3',
                              'before:tw-h-3',
                              'before:tw-rotate-45',
                              'before:tw--bottom-1.5',
                          ],
                          positionStyles[position],
                      ]
                    : 'tw-sr-only',
            ]"
            role="tooltip"
            :id="tooltipId"
        >
            <slot>
                {{ content }}
            </slot>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent, ref, toRef } from 'vue';
import type { Extends, Sizes } from '../../types';
import type { PropType } from 'vue';
import type { IconName } from '../Icon/icons';
import Icon from '../Icon/Icon.vue';
import { uniqueId } from 'lodash';
import type { Position } from './TooltipButton';

const positionStyles: Record<Position, string[]> = {
    top: ['before:tw-left-[calc(50%-12px/2)]', 'tw-translate-x-[-50%]', 'tw-left-2/4'],
    right: ['before:tw-left-[12px]', 'tw-translate-x-[-10px]', 'tw-left-0'],
    left: ['before:tw-right-[12px]', 'tw-translate-x-[10px]', 'tw-right-0'],
};

type Size = Extends<Sizes, 'sm' | 'md' | 'lg'>;

const Styles: Record<Size, string[]> = {
    sm: ['tw-w-[16px]', 'tw-h-[16px]'],
    md: ['tw-w-[20px]', 'tw-h-[20px]'],
    lg: ['tw-w-[24px]', 'tw-h-[24px]'],
};

export default defineComponent({
    props: {
        content: { type: String },
        icon: { type: String as PropType<IconName> },
        position: { type: String as PropType<Position>, default: 'top' },
        size: { type: String as PropType<Size>, default: 'sm' },
    },
    components: { Icon },
    setup(props) {
        const icon = toRef(props, 'icon');
        const show = ref(false);
        const tooltipId = uniqueId('tooltip-button-');

        return {
            positionStyles,
            show,
            icon,
            tooltipId,
            styles: Styles[props.size],
        };
    },
});
</script>
