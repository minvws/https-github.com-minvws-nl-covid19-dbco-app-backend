<template>
    <div class="bulk-actionbar-wrapper">
        <div class="bulk-actionbar d-flex justify-content-between align-items-center">
            <div class="actions">
                <slot />
            </div>
            <div class="bulk-actionbar__text">
                {{ text }}
            </div>
            <!-- eslint-disable-next-line vuejs-accessibility/aria-unsupported-elements -->
            <Link class="bulk-actionbar__close" @click="$emit('hide')" aria-label="close">
                <i role="presentation" class="icon icon--close icon--lg" />
            </Link>
        </div>
    </div>
</template>
<script lang="ts">
import { defineComponent } from 'vue';
import { Link } from '@dbco/ui-library';

export default defineComponent({
    name: 'BulkActionbar',
    components: { Link },
    props: {
        text: {
            required: true,
            type: String,
        },
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
        box-shadow:
            0px 1px 2px rgba(0, 0, 0, 0.16),
            0px 2px 6px rgba(0, 0, 0, 0.1),
            0px 32px 64px rgba(0, 0, 0, 0.06);
        color: white;
        gap: 1rem;
        position: relative;
        padding: 0.5rem;
        pointer-events: all;
        margin: 0 auto;
        max-width: 660px;
        width: 100%;

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem 0;

            > * {
                border: 1px solid $grey-on-black;
                white-space: nowrap;

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

        &__text {
            margin-right: 3rem;
            white-space: nowrap;
        }

        &__close {
            position: absolute;
            right: 0.5rem;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;

            .icon {
                margin-top: -1px;
            }
        }
    }
}
</style>
