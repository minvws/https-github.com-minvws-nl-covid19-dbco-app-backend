import Vue from 'vue';
import Vuex from 'vuex';
import type { ContextStoreState } from './store/context/contextStore';
import contextStore from './store/context/contextStore';
import type { IndexStoreState } from './store/index/indexStore';
import indexStore from './store/index/indexStore';
import organisationStore from './store/organisation/organisationStore';
import type { OrganisationStoreState } from './store/organisation/organisationTypes';
import placeStore from './store/place/placeStore';
import type { PlaceStoreState } from './store/place/placeTypes';
import type { SupervisionStoreState } from './store/supervision/supervisionStore';
import supervisionStore from './store/supervision/supervisionStore';
import type { TaskStoreState } from './store/task/taskStore';
import taskStore from './store/task/taskStore';
import type { UserInfoState } from './store/userInfo/userInfoStore';
import userInfoStore from './store/userInfo/userInfoStore';

Vue.use(Vuex);

export interface RootStoreState {
    context: ContextStoreState;
    index: IndexStoreState;
    organisation: OrganisationStoreState;
    place: PlaceStoreState;
    task: TaskStoreState;
    userInfo: UserInfoState;
    supervision: SupervisionStoreState;
}

const modules = {
    context: contextStore,
    index: indexStore,
    organisation: organisationStore,
    place: placeStore,
    task: taskStore,
    userInfo: userInfoStore,
    supervision: supervisionStore,
};

const store = new Vuex.Store<RootStoreState>({
    modules,
});

export default store;

export type StoreModules = typeof modules;
