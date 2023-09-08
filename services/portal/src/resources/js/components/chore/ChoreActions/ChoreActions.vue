<template>
    <div id="chore-actions" :class="{ ['picked-up']: pickedUp }">
        <template v-if="!pickedUp">
            <BButton variant="secondary" :disabled="loading" type="button" @click="$emit('toggle')">
                <div v-if="loading" class="loading-state-button">
                    <span><BSpinner small /></span>
                    {{ labelForPickupActionLoading }}...
                </div>
                <div v-else>
                    {{ labelForPickupAction }}
                </div>
            </BButton>
        </template>
        <template v-else>
            <Link
                class="tw-text-md tw-leading-md tw-font-medium"
                iconLeft="external-link"
                target="_blank"
                :href="viewLink"
                :ariaLabel="`verlaat de BCO Portal site: ${labelForViewLink}`"
            >
                {{ labelForViewLink }}
            </Link>
            <BButton variant="secondary" type="button" @click="$emit('toggle')">
                {{ labelForDropAction }}
            </BButton>
            <BButton variant="primary" :disabled="loading" type="button" @click="$emit('tertiaryAction')">
                {{ labelForTertiaryAction }}
            </BButton>
        </template>
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { TranslateResult } from 'vue-i18n';
import { Link } from '@dbco/ui-library';

export default defineComponent({
    props: {
        labelForDropAction: { type: String as PropType<TranslateResult | string>, required: true },
        labelForPickupAction: { type: String as PropType<TranslateResult | string>, required: true },
        labelForPickupActionLoading: { type: String as PropType<TranslateResult | string>, required: false },
        labelForTertiaryAction: { type: String as PropType<TranslateResult | string>, required: true },
        labelForViewLink: { type: String as PropType<TranslateResult | string>, required: true },
        pickedUp: { type: Boolean as PropType<boolean>, required: true },
        loading: { type: Boolean as PropType<boolean>, required: false },
        viewLink: { type: String as PropType<string>, required: true },
    },
    components: { Link },
    emits: ['tertiaryAction', 'toggle'],
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

#chore-actions {
    border-top: $border-default;
    display: grid;
    padding: $padding-sm $padding-md;
    width: 100%;

    @media (max-width: ($breakpoint-xxl - 1)) {
        display: flex;
        flex-wrap: wrap;
    }
    .loading-state-button {
        display: flex;
        flex-direction: row;

        span {
            margin-right: $padding-xs;
        }
    }

    &.picked-up {
        grid-template-columns: max-content auto max-content max-content;
        gap: 1rem;

        a + button {
            grid-column-start: 3;
        }
    }
}
</style>
