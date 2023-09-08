<template>
    <tr @click="handleClick">
        <slot />
    </tr>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
export default defineComponent({
    methods: {
        handleClick() {
            this.$emit('click');
        },
    },
});
</script>
