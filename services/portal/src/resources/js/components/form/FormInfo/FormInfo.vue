<template>
    <div :class="['info-block', `info-block--${infoType}`, 'form-font']">
        <component :is="infoIcons[infoType]" v-if="showIcon" class="svg-icon" />
        <span class="pl-2" v-safe-html="text"></span>
        <BButton v-if="hasAction" variant="link" size="sm" @click="$emit('actionTriggered')" class="action-button">
            <i class="icon icon--edit"></i>
            {{ actionText }}
        </BButton>
        <slot />
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { SafeHtml } from '@/utils/safeHtml';
import type { PropType } from 'vue-demi';
import type { VueConstructor } from 'vue/types/umd';
import ErrorIcon from '@icons/error.svg?vue';
import InfoIcon from '@icons/info.svg?vue';
import SuccessIcon from '@icons/success.svg?vue';
import WarningIcon from '@icons/warning.svg?vue';

enum InfoType {
    INFO = 'info',
    SUCCESS = 'success',
    WARNING = 'warning',
    ERROR = 'error',
}

const infoIcons: Record<InfoType, VueConstructor<Vue>> = {
    [InfoType.INFO]: InfoIcon,
    [InfoType.SUCCESS]: SuccessIcon,
    [InfoType.WARNING]: WarningIcon,
    [InfoType.ERROR]: ErrorIcon,
};

export default defineComponent({
    name: 'FormInfo',
    setup() {
        return { infoIcons };
    },
    props: {
        text: {
            type: [String, Object] as PropType<string | SafeHtml>,
            default: '',
        },
        infoType: {
            type: String as PropType<`${InfoType}`>,
            default: InfoType.INFO,
        },
        showIcon: {
            type: Boolean,
            default: true,
        },
        hasAction: {
            type: Boolean,
            default: false,
        },
        actionText: {
            type: String,
            required: false,
        },
        actionTriggered: {
            type: Function,
            required: false,
        },
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.info-block {
    display: flex;
    align-items: center;
    font-size: 14px;
    font-weight: 400;
    line-height: 20px;
    border-radius: $border-radius-small;
    background: rgba($bco-purple, 0.08);
    color: $black;
    padding: 0.875rem 0.875rem 0.875rem 1.375rem;

    span {
        margin-left: 0.75rem;
    }

    .svg-icon {
        height: 16px;
        color: $bco-purple;
        flex-shrink: 0;
    }

    &--lg {
        .svg-icon {
            width: 24px;
            height: auto;
        }
    }

    &--success {
        font-weight: 500;
        background: rgba($bco-green, 0.08);

        .svg-icon {
            color: $light-green;
        }
    }

    &--warning {
        font-weight: 500;
        background: rgba($bco-orange, 0.08);

        .svg-icon {
            color: $bco-orange;
        }
    }

    &--error {
        background: rgba($bco-red, 0.08);

        .svg-icon {
            color: $bco-red;
        }
    }

    .action-button {
        cursor: pointer;
        font-weight: 500;
        padding: 0 $padding-xs;

        .icon--edit {
            background-color: $bco-purple;
        }
    }
}
</style>
