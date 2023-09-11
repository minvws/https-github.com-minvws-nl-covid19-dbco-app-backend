<template>
    <div class="tab-content">
        <DispatchRenderer
            v-for="(child, $index) in category.elements || []"
            :key="`${path}-${$index}`"
            :schema="schema"
            :uischema="child"
            :path="path"
        />
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { Category, JsonSchema } from '@jsonforms/core';
import { DispatchRenderer } from '@jsonforms/vue2';

export default defineComponent({
    name: 'SingleCategory',
    components: {
        DispatchRenderer,
    },
    props: {
        category: {
            type: Object as PropType<Category>,
            required: true,
        },
        path: {
            type: String,
            required: true,
        },
        schema: {
            type: Object as PropType<JsonSchema>,
            required: true,
        },
    },
    setup() {
        return {};
    },
});
</script>

<style lang="scss" scoped>
div {
    padding: 0.5rem;
}
</style>
