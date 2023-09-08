<template>
    <BForm class="form-container">
        <BFormGroup label="GGD-regio (op basis van postcode)">
            <BFormInput disabled readonly :value="postalCodeValue" />
            <div v-if="postalCodeValue.length" class="d-flex align-items-center mt-3">
                <BFormCheckbox :checked="overwrite" @change="toggleOverwrite" />
                <span>GGD-regio handmatig invoeren</span>
            </div>
        </BFormGroup>
        <BFormGroup v-if="overwrite" label="GGD-regio">
            <BDropdown
                id="overwrite-dropdown"
                :text="current ? current.name : 'Selecteer GGD-regio'"
                :toggle-class="['d-flex', 'align-items-center', 'justify-content-between']"
                block
                lazy
                variant="outline-primary"
            >
                <BDropdownItem
                    v-for="organisation in all"
                    :key="organisation.uuid"
                    @click="editOrganisation(organisation)"
                    >{{ organisation.name }}</BDropdownItem
                >
            </BDropdown>
        </BFormGroup>
        <div class="mt-3 d-flex" v-if="showOverwriteWarning">
            <i class="icon icon--error-notice flex-shrink-0 ml-0 mr-2" />
            <p class="form-font m-0">
                Dit is niet je eigen GGD-regio. Wil je deze regio toch gebruiken? Dan kun je de context niet inzien of
                bewerken.
            </p>
        </div>
    </BForm>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { Organisation } from '@/components/form/ts/formTypes';
import FormInfo from '@/components/form/FormInfo/FormInfo.vue';
import { mapRootGetters, mapState } from '@/utils/vuex';

export default defineComponent({
    name: 'OrganisationEdit',
    components: {
        FormInfo,
    },
    data() {
        return {
            organisationEdited: false,
            overwrite: false,
            placeOrganisationByPostalCode: undefined as Partial<Organisation> | undefined,
            placeOrganisation: undefined as Partial<Organisation> | undefined,
            showRequiredMessage: false,
        };
    },
    async created() {
        this.overwrite = this.$store.getters['place/organisationIsOverwritten'];
        this.getPlaceOrganisationByPostalCode();
        this.getPlaceOrganisation();
        await this.$store.dispatch('organisation/FETCH_ALL');
    },
    computed: {
        ...mapRootGetters({
            userOrganisation: 'userInfo/organisationUuid',
            currentFromAddressSearch: 'organisation/currentFromAddressSearch',
        }),
        ...mapState('organisation', ['all', 'current']),
        postalCodeValue(): string {
            if (this.currentFromAddressSearch?.name) {
                return this.currentFromAddressSearch.name;
            }
            return this.placeOrganisationByPostalCode?.name || '';
        },
        showOverwriteWarning(): boolean {
            if (this.current?.uuid) {
                return this.organisationEdited && this.current.uuid !== this.userOrganisation;
            }
            return this.placeOrganisationByPostalCode?.uuid !== this.userOrganisation;
        },
    },
    destroyed() {
        this.$store.commit('organisation/CLEAR_KEEP_ALL');
    },
    methods: {
        editOrganisation(organisation: Partial<Organisation>) {
            this.organisationEdited = true;
            this.$store.commit('organisation/SET_CURRENT', organisation);
        },
        getPlaceOrganisationByPostalCode() {
            const uuidByPostalCode = this.$store.getters['place/organisationUuidByPostalCode'];
            const placeOrganisationByPostalCode =
                this.$store.getters['organisation/getOrganisationByUuid'](uuidByPostalCode);
            this.placeOrganisationByPostalCode = placeOrganisationByPostalCode;
        },
        getPlaceOrganisation() {
            const uuid = this.$store.getters['place/organisationUuid'];
            const placeOrganisation = this.$store.getters['organisation/getOrganisationByUuid'](uuid);
            this.placeOrganisation = placeOrganisation;
            this.$store.commit('organisation/SET_CURRENT', placeOrganisation);
        },
        toggleOverwrite() {
            if (this.overwrite) {
                this.organisationEdited = true;
                this.$store.commit('organisation/SET_CURRENT', undefined);
            } else {
                this.$store.commit('organisation/SET_CURRENT', this.placeOrganisation);
            }
            this.overwrite = !this.overwrite;
        },
    },
    watch: {
        currentFromAddressSearch(newVal) {
            if (newVal?.uuid?.length && newVal.uuid !== this.userOrganisation && !this.overwrite)
                this.$store.commit('organisation/SET_CURRENT', undefined);
        },
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';
.form-group {
    ::v-deep {
        legend,
        span {
            font-weight: 500;
        }
        input {
            color: $black;
            font-size: 14px;
            padding: 10px 16px;
            height: auto;
        }
    }
}
.icon--error-notice {
    margin-top: 2px;
}
#overwrite-dropdown {
    ::v-deep {
        button {
            border-color: $lightest-grey;
            color: $black;
            padding: 10px 16px;
            background: none;
        }
        ul {
            width: 100%;
            max-height: 12.5rem;
            overflow: auto;
        }
        .dropdown-item {
            color: $black;
        }
    }
}
</style>
