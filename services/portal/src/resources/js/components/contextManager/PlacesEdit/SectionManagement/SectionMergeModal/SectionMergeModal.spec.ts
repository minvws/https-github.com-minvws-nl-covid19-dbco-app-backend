import { setupTest } from '@/utils/test';
import type { Section } from '@dbco/portal-api/section.dto';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import SectionMergeModal from './SectionMergeModal.vue';

const sections: Section[] = [
    { label: 'Entree', uuid: '8c22fe3c-1063-452b-b18b-5ce96418be0b', indexCount: 1 },
    { label: 'Toilet', uuid: '7441dab3-b21d-407b-86ac-b6ba7e16ee29', indexCount: 1 },
];

const createComponent = setupTest((localVue: VueConstructor) => {
    return shallowMount(SectionMergeModal, {
        localVue,
        propsData: {
            sections,
        },
    });
});

describe('SectionMergeModal.vue', () => {
    it('should be visible', () => {
        const wrapper = createComponent();

        expect(wrapper.findComponent({ name: 'BModal' }).exists()).toBe(true);
    });

    it('should show sections as options', () => {
        const wrapper = createComponent();

        expect(wrapper.findComponent({ name: 'BFormRadioGroup' }).exists()).toBe(true);
        expect(wrapper.findComponent({ name: 'BFormRadioGroup' }).props('options')).toEqual(sections);
    });

    it('should have first option selected by default', () => {
        const wrapper = createComponent();

        expect(wrapper.findComponent({ name: 'BFormRadioGroup' }).exists()).toBe(true);
        expect(wrapper.findComponent({ name: 'BFormRadioGroup' }).props('checked')).toEqual(sections[0].uuid);
    });

    it('should compute currently selected section to "mainSection"', () => {
        const wrapper = createComponent();

        expect(wrapper.vm.mainSection).toEqual(sections[0]);
    });

    it('should compute unselected sections to "mergeSections"', () => {
        const wrapper = createComponent();

        expect(wrapper.vm.mergeSections).toEqual([sections[1]]);
    });

    it('should emit "on-hide" on root when hide() method is fired', async () => {
        const wrapper = createComponent();
        await wrapper.vm.hide();

        expect(wrapper.emitted('on-hide')).toBeTruthy();
    });

    it('should emit "on-merge" on root and afterwards the hide() method when merge() method is fired', async () => {
        const wrapper = createComponent();
        const spyHide = vi.spyOn(wrapper.vm as any, 'hide').mockImplementationOnce(() => {});
        await wrapper.vm.merge();

        expect(wrapper.emitted('on-merge')).toBeTruthy();
        expect(spyHide).toHaveBeenCalledTimes(1);
    });
});
