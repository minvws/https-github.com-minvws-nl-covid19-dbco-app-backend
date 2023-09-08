<template>
    <BDropdown
        ref="dropdown"
        size="lg"
        variant="link"
        :toggle-class="['text-decoration-none', toggleClass, 'p-0']"
        :right="right"
        lazy
        @show="openDropdown()"
    >
        <template #button-content>
            <slot></slot>
        </template>
        <div v-if="loading" class="text-center">
            <BSpinner variant="primary" small />
        </div>
        <!-- eslint-disable-next-line vuejs-accessibility/click-events-have-key-events, vuejs-accessibility/no-static-element-interactions -->
        <div
            v-if="openOption"
            class="d-flex align-items-center dropdown-option text-primary has-prepend-icon"
            key="back"
            @click.stop="openOption = undefined"
        >
            <i class="icon icon--caret-left icon--center icon--sm ml-0" />
            <span class="flex-grow-1">Terug</span>
        </div>
        <template v-for="(option, index) in visibleOptions">
            <BDropdownDivider :key="index" v-if="option.type === dropdownOptionType.DIVIDER" />
            <!-- eslint-disable-next-line vuejs-accessibility/click-events-have-key-events, vuejs-accessibility/no-static-element-interactions -->
            <div
                v-else-if="option.type === dropdownOptionType.MENU"
                :id="option.label + '-' + index"
                class="d-flex align-items-center dropdown-option"
                @click.stop="openOption = option"
                :key="index"
            >
                <span class="flex-grow-1">{{ option.label }}</span>
                <i class="icon icon--caret-right icon--center icon--sm mr-0" />
            </div>
            <!-- eslint-disable-next-line vuejs-accessibility/click-events-have-key-events, vuejs-accessibility/no-static-element-interactions -->
            <div
                v-else-if="option.type === dropdownOptionType.ITEM"
                :id="option.label + '-' + index"
                class="d-flex align-items-center dropdown-option"
                :class="{ 'has-prepend-icon': option.isSelected }"
                @click="onClick(option, $event)"
                :key="index"
            >
                <slot v-if="option.slot" :name="option.slot"></slot>
                <span v-else>
                    <i v-if="option.isSelected" class="icon icon--checkmark icon--center ml-0" />
                    <span class="flex-grow-1">{{ option.label }}</span>
                </span>
            </div>
        </template>
    </BDropdown>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { BDropdown } from 'bootstrap-vue';

export enum DropdownOptionType {
    MENU = 'MENU',
    ITEM = 'ITEM',
    DIVIDER = 'DIVIDER',
}

export type DropdownOption = DropdownOptionMenu | DropdownOptionItem | DropdownOptionDivider;

interface DropdownOptionMenu {
    type: DropdownOptionType.MENU;
    options: DropdownOption[];
    label: string;
}

interface DropdownOptionItem {
    type: DropdownOptionType.ITEM;
    label: string;
    /**
     * Use a slot to render the named slot template, which was provided to the component
     */
    slot?: string;
    isSelected?: boolean;
    href?: string;
    onClick?: ($event: Event) => void;
}

interface DropdownOptionDivider {
    type: DropdownOptionType.DIVIDER;
}

export default defineComponent({
    name: 'DbcoMultilevelDropdown',
    props: {
        toggleClass: {
            type: String,
            default: 'text-primary',
        },
        right: {
            default: false,
        },
        options: {
            type: Array as PropType<DropdownOption[]>,
            required: true,
        },
    },
    data() {
        return {
            loading: false,
            openOption: undefined as DropdownOptionMenu | undefined,
            dropdownOptionType: DropdownOptionType,
        };
    },
    computed: {
        visibleOptions() {
            if (this.openOption) {
                return this.openOption.options;
            }

            return this.options;
        },
    },
    watch: {
        openOption: function () {
            (this.$refs.dropdown as BDropdown).updatePopper();
        },
    },
    methods: {
        onClick(option: DropdownOptionItem, $event: Event) {
            if (option.onClick) {
                option.onClick($event);
            }
            if (option.href) {
                window.location.assign(option.href);
            }
        },
        openDropdown() {
            this.openOption = undefined;
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

        .dropdown-menu {
            .dropdown-option {
                font-size: 0.875rem;
                padding: 0.875rem 0.75rem 0.875rem 2rem;
                cursor: pointer;
                max-width: 400px;
                color: $black;

                span {
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                &.has-prepend-icon {
                    padding: 0.5rem;
                }

                &:hover {
                    background-color: $dropdown-link-hover-bg;
                }
            }
        }
    }
}
</style>
