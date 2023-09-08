import type { PermissionV1 } from '@dbco/enum';

import type { UserInfoState, Organisation } from './userInfoStore';
import type { User } from '@dbco/portal-api/user';

export enum UserInfoMutations {
    FILL = 'FILL',
    CHANGE = 'CHANGE',
}

export const userInfoMutations = {
    [UserInfoMutations.FILL](
        state: UserInfoState,
        data: { organisation: Organisation; permissions: PermissionV1[]; user: User }
    ) {
        Object.assign(state, { ...data, loaded: true });
    },
    [UserInfoMutations.CHANGE](
        state: UserInfoState,
        { path, values }: { path: 'user'; values: User } | { path: 'organisation'; values: Organisation }
    ) {
        // @ts-ignore
        state[path] = { ...values };
    },
};
