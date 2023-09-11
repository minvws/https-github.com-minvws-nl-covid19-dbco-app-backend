import type { UiSchema } from '@dbco/ui-library';

export const uiSchema: UiSchema = {
    type: 'VerticalLayout',
    elements: [
        {
            type: 'Control',
            label: 'Foobar',
            scope: '#/properties/data/properties/foo/properties/bar',
        },
        {
            label: 'Contacts',
            type: 'Control',
            scope: '#/properties/data/properties/contacts',
            customRenderer: 'ChildFormCollection',
            options: {
                detail: {
                    type: 'HorizontalLayout',
                    elements: [
                        {
                            type: 'Control',
                            scope: '#/properties/data/properties/person/properties/fullName',
                        },
                        {
                            type: 'Control',
                            customRenderer: 'FormLink',
                            label: 'Edit',
                            scope: '#/properties/links/properties/editModal',
                        },
                    ],
                },
            },
        },
    ],
};

export const contactUiSchema: UiSchema = {
    type: 'VerticalLayout',
    elements: [
        {
            type: 'Control',
            label: 'Full name',
            scope: '#/properties/data/properties/person/properties/fullName',
        },
        {
            type: 'Control',
            label: 'Date of birth',
            scope: '#/properties/data/properties/person/properties/dob',
        },
    ],
};
