<template>
    <div class="place">
        <div class="place-content">
            <div class="p-0">
                <i
                    :class="[
                        'icon',
                        'icon--xl',
                        'icon--m0',
                        'm-0',
                        'mr-3',
                        $filters.placeCategoryImageClass(value.category),
                    ]"
                />
            </div>
            <div>
                <strong><Highlight :text="value.label" :query="searchString" /></strong>
                <span v-if="value.indexCount === 1" data-testid="indexCount-span">- 1 index</span>
                <span v-if="value.indexCount > 1" data-testid="indexCount-span">- {{ value.indexCount }} indexen</span>
                <br />
                <span v-if="value.category" data-testid="category-span">{{ formattedCategory }}</span>
                <span v-if="showSeparator" data-testid="category-separator-span"> • </span>
                <Highlight v-if="address" :text="address" :query="searchString" data-testid="address" />
                <span v-if="value.isVerified" class="verified"><CheckIcon class="mr-1" />geverifieerd</span>
            </div>
        </div>
        <div>
            <BButton
                v-if="isEditable"
                :disabled="disabled"
                data-testid="edit-place-button"
                @click="$emit('edit')"
                class="mr-3 w-auto"
                variant="outline-primary"
                >Wijzigen</BButton
            >
            <BButton
                v-if="isCancellable"
                :disabled="disabled"
                @click="showModal()"
                class="w-auto"
                variant="outline-danger"
                data-testid="deconnect-place-button"
                >Ontkoppelen</BButton
            >
        </div>
    </div>
</template>

<script lang="ts">
import { startCase } from 'lodash';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import Highlight from '@/components/utils/Highlight/Highlight.vue';
import type { ContextCategoryV1 } from '@dbco/enum';
import { contextCategoryV1Options } from '@dbco/enum';
import CheckIcon from '@icons/check.svg?vue';
export default defineComponent({
    name: 'Place',
    components: {
        Highlight,
        CheckIcon,
    },
    props: {
        value: {
            type: Object as PropType<Record<any, any>>,
            required: true,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        isCancellable: {
            type: Boolean,
            default: false,
        },
        isEditable: {
            type: Boolean,
            default: false,
        },
        searchString: {
            default: '',
        },
    },
    computed: {
        formattedCategory() {
            const category: ContextCategoryV1 = this.value.category;

            const contextCategory = contextCategoryV1Options.find(
                (contextCategory) => contextCategory.value === category
            );
            return contextCategory ? contextCategory.label : startCase(category);
        },
        address() {
            if (this.value.addressLabel) return this.value.addressLabel;
            if (!this.value.address) return '';

            const { street, houseNumber, houseNumberSuffix, postalCode, town } = this.value.address;
            return `${street || ''} ${houseNumber || ''}${houseNumberSuffix || ''}, ${postalCode || ''} ${
                town || ''
            }`.trim();
        },
        showSeparator() {
            return (
                this.value.category &&
                (this.value.address.street || this.value.address.houseNumber || this.value.address.houseNumberSuffix)
            );
        },
    },
    methods: {
        showModal() {
            this.$modal.show({
                title: 'Weet je zeker dat je de locatie wilt ontkoppelen?',
                text: 'De locatie is hierna niet meer gekoppeld aan dit dossier.',
                okTitle: 'Ontkoppelen',
                onConfirm: () => {
                    this.$emit('cancel');
                },
            });
        },
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.place {
    width: 100%;
    padding: 1rem;
    justify-content: space-between;

    &,
    .place-content {
        display: flex;
        align-items: center;
    }

    .verified {
        display: flex;
        align-items: center;
        color: $bco-info;

        svg {
            height: 0.6875rem;
            color: $bco-info;
        }
    }
}
</style>
