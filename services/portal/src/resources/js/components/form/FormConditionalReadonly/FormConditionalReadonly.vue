<template>
    <FormulateInput
        v-bind="isCondition ? readonly : context.attributes"
        :type="isCondition ? 'formReadonly' : inputType"
        v-model="context.model"
        :readonly="isCondition"
        :disableErrors="true"
    />
</template>

<script>
import { formConditionMet } from '@/utils/form';
import { StoreType } from '@/store/storeType';

export default {
    name: 'FormConditionalReadonly',
    props: {
        context: {
            type: Object,
            required: true,
        },
        inputType: {
            type: String,
            required: true,
        },
        condition: {
            type: Object,
            required: true,
        },
        readonly: {
            type: Object,
            required: true,
        },
        store: {
            type: String,
            default: StoreType.INDEX,
        },
    },
    computed: {
        isCondition() {
            return formConditionMet(this.$store, this.condition);
        },
    },
};
</script>
