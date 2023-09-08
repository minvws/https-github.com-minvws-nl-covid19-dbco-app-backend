import type { Commit } from 'vuex';
import { UserInfoMutations } from './userInfoMutations';

export enum UserInfoActions {
    FILL = 'FILL',
    CHANGE = 'CHANGE',
}

export const userInfoActions = {
    [UserInfoActions.FILL]: ({ commit }: { commit: Commit }, payload: any) => {
        commit(UserInfoMutations.FILL, payload);
    },
    [UserInfoActions.CHANGE]: ({ commit }: { commit: Commit }, payload: any) => {
        commit(UserInfoMutations.CHANGE, payload);
    },
};
