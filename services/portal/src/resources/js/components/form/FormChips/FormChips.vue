<template>
    <div
        :class="`formulate-input-element formulate-input-element--${context.type}`"
        :data-type="context.type"
        v-click-outside="outside"
    >
        <!-- eslint-disable-next-line vuejs-accessibility/click-events-have-key-events, vuejs-accessibility/no-static-element-interactions -->
        <div :class="['chips form-control', { open: isOpen }]" @click="toggleOpen">
            <div class="placeholder container" v-if="!model.length"></div>
            <FormulateInput
                v-for="button in buttons"
                :key="button.value"
                :label="button.label"
                ignored
                class="chip"
                :disabled="disabled"
                @click.stop="onButton(button)"
                type="button"
                data-testid="chip"
                v-slot:default="context"
            >
                <FormLabel :context="context" :class="{ 'pr-3': !disabled }" />
                <i v-if="!disabled" class="icon icon--delete-circle"></i>
            </FormulateInput>
            <div class="control">
                <i
                    class="icon icon--arrow-down"
                    :class="{
                        open: isOpen,
                    }"
                ></i>
            </div>
        </div>
        <div class="pt-3" v-show="isOpen" data-testid="container-options">
            <FormulateInput
                v-model="search"
                ref="searchInput"
                type="search"
                :placeholder="context.attributes.placeholder"
                ignored
            />
            <div class="list" v-if="isOpen">
                <FormulateInput v-model="model" :options="filtered" type="checkbox" ignored />
            </div>
            <div v-if="hasNoResults">Geen resultaten</div>
        </div>
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import FormLabel from '../FormLabel/FormLabel.vue';
import type { VueFormulateContext } from '../ts/formTypes';

interface Button {
    label: string;
    value: string;
}

export default defineComponent({
    name: 'FormChips',
    components: { FormLabel },
    props: {
        disabled: {
            type: Boolean,
            required: false,
        },
        context: {
            type: Object as PropType<VueFormulateContext & { options: { value: string; label: string }[] }>,
            required: true,
        },
    },
    inject: ['rootModel'],
    data() {
        return {
            isOpen: false,
            search: '',
        };
    },
    methods: {
        onButton({ value }: Button) {
            const index = this.model.indexOf(value);
            if (index < 0) return;
            const newModel = [...this.model];
            newModel.splice(index, 1);

            this.context.model = newModel;
            this.$emit('change');
        },
        outside() {
            this.isOpen = false;
        },
        toggleOpen() {
            if (this.disabled) return;
            this.isOpen = !this.isOpen;

            if (this.isOpen) {
                // Focus search input on open
                this.$nextTick(() => (this.$refs as any).searchInput.$el.querySelector('input')?.focus());
            }
        },
    },
    computed: {
        model: {
            get() {
                // If this field is part of a repeatable, use the local modal
                // Since we do not know which index this is in the repeatable
                if (this.context.isSubField()) {
                    return this.context.model || [];
                }

                // Getting data from store, because VueFormulate does not update the checkbox correctly
                return (this as any).rootModel()[this.context.name] || [];
            },
            set(value: any) {
                // Ignore any other value than null or []
                if (!Array.isArray(value) && value !== null) return;

                this.context.model = value;
            },
        },
        buttons() {
            const { options } = this.context;
            return this.model.map((key: any) => {
                const option = options.find(({ value }) => key === value);
                return {
                    label: option ? option.label : key,
                    value: key,
                };
            });
        },
        filtered() {
            const { options } = this.context;
            const search = this.search.toLowerCase();
            if (!search) return options;
            const filter = Object.values(options)
                .map(({ label, value }) => {
                    const result = `${label.toLowerCase()}-${value.toLowerCase()}`;
                    if (result.indexOf(search) !== -1) return value;
                    return null;
                })
                .filter((value) => value !== null);
            const transform = filter.reduce((cul, cur) => {
                const result = options.find(({ value }) => {
                    return value === cur;
                });
                if (result) cul[result.value] = result.label;
                return cul;
            }, {});
            return transform;
        },
        hasNoResults() {
            if (this.filtered === null) return false;
            return !!this.search && Object.values(this.filtered).length === 0;
        },
    },
});
</script>

<style lang="scss" scoped>
.form-control {
    padding: 3px 28px 1px 2px;
}

.chips {
    position: relative;
    font-size: 0;
    height: 100%;
    padding: 4px;

    .placeholder {
        position: relative;
        height: 2rem;
    }

    &.open {
        border: 1px solid #5616ff;
        box-shadow:
            0px 0px 6px rgba(86, 22, 255, 0.25),
            0px 1px 2px rgba(0, 0, 0, 0.03);
    }
}

.control {
    position: absolute;
    right: 0;
    top: 0;
    width: 28px;
    height: 100%;

    .icon {
        position: relative;
        top: 12px;
        width: 8px;
    }
}

.btn {
    height: 28px;
}

.list {
    max-height: 200px;
    overflow-y: auto;
    position: relative;
}
</style>
