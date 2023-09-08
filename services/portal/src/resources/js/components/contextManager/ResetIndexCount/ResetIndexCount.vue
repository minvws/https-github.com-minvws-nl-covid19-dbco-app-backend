<template>
    <button class="btn btn-primary btn--small" type="button" :disabled="!enableButton" @click="finalize">
        {{ t('components.resetIndexCount.button') }}
    </button>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { computed, defineComponent, watchEffect } from 'vue';
import { useI18n } from 'vue-i18n-composable';
import { useResetIndexCount } from '@/store/cluster/clusterStore';
import { isIdle, isRejected, isResolved, Status } from '@/store/useStatusAction';

export default defineComponent({
    props: {
        indexCountSinceReset: { type: Number as PropType<number>, required: true },
        placeUuid: { type: String as PropType<string>, required: true },
    },
    emits: ['reset'],
    setup(props, ctx) {
        var thisButtonPressed = false;
        const enableButton = computed(
            () => (props.indexCountSinceReset !== 0 && isIdle(store.resetStatus)) || isRejected(store.resetStatus)
        );
        const finalize = async () => {
            thisButtonPressed = true;
            await store.reset(props.placeUuid);
        };
        const store = useResetIndexCount();
        const { t } = useI18n();
        watchEffect(() => {
            if (isResolved(store.resetStatus) && thisButtonPressed) {
                thisButtonPressed = false;
                ctx.emit('reset');
                store.$state.resetStatus = {
                    status: Status.idle,
                };
            }
        });

        return {
            enableButton,
            finalize,
            store,
            t,
        };
    },
});
</script>
