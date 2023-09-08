<template>
    <div>{{ label }}</div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import { getDifferenceStringForTwoDates } from '@/utils/date';
import { getPath, unflatten } from '@/utils/object';
import type { VueFormulateContext } from '../ts/formTypes';

export default defineComponent({
    name: 'FormDateDifferenceLabel',
    props: {
        // Not used, but prevents that vue formulate passes the context to the first DOM element in the template
        context: {
            type: Object as PropType<VueFormulateContext>,
        },
        dateName: {
            type: String,
            required: true,
        },
        baseDateName: {
            type: String,
            required: false,
        },
        baseDateLabel: {
            type: String,
            required: false,
        },
    },
    inject: {
        // Will be provided in case of repeatable groups
        getIndex: {
            default() {
                return () => null;
            },
        },
        rootModel: {},
    },
    computed: {
        label() {
            const date = this.getDate(this.dateName);
            const baseDate = this.baseDateName ? this.getDate(this.baseDateName) : new Date();
            return getDifferenceStringForTwoDates(date, baseDate, this.baseDateLabel);
        },
    },
    methods: {
        getDate(name: string): Date | undefined {
            const { getIndex, rootModel } = this as any;
            // If the index is known due to a repeatable group and the selector includes >, replace it by the index
            if (name.includes('>') && !isNaN(getIndex())) name = name.replace('>', getIndex().toString());

            const value = getPath(name, unflatten(rootModel()));
            if (!value) return;

            return new Date(value);
        },
    },
});
</script>

<style></style>
