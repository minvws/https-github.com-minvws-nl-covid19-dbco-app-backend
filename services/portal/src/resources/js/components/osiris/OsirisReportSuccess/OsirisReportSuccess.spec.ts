import { shallowMount } from '@vue/test-utils';
import OsirisReportSuccess from './OsirisReportSuccess.vue';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const createComponent = setupTest((localVue: VueConstructor) => {
    return shallowMount(OsirisReportSuccess, {
        localVue,
    });
});

describe('OsirisReportSuccess.vue', () => {
    it('should load the component', () => {
        const wrapper = createComponent();

        expect(wrapper.exists()).toBe(true);
    });

    it('should emit "loaded" on creation', () => {
        const wrapper = createComponent();

        expect(wrapper.emitted().loaded).toBeTruthy();
    });

    it('should emit "confirm" on ok', () => {
        const wrapper = createComponent();

        wrapper.findComponent({ name: 'BModal' }).vm.$emit('ok');

        expect(wrapper.emitted().confirm).toBeTruthy();
    });

    it('should emit "cancel" on hide w/trigger not equal to "ok"', () => {
        const wrapper = createComponent();

        wrapper.findComponent({ name: 'BModal' }).vm.$emit('hide', { trigger: 'hide' });

        expect(wrapper.emitted().cancel).toBeTruthy();
    });

    it('should not emit "cancel" on hide w/trigger "ok"', () => {
        const wrapper = createComponent();

        wrapper.findComponent({ name: 'BModal' }).vm.$emit('hide', { trigger: 'ok' });

        expect(wrapper.emitted().cancel).toBeFalsy();
    });
});
