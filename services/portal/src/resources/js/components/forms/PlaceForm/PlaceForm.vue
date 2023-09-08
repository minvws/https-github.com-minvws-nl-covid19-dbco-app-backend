<template>
    <div>
        <div v-if="useLocationView">
            <h4>Locatie</h4>
            <div class="place-wrapper">
                <Place :value="formData" />
            </div>
        </div>

        <div class="form-container mx-n3">
            <FormulateForm v-if="schema" v-model="formData" :schema="schema" @submit="submitForm">
                <div class="container mt-4 mb-3">
                    <OrganisationEdit class="mb-4" v-if="!useLocationView && canVerifyPlace" />
                    <BButton v-if="editMode" type="submit" block variant="primary">Context wijzigen</BButton>
                    <BButton v-else type="submit" block variant="primary">{{ creationTitle }}</BButton>
                </div>
            </FormulateForm>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { PermissionV1 } from '@dbco/enum';
import checkForDuplicates from '../formUtils/checkForDuplicates/checkForDuplicates';
import formatAddress from '../formUtils/formatAddress/formatAddress';
import { placeSchema, placeCreateSuggestedSchema } from '@/components/form/ts/formSchema';
import Place from '../Place/Place.vue';
import OrganisationEdit from '@/components/contextManager/PlacesEdit/OrganisationEdit/OrganisationEdit.vue';
import type { LocationDTO, PlaceDTO } from '@dbco/portal-api/place.dto';
import { mapRootGetters, mapState } from '@/utils/vuex';

export default defineComponent({
    name: 'PlaceForm',
    components: { Place, OrganisationEdit },
    props: {
        /**
         * Editmode can be true for an existing place or for a place suggestion (location type):
         * - Existing place
         *      Editing and saving will create a new place, by linking the new place to the
         *      context, the backend will delete the previous place, if it's not linked to any
         *      other context'
         *  - place suggestion
         *      A place suggestion can be edited, however limited due to its differing data type.
         */
        editMode: {
            default: false,
        },
    },
    data() {
        return {
            errors: {},
            formData: {
                label: null,
                category: '',
                categoryLabel: null,
                address: null,
                addressLabel: null,
                ggd: {
                    code: null,
                    municipality: null,
                },
                uuid: '',
                street: '',
                housenumber: '',
                housenumberSuffix: null,
                postalcode: '',
                town: '',
                country: '',
                indexCount: 0,
                updatedAt: '',
                createdAt: '',
                isVerified: false,
                organisationUuidByPostalCode: null,
                organisationUuid: null,
                source: 'manual',
            } as Partial<PlaceDTO | LocationDTO>,
            placeSchema: placeSchema(),
        };
    },
    created() {
        if (this.current?.uuid) {
            this.formData = this.current;
        } else if (this.location?.id) {
            this.formData = this.location;
        } else {
            this.$store.commit('place/SET_ORGANISATION', this.userOrganisation);
            this.$store.commit('place/SET_ORGANISATION_BY_POSTALCODE', this.userOrganisation);
        }
    },
    computed: {
        ...mapRootGetters({
            currentFromAddressSearch: 'organisation/currentFromAddressSearch',
            location: 'place/currentLocation',
            userOrganisation: 'userInfo/organisationUuid',
        }),
        ...mapState('place', ['current']),
        ...mapState('organisation', {
            organisation: 'current',
        }),
        canVerifyPlace() {
            return !!this.$store.getters['userInfo/hasPermission'](PermissionV1.VALUE_placeVerify);
        },
        creationTitle() {
            return this.canVerifyPlace ? 'Context aanmaken' : 'Context aanmaken';
        },
        currentFromAddressSearchDiffers() {
            return (
                !!this.currentFromAddressSearch?.uuid?.length &&
                this.currentFromAddressSearch.uuid !== this.userOrganisation
            );
        },
        formattedFormData() {
            return formatAddress(this.formData);
        },
        isNewFromSuggestion() {
            return 'id' in this.formData;
        },
        organisationUuidToDispatch() {
            if (!this.organisation?.uuid?.length && this.currentFromAddressSearchDiffers)
                return this.currentFromAddressSearch?.uuid;
            return this.organisation?.uuid;
        },
        payload() {
            return {
                ...this.formattedFormData,
                ...{ organisationUuid: this.organisationUuidToDispatch },
            };
        },
        schema() {
            // The complete form is shown when creating a manual place (not from suggestion), or when
            // in editMode (edit existing or edit place suggestion)
            return !this.isNewFromSuggestion || this.editMode ? this.placeSchema : placeCreateSuggestedSchema();
        },
        useLocationView() {
            return !!this.isNewFromSuggestion && !this.editMode;
        },
    },
    methods: {
        async savePlace() {
            this.errors = {};
            let isUpdate = false;
            if (this.editMode) {
                await this.$store.dispatch('place/UPDATE', this.payload);
                isUpdate = true;
            } else {
                await this.$store.dispatch('place/CREATE', this.payload);
            }
            this.$emit('select', this.current, isUpdate);
            this.$emit('created');
        },
        async submitForm() {
            if (this.canVerifyPlace) {
                this.formattedFormData.isVerified = true;
            }
            const duplicates = await checkForDuplicates(this.formattedFormData);
            if (duplicates.length) {
                this.$emit('duplicates', this.payload, duplicates);
                return;
            }
            await this.savePlace();
        },
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

.place-wrapper {
    align-items: center;
    border: 1px solid $lightest-grey;
    border-radius: $border-radius-small;
    display: flex;
    margin-bottom: 1.5rem;
    width: 100%;

    img {
        cursor: pointer;
    }
}
</style>
