<template>
    <div class="place-search form" :class="{ 'place-search--expanded': isExpanded }">
        <BInputGroup>
            <label class="context-label" for="contexten">Voeg contexten toe</label>
            <BFormInput id="contexten" autocomplete="off" v-model="searchString" ref="searchInput" debounce="500" />
            <BInputGroupAppend is-text>
                <BSpinner v-if="isBusy" class="spinner" variant="primary" />
                <img v-else :src="iconSearchSvg" alt="search icon" aria-hidden="true" />
            </BInputGroupAppend>
        </BInputGroup>
        <div v-if="isExpanded" class="results">
            <div v-if="visibleMatches.length" class="matches">
                <div v-for="match in visibleMatches" :key="match.uuid" class="d-flex flex-row align-items-center">
                    <BFormCheckbox
                        class="ml-3"
                        :checked="isSelected(match)"
                        @change="() => toggle(match)"
                        :id="`match-${match.uuid}`"
                    />
                    <label :for="`match-${match.uuid}`">
                        <Place :value="match" :searchString="searchString" />
                    </label>
                </div>
            </div>
            <div v-else class="no-results">
                <img :src="iconSearchSvg" alt="search icon" />
                <span> <strong>Geen resultaten gevonden</strong><br />Probeer je zoekopdracht breder te maken </span>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { placeApi } from '@dbco/portal-api';
import PlaceComponent from '@/components/forms/Place/Place.vue';
import type { PlaceDTO } from '@dbco/portal-api/place.dto';
import iconSearchSvg from '@images/icon-search.svg';

export default defineComponent({
    name: 'PlaceSearch',
    components: { Place: PlaceComponent },
    props: {
        hideUuid: {
            type: String,
            required: false,
        },
        initiallySelected: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            isBusy: false,
            selected: [...this.initiallySelected] as PlaceDTO[],
            matches: [] as PlaceDTO[],
            searchString: '',
            iconSearchSvg,
        };
    },
    computed: {
        isExpanded() {
            return Boolean(!this.isBusy && this.searchString && this.searchString.length >= 3);
        },
        visibleMatches() {
            return this.matches.filter(({ uuid }) => uuid !== this.hideUuid);
        },
    },
    methods: {
        isSelected(match: PlaceDTO) {
            return this.selected.some(({ uuid }) => uuid === match.uuid);
        },
        toggle(place: PlaceDTO) {
            if (this.isSelected(place)) {
                this.selected = this.selected.filter(({ uuid }) => uuid !== place.uuid);
            } else {
                this.selected.push(place);
            }

            this.$emit('updateSelected', this.selected);
        },
        async performSearch() {
            this.isBusy = true;
            this.matches = [];

            try {
                const { data } = await placeApi.searchSimilar(this.searchString);
                this.matches = data || [];
            } finally {
                this.isBusy = false;
            }

            // eslint-disable-next-line no-warning-comments
            // TODO: Possible bug: there is no matches ref set?
            if (this.$refs.matches) {
                (this.$refs.matches as HTMLElement).scrollTop = 0;
            }
        },
    },
    watch: {
        searchString(newVal) {
            if (newVal.length < 3) return;

            void this.performSearch();
        },
    },
});
</script>

<style scoped lang="scss">
@import './resources/scss/_variables.scss';

.results {
    border: 1px solid $lightest-grey;
    border-top: 0;
    border-bottom-left-radius: 0.25rem;
    border-bottom-right-radius: 0.25rem;
}

.context-label {
    width: 100%;
}

.no-results {
    align-items: center;
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

.place-search {
    &--expanded {
        .form-control {
            border-bottom-left-radius: 0;
        }

        .input-group-text {
            border-bottom-right-radius: 0;
        }
    }
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
}

.matches {
    max-height: 500px;
    overflow-y: auto;
    cursor: pointer;
}
</style>
