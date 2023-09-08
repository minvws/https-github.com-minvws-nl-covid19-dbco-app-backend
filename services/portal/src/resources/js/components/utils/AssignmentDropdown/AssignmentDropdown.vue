<template>
    <BDropdown
        ref="dropdown"
        size="lg"
        variant="link"
        :toggle-class="['text-decoration-none', toggleClass, 'text-left']"
        :block="block"
        :dropup="dropup"
        :right="right"
        :text="title"
        lazy
        @show="openDropdown()"
    >
        <div v-if="loading" class="text-center">
            <BSpinner variant="primary" small />
        </div>
        <button
            v-if="openedOption !== null"
            class="d-flex align-items-center dropdown-option text-primary has-prepend-icon"
            key="back"
            @click.stop.prevent="openedOption = null"
            type="button"
        >
            <i class="icon icon--caret-left icon--center icon--sm ml-0" />
            <span class="flex-grow-1">Terug</span>
        </button>
        <template v-for="(option, index) in visibleOptions">
            <BDropdownDivider :key="index" v-if="option.type === 'separator'" />
            <button
                v-else-if="option.assignmentType != 'user'"
                :id="option.label + '-' + index"
                class="d-flex align-items-center dropdown-option"
                :class="{
                    'has-prepend-icon': option.isSelected,
                    disabled: option.hasOwnProperty('isEnabled') && !option.isEnabled,
                }"
                @click.stop.prevent="selectOption(option, index)"
                :key="index"
                type="button"
            >
                <i v-if="option.isSelected" class="icon icon--checkmark icon--center ml-0" />
                <span class="flex-grow-1">{{ option.label }}</span>
                <i v-if="option.type === 'menu'" class="icon icon--caret-right icon--center icon--sm mr-0" />
            </button>
            <BTooltip
                :id="'tooltip-' + index"
                :target="option.label + '-' + index"
                title="Tooltip title"
                triggers="manual"
                noninteractive
                :key="'tooltip-' + index"
            >
                De toewijzing is helaas niet gelukt.<br />
                Ververs je pagina en probeer het opnieuw.
            </BTooltip>
        </template>
        <template v-if="$as.defined(userOptions).length > 0">
            <div class="d-flex align-items-center px-2 pb-2">
                <BInputGroup>
                    <BFormInput
                        autocomplete="off"
                        v-model="searchString"
                        placeholder="Zoek medewerkers"
                        debounce="500"
                        class="border-right-0"
                    />
                    <BInputGroupAppend is-text class="border-left-0">
                        <img :src="iconSearchSvg" alt="search icon" />
                    </BInputGroupAppend>
                </BInputGroup>
            </div>
            <div class="dropdown-user-wrapper">
                <template v-for="(option, index) in visibleUserOptions">
                    <button
                        class="d-flex align-items-center dropdown-option"
                        :class="{
                            'has-prepend-icon': option.isSelected,
                            disabled: option.hasOwnProperty('isEnabled') && !option.isEnabled,
                        }"
                        @click.stop.prevent="selectOption(option, index)"
                        :key="'user-' + index"
                        type="button"
                    >
                        <i v-if="option.isSelected" class="icon icon--checkmark icon--center ml-0" />
                        <span class="flex-grow-1">{{ option.label }}</span>
                    </button>
                </template>
            </div>
        </template>
    </BDropdown>
</template>

<script lang="ts">
import { usePlanner } from '@/store/planner/plannerStore';
import { mapActions, mapWritableState } from 'pinia';
import type { BDropdown } from 'bootstrap-vue';
import type { Assignment, AssignmentOption } from '@dbco/portal-api/assignment';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import iconSearchSvg from '@images/icon-search.svg';

export default defineComponent({
    name: 'AssignmentDropdown',
    props: {
        uuids: {
            type: Array as PropType<string[]>,
            required: true,
        },
        title: {
            type: String,
            default: 'Toewijzen',
        },
        toggleClass: {
            type: String,
            default: 'text-primary',
        },
        block: {
            type: Boolean,
            required: false,
        },
        dropup: {
            type: Boolean,
            required: false,
            default: false,
        },
        right: {
            type: Boolean,
            required: false,
            default: false,
        },
        staleSince: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            loading: false,
            openedOption: null as number | null,
            searchString: null as string | null,
            iconSearchSvg,
        };
    },
    destroyed() {
        this.clearAssignment();
    },
    computed: {
        ...mapWritableState(usePlanner, ['assignment']),
        isSingle() {
            return this.uuids.length === 1;
        },
        userOptions() {
            return this.visibleOptions?.filter((o) => o.assignmentType == 'user');
        },
        visibleOptions() {
            if (this.openedOption !== null && this.assignment.options) {
                return this.assignment.options[this.openedOption].options;
            }
            return this.assignment.options;
        },
        visibleUserOptions() {
            const search = this.searchString;
            if (!search) return this.userOptions;
            return this.userOptions?.filter((o) => o.label?.toLowerCase().includes(search.toLowerCase()));
        },
    },
    methods: {
        ...mapActions(usePlanner, ['clearAssignment', 'fetchAssignmentOptions']),
        async openDropdown() {
            this.assignment.options = [];
            this.openedOption = null;

            this.loading = true;
            await this.fetchAssignmentOptions(this.uuids);
            (this.$refs.dropdown as BDropdown).updatePopper();
            this.loading = false;
        },
        selectOption(option: AssignmentOption, index: number) {
            if (option.hasOwnProperty('isEnabled') && !option.isEnabled) return;

            if (option.type === 'menu') {
                this.openedOption = index;
            } else {
                let params: Assignment = {
                    staleSince: '',
                };
                if (!this.isSingle) {
                    params['cases'] = this.uuids;
                }
                if (option.assignment) {
                    if (option.assignment.hasOwnProperty('assignedUserUuid')) {
                        params = { ...params, assignedUserUuid: option.assignment.assignedUserUuid };
                    } else if (option.assignment.hasOwnProperty('assignedCaseListUuid')) {
                        params = { ...params, assignedCaseListUuid: option.assignment.assignedCaseListUuid };
                    } else if (option.assignment.hasOwnProperty('assignedOrganisationUuid')) {
                        params = { ...params, assignedOrganisationUuid: option.assignment.assignedOrganisationUuid };
                    } else if (option.assignment.hasOwnProperty('caseListUuid')) {
                        params = { ...params, assignedCaseListUuid: option.assignment.caseListUuid };
                    }

                    params.staleSince = this.staleSince;

                    this.assignment.queued = { uuids: this.uuids, params };
                    this.$emit('optionSelected', { ...params, option });
                    this.openedOption = null;
                    (this.$refs.dropdown as BDropdown).hide();
                }
            }
        },
        showAssignmentConflictModal(errors: any[]) {
            const description =
                this.uuids.length === errors.length && !this.isSingle
                    ? this.$t('components.assignmentConflictModal.description_none')
                    : this.$tc('components.assignmentConflictModal.description', errors.length);

            this.$emit('assignErrors', description, errors);
        },
    },
    watch: {
        assignment(newVal, oldVal) {
            if (newVal.conflicts !== oldVal.conflicts && newVal.conflicts.length) {
                this.showAssignmentConflictModal(newVal.conflicts);
            }
        },
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';
.b-dropdown {
    ::v-deep {
        .btn {
            &.dropdown-toggle-no-caret {
                padding: 0 0.75rem;
            }
        }

        .dropdown-toggle {
            white-space: normal;
        }

        .dropdown-menu {
            max-height: 612px;
            overflow-y: auto;
            padding: 0.5rem 0;

            button {
                background: none;
                border: none;
            }

            .dropdown-option {
                font-size: 0.875rem;
                padding: 0.5rem 0.5rem 0.5rem 2rem;
                cursor: pointer;
                max-width: 400px;

                span {
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                &.has-prepend-icon {
                    padding: 0.5rem;
                }

                &.disabled {
                    color: $grey;
                    cursor: not-allowed;

                    i {
                        color: inherit;
                    }
                }

                &:hover {
                    color: $primary;
                    background-color: $input-grey;
                }
            }

            .dropdown-user-wrapper {
                max-height: 216px;
                overflow-y: auto;
            }

            .input-group-text {
                background-color: $white;
            }
        }
    }
}
</style>
