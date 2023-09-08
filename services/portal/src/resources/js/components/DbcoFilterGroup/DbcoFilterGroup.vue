<template>
    <div class="filter-wrapper">
        <slot></slot>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'DbcoFilterGroup',
    created() {
        this.$on('selected', (selectedOption: { value: string; type: string }) => {
            this.$emit('filter', selectedOption);
        });
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';
.filter-wrapper {
    display: flex;
    align-items: stretch;
    height: 2.5rem;
    background: $input-grey;
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
    border: 1px solid $lightest-grey;
}
</style>
