import { shallowMount } from '@vue/test-utils';
import ContextPlace from './ContextPlace.vue';
import Vuex from 'vuex';

import contextStore from '@/store/context/contextStore';
import { contextApi } from '@dbco/portal-api';
import { flushCallStack, setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';
import type { MockedFunction } from 'vitest';
import { vi } from 'vitest';

vi.mock('@dbco/portal-api/client/context.api', () => ({
    getFragments: vi.fn(() =>
        Promise.resolve({
            data: {},
        })
    ),
    getSections: vi.fn(() => Promise.resolve({ sections: [] })),
    unlinkPlace: vi.fn(() => Promise.resolve()),
}));

const createComponent = setupTest(
    (localVue: VueConstructor, data: object = {}, context: object = {}, propsData: object = {}) => {
        context = {
            ...contextStore,
            state: {
                ...(contextStore as any).state,
                ...context,
            },
        };

        return shallowMount(ContextPlace, {
            localVue,
            propsData,
            data: () => data,
            store: new Vuex.Store({
                modules: {
                    context,
                },
            }),
        });
    }
);

describe('ContextPlace.vue', () => {
    it('should render no-place note and button when no place has been set', () => {
        const wrapper = createComponent(undefined, {
            place: null,
        });

        expect(wrapper.find('.no-place').exists()).toBe(true);
        expect(wrapper.find('.selected-place').exists()).toBe(false);

        expect(wrapper.find('.note').text()).toEqual('Er is nog geen locatie gekoppeld aan deze context.');
        expect(wrapper.findComponent({ name: 'BButton' }).text()).toEqual('Context koppelen');
    });

    it('should show place component if place is set', () => {
        const wrapper = createComponent(undefined, {
            place: {
                uuid: '5678',
            },
        });

        expect(wrapper.find('.no-place').exists()).toBe(false);
        expect(wrapper.find('.selected-place').exists()).toBe(true);

        expect(wrapper.findComponent({ name: 'Place' }).exists()).toBe(true);
        expect(wrapper.find('[data-testid="sections"]').exists()).toBe(false);
    });

    it('should show sections of a place if place is set and sections are available', async () => {
        (contextApi.getSections as MockedFunction<typeof contextApi.getSections>).mockReturnValueOnce(
            Promise.resolve({ sections: [{ uuid: '0000' }, { uuid: '1111' }, { uuid: '2222' }] })
        );

        const wrapper = createComponent(undefined, {
            place: {
                uuid: '5678',
            },
        });
        await flushCallStack();

        expect(wrapper.find('[data-testid="sections"]').exists()).toBe(true);
        expect(wrapper.findAll('[data-testid="sections"] li').length).toBe(3);
    });

    it('should not show the PlaceSelectmodal by default', () => {
        const wrapper = createComponent(undefined, {
            place: null,
        });

        expect(wrapper.findComponent({ name: 'PlaceSelectModal' }).exists()).toBe(false);
    });

    it('should show the PlaceSelectmodal after clicking the link button', async () => {
        const wrapper = createComponent(undefined, {
            place: null,
        });

        await wrapper.findComponent({ name: 'BButton' }).trigger('click');

        expect(wrapper.findComponent({ name: 'PlaceSelectModal' }).exists()).toBe(true);
    });

    it('should reset+unlink place and reload fragments after Place emits the "cancel" event', async () => {
        const wrapper = createComponent(undefined, {
            uuid: '1234',
            place: {
                uuid: '5678',
            },
        });

        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');
        const spyOnUnlinkPLace = vi.spyOn(contextApi, 'unlinkPlace');

        expect(wrapper.find('.no-place').exists()).toBe(false);
        await wrapper.findComponent({ name: 'Place' }).vm.$emit('cancel');
        await flushCallStack();
        expect(wrapper.find('.no-place').exists()).toBe(true);
        expect(spyOnUnlinkPLace).toHaveBeenCalledWith('1234', '5678');
        expect(spyOnDispatch).toHaveBeenCalledWith('context/LOAD', '1234');
    });

    it('should show the PlaceSelectmodal after Place emits the "edit" event', async () => {
        const wrapper = createComponent(undefined, {
            place: {
                uuid: '5678',
            },
        });

        await wrapper.findComponent({ name: 'Place' }).vm.$emit('edit');

        expect(wrapper.findComponent({ name: 'PlaceSelectModal' }).exists()).toBe(true);
    });

    it('should reload sections and fragments after PlaceSelectModal emits the "hide" event', async () => {
        const wrapper = createComponent(
            {
                modalVisible: true,
            },
            {
                uuid: '1234',
                place: {
                    uuid: '5678',
                },
            }
        );

        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');
        const spyOnGetSections = vi.spyOn(contextApi, 'getSections');
        spyOnGetSections.mockClear();

        await wrapper.findComponent({ name: 'PlaceSelectModal' }).vm.$emit('hide');
        await flushCallStack();

        expect(wrapper.findComponent({ name: 'PlaceSelectModal' }).exists()).toBe(false);
        expect(spyOnDispatch).toHaveBeenCalledWith('context/LOAD', '1234');
        expect(spyOnGetSections).toHaveBeenCalledWith('1234');
    });

    it('if disabled prop is not set and this.place is not set, select-place button should be visible not be disabled', () => {
        const wrapper = createComponent(
            {
                modalVisible: true,
            },
            {
                uuid: '1234',
                place: undefined,
            },
            {}
        );

        const field = wrapper.find(`[data-testid="select-place"]`);
        expect(field.attributes().disabled).toBe(undefined);
    });

    it('if disabled prop === true and this.place is not set, select-place button should be visible be disabled', () => {
        const wrapper = createComponent(
            {
                modalVisible: true,
            },
            {
                uuid: '1234',
                place: undefined,
            },
            {
                disabled: true,
            }
        );

        const field = wrapper.find(`[data-testid="select-place"]`);
        expect(field.attributes().disabled).toBe('true');
    });

    it('if disabled prop is not set and this.place is not set, Place component should be visible not be disabled', () => {
        const wrapper = createComponent(
            {
                modalVisible: true,
            },
            {
                uuid: '1234',
                place: {
                    uuid: '487EFC07-96DB-4B54-A8B9-6834B18BD858',
                },
            },
            {}
        );

        const field = wrapper.find('[data-testid="place"]');
        expect(field.attributes().disabled).toBe(undefined);
    });

    it('if disabled prop === true and this.place is set, Place component should be visible be disabled', () => {
        const wrapper = createComponent(
            {
                modalVisible: true,
            },
            {
                uuid: '1234',
                place: {
                    uuid: '487EFC07-96DB-4B54-A8B9-6834B18BD858',
                },
            },
            {
                disabled: true,
            }
        );

        const field = wrapper.find('[data-testid="place"]');
        expect(field.attributes().disabled).toBe('true');
    });
});
