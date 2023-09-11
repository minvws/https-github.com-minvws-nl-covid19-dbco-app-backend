<template>
    <BModal class="place-select" @hide="$emit('hide')" hide-footer :title="title" visible>
        <div v-if="mode === MODES.PICK_PLACE">
            <div class="location-select form">
                <p class="mb-2 title">Zoek op naam en/of adres van de locatie</p>
                <BInputGroup>
                    <BFormInput autocomplete="off" v-model="searchString" ref="searchInput" debounce="500" />
                    <BInputGroupAppend is-text>
                        <BSpinner v-if="isBusy" class="spinner" variant="primary" />
                        <img v-else :src="iconSearchSvg" alt="search icon" />
                    </BInputGroupAppend>
                </BInputGroup>
                <div class="dropdown" v-if="!isBusy && searchString && searchString.length >= 3">
                    <BListGroup ref="matches" class="matches" v-if="computedMatches.length">
                        <BListGroupItem
                            href="#"
                            v-for="match in computedMatches"
                            :key="match.uuid || match.id"
                            :disabled="isDisabledForCreation(match)"
                            @click="selectPlace(match)"
                        >
                            <Place :value="match" :searchString="searchString" />
                        </BListGroupItem>

                        <BListGroupItem class="list-button" v-if="!showAll && matches.length > this.showLimit">
                            <BButton @click="showAll = true" size="sm" variant="primary" class="w-100"
                                >Toon meer resultaten</BButton
                            >
                        </BListGroupItem>
                    </BListGroup>
                    <div v-else class="no-results">
                        <img :src="iconSearchSvg" alt="search icon" />
                        <span>
                            <strong>Geen resultaten gevonden</strong><br />Probeer je zoekopdracht breder te maken
                        </span>
                    </div>

                    <BListGroup>
                        <BListGroupItem class="list-button">
                            <BButton @click="selectPlace(null)" size="sm" variant="outline-primary" class="w-100"
                                >Ik kan de context niet vinden, nieuwe aanmaken</BButton
                            >
                        </BListGroupItem>
                    </BListGroup>
                </div>
            </div>
        </div>
        <div v-else-if="mode === MODES.ADD || mode === MODES.EDIT || mode === MODES.PICK_CATEGORY">
            <PlaceForm
                :editMode="mode === MODES.EDIT"
                @created="placeCreated"
                @duplicates="newPlaceDuplicates"
                @select="selectPlace"
                @edit="mode = MODES.EDIT"
            />
        </div>
        <div v-else-if="mode === MODES.PICK_SECTIONS">
            <ContextSectionsForm
                v-if="contextUuid"
                :place="current"
                :context-uuid="contextUuid"
                @edit="mode = MODES.EDIT"
                @submitContext="$emit('hide')"
            />
            <PlaceSectionsForm v-else :place="current" @edit="mode = MODES.EDIT" @saved="creationCompleted" />
        </div>
        <div v-else-if="mode === MODES.CHECK_DUPLICATES">
            <DuplicatePlacesForm
                :duplicatePlaces="duplicatePlaces"
                :newPlace="current && 'uuid' in current ? current : location"
                @selectPlace="selectPlace"
            />
        </div>
    </BModal>
</template>

<script>
import { contextApi, placeApi } from '@dbco/portal-api';
import PlaceSectionsForm from '@/components/contextManager/PlacesEdit/PlaceSectionsForm/PlaceSectionsForm.vue';
import ContextSectionsForm from '@/components/forms/ContextSectionsForm/ContextSectionsForm.vue';
import DuplicatePlacesForm from '@/components/forms/DuplicatePlacesForm/DuplicatePlacesForm.vue';
import Place from '@/components/forms/Place/Place.vue';
import PlaceForm from '@/components/forms/PlaceForm/PlaceForm.vue';
import { SharedActions } from '@/store/actions';
import { SharedMutations } from '@/store/mutations';
import { PlaceMutations } from '@/store/place/placeMutations/placeMutations';
import { StoreType } from '@/store/storeType';
import { isEmpty } from 'lodash';
import { mapGetters, mapState } from 'vuex';
import iconSearchSvg from '@images/icon-search.svg';
import showToast from '@/utils/showToast';

const MODES = {
    ADD: 'add',
    EDIT: 'edit',
    CHECK_DUPLICATES: 'checkDuplicates',
    PICK_CATEGORY: 'pickCategory',
    PICK_PLACE: 'pickPlace',
    PICK_SECTIONS: 'pickSections',
};

export default {
    name: 'PlaceSelectModal',
    components: { Place, ContextSectionsForm, PlaceForm, PlaceSectionsForm, DuplicatePlacesForm },
    props: {
        initialQuery: {
            type: String,
            default: '',
        },
    },
    data() {
        return {
            MODES,
            duplicatePlaces: null,
            isBusy: false,
            mode: null,
            matches: [],
            searchString: this.initialQuery,
            showAll: false,
            showLimit: 10,
            iconSearchSvg,
        };
    },
    created() {
        if (this.contextPlace && !isEmpty(this.contextPlace)) {
            this.$store.commit(`${StoreType.PLACE}/${PlaceMutations.SET_PLACE}`, this.contextPlace);
            this.mode = MODES.PICK_SECTIONS;
        } else {
            this.mode = MODES.PICK_PLACE;
            this.performSearch();
        }
    },
    destroyed() {
        this.$store.commit(`${StoreType.PLACE}/${SharedMutations.CLEAR}`);
    },
    computed: {
        ...mapGetters({
            caseUuid: `${StoreType.INDEX}/uuid`,
            contextUuid: `${StoreType.CONTEXT}/uuid`,

            contextPlace: `${StoreType.CONTEXT}/place`,
            indexContexts: `${StoreType.INDEX}/contexts`,
            location: `${StoreType.PLACE}/currentLocation`,
        }),
        ...mapState(StoreType.PLACE, ['current']),
        computedMatches() {
            if (this.showAll) return this.matches;

            return this.matches.slice(0, this.showLimit);
        },
        title() {
            switch (this.mode) {
                case MODES.ADD:
                    return 'Nieuwe context aanmaken';

                case MODES.EDIT:
                    return 'Context wijzigen';

                case MODES.CHECK_DUPLICATES:
                    return 'Er bestaan al één of meerdere contexten op dit adres';

                case MODES.PICK_CATEGORY:
                    return 'Voeg een categorie toe aan deze context';

                case MODES.PICK_SECTIONS:
                    return 'Afdeling, team, klas, lijn- of vluchtnummer toevoegen';

                case MODES.PICK_PLACE:
                    return this.contextUuid ? 'Context koppelen' : 'Context aanmaken';
            }
        },
    },
    methods: {
        isDisabledForCreation(match) {
            // When there's a context, a selected match will be linked/unlinked to it. This is allowed.
            // When there's no context to link/unlink to the selected match will be created. This is disabled for already existing places(Matches with uuid).
            return !this.contextUuid && 'uuid' in match;
        },
        newPlaceDuplicates(place, duplicates) {
            this.duplicatePlaces = duplicates;
            if (place?.id) {
                this.$store.commit(`${StoreType.PLACE}/${PlaceMutations.SET_LOCATION}`, place);
            } else {
                this.$store.commit(`${StoreType.PLACE}/${PlaceMutations.SET_PLACE}`, place);
            }
            this.mode = MODES.CHECK_DUPLICATES;
        },
        async performSearch() {
            if (!this.searchString.length) return;
            this.isBusy = true;
            this.matches = [];

            try {
                const data = await placeApi.search(this.searchString);
                this.showAll = false;
                this.matches = [...(data.places ?? []), ...(data.suggestions ?? [])];
            } finally {
                this.isBusy = false;
            }

            if (this.$refs.matches) {
                this.$refs.matches.scrollTop = 0;
            }
        },
        creationCompleted() {
            this.$store.commit(`${StoreType.PLACE}/${SharedMutations.CLEAR}`);
            this.$emit('creationCompleted');
        },
        placeCreated() {
            this.$emit('placeCreated');
        },
        async selectPlace(place, isUpdate) {
            // Check if there are other contexts for this index with the same place
            const duplicateRelations = this.indexContexts.filter(
                (context) =>
                    place?.uuid && context.uuid && context.uuid !== this.contextUuid && context.placeUuid === place.uuid
            );

            if (duplicateRelations.length === 0) {
                // If no duplicates, set the place
                await this.setPlace(place, isUpdate);
                return;
            }

            // Get all dates, get an unique set, sort and format them
            const dates = Array.from(new Set(duplicateRelations.reduce((acc, cur) => [...acc, ...cur.moments], [])))
                .sort()
                .map((date) => this.$filters.dateFormat(date));

            let dateString = '';
            if (dates.length > 1) {
                // Make a comma separated string out of them with a different last seperator
                dateString = ` op ${dates.slice(0, -1).join(', ')} en ${dates.slice(-1)}`;
            } else if (dates.length === 1) {
                dateString = ` op ${dates.slice(-1)}`;
            }

            this.$modal.show({
                title: 'Deze locatie is al gekoppeld aan een andere context.',
                text: `Je hebt al aangegeven dat de index${dateString} op deze locatie was. Het is daarom niet nodig om een nieuwe context aan te maken. Je kunt de nieuwe bezoekdata aan zowel de context binnen de bron- als besmettelijke periode toevoegen.`,
                okTitle: 'Toch koppelen',
                onConfirm: async () => {
                    await this.setPlace(place, isUpdate);
                },
            });
        },
        async setPlace(place, isUpdate) {
            if (place === null || !place.uuid) {
                // If no place (=new) or no uuid (=suggestion), open the add form
                if (place?.id) {
                    this.$store.commit(`${StoreType.PLACE}/${PlaceMutations.SET_LOCATION}`, place);
                }
                this.mode = MODES.ADD;
                return;
            }
            this.$store.commit(`${StoreType.PLACE}/${PlaceMutations.SET_PLACE}`, place);
            if (!place.category) {
                this.mode = MODES.PICK_CATEGORY;
            } else {
                // Otherwise select this place
                this.mode = MODES.PICK_SECTIONS;

                await this.persist(isUpdate);
            }
        },
        async persist(isUpdate) {
            if (!this.contextUuid) return;
            // Set to store
            this.$store.dispatch(`${StoreType.CONTEXT}/${SharedActions.CHANGE}`, {
                path: 'place',
                values: this.current,
            });

            if (isUpdate) return;
            // Save to BE
            try {
                await contextApi.linkPlace(this.contextUuid, this.current.uuid);
            } catch (error) {
                const errorMessage = `${this.$t('errors.pair', { requested: 'de locatie' })}`;
                showToast(errorMessage, 'link-place-error', true);
            }

            // Place may no longer be editable after linking it to the context.
            // So we get the new status from the backend and update the store to reflect the correct state.
            const { contexts } = await contextApi.getContexts(this.caseUuid);
            const context = contexts.find((context) => context.place.uuid === this.current.uuid);
            await this.$store.dispatch(`${StoreType.CONTEXT}/${SharedActions.CHANGE}`, {
                path: 'place',
                values: context.place,
            });
            this.$store.commit(`${StoreType.PLACE}/${PlaceMutations.SET_PLACE}`, context.place);
        },
    },
    watch: {
        searchString(newVal, oldVal) {
            if (newVal === oldVal || newVal.length < 3) return;

            this.performSearch();
        },
    },
};
</script>

<style scoped lang="scss">
@import './resources/scss/_variables.scss';

.title {
    font-weight: 500;
}

.no-results {
    align-items: center;
    border-bottom: 1px solid $bco-purple-light;
    display: flex;
    justify-content: center;
    padding: 4rem 0;

    img {
        background-color: $bco-grey;
        border-radius: $border-radius-medium;
        object-fit: contain;
        margin-right: 1rem;
        padding: 0.5rem;
    }
}

.location-select {
    /* Ensures dropdown is positioned correctly */
    position: relative;
    padding: 0;
    margin: 0;
}

.dropdown {
    background-color: $white;
    border: 1px solid $bco-purple-light;
    position: absolute;

    margin-top: 0.5rem;

    width: 100%;
    z-index: 9999;
}

.input-group-text {
    min-width: 44px;

    .spinner {
        position: absolute;
        margin-left: -0.125rem;
    }
}

.list-group {
    .list-group-item {
        border: 0;
        padding: 0;

        &.list-button {
            padding: 1rem;

            button {
                padding: 0.5rem 0;
            }
        }
    }

    &.matches {
        max-height: 500px;
        overflow-y: auto;
    }
}
</style>
