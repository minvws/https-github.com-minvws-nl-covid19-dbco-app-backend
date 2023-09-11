import { mount } from '@vue/test-utils';
import { createDefaultLocalVue } from '../../test/local-vue';
import TooltipButton from './TooltipButton.vue';
import type { Position } from './TooltipButton';

function createComponent(propsData?: object) {
    return mount(TooltipButton, {
        localVue: createDefaultLocalVue(),
        propsData,
        slots: { default: 'content' },
    });
}

type StyleProps = {
    position?: Position;
};

describe('TooltipButton.vue', () => {
    it('should be able to render a default Tooltip without hover effect', () => {
        const wrapper = createComponent();

        const tooltip = wrapper.find('[role="tooltip"]');
        expect(tooltip.classes()).toStrictEqual(['tw-sr-only']);
    });

    it('should show content for screen readers', () => {
        const wrapper = createComponent();

        expect(wrapper.find('.tw-sr-only').text()).contain('content');
    });

    it('should show content in tooltip when hovering button', async () => {
        const wrapper = createComponent();

        await wrapper.find('button').trigger('mouseover');
        wrapper.vm.$nextTick(() => {
            const tooltip = wrapper.find('[role="tooltip"]');
            expect(wrapper.find('[role="tooltip"]').text()).contain('content');
            expect(tooltip.classes()).not.toContain('tw-sr-only');
        });
    });

    it.each<[StyleProps, string, string, string]>([
        [{}, 'before:tw-left-[calc(50%-12px/2)]', 'tw-translate-x-[-50%]', 'tw-left-2/4'],
        [{ position: 'right' }, 'before:tw-left-[12px]', 'tw-translate-x-[-10px]', 'tw-left-0'],
        [{ position: 'left' }, 'before:tw-right-[12px]', 'tw-translate-x-[10px]', 'tw-right-0'],
    ])(
        'when the property %j is set it should render with the correct classlist',
        async (props, expectedBeforeClass, expectedTranslationClass, expectedPositionClass) => {
            const wrapper = createComponent(props);
            await wrapper.find('button').trigger('mouseover');
            wrapper.vm.$nextTick(() => {
                const tooltip = wrapper.find('[role="tooltip"]');
                expect(tooltip.classes()).include(expectedBeforeClass);
                expect(tooltip.classes()).include(expectedTranslationClass);
                expect(tooltip.classes()).include(expectedPositionClass);
            });
        }
    );
});
