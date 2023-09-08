import { placeApi } from '@dbco/portal-api';
import { fakerjs } from '@/utils/test';
import { fakePlaceCase } from '@/utils/__fakes__/place';
import { fakeError } from '@/utils/__fakes__/shared';
import { createPinia, setActivePinia } from 'pinia';
import { useChoreStore } from '../chore/choreStore';
import { usePlaceCasesStore, useResetIndexCount } from './clusterStore';
import type { PlaceCasesResponse } from '@dbco/portal-api/place.dto';

describe('resetIndexCount Store', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });
    afterEach(() => {
        vi.clearAllMocks();
    });

    it('should reset Cluster count, respond with success', async () => {
        const store = useResetIndexCount();
        const reset = vi.spyOn(placeApi, 'resetIndexCount').mockImplementation(() => Promise.resolve());

        const placeUuid = fakerjs.string.uuid();
        await store.reset(placeUuid);

        expect(reset).toHaveBeenCalledWith(placeUuid);
    });

    it('should propagate error to choreStore', async () => {
        const store = useResetIndexCount();
        const setBackendError = vi.spyOn(useChoreStore(), 'setBackendError');

        const error = new Error();
        vi.spyOn(placeApi, 'resetIndexCount').mockImplementation(() => Promise.reject(error));

        await store.reset(fakerjs.string.uuid());

        expect(setBackendError).toBeCalledWith(error);
    });
});

describe('placeCases Store', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });

    it('should get content for the placeCases table with an api request when fetchCases is dispatched', async () => {
        const spyOnApi = vi.spyOn(placeApi, 'getCases').mockImplementation(() =>
            Promise.resolve({
                currentPage: 1,
                data: [fakePlaceCase() as PlaceCasesResponse],
                from: 0,
                lastPage: 2,
                to: 20,
                total: 40,
            })
        );

        // GIVEN a placeCasesStore
        const placeCasesStore = usePlaceCasesStore();

        // WHEN the fetchCases action is dispatched
        await placeCasesStore.fetchCases(fakerjs.string.uuid());

        // THEN the cases are fetched with an api request
        expect(spyOnApi).toHaveBeenCalledTimes(1);
    });

    it('should store the returned cases given the fetchCases api request was successful', async () => {
        // GIVEN a placeCasesStore
        const placeCasesStore = usePlaceCasesStore();

        // AND a successful api request that returns the cases
        const cases = [fakePlaceCase() as PlaceCasesResponse, fakePlaceCase() as PlaceCasesResponse];
        vi.spyOn(placeApi, 'getCases').mockImplementation(() =>
            Promise.resolve({ currentPage: 1, data: cases, from: 0, lastPage: 2, to: 20, total: 40 })
        );

        // WHEN the fetchCases action is dispatched
        await placeCasesStore.fetchCases(fakerjs.string.uuid());

        // THEN the returned cases are stored
        expect(placeCasesStore.cases).toStrictEqual(cases);
    });

    it('should set a backend error through the choreStore when fetchCases is dispatched and the api request fails', async () => {
        const SpyOnChoreStore = vi.spyOn(useChoreStore(), 'setBackendError').mockImplementation(vi.fn() as any);

        // GIVEN a placeCasesStore
        const placeCasesStore = usePlaceCasesStore();

        // AND a failing api request
        vi.spyOn(placeApi, 'getCases').mockImplementation(() => Promise.reject(fakeError));

        // WHEN the fetchCases action is dispatched
        await placeCasesStore.fetchCases(fakerjs.string.uuid());

        // THEN the backend error is set through the choreStore
        expect(SpyOnChoreStore).toHaveBeenCalledWith(fakeError);
    });

    it('should increment the table page by 1 when incrementTablePage is dispatched', () => {
        // GIVEN a placeCasesStore Store with the table page set to 1 as default
        const placeCasesStore = usePlaceCasesStore();
        expect(placeCasesStore.table.page).toBe(1);

        // WHEN the incrementTablePage action is dispatched
        placeCasesStore.incrementTablePage();

        // THEN the table page is incremented by 1
        expect(placeCasesStore.table.page).toBe(2);
    });

    it('should not fetch more cases if first page has already been fetched', async () => {
        const spyOnApi = vi.spyOn(placeApi, 'getCases');

        // GIVEN a placeCasesStore Store with the table page set to 1 as default
        // AND the 1st table page has already been fetched
        const placeCasesStore = usePlaceCasesStore();
        placeCasesStore.table.fetchedPages = [1];

        // WHEN the fetchCases action is dispatched
        await placeCasesStore.fetchCases(fakerjs.string.uuid());

        // THEN the api is not called
        expect(spyOnApi).toHaveBeenCalledTimes(0);
    });

    it('should not fetch more cases if given page has already been fetched', async () => {
        const spyOnApi = vi.spyOn(placeApi, 'getCases');

        // GIVEN a placeCasesStore Store with the table page set to 2
        // AND the 1st table page has already been fetched
        const placeCasesStore = usePlaceCasesStore();
        placeCasesStore.table.page = 2;
        placeCasesStore.table.fetchedPages = [1, 2, 3];

        // WHEN the fetchCases action is dispatched
        await placeCasesStore.fetchCases(fakerjs.string.uuid());

        // THEN the api is not called
        expect(spyOnApi).toHaveBeenCalledTimes(0);
    });
});
