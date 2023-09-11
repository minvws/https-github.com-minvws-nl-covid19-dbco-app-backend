import Alert from './Alert.vue';
import type { JsonFormsControlTestConfig } from '../../test';
import { createJsonFormsControlTest } from '../../test';
import type { AlertElement, UiSchema } from '../../types';
import { faker } from '@faker-js/faker';

function createComponent(config: Partial<JsonFormsControlTestConfig>, uiSchema: Partial<AlertElement>) {
    return createJsonFormsControlTest({
        control: Alert,
        useFilteredControls: false,
        data: {},
        schema: {},
        uiSchema: {
            type: 'Alert',
            ...uiSchema,
        } as UiSchema,
        ...config,
    });
}

describe('Alert.vue', () => {
    it('should render the alert', () => {
        const label = faker.lorem.sentence();
        const description = faker.lorem.sentence();
        const formWrapper = createComponent({}, { label, description });
        expect(formWrapper.text()).includes(label);
        expect(formWrapper.text()).includes(description);
    });

    it('should render the alert using an i18n resource', () => {
        const label = faker.lorem.sentence();
        const description = faker.lorem.sentence();
        const formWrapper = createComponent(
            {
                i18nResource: {
                    foo: {
                        label,
                        description,
                    },
                },
            },
            { i18n: 'foo' }
        );
        expect(formWrapper.text()).includes(label);
        expect(formWrapper.text()).includes(description);
    });
});
