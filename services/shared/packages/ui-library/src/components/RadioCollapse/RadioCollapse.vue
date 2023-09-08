<template>
    <Card noPadding>
        <div
            :class="[
                'tw-p-6',
                'tw-flex',
                'tw-items-center',
                'tw-gap-4',
                isOpen && ['tw-border-gray-200', 'tw-border-0', 'tw-border-b-[1px]', 'tw-border-solid'],
            ]"
        >
            <p :class="['tw-font-medium', 'tw-flex-auto', 'tw-m-0']">{{ title }}</p>
            <RadioGroup
                :class="['tw-flex', 'tw-gap-2', 'tw-flex-auto', 'tw-max-w-md']"
                variant="button"
                :value="isOpen.toString()"
                :name="collapseId"
                v-on="$listeners"
            >
                <Radio class="tw-flex-auto" value="true" @click="toggle">{{ openButtonLabel }}</Radio>
                <Radio class="tw-flex-auto" value="false" @click="toggle">{{ closeButtonLabel }}</Radio>
            </RadioGroup>
        </div>
        <div ref="collapseRef" :id="collapseId">
            <slot />
        </div>
    </Card>
</template>

<script lang="ts">
import { uniqueId } from 'lodash';
import { Card, Radio, RadioGroup } from '..';
import { defineComponent, ref } from 'vue';
import { useCollapse } from '../../composables';

export default defineComponent({
    components: {
        Card,
        Radio,
        RadioGroup,
    },
    emits: {
        /* c8 ignore next */
        change: (event: ChangeEvent<HTMLInputElement>) => undefined, // eslint-disable-line @typescript-eslint/no-unused-vars
    },
    props: {
        initialIsOpen: {
            type: Boolean,
            default: false,
        },
        openButtonLabel: {
            type: String,
            default: 'Ja',
        },
        closeButtonLabel: {
            type: String,
            default: 'Nee',
        },
        title: {
            type: String,
            required: true,
        },
    },
    setup({ initialIsOpen }) {
        const collapseId = uniqueId('collapse-');
        const isOpen = ref(initialIsOpen);

        const toggle = () => (isOpen.value = !isOpen.value);

        const { collapseRef } = useCollapse({ isOpen });

        return { collapseId, collapseRef, isOpen, toggle };
    },
});
</script>
