<template>
    <TabsContext variant="pill">
        <TabList>
            <Tab>{{ $t(`components.covidCaseHistory.tabs.default`) }}</Tab>
            <Tab>{{ $t(`components.covidCaseHistory.tabs.osiris`) }}</Tab>
        </TabList>
        <TabPanels>
            <TabPanel><CovidCaseHistory :selectedCaseUuid="caseUuid" /> </TabPanel>
            <TabPanel><CovidCaseOsirisLog :caseUuid="caseUuid" :caseOsirisNumber="caseOsirisNumber" /> </TabPanel>
        </TabPanels>
    </TabsContext>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import { useI18n } from 'vue-i18n-composable';
import CovidCaseHistory from '@/components/utils/CovidCaseHistory/CovidCaseHistory.vue';
import CovidCaseOsirisLog from '@/components/utils/CovidCaseOsirisLog/CovidCaseOsirisLog.vue';
import { Tab, TabsContext, TabList, TabPanels, TabPanel } from '@dbco/ui-library';

export default defineComponent({
    props: {
        caseUuid: { type: String as PropType<string>, required: true },
        caseOsirisNumber: { type: Number as PropType<number | null | undefined> },
    },
    components: {
        CovidCaseHistory,
        CovidCaseOsirisLog,
        Tab,
        TabsContext,
        TabList,
        TabPanels,
        TabPanel,
    },
    setup() {
        const { t } = useI18n();
        return {
            t,
        };
    },
});
</script>
