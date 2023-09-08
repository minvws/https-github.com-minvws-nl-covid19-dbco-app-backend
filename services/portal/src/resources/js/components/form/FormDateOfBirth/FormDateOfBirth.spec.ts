import { mount } from '@vue/test-utils';
import FormDateOfBirth from './FormDateOfBirth.vue';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

beforeAll(() => {
    vi.useFakeTimers();
    vi.setSystemTime(new Date(2021, 0, 1));
});

const createComponent = setupTest((localVue: VueConstructor, props?: object, data?: object) => {
    return mount(FormDateOfBirth, {
        localVue,
        propsData: props,
        data: () => data,
    });
});

describe('FormDateOfBirth.vue', () => {
    const ageElement = 'div[data-testid="age"]';
    const inputTextbox = 'input[type="text"]';

    it('should load the component', () => {
        // ARRANGE
        const props = {
            context: {
                model: '2000-01-01',
                attributes: {
                    displayAge: 21,
                },
            },
        };

        const data = {
            value: '01-01-2000',
        };

        const wrapper = createComponent(props, data);

        // ASSERT
        expect(wrapper.find('div').exists()).toBe(true);
    });

    it('should format the input onBlur', async () => {
        // ARRANGE
        const props = {
            context: {
                model: '2001-04-20',
                attributes: {
                    displayAge: true,
                },
            },
        };

        const data = {
            value: '20-04-2001',
        };

        const wrapper = createComponent(props, data);
        expect(wrapper.find(ageElement).text()).toBe(`19 jaar`);

        // ACT
        await wrapper.find(inputTextbox).setValue('28-06-1991');
        await wrapper.find(inputTextbox).trigger('blur');

        // ASSERT
        expect(wrapper.find(ageElement).text()).toBe(`29 jaar`);
    });

    it('should not have an age element if displayAge is false', () => {
        // ARRANGE
        const props = {
            context: {
                model: '2001-01-01',
                attributes: {
                    displayAge: false,
                },
            },
        };

        const data = {
            value: '01-01-2001',
        };

        const wrapper = createComponent(props, data);
        const ageElement = 'div[data-testid="age"]';

        // ASSERT
        expect(wrapper.find(ageElement).exists()).toBe(false);
    });

    it('should not have an age element if context.model is false', () => {
        // ARRANGE
        const props = {
            context: {
                model: false,
                attributes: {
                    displayAge: true,
                },
            },
        };

        const data = {
            value: '01-01-2001',
        };

        const wrapper = createComponent(props, data);

        const ageElement = 'div[data-testid="age"]';

        // ASSERT
        expect(wrapper.find(ageElement).exists()).toBe(false);
    });

    it('should not have an age element if age is above 200', () => {
        // ARRANGE
        const props = {
            context: {
                model: '1800-01-01',
                attributes: {
                    displayAge: true,
                },
            },
        };

        const data = {
            value: '01-01-2001',
        };

        const wrapper = createComponent(props, data);

        const ageElement = 'div[data-testid="age"]';

        // ASSERT
        expect(wrapper.find(ageElement).exists()).toBe(false);
    });

    it('should set context.model to null if the new value (newVal) is undefined', async () => {
        // ARRANGE
        const props = {
            context: {
                model: '2001-01-01',
                attributes: {
                    displayAge: true,
                },
            },
        };

        const data = {
            value: '01-01-2001',
        };

        const wrapper = createComponent(props, data);

        const ageElement = 'div[data-testid="age"]';
        const inputTextbox = 'input[type="text"]';

        await expect(wrapper.find(ageElement).text()).toBe(`20 jaar`);

        // ACT
        await wrapper.find(inputTextbox).setValue(undefined);
        await wrapper.find(inputTextbox).trigger('blur');

        // ASSERT
        await expect(wrapper.vm.$props.context.model).toBe(null);
    });

    it('should set context.model to null if the new value (newVal) is under 6 chars', async () => {
        // ARRANGE
        const props = {
            context: {
                model: '2001-01-01',
                attributes: {
                    displayAge: true,
                },
            },
        };

        const data = {
            value: '01-01-2001',
        };

        const wrapper = createComponent(props, data);

        const ageElement = 'div[data-testid="age"]';
        const inputTextbox = 'input[type="text"]';

        await expect(wrapper.find(ageElement).text()).toBe(`20 jaar`);

        // ACT
        await wrapper.find(inputTextbox).setValue('12345');
        await wrapper.find(inputTextbox).trigger('blur');

        // ASSERT
        await expect(wrapper.vm.$props.context.model).toBe(null);
    });

    it('should set context.model to null if the new value (newVal) is not a valid date', async () => {
        // ARRANGE
        const props = {
            context: {
                model: '2001-01-01',
                attributes: {
                    displayAge: true,
                },
            },
        };

        const data = {
            value: '01-01-2001',
        };

        const wrapper = createComponent(props, data);

        const ageElement = 'div[data-testid="age"]';
        const inputTextbox = 'input[type="text"]';

        await expect(wrapper.find(ageElement).text()).toBe(`20 jaar`);

        // ACT
        await wrapper.find(inputTextbox).setValue('99-99-9999');
        await wrapper.find(inputTextbox).trigger('blur');

        // ASSERT
        await expect(wrapper.vm.$props.context.model).toBe(null);
    });
});
