import VerticalLayout from './VerticalLayout.vue';
import { VStack } from '../../../components';
import { createJsonFormsControlTest } from '../../test';

function createComponent() {
    return createJsonFormsControlTest({
        control: VerticalLayout,
        useFilteredControls: false,
        data: {},
        schema: {
            type: 'object',
            properties: {
                one: { type: 'string' },
                two: { type: 'string' },
                three: { type: 'string' },
            },
        },
        uiSchema: {
            type: 'VerticalLayout',
            elements: [
                {
                    type: 'Control',
                    scope: '#/properties/one',
                },
                {
                    type: 'Control',
                    scope: '#/properties/two',
                },
                {
                    type: 'Control',
                    scope: '#/properties/three',
                },
            ],
        },
    });
}

describe('VerticalLayout.vue', () => {
    it('should render all the elements', () => {
        const formWrapper = createComponent();
        const labels = formWrapper.findAll('label').wrappers.map((wrapper) => wrapper.text());
        expect(formWrapper.findComponent(VStack).exists()).toBe(true);
        expect(labels).toEqual(['One', 'Two', 'Three']);
    });
});
