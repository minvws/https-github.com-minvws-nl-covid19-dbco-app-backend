<template>
    <div class="connected-cases-wrapper mt-3">
        <h3>Andere dossiers van deze persoon</h3>
        <BSpinner v-if="loading" small />
        <p v-if="!loading && connectedItems.length === 0" class="py-3 m-0" data-testid="no-cases-text">
            Er zijn in het BCO portaal geen andere dossiers gevonden van deze persoon
        </p>
        <div
            v-for="connectedItem in connectedItems"
            :key="connectedItem.uuid"
            class="row border-bottom"
            data-testid="connected-case"
        >
            <div class="col-3 py-3">
                <p class="font-weight-bold mb-0">Casenummer</p>
                <span class="font-weight-normal">
                    {{
                        connectedItem.organisation && !connectedItem.organisation.isCurrent
                            ? `${connectedItem.organisation.abbreviation}-${connectedItem.number}`
                            : connectedItem.number
                    }}
                </span>
            </div>
            <div class="col-3 py-3">
                <p class="font-weight-bold mb-0">Type</p>
                <span v-if="connectedItem.type === BsnLookupType.Index" class="font-weight-normal"> Index </span>
                <span
                    v-if="connectedItem.type === BsnLookupType.Task"
                    class="font-weight-normal"
                    data-testid="task-category-label"
                >
                    {{ $filters.categoryFormat(connectedItem.category) }}
                </span>
            </div>
            <div class="col-3 py-3">
                <p class="font-weight-bold mb-0" data-testid="relevant-date-label">
                    <template v-if="connectedItem.type === BsnLookupType.Index && connectedItem.hasSymptoms">
                        Eerste ziektedag
                    </template>
                    <template v-else-if="connectedItem.type === BsnLookupType.Index && !connectedItem.hasSymptoms">
                        Testdatum
                    </template>
                    <template v-else> Laatste contactdatum </template>
                </p>
                <span class="font-weight-normal" data-testid="relevant-date">
                    <template v-if="connectedItem.type === BsnLookupType.Index && connectedItem.hasSymptoms">
                        {{ $filters.dateFormatMonth(connectedItem.dateOfSymptomOnset) }}
                    </template>
                    <template v-else-if="connectedItem.type === BsnLookupType.Index && !connectedItem.hasSymptoms">
                        {{ $filters.dateFormatMonth(connectedItem.dateOfTest) }}
                    </template>
                    <template v-else-if="connectedItem.type === BsnLookupType.Task">
                        {{ $filters.dateFormatMonth(connectedItem.dateOfLastExposure) }}
                    </template>
                </span>
            </div>
            <div class="col-3 py-3" v-if="connectedItem.type === BsnLookupType.Task">
                <p class="font-weight-bold mb-0">Relatie tot de index</p>
                <span class="font-weight-normal">
                    <span v-if="connectedItem.relationship && relationshipV1Options[connectedItem.relationship]">
                        {{ relationshipV1Options[connectedItem.relationship] }}
                    </span>
                </span>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { BsnLookupType } from '@/components/form/ts/formTypes';
import { relationshipV1Options } from '@dbco/enum';
import type { RelationshipV1, YesNoUnknownV1 } from '@dbco/enum';
import { taskApi, caseApi } from '@dbco/portal-api';
import type { OrganisationCommonDTO } from '@dbco/schema/organisation/organisationCommon';
import type { PropType } from 'vue';
import { ref, onMounted } from 'vue';

interface ConnectedIndex {
    uuid: string;
    organisation: OrganisationCommonDTO & { isCurrent: boolean };
    number: string;
    dateOfSymptomOnset: string | null;
    hasSymptoms: YesNoUnknownV1;
    dateOfTest: string | null;
    type: BsnLookupType.Index; // Component specific propery for render purposes
}

interface ConnectedTask {
    uuid: string;
    organisation: OrganisationCommonDTO & { isCurrent: boolean };
    number: string;
    category: string | null;
    dateOfLastExposure: string | null;
    relationship: RelationshipV1 | null;
    type: BsnLookupType.Task; // Component specific propery for render purposes
}

type ConnectedItem = ConnectedIndex | ConnectedTask;

const props = defineProps({
    targetType: { type: String as PropType<BsnLookupType>, required: true },
    uuid: { type: String as PropType<string>, required: true },
});

const connectedItems = ref<ConnectedItem[]>([]);
const loading = ref(false);

onMounted(async () => {
    if (!props.uuid) return;

    loading.value = true;
    const fetchConnections = props.targetType === BsnLookupType.Task ? taskApi.getConnected : caseApi.getConnected;
    const data = await fetchConnections(props.uuid);
    data.cases.forEach((element: ConnectedIndex) => {
        // actually set the type property for display purposes, which is not in the BE response
        element.type = BsnLookupType.Index;
        connectedItems.value.push(element);
    });
    data.tasks.forEach((element: ConnectedTask) => {
        // actually set the type property for display purposes, which is not in the BE response
        element.type = BsnLookupType.Task;
        connectedItems.value.push(element);
    });
    loading.value = false;
});
</script>
