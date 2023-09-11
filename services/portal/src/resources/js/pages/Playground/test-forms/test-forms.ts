import type { JsonSchema, UiSchema } from '@dbco/ui-library';
import fooBarBaz from './foo-bar-baz';
import fooBarContacts from './foo-bar-contacts';
import typeTest from './types-test';

export type TestForm = {
    data: AnyObject;

    schema: JsonSchema;
    contactSchema?: JsonSchema;
    eventSchema?: JsonSchema;

    uiSchema: UiSchema;
    contactUiSchema?: UiSchema;
    eventUiSchema?: UiSchema;

    dataFE?: AnyObject; // Override when loading the form as a test
    schemaFE?: JsonSchema; // Override when loading the form as a test
};

export const testForms: Record<string, TestForm> = {
    ['type-test']: typeTest,
    ['foo-bar-baz']: fooBarBaz,
    ['foo-bar-contacts']: fooBarContacts,
};
