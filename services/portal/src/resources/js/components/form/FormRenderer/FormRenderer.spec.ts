import indexStore from '@/store/index/indexStore';
import taskStore from '@/store/task/taskStore';
import contextStore from '@/store/context/contextStore';

import Vuex from 'vuex';
import FormRenderer from './FormRenderer.vue';

import { SharedActions } from '@/store/actions';
import { StoreType } from '@/store/storeType';
import { TaskActions } from '@/store/task/taskActions';
import { userCanEdit } from '@/utils/interfaceState';
import { flushCallStack, setupTest } from '@/utils/test';
import { mount } from '@vue/test-utils';
import type { Mock } from 'vitest';
import { vi } from 'vitest';
import type { VueConstructor } from 'vue';

vi.mock('@/utils/interfaceState');

const schema = [
    {
        type: 'input',
        label: 'Test1',
        name: 'general.test1',
    },
    {
        type: 'input',
        label: 'Test2',
        name: 'general.test2',
    },
    {
        type: 'input',
        label: 'Test2',
        name: 'general.test3',
    },
];

const createComponent = setupTest(
    (localVue: VueConstructor, props?: object, data: object = {}, indexState: object = {}) => {
        return mount<FormRenderer>(FormRenderer, {
            localVue,
            data: () => data,
            propsData: props,
            store: new Vuex.Store({
                modules: {
                    index: {
                        ...indexStore,
                        state: {
                            ...(indexStore as any).state,
                            ...indexState,
                        },
                    },
                    context: contextStore,
                    task: taskStore,
                },
            }),
            stubs: {
                BSpinner: true,
                FormulateFormWrapper: true,
            },
        });
    }
);
describe('FormRenderer.vue', () => {
    beforeEach(() => {
        vi.spyOn(console, 'log').mockImplementation(() => {});
    });

    afterEach(() => {
        vi.resetAllMocks();
    });

    it('should show the spinner if loaded is false', () => {
        const props = { schema };
        const wrapper = createComponent(props);

        expect(wrapper.find('bspinner-stub').exists()).toBe(true);
    });

    it('should show FormulateFormWrappers if loaded is true', () => {
        const props = { schema };
        const data = { loaded: true };

        const wrapper = createComponent(props, data);

        // Same number of elements as elements in the schema
        expect(wrapper.findAll('formulateformwrapper-stub').length).toBe(schema.length);
    });

    it('should hide the spinner after loading the component', async () => {
        const props = { schema };
        const wrapper = createComponent(props);

        expect(wrapper.find('bspinner-stub').exists()).toBe(true);

        // Component sets this.loaded to true after the component has been loaded to ensure responsitivity
        // It takes a while to process the VueFormulate schemma
        await flushCallStack();

        expect(wrapper.find('bspinner-stub').exists()).toBe(false);
    });

    it('should show the debug panel if showDebug is true', () => {
        const props = { schema, showDebug: true };
        const data = { loaded: true };

        const wrapper = createComponent(props, data);

        expect(wrapper.find('.debug').exists()).toBe(true);
    });

    it('should provide the method submitForm', () => {
        const props = { schema };
        const data = { loaded: true };
        const wrapper = createComponent(props, data);

        expect(wrapper.vm._provided.submitForm).toEqual(expect.any(Function));
        expect(wrapper.vm._provided.submitForm).toEqual(wrapper.vm.submit);
    });

    it('should process rules and submit after a change', () => {
        const rules = [
            {
                title: 'Test',
                watch: 'general.test',
                callback: (data: any, [generalTest]: any, oldVals: any) => {
                    const changes: any = {};

                    if (generalTest === 'newvalue') {
                        changes['general.otherField'] = 'change';
                    }

                    return changes;
                },
            },
        ];

        const props = { schema, rules };

        const data = {
            loaded: true,
        };

        const state = {
            uuid: '00001',
            fragments: {
                general: {
                    test: 'oldvalue',
                },
            },
        };
        const wrapper = createComponent(props, data, state);

        // Apply localChanges after initializing the component as if this field has been changed
        wrapper.vm.localChanges = {
            'general.test': 'newvalue',
        };

        // Trigger the change event, this will execute the submit method
        wrapper.findAll('formulateformwrapper-stub').at(0).vm.$emit('change', { stopPropagation: vi.fn() });

        // general.test has changed from 'oldvalue' to 'newvalue'
        // our defined rule should act upon this
        expect(wrapper.vm.$store.getters[`index/fragments`]).toEqual({
            general: {
                test: 'newvalue',
                otherField: 'change',
            },
        });
    });

    it('should execute method repeatableRemoved on repeatableRemoved event of FormulateFormWrapper', () => {
        const props = { schema };

        const data = {
            loaded: true,
        };

        const state = {
            uuid: '00001',
            fragments: {
                general: {
                    test: ['a', 'b', 'c'],
                },
            },
        };

        const wrapper = createComponent(props, data, state);

        // Trigger a repeatableRemoved event
        wrapper
            .findAll('formulateformwrapper-stub')
            .at(0)
            .vm.$emit('repeatableRemoved', {
                stopPropagation: vi.fn(),
                name: 'general.test',
                values: ['a', 'c'],
            });

        expect(wrapper.vm.$store.getters[`index/fragments`]).toEqual({
            general: {
                test: ['a', 'c'],
            },
        });
    });

    it('should execute event.stopPropagation() if event has stopPropagation function', () => {
        vi.spyOn(console, 'error').mockImplementation(() => {});
        const props = { schema };

        (userCanEdit as Mock).mockImplementationOnce(() => true);

        const wrapper = createComponent(props, {}, {});

        const stopPropagation = vi.fn();
        const event = {
            stopPropagation,
        };

        const stopPropagationEvt = vi.spyOn(event, 'stopPropagation');

        wrapper.vm.submit(event);
        expect(stopPropagationEvt).toBeCalled();
    });

    it('function should not fail if event object doesnt have stopPropagation function', () => {
        vi.spyOn(console, 'error').mockImplementation(() => {});
        const props = { schema };

        (userCanEdit as Mock).mockImplementationOnce(() => true);

        const wrapper = createComponent(props, {}, {});

        const event = {};

        wrapper.vm.submit(event);
    });

    it('should update localChanges when fragments change', async () => {
        const props = { schema };
        const indexStore = {
            fragments: {
                test: {
                    test: 'test',
                },
            },
        };

        const oldFragments = {
            'test.test': 'test',
        };
        const newFragments = {
            'test.test': 'test 2',
        };

        const wrapper = createComponent(props, {}, indexStore);
        expect(wrapper.vm.localChanges).toEqual(oldFragments);

        // Call directly to trigger the computed setter
        wrapper.vm.fragments = newFragments;
        await flushCallStack();

        expect(wrapper.vm.localChanges).toEqual(newFragments);
    });

    it('should not dispatch if there are no localChanges', async () => {
        const props = { schema };

        const wrapper = createComponent(props, {}, {});
        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');

        await wrapper.setData({ localChanges: undefined });

        wrapper.vm.submit();
        expect(spyOnDispatch).not.toHaveBeenCalled();
    });

    it.each([
        {
            storeType: StoreType.TASK,
            expectedDispatch: `${StoreType.TASK}/${TaskActions.UPDATE_TASK_FRAGMENT}`,
        },
        {
            storeType: StoreType.INDEX,
            expectedDispatch: `${StoreType.INDEX}/${SharedActions.UPDATE_FORM_VALUE}`,
        },
        {
            storeType: StoreType.CONTEXT,
            expectedDispatch: `${StoreType.CONTEXT}/${SharedActions.UPDATE_FORM_VALUE}`,
        },
    ])(
        `should dispatch '$expectedDispatch' on submit if prop storeType=$storeType`,
        ({ expectedDispatch, storeType }) => {
            vi.spyOn(console, 'error').mockImplementation(() => {});
            const props = { schema, storeType };
            const data = { localChanges: { 'fragment.test': { test: 1 } } };

            const wrapper = createComponent(props, data, {});
            const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');

            wrapper.vm.submit();
            expect(spyOnDispatch).toHaveBeenCalledWith(expectedDispatch, data.localChanges);
        }
    );
});
