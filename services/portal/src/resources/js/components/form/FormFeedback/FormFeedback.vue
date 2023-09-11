<template>
    <div class="feedback-message mb-1 col-12 px-0">
        <i v-if="isCondition" class="icon icon--m0" :class="conditionsMetIcon" />
        <i v-else class="icon icon--error icon--m0" />
        <span class="pl-1">{{ title }}</span>
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import { formConditionMet } from '@/utils/form';
import type { FormCondition } from '@/components/form/ts/formTypes';
import { StoreType } from '@/store/storeType';

export default defineComponent({
    name: 'FormFeedback',
    props: {
        title: {
            type: String,
            required: true,
        },
        conditions: {
            type: Array as PropType<FormCondition[]>,
            required: true,
        },
        conditionsMetIcon: {
            type: String,
            default: 'icon--success',
        },
        store: {
            type: String as PropType<StoreType>,
            default: StoreType.INDEX,
        },
    },
    computed: {
        isCondition() {
            const conditionsMet = this.conditions.every((condition) => formConditionMet(this.$store, condition));

            return conditionsMet ? true : false;
        },
    },
});
</script>

<style lang="scss" scoped>
.feedback-message {
    display: flex;
    align-items: center;
}
</style>
