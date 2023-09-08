<template>
    <div v-tw-merge :class="['tw-relative', 'tw-inline-block', 'tw-leading-[0]']">
        <slot
            :styles="[
                'tw-peer',
                'tw-z-10',
                'tw-w-full',
                'tw-pt-[10px]',
                'tw-pb-[11px]',
                '!tw-body-sm',
                'tw-text-gray-950',
                'tw-border',
                'tw-border-solid',
                'focus:tw-ring-transparent',
                'focus:tw-ring-offset-0',
                square ? '!tw-rounded-none' : round ? '!tw-rounded-full' : '!tw-rounded',
                invalid
                    ? ['!tw-border-red-600', '!tw-shadow-[0_0_0_1px_theme(colors.red.600)_inset]']
                    : 'tw-border-gray-500',
                useFocusOutline
                    ? ['focus:tw-border-gray-500', 'focus:tw-focus-outline']
                    : ['focus:!tw-border-violet-600', 'focus:!tw-shadow-[inset_0_0_0_1px_theme(colors.violet.600)]'],
                ['disabled:tw-cursor-not-allowed', 'disabled:tw-opacity-50'],
                [
                    'read-only:tw-cursor-not-allowed',
                    'read-only:tw-text-gray-600',
                    'read-only:tw-bg-gray-100',
                    '[&:not(:focus)]:read-only:tw-border-gray-300',
                ],
            ]"
        />
        <span
            :class="[
                useFocusOutline ? 'tw-hidden' : 'peer-focus:tw-shadow-focus',
                square ? '!tw-rounded-none' : round ? 'tw-rounded-full' : 'tw-rounded',
                'tw-block',
                'tw-absolute',
                'tw-top-0',
                'tw-left-0',
                'tw-bottom-0',
                'tw-right-0',
                'tw-z-0',
                'tw-pointer-events-none',
            ]"
            ><!-- focus glow --></span
        >
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
    props: {
        invalid: { type: Boolean, default: false },
        useFocusOutline: { type: Boolean, default: false },
        round: { type: Boolean, default: false },
        square: { type: Boolean, default: false },
    },
    setup() {
        return {};
    },
});
</script>
