import type { Organisation } from './userInfoStore';
import userInfoStore from './userInfoStore';
import type { User } from '@dbco/portal-api/user';
import { Role } from '@dbco/portal-api/user';
import { BcoPhaseV1, PermissionV1 } from '@dbco/enum';

describe('userInfoStore', () => {
    const defaultState = { ...userInfoStore.state };

    const getState = ({
        user = {} as Partial<User>,
        organisation = {} as Partial<Organisation>,
        permissions = [] as PermissionV1[],
    } = {}) => {
        const state = { ...defaultState };
        userInfoStore.mutations.FILL(state, {
            user: {
                name: 'testUser',
                roles: [Role.user],
                uuid: '1234',
                ...user,
            },
            organisation: {
                abbreviation: 'abbr',
                bcoPhase: null,
                type: 'regional',
                hasOutsourceToggle: false,
                isAvailableForOutsourcing: false,
                name: 'testOrganisation',
                uuid: '5678',
                ...organisation,
            },
            permissions: [...permissions],
        });

        return state;
    };

    it('should have default loaded state of false', () => {
        expect(userInfoStore.state.loaded).toBe(false);
    });

    it('should return user info', () => {
        const user: User = {
            name: 'userInfoName',
            roles: [Role.user, Role.compliance],
            uuid: '1212',
        };
        const state = getState({
            user,
        });

        expect(userInfoStore.getters.user(state)).toEqual(user);
    });

    it('should return organisation info', () => {
        const organisation: Organisation = {
            abbreviation: 'org',
            bcoPhase: BcoPhaseV1.VALUE_2,
            type: 'regional',
            hasOutsourceToggle: true,
            isAvailableForOutsourcing: true,
            name: 'the name',
            uuid: '7878',
        };
        const state = getState({
            organisation,
        });

        expect(userInfoStore.getters.organisation(state)).toEqual(organisation);
    });

    it('should return roles', () => {
        const roles = [Role.user, Role.planner];
        const state = getState({
            user: { roles },
        });

        expect(userInfoStore.getters.roles(state)).toEqual(roles);
    });

    it('should check permissions', () => {
        const permissions = [PermissionV1.VALUE_caseBsnLookup, PermissionV1.VALUE_contextEdit];
        const state = getState({
            permissions,
        });

        expect(userInfoStore.getters.hasPermission(state)(PermissionV1.VALUE_contextEdit)).toBe(true);
        expect(userInfoStore.getters.hasPermission(state)(PermissionV1.VALUE_contextDelete)).toBe(false);
    });

    it('should check if one of the permissions matches', () => {
        const permissions = [PermissionV1.VALUE_caseBsnLookup, PermissionV1.VALUE_contextEdit];
        const state = getState({
            permissions,
        });

        expect(
            userInfoStore.getters.hasPermission(state)(
                PermissionV1.VALUE_caseAddressLookup,
                PermissionV1.VALUE_contextEdit
            )
        ).toBe(true);
    });
});
