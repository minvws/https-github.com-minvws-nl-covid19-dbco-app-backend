<template>
    <div v-if="isLoaded">
        <BTableSimple class="table-form table-ggd table--clickable">
            <colgroup>
                <col class="w-30" />
                <col class="w-30" />
                <col class="w-20" />
                <col class="w-20" />
                <col v-show="group != ContextGroup.Contagious" class="td-icon" />
                <col class="td-icon" />
            </colgroup>
            <BThead>
                <BTr>
                    <BTh scope="col">Omschrijving</BTh>
                    <BTh scope="col">Notitie (optioneel)</BTh>
                    <BTh scope="col">Datum(s)</BTh>
                    <BTh scope="col">Relatie tot context</BTh>
                    <BTh
                        v-if="group != ContextGroup.Contagious"
                        class="cell-flex"
                        data-testid="source-cell-th"
                        scope="col"
                    >
                        Bron
                        <i
                            class="icon icon--m0 icon--questionmark"
                            v-b-tooltip.hover
                            title="Vink hier aan welke bron of bronnen het meest waarschijnlijk zijn. Alleen als er gerelateerde gevallen op de context zijn."
                        />
                    </BTh>
                    <BTh scope="col"></BTh>
                </BTr>
            </BThead>
            <BTbody>
                <ContextEditingTableRow
                    v-for="(context, $index) in tableRows"
                    :key="`context-${$index}`"
                    @change="(context) => debouncedPersist(context, $index)"
                    @click="(uuid, $event) => checkTableRowClick(uuid, $event)"
                    @delete="(uuid) => deleteContext(uuid)"
                    :errors="validationErrors[context.uuid || '']"
                    :group="group"
                    :isSaving="savingUuids.includes(context.uuid || '')"
                    :context="context"
                />
            </BTbody>
        </BTableSimple>

        <ContextEditingModal v-if="selectedContext" :context="selectedContext" @onClose="onModalClose" />
    </div>
    <div v-else class="mb-5 text-center">
        <BSpinner variant="primary" small />
    </div>
</template>

<script lang="ts">
import { contextApi } from '@dbco/portal-api';
import { useModal } from '@/components/AppHooks';
import { getAllErrors } from '@/components/form/ts/formRequest';
import { ContextGroup } from '@/components/form/ts/formTypes';
import ContextEditingModal from '@/components/modals/ContextEditingModal/ContextEditingModal.vue';
import { SharedActions } from '@/store/actions';
import type { IndexStoreState } from '@/store/index/indexStore';
import { StoreType } from '@/store/storeType';
import axios from 'axios';
import _ from 'lodash';
import type { PropType } from 'vue';
import { computed, defineComponent, onBeforeMount, ref, set } from 'vue';
import ContextEditingTableRow from './ContextEditingTableRow/ContextEditingTableRow.vue';
import type { Context } from '@dbco/portal-api/context.dto';
import type { ValidationResult } from '@dbco/portal-api/validation-result.dto';
import { useContextTableStore } from '@/store/context/contextTableStore';
import { useStore } from '@/utils/vuex';

interface IndexChangeAction<TKey extends keyof IndexStoreState> {
    path: TKey;
    values: IndexStoreState[TKey];
}

export default defineComponent({
    name: 'ContextEditingTable',
    components: { ContextEditingModal, ContextEditingTableRow },
    props: {
        group: {
            type: String as PropType<ContextGroup>,
            required: true,
            validator: (prop: ContextGroup) => Object.values(ContextGroup).includes(prop),
        },
    },
    setup(props) {
        const isLoaded = ref(false);

        const contexts = ref<Context[]>([]);
        const selectedContext = ref<Context | undefined>(undefined);
        const validationErrors = ref<Record<string, string[]>>({});
        const savingUuids = ref<string[]>([]);

        const modal = useModal();
        const store = useStore();

        const contextStore = useContextTableStore();

        const caseUuid = computed(() => store.getters[`${StoreType.INDEX}/uuid`]);

        const tableRows = computed(() => {
            const placeholderRow = {
                uuid: '',
                moments: [],
            };

            switch (props.group) {
                case ContextGroup.All:
                    return [...contextStore.allContexts, placeholderRow];
                case ContextGroup.Contagious:
                    return [...contextStore.contagiousContexts, placeholderRow];
                case ContextGroup.Source:
                    return [...contextStore.sourceContexts, placeholderRow];
                default:
                    throw new Error('invalid context group');
            }
        });

        const indexChangeAction = async <TKey extends keyof IndexStoreState>({
            path,
            values,
        }: IndexChangeAction<TKey>): Promise<void> =>
            await store.dispatch(`${StoreType.INDEX}/${SharedActions.CHANGE}`, { path, values });
        const debouncedPersist: (context: Context, index: number) => void = _.debounce(
            async function (context, index) {
                await persist(context, index);
            },
            300,
            { leading: true }
        );

        const edit = (uuid: string) =>
            (selectedContext.value = contexts.value.find((context) => context.uuid === uuid));

        const handleValidation = (uuid: string, validationResult: ValidationResult = {}, isError = false) => {
            const allErrors = getAllErrors(validationResult);

            // If request is unsuccessful and there are no validation errors
            if (isError && !allErrors) {
                alert('Er ging iets mis bij het opslaan van de context.');
                return;
            }

            validationErrors.value[uuid] = allErrors ? Object.keys(allErrors.errors) : [];
        };

        const load = async (caseUuid: string) => {
            if (!caseUuid) return;
            // In the future this.tasks should be replaced by a computed property pointed to the store
            // API requests to fetch data should never be called in components
            // Jira ticket: DBCO-4766
            isLoaded.value = false;

            const data = await contextApi.getContexts(caseUuid);
            contexts.value = data.contexts;
            // Keep store up to date
            await indexChangeAction({
                path: 'contexts',
                values: data.contexts,
            });
            isLoaded.value = true;
        };

        const onModalClose = async () => {
            selectedContext.value = undefined;
            await load(caseUuid.value);
        };

        const persist = async (context: Context, index: number) => {
            // Ensure we have the latest uuid to prevent creating duplicates
            context.uuid = tableRows.value[index].uuid ?? '';

            // If this row is new, but already saving, debounce the persist call so it will update after creation
            if (!context.uuid && savingUuids.value.includes(context.uuid)) {
                debouncedPersist(context, index);
                return;
            }

            savingUuids.value.push(context.uuid);

            try {
                // Place is relational data and should not be sent to the API
                // eslint-disable-next-line @typescript-eslint/no-unused-vars
                const { place, ...apiData } = context;
                const isNewContext = !context.uuid;

                // These api calls need to be replaced by store functions
                // Jira ticket: DBCO-4766
                const data = await (isNewContext
                    ? contextApi.createContext(caseUuid.value, apiData)
                    : contextApi.updateContext(context.uuid, apiData));

                validationErrors.value[context.uuid] = [];

                const updatedContext: Context = {
                    ...context,
                    uuid: data.context.uuid,
                };

                if (isNewContext) {
                    contexts.value.push(updatedContext);
                } else {
                    // Find the index of the row in the unfiltered list of contexts
                    const contextsIndex = contexts.value.findIndex((c) => c.uuid === context.uuid);
                    set(contexts.value, contextsIndex, updatedContext);
                }

                handleValidation(data.context.uuid, data.validationResult);
            } catch (error) {
                const validationResult = axios.isAxiosError(error) ? error.response?.data?.validationResult : {};
                handleValidation(context.uuid, validationResult, true);
            } finally {
                // Keep store up to date
                await indexChangeAction({
                    path: 'contexts',
                    values: contexts.value,
                });
                savingUuids.value = savingUuids.value.filter((uuid) => uuid !== context.uuid);
            }
        };

        const deleteContext = (uuid?: string) => {
            if (!uuid) return;

            modal.show({
                title: 'Weet je zeker dat je deze context uit de case wilt verwijderen?',
                text: 'Let op: je kunt dit hierna niet meer ongedaan maken',
                okTitle: 'Verwijderen',
                okVariant: 'outline-danger',
                onConfirm: async () => {
                    savingUuids.value.push(uuid);

                    try {
                        // API requests to fetch data should never be called in components
                        // In the future a dispatch should be called here for deleting the task
                        // Jira ticket: DBCO-4766
                        await contextApi.deleteContext(uuid);
                        contexts.value = contexts.value.filter((contextItem) => contextItem.uuid !== uuid);
                    } finally {
                        // Keep store up to date
                        await indexChangeAction({
                            path: 'contexts',
                            values: contexts.value,
                        });
                        savingUuids.value = savingUuids.value.filter((uuidItem) => uuidItem !== uuid);
                    }
                },
            });
        };

        const checkTableRowClick = (uuid: string, $event: Event) => {
            // If event target element is passed
            // Check if the user clicked on a table column/row or BInputGroup
            if (
                $event.target &&
                $event.target instanceof HTMLElement &&
                !['TD', 'TR'].includes($event.target.nodeName) &&
                !$event.target.classList.contains('input-group')
            ) {
                return;
            }

            edit(uuid);
        };

        onBeforeMount(async () => {
            await load(caseUuid.value);
            contextStore.initalLoadComplete = true;
        });

        return {
            ContextGroup,
            isLoaded,

            savingUuids,
            selectedContext,
            tableRows,
            validationErrors,

            checkTableRowClick,
            debouncedPersist,
            deleteContext,
            onModalClose,

            contextStore,
        };
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.cell-flex {
    display: flex;
    align-items: center;

    .icon {
        margin-left: $padding-xs;
        margin-bottom: 2px;
    }
}
</style>
