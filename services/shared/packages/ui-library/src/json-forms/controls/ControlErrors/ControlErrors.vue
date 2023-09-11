<template>
    <FormErrors v-if="errors.length" :id="errorId" :messages="errors" />
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent, toRef } from 'vue';
import FormErrors from '../../../components/FormErrors/FormErrors.vue';
import type { ControlBindings } from '../../types';
import { useErrors, useErrorId } from '../../composition';

export default defineComponent({
    components: {
        FormErrors,
    },
    props: {
        control: { type: Object as PropType<ControlBindings>, required: true },
    },
    setup(props) {
        const control = toRef(props, 'control');

        return {
            errors: useErrors(control),
            errorId: useErrorId(control),
        };
    },
});
</script>
