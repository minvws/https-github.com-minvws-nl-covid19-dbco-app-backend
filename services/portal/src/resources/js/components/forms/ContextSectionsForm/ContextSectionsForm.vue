<template>
    <BContainer>
        <BRow align-v="center">
            <BCol class="p-0 form">
                <h4>Locatie</h4>
                <div class="place-wrapper">
                    <Place :value="place" />
                    <button type="button" data-testid="edit-icon" v-if="canEdit" @click="$emit('edit')" class="mr-4">
                        <img :src="iconEditSvg" alt="edit icon" />
                    </button>
                </div>
            </BCol>
        </BRow>

        <BRow v-show="loaded">
            <BCol class="p-0 form">
                <h4>Afdeling, team, klas, lijn- of vluchtnummer</h4>
                <div class="tags-input formulate-input">
                    <div class="tags">
                        <BFormTag
                            v-for="(section, index) in contextSections"
                            :key="index"
                            :title="section.label"
                            variant="primary"
                            @remove="toggleSection(section)"
                        />
                        <BFormInput
                            v-model="searchString"
                            autocomplete="off"
                            data-testid="search-string-input"
                            :placeholder="
                                contextSections.length === 0
                                    ? 'Voeg een afdeling, team, klas, lijn- of vluchtnummer toe'
                                    : ''
                            "
                            debounce="300"
                            v-on:keyup.enter="addSection"
                        />
                    </div>
                    <div class="tags-menu">
                        <div
                            v-for="section in filteredSections"
                            :key="section.uuid"
                            class="option d-flex align-items-center p-3"
                        >
                            <BFormCheckbox
                                :checked="isSelected(section)"
                                @change="toggleSection(section)"
                                data-testid="place-section-checkbox"
                                :data-testlabel="section.label"
                            />
                            <div class="option-description d-flex flex-column" data-testid="place-section">
                                <strong><Highlight :text="section.label" :query="searchString" /></strong>
                                <span v-if="!loading && section.indexCount === 0">Geen indexen</span>
                                <span v-else-if="!loading && section.indexCount === 1 && isSelected(section)"
                                    >Alleen deze index</span
                                >
                                <span v-else-if="!loading && section.indexCount > 0"
                                    >{{ section.indexCount }} {{ indexTitle(section.indexCount) }}</span
                                >
                            </div>
                        </div>
                        <div class="p-3" v-if="searchString && canAdd" data-testid="add-section-button-wrapper">
                            <BButton
                                variant="outline-primary"
                                block
                                @click="addSection"
                                data-testid="add-section-button"
                                >'{{ searchString }}' aanmaken</BButton
                            >
                        </div>
                    </div>
                </div>
            </BCol>
        </BRow>

        <BRow>
            <BCol class="form p-0 mt-3">
                <BFormCheckbox v-model="overrideSubmit" :disabled="contextSections.length > 0"
                    >Ik kan niet precies aangeven waar binnen deze context de index was</BFormCheckbox
                >
            </BCol>
        </BRow>

        <BRow>
            <BCol class="p-0">
                <BButton
                    :disabled="contextSections.length === 0 && !overrideSubmit"
                    variant="primary"
                    class="w-100 mt-3"
                    @click="$emit('submitContext')"
                    >Opslaan</BButton
                >
            </BCol>
        </BRow>
    </BContainer>
</template>

<script>
import { contextApi, placeApi } from '@dbco/portal-api';
import Highlight from '@/components/utils/Highlight/Highlight.vue';
import Place from '../Place/Place.vue';
import iconEditSvg from '@images/icon-edit.svg';

export default {
    name: 'ContextSectionsForm',
    components: {
        Highlight,
        Place,
    },
    props: {
        place: {
            type: Object,
            required: true,
        },
        contextUuid: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            loaded: false,
            loading: false,
            contextSections: [],
            overrideSubmit: false,
            placeSections: [],
            searchString: '',
            iconEditSvg,
        };
    },
    mounted() {
        this.loadSections();
    },
    computed: {
        filteredSections() {
            return this.placeSections
                .filter((section) => section.label.toLowerCase().includes(this.searchString.toLowerCase()))
                .sort((a, b) => {
                    if (a.label < b.label) {
                        return -1;
                    } else if (a.label > b.label) {
                        return 1;
                    }
                    return 0;
                });
        },
        canAdd() {
            return !this.filteredSections.some((section) => {
                return section.label.toLowerCase() === this.searchString.toLowerCase();
            });
        },
        canEdit() {
            return !this.place.isVerified && this.place.editable;
        },
    },
    methods: {
        async loadSections() {
            const [sectionsResponse, contextSectionsResponse] = await Promise.all([
                placeApi.getSections(this.place.uuid),
                contextApi.getSections(this.contextUuid),
            ]);
            this.placeSections = sectionsResponse.sections;
            this.contextSections = contextSectionsResponse.sections;
            this.loaded = true;
        },
        async addSection() {
            const newLabel = { label: this.searchString };
            const data = await placeApi.createPlaceSections(this.place.uuid, [newLabel], this.contextUuid);
            if (data.sections) {
                this.placeSections = [...this.placeSections, ...data.sections];
                this.contextSections = [...this.contextSections, ...data.sections];
                this.searchString = ''; // clear add form
            }
            void this.loadSections();
        },
        isSelected(section) {
            return this.contextSections.some((cSection) => cSection.uuid == section.uuid);
        },
        async toggleSection(section) {
            this.loading = true;
            const contextSectionIndex = this.contextSections.findIndex((cSection) => cSection.uuid == section.uuid);
            if (contextSectionIndex >= 0) {
                this.contextSections.splice(contextSectionIndex, 1);
                await contextApi.unlinkSection(this.contextUuid, section.uuid);
            } else {
                this.contextSections.push(section);
                await contextApi.linkSection(this.contextUuid, section.uuid);
            }
            await this.loadSections();
            this.loading = false;
        },
        indexTitle(indexCount) {
            return indexCount > 1 ? 'indexen' : 'index';
        },
    },
};
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

.error {
    font-size: 0.8em;
}

.place-wrapper {
    align-items: center;
    border: 1px solid $lightest-grey;
    border-radius: $border-radius-small;
    display: flex;
    margin-bottom: 1.5rem;
    width: 100%;

    button {
        background: none;
        border: none;
    }
}

.tags-input {
    border: 1px solid $bco-purple-light;
    border-radius: $border-radius-small;

    .tags {
        padding: 5px;
        width: 100%;
        overflow: hidden;
        display: flex;
        flex-wrap: wrap;
        row-gap: 0.25rem;

        .b-form-tag {
            flex: 0 0 auto;
            height: 32px;
            font-size: 14px;
            line-height: 25px;
            color: $black;
            background: rgba($color: $primary, $alpha: 0.05);
            align-items: center !important;

            ::v-deep button.b-form-tag-remove {
                color: $primary;
                opacity: 1;
            }
        }

        input {
            flex: 1 0 auto;
            border: none;
            outline: none;
            box-shadow: none;
            width: auto;
        }
    }

    .tags-menu {
        max-height: 216px;
        border-top: 1px solid $bco-purple-light;
        overflow: auto;
        box-sizing: content-box;

        .option {
            .option-description {
                padding-left: 12px;
            }
        }
    }
}
</style>
