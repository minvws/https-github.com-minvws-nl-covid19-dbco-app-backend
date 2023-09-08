import { createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import type { RootStoreState } from '@/store';
import indexStore from './indexStore';
import { caseApi } from '@dbco/portal-api';
import type { IndexStoreState } from './indexStore';
import { fakeError } from '@/utils/__fakes__/shared';
import type { IndexUpdateContactStatusQueryDTO } from '@dbco/portal-api/case.dto';
import { ContactTracingStatusV1 } from '@dbco/enum';
import { fakerjs, flushCallStack } from '@/utils/test';
import * as formRequest from '@/components/form/ts/formRequest';

describe('indexStoreAction.ts', () => {
    afterEach(() => {
        vi.clearAllMocks();
    });

    const localVue = createLocalVue();
    localVue.use(Vuex);

    const getStore = (IndexStoreState: Partial<IndexStoreState>) => {
        const indexStoreModule = {
            ...indexStore,
            state: {
                ...indexStore.state,
                ...IndexStoreState,
            },
            actions: indexStore.actions,
            mutations: indexStore.mutations,
        };

        return new Vuex.Store<RootStoreState>({
            modules: {
                index: indexStoreModule,
            },
        });
    };

    it('should call updateContactStatus when UPDATE_CONTACT_STATUS is dispatched', async () => {
        const fakePayload: IndexUpdateContactStatusQueryDTO = {
            uuid: fakerjs.string.uuid(),
            statusIndexContactTracing: ContactTracingStatusV1.VALUE_bco_finished,
            statusExplanation: fakerjs.lorem.sentence(),
            casequalityFeedback: fakerjs.lorem.sentence(),
        };
        const spyOnFetch = vi
            .spyOn(caseApi, 'updateContactStatus')
            .mockImplementationOnce(() => Promise.resolve({ response: { data: {} } }));
        const store = getStore({});

        await store.dispatch('index/UPDATE_CONTACT_STATUS', fakePayload);

        expect(spyOnFetch).toHaveBeenCalledWith(fakePayload);
    });

    it('should return a message if UPDATE_CONTACT_STATUS fails', async () => {
        const spyOnFetch = vi
            .spyOn(caseApi, 'updateContactStatus')
            .mockImplementationOnce(() => Promise.reject(fakeError));
        const store = getStore({});
        const spyOnCommit = vi.spyOn(store, 'commit');

        try {
            await store.dispatch('index/UPDATE_CONTACT_STATUS');
        } catch (error) {
            expect(spyOnCommit).toHaveBeenCalledWith('index/SET_BACKEND_ERROR', fakeError, undefined);
            expect(error).toStrictEqual({ message: 'Er ging iets mis met het updaten van de status.' });
        }

        expect(spyOnFetch).toHaveBeenCalledTimes(1);
    });

    it('call getTestResults when GET_TEST_RESULTS is dispatched', async () => {
        const spyOnFetch = vi
            .spyOn(caseApi, 'getTestResults')
            .mockImplementationOnce(() => Promise.resolve({ response: { data: {} } }));
        const store = getStore({});

        await store.dispatch('index/GET_TEST_RESULTS');

        expect(spyOnFetch).toHaveBeenCalledTimes(1);
    });

    it('should set calendarData in state when fetched through load call', async () => {
        const fakeDateRange = {
            id: fakerjs.string.uuid(),
            type: fakerjs.lorem.word(),
            startDate: fakerjs.date.past(),
            endDate: fakerjs.date.soon(),
        };
        vi.spyOn(caseApi, 'getFragments').mockImplementationOnce(() =>
            Promise.resolve({
                data: {},
                computedData: {
                    calendarData: [fakeDateRange],
                },
            })
        );
        vi.spyOn(caseApi, 'getMeta').mockImplementationOnce(() => Promise.resolve({}));
        const store = getStore({});

        await store.dispatch('index/LOAD', '1234');
        await flushCallStack();

        expect(store.state.index.calendarData).toStrictEqual([fakeDateRange]);
    });

    it('should set calendarData in state when updated', async () => {
        const fakeDateRange = {
            id: fakerjs.string.uuid(),
            type: fakerjs.lorem.word(),
            startDate: fakerjs.date.past(),
            endDate: fakerjs.date.soon(),
        };
        vi.spyOn(formRequest, 'update').mockImplementationOnce(() =>
            Promise.resolve({
                isDuplicated: false,
                computedData: {
                    calendarData: [fakeDateRange],
                },
                errors: {},
            })
        );
        const store = getStore({});

        await store.dispatch('index/UPDATE_FORM_VALUE', {
            fakeFragment: { fakeFragmentProperty: fakerjs.string.uuid() },
        });
        await flushCallStack();

        expect(store.state.index.calendarData).toStrictEqual([fakeDateRange]);
    });

    it('should get formatted errors from load call', async () => {
        const fakePayload = {
            contact: {
                warning: {
                    failed: {
                        email: {
                            Email: ['filter'],
                        },
                    },
                    errors: {
                        email: ['Veld "E-mailadres" is geen geldig e-mailadres.'],
                    },
                },
            },
        };
        const expectedFormatting = {
            contact: {
                email: '{"warning":["Veld \\"E-mailadres\\" is geen geldig e-mailadres."]}',
            },
        };
        vi.spyOn(caseApi, 'getFragments').mockImplementationOnce(() =>
            Promise.resolve({
                data: {},
                validationResult: fakePayload,
            })
        );
        vi.spyOn(caseApi, 'getMeta').mockImplementationOnce(() => Promise.resolve({}));
        const store = getStore({});

        await store.dispatch('index/LOAD', '1234');
        await flushCallStack();

        expect(store.state.index.errors).toStrictEqual(expectedFormatting);
    });
});
