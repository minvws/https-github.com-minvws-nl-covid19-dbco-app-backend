<template>
    <div>
        <CategorizationList @select="onSelect" :categorization="categorization" :selectedCategory="selectedCategory" />
        <SingleCategory :category="selectedCategory" :path="path" :schema="schema" />
    </div>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue';
import { rendererProps, useJsonFormsLayout } from '@jsonforms/vue2';
import type { Categorization, Category } from '@jsonforms/core';
import { isCategorization } from '@jsonforms/core';
import CategorizationList from './CategorizationList.vue';
import SingleCategory from './SingleCategory.vue';

export default defineComponent({
    name: 'Categorization',
    components: {
        CategorizationList,
        SingleCategory,
    },
    props: {
        ...rendererProps(),
    },
    setup(props: any) {
        const findCategory = (categorization: Categorization): Category => {
            const category = categorization.elements[0];

            if (isCategorization(category)) return findCategory(category);
            else return category;
        };

        const onSelect = (category: Category) => {
            selectedCategory.value = category;
        };

        const categorization = props.uischema as Categorization;
        const selectedCategory = ref(findCategory(categorization));

        return {
            ...useJsonFormsLayout(props),
            onSelect,
            selectedCategory,
            categorization,
        };
    },
});
</script>
