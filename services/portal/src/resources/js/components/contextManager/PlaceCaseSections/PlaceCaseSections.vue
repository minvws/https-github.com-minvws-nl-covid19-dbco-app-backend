<template>
    <span v-if="sections.length > 3 && !showAll"
        >{{ formattedSections }}
        <BButton @click.stop="showAll = true" size="sm" variant="link" class="d-block p-0">{{
            t('components.placeCasesTable.hints.show_all', { count: sections.length })
        }}</BButton>
    </span>
    <span v-else>{{ formattedSections }}</span>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent, ref, computed, unref } from 'vue';
import { useI18n } from 'vue-i18n-composable';

export default defineComponent({
    props: {
        sections: { type: Array as PropType<Array<string>>, required: true },
    },
    setup({ sections }) {
        const { t } = useI18n();

        const sectionShowLimit = ref(3);
        const showAll = ref(false);
        const formattedSections = computed(() => {
            const visibleSections = unref(showAll) ? sections : sections.slice(0, unref(sectionShowLimit));
            return visibleSections?.length ? visibleSections.join(', ') : '-';
        });

        return {
            formattedSections,
            sectionShowLimit,
            showAll,
            t,
        };
    },
});
</script>
