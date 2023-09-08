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
        <!-- eslint-disable-next-line vuejs-accessibility/click-events-have-key-events, vuejs-accessibility/no-static-element-interactions -->
        <div
            v-if="openedOption !== null"
            class="d-flex align-items-center dropdown-option text-primary has-prepend-icon"
            key="back"
            @click.stop="openedOption = null"
        >
            <i class="icon icon--caret-left icon--center icon--sm ml-0" />
            <span class="flex-grow-1">Terug</span>
        </div>
        <template v-for="(option, index) in visibleOptions">
            <BDropdownDivider :key="index" v-if="option.type === 'separator'" />
            <!-- eslint-disable-next-line vuejs-accessibility/click-events-have-key-events, vuejs-accessibility/no-static-element-interactions -->
            <div
                v-else-if="option.assignmentType != 'user'"
                :id="option.label + '-' + index"
                class="d-flex align-items-center dropdown-option"
                :class="{
                    'has-prepend-icon': option.isSelected,
                    disabled: option.hasOwnProperty('isEnabled') && !option.isEnabled,
                }"
                @click.stop="selectOption(option, index)"
                :key="index"
            >
                <i v-if="option.isSelected" class="icon icon--checkmark icon--center ml-0" />
                <span class="flex-grow-1">{{ option.label }}</span>
                <i v-if="option.type === 'menu'" class="icon icon--caret-right icon--center icon--sm mr-0" />
            </div>
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
        <template v-if="userOptions.length > 0">
            <div class="d-flex align-items-center px-2 pb-2">
                <BInputGroup>
                    <BFormInput
                        autofocus
                        autocomplete="off"
                        v-model="searchString"
                        placeholder="Zoek medewerkers"
                        debounce="500"
                        class="border-right-0"
                    />
                    <BInputGroupAppend is-text class="border-left-0">
                        <img :src="iconSearchSvg" alt="search icon" aria-hidden="true" />
                    </BInputGroupAppend>
                </BInputGroup>
            </div>
            <div class="dropdown-user-wrapper">
                <template v-for="(option, index) in visibleUserOptions">
                    <!-- eslint-disable-next-line vuejs-accessibility/click-events-have-key-events, vuejs-accessibility/no-static-element-interactions -->
                    <div
                        class="d-flex align-items-center dropdown-option"
                        :class="{
                            'has-prepend-icon': option.isSelected,
                            disabled: option.hasOwnProperty('isEnabled') && !option.isEnabled,
                        }"
                        @click.stop="selectOption(option, index)"
                        :key="'user-' + index"
                    >
                        <i v-if="option.isSelected" class="icon icon--checkmark icon--center ml-0" />
                        <span class="flex-grow-1">{{ option.label }}</span>
                    </div>
                </template>
            </div>
        </template>
    </BDropdown>
</template>
<script>
import { caseApi } from '@dbco/portal-api';
import { usePlanner } from '@/store/planner/plannerStore';
import { mapWritableState } from 'pinia';
import iconSearchSvg from '@images/icon-search.svg';

export default {
    name: 'DbcoAssignDropdown',
    props: {
        uuid: {
            type: Array,
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
            options: [],
            openedOption: null,
            searchString: null,
            iconSearchSvg,
        };
    },
    computed: {
        ...mapWritableState(usePlanner, ['selectedCase']),
        visibleOptions() {
            if (this.openedOption !== null) {
                return this.options[this.openedOption].options;
            }

            return this.options;
        },
        userOptions() {
            return this.visibleOptions.filter((o) => o.assignmentType == 'user');
        },
        visibleUserOptions() {
            if (!this.searchString) return this.userOptions;
            return this.userOptions.filter((o) => o.label.toLowerCase().includes(this.searchString.toLowerCase()));
        },
        isSingle() {
            return this.uuid.length === 1;
        },
    },
    watch: {
        openedOption: function () {
            this.$refs.dropdown.updatePopper();
        },
    },
    methods: {
        async openDropdown() {
            this.options = [];
            this.openedOption = null;

            this.loading = true;
            const data = await caseApi.getAssignmentOptions(this.uuid);
            if (data.options) {
                this.options = data.options;
                this.$refs.dropdown.updatePopper();
            }
            this.loading = false;
        },
        async selectOption(option, index) {
            if (option.hasOwnProperty('isEnabled') && !option.isEnabled) return;

            if (option.type === 'menu') {
                this.openedOption = index;
            } else {
                let params = {};
                if (!this.isSingle) {
                    params['cases'] = this.uuid;
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
                    try {
                        const response = await caseApi.updateAssignment(this.uuid, params);
                        if (response.status === 200 || response.status === 204) {
                            if (this.isSingle) {
                                params = { ...params, cases: this.uuid };
                                this.selectedCase = response.data;
                            } else if (response.data) {
                                this.showAssignmentConflictModal(response.data);
                            }

                            this.$emit('optionSelected', { ...params, option });
                            this.openedOption = null;
                            this.$refs.dropdown.hide();
                        }
                    } catch (error) {
                        if (error.response) {
                            if (error.response.status === 422) {
                                this.$root.$emit('bv::show::tooltip', `tooltip-${index}`);
                            }

                            if (error.response.status === 409) {
                                this.showAssignmentConflictModal(error.response.data);
                            }
                        }
                    }
                }
            }
        },
        showAssignmentConflictModal(errors) {
            const description =
                this.uuid.length === errors.length && !this.isSingle
                    ? this.$t('components.assignmentConflictModal.description_none')
                    : this.$tc('components.assignmentConflictModal.description', errors.length);

            this.$emit('assignErrors', description, errors);
        },
    },
};
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
                background-color: #ffffff;
            }
        }
    }
}
</style>
