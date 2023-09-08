import type { IndexStoreState } from '@/store/index/indexStore';
import indexStoreModule from '@/store/index/indexStore';
import { fakerjs, setupTest } from '@/utils/test';
import { mount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import { Store } from 'vuex';
import type { FormCondition } from '../ts/formTypes';
import FormSlot from './FormSlot.vue';
import { StoreType } from '@/store/storeType';
const createComponent = setupTest(
    (
        localVue: VueConstructor,
        propsData?: object,
        indexStoreState?: Partial<IndexStoreState>,
        rootModel?: Record<any, any>
    ) => {
        return mount(FormSlot, {
            localVue,
            propsData,
            store: new Store({
                modules: {
                    [StoreType.INDEX]: {
                        ...indexStoreModule,
                        state: {
                            ...indexStoreModule.state,
                            ...indexStoreState,
                        },
                    },
                },
            }),
            slots: {
                default: 'Conditional field',
            },
            provide: {
                rootModel: () => rootModel,
            },
        });
    }
);
describe('FormSlot.vue', () => {
    describe('store conditions', () => {
        it('should render conditional fields when conditions are met', () => {
            const uuid = fakerjs.string.uuid();
            const state: Partial<IndexStoreState> = {
                fragments: {
                    uuid,
                },
            };
            const conditions: FormCondition[] = [{ prop: 'uuid', values: [uuid] }];
            const wrapper = createComponent({ conditions, context: { type: 'slot' } }, state);

            expect(wrapper.html()).toBeTruthy();
        });

        it('should render nothing when no conditions are met', () => {
            const uuid = fakerjs.string.uuid();
            const state: Partial<IndexStoreState> = {
                fragments: {
                    uuid,
                },
            };
            const conditions: FormCondition[] = [{ prop: 'uuid', values: [fakerjs.string.uuid()] }];
            const wrapper = createComponent({ conditions, context: { type: 'slot' } }, state);

            expect(wrapper.html()).toBeFalsy();
        });
    });
    describe('form model conditions (no store)', () => {
        it('should render conditional fields when conditions are met', () => {
            const uuid = fakerjs.string.uuid();
            const conditions: FormCondition[] = [{ field: 'uuid', values: [uuid] }];
            const wrapper = createComponent({ conditions, context: { type: 'slot' } }, undefined, { uuid });

            expect(wrapper.html()).toBeTruthy();
        });

        it('should render nothing when no conditions are met', () => {
            const uuid = fakerjs.string.uuid();

            const conditions: FormCondition[] = [{ field: 'uuid', values: [fakerjs.string.uuid()] }];
            const wrapper = createComponent({ conditions, context: { type: 'slot' } }, undefined, { uuid });

            expect(wrapper.html()).toBeFalsy();
        });
    });
    describe('form condition operator', () => {
        it('should show when every condition is met', () => {
            const uuid = fakerjs.string.uuid();
            const uuid2 = fakerjs.string.uuid();
            const conditions: FormCondition[] = [
                { field: 'uuid', values: [uuid] },
                { field: 'uuid2', values: [uuid2] },
            ];

            const wrapper = createComponent(
                {
                    conditions,
                    context: { type: 'slot' },
                    conditionOperator: 'AND',
                },
                undefined,
                { uuid, uuid2 }
            );

            expect(wrapper.html()).toBeTruthy();
        });

        it('should not show when some condition is met', () => {
            const uuid = fakerjs.string.uuid();
            const uuid2 = fakerjs.string.uuid();
            const conditions: FormCondition[] = [
                { field: 'uuid', values: [uuid] },
                { field: 'uuid2', values: [uuid2] },
            ];

            const wrapper = createComponent(
                {
                    conditions,
                    context: { type: 'slot' },
                    conditionOperator: 'AND',
                },
                undefined,
                { uuid, uuid2: 'notUuid2' }
            );

            expect(wrapper.html()).toBeFalsy();
        });

        it('should show when some condition is met, but operator is OR', () => {
            const uuid = fakerjs.string.uuid();
            const uuid2 = fakerjs.string.uuid();
            const conditions: FormCondition[] = [
                { field: 'uuid', values: [uuid] },
                { field: 'uuid2', values: [uuid2] },
            ];

            const wrapper = createComponent(
                {
                    conditions,
                    context: { type: 'slot' },
                    conditionOperator: 'OR',
                },
                undefined,
                { uuid, uuid2: 'notUuid2' }
            );

            expect(wrapper.html()).toBeTruthy();
        });
    });
});
