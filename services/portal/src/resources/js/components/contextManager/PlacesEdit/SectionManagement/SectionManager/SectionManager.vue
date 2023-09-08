<template>
    <div>
        <BFormGroup label="Afdeling, teams of klassen">
            <div class="sections">
                <div class="sections-input">
                    <BFormInput
                        v-model="searchString"
                        autocomplete="off"
                        data-testid="search-string-input"
                        placeholder="Voeg een afdeling, team of klas toe"
                        debounce="300"
                        v-on:keyup.enter="addSection"
                    />
                </div>
                <div class="sections-menu">
                    <!-- Temporarily hide "no-section-entry" with "hidden" class until merging is possible.
                    Ticket for merging with "no-section-entry": https://egeniq.atlassian.net/browse/DBCO-3687 -->
                    <div
                        v-for="section in filteredSections"
                        :key="section.uuid"
                        class="section"
                        :class="{ hidden: section.uuid === 'no-section-entry-uuid' }"
                    >
                        <div class="d-flex align-items-center p-3">
                            <BFormCheckbox
                                :checked="isSelected(section.uuid)"
                                @change="toggleSection(section)"
                                :data-testlabel="section.label"
                            />
                            <div class="option-description d-flex flex-column" data-testid="place-section">
                                <strong><Highlight :text="section.label" :query="searchString" /></strong>
                                <span v-if="section.indexCount === 0">Geen indexen</span>
                                <span v-else-if="section.indexCount > 0"
                                    >{{ section.indexCount }}
                                    {{ indexTitle(section.indexCount, !!section.hasCalculatedIndex) }}</span
                                >
                            </div>
                        </div>
                        <BButton
                            @click="changeLabelSection = section"
                            class="change-label-trigger hide-until-active label-change-button"
                            >Naam wijzigen</BButton
                        >
                        <BForm
                            @submit="changeLabel(section.uuid)"
                            @reset="changeLabelSection = null"
                            class="change-label-form"
                            inline
                            v-if="changeLabelSection === section"
                        >
                            <BFormGroup label-sr-only label="Naam wijzigen" label-for="afdeling-naam-wijzigen">
                                <BFormInput
                                    debounce="300"
                                    autocomplete="off"
                                    id="afdeling-naam-wijzigen"
                                    v-model="labelToChange"
                                />
                            </BFormGroup>

                            <BButton type="submit" class="label-change-button">Bevestigen</BButton>
                            <BButton type="reset" class="label-change-button">Annuleren</BButton>
                        </BForm>
                    </div>
                    <div class="p-3" v-if="canAdd" data-testid="add-section-button-wrapper">
                        <BButton block @click="addSection" data-testid="add-section-button" variant="outline-primary"
                            >'{{ searchString }}' aanmaken</BButton
                        >
                    </div>
                </div>
            </div>
            <BButton
                class="merge-sections-button"
                @click="merging = true"
                :disabled="!canMerge"
                variant="outline-primary"
                size="lg"
            >
                <i class="icon icon--m0 icon--merge"></i>
                Selectie samenvoegen
            </BButton>
        </BFormGroup>
        <SectionMergeModal
            v-if="merging"
            :sections="selectedSections"
            @on-merge="triggerMerge"
            @on-hide="merging = false"
        />
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { CurrentSection } from '../sectionManagementTypes';
import Highlight from '@/components/utils/Highlight/Highlight.vue';
import SectionMergeModal from '../SectionMergeModal/SectionMergeModal.vue';
import { mapRootGetters } from '@/utils/vuex';

export default defineComponent({
    name: 'SectionManager',
    components: {
        Highlight,
        SectionMergeModal,
    },
    data() {
        return {
            changeLabelSection: null as CurrentSection | null,
            labelToChange: '',
            merging: false,
            searchString: '',
            selectedSections: [] as CurrentSection[],
        };
    },
    computed: {
        ...mapRootGetters({
            sections: 'place/currentSections',
        }),
        canAdd(): boolean {
            return (
                !!this.searchString.length &&
                !this.filteredSections.some((section) => {
                    return section.label.toLowerCase() === this.searchString.toLowerCase();
                })
            );
        },
        canMerge() {
            return this.selectedSections.length > 1;
        },
        filteredSections() {
            const filterFunc = (section: CurrentSection) =>
                section.label.toLowerCase().includes(this.searchString.toLowerCase());
            const sortFunc = (a: CurrentSection, b: CurrentSection) => {
                if (a.uuid === 'no-section-entry-uuid') return -1;
                if (b.uuid === 'no-section-entry-uuid') return 1;
                if (a.label < b.label) return -1;
                if (a.label > b.label) return 1;
                return 0;
            };
            return this.sections.filter(filterFunc).sort(sortFunc);
        },
    },
    methods: {
        addSection() {
            if (!this.canAdd) return;
            this.$store.commit('place/ADD_SECTION', this.searchString);
            this.searchString = '';
        },
        changeLabel(uuid: CurrentSection['uuid']) {
            this.$store.commit('place/CHANGE_SECTION_LABEL', { label: this.labelToChange, uuid });
            this.changeLabelSection = null;
        },
        indexTitle(indexCount: number, hasCalculatedIndex: boolean) {
            return indexCount > 1 ? `${hasCalculatedIndex ? 'of minder ' : ''}indexen` : 'index';
        },
        isSelected(uuid: CurrentSection['uuid']) {
            return this.selectedSections.some((sS) => sS.uuid === uuid);
        },
        toggleSection(section: CurrentSection) {
            const selectedSectionIndex = this.selectedSections.findIndex((sS) => sS.uuid === section.uuid);
            if (selectedSectionIndex >= 0) {
                this.selectedSections.splice(selectedSectionIndex, 1);
            } else {
                this.selectedSections.push(section);
            }
        },
        triggerMerge(mainSection: CurrentSection, mergeSections: CurrentSection[]) {
            this.$store.commit('place/MERGE_SECTIONS', { mainSection, mergeSections });
            this.selectedSections = [];
        },
    },
    watch: {
        changeLabelSection(activatedSection) {
            // Triggered when a section's changeLabelForm is activated or deactivated(reset)
            // changeLabelForm uses the activated section's label as its initial value for editing
            activatedSection === null ? (this.labelToChange = '') : (this.labelToChange = activatedSection.label);
        },
    },
});
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';
.form-group {
    margin-bottom: 0;
    ::v-deep {
        legend {
            font-weight: 500;
        }
    }
    .sections {
        border: 1px solid $bco-purple-light;
        border-radius: $border-radius-small;
        margin-bottom: 1rem;

        .sections-input {
            padding: 5px;
            width: 100%;

            input {
                border: none;
                outline: none;
                box-shadow: none;
                width: 100%;
                font-size: 14px;
            }
        }

        .sections-menu {
            max-height: 216px;
            border-top: 1px solid $bco-purple-light;
            overflow: auto;
            box-sizing: content-box;

            .section {
                position: relative;
                display: flex;
                justify-content: space-between;
                align-items: baseline;
                &.hidden {
                    /* Temporarily hide "no-section-entry" with "hidden" class until merging is possible.
                    Ticket for merging with "no-section-entry": https://egeniq.atlassian.net/browse/DBCO-3687 */
                    display: none;
                    border-bottom: 1px solid $bco-purple-light;
                }

                .option-description {
                    padding-left: 12px;
                }

                .change-label-trigger {
                    margin-right: 0.5rem;
                }

                .label-change-button {
                    background: none;
                    color: $primary;
                    border: none;
                    font-weight: 500;
                    font-size: 14px;
                }

                &:not(:hover, :focus, :active, :focus-visible, :focus-within) {
                    .hide-until-active {
                        visibility: hidden;
                    }
                }

                .change-label-form {
                    position: absolute;
                    top: 0;
                    right: 0;
                    bottom: 0;
                    left: 2.75rem;
                    background-color: $white;

                    input.form-control {
                        max-width: 194px;
                        margin-right: 0.25rem;
                        padding: 0.75rem 1rem;
                        font-size: 14px;
                        line-height: 1.05;
                        height: auto;

                        &:focus {
                            box-shadow: none;
                            border-color: $primary;
                        }
                    }
                }
            }
        }
    }
    .merge-sections-button {
        border-color: $bco-purple-light;
        font-weight: 500;
        color: $primary;
        .icon {
            background-color: $primary;
        }
        &:hover {
            color: $white;
        }
        &:disabled {
            color: $dark-grey;
            opacity: 1;
            pointer-events: none;
            .icon {
                background-color: $dark-grey;
            }
        }
    }
}
</style>
