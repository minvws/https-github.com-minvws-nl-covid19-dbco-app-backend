<template>
    <FormulateInput v-model="context.model" v-bind="context.attributes" type="number" :disableErrors="true" />
</template>

<script>
export default {
    name: 'FormNumberInput',
    props: {
        context: {
            type: Object,
            required: true,
        },
    },
    watch: {
        'context.model': {
            handler(value) {
                const number = parseFloat(value);
                if (Number.isNaN(number) || value === number) {
                    return;
                }
                this.context.model = number;
            },
            immediate: true,
        },
    },
};
</script>
