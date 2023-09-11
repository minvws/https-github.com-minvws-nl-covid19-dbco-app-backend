<template>
    <BForm id="cta-form" v-if="confirming.active" @submit.prevent="confirm">
        <div id="cta-form-textarea">
            <h5>{{ t(`components.callToActionSidebar.note.${confirming.type}.title`) }}</h5>
            <BFormTextarea
                min="0"
                rows="6"
                v-model="note"
                :disabled="loading"
                :maxlength="noteMaxLength"
                :placeholder="t(`components.callToActionSidebar.note.${confirming.type}.placeholder`)"
                @input="showRequiredMessage = false"
            />
            <small>{{ characterCount }}</small>
            <div v-if="showRequiredMessage">
                <i class="icon icon--error-notice" />
                <p class="form-font">
                    {{ t(`components.callToActionSidebar.note.${confirming.type}.required`) }}
                </p>
            </div>
        </div>

        <div id="cta-form-actions">
            <BButton variant="link" :disabled="loading" @click="cancel">
                {{ t('components.callToActionSidebar.actions.cancel') }}
            </BButton>
            <BButton :disabled="loading" type="submit" variant="primary">
                <div v-if="loading" class="loading-state-button">
                    <span><BSpinner small /></span>
                    {{ t(`components.callToActionSidebar.actions.confirm.${confirming.type}`) }}...
                </div>
                <div v-else>
                    {{ t(`components.callToActionSidebar.actions.confirm.${confirming.type}`) }}
                </div>
            </BButton>
        </div>
    </BForm>
    <div id="cta-form" v-else>
        <ChoreActions
            :labelForDropAction="t(`components.callToActionSidebar.actions.drop`)"
            :labelForPickupAction="t(`components.callToActionSidebar.actions.pick_up`)"
            :labelForPickupActionLoading="t(`components.callToActionSidebar.actions.pick_up_loading`)"
            :labelForTertiaryAction="t(`components.callToActionSidebar.actions.complete`)"
            :labelForViewLink="t(`components.callToActionSidebar.actions.view`)"
            :pickedUp="pickedUp"
            :viewLink="`/editcase/${resourceUuid}`"
            @tertiaryAction="complete"
            @toggle="togglePickup"
            :loading="loading"
        />
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { computed, watch, defineComponent, ref } from 'vue';
import { useCallToActionStore } from '@/store/callToAction/callToActionStore';
import type { CallToActionResponse } from '@dbco/portal-api/callToAction.dto';
import ChoreActions from '@/components/chore/ChoreActions/ChoreActions.vue';
import { useI18n } from 'vue-i18n-composable';
import useStatusAction, { isPending } from '@/store/useStatusAction';

interface Confirming {
    active: boolean;
    type: 'complete' | 'drop';
}

export default defineComponent({
    components: {
        ChoreActions,
    },
    props: {
        callToAction: { type: Object as PropType<CallToActionResponse | null>, required: true },
        pickedUp: { type: Boolean as PropType<boolean>, required: true },
    },
    setup(props) {
        const characterCount = computed(() => `${note.value.length}/${noteMaxLength.value}`);
        const confirming = ref<Confirming>({
            active: false,
            type: 'complete',
        });
        const note = ref<string>('');
        const noteMaxLength = ref<string>('5000');
        const resourceUuid = computed(() => props.callToAction?.resource.uuid);
        const showRequiredMessage = ref<boolean>(false);

        const { status: pickupSelectedStatus, action: pickupSelectedAction } = useStatusAction(async () => {
            await useCallToActionStore().pickupSelected();
        });

        const { status: dropSelectedStatus, action: dropSelectedAction } = useStatusAction(async (value: string) => {
            await useCallToActionStore().dropSelected(value);
        });

        const { status: completeSelectedStatus, action: completeSelectedAction } = useStatusAction(
            async (value: string) => {
                await useCallToActionStore().completeSelected(value);
            }
        );

        const cancel = () => {
            confirming.value.active = false;
            note.value = '';
            showRequiredMessage.value = false;
        };
        watch(
            () => props.callToAction,
            () => {
                cancel();
            }
        );
        const complete = () => {
            confirming.value = {
                active: true,
                type: 'complete',
            };
        };
        const confirm = async () => {
            if (!note.value.length) return (showRequiredMessage.value = true);
            if (confirming.value.type === 'complete') {
                await completeSelectedAction(note.value);
            } else {
                await dropSelectedAction(note.value);
            }
            cancel();
        };
        const togglePickup = async () => {
            if (!props.pickedUp) {
                await pickupSelectedAction();
                return;
            }
            confirming.value = {
                active: true,
                type: 'drop',
            };
        };
        const loading = computed(
            () =>
                isPending(pickupSelectedStatus.value) ||
                isPending(dropSelectedStatus.value) ||
                isPending(completeSelectedStatus.value)
        );
        return {
            cancel,
            characterCount,
            complete,
            confirm,
            confirming,
            loading,
            note,
            noteMaxLength,
            resourceUuid,
            showRequiredMessage,
            togglePickup,
            ...useI18n(),
        };
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

#cta-form {
    border-top: $border-default;

    #cta-form-textarea {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        padding: $padding-default;
        padding-bottom: $padding-sm;

        h5 {
            align-self: flex-start;
            font-weight: 700;
            font-size: 0.875rem;
        }

        textarea {
            flex: 1 0 auto;
            margin-bottom: 0;
            resize: none;
        }

        small {
            color: $dark-grey;
            font-size: 0.75rem;
        }

        div {
            align-items: center;
            align-self: flex-start;
            display: flex;
            gap: $padding-xs;

            i,
            p {
                margin: 0;
            }
        }
    }

    #cta-form-actions {
        display: flex;
        justify-content: flex-end;
        padding: $padding-sm $padding-md;

        .loading-state-button {
            display: flex;
            flex-direction: row;

            span {
                margin-right: $padding-xs;
            }
        }
    }
}
</style>
