<template>
    <tbody>
        <slot></slot>
    </tbody>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
export default defineComponent({});
</script>

<style lang="css" scoped>
tbody::v-deep > tr:last-of-type > td {
    @apply tw-border-b-0;
}
</style>
