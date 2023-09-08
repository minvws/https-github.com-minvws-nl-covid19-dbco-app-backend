import type { ContextCommonDTO } from '@dbco/schema/context/contextCommon';
import { sharedMutations } from '../mutations';
import { StoreType } from '../storeType';
import { sharedActions } from '../actions';

const getDefaultState = () => ({
    uuid: null as string | null,
    loaded: false,
    errors: {},
    fragments: {} as Partial<ContextCommonDTO>,
    place: {},
});

export type ContextStoreState = ReturnType<typeof getDefaultState>;

export default {
    namespaced: true,
    state: getDefaultState(),
    actions: sharedActions(StoreType.CONTEXT),
    mutations: sharedMutations(getDefaultState),
    getters: {
        errors: (state: ContextStoreState) => state.errors,
        forms: (state: ContextStoreState) => state,
        fragments: (state: ContextStoreState) => state.fragments,
        place: (state: ContextStoreState) => state.place,
        uuid: (state: ContextStoreState) => state.uuid,
        // This getter is being used in actions.updateFormValue (getters.type)
        type: () => 'contexts',
    },
};
