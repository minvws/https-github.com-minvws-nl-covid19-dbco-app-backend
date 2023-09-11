import { shallowMount } from '@vue/test-utils';
import FormDatePicker from './FormDatePicker.vue';
import { setupTest } from '@/utils/test';
import type { CreateElement, VueConstructor } from 'vue';
import Vuex from 'vuex';
import { CalendarViewV1 } from '@dbco/enum';
import indexStore from '@/store/index/indexStore';
import { createTestingPinia } from '@pinia/testing';

let datePickerAttrs: AnyObject;

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    const indexStoreModule = {
        ...indexStore,
        state: {
            ...indexStore.state,
        },
    };

    return shallowMount(FormDatePicker, {
        localVue,
        propsData: {
            context: {
                model: '2021-06-01',
            },
            ...props,
        },
        pinia: createTestingPinia(),
        store: new Vuex.Store({
            modules: {
                index: indexStoreModule,
            },
        }),
        stubs: {
            DatePicker: {
                render(h: CreateElement) {
                    // @ts-ignore
                    datePickerAttrs = this.$attrs;

                    return h('div');
                },
            },
        },
    });
});

describe('FormDatePicker.vue', () => {
    it('should load the component', () => {
        // ARRANGE
        const props = {
            context: {
                model: '2021-06-01',
            },
            calendarView: CalendarViewV1.VALUE_index_context_table,
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.findComponent({ name: 'DatePicker' }).exists()).toBe(true);
        expect(datePickerAttrs.editable).toBe(true);
    });

    it('should emit "change" event when receiving "input" event from DatePicker', () => {
        // ARRANGE
        const props = {
            context: {
                model: '2021-06-01',
            },
            calendarView: CalendarViewV1.VALUE_index_context_table,
        };

        const wrapper = createComponent(props);
        wrapper.findComponent({ name: 'DatePicker' }).vm.$emit('input', 'abc');

        // ASSERT
        expect(wrapper.emitted().change).toHaveLength(1);
        expect(wrapper.emitted().change?.[0]).toStrictEqual(['abc']);
    });
});
