<template>
    <TabsContext>
        <TabList class="tw-bg-white tw-mb-5 tw-px-3">
            <Tab>Diseases</Tab>
            <Tab :disabled="!selectedDiseaseId">Schemas</Tab>
            <Tab :disabled="!selectedDiseaseId || !selectedModelId">UI Schemas</Tab>
        </TabList>

        <TabPanels>
            <TabPanel>
                <Heading class="tw-mb-3">Diseases</Heading>
                <Diseases @select="handleSelectDisease" />
            </TabPanel>

            <TabPanel>
                <Heading class="tw-mb-3">Schemas</Heading>
                <DiseaseSchemas
                    v-if="selectedDiseaseId"
                    @select="handleSelectDiseaseModel"
                    :diseaseId="selectedDiseaseId"
                />
            </TabPanel>

            <TabPanel>
                <Heading class="tw-mb-3">UI</Heading>
                <DiseaseUiSchemas v-if="selectedModelId" :diseaseModelId="selectedModelId" />
            </TabPanel>
        </TabPanels>
    </TabsContext>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue';
import {
    Button,
    Card,
    Container,
    Heading,
    Tab,
    TabsContext,
    TabList,
    TabPanels,
    TabPanel,
    HStack,
} from '@dbco/ui-library';
import Diseases from './Diseases/Diseases.vue';
import DiseaseSchemas from './DiseaseSchemas/DiseaseSchemas.vue';
import DiseaseUiSchemas from './DiseaseUiSchemas/DiseaseUiSchemas.vue';

export default defineComponent({
    name: 'PlaygroundPage',
    components: {
        Button,
        Card,
        Container,
        Diseases,
        DiseaseSchemas,
        DiseaseUiSchemas,
        Heading,
        Tab,
        TabsContext,
        TabList,
        TabPanels,
        TabPanel,
        HStack,
    },
    setup() {
        const selectedDiseaseId = ref<number | null>(null);
        const selectedModelId = ref<number | null>(null);

        function handleSelectDisease(id: number) {
            selectedModelId.value = null;
            selectedDiseaseId.value = id;
        }
        function handleSelectDiseaseModel(id: number) {
            selectedModelId.value = id;
        }

        return {
            selectedDiseaseId,
            selectedModelId,
            handleSelectDisease,
            handleSelectDiseaseModel,
        };
    },
});
</script>
