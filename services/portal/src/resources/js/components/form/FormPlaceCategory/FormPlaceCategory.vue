<template>
    <BDropdown ref="category_dropdown" dropup variant="link" class="form-control dbco-dropdown w-100 p-1" no-caret>
        <template #button-content>
            <div class="dropdown-button-wrapper d-flex justify-content-between align-items-center">
                <div>
                    <i
                        :class="[
                            'icon',
                            'icon--xl',
                            'icon--m0',
                            $filters.placeCategoryImageClass(selectedCategory ? selectedCategory.group : null),
                        ]"
                    />
                </div>
                <div class="title flex-fill">
                    {{ selectedCategory ? selectedCategory.label : 'Kies categorie' }}
                </div>
                <div>
                    <i class="icon icon--arrow-down" />
                </div>
            </div>
        </template>

        <div class="dropdown-menu-wrapper" role="listbox">
            <div v-for="(category, group) in categories" :key="group" class="dropdown-group">
                <template v-if="category.values">
                    <button
                        type="button"
                        data-testid="categoryDropdown"
                        :aria-expanded="open === group"
                        role="listitem"
                        :class="['dropdown-group-header d-flex align-items-center', { 'is-open': open === group }]"
                        @click="toggleGroup(group)"
                    >
                        <div class="caret">
                            <i class="icon icon--caret-right" />
                        </div>
                        <div class="title flex-fill text-left">
                            {{ category.title }}
                        </div>
                    </button>

                    <template v-if="open === group">
                        <div class="dropdown-sub-menu" data-testid="categorySelectors">
                            <BDropdownItem
                                v-for="(subcategory, value) in category.values"
                                :key="value"
                                :data-testid="'testID' + value"
                                @click="selectCategory({ group, label: subcategory.label, value })"
                            >
                                {{ subcategory.label }}
                                <span>{{ subcategory.description }}</span>
                            </BDropdownItem>
                        </div>
                    </template>
                </template>
                <template v-else>
                    <BDropdownItem
                        data-testid="groupSelector"
                        link-class="dropdown-group-item d-flex align-items-center"
                        @click="selectCategory({ group, label: category.title, value: group })"
                    >
                        <div class="title flex-fill text-left">
                            {{ category.title }}
                        </div>
                    </BDropdownItem>
                </template>
            </div>
        </div>
    </BDropdown>
</template>

<script>
import { contextCategoryByGroup } from '../ts/formOptions';
import { contextCategoryV1Options, contextCategoryGroupV1Options } from '@dbco/enum';

export default {
    name: 'FormPlaceCategory',
    props: {
        context: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            categories: contextCategoryByGroup(contextCategoryV1Options, contextCategoryGroupV1Options),
            open: null,
            selectedCategory: null,
        };
    },
    created() {
        if (!this.context.model) return;
        Object.entries(this.categories).forEach(([group, category]) => {
            if (!category.values) {
                if (group !== this.context.model) return;
                this.selectedCategory = { group, label: category.title, value: group };
                return;
            }

            Object.entries(category.values).forEach(([value, subcategory]) => {
                if (value !== this.context.model) return;
                this.selectedCategory = { group, label: subcategory.label, value };
            });
        });
    },
    methods: {
        selectCategory(category) {
            this.selectedCategory = category;
            this.context.model = category.value;
        },
        toggleGroup(group) {
            this.open = this.open === group ? null : group;
            this.$refs.category_dropdown.updatePopper();
        },
    },
};
</script>

<style lang="scss" scoped>
@import '@/../scss/variables';

.dbco-dropdown {
    height: inherit;

    .title {
        font-size: 0.875rem;
        font-weight: bold;
        padding: 8px 0;
    }
}

::v-deep .dropdown-toggle {
    .dropdown-button-wrapper {
        padding: 0 0.5rem;
        height: 100%;

        > div {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;

            .icon.icon--arrow-down {
                width: 8px;
            }
        }
    }
}

.dropdown-menu-wrapper {
    padding: 8px;
    // Make sure the content will scroll when the browser window is really small
    max-height: 96vh;
    overflow-y: auto;

    .dropdown-group {
        .dropdown-group-header {
            background: none;
            border: none;
            cursor: pointer;
            &:focus,
            &:hover {
                background-color: #f8f9fa;
            }

            > div {
                margin-left: 8px;
            }

            &.is-open .caret .icon {
                margin-bottom: 0;
                transform: rotate(90deg);
            }

            .caret .icon {
                width: 0.5em;
                height: 0.5em;
                margin: 0 0 2px;
            }
        }

        ::v-deep .dropdown-group-item {
            padding: 0;
            font-size: 1rem;

            &:hover {
                background-color: #f8f9fa;
            }

            > div {
                margin-left: 8px;
            }

            .caret .icon {
                width: 0.5em;
                height: 0.5em;
                margin: 0;
            }
        }

        ::v-deep .dropdown-sub-menu {
            .dropdown-item {
                font-weight: normal;
                line-height: 1.25rem;
                padding: 0.5rem 1.5rem;

                &:active,
                &:hover {
                    background-color: #f8f9fa;
                    color: inherit;
                }

                span {
                    color: $light-grey;
                    display: block;
                    font-size: 0.75rem;
                    line-height: 1rem;
                }
            }
        }
    }
}
</style>
