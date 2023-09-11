import { mount } from '@vue/test-utils';
import FormButtonToggleGroup from './FormButtonToggleGroup.vue';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const createComponent = setupTest(
    // TSC somehow does not understand that this is a string
    // eslint-disable-next-line @typescript-eslint/no-inferrable-types
    (localVue: VueConstructor, props?: AnyObject, slotTemplate: string = '', rootModel = {}) => {
        return mount(FormButtonToggleGroup, {
            localVue,
            propsData: {
                buttonText: '',
                context: {},
                childrenSchema: [],
                title: '',
                ...props,
            },
            stubs: {
                FormulateFormWrapper: true,
                LabelComponentStub: props?.labelComponent ?? true,
                ButtonComponentStub: props?.buttonComponent ?? true,
            },
            slots: {
                default: slotTemplate,
            },
            provide: {
                rootModel: () => rootModel,
            },
        });
    }
);

describe('FormButtonToggleGroup.vue', () => {
    it('should display the title text on the component', () => {
        const props = {
            title: 'testTitle',
        };

        const wrapper = createComponent(props);

        expect(wrapper.find('h3').text()).toBe(props.title);
    });

    it('should render labelComponent', () => {
        const props = {
            labelComponent: { template: '<span data-testid="label-component">label text</span>' },
        };

        const wrapper = createComponent(props);

        expect(wrapper.findByTestId('label-component').text()).toEqual('label text');
    });

    it('should render buttonComponent', () => {
        const props = {
            buttonComponent: { template: '<span data-testid="button-component">button text</span>' },
        };

        const wrapper = createComponent(props);

        expect(wrapper.findByTestId('button-component').text()).toEqual('button text');
    });

    it('should expand when the button is clicked', async () => {
        const props = {
            childrenSchema: [
                {
                    component: 'div',
                    class: 'child-schema-stub',
                    children: 'child schema',
                },
            ],
        };

        const wrapper = createComponent(props, '<div data-testid="children-slot">children slot</div>');

        await wrapper.find('button').trigger('click');

        expect(wrapper.findByTestId('children-slot').isVisible()).toBe(true);
        expect(wrapper.findByTestId('children-slot').text()).toEqual('children slot');
    });

    it('should render expanded when children schema fields contain data', () => {
        const props = {
            childrenSchema: [
                {
                    name: 'fragment.field',
                },
            ],
        };

        const rootModel = {
            'fragment.field': 'some value',
        };

        const wrapper = createComponent(props, '<div data-testid="children-slot">children slot</div>', rootModel);

        expect(wrapper.findByTestId('children-slot').isVisible()).toBe(true);
        expect(wrapper.findByTestId('children-slot').text()).toEqual('children slot');
    });

    it('should render closed when children schema fields are empty', () => {
        const props = {
            childrenSchema: [
                {
                    name: 'fragment.field',
                },
                {
                    name: 'fragment.arrayfield',
                },
            ],
        };

        const rootModel = {
            'fragment.field': null,
            'fragment.arrayfield': [],
        };

        const wrapper = createComponent(props, '<div data-testid="children-slot">children slot</div>', rootModel);

        expect(wrapper.findByTestId('children-slot').isVisible()).toBe(false);
    });

    it('should render closed when children schema fields are not in the rootModel', () => {
        const props = {
            childrenSchema: [
                {
                    name: 'fragment.field',
                },
            ],
        };

        const rootModel = {
            'fragment.someotherfield': 'some value',
        };

        const wrapper = createComponent(props, '<div data-testid="children-slot">children slot</div>', rootModel);

        expect(wrapper.findByTestId('children-slot').isVisible()).toBe(false);
    });

    it('should disable "button-show-group" button when disabled prop is true', () => {
        const props = {
            disabled: true,
            childrenSchema: [
                {
                    name: 'fragment.field',
                },
            ],
        };

        const rootModel = {
            'fragment.someotherfield': 'some value',
        };

        const wrapper = createComponent(props, '<div data-testid="children-slot">children slot</div>', rootModel);

        expect(wrapper.findByTestId('button-show-group').attributes().disabled).toBe('disabled');
    });

    it('should not disable "button-show-group" button when disabled prop is false', () => {
        const props = {
            disabled: false,
            childrenSchema: [
                {
                    name: 'fragment.field',
                },
            ],
        };

        const rootModel = {
            'fragment.someotherfield': 'some value',
        };

        const wrapper = createComponent(props, '<div data-testid="children-slot">children slot</div>', rootModel);

        expect(wrapper.findByTestId('button-show-group').attributes().disabled).toBe(undefined);
    });
});
