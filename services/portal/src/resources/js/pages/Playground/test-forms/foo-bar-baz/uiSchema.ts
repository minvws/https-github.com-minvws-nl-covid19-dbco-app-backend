import type { UiSchema } from '@dbco/ui-library';

export const uiSchema: UiSchema = {
    type: 'VerticalLayout',
    elements: [
        {
            type: 'Control',
            label: 'Foobar',
            scope: '#/properties/data/properties/foo/properties/bar',
        },
    ],
};
