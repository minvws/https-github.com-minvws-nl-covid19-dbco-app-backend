<template>
    <BModal visible title="Controleer de status van de case" okOnly okTitle="Doorgaan" @hide="hide" @ok="confirm" />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { BvModalEvent } from 'bootstrap-vue';

export default defineComponent({
    name: 'OsirisReportSuccess',
    created() {
        this.$emit('loaded');
    },
    methods: {
        confirm() {
            this.$emit('confirm');
        },
        hide({ trigger }: BvModalEvent) {
            // Emit cancel on all possible ways to close the modal (only way to catch ESC), except confirming
            if (trigger === 'ok') return;

            this.$emit('cancel');
        },
    },
});
</script>

<style lang="scss" scoped></style>
