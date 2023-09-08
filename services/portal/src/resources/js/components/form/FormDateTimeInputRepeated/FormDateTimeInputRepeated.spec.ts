import BootstrapVue from 'bootstrap-vue';
import Vuex from 'vuex';
// @ts-ignore
import VueFormulate from '@braid/vue-formulate';

import type { Wrapper } from '@vue/test-utils';
import { createLocalVue, mount } from '@vue/test-utils';

import FormDateTimeInputRepeated from './FormDateTimeInputRepeated.vue';
import FormDatePicker from '@/components/form/FormDatePicker/FormDatePicker.vue';
import indexStore from '@/store/index/indexStore';
import { createTestingPinia } from '@pinia/testing';
import { CalendarViewV1 } from '@dbco/enum';

describe('FormDateTimeInputRepeated.vue', () => {
    const localVue = createLocalVue();
    let wrapper: Wrapper<Vue>;
    localVue.use(BootstrapVue);
    localVue.use(Vuex);
    localVue.use(VueFormulate, {
        library: {
            formDatePicker: {
                classification: 'text',
                component: FormDatePicker,
                slotProps: {
                    component: ['calendarView', 'disabled', 'rangeCutOff', 'ranges', 'singleSelection'],
                },
            },
        },
    });

    const getWrapper = (props?: object, indexStoreState: object = {}) => {
        const indexModule = {
            ...indexStore,
            state: {
                ...(indexStore as any).state,
                ...indexStoreState,
            },
        };

        return mount(FormDateTimeInputRepeated, {
            localVue,
            stubs: {
                DatePicker: true,
            },
            propsData: props,
            store: new Vuex.Store({
                modules: {
                    index: indexModule,
                },
            }),
            pinia: createTestingPinia(),
        });
    };

    const inputDisableFields = [
        'input-start-time',
        'input-end-time',
        'input-repeatable-remove-button',
        'add-more-button',
    ];

    it('inputfields in inputDisabledFields[] should be disabled when disabled prop is set to true', async () => {
        // ARRANGE
        const props = {
            caseUuid: '8aa7348e-b7e0-4758-be90-3153ff75502a',
            context: {
                minimum: 1,
                model: [
                    {
                        schemaVersion: 1,
                        day: '2022-02-17',
                        startTime: '10:00',
                        endTime: '11:00',
                        source: null,
                        formatted: null,
                    },
                    {
                        schemaVersion: 1,
                        day: '2022-02-18',
                        startTime: '09:00',
                        endTime: '12:00',
                        source: null,
                        formatted: null,
                    },
                    {
                        schemaVersion: 1,
                        day: '2022-02-19',
                        startTime: '09:00',
                        endTime: '10:00',
                        source: null,
                        formatted: null,
                    },
                ],
                classes: {
                    groupRepeatableRemove: '',
                },
                name: 'thisisaname',
                attributes: {
                    calendarView: CalendarViewV1.VALUE_index_context_table,
                    rangeCutOff: new Date(),
                },
            },
            schema: {},
            childrenSchema: [],
            disabled: true,
        };

        wrapper = await getWrapper(props, {
            meta: {
                episodeStartDate: '2022-02-18',
            },
        });

        // DatePicker disables the component by settings the disabled attribute to "true" instead of "disabled"
        expect(wrapper.find(`datepicker-stub`).attributes().disabled).toBe('true');

        inputDisableFields.forEach((testId) => {
            expect(wrapper.find(`[data-testid='${testId}']`).attributes().disabled).toBe('disabled');
        });
    });

    it('inputfields in inputDisabledFields[] should not be disabled when disabled prop is set to false', async () => {
        // ARRANGE
        const props = {
            caseUuid: '8aa7348e-b7e0-4758-be90-3153ff75502a',
            context: {
                minimum: 1,
                model: [
                    {
                        schemaVersion: 1,
                        day: '2022-02-17',
                        startTime: '10:00',
                        endTime: '11:00',
                        source: null,
                        formatted: null,
                    },
                    {
                        schemaVersion: 1,
                        day: '2022-02-18',
                        startTime: '09:00',
                        endTime: '12:00',
                        source: null,
                        formatted: null,
                    },
                    {
                        schemaVersion: 1,
                        day: '2022-02-19',
                        startTime: '09:00',
                        endTime: '10:00',
                        source: null,
                        formatted: null,
                    },
                ],
                classes: {
                    groupRepeatableRemove: '',
                },
                name: 'thisisaname',
                attributes: {
                    calendarView: CalendarViewV1.VALUE_index_context_table,
                    rangeCutOff: new Date(),
                },
            },
            schema: {},
            childrenSchema: [],
            disabled: false,
        };

        wrapper = await getWrapper(props, {
            meta: {
                episodeStartDate: '2022-02-18',
            },
        });

        expect(wrapper.find(`datepicker-stub`).attributes().disabled).toBe(undefined);

        inputDisableFields.forEach((testId) => {
            expect(wrapper.find(`[data-testid='${testId}']`).attributes().disabled).toBe(undefined);
        });
    });
});
