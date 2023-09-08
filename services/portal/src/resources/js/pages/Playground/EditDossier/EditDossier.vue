<template>
    <div>
        <CovidCaseHeaderBar :covidCase="fakeIndexStoreState" :class="{ 'sidebar-collapsed': false }" />
        <Container class="tw-overflow-x-visible">
            <Dossier :dossierId="dossierId" :testFormId="testFormId" />
        </Container>
    </div>
</template>

<script lang="ts">
import type { IndexStoreState } from '@/store/index/indexStore';
import { Container } from '@dbco/ui-library';
import { defaultsDeep } from 'lodash';
import { defineComponent } from 'vue';
import CovidCaseHeaderBar from '../../../components/caseEditor/CovidCaseHeaderBar/CovidCaseHeaderBar.vue';
import Dossier from './Dossier/Dossier.vue';

import { fakeIndexStoreState } from './fake-index-store';

export default defineComponent({
    components: {
        Container,
        CovidCaseHeaderBar,
        Dossier,
    },
    props: {
        dossierId: {
            type: Number,
            required: true,
        },
        testFormId: {
            type: String,
            required: true,
        },
    },
    setup({ dossierId, testFormId }) {
        const indexStoreConfig: Partial<IndexStoreState> = {
            meta: {
                caseId: dossierId || testFormId,
            },
            fragments: {
                index: {
                    firstname: 'Jan',
                    lastname: 'de Vries',
                },
            },
        };

        return {
            dossierId,
            testFormId,
            fakeIndexStoreState: defaultsDeep(indexStoreConfig, fakeIndexStoreState),
        };
    },
});
</script>
