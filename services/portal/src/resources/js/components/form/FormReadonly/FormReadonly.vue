<template>
    <BInputGroup class="flex-nowrap">
        <BFormInput :value="value" readonly />
        <BInputGroupAppend is-text>
            <i class="icon icon--m0 icon--questionmark" v-b-tooltip.hover :title="tooltip" />
        </BInputGroupAppend>
    </BInputGroup>
</template>

<script>
export default {
    name: 'FormReadonly',
    props: {
        context: {
            type: Object,
            required: true,
        },
        tooltip: {
            type: String,
        },
        format: {
            type: String,
            required: false,
        },
    },
    computed: {
        value() {
            if (!this.format || !this.context.model) {
                return this.context.model;
            }
            switch (this.format) {
                case 'date':
                    return this.context.model.split('-').reverse().join('/');
                default:
                    return this.context.model;
            }
        },
    },
};
</script>

<style scoped></style>
