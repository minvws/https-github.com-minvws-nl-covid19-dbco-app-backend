<template>
    <Stack :as="as" direction="row" :class="[backgroundMap[variant], 'tw-py-4', 'tw-px-5']" spacing="2">
        <Icon :name="iconMap[variant]" :class="[iconColorMap[variant]]" :aria-label="iconLabelMap[variant]" />
        <Stack direction="column" class="tw-body-sm" spacing="2">
            <p class="tw-font-bold tw-my-0"><slot /></p>
            <p v-if="slots.additional" class="tw-my-0"><slot name="additional" /></p>
        </Stack>
    </Stack>
</template>

<script lang="ts">
import Icon from '../Icon/Icon.vue';
import Heading from '../Heading/Heading.vue';
import Stack from '../Stack/Stack.vue';
import type { IconName } from '../Icon/icons';
import type { PropType } from 'vue';
import { computed, defineComponent, useSlots } from 'vue';

const variants = ['info', 'success', 'warning', 'error'] as const;

type Tag = 'div' | 'section' | 'article';
type Variant = (typeof variants)[number];

const iconMap: Record<Variant, IconName> = {
    info: 'information-mark-circle',
    success: 'check-mark',
    warning: 'exclamation-mark-triangle-outline',
    error: 'exclamation-mark-circle',
};

const backgroundMap: Record<Variant, string> = {
    info: 'tw-bg-violet-100',
    success: 'tw-bg-green-100',
    warning: 'tw-bg-yellow-100',
    error: 'tw-bg-red-100',
};

const iconColorMap: Record<Variant, string> = {
    info: 'tw-text-violet-700',
    success: 'tw-text-green-700',
    warning: 'tw-text-yellow-700',
    error: 'tw-text-red-700',
};

const iconLabelMap: Record<Variant, string> = {
    info: 'Toelichting',
    success: 'Gelukt',
    warning: 'Waarschuwing',
    error: 'Fout',
};

export default defineComponent({
    props: {
        as: { type: String as PropType<Tag>, default: 'div' },
        title: { type: String },
        variant: { type: String as PropType<Variant> },
    },
    components: {
        Heading,
        Stack,
        Icon,
    },
    setup(props) {
        const variant = computed(() => {
            const { variant = 'info' } = props;
            if (variants.includes(variant as Variant)) {
                return variant as Variant;
            }
            console.warn(`Invalid variant '${variant}' supplied to Alert. Valid variants are: ${variants.join(', ')}.`);
            return 'info';
        });

        return {
            variant,
            iconMap,
            backgroundMap,
            iconColorMap,
            iconLabelMap,
            slots: useSlots(),
        };
    },
});
</script>
