<template>
    <label :class="context.classes.label" :for="context.id">
        <span>{{ context.label }}</span>
        <i v-if="description" class="icon icon--m0 mt-0 icon--questionmark" v-b-tooltip.hover.html="description" />
    </label>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'FormLabel',
    props: {
        context: {
            type: Object as PropType<{
                classes: { label: string };
                label: string;
                id: string;
            }>,
            required: true,
        },
        description: {
            type: String,
            required: false,
        },
    },
});
</script>
