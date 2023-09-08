<template>
    <Container class="tw-p-0">
        <DiseaseTabs />

        <Card>
            <Heading class="tw-mb-3">Dossier</Heading>
            <Dossier debug />
        </Card>
    </Container>
</template>

<script lang="ts">
import { Card, Container, Heading } from '@dbco/ui-library';
import { defineComponent, ref } from 'vue';
import DiseaseTabs from './DiseaseTabs/DiseaseTabs.vue';
import Dossier from './Dossier/Dossier.vue';

export default defineComponent({
    components: {
        Card,
        Container,
        Dossier,
        Heading,
        DiseaseTabs,
    },
    setup() {
        const activeTab = ref(1);
        const selectedDiseaseId = ref(null);
        const selectedModelId = ref(null);

        return {
            activeTab,
            selectedDiseaseId,
            selectedModelId,
        };
    },
});
</script>
