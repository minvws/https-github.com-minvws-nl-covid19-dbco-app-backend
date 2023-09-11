<template>
    <div :class="`formulate-input-element formulate-input-element--${context.type}`" :data-type="context.type">
        <BDropdown
            v-bind="context.attributes"
            ref="dropdown"
            variant="transparent"
            class="form-control w-100 p-0"
            menu-class="w-100"
            no-caret
            @shown="focus"
            @hidden="search = ''"
            :disabled="disabled"
            data-testid="dropdown"
        >
            <template #button-content>
                <div class="d-flex flex-wrap align-items-center p-1">
                    <FormulateInput
                        v-for="button in buttons"
                        :key="button.value"
                        :label="button.label"
                        ignored
                        class="chip"
                        :class="{ chip__disabled: !isSelectable(button) }"
                        @click.stop="onButton(button)"
                        type="button"
                        v-slot:default="context"
                        :data-testid="`selected-label-${button.value}`"
                        :disabled="disabled"
                    >
                        <FormLabel
                            data-testid="form-label"
                            :context="context"
                            :class="[isSelectable(button) && !disabled ? 'pr-3' : 'text-muted']"
                        />
                        <i v-if="isSelectable(button) && !disabled" class="icon icon--delete-circle"></i>
                    </FormulateInput>
                    <FormulateInput
                        v-if="filterEnabled && !disabled"
                        v-model="search"
                        :placeholder="placeholder"
                        ref="searchInput"
                        type="text"
                        ignored
                        :data-testid="`${context.name}-filter-input`"
                    />
                </div>
            </template>

            <div class="dropdown-menu-wrapper px-3 py-2">
                <fieldset v-for="(options, label) in filtered" :key="label" class="dropdown-group">
                    <legend v-if="label" class="group-label">{{ label }}</legend>
                    <label
                        class="option"
                        v-for="option in options"
                        :key="option.value"
                        :for="`${context.name}-${option.value}`"
                    >
                        <BFormCheckbox
                            v-model="model"
                            :name="context.name"
                            v-bind="$as.any({ value: option.value })"
                            :disabled="!isSelectable(option)"
                            @change="focus"
                            :id="`${context.name}-${option.value}`"
                        />
                        <div class="label">
                            {{ option.label }}
                            <span v-if="option.description">{{ option.description }}</span>
                        </div>
                    </label>
                </fieldset>
                <div v-if="hasNoResults" class="no-results py-1">Geen resultaten gevonden</div>
            </div>
        </BDropdown>
    </div>
</template>

<script>
import FormLabel from '@/components/form/FormLabel/FormLabel.vue';
export default {
    name: 'FormMultiSelectDropdown',
    components: { FormLabel },
    props: {
        context: {
            type: Object,
            required: true,
        },
        filterEnabled: {
            type: Boolean,
            required: false,
            default: false,
        },
        listGroups: {
            type: Object,
            required: false,
        },
        listOptions: {
            type: Array,
            required: true,
        },
        disabled: {
            type: Boolean,
            required: false,
        },
    },
    data() {
        return {
            search: '',
        };
    },
    methods: {
        focus() {
            this.$refs.searchInput?.$el.querySelector('input').focus();
        },
        isSelectable(item) {
            // If no property, then it is selectable by default
            if (!item || !item.hasOwnProperty('is_selectable')) return true;

            return item.is_selectable;
        },
        onButton({ value, is_selectable }) {
            if (!is_selectable) return;

            const index = this.model.indexOf(value);
            if (index < 0) return;
            const newModel = [...this.model];
            newModel.splice(index, 1);
            this.context.model = newModel;
            this.$emit('change');
        },
    },
    computed: {
        model: {
            get() {
                return this.context.model || [];
            },
            set(value) {
                this.context.model = value;
            },
        },
        buttons() {
            return this.model.map((key) => {
                const match = this.listOptions.find(({ value }) => key === value);
                return {
                    is_selectable: this.isSelectable(match),
                    label: match ? match.label : key,
                    value: key,
                };
            });
        },
        filtered() {
            const results = {};
            const groups = { ['default']: '', ...this.listGroups };
            const search = this.search.toLowerCase();
            for (const [key, label] of Object.entries(groups)) {
                const groupItems = this.listOptions.filter(
                    (option) =>
                        // If groups defined, try to find group or put in default group
                        // If no groups defined, put everything in default group
                        (this.listGroups ? option.group || 'default' : 'default') === key &&
                        // Filter on search term
                        option.label.toLowerCase().indexOf(search) !== -1
                );
                // Only add group if there are results in the group
                if (groupItems.length === 0) continue;
                results[label] = groupItems;
            }
            // Updates dropdown position
            this.$refs.dropdown?.updatePopper();
            return results;
        },
        hasNoResults() {
            return !Object.values(this.filtered).length;
        },
        placeholder() {
            return this.model.length === 0 ? this.context.attributes.placeholder : '';
        },
    },
};
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.form-control {
    background: #ffffff
        url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='4' height='5' viewBox='0 0 4 5'%3e%3cpath fill='%23343a40' d='M2 0L0 2h4zm0 5L0 3h4z'/%3e%3c/svg%3e")
        right 0.75rem center/8px 10px no-repeat;

    ::v-deep {
        input,
        input:focus {
            border: none;
            box-shadow: none;
            margin: 0.125rem;
            min-height: 1.625rem;
            outline: none;
            padding: 0;
        }

        button {
            padding: 0;
        }

        .custom-control {
            align-self: flex-start;
        }

        .formulate-input {
            margin: 0;
        }
    }

    .dropdown-menu-wrapper {
        max-height: 300px;
        overflow: auto;
    }

    .group-label {
        color: $light-grey;
        text-transform: uppercase;
    }

    .no-results {
        font-size: 0.875rem;
        font-weight: 500;
        text-align: center;
    }

    .option {
        align-items: center;
        display: flex;
        font-size: 0.875rem;
        font-weight: normal;

        span {
            color: $light-grey;
            display: block;
            font-size: 0.75rem;
            line-height: 1rem;
        }
    }
}

::v-deep {
    .dropdown.form-control {
        height: inherit;
    }
}
</style>
