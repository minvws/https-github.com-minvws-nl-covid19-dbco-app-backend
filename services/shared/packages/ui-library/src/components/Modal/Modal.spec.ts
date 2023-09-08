import { mount } from '@vue/test-utils';
import { faker } from '@faker-js/faker';
import Modal from './Modal.vue';
import IconButton from '../IconButton/IconButton.vue';
import { createDefaultLocalVue } from '../../test/local-vue';

const createComponent = (propsData: { isOpen: boolean }, content: string) => {
    return mount(Modal, {
        localVue: createDefaultLocalVue(),
        propsData,
        slots: {
            header: 'Title',
            default: content,
            footer: 'Footer',
        },
        stubs: { Backdrop: true },
    });
};

describe('Modal.vue', () => {
    it('should render as default closed', () => {
        const content = faker.lorem.word();
        const wrapper = createComponent({ isOpen: false }, content);
        const backdrop = wrapper.find('backdrop-stub');
        expect(backdrop.exists()).toBe(true);
        expect(backdrop.attributes('isopen')).toBe(undefined);
    });

    it('should render as open when the isOpen prop is set to true', () => {
        const content = faker.lorem.word();
        const wrapper = createComponent({ isOpen: true }, content);
        const backdrop = wrapper.find('backdrop-stub');
        expect(backdrop.attributes('isopen')).toBe('true');
    });

    it('should emit "close" when the close button in the header is clicked', async () => {
        const content = faker.lorem.word();
        const wrapper = createComponent({ isOpen: true }, content);
        const emitSpy = vi.spyOn(wrapper.vm, '$emit');
        const closeButton = wrapper.findComponent(IconButton);
        await closeButton.vm.$emit('click');
        expect(emitSpy).toHaveBeenCalledWith('close');
    });
});
