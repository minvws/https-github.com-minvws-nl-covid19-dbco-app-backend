<template>
    <div class="option">
        <i
            v-if="tooltip"
            class="icon icon--m0 icon--questionmark"
            data-testid="tooltip"
            v-b-tooltip="{
                container: 'body',
                title: tooltip,
                trigger: 'hover',
            }"
        />
        <span class="label">{{ label }}</span>

        <template v-if="type === InputType.Text">
            <BFormInput
                v-if="inputVisible"
                ref="input"
                v-model="localValue"
                @blur="change"
                @keydown.enter="handleKeyDown"
                class="field border-0"
                :placeholder="placeholder"
                size="sm"
                type="text"
            />
            <button v-else type="button" class="chip" @click="focus">
                <slot />
            </button>
        </template>
        <BFormSelect
            v-else-if="type === InputType.Select"
            v-model="localValue"
            @change="change"
            :class="['field', { 'placeholder-active': !localValue }]"
            :options="options"
            :placeholder="placeholder"
            size="sm"
        >
            <template #first v-if="placeholder">
                <BFormSelectOption value="" disabled hidden>{{ placeholder }}</BFormSelectOption>
            </template>
        </BFormSelect>

        <span v-if="note" class="note ml-auto">{{ note }}</span>
        <FormInfo v-if="error" class="ml-auto px-2 py-1" data-testid="error" infoType="warning" :text="error" />
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import FormInfo from '@/components/form/FormInfo/FormInfo.vue';

enum InputType {
    Text = 'text',
    Select = 'select',
}

interface SelectOption {
    value: unknown;
    text: string;
}

export default defineComponent({
    name: 'ModalOption',
    components: { FormInfo },
    props: {
        error: {
            type: String,
            required: false,
        },
        label: {
            type: String,
            required: true,
        },
        note: {
            type: String,
            required: false,
        },
        options: {
            type: Array as PropType<SelectOption[]>,
            default: () => [],
        },
        placeholder: {
            type: String,
            required: false,
        },
        tooltip: {
            type: String,
            required: false,
        },
        type: {
            type: String as PropType<`${InputType}`>,
            default: InputType.Text,
        },
        value: {
            type: String,
            required: false,
        },
    },
    model: {
        event: 'change',
    },
    data() {
        return {
            InputType,
            isFocus: false,
            localValue: this.value || '',
        };
    },
    computed: {
        inputVisible() {
            return !!this.error || this.isFocus || !this.value;
        },
    },
    methods: {
        handleKeyDown(event: KeyboardEvent) {
            (event.target as HTMLInputElement)?.blur();
        },
        change() {
            this.isFocus = false;
            this.$emit('change', this.localValue);
        },
        async focus() {
            this.isFocus = true;
            this.$emit('focus');

            // Wait for field to become visible
            await this.$nextTick();
            (this.$refs.input as HTMLInputElement).focus();
        },
    },
});
</script>
<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.option {
    align-items: center;
    border-bottom: 1px solid $lightest-grey;
    display: flex;
    font-weight: normal;
    gap: 0.5rem;
    padding: 0.5rem 0;

    &:last-child {
        border: none;
        padding-bottom: 0;
    }

    .label,
    .icon {
        flex-shrink: 0;
    }

    .label,
    .note {
        color: $light-grey;
        font-size: 0.75rem;
    }

    .field {
        flex-shrink: 1;
        width: 200px;
        cursor: pointer;

        &:focus {
            box-shadow: none;
            cursor: text;

            &::placeholder {
                visibility: hidden;
            }
        }

        &::placeholder {
            color: $bco-purple;
            font-size: 0.875rem;
            font-weight: 500;
        }
    }

    select {
        border: 1px solid $lightest-grey;

        &.placeholder-active {
            color: $lighter-grey;
        }

        ::v-deep option {
            color: $black;
        }
    }

    .chip {
        background-color: rgba($primary, 0.05);
        border: none;
        border-radius: $border-radius-medium;
        color: $black;
        cursor: pointer;
        display: inline-block;
        font-size: 0.875rem;
        height: 32px;
        padding: 0.375rem 0.5rem;
        margin-right: 4px;
    }
}
</style>
