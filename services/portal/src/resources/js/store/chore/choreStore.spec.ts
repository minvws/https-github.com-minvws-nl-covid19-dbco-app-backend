import type { CallToActionResponse } from '@dbco/portal-api/callToAction.dto';
import { fakerjs } from '@/utils/test';
import { fakeCallToAction } from '@/utils/__fakes__/callToAction';
import { setActivePinia, createPinia } from 'pinia';
import { useChoreStore } from './choreStore';

describe('Chore Store', () => {
    vi.stubGlobal('clearInterval', vi.fn());

    beforeEach(() => {
        vi.restoreAllMocks();
        setActivePinia(createPinia());
    });

    it('should do nothing when checkIfTableContentOutdated is dispatched given there is no active poll', () => {
        // GIVEN a choreStore with no active poll
        const choreStore = useChoreStore();

        // WHEN the checkIfTableContentOutdated action is dispatched
        const fakeData = fakerjs.custom.typedArray<CallToActionResponse>(fakeCallToAction);
        const fakeUpdateMessage = fakerjs.lorem.sentence();
        choreStore.checkIfTableContentOutdated(fakeData, fakeUpdateMessage);

        // THEN the store should do nothing, so its state remains the same
        expect(choreStore.updateMessage).toBe(null);
    });

    it('should store a given update message and stop polling when checkIfTableContentOutdated is dispatched and the given content is outdated', () => {
        // GIVEN a choreStore with an active poll that started before the creation date of the given content
        const callToAction = fakeCallToAction;
        const choreStore = useChoreStore();
        useChoreStore().pollTableContent.polling = setInterval(() => {}, 10);
        useChoreStore().pollTableContent.pollStartedAt = fakerjs.date.past({
            years: 1,
            refDate: callToAction.createdAt,
        });

        // WHEN the checkIfTableContentOutdated action is dispatched
        const fakeData = fakerjs.custom.typedArray<CallToActionResponse>(callToAction);
        const fakeUpdateMessage = fakerjs.lorem.sentence();
        choreStore.checkIfTableContentOutdated(fakeData, fakeUpdateMessage);

        // THEN the given update message should be stored
        expect(choreStore.updateMessage).toBe(fakeUpdateMessage);

        // AND the poll should be stopped
        expect(clearInterval).toHaveBeenCalled();
    });

    it('should not store a given update message or stop polling when checkIfTableContentOutdated is dispatched and the given content is up to date', () => {
        // GIVEN a choreStore with an active poll that started before the creation date of the given content
        const choreStore = useChoreStore();
        useChoreStore().pollTableContent.polling = setInterval(() => {}, 10);
        useChoreStore().pollTableContent.pollStartedAt = fakerjs.date.soon();

        // WHEN the checkIfTableContentOutdated action is dispatched
        const fakeData = fakerjs.custom.typedArray<CallToActionResponse>(fakeCallToAction);
        const fakeUpdateMessage = fakerjs.lorem.sentence();
        choreStore.checkIfTableContentOutdated(fakeData, fakeUpdateMessage);

        // THEN the given update message should not be stored
        expect(choreStore.updateMessage).toBe(null);

        // AND the poll should not be stopped
        expect(clearInterval).toBeCalledTimes(0);
    });

    it('should clear active polling interval when startPollingSelected is dispatched', () => {
        // GIVEN a choreStore with an active poll
        const choreStore = useChoreStore();
        useChoreStore().pollSelected.polling = setInterval(() => {}, 10);
        useChoreStore().pollSelected.pollStartedAt = fakerjs.date.recent({ days: 2 });

        // WHEN the startPollingSelected action is dispatched
        const fakeCallbackFn = vi.fn();
        choreStore.startPollingSelected(fakeCallbackFn);

        // THEN the active polling interval has been cleared
        expect(clearInterval).toHaveBeenCalled();
    });

    it('should clear active polling interval when startPollingTableContent is dispatched', () => {
        // GIVEN a choreStore with an active poll
        const choreStore = useChoreStore();
        useChoreStore().pollTableContent.polling = setInterval(() => {}, 10);
        useChoreStore().pollTableContent.pollStartedAt = fakerjs.date.recent({ days: 2 });

        // WHEN the startPollingTableContent action is dispatched
        const fakeCallbackFn = vi.fn();
        choreStore.startPollingTableContent(fakeCallbackFn);

        // THEN the active polling interval has been cleared
        expect(clearInterval).toHaveBeenCalled();
    });

    it('should clear active polling interval when stopPollingSelected is dispatched', () => {
        // GIVEN a choreStore with an active poll
        const choreStore = useChoreStore();
        useChoreStore().pollSelected.polling = setInterval(() => {}, 10);
        useChoreStore().pollSelected.pollStartedAt = fakerjs.date.recent({ days: 2 });

        // WHEN the stopPollingSelected action is dispatched
        choreStore.stopPollingSelected();

        // THEN the active polling interval has been cleared
        expect(clearInterval).toHaveBeenCalled();
    });

    it('should do nothing given stopPollingSelected is dispatched when there is no active polling interval', () => {
        // GIVEN a choreStore with no active poll
        const choreStore = useChoreStore();

        // WHEN the stopPollingSelected action is dispatched
        choreStore.stopPollingSelected();

        // THEN clear interval has not been called
        expect(clearInterval).toHaveBeenCalledTimes(0);
    });

    it('should do nothing given stopPollingTableContent is dispatched when there is no active polling interval', () => {
        // GIVEN a choreStore with no active poll
        const choreStore = useChoreStore();

        // WHEN the stopPollingTableContent action is dispatched
        choreStore.stopPollingTableContent();

        // THEN clear interval has not been called
        expect(clearInterval).toHaveBeenCalledTimes(0);
    });
});
