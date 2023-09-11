<template>
    <div>
        <div class="tw-bg-white tw-py-3.5 tw-border-b tw-border-black">
            <Container>
                <router-link to="/beheren/beleidsversies" custom v-slot="{ href, navigate }">
                    <Link :href="href" @click="navigate" iconLeft="chevron-left">Terug naar beleidsversies</Link>
                </router-link>
            </Container>
        </div>
        <div class="tw-pt-12">
            <Container>
                <div v-if="policyVersion.uuid.length">
                    <div class="tw-flex tw-justify-between">
                        <Heading class="tw-flex tw-items-center tw-justify-left" as="h1" size="md">
                            Beleid / {{ policyVersion.name }}
                            <PolicyVersionStatusBadge :status="policyVersion.status" class="tw-ml-4" />
                        </Heading>
                        <div class="policy-guideline-detail-action tw-flex tw-gap-5 tw-items-baseline">
                            <LastUpdated />
                            <div
                                data-testid="activation-wrapper"
                                v-if="policyVersion.status === PolicyVersionStatusV1.VALUE_draft"
                            >
                                <Button @click="openActivationModal" :disabled="!!updateErrors.startDate">{{
                                    activationButtonText
                                }}</Button>
                                <SaveModal
                                    :isOpen="activationModalIsOpen"
                                    :title="activationModalText"
                                    @close="closeActivationModal"
                                    okLabel="Zet klaar"
                                    @ok="
                                        updateDetails(policyVersion.uuid, {
                                            status: policyStartsToday
                                                ? PolicyVersionStatusV1.VALUE_active
                                                : PolicyVersionStatusV1.VALUE_active_soon,
                                        })
                                    "
                                    :okDisabled="!isPolicyReviewed"
                                >
                                    <Checkbox
                                        class="tw-py-4"
                                        :checked="isPolicyReviewed"
                                        @change="isPolicyReviewed = !isPolicyReviewed"
                                        >Een collega heeft dit beleid gecheckt.</Checkbox
                                    >
                                </SaveModal>
                            </div>
                        </div>
                    </div>
                    <Heading as="h2" size="sm" class="tw-mt-8 tw-pb-6">Over het beleid</Heading>
                    <Card>
                        <FormulateForm
                            v-if="versionForm.uuid.length"
                            v-model="versionForm"
                            class="form-container tw-w-7/12"
                            :errors="updateErrors"
                        >
                            <HStack>
                                <FormulateInput
                                    class="w100"
                                    type="text"
                                    name="name"
                                    label="Versienaam"
                                    @change="handleInput({ name: versionForm.name })"
                                    :disabled="disabled"
                                />
                                <FormulateInput
                                    class="w100"
                                    type="date"
                                    name="startDate"
                                    label="Ingangsdatum"
                                    :min="$filters.dateFnsFormat(new Date(), 'yyyy-MM-dd')"
                                    @change="handleInput({ startDate: versionForm.startDate })"
                                    :disabled="disabled"
                                />
                            </HStack>
                            <SaveModal
                                cancelIsPrimary
                                okLabel="Terugzetten"
                                title="Status terugzetten naar concept om je wijzigingen op te slaan?"
                                :isOpen="updateModalIsOpen"
                                @close="closeUpdateModal"
                                @ok="
                                    updateDetails(policyVersion.uuid, {
                                        ...pendingUpdate,
                                        ...{ status: PolicyVersionStatusV1.VALUE_draft },
                                    })
                                "
                            >
                                <p class="tw-mb-0">
                                    Je moet dit beleid eerst terugzetten naar concept om wijzigingen op te slaan. Let op
                                    dat het beleid dan niet meer actief wordt op de ingangsdatum, tenzij je het opnieuw
                                    klaarzet.
                                </p>
                            </SaveModal>
                        </FormulateForm>
                        <Spinner v-else size="lg" />
                    </Card>
                    <Heading as="h2" size="sm" class="tw-mt-8 tw-pb-6">Kalenders</Heading>
                    <Card>
                        <Heading as="h3" size="sm" class="tw-pl-6 tw-py-3">Kalender items</Heading>
                        <CalendarItemTable
                            :versionStatus="policyVersion.status"
                            :versionUuid="policyVersion.uuid"
                            :disabled="disabled"
                            @status-changed="updateVersion"
                        />
                    </Card>
                    <Card>
                        <Heading as="h3" size="sm" class="tw-pl-6 tw-py-3">Kalender views</Heading>
                        <CalendarViewTable :versionUuid="policyVersion.uuid" />
                    </Card>
                    <Heading as="h2" size="sm" class="tw-mt-8 tw-pb-6">Risicoprofielen en richtlijnen</Heading>
                    <TabsContext :index="currentTabIndex">
                        <TabList>
                            <RouterTab :to="`/beheren/beleidsversies/${policyVersion.uuid}`" @click="switchTab(0)">
                                Index
                            </RouterTab>
                            <RouterTab
                                :to="`/beheren/beleidsversies/${policyVersion.uuid}#contact`"
                                @click="switchTab(1)"
                            >
                                Contacten
                            </RouterTab>
                        </TabList>
                        <TabPanels>
                            <TabPanel>
                                <Heading as="h3" size="sm" class="tw-mt-8 tw-pb-6">Index</Heading>
                                <Card>
                                    <div class="tw-mb-8">
                                        <Heading as="h4" size="sm" class="tw-pl-6 tw-py-3">Risicoprofielen</Heading>
                                        <RiskProfileTable
                                            v-if="indexRiskProfiles && guidelines"
                                            :disabled="disabled"
                                            :riskProfiles="indexRiskProfiles"
                                            :versionStatus="policyVersion.status"
                                            :guidelines="guidelines"
                                            @status-changed="updateVersion"
                                            @reset="loadRiskProfiles"
                                        />
                                        <Spinner v-else size="lg" />
                                    </div>
                                    <div class="tw-mb-8">
                                        <Heading as="h4" size="sm" class="tw-pl-6 tw-py-3">Richtlijnen</Heading>
                                        <PolicyGuidelineTable v-if="guidelines" :guidelines="guidelines" />
                                        <Spinner v-else size="lg" />
                                    </div>
                                </Card>
                            </TabPanel>
                            <TabPanel>
                                <Heading as="h3" size="sm" class="tw-mt-8 tw-pb-6">Contacten</Heading>
                                <Card>
                                    <div class="tw-mb-8">
                                        <Heading as="h4" size="sm" class="tw-pl-6 tw-py-3">Risicoprofielen</Heading>
                                        <RiskProfileTable
                                            v-if="contactRiskProfiles && guidelines"
                                            :disabled="disabled"
                                            :riskProfiles="contactRiskProfiles"
                                            :versionStatus="policyVersion.status"
                                            :guidelines="guidelines"
                                            @status-changed="updateVersion"
                                            @reset="loadRiskProfiles"
                                        />
                                        <Spinner v-else size="lg" />
                                    </div>
                                </Card>
                            </TabPanel>
                        </TabPanels>
                    </TabsContext>
                </div>
                <Spinner v-else size="lg" />
            </Container>
        </div>
    </div>
</template>

<script lang="ts" setup>
import {
    Button,
    Card,
    Checkbox,
    Container,
    Heading,
    HStack,
    Link,
    RouterTab,
    SaveModal,
    Spinner,
    TabsContext,
    TabList,
    TabPanels,
    TabPanel,
    useOpenState,
} from '@dbco/ui-library';
import { adminApi } from '@dbco/portal-api';
import { computed, onMounted, ref } from 'vue';
import { dateFnsFormat } from '@/filters/date/date';
import { isToday } from 'date-fns';
import { PolicyPersonTypeV1, PolicyVersionStatusV1 } from '@dbco/enum';
import { useRoute } from '@/router/router';
import CalendarItemTable from '@/components/admin/PolicyAdvice/CalendarItemTable/CalendarItemTable.vue';
import CalendarViewTable from '@/components/admin/PolicyAdvice/CalendarViewTable/CalendarViewTable.vue';
import LastUpdated from '@/components/caseEditor/LastUpdated/LastUpdated.vue';
import PolicyGuidelineTable from '@/components/admin/PolicyAdvice/PolicyGuidelineTable/PolicyGuidelineTable.vue';
import PolicyVersionStatusBadge from '@/components/admin/PolicyAdvice/PolicyVersionStatusBadge/PolicyVersionStatusBadge.vue';
import RiskProfileTable from '@/components/admin/PolicyAdvice/RiskProfileTable/RiskProfileTable.vue';
import showToast from '@/utils/showToast';
import useStatusAction from '@/store/useStatusAction';
import type { PolicyVersion, RiskProfile } from '@dbco/portal-api/admin.dto';
import type { AxiosError } from 'axios';

const initialPolicyVersion: PolicyVersion = {
    uuid: '',
    name: '',
    startDate: '',
    status: PolicyVersionStatusV1.VALUE_draft,
};

const currentTabIndex = ref(0);
const policyVersion = ref<PolicyVersion>(initialPolicyVersion);
const versionForm = ref<PolicyVersion>(initialPolicyVersion);
const updateErrors = ref<AnyObject>({});
const { action: loadGuidelines, result: guidelines } = useStatusAction(adminApi.getPolicyGuidelines);

const disabled = computed(
    () =>
        policyVersion.value.status === PolicyVersionStatusV1.VALUE_active ||
        policyVersion.value.status === PolicyVersionStatusV1.VALUE_old
);

const {
    isOpen: activationModalIsOpen,
    close: closeActivationModal,
    open: openActivationModal,
} = useOpenState({ onClose: () => (isPolicyReviewed.value = false) });
const {
    isOpen: updateModalIsOpen,
    close: closeUpdateModal,
    open: openUpdateModal,
} = useOpenState({
    onClose: () => setVersionForm(),
});
const isPolicyReviewed = ref(false);

const policyStartsToday = computed(() => {
    return isToday(new Date(policyVersion.value.startDate));
});

const activationButtonText = computed(() => {
    return policyStartsToday.value ? 'Concept activeren' : 'Concept klaarzetten';
});

const activationModalText = computed(() => {
    return `Wil je klaarzetten voor activatie? Dit beleid wordt automatisch actief op de ingangsdatum: ${dateFnsFormat(
        policyVersion.value.startDate,
        'dd-MM-yyy'
    )}.`;
});

onMounted(async () => {
    const router = useRoute();
    const { versionUuid } = router.params;
    if (versionUuid) {
        policyVersion.value = await adminApi.getPolicyVersion(versionUuid);
        setVersionForm();
        if (policyVersion.value.status === PolicyVersionStatusV1.VALUE_draft) {
            void updateDetails(versionUuid, policyVersion.value);
        }

        void loadRiskProfiles();

        if (router.hash === '#contact') {
            switchTab(1);
        }
    }
});

function updateVersion(updatedVersion: PolicyVersion) {
    policyVersion.value = updatedVersion;
    setVersionForm();
}

const pendingUpdate = ref<Partial<PolicyVersion>>(versionForm.value);

function handleInput(value: Partial<PolicyVersion>) {
    if (policyVersion.value.status !== PolicyVersionStatusV1.VALUE_active_soon) {
        return updateDetails(policyVersion.value.uuid, value);
    }
    pendingUpdate.value = value;
    openUpdateModal();
}

function setVersionForm() {
    versionForm.value = {
        ...policyVersion.value,
        ...{
            startDate: dateFnsFormat(policyVersion.value.startDate, 'yyyy-MM-dd') as string,
        },
    };
    updateErrors.value = {};
}

const contactRiskProfiles = ref<RiskProfile[]>([]);
const indexRiskProfiles = ref<RiskProfile[]>([]);

async function loadRiskProfiles() {
    void loadGuidelines(policyVersion.value.uuid);
    const contactProfiles = await adminApi.getRiskProfiles(policyVersion.value.uuid, PolicyPersonTypeV1.VALUE_contact);
    if (contactProfiles) contactRiskProfiles.value = contactProfiles;
    const indexProfiles = await adminApi.getRiskProfiles(policyVersion.value.uuid, PolicyPersonTypeV1.VALUE_index);
    if (indexProfiles) indexRiskProfiles.value = indexProfiles;
}

async function updateDetails(versionUuid: string, details: Partial<PolicyVersion>) {
    const payload = { ...details };

    if (payload.startDate) {
        payload.startDate = new Date(payload.startDate);
    }

    try {
        const updatedVersion = await adminApi.updatePolicyVersion(versionUuid, payload);
        updateErrors.value = {};
        policyVersion.value = updatedVersion;
    } catch (error) {
        const { response } = error as AxiosError<{ errors: Record<string, string[]> }>;
        if (response?.data.errors) {
            const { name, startDate } = response.data.errors;
            if (name) updateErrors.value = { name: JSON.stringify({ warning: name }) };
            if (startDate) updateErrors.value = { startDate: JSON.stringify({ warning: startDate }) };
        } else {
            handleUpdateFailure();
        }
    } finally {
        if (activationModalIsOpen.value) closeActivationModal();
        if (updateModalIsOpen.value) closeUpdateModal();
    }
}

function switchTab(to: number) {
    currentTabIndex.value = to;
}

function handleUpdateFailure() {
    return showToast(`Er ging iets mis bij het opslaan. probeer het opnieuw.`, 'policy-version-update-toast', true);
}
</script>
