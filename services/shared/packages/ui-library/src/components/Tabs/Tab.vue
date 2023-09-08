<template>
    <button
        type="button"
        role="tab"
        @click="handleClick"
        :id="tabId"
        :aria-selected="isActive ? 'true' : 'false'"
        :aria-controls="tabPanelId"
        :class="[[...variantStyles[variant]], [isActive ? activeStyles[variant] : inactiveStyles[variant]]]"
    >
        <slot />
    </button>
</template>

<script lang="ts">
import { defineComponent, toRef } from 'vue';
import { injectTabsState } from './use-tabs-state';
import type { PropType } from 'vue';
import type { TabVariant } from './use-tabs-state';

const activeStyles: Record<TabVariant, string[]> = {
    pill: ['tw-z-10', 'after:tw-border-violet-700'],
    underline: ['tw-text-black', 'before:tw-h-[4px]', 'before:tw-bg-violet-800'],
};

const inactiveStyles: Record<TabVariant, (string | string[])[]> = {
    pill: ['after:tw-border-gray-200/30', ['hover:after:tw-border-violet-700', 'hover:tw-z-10']],
    underline: [
        'tw-text-gray-600',
        'hover:tw-text-black',
        'focus:before:tw-h-[4px]',
        'hover:before:tw-h-[4px]',
        'before:tw-bg-gray-200',
    ],
};

const variantStyles: Record<TabVariant, (string | string[])[]> = {
    pill: [
        'tw-border-none',
        'tw-relative',
        'tw-bg-white',
        'tw-text-violet-700',
        'tw-font-medium',
        'tw-py-[6px]',
        'tw-px-4',
        'first-of-type:tw-rounded-l',
        'last-of-type:tw-rounded-r',
        [
            `after:tw-content-['']`,
            'after:tw-absolute',
            'after:tw-border-solid',
            'after:tw-border-2',
            'after:tw-top-[-1px]',
            'after:tw-left-[-1px]',
            'after:tw-bottom-[-1px]',
            'after:tw-right-[-1px]',
            'first-of-type:after:tw-rounded-l',
            'last-of-type:after:tw-rounded-r',
        ],
    ],
    underline: [
        'tw-bg-transparent',
        'tw-border-none',
        'tw-text-sm',
        'tw-font-normal',
        'tw-pt-3',
        'tw-pb-4',
        'tw-px-2',
        'first-of-type:-tw-ml-2',
        'tw-relative',
        'tw-outline-none',
        'tw-z-10',
        'tw-cursor-pointer',
        `before:tw-content-['']`,
        'before:tw-absolute',
        'before:tw-block',
        'before:tw-bottom-0',
        'before:tw-left-2',
        'before:tw-right-2',
        `focus:after:tw-content-['']`,
        'focus:after:tw-absolute',
        'focus:after:tw-border-solid',
        'focus:after:tw-border-2',
        'focus:after:tw-border-violet-600',
        'focus:after:tw-top-0',
        'focus:after:tw-left-0',
        'focus:after:tw-bottom-[-4px]',
        'focus:after:tw-right-0',
        'focus:after:tw-rounded',
        'focus:after:tw-shadow-focus',
    ],
};

export default defineComponent({
    props: {
        isActive: {
            type: Boolean as PropType<boolean | null>,
            default: null,
        },
    },
    setup(props, { emit }) {
        const { isActive, eventBus, tabIndex, tabId, tabPanelId, variant } = injectTabsState({
            isActive: toRef(props, 'isActive'),
        });

        function handleClick(event: MouseEvent) {
            if (eventBus) {
                eventBus.$emit('tab-click', { tabIndex });
            }
            emit('click', event);
        }

        return { isActive, handleClick, tabId, tabPanelId, activeStyles, inactiveStyles, variant, variantStyles };
    },
});
</script>
