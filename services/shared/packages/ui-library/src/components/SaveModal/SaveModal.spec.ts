import { mount } from '@vue/test-utils';
import { faker } from '@faker-js/faker';
import SaveModal from './SaveModal.vue';
import Button from '../Button/Button.vue';

import { createDefaultLocalVue } from '../../test/local-vue';

type Props = {
    isOpen: boolean;
    title: string;
    cancelLabel?: string;
    loading?: boolean;
    okLabel?: string;
};

const createComponent = (propsData: Props, content: string) => {
    return mount(SaveModal, {
        localVue: createDefaultLocalVue(),
        propsData,
        slots: {
            default: content,
        },
        stubs: { Backdrop: true },
    });
};

describe('SaveModal.vue', () => {
    it('should emit "close" when the cancel button in the footer is clicked', () => {
        const content = faker.lorem.word();
        const wrapper = createComponent({ isOpen: true, title: 'Title' }, content);
        const emitSpy = vi.spyOn(wrapper.vm, '$emit');
        const cancelButton = wrapper.findAllComponents(Button).at(1);
        cancelButton.vm.$emit('click');
        expect(emitSpy).toHaveBeenCalledWith('close');
    });

    it('should emit "ok" when the ok button in the footer is clicked', () => {
        const content = faker.lorem.word();
        const wrapper = createComponent({ isOpen: true, title: 'Title' }, content);
        const emitSpy = vi.spyOn(wrapper.vm, '$emit');
        const okButton = wrapper.findAllComponents(Button).at(2);
        okButton.vm.$emit('click');
        expect(emitSpy).toHaveBeenCalledWith('ok');
    });

    it('should disable the footer buttons when loading', () => {
        const content = faker.lorem.word();
        const wrapper = createComponent({ isOpen: true, title: 'Title', loading: true }, content);
        const buttons = wrapper.findAllComponents(Button);
        expect(buttons.at(1).attributes('disabled')).toBe('disabled');
        expect(buttons.at(2).attributes('disabled')).toBe('disabled');
    });
});
