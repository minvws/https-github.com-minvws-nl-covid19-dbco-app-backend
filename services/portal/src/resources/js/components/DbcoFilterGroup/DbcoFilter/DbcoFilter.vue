<template>
    <div class="filter">
        <label :for="type" class="m-0">{{ label }}</label>
        <BDropdown
            ref="dropdown"
            :id="type"
            :text="selectedOption ? selectedOption.label : ''"
            variant="link"
            :disabled="disabled"
            toggle-class="px-2 text-primary text-decoration-none"
        >
            <div class="d-flex align-items-center px-2 pb-2" v-if="searchable">
                <BInputGroup>
                    <BFormInput
                        autocomplete="off"
                        v-model="searchString"
                        :placeholder="searchPlaceholderText"
                        debounce="500"
                        ref="searchInput"
                        @keyup.enter="selectSearchOption"
                        class="border-right-0"
                    />
                    <BInputGroupAppend is-text class="border-left-0">
                        <img :src="iconSearchSvg" alt="" />
                    </BInputGroupAppend>
                </BInputGroup>
            </div>
            <template v-for="(option, index) in visibleOptions">
                <!-- eslint-disable-next-line vuejs-accessibility/no-static-element-interactions -->
                <div
                    :id="option.label + '-' + index"
                    class="d-flex align-items-center dropdown-option"
                    :class="{ 'has-prepend-icon': option.value === selected }"
                    @click="selectFilterOption(option.value)"
                    @keydown.enter="selectFilterOption(option.value)"
                    :key="index"
                    tabindex="0"
                >
                    <span>
                        <i v-if="option.value === selected" class="icon icon--checkmark icon--center ml-0" />
                        <span class="flex-grow-1">{{ option.label }}</span>
                    </span>
                </div>
            </template>
        </BDropdown>
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { BDropdown, BFormInput } from 'bootstrap-vue';
import iconSearchSvg from '@images/icon-search.svg';

export type FilterOption = { value: string | null; label: string };

export default defineComponent({
    name: 'DbcoFilter',
    props: {
        type: {
            type: String,
            required: true,
        },
        searchable: {
            type: Boolean,
            default: false,
        },
        searchPlaceholder: {
            type: String,
            required: false,
        },
        selected: {
            default: null,
            type: String as PropType<string | null | Record<string, number>>,
            required: false,
        },
        label: {
            type: String,
            required: true,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        options: {
            type: Array as PropType<FilterOption[]>,
            required: true,
        },
    },
    data() {
        return {
            searchString: null as string | null,
            iconSearchSvg,
        };
    },
    mounted() {
        if (this.searchable) {
            (this.$refs.dropdown as BDropdown).$on('shown', this.focusSearchInput);
        }
    },
    computed: {
        visibleOptions() {
            return this.searchString && this.searchable
                ? this.options.filter((option) =>
                      option.label.toLowerCase().includes(this.searchString?.toLowerCase() ?? '')
                  )
                : this.options;
        },
        selectedOption() {
            return this.options.find((o) => o.value === this.selected);
        },
        searchPlaceholderText() {
            return this.searchPlaceholder ?? `Zoek naar ${this.type}`;
        },
    },
    methods: {
        resetSearch() {
            this.searchString = null;
        },
        selectFilterOption(value: string | null) {
            if (this.searchable) {
                this.resetSearch();
            }
            (this.$refs.dropdown as BDropdown).hide();
            this.$parent!.$emit('selected', { value, type: this.type });
        },
        selectSearchOption() {
            if (this.searchString && this.visibleOptions.length) {
                const [firstOption] = this.visibleOptions;
                this.selectFilterOption(firstOption.value);
            }
        },
        focusSearchInput() {
            (this.$refs.searchInput as BFormInput).focus();
        },
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';
.filter {
    display: flex;
    align-items: center;
    padding: 0 $padding-sm;
    border-right: 1px solid $lightest-grey;
    &:last-child {
        border-right: none;
    }
    label {
        color: $light-grey;
    }
    .b-dropdown {
        ::v-deep {
            .dropdown-menu {
                padding: $padding-xs 0;
                max-height: 22.5rem;
                overflow: auto;
                .dropdown-option {
                    font-size: 0.875rem;
                    padding: $padding-xs $padding-xs $padding-xs $padding-md;
                    cursor: pointer;
                    max-width: 25rem;
                    span {
                        overflow: hidden;
                        text-overflow: ellipsis;
                    }
                    &.has-prepend-icon {
                        padding: $padding-xs;
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
            }
        }
    }
}
</style>
