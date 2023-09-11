import { fakerjs, setupTest } from '@/utils/test';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import DbcoHeader from './DbcoHeader.vue';

const createComponent = setupTest(
    (
        localVue: VueConstructor,
        propsData: {
            environment: string;
            section: string;
        }
    ) =>
        shallowMount(DbcoHeader, {
            localVue,
            propsData,
            attachTo: document.body,
        })
);

describe('DbcoHeader.vue', () => {
    const mockHeight = vi.fn(() => 100);

    beforeEach(() => {
        Object.defineProperty(Element.prototype, 'clientHeight', {
            configurable: true,
            get: mockHeight,
            set: vi.fn(),
        });
    });

    it('should render as fixed by default', () => {
        const wrapper = createComponent({
            environment: fakerjs.lorem.word(),
            section: 'test',
        });

        expect(wrapper.findByTestId('header-content').isVisible()).toBe(true);
        expect(wrapper.findByTestId('header-content').classes()).toContain('tw-fixed');
        expect(wrapper.findByTestId('header-placeholder').isVisible()).toBe(true);
    });

    it('should render not be fixed on section "editcase"', () => {
        const wrapper = createComponent({
            environment: fakerjs.lorem.word(),
            section: 'editcase',
        });

        expect(wrapper.findByTestId('header-content').isVisible()).toBe(true);
        expect(wrapper.findByTestId('header-content').classes()).not.toContain('tw-fixed');
        expect(wrapper.findByTestId('header-placeholder').exists()).toBe(false);
    });

    it('placeholder should adopt content height', async () => {
        const wrapper = await createComponent({
            environment: fakerjs.lorem.word(),
            section: 'test',
        });

        expect(wrapper.findByTestId('header-placeholder').attributes().style).toContain('height: 100px;');

        mockHeight.mockImplementationOnce(() => 140);
        window.dispatchEvent(new Event('resize'));
        await wrapper.vm.$nextTick();

        expect(wrapper.findByTestId('header-placeholder').attributes().style).toContain('height: 140px;');
    });

    it('should clean up on unmount', () => {
        window.removeEventListener = vi.fn().mockImplementationOnce((_, callback) => {
            callback();
        });

        const wrapper = createComponent({
            environment: fakerjs.lorem.word(),
            section: fakerjs.lorem.word(),
        });

        wrapper.destroy();

        expect(window.removeEventListener).toBeCalledWith('resize', expect.any(Function));
    });
});
