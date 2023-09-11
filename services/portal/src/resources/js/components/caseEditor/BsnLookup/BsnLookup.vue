<template>
    <div>
        <BsnPersonalDetails v-bind="personalDetailsInfo" @edit="editing = true" />

        <div v-if="personalDetails && personalDetails.bsnCensored && !editing" data-testid="duplicate-cases">
            <FormInfo class="info-block--lg" :text="dataCheckedAlertLabel" infoType="success" />
            <BsnConnectedItems v-if="uuid && userCanEdit" :targetType="targetType" :uuid="uuid" />
        </div>

        <div v-else>
            <template v-if="editing">
                <span class="font-weight-bold mb-1">Dit is waarom je identificeert:</span>
                <ul class="list mt-2 mb-3">
                    <li>Zo weet je zeker dat je met de juiste persoon spreekt</li>
                    <li>Zo voorkom je dubbele dossiers</li>
                </ul>
                <FormulateFormWrapper
                    v-model="flattenedFragments"
                    @input="change"
                    class="row mt-4"
                    :errors="formErrors"
                    :schema="lookupFormSchema"
                />
                <FormInfo
                    v-if="error"
                    :text="error"
                    class="mb-3 info-block--lg"
                    infoType="warning"
                    data-testid="form-error"
                />

                <slot />

                <div v-if="userCanEdit && !hasNoBsnOrAddressChecked()" class="mt-3 mb-2">
                    <BButton class="mr-2 w-auto" variant="primary" data-testid="lookup-button" @click="lookup">
                        {{ error ? 'Opnieuw controleren' : 'Controleren' }}
                    </BButton>
                    <BButton v-if="error" variant="outline-primary" class="w-auto" @click="onSubmit">
                        Toch doorgaan
                    </BButton>
                </div>
            </template>

            <template v-if="!editing">
                <FormInfo
                    class="info-block--lg"
                    infoType="warning"
                    text="Het is niet gelukt deze persoon te identificeren."
                />
                <BButton
                    @click="editing = true"
                    class="mt-3 w-auto"
                    data-testid="identify-button"
                    :disabled="disabled"
                    variant="outline-primary"
                >
                    {{ bsnLookupAttempted ? 'Opnieuw identificeren' : 'Identificeren' }}
                </BButton>
            </template>
        </div>
    </div>
</template>

<script lang="ts">
import BsnConnectedItems from '@/components/caseEditor/BsnConnectedItems/BsnConnectedItems.vue';
import BsnPersonalDetails from '@/components/caseEditor/BsnPersonalDetails/BsnPersonalDetails.vue';
import FormInfo from '@/components/form/FormInfo/FormInfo.vue';
import { bsnLookupSchemaContact, bsnLookupSchemaIndex } from '@/components/form/ts/formSchema';
import { BsnLookupType } from '@/components/form/ts/formTypes';
import { SharedActions } from '@/store/actions';
import type { IndexStoreState } from '@/store/index/indexStore';
import { StoreType } from '@/store/storeType';
import type { TaskStoreState } from '@/store/task/taskStore';
import { userCanEdit } from '@/utils/interfaceState';
import { flatten, unflatten } from '@/utils/object';
import type { SafeHtml } from '@/utils/safeHtml';
import { generateSafeHtml } from '@/utils/safeHtml';
import { AutomaticAddressVerificationStatusV1 } from '@dbco/enum';
import { bsnApi } from '@dbco/portal-api';
import { BsnLookupError } from '@dbco/portal-api/bsn.dto';
import type { AxiosError } from 'axios';
import { debounce } from 'lodash';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';

type Fragment = TaskStoreState['fragments'] | IndexStoreState['fragments'];

export default defineComponent({
    name: 'BsnLookup',
    components: { BsnPersonalDetails, BsnConnectedItems, FormInfo },
    props: {
        targetType: { type: String as PropType<BsnLookupType>, required: true },
        disabled: { type: Boolean, required: false },
    },
    data() {
        return {
            flattenedFragments: {} as Record<string, any>,
            change: null as null | (() => void),
            editing: false,
            error: undefined as string | SafeHtml | undefined,
            formErrors: {},
        };
    },
    created() {
        this.flattenedFragments = flatten(this.fragments);
        // Having the change function in created gets rid of weird behaviour https://stackoverflow.com/a/49780382
        this.change = debounce(() => {
            if (this.editing) void this.submit();
        }, 1000);

        if (this.personalDetailsEmpty || this.hasNoBsnOrAddressChecked()) {
            this.editing = true;
        }
    },
    computed: {
        userCanEdit,
        meta() {
            return this.$store.getters['index/meta'] as IndexStoreState['meta'];
        },
        storeType() {
            return this.targetType === BsnLookupType.Task ? StoreType.TASK : StoreType.INDEX;
        },
        uuid(): TaskStoreState['uuid'] | IndexStoreState['uuid'] {
            return this.$store.getters[`${this.storeType}/uuid`];
        },
        fragments(): Fragment {
            return this.$store.getters[`${this.storeType}/fragments`];
        },
        personalDetails() {
            return this.isContact(this.fragments) ? this.fragments.personalDetails : this.fragments.index;
        },
        hasNoBsnOrAddress() {
            return this.personalDetails?.hasNoBsnOrAddress;
        },
        lookupFormSchema() {
            return this.isContact(this.fragments) ? bsnLookupSchemaContact() : bsnLookupSchemaIndex();
        },
        personalDetailsEmpty() {
            return (
                !this.personalDetails?.address?.houseNumber &&
                !this.personalDetails?.address?.houseNumberSuffix &&
                !this.personalDetails?.address?.postalCode &&
                !this.personalDetails?.bsnCensored &&
                !this.personalDetails?.dateOfBirth
            );
        },
        personalDetailsInfo() {
            return this.isContact(this.fragments)
                ? {
                      firstname: this.fragments.general?.firstname,
                      lastname: this.fragments.general?.lastname,
                      address: this.fragments.personalDetails?.address,
                      dateOfBirth: this.fragments.personalDetails?.dateOfBirth,
                      bsnCensored: this.fragments.personalDetails?.bsnCensored,
                      addressVerified: !!this.fragments.personalDetails?.bsnCensored,
                  }
                : {
                      firstname: this.fragments.index?.firstname,
                      initials: this.fragments.index?.initials,
                      lastname: this.fragments.index?.lastname,
                      address: this.fragments.index?.address,
                      dateOfBirth: this.fragments.index?.dateOfBirth,
                      bsnCensored: this.fragments.index?.bsnCensored,
                      addressVerified:
                          this.meta.automaticAddressVerificationStatus ===
                          AutomaticAddressVerificationStatusV1.VALUE_verified,
                  };
        },
        bsnLookupAttempted() {
            if (!this.personalDetails) return false;
            const { dateOfBirth, address } = this.personalDetails;
            return Boolean(dateOfBirth || address?.postalCode || address?.houseNumber || address?.houseNumberSuffix);
        },
        dataCheckedAlertLabel() {
            const person = this.isContact(this.fragments) ? 'Contact' : 'Index';
            return `Gecontroleerd in Basisregistratie Personen. ${person} is geïdentificeerd.`;
        },
    },
    methods: {
        isContact(fragments: Fragment): fragments is TaskStoreState['fragments'] {
            // actually use the targetType prop to determine what type of fragment was fetched
            return this.targetType === BsnLookupType.Task;
        },
        hasNoBsnOrAddressChecked() {
            return Array.isArray(this.hasNoBsnOrAddress) ? this.hasNoBsnOrAddress.length > 0 : this.hasNoBsnOrAddress;
        },
        async refresh() {
            await this.$store.dispatch(`${this.storeType}/LOAD`, this.uuid);
            this.$nextTick(() => (this.flattenedFragments = flatten(this.fragments)));
        },
        async lookup() {
            this.error = undefined;
            this.formErrors = {};

            const dataForApi = unflatten(this.flattenedFragments);
            const personalDetailsFragment = this.isContact(this.fragments)
                ? dataForApi.personalDetails
                : dataForApi.index;

            const {
                dateOfBirth,
                bsnCensored: bsn,
                address: { postalCode, houseNumber, houseNumberSuffix },
            } = personalDetailsFragment;

            // This error shows when a user doesn't fill in all the fields
            if (!bsn || !dateOfBirth || !postalCode || !houseNumber) {
                this.error = generateSafeHtml(
                    '<strong>Let op:</strong> nog niet alle gegevens zijn ingevuld. Vul de gegevens aan en probeer het opnieuw.'
                );
                return;
            }

            try {
                const bsnInfo = await bsnApi.bsnLookup({
                    dateOfBirth,
                    postalCode,
                    houseNumber,
                    houseNumberSuffix,
                    lastThreeDigits: bsn.slice(-3),
                });
                const containsError = 'error' in bsnInfo;

                if (containsError) {
                    switch (bsnInfo?.error) {
                        case BsnLookupError.NO_MATCHING_RESULTS:
                            this.error =
                                'We hebben op basis van deze gegevens iemand gevonden, maar de laatste 3 cijfers van het BSN komen niet overeen. Kloppen de gegevens? Klik dan op ‘Toch doorgaan’.';
                            break;
                        case BsnLookupError.TOO_MANY_RESULTS:
                            this.error =
                                'Er zijn meerdere personen met deze geboortedatum op dit adres gevonden. Daarom kan deze persoon niet geïdentificeerd worden. Kloppen de gegevens? Klik dan op ‘Toch doorgaan’.';
                            break;
                        case BsnLookupError.SERVICE_UNAVAILABLE:
                            this.error =
                                'Het portaal kan op dit moment niet de identiteit van deze persoon controleren. Kloppen de gegevens? Klik dan op ‘Toch doorgaan’.';
                            break;
                        case BsnLookupError.NOT_FOUND:
                        default:
                            this.error =
                                'Geen persoon gevonden op basis van deze gegevens. Kloppen de gegevens? Klik dan op ‘Toch doorgaan’.';
                    }
                }

                await this.submit();

                if (!containsError && 'guid' in bsnInfo && this.uuid) {
                    if (this.isContact(this.fragments)) {
                        await bsnApi.updateTaskBsn(this.uuid, bsnInfo.guid);
                    } else {
                        await bsnApi.updateIndexBsn(this.uuid, bsnInfo.guid);
                    }
                    await this.refresh();
                    this.editing = false;
                }
            } catch (error) {
                const { response } = error as AxiosError<{ errors: Record<string, string[]> }>;
                const bsnError = response?.data.errors.lastThreeDigits;
                if (!bsnError) throw error;
                this.formErrors = { 'index.bsnCensored': JSON.stringify({ warning: bsnError }) };
            }
        },
        async onSubmit() {
            await this.submit();
            this.$nextTick(this.refresh);
            this.editing = false;
        },
        async submit() {
            const dataForApi = unflatten(this.flattenedFragments);

            if (this.isContact(this.fragments)) {
                const data = {
                    dateOfBirth: dataForApi.personalDetails.dateOfBirth,
                    address: dataForApi.personalDetails.address,
                    bsnNotes: this.personalDetails?.bsnNotes,
                    hasNoBsnOrAddress: this.hasNoBsnOrAddress,
                };
                await this.$store.dispatch(`${StoreType.TASK}/${SharedActions.UPDATE_FORM_VALUE}`, {
                    personalDetails: { ...this.fragments.personalDetails, ...data },
                });
            } else {
                const data = {
                    dateOfBirth: dataForApi.index.dateOfBirth,
                    address: dataForApi.index.address,
                    bsnNotes: this.personalDetails?.bsnNotes,
                    hasNoBsnOrAddress: this.hasNoBsnOrAddress,
                };
                await this.$store.dispatch(`${StoreType.INDEX}/${SharedActions.UPDATE_FORM_VALUE}`, {
                    index: { ...this.fragments.index, ...data },
                });
            }
        },
    },
});
</script>
