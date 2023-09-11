<template>
    <FormulateFormWrapper v-model="values" :schema="[schema]" />
</template>

<script>
import { YES, NO } from '../ts/formOptions';

export default {
    name: 'FormNumberBoolean',
    props: {
        context: {
            type: Object,
            required: true,
        },
        schema: {
            type: Object,
            required: true,
        },
        format: {
            type: String,
            required: false,
        },
    },
    inject: ['rootModel'],
    data() {
        let initialValue = this.rootModel()[this.context.name];

        if (this.format !== 'number' && initialValue !== '' && initialValue !== null) {
            initialValue = initialValue ? YES : NO;
        }

        return {
            // vue-formulate expects an object with string values for the radio type we use
            values: { [this.context.name]: `${initialValue}` },
        };
    },
    watch: {
        values(values) {
            const [value] = Object.values(values);

            if (this.format === 'number') {
                const number = parseInt(value);
                if (Number.isNaN(number)) this.context.model = null;
                else this.context.model = number;

                return;
            }

            this.context.model = value === 'null' ? null : !!parseInt(value);
        },
    },
};
</script>
