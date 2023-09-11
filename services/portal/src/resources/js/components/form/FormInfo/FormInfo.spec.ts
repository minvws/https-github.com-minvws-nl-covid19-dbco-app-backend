import SafeHtmlDirective from '@/directives/safeHtml';
import { setupTest } from '@/utils/test';
import { generateSafeHtml } from '@/utils/safeHtml';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import FormInfo from './FormInfo.vue';

// eslint-disable-next-line @typescript-eslint/no-inferrable-types
const createComponent = setupTest((localVue: VueConstructor, props: object = {}, slotTemplate: string = '') => {
    return shallowMount(FormInfo, {
        localVue,
        propsData: props,
        directives: {
            safeHtml: SafeHtmlDirective,
        },
        slots: {
            default: slotTemplate,
        },
    });
});

describe('FormInfo.vue', () => {
    it('should show icon if ShowIcon=true (default)', () => {
        const wrapper = createComponent();

        expect(wrapper.find('.svg-icon').exists()).toBe(true);
    });

    it('should NOT show icon if ShowIcon=false', () => {
        const wrapper = createComponent({
            showIcon: false,
        });

        expect(wrapper.find('.svg-icon').exists()).toBe(false);
    });

    it('should render text if string is given', () => {
        const text = 'Test';
        const wrapper = createComponent({ text });

        expect(wrapper.find<HTMLSpanElement>('span').element.innerText).toBe(text);
    });

    it('should render HTML if SafeHtml is given', () => {
        const safeHtml = generateSafeHtml('<strong>Test</strong>');
        const wrapper = createComponent({ text: safeHtml });

        expect(wrapper.find<HTMLSpanElement>('span').element.innerHTML).toBe(safeHtml.html);
    });

    it('should render slot if given', () => {
        const wrapper = createComponent(undefined, '<div class="slot-template">slot text</div>');

        expect(wrapper.find('.slot-template').exists()).toBe(true);
    });

    it('should load the component with all values provided and display an action button', () => {
        // ARRANGE
        const safeHtml = generateSafeHtml('<strong>Test</strong>');
        const props = {
            text: safeHtml,
            infoType: 'testInfoType',
            showIcon: true,
            hasAction: true,
            actionText: 'Dossier bewerken',
            actionTriggered: vi.fn(),
        };
        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find<HTMLSpanElement>('span').element.innerHTML).toBe(safeHtml.html);
        expect(wrapper.find('div[class*="info-block--testInfoType"]').exists()).toBe(true);
        expect(wrapper.find('[class*="action-button"]').exists()).toBe(true);
        expect(wrapper.find('i[class*="icon--edit"]').exists()).toBe(true);
    });
});
