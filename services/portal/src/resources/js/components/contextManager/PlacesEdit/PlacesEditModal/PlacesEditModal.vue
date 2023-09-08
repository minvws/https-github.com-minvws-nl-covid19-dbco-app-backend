<template>
    <BModal
        v-if="place && place.uuid"
        title="Context bewerken"
        ok-only
        visible
        @ok="savePlace($event)"
        @hidden="$emit('hide')"
    >
        <template #modal-footer="{ ok }">
            <BButton variant="primary" :disabled="isLoading" @click="ok()">
                <div v-if="isLoading" class="loading-state-button">
                    <span><BSpinner small /></span>
                </div>
                Opslaan
            </BButton>
        </template>

        <div class="form-container mx-n3 mb-4">
            <FormulateFormWrapper v-if="schema && values" v-model="values" :errors="errors" :schema="schema" />
        </div>
        <OrganisationEdit />
        <hr />
        <SectionManager />
        <SectionSituationNumber :situationsFromApi="place.situationNumbers" />
    </BModal>
</template>

<script lang="ts">
import type { PlaceDTO } from '@dbco/portal-api/place.dto';
import { placeSchema } from '@/components/form/ts/formSchema';
import { mapGetters, mapState } from '@/utils/vuex';
import type { BvModalEvent } from 'bootstrap-vue';
import { defineComponent } from 'vue';
import OrganisationEdit from '../OrganisationEdit/OrganisationEdit.vue';
import SectionManager from '../SectionManagement/SectionManager/SectionManager.vue';
import SectionSituationNumber from '../SectionManagement/SectionSituationNumber/SectionSituationNumber.vue';
import { mapState as mapPiniaState } from 'pinia';
import { usePlaceSituationStore } from '@/store/placeSituation/placeSituationStore';
import { StoreType } from '@/store/storeType';
import { PlaceActions } from '@/store/place/placeActions/placeActions';
import { SharedMutations } from '@/store/mutations';

export default defineComponent({
    name: 'PlacesEditModal',
    components: {
        SectionManager,
        SectionSituationNumber,
        OrganisationEdit,
    },
    data() {
        return {
            errors: {},
            values: {} as Partial<PlaceDTO>,
            isLoading: false,
        };
    },
    async created() {
        if (!this.place?.uuid?.length) return;
        this.values = { ...this.place };
        await this.$store.dispatch(`${StoreType.PLACE}/${PlaceActions.FETCH_SECTIONS}`, this.place.uuid);
    },
    destroyed() {
        this.$store.commit(`${StoreType.PLACE}/${SharedMutations.CLEAR}`);
    },
    computed: {
        ...mapGetters(StoreType.USERINFO, {
            userOrganisation: 'organisationUuid',
        }),
        ...mapState(StoreType.ORGANISATION, {
            currentFromAddressSearch: 'currentFromAddressSearch',
            organisation: 'current',
        }),
        ...mapState(StoreType.PLACE, {
            place: 'current',
        }),
        ...mapPiniaState(usePlaceSituationStore, ['situationNumbers']),
        currentFromAddressSearchDiffers(): boolean {
            return !!this.currentFromAddressSearch?.length && this.currentFromAddressSearch !== this.userOrganisation;
        },
        organisationUuidToDispatch(): string | undefined {
            if (!this.organisation?.uuid?.length && this.currentFromAddressSearchDiffers)
                return this.currentFromAddressSearch;
            return this.organisation?.uuid;
        },
        schema() {
            if (this.currentFromAddressSearchDiffers && !this.organisation) {
                return placeSchema(true);
            }
            return placeSchema(false);
        },
    },
    methods: {
        async savePlace(event: BvModalEvent) {
            event?.preventDefault();
            this.isLoading = true;
            this.errors = {};
            await this.$store.dispatch(`${StoreType.PLACE}/${PlaceActions.UPDATE}`, {
                ...this.values,
                ...{ organisationUuid: this.organisationUuidToDispatch },
                ...{ situationNumbers: this.situationNumbers },
            });
            await this.$store.dispatch(`${StoreType.PLACE}/${PlaceActions.SAVE_SECTIONS}`);
            this.$emit('saved');
            this.isLoading = false;
        },
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';
hr {
    margin: 2rem 0;
}

.loading-state-button {
    display: flex;
    flex-direction: row;

    span {
        margin-right: $padding-xs;
    }
}
</style>
