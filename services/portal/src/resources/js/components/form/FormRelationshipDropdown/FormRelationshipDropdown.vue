<template>
    <FormulateInput v-bind="attributes" type="select" />
</template>

<script>
import { contextRelationshipV1Options } from '@dbco/enum';

export default {
    name: 'FormRelationshipDropdown',
    props: {
        context: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            relationshipOptions: {},
        };
    },
    computed: {
        attributes() {
            return {
                ...this.context.attributes,
                options: contextRelationshipV1Options,
                value: this.context.model,
            };
        },
    },
};
</script>
