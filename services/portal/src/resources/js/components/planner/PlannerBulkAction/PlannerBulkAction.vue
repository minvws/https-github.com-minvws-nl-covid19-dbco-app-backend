<template>
    <div key="test" class="bulk-actionbar-wrapper">
        <div class="bulk-actionbar d-flex justify-content-between align-items-center">
            <div class="actions">
                <DbcoAssignDropdown
                    v-if="assignable"
                    :uuid="selected"
                    :staleSince="staleSince"
                    title="Toewijzen"
                    toggleClass="text-white p-2"
                    dropup
                    @optionSelected="assigned($event)"
                    @assignErrors="showAssignErrors"
                />
                <BDropdown
                    size="lg"
                    variant="link"
                    :toggle-class="['text-decoration-none', 'text-white', 'p-2', 'text-left']"
                    :dropup="true"
                    lazy
                    text="Prioriteit"
                >
                    <BDropdownItem
                        v-for="(priority, key) in priorities"
                        :data-testid="`dropdown-item-priority-${key}`"
                        :key="`priority-${key}`"
                        @click="updatePriority(key)"
                    >
                        <i class="icon icon--center icon--m0 mr-2" :class="['icon--priority-' + key]" />
                        {{ priority }}
                    </BDropdownItem>
                </BDropdown>
                <DbcoPhaseDropdown
                    class="dropup"
                    toggleClass="text-white p-2"
                    :cases="selected"
                    @phaseChanged="$emit('phaseChanged')"
                />
                <BButton v-if="archiveable" class="archive-button" @click="archive">Sluiten</BButton>
            </div>
            <div class="d-flex align-items-center text-nowrap">
                {{ selected.length }}
                {{ selected.length > 1 ? 'cases' : 'case' }}
                <a
                    href="javascript:void(0)"
                    class="close-button ml-3"
                    @click="clear"
                    @keyup.esc="clear"
                    aria-label="close"
                    ref="closeButton"
                >
                    <i class="icon icon--close icon--lg"
                /></a>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import type { AssignmentConflict } from '@/components/form/ts/formTypes';
import type { AssignmentResult } from '@dbco/portal-api/assignment';
import DbcoAssignDropdown from '@/components/formControls/DbcoAssignDropdown/DbcoAssignDropdown.vue';
import DbcoPhaseDropdown from '@/components/utils/DbcoPhaseDropdown/DbcoPhaseDropdown.vue';
import { priorityV1Options } from '@dbco/enum';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'PlannerBulkAction',
    components: {
        DbcoAssignDropdown,
        DbcoPhaseDropdown,
    },
    props: {
        archiveable: {
            type: Boolean,
            required: true,
        },
        assignable: {
            type: Boolean,
            required: true,
        },
        selected: {
            type: Array as PropType<string[]>,
            required: true,
        },
        staleSince: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            priorities: priorityV1Options,
        };
    },
    computed: {},
    methods: {
        archive() {
            this.$emit('onArchive');
        },
        assigned(assignment: AssignmentResult) {
            this.$emit('onAssign', assignment);
        },
        showAssignErrors(description: string, errors: AssignmentConflict[]) {
            this.$emit('assignErrors', description, errors);
        },
        clear() {
            this.$emit('onClear');
        },
        updatePriority(priority: string) {
            this.$emit('onUpdatePriority', priority);
        },
    },
    mounted() {
        (this.$refs.closeButton as HTMLAnchorElement).focus();
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.bulk-actionbar-wrapper {
    position: fixed;
    left: 0;
    bottom: 1rem;
    padding: 0 1rem;
    width: 100%;
    pointer-events: none;
    z-index: 1;

    .bulk-actionbar {
        background: $black;
        border-radius: $border-radius-medium;
        color: white;
        gap: 1rem;
        margin: 0 auto;
        padding: 0.5rem;
        pointer-events: all;
        max-width: 660px;
        width: 100%;

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem 0;

            .show,
            :focus {
                z-index: 2;
            }

            > * {
                border: 1px solid $grey-on-black;
                white-space: nowrap;

                ::v-deep button {
                    font-weight: 500;
                }

                &:not(:first-child) {
                    margin-left: -1px;
                }

                &:first-child {
                    border-radius: $border-radius-small 0 0 $border-radius-small;
                }

                &:last-child {
                    border-radius: 0 $border-radius-small $border-radius-small 0;
                }
            }
        }

        ::v-deep {
            .b-dropdown {
                &.show {
                    border-color: #ffffff;

                    .icon--priority-3 {
                        vertical-align: top;
                        height: 1.3rem;
                    }
                }

                .btn-link:after {
                    color: $lightest-grey-on-black;
                }
            }

            .archive-button {
                display: inline-block;
                font-size: 0.875rem;
                line-height: 1.5;
                padding: 0.563rem 0.5rem;
                color: $white;
                background: none;

                &:active {
                    border-color: $white;
                    color: $lightest-grey-on-black;
                }

                &:focus {
                    outline: 0;
                    box-shadow: 0 0 0 0.2rem rgb(86 22 255 / 25%);
                }
            }
        }
    }
}
</style>
