<template>
    <FormulateForm
        id="callcenter-search-form"
        name="callcenter-search-form"
        class="form-container"
        v-model="formValues"
        @submit="search"
        #default="{ hasValue }"
        :errors="store.searchValidationErrors && store.searchValidationErrors.errors"
    >
        <header>
            <h2>Zoek index- of contactdossier</h2>
        </header>
        <div class="search-form">
            <FormulateInput
                type="text"
                name="dateOfBirth"
                label="Geboortedatum (DD-MM-JJJJ)"
                v-mask="'##-##-####'"
                error-behavior="submit"
                validation="date:DD-MM-YYYY"
                :validation-messages="{
                    date: 'Er mist nog een geldige geboortedatum (DD-MM-JJJJ)',
                }"
                class="w100"
            />
            <FormulateInput
                type="text"
                name="lastThreeBsnDigits"
                label="Laatste 3 cijfers BSN"
                error-behavior="submit"
                :validation="
                    !hasLastThreeBsnDigits || (toggledFields.has('lastThreeBsnDigits') && formValues.lastname !== '')
                        ? ''
                        : 'bail|required|number|min:3,length|max:3,length'
                "
                :validation-messages="
                    toggledFields.has('lastThreeBsnDigits')
                        ? { required: 'Laatste 3 cijfers BSN of Achternaam is verplicht.' }
                        : { required: 'Laatste 3 cijfers BSN is verplicht.' }
                "
                class="mb-2"
                :disabled="!hasLastThreeBsnDigits"
            />
            <Link
                class="d-block mb-4"
                @click="
                    hasLastThreeBsnDigits ? $bvModal.show('no-bsn-warning-modal') : toggleField('lastThreeBsnDigits')
                "
                data-testid="no-bsn-link"
            >
                Deze persoon heeft {{ hasLastThreeBsnDigits ? 'geen' : 'wel een' }} BSN
            </Link>
            <div class="address">
                <FormulateInput
                    type="text"
                    name="postalCode"
                    label="Postcode"
                    error-behavior="submit"
                    :validation="
                        !hasAddress || (toggledFields.has('address') && formValues.phone !== '') ? '' : 'bail|required'
                    "
                    :validation-messages="
                        toggledFields.has('address')
                            ? { required: 'Er missen nog een geldige postcode en huisnummer of telefoonnummer.' }
                            : { required: 'Postcode is verplicht.' }
                    "
                    class="mb-2"
                    :disabled="!hasAddress"
                />
                <FormulateInput
                    type="text"
                    name="houseNumber"
                    label="Huisnummer"
                    error-behavior="submit"
                    :validation="
                        !hasAddress || (toggledFields.has('address') && formValues.phone !== '') ? '' : 'bail|required'
                    "
                    :validation-messages="
                        toggledFields.has('address')
                            ? { required: 'Er missen nog een geldige postcode en huisnummer of telefoonnummer.' }
                            : { required: 'Huisnummer is verplicht.' }
                    "
                    class="mb-2"
                    :disabled="!hasAddress"
                />
                <FormulateInput
                    type="text"
                    name="houseNumberSuffix"
                    label="Toevoeging"
                    class="mb-2"
                    :disabled="!hasAddress"
                />
            </div>
            <span class="d-block mb-1">
                Vul het adres in waarmee de persoon staat ingeschreven in de gemeentelijke basisadministratie.
            </span>
            <Link class="d-block mb-4" @click="toggleField('address')" data-testid="no-address-link">
                Deze persoon heeft {{ hasAddress ? 'geen' : 'wel een' }} adres
            </Link>
            <div v-show="toggledFields.has('lastThreeBsnDigits')">
                <FormulateInput
                    type="text"
                    name="lastname"
                    label="Achternaam"
                    error-behavior="submit"
                    :validation="
                        (hasLastThreeBsnDigits && !toggledFields.has('lastThreeBsnDigits')) ||
                        (toggledFields.has('lastThreeBsnDigits') && formValues.lastThreeBsnDigits !== '')
                            ? ''
                            : 'required'
                    "
                    :validation-messages="
                        hasLastThreeBsnDigits && toggledFields.has('lastThreeBsnDigits')
                            ? { required: 'Laatste 3 cijfers BSN of Achternaam is verplicht.' }
                            : { required: 'Achternaam is verplicht.' }
                    "
                    class="mb-2 w100"
                />
                <span class="d-block mb-4">
                    Vul de volledige achternaam bij geboorte in, dus inclusief eventueel tussenvoegsel.
                </span>
            </div>
            <FormulateInput
                v-show="toggledFields.has('address')"
                type="text"
                name="phone"
                label="Telefoonnummer"
                error-behavior="submit"
                :validation="
                    (hasAddress && !toggledFields.has('address')) ||
                    (toggledFields.has('address') && formValues.postalCode !== '' && formValues.houseNumber !== '')
                        ? ''
                        : 'required'
                "
                :validation-messages="
                    hasAddress && toggledFields.has('address')
                        ? { required: 'Er missen nog een geldige postcode en huisnummer of Telefoonnummer.' }
                        : { required: 'Telefoonnummer is verplicht.' }
                "
                class="mb-2 w100"
            />
        </div>
        <footer>
            <BButton variant="secondary" @click="clearForm" :disabled="!hasValue" data-testid="stop-button">
                Stoppen
            </BButton>
            <BButton
                variant="primary"
                type="submit"
                data-testid="search-button"
                :disabled="store.searchState === RequestState.Pending"
            >
                <span v-if="store.searchState === RequestState.Idle">Zoeken</span>
                <span v-else-if="store.searchState === RequestState.Pending">...</span>
                <span v-else>Opnieuw zoeken</span>
            </BButton>
        </footer>
        <BModal
            id="no-bsn-warning-modal"
            title="Weet je zeker dat je niet op BSN kunt zoeken?"
            ok-title="Zoek op achternaam"
            @ok="toggleField('lastThreeBsnDigits')"
        >
            <p>Als iemand met BSN in het systeem staat, ga je die persoon niet vinden zonder BSN in te vullen.</p>
        </BModal>
    </FormulateForm>
</template>

<script lang="ts">
import { computed, defineComponent, ref } from 'vue';
import { useCallcenterStore, RequestState } from '@/store/callcenter/callcenterStore';
import { useFormulate } from '@/components/AppHooks';
import type { CallcenterSearchRequest } from '@dbco/portal-api/callcenter.dto';
import { Link } from '@dbco/ui-library';

const initialFormValues = (): CallcenterSearchRequest => ({
    dateOfBirth: '',
    lastThreeBsnDigits: '',
    postalCode: '',
    houseNumber: '',
    houseNumberSuffix: '',
    lastname: '',
    phone: '',
});

export default defineComponent({
    name: 'CallcenterSearch',
    components: { Link },
    setup() {
        const store = useCallcenterStore();
        const formValues = ref(initialFormValues());
        const hasLastThreeBsnDigits = ref(true);
        const hasAddress = ref(true);
        const toggledFields = ref<Set<string>>(new Set());

        const searchRequestData = computed(() => {
            let searchData: CallcenterSearchRequest = {
                dateOfBirth: formValues.value.dateOfBirth,
            };

            // Add non-empty fields to the search request
            (Object.keys(formValues.value) as Array<keyof CallcenterSearchRequest>).forEach((key) => {
                const fieldValue = formValues.value[key];
                if (!fieldValue || fieldValue === undefined) return;

                searchData[key] = fieldValue;
            });

            return searchData;
        });

        function toggleField(name: string) {
            toggledFields.value.add(name);
            if (name === 'lastThreeBsnDigits') hasLastThreeBsnDigits.value = !hasLastThreeBsnDigits.value;
            if (name === 'address') hasAddress.value = !hasAddress.value;
        }

        async function search() {
            await store.search({ searchData: searchRequestData.value });
            useFormulate().resetValidation('callcenter-search-form');

            if (RequestState.Resolved && store.searchResultsCount === 0) {
                toggledFields.value.add('lastThreeBsnDigits');
                toggledFields.value.add('address');
            }
        }

        function clearForm() {
            useFormulate().reset('callcenter-search-form', initialFormValues());
            hasLastThreeBsnDigits.value = true;
            hasAddress.value = true;
            toggledFields.value.clear();
            store.reset();
        }

        return {
            RequestState,
            formValues,
            hasLastThreeBsnDigits,
            hasAddress,
            toggledFields,
            store,
            toggleField,
            search,
            clearForm,
        };
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

#callcenter-search-form {
    background: $white;
    border-right: $border-default;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    overflow-y: scroll;

    header {
        padding: $padding-md;
    }

    .search-form {
        flex-grow: 1;
        padding: 0 $padding-md $padding-md $padding-md;

        .address {
            display: grid;
            grid-template-columns: 3fr 2fr 2fr;
            column-gap: 1rem;
        }
    }

    footer {
        border-top: $border-default;
        padding: $padding-md;
        display: flex;
        justify-content: space-between;
        gap: 1rem;

        .btn {
            flex-grow: 1;
        }
    }
}
</style>
