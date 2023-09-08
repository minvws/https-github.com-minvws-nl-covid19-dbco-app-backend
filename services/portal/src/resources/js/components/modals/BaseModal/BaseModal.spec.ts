import { shallowMount } from '@vue/test-utils';
// @ts-ignore
import BaseModal, { defaultParams } from '@/components/modals/BaseModal/BaseModal.vue';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

vi.mock('@/components/plugins/modal.ts');

const defaultData = {
    modalConfig: defaultParams,
    textAreaInput: '',
    text: undefined,
    onCancel: () => {},
    onConfirm: () => {},
};

const createComponent = setupTest((localVue: VueConstructor, data?: object) => {
    return shallowMount(BaseModal, {
        localVue,
        data() {
            return data ?? defaultData;
        },
    });
});

describe('BaseModal.vue', () => {
    it('should default with default properties', () => {
        const wrapper = createComponent();

        expect(wrapper.vm.modalConfig).toMatchObject(defaultParams);
        expect(wrapper.vm.text).toBeUndefined();
        expect(wrapper.vm.onCancel).toBeInstanceOf(Function);
        expect(wrapper.vm.onConfirm).toBeInstanceOf(Function);
    });

    it('should display text passed through "text" data property', () => {
        const data = { ...defaultData, text: 'Test' };
        const wrapper = createComponent(data);
        expect(wrapper.text()).toBe('Test');
    });
});
