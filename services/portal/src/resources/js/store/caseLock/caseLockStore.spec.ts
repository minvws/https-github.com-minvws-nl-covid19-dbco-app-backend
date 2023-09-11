import { fakerjs } from '@/utils/test';
import { setActivePinia, createPinia } from 'pinia';
import { useCaseLockStore } from './caseLockStore';
import { hasCaseLock } from '@/utils/interfaceState';
import type { Mock } from 'vitest';
import { caseApi } from '@dbco/portal-api';
import { escape } from 'lodash';
import {
    fakeCaseLockResponseLocked,
    fakeCaseLockResponseLockedNoUser,
    fakeCaseLockResponseUnlocked,
} from '@/utils/__fakes__/caselock';
vi.mock('@/utils/interfaceState');

describe('CaseLock Store', () => {
    beforeEach(() => {
        vi.restoreAllMocks();
        setActivePinia(createPinia());
        vi.useFakeTimers();
    });

    afterEach(() => {
        vi.useRealTimers();
        vi.clearAllMocks();
    });

    it('should return translated caseLock notification', () => {
        // GIVEN a caseLockStore with an active caseLock
        const caseLockStore = useCaseLockStore();
        caseLockStore.caseLock.user.name = fakerjs.person.fullName();
        caseLockStore.caseLock.user.organisation = fakerjs.company.name();

        // WHEN the notification is gotten
        const storeNotification = caseLockStore.translatedCaseLockNotification;

        // THEN it should match the expectedTranslation
        const expectedTranslation = `Je kunt dit dossier nu niet bewerken. ${escape(
            caseLockStore.caseLock.user.name
        )}, ${escape(caseLockStore.caseLock.user.organisation)} is in het dossier bezig.`;
        expect(storeNotification.html).toBe(expectedTranslation);
    });

    it('should poll for the caseLock status on interval when the initialize action is dispatched and the case is locked by another user', async () => {
        // GIVEN a caseLockStore
        const caseLockStore = useCaseLockStore();

        const spyOnAction = vi.spyOn(caseLockStore, 'getStatus').mockImplementation(() => Promise.resolve());

        // AND a case that is locked by another user
        (hasCaseLock as Mock).mockImplementationOnce(() => true);

        // WHEN the initialize action is dispatched
        await caseLockStore.initialize(fakerjs.string.uuid());
        vi.runOnlyPendingTimers();

        // THEN the getStatus action should have been dispatched on initial dispatch and on interval
        expect(spyOnAction).toHaveBeenCalledTimes(2);
    });

    it('should refresh the caseLock on interval when the initialize action is dispatched and the case is locked by the current user', async () => {
        // GIVEN a caseLockStore
        const caseLockStore = useCaseLockStore();

        const spyOnAction = vi.spyOn(caseLockStore, 'refresh').mockImplementationOnce(() => Promise.resolve());

        // WHEN the initialize action is dispatched
        await caseLockStore.initialize(fakerjs.string.uuid());
        vi.runOnlyPendingTimers();

        // THEN the refresh action should have been dispatched
        expect(spyOnAction).toHaveBeenCalledTimes(1);
    });

    it('should store case lock and set removed to false when caseLock fetch returns a caseLock from another user', async () => {
        // GIVEN a caseLockStore with a stored caseUuid
        const caseLockStore = useCaseLockStore();
        caseLockStore.caseUuid = fakerjs.string.uuid();

        // AND a fetch that returns without a caseLock from another user
        const expectedCaseLockResponse = fakeCaseLockResponseLocked;
        const spyOnApi = vi
            .spyOn(caseApi, 'getCaseLock')
            .mockImplementationOnce(() => Promise.resolve(expectedCaseLockResponse));

        // WHEN the getStatus action is dispatched
        await caseLockStore.getStatus();

        // THEN the caseLock is stored
        expect(spyOnApi).toHaveBeenCalledTimes(1);
        expect(caseLockStore.caseLock.user.name).toBe(expectedCaseLockResponse.data.user?.name);
        expect(caseLockStore.caseLock.user.organisation).toBe(expectedCaseLockResponse.data.user?.organisation);

        // AND removed is false
        expect(caseLockStore.caseLock.removed).toBe(false);
    });

    it('should store fallback user when fetch returns a caseLock without one', async () => {
        // GIVEN a caseLockStore with a stored caseUuid
        const caseLockStore = useCaseLockStore();
        caseLockStore.caseUuid = fakerjs.string.uuid();

        // AND a fetch that returns a caseLock without a user
        const expectedCaseLockResponse = fakeCaseLockResponseLockedNoUser;
        const spyOnApi = vi
            .spyOn(caseApi, 'getCaseLock')
            .mockImplementationOnce(() => Promise.resolve(expectedCaseLockResponse));

        // WHEN the getStatus action is dispatched
        await caseLockStore.getStatus();

        // THEN a fallback user is stored
        expect(spyOnApi).toHaveBeenCalledTimes(1);
        expect(caseLockStore.caseLock.user.name).toBe('');
        expect(caseLockStore.caseLock.user.organisation).toBe('');
    });

    it('should clear case lock and set removed to true when caseLock fetch returns without a caseLock', async () => {
        // GIVEN a caseLockStore with a stored caseUuid
        const caseLockStore = useCaseLockStore();
        caseLockStore.caseUuid = fakerjs.string.uuid();

        // AND a fetch that returns without a caseLock from another user
        const expectedCaseLockResponse = fakeCaseLockResponseUnlocked;
        const spyOnApi = vi
            .spyOn(caseApi, 'getCaseLock')
            .mockImplementationOnce(() => Promise.resolve(expectedCaseLockResponse));

        // WHEN the getStatus action is dispatched
        await caseLockStore.getStatus();

        // THEN the caseLock is stored
        expect(spyOnApi).toHaveBeenCalledTimes(1);
        expect(caseLockStore.caseLock.user.name).toBe('');
        expect(caseLockStore.caseLock.user.organisation).toBe('');

        // AND removed is false
        expect(caseLockStore.caseLock.removed).toBe(true);
    });

    it('should make a refreshCaseLock call when the refresh action is dispatched', async () => {
        // GIVEN a caseLockStore with a stored caseUuid
        const caseLockStore = useCaseLockStore();
        caseLockStore.caseUuid = fakerjs.string.uuid();

        const spyOnApi = vi.spyOn(caseApi, 'refreshCaseLock').mockImplementationOnce(() => Promise.resolve());

        // WHEN the refresh action is dispatched
        await caseLockStore.refresh();

        // THEN the refreshCaseLock call is made
        expect(spyOnApi).toHaveBeenCalledTimes(1);
    });

    it('should clear a running poll when the stopPolling action is dispatched', () => {
        vi.spyOn(global, 'clearInterval');

        // GIVEN a caseLockStore with an active poll
        const caseLockStore = useCaseLockStore();
        caseLockStore.poll.polling = setInterval(() => {}, 1000);

        // WHEN the stopPolling action is dispatched
        caseLockStore.stopPolling();

        // THEN the poll should be cleared
        expect(clearInterval).toHaveBeenCalledTimes(1);
    });
});
