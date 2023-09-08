<template>
    <BModal
        v-if="schema"
        ref="modalRef"
        @ok="onOk"
        :cancelDisabled="isLoading"
        :okDisabled="isLoading"
        :okTitle="selectedCaseUuid ? 'Opslaan' : 'Doorgaan'"
        :title="selectedCaseUuid ? 'Wijzigingen toepassen' : 'Case aanmaken'"
        centered
        noCloseOnBackdrop
        scrollable
        size="lg"
        visible
    >
        <div class="form-container">
            <template v-if="isIdentified">
                <FormInfo
                    class="my-3 info-block--lg"
                    text="Gegevens waarmee de index geïdentificeerd is kun je niet meer aanpassen"
                    infoType="success"
                />
                <div class="d-flex mb-3" v-if="index">
                    <div class="col">
                        <h4 class="mt-3">Persoonsgegevens</h4>
                        <p class="mb-0">{{ index.firstname }} {{ index.lastname }}</p>
                        <p class="mb-0" v-if="index.dateOfBirth">
                            {{ $filters.age(index.dateOfBirth) }} jaar ({{
                                $filters.dateFormatMonth(index.dateOfBirth)
                            }})
                        </p>
                        <p class="mb-0" v-if="index.bsnCensored">BSN {{ index.bsnCensored }}</p>
                    </div>
                    <div class="col" v-if="address">
                        <h4 class="mt-3">Adres</h4>
                        <p class="mb-0">
                            {{ address.street }}
                            {{ address.houseNumber }}
                            {{ address.houseNumberSuffix }}
                        </p>
                        <p class="mb-0">
                            {{ address.postalCode }}
                            {{ address.town }}
                        </p>
                    </div>
                </div>
            </template>

            <FormulateFormWrapper
                v-model="formValues"
                @delete="deleteCase"
                @blur="formErrors = {}"
                @input="formErrors = {}"
                @submit="onSubmit"
                :disabled="isLoading"
                :errors="formErrors"
                name="case-form"
                :schema="schema"
            />
        </div>

        <BsnModal
            v-if="bsnModelData"
            @hide="bsnModelData = null"
            @continue="(bsnInfo) => createOrUpdateCase(bsnInfo)"
            :data="bsnModelData"
        />
    </BModal>
</template>

<script lang="ts">
import { bsnApi, caseApi } from '@dbco/portal-api';
import type { BsnLookupResponse } from '@dbco/portal-api/bsn.dto';
import { useFormulate, useModal } from '@/components/AppHooks';
import FormInfo from '@/components/form/FormInfo/FormInfo.vue';
import BsnModal from '@/components/modals/BsnModal/BsnModal.vue';
import { usePlanner } from '@/store/planner/plannerStore';
import { flatten, unflatten } from '@/utils/object';
import type { AddressCommon } from '@dbco/schema/shared/address/addressCommon';
import type { AxiosError } from 'axios';
import type { BModal } from 'bootstrap-vue';
import type { PropType } from 'vue';
import { computed, defineComponent, ref } from 'vue';
import { createCase, updateCase } from '../ts/formRequest';
import { caseSchema } from '../ts/formSchema';
import type { Address, FormField } from '../ts/formTypes';
import { AutomaticAddressVerificationStatusV1 } from '@dbco/enum';
import type { PriorityV1 } from '@dbco/enum';
import type { CaseCreateUpdate } from '@dbco/portal-api/case.dto';
import type { PlannerCaseListItem } from '@dbco/portal-api/caseList.dto';

interface BsnModalData {
    caseUuid: string;
    firstname: string;
    lastname: string;
    dateOfBirth: string;
    address: AddressCommon;
    bsnInfo: BsnLookupResponse;
}

type FormValues = {
    'index.initials'?: string;
    'index.firstname'?: string;
    'index.lastname'?: string;
    'index.dateOfBirth'?: string;
    'index.bsn'?: string;
    'index.bsnCensored'?: string;
    'index.address'?: Partial<Address>;
    'contact.phone'?: string;
    'contact.email'?: string;
    'general.hpzoneNumber'?: string;
    'test.dateOfTest'?: string;
    'test.monsterNumber'?: string;

    caseLabels?: string[];
    notes?: string;
    priority?: PriorityV1;
    pseudoBsnGuid?: string;
    automaticAddressVerificationStatus?: AutomaticAddressVerificationStatusV1;
};

export default defineComponent({
    name: 'FormCase',
    components: { BsnModal, FormInfo },
    props: {
        list: {
            type: String,
            required: false,
        },
        selectedCase: {
            type: Object as PropType<PlannerCaseListItem>,
            default: () => ({}),
        },
    },

    setup(props, ctx) {
        const bsnModelData = ref<BsnModalData | null>(null);
        const isLoading = ref(false);
        const modalRef = ref<BModal>();
        const schema = ref<FormField[]>();

        const formErrors = ref({});
        const formValues = ref<FormValues>({});

        const isIdentified = computed(() => !!unflattenedFormValues.value.pseudoBsnGuid);
        const selectedCaseUuid = computed(() => props.selectedCase.uuid);
        const unflattenedFormValues = computed(() => unflatten(formValues.value));

        const caseApiData = computed(() => {
            const data: Partial<CaseCreateUpdate> = unflattenedFormValues.value;

            // If a list has been chosen, add it to the object
            if (props.list) data.assignedCaseListUuid = props.list;

            return data;
        });

        const deleteCase = () => {
            useModal().show({
                title: 'Weet je zeker dat je de case wilt verwijderen? Dat kan niet ongedaan worden gemaakt.',
                okTitle: 'Verwijderen',
                okVariant: 'outline-danger',
                onConfirm: async () => {
                    await caseApi.deleteCase(selectedCaseUuid.value);

                    // Wait for modal animation
                    setTimeout(() => {
                        ctx.emit('deleted', selectedCaseUuid.value);
                        // Reset form errors
                        formValues.value = {};
                    }, 200);
                },
            });
        };

        const getData = async () => {
            if (!selectedCaseUuid.value) return;

            isLoading.value = true;

            const {
                data: { caseLabels, ...rest },
            } = await caseApi.getPlannerCase(selectedCaseUuid.value);
            formValues.value = {
                ...flatten(rest),
                caseLabels: caseLabels.map((caseLabel) => caseLabel.uuid),
            };

            isLoading.value = false;
        };

        // Do not change this methods name/purpose, other components are calling it directly
        const open = async () => {
            await getData();

            const { caseLabels } = usePlanner();
            const addressVerified =
                unflattenedFormValues.value.automaticAddressVerificationStatus ===
                AutomaticAddressVerificationStatusV1.VALUE_verified;
            schema.value = caseSchema(
                props.selectedCase,
                !!unflattenedFormValues.value.pseudoBsnGuid,
                caseLabels,
                addressVerified
            );
            modalRef.value?.show();
        };

        const onOk = (event: Event) => {
            // Cancel hide to make sure we can do the async request first
            event.preventDefault();

            // Submit form for frontend validation
            useFormulate().submit('case-form');
        };

        const onSubmit = async () => {
            isLoading.value = true;
            // Reset form errors
            formErrors.value = {};

            const index = unflattenedFormValues.value.index;
            const address = index?.address;

            const bsn = index?.bsn;
            const dateOfBirth = index?.dateOfBirth;

            const postalCode = address?.postalCode;
            const houseNumber = address?.houseNumber;
            const houseNumberSuffix = address?.houseNumberSuffix;

            const isBsnDataComplete = bsn && dateOfBirth && postalCode && houseNumber;

            if (isBsnDataComplete) {
                try {
                    const bsnInfo = await bsnApi.bsnLookup({
                        dateOfBirth,
                        postalCode,
                        houseNumber,
                        houseNumberSuffix,
                        bsn,
                    });

                    bsnModelData.value = {
                        caseUuid: selectedCaseUuid.value,
                        dateOfBirth,
                        firstname: index?.firstname || '',
                        lastname: index?.lastname || '',
                        address: index?.address || {},
                        bsnInfo,
                    };
                } catch (error) {
                    const { response } = error as AxiosError<{ errors: Record<string, string[]> }>;
                    const bsnError = response?.data.errors.bsn;

                    if (!bsnError) throw error;

                    formErrors.value = {
                        'index.bsn': JSON.stringify({ warning: bsnError }),
                    };
                } finally {
                    isLoading.value = false;
                }

                return;
            }

            await createOrUpdateCase();
            isLoading.value = false;
        };

        const createOrUpdateCase = async (bsnInfo: BsnLookupResponse | null = null) => {
            const data = caseApiData.value;
            if (bsnInfo && 'guid' in bsnInfo) {
                data.pseudoBsnGuid = bsnInfo.guid;
            }

            isLoading.value = true;
            const {
                data: { uuid },
                errors,
            } = selectedCaseUuid.value ? await updateCase(selectedCaseUuid.value, data) : await createCase(data);
            formErrors.value = errors || {};
            isLoading.value = false;

            if (!uuid) return;

            modalRef.value?.hide();
            setTimeout(() => {
                // wait for modal animation
                ctx.emit('created', uuid);
                // clear form
                formValues.value = {};
            }, 200);
        };

        return {
            index: unflattenedFormValues.value.index,
            address: unflattenedFormValues.value.index?.address,

            bsnModelData,
            formErrors,
            formValues,
            isIdentified,
            isLoading,
            schema,
            selectedCaseUuid,

            createOrUpdateCase,
            deleteCase,
            onOk,
            onSubmit,

            // Must be shared with template to make ref work
            modalRef,

            // Must be shared with template for other components to call
            open,
        };
    },
});
</script>
