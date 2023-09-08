import { shallowMount } from '@vue/test-utils';
import { faker } from '@faker-js/faker';
import Backdrop from './Backdrop.vue';
import { createDefaultLocalVue } from '../../test/local-vue';

const $emit = vi.fn();

const createComponent = (propsData: { isOpen: boolean }, content: string) => {
    return shallowMount(Backdrop, {
        localVue: createDefaultLocalVue(),
        propsData,
        slots: {
            default: content,
        },
        mocks: {
            $emit,
        },
    });
};

describe('Backdrop.vue', () => {
    it('should render as default closed', () => {
        const content = faker.lorem.word();
        const wrapper = createComponent({ isOpen: false }, content);
        const portal = wrapper.find('portal-stub');
        expect(portal.exists()).toBe(true);
        expect(portal.text()).toBe('');
    });

    it('should render with content', () => {
        const content = faker.lorem.word();
        const wrapper = createComponent({ isOpen: true }, content);
        const portal = wrapper.find('portal-stub');
        const divs = portal.findAll('div');
        expect(divs.at(3).text()).toBe(content);
    });

    it('should render with correct styling and markup', () => {
        const content = faker.lorem.word();
        const wrapper = createComponent({ isOpen: true }, content);
        const portal = wrapper.find('portal-stub');
        const divs = portal.findAll('div');
        expect(divs.at(0).classes()).include('tw-fixed', 'tw-overflow-y-auto');
        expect(divs.at(1).classes()).include('tw-relative', 'tw-justify-center');
        expect(divs.at(2).classes()).include('tw-absolute', 'tw-z-40');
        expect(divs.at(2).attributes('role')).toBe('dialog');
        expect(divs.at(3).classes()).include('tw-relative', 'tw-z-50');
    });

    it('should emit "close" when the Escape key is pressed', async () => {
        const content = faker.lorem.word();
        createComponent({ isOpen: true }, content);
        await window.dispatchEvent(new KeyboardEvent('keyup', { key: 'Escape' }));
        expect($emit).toHaveBeenCalledWith('close');
    });

    it('should remove keyup event listener when unmounting', async () => {
        window.removeEventListener = vi.fn().mockImplementationOnce(vi.fn());
        const content = faker.lorem.word();
        const wrapper = createComponent({ isOpen: true }, content);
        await wrapper.destroy();
        expect(window.removeEventListener).toBeCalledWith('keyup', expect.any(Function));
    });
});
