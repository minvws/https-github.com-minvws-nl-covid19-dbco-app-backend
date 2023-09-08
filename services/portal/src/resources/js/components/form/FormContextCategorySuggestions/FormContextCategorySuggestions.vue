<template>
    <BContainer>
        <BRow v-if="rowCount > 0">
            <!-- Do this twice -->
            <BCol md="6" v-for="(column, $columIndex) in 2" :key="$columIndex">
                <ul class="list pl-4">
                    <li
                        v-for="(suggestion, $index) in groupSuggestions.slice(
                            $columIndex * rowCount,
                            column * rowCount
                        )"
                        :key="$index"
                        class="mb-2"
                    >
                        {{ suggestion }}
                    </li>
                </ul>
            </BCol>
        </BRow>
    </BContainer>
</template>

<script lang="ts">
import type { ContextCategoryV1 } from '@dbco/enum';
import {
    contextCategoryV1Options,
    ContextCategorySuggestionGroupV1,
    contextCategorySuggestionGroupV1Options,
} from '@dbco/enum';
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'FormContextCategorySuggestions',
    props: {
        context: {
            type: Object,
            required: true,
        },
    },
    computed: {
        place() {
            return this.$store.getters['context/place'];
        },
        /**
         * Since we want to display 2 columns (with items in the correct order),
         * calculate the amount of resulting rows to help us render the two ul's
         */
        rowCount() {
            return Math.ceil(this.groupSuggestions.length / 2);
        },
        groupSuggestions() {
            // No place selected
            if (!this.place) return [];

            const category = this.place.category as ContextCategoryV1;

            const contextCategory = contextCategoryV1Options.find(
                (contextCategory) => contextCategory.value === category
            );

            const suggestionGroup =
                contextCategorySuggestionGroupV1Options.find(
                    (group) => group.value === contextCategory?.suggestionGroup
                ) ||
                // Default to the "overig" category
                contextCategorySuggestionGroupV1Options.find(
                    (group) => group.value === ContextCategorySuggestionGroupV1.VALUE_overig
                );

            // Finally, return the suggestions, when the suggestiongroup was found
            return suggestionGroup?.suggestions ?? [];
        },
    },
});
</script>
