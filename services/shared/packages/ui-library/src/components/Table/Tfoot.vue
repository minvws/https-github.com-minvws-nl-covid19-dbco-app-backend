<template>
    <tfoot>
        <slot></slot>
    </tfoot>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
export default defineComponent({});
</script>

<style lang="css" scoped>
tfoot::v-deep > tr:first-of-type > th {
    @apply tw-border-t-[1px];
}
</style>
