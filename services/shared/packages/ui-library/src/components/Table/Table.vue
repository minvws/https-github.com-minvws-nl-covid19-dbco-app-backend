<template>
    <table v-tw-merge class="tw-indent-0 tw-border-inherit tw-border-collapse tw-font-sans tw-font-medium tw-w-full">
        <slot></slot>
    </table>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
export default defineComponent({});
</script>

<style lang="css" scoped>
table {
    font-variant-numeric: lining-nums tabular-nums;
}
</style>
