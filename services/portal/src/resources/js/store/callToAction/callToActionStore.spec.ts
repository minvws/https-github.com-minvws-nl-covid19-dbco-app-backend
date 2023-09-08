import { fakerjs } from '@/utils/test';
import { setActivePinia, createPinia } from 'pinia';
import { useCallToActionStore } from './callToActionStore';
import { callToActionApi } from '@dbco/portal-api';
import type { CallToActionResponse } from '@dbco/portal-api/callToAction.dto';
import { CallToActionSortOptions } from '@dbco/portal-api/callToAction.dto';
import {
    fakeAssignedCTA,
    fakeCallToAction,
    fakeCallToActionHistoryItem,
    generateFakeCallToActionHistoryItem,
    generateFakeCallToActionRequest,
    generateFakeCallToActionResponse,
} from '@/utils/__fakes__/callToAction';
import { useChoreStore } from '../chore/choreStore';
import { fakeError } from '@/utils/__fakes__/shared';
import { noop } from 'lodash';
import { fakeTimelineItem, fakeTimelineItemWithCallToAction } from '@/utils/__fakes__/timeline';
import { CallToActionEventV1 } from '@dbco/enum';

describe('CallToAction Store', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });
    afterEach(() => {
        vi.clearAllMocks();
    });

    it('should create Call to Action, respond with success', async () => {
        const callToActionStore = useCallToActionStore();

        const create = vi
            .spyOn(callToActionApi, 'create')
            .mockImplementation(() => Promise.resolve(generateFakeCallToActionResponse()));

        const request = generateFakeCallToActionRequest();
        await callToActionStore.createCallToAction(request);

        expect(create).toHaveBeenCalledWith(request, undefined);
    });

    it('should pass token to create Call to Action, respond with success', async () => {
        const callToActionStore = useCallToActionStore();

        const create = vi
            .spyOn(callToActionApi, 'create')
            .mockImplementation(() => Promise.resolve(generateFakeCallToActionResponse()));

        const request = generateFakeCallToActionRequest();
        const token = fakerjs.string.sample();
        await callToActionStore.createCallToAction(request, token);

        expect(create).toHaveBeenCalledWith(request, token);
    });

    it('should propagate error to choreStore', async () => {
        const spyOnChoreStore = vi.spyOn(useChoreStore(), 'setBackendError').mockImplementation(vi.fn() as any);
        const callToActionStore = useCallToActionStore();

        vi.spyOn(callToActionApi, 'create').mockImplementation(() => Promise.reject(fakeError));

        try {
            await callToActionStore.createCallToAction(generateFakeCallToActionRequest());
        } catch (error) {
            // try catch used as error is rethrown in createCallToAction
            expect(spyOnChoreStore).toHaveBeenCalledWith(error);
        }
    });

    it('should return timeline items including call to action history when addHistoryItemsToTimeline is dispatched', async () => {
        // GIVEN a callToActionStore
        const callToActionStore = useCallToActionStore();

        vi.spyOn(useCallToActionStore(), 'getHistoryItems').mockImplementation(() =>
            Promise.resolve([fakeCallToActionHistoryItem, fakeCallToActionHistoryItem])
        );

        // WHEN the addHistoryItemsToTimeline action is dispatched with timeline items
        const expectedTimeline = await callToActionStore.addHistoryItemsToTimeline([
            fakeTimelineItemWithCallToAction,
            fakeTimelineItem,
            fakeTimelineItemWithCallToAction,
        ]);

        // THEN the call to action in the table conten should be updated
        expect(expectedTimeline.at(0)?.call_to_action_items).toHaveLength(2);
        expect(expectedTimeline.at(1)?.call_to_action_items).toBeUndefined();
        expect(expectedTimeline.at(2)?.call_to_action_items).toHaveLength(2);
    });

    it('should request latest table content and check if it is outdated through the choreStore when tableContentUpdate is dispatched', async () => {
        const spyOnApi = vi
            .spyOn(callToActionApi, 'getAll')
            .mockImplementation(() =>
                Promise.resolve({ currentPage: 1, data: [fakeCallToAction], from: 0, lastPage: 2, to: 20, total: 40 })
            );

        const SpyOnChoreStore = vi
            .spyOn(useChoreStore(), 'checkIfTableContentOutdated')
            .mockImplementation(vi.fn(noop));

        // GIVEN a callToActionStore
        const callToActionStore = useCallToActionStore();

        // WHEN the tableContentUpdate action is dispatched
        await callToActionStore.tableContentUpdate();

        // THEN the latest table content is requested
        expect(spyOnApi).toHaveBeenCalledTimes(1);

        // AND checked if outdated through the choreStore
        expect(SpyOnChoreStore).toHaveBeenCalledWith([fakeCallToAction], expect.any(String));
    });

    it('should request latest table content and do nothing when no data available', async () => {
        vi.spyOn(callToActionApi, 'getAll').mockImplementation(() =>
            Promise.resolve({ currentPage: 1, data: [], from: 0, lastPage: 2, to: 20, total: 40 })
        );

        const SpyOnChoreStore = vi
            .spyOn(useChoreStore(), 'checkIfTableContentOutdated')
            .mockImplementation(() => vi.fn());

        // GIVEN a callToActionStore
        const callToActionStore = useCallToActionStore();

        // WHEN the tableContentUpdate action is dispatched
        await callToActionStore.tableContentUpdate();

        // THEN do nothing
        expect(SpyOnChoreStore).toHaveBeenCalledTimes(0);
    });

    it('should set a backend error through the choreStore when tableContentUpdate is dispatched and the api request fails', async () => {
        const SpyOnChoreStore = vi.spyOn(useChoreStore(), 'setBackendError').mockImplementation(vi.fn() as any);

        // GIVEN a callToActionStore
        const callToActionStore = useCallToActionStore();

        // AND a failing api request
        vi.spyOn(callToActionApi, 'getAll').mockImplementation(() => Promise.reject(fakeError));

        // WHEN the tableContentUpdate action is dispatched
        await callToActionStore.tableContentUpdate();

        // THEN the backend error is set through the choreStore
        expect(SpyOnChoreStore).toHaveBeenCalledWith(fakeError);
    });

    it('should check if the selected call to action is still available with an api request when checkSelectedAvailability is dispatched', async () => {
        const spyOnApi = vi.spyOn(callToActionApi, 'get').mockImplementation(() => Promise.resolve(fakeCallToAction));

        // GIVEN a selected call to action
        const callToActionStore = useCallToActionStore();
        callToActionStore.selected = fakeCallToAction;

        // WHEN the checkSelectedAvailability action is dispatched
        await callToActionStore.checkSelectedAvailability();

        // THEN the availability of the selected call to action is checked with an api request
        expect(spyOnApi).toHaveBeenCalledTimes(1);
    });

    it('should do nothing when checkSelectedAvailability is dispatched given there is no selected call to action', async () => {
        const spyOnApi = vi.spyOn(callToActionApi, 'get').mockImplementation(() => Promise.resolve(fakeCallToAction));

        // GIVEN a callToActionStore without a selected call to action
        const callToActionStore = useCallToActionStore();

        // WHEN the checkSelectedAvailability action is dispatched
        await callToActionStore.checkSelectedAvailability();

        // THEN the availability is not checked
        expect(spyOnApi).toHaveBeenCalledTimes(0);
    });

    it('should set a backend error through the choreStore when checkSelectedAvailability is dispatched and the api request fails', async () => {
        const SpyOnChoreStore = vi.spyOn(useChoreStore(), 'setBackendError').mockImplementation(vi.fn() as any);

        // GIVEN a callToActionStore
        const callToActionStore = useCallToActionStore();
        callToActionStore.selected = fakeCallToAction;

        // AND a failing api request
        vi.spyOn(callToActionApi, 'get').mockImplementation(() => Promise.reject(fakeError));

        // WHEN the checkSelectedAvailability action is dispatched
        await callToActionStore.checkSelectedAvailability();

        // THEN the backend error is set through the choreStore
        expect(SpyOnChoreStore).toHaveBeenCalledWith(fakeError);
    });

    it('should complete the selected call to action with an api request when completeSelected is dispatched', async () => {
        const spyOnApi = vi
            .spyOn(callToActionApi, 'complete')
            .mockImplementation(() => Promise.resolve(fakeCallToAction));

        // GIVEN a selected call to action
        const callToActionStore = useCallToActionStore();
        callToActionStore.selected = fakeCallToAction;

        // WHEN the completeSelected action is dispatched
        await callToActionStore.completeSelected(fakerjs.lorem.paragraph());

        // THEN the selected call to action is completed with an api request
        expect(spyOnApi).toHaveBeenCalledTimes(1);
    });

    it('should stop polling for availability given the completeSelected api request was successful', async () => {
        const SpyOnChoreStore = vi.spyOn(useChoreStore(), 'stopPollingSelected').mockImplementation(vi.fn());

        // GIVEN a selected call to action
        const callToActionStore = useCallToActionStore();
        callToActionStore.selected = fakeCallToAction;

        // WHEN the completeSelected action is dispatched
        await callToActionStore.completeSelected(fakerjs.lorem.paragraph());

        // THEN the polling for availability of the selected call to action is stopped
        expect(SpyOnChoreStore).toHaveBeenCalledTimes(1);
    });

    it('should remove the selected call to action from the table content given the completeSelected api request was successful', async () => {
        // GIVEN a selected call to action and tableContent in store
        const callToActionStore = useCallToActionStore();
        callToActionStore.selected = fakeCallToAction;
        callToActionStore.tableContent = [fakeCallToAction];

        // WHEN the completeSelected action is dispatched
        await callToActionStore.completeSelected(fakerjs.lorem.paragraph());

        // THEN the selected call to action is removed from the table content
        expect(callToActionStore.tableContent).toStrictEqual([]);
    });

    it('should deselect the selected call to action given the completeSelected api request was successful', async () => {
        // GIVEN a selected call to action
        const callToActionStore = useCallToActionStore();
        callToActionStore.selected = fakeCallToAction;

        // WHEN the completeSelected action is dispatched
        await callToActionStore.completeSelected(fakerjs.lorem.paragraph());

        // THEN the selected call to action is deselected
        expect(callToActionStore.selected).toBe(null);
    });

    it('should do nothing when completeSelected is dispatched given there is no selected call to action', async () => {
        const spyOnApi = vi
            .spyOn(callToActionApi, 'complete')
            .mockImplementation(() => Promise.resolve(fakeCallToAction));

        // GIVEN a callToActionStore without a selected call to action
        const callToActionStore = useCallToActionStore();

        // WHEN the completeSelected action is dispatched
        await callToActionStore.completeSelected(fakerjs.lorem.paragraph());

        // THEN the availability is not checked
        expect(spyOnApi).toHaveBeenCalledTimes(0);
    });

    it('should set a backend error through the choreStore when completeSelected is dispatched and the api request fails', async () => {
        const SpyOnChoreStore = vi.spyOn(useChoreStore(), 'setBackendError').mockImplementation(vi.fn() as any);

        // GIVEN a callToActionStore
        const callToActionStore = useCallToActionStore();
        callToActionStore.selected = fakeCallToAction;

        // AND a failing api request
        vi.spyOn(callToActionApi, 'complete').mockImplementation(() => Promise.reject(fakeError));

        // WHEN the completeSelected action is dispatched
        await callToActionStore.completeSelected(fakerjs.lorem.paragraph());

        // THEN the backend error is set through the choreStore
        expect(SpyOnChoreStore).toHaveBeenCalledWith(fakeError);
    });

    it('should drop the selected call to action with an api request when dropSelected is dispatched', async () => {
        const spyOnApi = vi
            .spyOn(callToActionApi, 'deleteAssignment')
            .mockImplementation(() => Promise.resolve(fakeCallToAction));

        // GIVEN a selected call to action
        const callToActionStore = useCallToActionStore();
        callToActionStore.selected = fakeCallToAction;

        // WHEN the dropSelected action is dispatched
        await callToActionStore.dropSelected(fakerjs.lorem.paragraph());

        // THEN the selected call to action is dropped with an api request
        expect(spyOnApi).toHaveBeenCalledTimes(1);
    });

    it('should remove the assignment from the call to action when the dropSelected api request was successful', async () => {
        // GIVEN a selected call to action
        const callToActionStore = useCallToActionStore();
        callToActionStore.selected = fakeAssignedCTA;
        const droppedCallToAction: CallToActionResponse = {
            ...fakeAssignedCTA,
            ...{
                assignedUserUuid: null,
            },
        };

        // AND a successful api request that returns the dropped call to action
        vi.spyOn(callToActionApi, 'deleteAssignment').mockImplementation(() => Promise.resolve(droppedCallToAction));

        // WHEN the dropSelected action is dispatched
        await callToActionStore.dropSelected(fakerjs.lorem.paragraph());

        // THEN the returned call to action is stored as selected
        expect(callToActionStore.selected).toStrictEqual(droppedCallToAction);
    });

    it('should dispatch the updateSelectedInTable action given the dropSelected api request was successful', async () => {
        // GIVEN a selected call to action
        const callToActionStore = useCallToActionStore();
        callToActionStore.selected = fakeCallToAction;

        // AND a successful api request
        vi.spyOn(callToActionApi, 'deleteAssignment').mockImplementation(() => Promise.resolve(fakeCallToAction));

        const SpyOnAction = vi
            .spyOn(useCallToActionStore(), 'updateSelectedInTable')
            .mockImplementation(vi.fn() as any);

        // WHEN the dropSelected action is dispatched
        await callToActionStore.dropSelected(fakerjs.lorem.paragraph());

        // THEN the updateSelectedInTable is dispatched
        expect(SpyOnAction).toHaveBeenCalledTimes(1);
    });

    it('should do nothing when dropSelected is dispatched given there is no selected call to action', async () => {
        const spyOnApi = vi
            .spyOn(callToActionApi, 'deleteAssignment')
            .mockImplementation(() => Promise.resolve(fakeCallToAction));

        // GIVEN a callToActionStore without a selected call to action
        const callToActionStore = useCallToActionStore();

        // WHEN the dropSelected action is dispatched
        await callToActionStore.dropSelected(fakerjs.lorem.paragraph());

        // THEN the availability is not checked
        expect(spyOnApi).toHaveBeenCalledTimes(0);
    });

    it('should set a backend error through the choreStore when dropSelected is dispatched and the api request fails', async () => {
        const SpyOnChoreStore = vi.spyOn(useChoreStore(), 'setBackendError').mockImplementation(vi.fn() as any);

        // GIVEN a callToActionStore
        const callToActionStore = useCallToActionStore();
        callToActionStore.selected = fakeCallToAction;

        // AND a failing api request
        vi.spyOn(callToActionApi, 'deleteAssignment').mockImplementation(() => Promise.reject(fakeError));

        // WHEN the dropSelected action is dispatched
        await callToActionStore.dropSelected(fakerjs.lorem.paragraph());

        // THEN the backend error is set through the choreStore
        expect(SpyOnChoreStore).toHaveBeenCalledWith(fakeError);
    });

    it('should get content for the call to action table with an api request when fetchTableContent is dispatched', async () => {
        const spyOnApi = vi
            .spyOn(callToActionApi, 'getAll')
            .mockImplementation(() =>
                Promise.resolve({ currentPage: 1, data: [fakeCallToAction], from: 0, lastPage: 2, to: 20, total: 40 })
            );

        // GIVEN a callToActionStore
        const callToActionStore = useCallToActionStore();

        // WHEN the fetchTableContent action is dispatched
        await callToActionStore.fetchTableContent();

        // THEN the table content is fetched with an api request
        expect(spyOnApi).toHaveBeenCalledTimes(1);
    });

    it('should store the returned table content given the fetchTableContent api request was successful', async () => {
        // GIVEN a callToActionStore
        const callToActionStore = useCallToActionStore();

        // AND a successful api request that returns the table content
        vi.spyOn(callToActionApi, 'getAll').mockImplementation(() =>
            Promise.resolve({ currentPage: 1, data: [fakeCallToAction], from: 0, lastPage: 2, to: 20, total: 40 })
        );

        // WHEN the fetchTableContent action is dispatched
        await callToActionStore.fetchTableContent();

        // THEN the returned table content is stored
        expect(callToActionStore.tableContent).toStrictEqual([fakeCallToAction]);
    });

    it('should start checking if the table content is outdated through the choreStore given the fetchTableContent api request was successful', async () => {
        const SpyOnChoreStore = vi
            .spyOn(useChoreStore(), 'startPollingTableContent')
            .mockImplementation(vi.fn() as any);
        const SpyOnAction = vi.spyOn(useCallToActionStore(), 'tableContentUpdate').mockImplementation(vi.fn() as any);

        // GIVEN a callToActionStore
        const callToActionStore = useCallToActionStore();

        // AND a successful api request that returns the table content
        vi.spyOn(callToActionApi, 'getAll').mockImplementation(() =>
            Promise.resolve({ currentPage: 1, data: [fakeCallToAction], from: 0, lastPage: 2, to: 20, total: 40 })
        );

        // WHEN the fetchTableContent action is dispatched
        await callToActionStore.fetchTableContent();

        // THEN the startPollingTableContent action is dispatched with the tableContentUpdate action as callback function
        expect(SpyOnChoreStore).toHaveBeenCalledWith(SpyOnAction);
    });

    it('should set a backend error through the choreStore when fetchTableContent is dispatched and the api request fails', async () => {
        const SpyOnChoreStore = vi.spyOn(useChoreStore(), 'setBackendError').mockImplementation(vi.fn() as any);

        // GIVEN a callToActionStore
        const callToActionStore = useCallToActionStore();

        // AND a failing api request
        vi.spyOn(callToActionApi, 'getAll').mockImplementation(() => Promise.reject(fakeError));

        // WHEN the fetchTableContent action is dispatched
        await callToActionStore.fetchTableContent();

        // THEN the backend error is set through the choreStore
        expect(SpyOnChoreStore).toHaveBeenCalledWith(fakeError);
    });

    it('should increment the table page by 1 when incrementTablePage is dispatched', () => {
        // GIVEN a callToAction Store with the table page set to 1 as default
        const callToActionStore = useCallToActionStore();
        expect(callToActionStore.table.page).toBe(1);

        // WHEN the incrementTablePage action is dispatched
        callToActionStore.incrementTablePage();

        // THEN the table page is incremented by 1
        expect(callToActionStore.table.page).toBe(2);
    });

    it('should pickup the given call to action with an api request when pickupSelected is dispatched', async () => {
        const spyOnApi = vi
            .spyOn(callToActionApi, 'assignToUser')
            .mockImplementation(() => Promise.resolve(fakeCallToAction));

        // GIVEN a selected call to action
        const callToActionStore = useCallToActionStore();
        callToActionStore.selected = fakeCallToAction;

        // WHEN the pickupSelected action is dispatched
        await callToActionStore.pickupSelected();

        // THEN the given call to action is picked up with an api request
        expect(spyOnApi).toHaveBeenCalledWith(fakeCallToAction.uuid);
    });

    it('should store the returned call to action as selected given the pickupSelected api request was successful', async () => {
        // GIVEN a selected call to action
        const callToActionStore = useCallToActionStore();
        callToActionStore.selected = fakeCallToAction;

        // AND a successful api request that returns the picked up call to action
        vi.spyOn(callToActionApi, 'assignToUser').mockImplementation(() => Promise.resolve(fakeAssignedCTA));

        // WHEN the pickupSelected action is dispatched
        await callToActionStore.pickupSelected();

        // THEN the returned call to action is stored as selected
        expect(callToActionStore.selected).toBe(fakeAssignedCTA);
    });

    it('should dispatch the updateSelectedInTable action given the pickupSelected api request was successful', async () => {
        // GIVEN a selected call to action
        const callToActionStore = useCallToActionStore();
        callToActionStore.selected = fakeCallToAction;

        // AND a successful api request
        vi.spyOn(callToActionApi, 'assignToUser').mockImplementation(() => Promise.resolve(fakeCallToAction));

        const SpyOnAction = vi
            .spyOn(useCallToActionStore(), 'updateSelectedInTable')
            .mockImplementation(vi.fn() as any);

        // WHEN the pickupSelected action is dispatched
        await callToActionStore.pickupSelected();

        // THEN the updateSelectedInTable is dispatched
        expect(SpyOnAction).toHaveBeenCalledTimes(1);
    });

    it('should do nothing when pickupSelected is dispatched given there is no selected call to action', async () => {
        const spyOnApi = vi
            .spyOn(callToActionApi, 'assignToUser')
            .mockImplementation(() => Promise.resolve(fakeCallToAction));

        // GIVEN a callToActionStore without a selected call to action
        const callToActionStore = useCallToActionStore();

        // WHEN the pickupSelected action is dispatched
        await callToActionStore.pickupSelected();

        // THEN the pickup request was not made
        expect(spyOnApi).toHaveBeenCalledTimes(0);
    });

    it('should set a backend error through the choreStore when pickupSelected is dispatched and the api request fails', async () => {
        const SpyOnChoreStore = vi.spyOn(useChoreStore(), 'setBackendError').mockImplementation(vi.fn() as any);

        // GIVEN a callToActionStore with a selected call to action
        const callToActionStore = useCallToActionStore();
        callToActionStore.selected = fakeCallToAction;

        // AND a failing api request
        vi.spyOn(callToActionApi, 'assignToUser').mockImplementation(() => Promise.reject(fakeError));

        // WHEN the pickupSelected action is dispatched
        await callToActionStore.pickupSelected();

        // THEN the backend error is set through the choreStore
        expect(SpyOnChoreStore).toHaveBeenCalledWith(fakeError);
    });

    it('should deselect the selected call to action when pickupSelected is dispatched and the api request fails', async () => {
        // GIVEN a callToActionStore with a selected call to action
        const callToActionStore = useCallToActionStore();
        callToActionStore.selected = fakeCallToAction;

        // AND a failing api request
        vi.spyOn(callToActionApi, 'assignToUser').mockImplementation(() => Promise.reject(fakeError));

        // WHEN the pickupSelected action is dispatched
        await callToActionStore.pickupSelected();

        // THEN the selected call to action is deselected
        expect(callToActionStore.selected).toBe(null);
    });

    it('should stop polling for availability through the choreStore when pickupSelected is dispatched and the api request fails', async () => {
        const SpyOnChoreStore = vi.spyOn(useChoreStore(), 'stopPollingSelected').mockImplementation(vi.fn());

        // GIVEN a callToActionStore with a selected call to action
        const callToActionStore = useCallToActionStore();
        callToActionStore.selected = fakeCallToAction;

        // AND a failing api request
        vi.spyOn(callToActionApi, 'assignToUser').mockImplementation(() => Promise.reject(fakeError));

        // WHEN the pickupSelected action is dispatched
        await callToActionStore.pickupSelected();

        // THEN the polling for availability of the selected call to action is stopped
        expect(SpyOnChoreStore).toHaveBeenCalledTimes(1);
    });

    it('should reset table related values when the resetTable action is dispatched', () => {
        // GIVEN a callToActionStore with default table values
        const callToActionStore = useCallToActionStore();

        const defaultTable = callToActionStore.table;
        const defaultTableContent = callToActionStore.tableContent;

        // AND a mutation in the table related values
        const fakeInfiniteId = fakerjs.date.recent().valueOf();
        callToActionStore.table = {
            infiniteId: fakeInfiniteId,
            page: fakerjs.number.int(),
            perPage: defaultTable.perPage,
        };

        callToActionStore.tableContent = [fakeCallToAction];

        // WHEN the resetTable action is dispatched
        callToActionStore.resetTable();

        // THEN the table related values should be reset
        expect(callToActionStore.table.page).toBe(defaultTable.page);
        expect(callToActionStore.table.infiniteId).not.toBe(fakeInfiniteId);
        expect(callToActionStore.tableContent).toStrictEqual(defaultTableContent);
    });

    it('should select the given call to action with an api request when the select action is dispatched', async () => {
        const spyOnApi = vi.spyOn(callToActionApi, 'get').mockImplementation(() => Promise.resolve(fakeCallToAction));

        // GIVEN a callToAction store
        const callToActionStore = useCallToActionStore();

        // And a call to action to select
        const fakeCTAUuid = fakerjs.string.uuid();

        // WHEN the select action is dispatched
        await callToActionStore.select(fakeCTAUuid);

        // THEN the given call to action is selected with an api request
        expect(spyOnApi).toHaveBeenCalledWith(fakeCTAUuid);
    });

    it('should store the returned call to action as selected given the select api request was successful', async () => {
        // GIVEN a callToAction Store with a selected call to action
        const callToActionStore = useCallToActionStore();
        callToActionStore.selected = fakeCallToAction;

        // And a new call to action to select
        const fakeCTAUuid = fakerjs.string.uuid();

        // AND a successful api request that returns the selected call to action
        vi.spyOn(callToActionApi, 'get').mockImplementation(() => Promise.resolve(fakeAssignedCTA));

        // WHEN the select action is dispatched
        await callToActionStore.select(fakeCTAUuid);

        // THEN the returned call to action is stored as selected
        expect(callToActionStore.selected).toBe(fakeAssignedCTA);
    });

    it('should start polling for availability of the selected call to action through the choreStore given the select api request was successful', async () => {
        const SpyOnChoreStore = vi.spyOn(useChoreStore(), 'startPollingSelected').mockImplementation(vi.fn(noop));
        const SpyOnAction = vi
            .spyOn(useCallToActionStore(), 'checkSelectedAvailability')
            .mockImplementation(vi.fn() as any);

        // GIVEN a callToActionStore
        const callToActionStore = useCallToActionStore();

        // And a call to action to select
        const fakeCTAUuid = fakerjs.string.uuid();

        // AND a successful api request that returns the selected call to action
        vi.spyOn(callToActionApi, 'get').mockImplementation(() => Promise.resolve(fakeCallToAction));

        // WHEN the select action is dispatched
        await callToActionStore.select(fakeCTAUuid);

        // THEN the startPollingSelected action is dispatched with the checkSelectedAvailability action as callback function
        expect(SpyOnChoreStore).toHaveBeenCalledWith(SpyOnAction);
    });

    it('should set a backend error through the choreStore when select is dispatched and the api request fails', async () => {
        const SpyOnChoreStore = vi.spyOn(useChoreStore(), 'setBackendError').mockImplementation(vi.fn() as any);

        // GIVEN a callToActionStore
        const callToActionStore = useCallToActionStore();

        // And a call to action to select
        const fakeCTAUuid = fakerjs.string.uuid();

        // AND a failing api request
        vi.spyOn(callToActionApi, 'get').mockImplementation(() => Promise.reject(fakeError));

        // WHEN the select action is dispatched
        await callToActionStore.select(fakeCTAUuid);

        // THEN the backend error is set through the choreStore
        expect(SpyOnChoreStore).toHaveBeenCalledWith(fakeError);
    });

    it('should update table sort properties when setTableSort is dispatched', () => {
        // GIVEN a callToActionStore with default table values
        const callToActionStore = useCallToActionStore();

        expect(callToActionStore.table.order).toBe(undefined);
        expect(callToActionStore.table.sort).toBe(undefined);

        // WHEN the setTableSort action is dispatched with new sorting rules
        callToActionStore.setTableSort({ order: 'asc', sort: CallToActionSortOptions.CREATED_AT });

        // THEN the table sort related values should be updated
        expect(callToActionStore.table.order).toBe('asc');
        expect(callToActionStore.table.sort).toBe(CallToActionSortOptions.CREATED_AT);
    });

    it('should update the selected call to action in the table content when updateSelectedInTable is dispatched', () => {
        // GIVEN a callToActionStore with table content
        const callToActionStore = useCallToActionStore();
        callToActionStore.tableContent = [fakeCallToAction];

        // WHEN the updateSelectedInTable action is dispatched with a call to action that is in the table content (based on uuid)
        callToActionStore.updateSelectedInTable(fakeAssignedCTA);

        // THEN the call to action in the table conten should be updated
        expect(callToActionStore.tableContent).toStrictEqual([fakeAssignedCTA]);
    });

    it('should couple notes to events when coupleNotesToEvents is dispatched', () => {
        // GIVEN a callToActionStore
        const callToActionStore = useCallToActionStore();

        // WHEN the coupleNotesToEvents action is dispatched with an array of CallToAction events
        const completionDateTime = fakerjs.date.recent().toISOString();
        const returnDateTime = fakerjs.date.recent().toISOString();
        const givenEvents = [
            generateFakeCallToActionHistoryItem(completionDateTime, CallToActionEventV1.VALUE_note),
            generateFakeCallToActionHistoryItem(completionDateTime, CallToActionEventV1.VALUE_completed),
            generateFakeCallToActionHistoryItem(),
            generateFakeCallToActionHistoryItem(returnDateTime, CallToActionEventV1.VALUE_note),
            generateFakeCallToActionHistoryItem(returnDateTime, CallToActionEventV1.VALUE_returned),
            generateFakeCallToActionHistoryItem(fakerjs.date.soon().toISOString(), CallToActionEventV1.VALUE_note),
            generateFakeCallToActionHistoryItem(),
        ];
        const expectedEvents = callToActionStore.coupleNotesToEvents(givenEvents);

        // THEN the notes should be coupled to the events they belong to
        expect(expectedEvents[0].callToActionEvent).toBe(givenEvents[1].callToActionEvent);
        expect(expectedEvents[0].note).toBe(givenEvents[0].note);
        expect(expectedEvents[2].callToActionEvent).toBe(givenEvents[4].callToActionEvent);
        expect(expectedEvents[2].note).toBe(givenEvents[3].note);
        expect(expectedEvents[3].callToActionEvent).toBe(givenEvents[5].callToActionEvent);
    });
});
