<template>
    <div class="search-result-wrapper">
        <div v-if="searchWithResults">
            <h3 class="mb-4" data-testid="header-title">
                {{ store.searchResultsCount }} dossier<span v-if="store.searchResultsCount != 1">s</span> gevonden
            </h3>

            <CallcenterSearchResultItem
                v-for="item in store.searchResults"
                :key="item.uuid"
                :item="item"
                data-testid="search-result-item"
            />

            <FormInfo
                v-if="!store.searchedAllFields"
                text="Nog niet het juiste dossier gevonden? Vul extra gegevens in om je zoekopdracht uit te breiden."
                data-testid="extra-details-info"
            />
            <FormInfo
                v-if="store.searchedAllFields"
                text="Nog niet het juiste dossier gevonden? Mogelijk is een van de velden niet goed ingevuld. Ook kan het zijn dat het dossier onder een andere GGD-regio valt."
                data-testid="still-not-found-info"
            />
        </div>
        <div v-else class="placeholder">
            <template v-if="store.searchState === RequestState.Pending">
                <h3>...</h3>
            </template>
            <template v-else-if="searchWithNoResults && !store.searchedAllFields">
                <h3>Er zijn extra gegevens nodig</h3>
                <p>Er is nog geen resultaat gevonden. Vul extra gegevens in om je zoekopdracht uit te breiden.</p>
            </template>
            <template v-else-if="searchWithNoResults && store.searchedAllFields">
                <h3>Nog geen resultaat gevonden</h3>
                <p>
                    Mogelijk is een van de velden niet goed ingevuld. Ook kan het zijn dat het dossier onder een andere
                    GGD-regio valt.
                </p>
            </template>
            <template v-else>
                <h3>Vul alle velden in</h3>
                <p>Om privacyredenen kun je een dossier alleen vinden door te zoeken op meerdere gegevens tegelijk.</p>
            </template>
        </div>
    </div>
</template>

<script lang="ts" setup>
import CallcenterSearchResultItem from '@/components/callcenter/CallcenterSearchResultItem/CallcenterSearchResultItem.vue';
import FormInfo from '@/components/form/FormInfo/FormInfo.vue';
import { RequestState, useCallcenterStore } from '@/store/callcenter/callcenterStore';
import { computed } from 'vue';

const store = useCallcenterStore();
const searchWithResults = computed(() => store.searchState === RequestState.Resolved && store.searchResultsCount >= 1);
const searchWithNoResults = computed(
    () => store.searchState === RequestState.Resolved && store.searchResultsCount == 0
);
</script>

<style lang="scss" scoped>
@import './resources/scss/variables.scss';

.search-result-wrapper {
    display: flex;
    flex-direction: column;
    flex-grow: 1;

    .placeholder {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        flex-grow: 1;
    }
}
</style>
