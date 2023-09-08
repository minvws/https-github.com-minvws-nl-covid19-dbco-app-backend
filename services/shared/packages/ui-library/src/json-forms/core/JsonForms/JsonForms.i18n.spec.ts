import { faker } from '@faker-js/faker';
import { createJsonFormsTest } from '../../test';

describe('JsonForms.vue - i18n', () => {
    it('should use the i18nResource to supply the correct label for a field', () => {
        const schema = {
            type: 'object',
            properties: {
                one: { type: 'string', i18n: faker.lorem.word(5) },
                two: { type: 'string' },
                three: { type: 'string' },
                four: { type: 'string' },
            },
        } as const;

        const uiSchema = {
            type: 'VerticalLayout',
            elements: [
                {
                    type: 'Control',
                    scope: '#/properties/one',
                },
                {
                    type: 'Control',
                    scope: '#/properties/two',
                    i18n: faker.lorem.word(10),
                },
                {
                    type: 'Control',
                    scope: '#/properties/three',
                },
                {
                    type: 'Control',
                    scope: '#/properties/four',
                },
            ],
        } as const;

        const randomLabels = faker.lorem.words(3).split(' ');

        const i18nResource = {
            [`${schema.properties.one.i18n}.label`]: randomLabels[0],
            [`${uiSchema.elements[1].i18n}.label`]: randomLabels[1],
            [`three.label`]: randomLabels[2],
        };

        const formWrapper = createJsonFormsTest({
            data: {},
            schema,
            uiSchema,
            i18nResource,
        });

        const labels = formWrapper.findAll('label').wrappers.map((x) => x.text());

        expect(labels.length).toBe(4);
        expect(labels[0]).toBe(randomLabels[0]);
        expect(labels[1]).toBe(randomLabels[1]);
        expect(labels[2]).toBe(randomLabels[2]);
        expect(labels[3]).toBe('Four');
    });

    it('should use the default i18n resource to mark required labels and supply translated error messages', () => {
        const schema = {
            type: 'object',
            required: ['one'],
            properties: {
                one: { type: 'string' },
            },
        } as const;

        const uiSchema = {
            type: 'VerticalLayout',
            elements: [
                {
                    type: 'Control',
                    scope: '#/properties/one',
                },
            ],
        } as const;

        const formWrapper = createJsonFormsTest({
            data: {},
            schema,
            uiSchema,
        });

        const errorId = formWrapper.find('input').attributes('aria-errormessage');
        const errors = formWrapper.find(`#${errorId}`);

        const normalizedLabel = formWrapper.find('label').text().replace(/\s+/, ' ');
        expect(normalizedLabel).toBe('One (Verplicht)');
        expect(errors.text()).toBe('Dit veld is verplicht');
    });
});
