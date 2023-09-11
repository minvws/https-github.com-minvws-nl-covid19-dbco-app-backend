<template>
    <div
        :class="`formulate-input-element formulate-input-element--${context.type}`"
        :data-type="context.type"
        @change="$emit('change')"
        v-if="isCondition"
    >
        <slot />
    </div>
</template>

<script lang="ts">
import { formConditionMet } from '@/utils/form';
import { StoreType } from '@/store/storeType';
import type { FormCondition, FormConditionOperator } from '../ts/formTypes';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'FormSlot',
    props: {
        context: {
            type: Object,
            required: true,
        },
        conditions: {
            type: Array as PropType<FormCondition[]>,
            required: true,
        },
        store: {
            type: String as PropType<StoreType>,
            default: StoreType.INDEX,
        },
        conditionOperator: {
            type: String as PropType<FormConditionOperator>,
            default: 'AND',
        },
    },
    inject: {
        // Will be provided in case of repeatable groups
        getIndex: {
            default() {
                return () => undefined;
            },
        },
        rootModel: {
            default() {
                return () => undefined;
            },
        },
    },
    computed: {
        isCondition() {
            const checkCondition = (condition: FormCondition) => {
                return formConditionMet(
                    this.$store,
                    condition,
                    (this as any).rootModel(),
                    (this as any).getIndex(),
                    this.store
                );
            };

            if (this.conditionOperator === 'AND') {
                return this.conditions.every(checkCondition);
            }
            if (this.conditionOperator === 'OR') {
                return this.conditions.some(checkCondition);
            }
            throw Error(`no proper conditional operator (${this.conditionOperator})`);
        },
    },
});
</script>
