import type { BcoPhaseV1, PermissionV1 } from '@dbco/enum';
import type { User } from '@dbco/portal-api/user';

import { userInfoActions } from './userInfoActions';
import { userInfoMutations } from './userInfoMutations';

export type Organisation = {
    abbreviation: string;
    bcoPhase: BcoPhaseV1 | null;
    hasOutsourceToggle: boolean;
    isAvailableForOutsourcing: boolean;
    type: string;
    name: string;
    uuid: string;
};

/**
 * Returns true if permissions are loaded and one of the passed permission is found
 */
export type HasPermissionGetter = (...permissions: PermissionV1[]) => boolean;

/**
 * When loaded is true, the state contains user and organisation data
 */
export interface UserInfoState {
    loaded: boolean;
    organisation?: Organisation;
    permissions?: PermissionV1[];
    user?: User;
}

const getDefaultState = (): UserInfoState => ({
    loaded: false,
    organisation: undefined,
    permissions: undefined,
    user: undefined,
});

export default {
    namespaced: true,
    state: getDefaultState(),
    actions: userInfoActions,
    mutations: userInfoMutations,
    getters: {
        hasPermission:
            (state: UserInfoState) =>
            (...permissions: PermissionV1[]) =>
                permissions.some((permission) => state.permissions?.includes(permission)),
        /**
         * Returns the organisation or null when userInfo is not loaded yet
         */
        organisation: (state: UserInfoState) => state.organisation,
        /**
         * Returns the organisation uuid or null when userInfo is not loaded yet
         */
        organisationUuid: (state: UserInfoState) => state.organisation?.uuid,
        /**
         * Returns array of roles or an empty array when userInfo is not loaded yet
         */
        roles: (state: UserInfoState) => state.user?.roles ?? [],
        /**
         * Returns the user or null when userInfo is not loaded yet
         */
        user: (state: UserInfoState) => state.user,
        /**
         * Returns the user or null when userInfo is not loaded yet
         */
        userUuid: (state: UserInfoState) => state.user?.uuid,
    },
};
