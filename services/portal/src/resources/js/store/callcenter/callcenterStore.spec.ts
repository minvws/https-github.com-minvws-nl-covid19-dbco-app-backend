import { setActivePinia, createPinia } from 'pinia';
import { callcenterApi } from '@dbco/portal-api';
import { RequestState, useCallcenterStore } from './callcenterStore';
import { fakeSearchResult } from '@/utils/__fakes__/callcenter';
import * as showToast from '@/utils/showToast';
import { fakerjs } from '@/utils/test';
import type { CallcenterSearchResult } from '@dbco/portal-api/callcenter.dto';

describe('callcenterStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });
    afterEach(() => {
        vi.clearAllMocks();
    });

    it('should store results after a successful search attempt', async () => {
        const fakeSearchResults = [fakeSearchResult(), fakeSearchResult()] as CallcenterSearchResult[];
        const spyOnApi = vi.spyOn(callcenterApi, 'search').mockImplementation(() => Promise.resolve(fakeSearchResults));
        const callcenterStore = useCallcenterStore();

        await callcenterStore.search({
            searchData: {
                dateOfBirth: fakerjs.date.past().toDateString(),
                lastThreeBsnDigits: fakerjs.string.numeric(3),
                postalCode: fakerjs.location.zipCode('####??'),
                houseNumber: fakerjs.location.buildingNumber(),
                houseNumberSuffix: fakerjs.location.secondaryAddress(),
            },
        });

        expect(spyOnApi).toHaveBeenCalledTimes(1);
        expect(callcenterStore.searchResults).toStrictEqual(fakeSearchResults);
    });

    it('should set request state to resolved after successful search attempt', async () => {
        const fakeSearchResults = [fakeSearchResult()] as CallcenterSearchResult[];
        vi.spyOn(callcenterApi, 'search').mockImplementation(() => Promise.resolve(fakeSearchResults));
        const callcenterStore = useCallcenterStore();

        await callcenterStore.search({
            searchData: {
                dateOfBirth: fakerjs.date.past().toDateString(),
                lastThreeBsnDigits: fakerjs.string.numeric(3),
                postalCode: fakerjs.location.zipCode('####??'),
                houseNumber: fakerjs.location.buildingNumber(),
                houseNumberSuffix: fakerjs.location.secondaryAddress(),
            },
        });

        expect(callcenterStore.searchState).toBe(RequestState.Resolved);
    });

    it('should set request state to rejected after unsuccessful search attempt', async () => {
        const fakeSearchResults = [fakeSearchResult()] as CallcenterSearchResult[];
        vi.spyOn(callcenterApi, 'search').mockImplementation(() => Promise.reject(fakeSearchResults));
        vi.spyOn(showToast, 'default').mockImplementationOnce(() => vi.fn());
        const callcenterStore = useCallcenterStore();

        await callcenterStore.search({
            searchData: {
                dateOfBirth: fakerjs.date.past().toDateString(),
                lastThreeBsnDigits: fakerjs.string.numeric(3),
                postalCode: fakerjs.location.zipCode('####??'),
                houseNumber: fakerjs.location.buildingNumber(),
                houseNumberSuffix: fakerjs.location.secondaryAddress(),
            },
        });

        expect(callcenterStore.searchState).toBe(RequestState.Rejected);
    });

    it('should show toast error message after unsuccessful search attempt', async () => {
        const fakeSearchResults = [fakeSearchResult()] as CallcenterSearchResult[];
        vi.spyOn(callcenterApi, 'search').mockImplementation(() => Promise.reject(fakeSearchResults));
        const toastSpy = vi.spyOn(showToast, 'default').mockImplementationOnce(() => vi.fn());
        const callcenterStore = useCallcenterStore();

        await callcenterStore.search({
            searchData: {
                dateOfBirth: fakerjs.date.past().toDateString(),
                lastThreeBsnDigits: fakerjs.string.numeric(3),
                postalCode: fakerjs.location.zipCode('####??'),
                houseNumber: fakerjs.location.buildingNumber(),
                houseNumberSuffix: fakerjs.location.secondaryAddress(),
            },
        });

        expect(toastSpy).toHaveBeenCalledOnce();
    });

    it('should set searchValidationErrors if returned from the BE', async () => {
        const validationResponse = { response: { data: { errors: { dateOfBirth: ['Date of birth is required'] } } } };
        vi.spyOn(callcenterApi, 'search').mockImplementation(() => Promise.reject(validationResponse));
        const callcenterStore = useCallcenterStore();

        await callcenterStore.search({
            searchData: {
                dateOfBirth: fakerjs.date.past().toDateString(),
                lastThreeBsnDigits: fakerjs.string.numeric(3),
                postalCode: fakerjs.location.zipCode('####??'),
                houseNumber: fakerjs.location.buildingNumber(),
                houseNumberSuffix: fakerjs.location.secondaryAddress(),
            },
        });

        expect(callcenterStore.searchValidationErrors).toStrictEqual({
            errors: { dateOfBirth: '{"fatal":["Date of birth is required"]}' },
        });
    });

    it('should set searchedAllFields to false after a successful search attempt with minimum form data', async () => {
        const fakeSearchResults = [fakeSearchResult()] as CallcenterSearchResult[];
        vi.spyOn(callcenterApi, 'search').mockImplementation(() => Promise.resolve(fakeSearchResults));
        const callcenterStore = useCallcenterStore();

        await callcenterStore.search({
            searchData: {
                dateOfBirth: fakerjs.date.past().toDateString(),
                lastThreeBsnDigits: fakerjs.string.numeric(3),
                postalCode: fakerjs.location.zipCode('####??'),
                houseNumber: fakerjs.location.buildingNumber(),
                houseNumberSuffix: fakerjs.location.secondaryAddress(),
            },
        });

        expect(callcenterStore.searchedAllFields).toBe(false);
    });

    it('should set searchedAllFields to true after a successful search attempt with lastname and telephone form data', async () => {
        const fakeSearchResults = [fakeSearchResult()] as CallcenterSearchResult[];
        vi.spyOn(callcenterApi, 'search').mockImplementation(() => Promise.resolve(fakeSearchResults));
        const callcenterStore = useCallcenterStore();

        await callcenterStore.search({
            searchData: {
                dateOfBirth: fakerjs.date.past().toDateString(),
                lastThreeBsnDigits: fakerjs.string.numeric(3),
                postalCode: fakerjs.location.zipCode('####??'),
                houseNumber: fakerjs.location.buildingNumber(),
                houseNumberSuffix: fakerjs.location.secondaryAddress(),
                lastname: fakerjs.person.lastName(),
                phone: fakerjs.phone.number(),
            },
        });

        expect(callcenterStore.searchedAllFields).toBe(true);
    });

    it('should set store data to default after reset', () => {
        const fakeSearchResults = [fakeSearchResult()] as CallcenterSearchResult[];
        const callcenterStore = useCallcenterStore();

        callcenterStore.searchState = RequestState.Resolved;
        callcenterStore.searchResults = fakeSearchResults;
        callcenterStore.searchValidationErrors = { errors: { dateOfBirth: 'error' } };
        callcenterStore.searchedAllFields = true;

        callcenterStore.reset();

        expect(callcenterStore.searchState).toBe(RequestState.Idle);
        expect(callcenterStore.searchResults).toStrictEqual([]);
        expect(callcenterStore.searchValidationErrors).toStrictEqual(null);
        expect(callcenterStore.searchedAllFields).toBe(false);
    });
});
