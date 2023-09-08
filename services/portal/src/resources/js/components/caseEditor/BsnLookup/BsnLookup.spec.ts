import { bsnApi } from '@dbco/portal-api';
import { BsnLookupError } from '@dbco/portal-api/bsn.dto';
import { BsnLookupType } from '@/components/form/ts/formTypes';
import { SharedActions } from '@/store/actions';
import type { IndexStoreState } from '@/store/index/indexStore';
import indexStore from '@/store/index/indexStore';
import { StoreType } from '@/store/storeType';
import type { TaskStoreState } from '@/store/task/taskStore';
import taskStore from '@/store/task/taskStore';
import type { UserInfoState } from '@/store/userInfo/userInfoStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import { PermissionV1 } from '@dbco/enum';
import { generateSafeHtml } from '@/utils/safeHtml';
import { fakerjs, flushCallStack, setupTest } from '@/utils/test';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import Vuex from 'vuex';
import BsnLookup from './BsnLookup.vue';

vi.mock('@dbco/portal-api/client/task.api', () => ({
    getFragments: vi.fn(() =>
        Promise.resolve({
            data: {
                general: {
                    firstname: fakerjs.person.firstName(),
                    lastname: null,
                },
                personalDetails: {
                    address: {},
                    dateOfBirth: '1990-01-01',
                },
            },
        })
    ),
}));

vi.mock('@dbco/portal-api/client/case.api', () => ({
    getFragments: vi.fn(() =>
        Promise.resolve({
            data: {
                general: {
                    schemaVersion: 1,
                    source: null,
                    reference: fakerjs.string.numeric(7),
                    organisation: {
                        uuid: fakerjs.string.uuid(),
                        name: 'Demo GGD1',
                    },
                    createdAt: fakerjs.date.past().toISOString(),
                },
                index: {
                    firstname: fakerjs.person.firstName(),
                    lastname: fakerjs.person.lastName(),
                    bsnNotes: null,
                    dateOfBirth: '1990-03-03',
                    address: {
                        schemaVersion: 1,
                        postalCode: fakerjs.location.zipCode('####??'),
                        houseNumber: fakerjs.string.numeric(3),
                        houseNumberSuffix: null,
                        street: fakerjs.location.street(),
                        town: fakerjs.location.city(),
                    },
                    bsnCensored: `******${fakerjs.string.numeric(3)}`,
                    bsnLetters: fakerjs.string.sample(2),
                    hasNoBsnOrAddress: null,
                },
            },
        })
    ),
    getMeta: vi.fn(() =>
        Promise.resolve({
            case: {
                uuid: fakerjs.string.uuid(),
                organisation: {
                    uuid: fakerjs.string.uuid(),
                    abbreviation: 'GGD1',
                    name: 'Demo GGD1',
                },
            },
        })
    ),
}));

vi.mock('@dbco/portal-api/client/bsn.api', () => ({
    bsnLookup: vi.fn(() => Promise.resolve({ guid: null, error: BsnLookupError.NO_MATCHING_RESULTS })),
    updateTaskBsn: vi.fn(() => Promise.resolve()),
    updateIndexBsn: vi.fn(() => Promise.resolve()),
}));

const createComponent = setupTest(
    async (
        localVue: VueConstructor,
        targetType: string,
        indexStoreState: Partial<IndexStoreState> = {},
        taskState: Partial<TaskStoreState> = {},
        userInfoState: Partial<UserInfoState> = { permissions: [PermissionV1.VALUE_caseUserEdit] },
        props: object = {}
    ) => {
        const taskStoreModule = {
            ...taskStore,
            state: { ...taskStore.state, ...taskState },
        };

        const indexStoreModule = {
            ...indexStore,
            state: { ...indexStore.state, ...indexStoreState },
        };

        const userInfoStoreModule = {
            ...userInfoStore,
            state: { ...userInfoStore.state, ...userInfoState },
        };

        const wrapper = shallowMount<BsnLookup>(BsnLookup, {
            localVue,
            propsData: {
                targetType,
                ...props,
            },
            store: new Vuex.Store({
                modules: {
                    task: taskStoreModule,
                    index: indexStoreModule,
                    userInfo: userInfoStoreModule,
                },
            }),
            stubs: {
                FormulateFormWrapper: true,
                FormulateForm: true,
                FormInfo: true,
            },
        });
        await wrapper.vm.$nextTick();
        return wrapper;
    }
);

describe('BsnLookup.vue', () => {
    beforeEach(() => {
        vi.restoreAllMocks();
    });

    it('should show index dataCheckedAlertLabel when bsnCensored is set', async () => {
        const indexState: Partial<IndexStoreState> = {
            uuid: fakerjs.string.uuid(),
            fragments: {
                index: {
                    firstname: fakerjs.person.firstName(),
                    lastname: fakerjs.person.lastName(),
                    bsnCensored: `******${fakerjs.string.numeric(3)}`,
                },
            },
        };

        const wrapper = await createComponent(BsnLookupType.Index, indexState, {});
        const duplicateCasesElement = wrapper.findByTestId('duplicate-cases');
        const formInfoElement = duplicateCasesElement.findComponent({ name: 'FormInfo' });
        expect(formInfoElement.props('text')).toBe(
            'Gecontroleerd in Basisregistratie Personen. Index is geïdentificeerd.'
        );
    });

    it('should show contact dataCheckedAlertLabel when bsnCensored is set', async () => {
        const taskState: Partial<TaskStoreState> = {
            uuid: fakerjs.string.uuid(),
            fragments: {
                general: {
                    firstname: fakerjs.person.firstName(),
                    lastname: fakerjs.person.lastName(),
                },
                personalDetails: {
                    address: {},
                    dateOfBirth: fakerjs.date.past().toISOString(),
                    bsnCensored: `******${fakerjs.string.numeric(3)}`,
                },
            },
        };

        const wrapper = await createComponent(BsnLookupType.Task, {}, taskState);
        const duplicateCasesElement = wrapper.findByTestId('duplicate-cases');
        const formInfoElement = duplicateCasesElement.findComponent({ name: 'FormInfo' });
        expect(formInfoElement.props('text')).toBe(
            'Gecontroleerd in Basisregistratie Personen. Contact is geïdentificeerd.'
        );
    });

    it('should show duplicate-cases when bsnCensored is set', async () => {
        const indexState: Partial<IndexStoreState> = {
            uuid: fakerjs.string.uuid(),
            fragments: {
                index: {
                    firstname: fakerjs.person.firstName(),
                    lastname: fakerjs.person.lastName(),
                    bsnCensored: `******${fakerjs.string.numeric(3)}`,
                },
            },
        };

        const wrapper = await createComponent(BsnLookupType.Index, indexState, {});

        const duplicateCasesElement = wrapper.findByTestId('duplicate-cases');

        expect(duplicateCasesElement.exists()).toBe(true);
    });

    it('this.flattenedFragments should be set initially', async () => {
        const indexState: Partial<IndexStoreState> = {
            uuid: fakerjs.string.uuid(),
            fragments: {
                index: {
                    firstname: fakerjs.person.firstName(),
                    lastname: fakerjs.person.lastName(),
                    bsnCensored: `******${fakerjs.string.numeric(3)}`,
                },
            },
        };

        const wrapper = await createComponent(BsnLookupType.Index, indexState);

        await wrapper.vm.$nextTick();

        const flattenedObject = {
            'index.firstname': indexState.fragments?.index?.firstname,
            'index.lastname': indexState.fragments?.index?.lastname,
            'index.bsnCensored': indexState.fragments?.index?.bsnCensored,
        };

        expect(wrapper.vm.flattenedFragments).toEqual(flattenedObject);
    });

    it(`should dispatch "${StoreType.INDEX}/${SharedActions.LOAD}" from the store when methods.refresh is being called and this.targetType === "${BsnLookupType.Index}"`, async () => {
        const indexState: Partial<IndexStoreState> = {
            uuid: fakerjs.string.uuid(),
            fragments: {
                index: {
                    firstname: fakerjs.person.firstName(),
                    lastname: fakerjs.person.lastName(),
                    address: {},
                    dateOfBirth: fakerjs.date.past().toISOString(),
                },
            },
        };

        const wrapper = await createComponent(BsnLookupType.Index, indexState);

        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');
        await wrapper.vm.$nextTick();

        wrapper.vm.refresh();

        expect(spyOnDispatch).toHaveBeenCalledWith(`${StoreType.INDEX}/${SharedActions.LOAD}`, indexState.uuid);
    });

    it(`should dispatch "${StoreType.TASK}/${SharedActions.LOAD}" from the store when methods.refresh is being called and this.targetType === "${BsnLookupType.Task}"`, async () => {
        const taskState: Partial<TaskStoreState> = {
            uuid: fakerjs.string.uuid(),
            fragments: {
                general: {
                    firstname: fakerjs.person.firstName(),
                    lastname: fakerjs.person.lastName(),
                },
                personalDetails: {
                    address: {},
                    dateOfBirth: fakerjs.date.past().toISOString(),
                },
            },
        };

        const wrapper = await createComponent(BsnLookupType.Task, {}, taskState);

        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');

        await wrapper.vm.$nextTick();

        await wrapper.vm.refresh();

        expect(spyOnDispatch).toHaveBeenCalledWith(`${StoreType.TASK}/${SharedActions.LOAD}`, taskState.uuid);
    });

    it('should show error if this.lookup has been clicked and not all fields were filled in', async () => {
        const taskState: Partial<TaskStoreState> = {
            uuid: fakerjs.string.uuid(),
            fragments: {
                general: {
                    firstname: fakerjs.person.firstName(),
                    lastname: fakerjs.person.lastName(),
                },
                personalDetails: {
                    bsnCensored: undefined,
                    address: {
                        postalCode: undefined,
                        houseNumber: undefined,
                        houseNumberSuffix: undefined,
                    },
                    dateOfBirth: fakerjs.date.past().toISOString(),
                },
            },
        };

        const wrapper = await createComponent(BsnLookupType.Task, {}, taskState);

        await wrapper.findByTestId('identify-button').trigger('click');
        await wrapper.findByTestId('lookup-button').trigger('click');

        // Use props here since FormInfo is stubbed
        expect(wrapper.findComponent({ name: 'FormInfo' }).props('text')).toStrictEqual(
            generateSafeHtml(
                '<strong>Let op:</strong> nog niet alle gegevens zijn ingevuld. Vul de gegevens aan en probeer het opnieuw.'
            )
        );
    });

    it('should show error if bsnApi.bsnLookup did not return a .guid property', async () => {
        const taskState: Partial<TaskStoreState> = {
            uuid: fakerjs.string.uuid(),
            fragments: {
                general: {
                    firstname: fakerjs.person.firstName(),
                    lastname: fakerjs.person.lastName(),
                },
                personalDetails: {
                    bsnCensored: undefined,
                    address: {
                        postalCode: fakerjs.location.zipCode('####??'),
                        houseNumber: fakerjs.location.buildingNumber(),
                        houseNumberSuffix: undefined,
                    },
                    dateOfBirth: fakerjs.date.past().toISOString(),
                },
            },
        };

        const wrapper = await createComponent(BsnLookupType.Task, {}, taskState);

        await wrapper.findByTestId('identify-button').trigger('click');

        // Should set the fields because otherwise the form will not submit:
        await wrapper.setData({
            flattenedFragments: {
                'personalDetails.bsnCensored': fakerjs.string.numeric(3),
                'personalDetails.dateOfBirth': fakerjs.date.past().toISOString(),
                'personalDetails.address.postalCode': fakerjs.location.zipCode('####??'),
                'personalDetails.address.houseNumber': fakerjs.string.numeric(3),
                'personalDetails.address.houseNumberSuffix': fakerjs.string.sample(3),
            },
        });

        await wrapper.findByTestId('lookup-button').trigger('click');
        await flushCallStack();

        expect(wrapper.findByTestId('form-error').attributes('text')).toEqual(
            'We hebben op basis van deze gegevens iemand gevonden, maar de laatste 3 cijfers van het BSN komen niet overeen. Kloppen de gegevens? Klik dan op ‘Toch doorgaan’.'
        );
    });

    it('should show error if more than one matching result found', async () => {
        const taskState: Partial<TaskStoreState> = {
            uuid: fakerjs.string.uuid(),
            fragments: {
                general: {
                    firstname: fakerjs.person.firstName(),
                    lastname: fakerjs.person.lastName(),
                },
                personalDetails: {
                    bsnCensored: undefined,
                    address: {
                        postalCode: fakerjs.location.zipCode('####??'),
                        houseNumber: fakerjs.string.numeric(3),
                        houseNumberSuffix: undefined,
                    },
                    dateOfBirth: fakerjs.date.past().toISOString(),
                },
            },
        };

        vi.spyOn(bsnApi, 'bsnLookup').mockImplementationOnce(() =>
            Promise.resolve({ error: BsnLookupError.TOO_MANY_RESULTS })
        );

        const wrapper = await createComponent(BsnLookupType.Task, {}, taskState);

        await wrapper.findByTestId('identify-button').trigger('click');

        // Should set the fields because otherwise the form will not submit:
        await wrapper.setData({
            flattenedFragments: {
                'personalDetails.bsnCensored': fakerjs.string.numeric(3),
                'personalDetails.dateOfBirth': fakerjs.date.past().toISOString(),
                'personalDetails.address.postalCode': fakerjs.location.zipCode('####??'),
                'personalDetails.address.houseNumber': fakerjs.string.numeric(3),
                'personalDetails.address.houseNumberSuffix': fakerjs.string.sample(3),
            },
        });

        await wrapper.findByTestId('lookup-button').trigger('click');
        await flushCallStack();

        expect(wrapper.findByTestId('form-error').attributes('text')).toEqual(
            'Er zijn meerdere personen met deze geboortedatum op dit adres gevonden. Daarom kan deze persoon niet geïdentificeerd worden. Kloppen de gegevens? Klik dan op ‘Toch doorgaan’.'
        );
    });

    it('should show error if service is unavailable', async () => {
        const taskState: Partial<TaskStoreState> = {
            uuid: fakerjs.string.uuid(),
            fragments: {
                general: {
                    firstname: fakerjs.person.firstName(),
                    lastname: fakerjs.person.lastName(),
                },
                personalDetails: {
                    bsnCensored: undefined,
                    address: {
                        postalCode: fakerjs.location.zipCode('####??'),
                        houseNumber: fakerjs.string.numeric(3),
                        houseNumberSuffix: undefined,
                    },
                    dateOfBirth: fakerjs.date.past().toISOString(),
                },
            },
        };

        vi.spyOn(bsnApi, 'bsnLookup').mockImplementationOnce(() =>
            Promise.resolve({ error: BsnLookupError.SERVICE_UNAVAILABLE })
        );

        const wrapper = await createComponent(BsnLookupType.Task, {}, taskState);

        await wrapper.findByTestId('identify-button').trigger('click');

        // Should set the fields because otherwise the form will not submit:
        await wrapper.setData({
            flattenedFragments: {
                'personalDetails.bsnCensored': fakerjs.string.numeric(3),
                'personalDetails.dateOfBirth': fakerjs.date.past().toISOString(),
                'personalDetails.address.postalCode': fakerjs.location.zipCode('####??'),
                'personalDetails.address.houseNumber': fakerjs.string.numeric(3),
                'personalDetails.address.houseNumberSuffix': fakerjs.string.sample(3),
            },
        });

        await wrapper.findByTestId('lookup-button').trigger('click');
        await flushCallStack();

        expect(wrapper.findByTestId('form-error').attributes('text')).toEqual(
            'Het portaal kan op dit moment niet de identiteit van deze persoon controleren. Kloppen de gegevens? Klik dan op ‘Toch doorgaan’.'
        );
    });

    it('should show error if no person is found', async () => {
        const taskState: Partial<TaskStoreState> = {
            uuid: fakerjs.string.uuid(),
            fragments: {
                general: {
                    firstname: fakerjs.person.firstName(),
                    lastname: fakerjs.person.lastName(),
                },
                personalDetails: {
                    bsnCensored: undefined,
                    address: {
                        postalCode: fakerjs.location.zipCode('####??'),
                        houseNumber: fakerjs.string.numeric(3),
                        houseNumberSuffix: undefined,
                    },
                    dateOfBirth: fakerjs.date.past().toISOString(),
                },
            },
        };

        vi.spyOn(bsnApi, 'bsnLookup').mockImplementationOnce(() =>
            Promise.resolve({ error: BsnLookupError.NOT_FOUND })
        );

        const wrapper = await createComponent(BsnLookupType.Task, {}, taskState);
        await wrapper.findByTestId('identify-button').trigger('click');
        // Should set the fields because otherwise the form will not submit:
        await wrapper.setData({
            flattenedFragments: {
                'personalDetails.bsnCensored': fakerjs.string.numeric(3),
                'personalDetails.dateOfBirth': fakerjs.date.past().toISOString(),
                'personalDetails.address.postalCode': fakerjs.location.zipCode('####??'),
                'personalDetails.address.houseNumber': fakerjs.string.numeric(3),
                'personalDetails.address.houseNumberSuffix': fakerjs.string.sample(3),
            },
        });

        await wrapper.findByTestId('lookup-button').trigger('click');
        await flushCallStack();

        expect(wrapper.findByTestId('form-error').attributes('text')).toEqual(
            'Geen persoon gevonden op basis van deze gegevens. Kloppen de gegevens? Klik dan op ‘Toch doorgaan’.'
        );
    });

    it('should show bsn error if server error with lastThreeDigits', async () => {
        const taskState: Partial<TaskStoreState> = {
            uuid: fakerjs.string.uuid(),
            fragments: {
                general: {
                    firstname: fakerjs.person.firstName(),
                    lastname: fakerjs.person.lastName(),
                },
                personalDetails: {
                    bsnCensored: undefined,
                    address: {
                        postalCode: fakerjs.location.zipCode('####??'),
                        houseNumber: fakerjs.string.numeric(3),
                        houseNumberSuffix: undefined,
                    },
                    dateOfBirth: fakerjs.date.past().toISOString(),
                },
            },
        };

        vi.spyOn(bsnApi, 'bsnLookup').mockImplementationOnce(() =>
            Promise.reject({ response: { data: { errors: { lastThreeDigits: 'some error' } } } })
        );

        const wrapper = await createComponent(BsnLookupType.Task, {}, taskState);
        await wrapper.findByTestId('identify-button').trigger('click');
        // Should set the fields because otherwise the form will not submit:
        await wrapper.setData({
            flattenedFragments: {
                'personalDetails.bsnCensored': fakerjs.string.numeric(3),
                'personalDetails.dateOfBirth': fakerjs.date.past().toISOString(),
                'personalDetails.address.postalCode': fakerjs.location.zipCode('####??'),
                'personalDetails.address.houseNumber': fakerjs.string.numeric(3),
                'personalDetails.address.houseNumberSuffix': fakerjs.string.sample(3),
            },
        });

        await wrapper.findByTestId('lookup-button').trigger('click');
        await flushCallStack();

        expect(wrapper.vm.formErrors['index.bsnCensored']).toBe('{"warning":"some error"}');
    });

    it('should set this.editing = true if this.personalDetailsEmpty is true', async () => {
        const taskState: Partial<TaskStoreState> = {
            uuid: fakerjs.string.uuid(),
            fragments: {
                general: {
                    firstname: fakerjs.person.firstName(),
                    lastname: fakerjs.person.lastName(),
                },
                personalDetails: {
                    bsnCensored: undefined,
                    address: {
                        postalCode: undefined,
                        houseNumber: undefined,
                        houseNumberSuffix: undefined,
                    },
                    dateOfBirth: undefined,
                },
            },
        };

        const wrapper = await createComponent(BsnLookupType.Task, {}, taskState);

        expect(wrapper.vm.personalDetailsEmpty).toBe(true);
        expect(wrapper.vm.editing).toBe(true);
    });

    it('should set this.editing = true if this.hasNoBsnOrAddress is an empty array', async () => {
        const taskState: Partial<TaskStoreState> = {
            uuid: fakerjs.string.uuid(),
            fragments: {
                general: {
                    firstname: fakerjs.person.firstName(),
                    lastname: fakerjs.person.lastName(),
                },
                personalDetails: {
                    hasNoBsnOrAddress: true,
                    bsnCensored: undefined,
                    address: {
                        postalCode: undefined,
                        houseNumber: undefined,
                        houseNumberSuffix: undefined,
                    },
                    dateOfBirth: undefined,
                },
            },
        };

        const wrapper = await createComponent(BsnLookupType.Task, {}, taskState);
        expect(wrapper.vm.editing).toBe(true);
    });

    it('should set this.editing = true if this.hasNoBsnOrAddress is true', async () => {
        const taskState: Partial<TaskStoreState> = {
            uuid: fakerjs.string.uuid(),
            fragments: {
                general: {
                    firstname: fakerjs.person.firstName(),
                    lastname: fakerjs.person.lastName(),
                },
                personalDetails: {
                    hasNoBsnOrAddress: [],
                    bsnCensored: undefined,
                    address: {
                        postalCode: undefined,
                        houseNumber: undefined,
                        houseNumberSuffix: undefined,
                    },
                    dateOfBirth: undefined,
                },
            },
        };

        const wrapper = await createComponent(BsnLookupType.Task, {}, taskState);
        expect(wrapper.vm.editing).toBe(true);
    });

    it('should call this.submit if this.onSubmit has been called', async () => {
        const taskState: Partial<TaskStoreState> = {
            uuid: fakerjs.string.uuid(),
            fragments: {
                general: {
                    firstname: fakerjs.person.firstName(),
                    lastname: fakerjs.person.lastName(),
                },
                personalDetails: {
                    bsnCensored: undefined,
                    address: {
                        postalCode: undefined,
                        houseNumber: undefined,
                        houseNumberSuffix: undefined,
                    },
                    dateOfBirth: undefined,
                },
            },
        };

        const wrapper = await createComponent(BsnLookupType.Task, {}, taskState);

        const submitSpy = vi.spyOn(wrapper.vm, 'submit');
        submitSpy.mockReset();

        const refreshSpy = vi.spyOn(wrapper.vm, 'refresh');
        refreshSpy.mockReset();

        await wrapper.vm.onSubmit();

        expect(submitSpy).toHaveBeenCalledTimes(1);
        expect(refreshSpy).toHaveBeenCalledTimes(1);
        expect(wrapper.vm.editing).toBe(false);
    });
});
