import type { UiSchema } from '@dbco/ui-library';
import { schema } from './schema';

export const uiSchema: UiSchema = {
    type: 'VerticalLayout',
    elements: Object.keys(schema.properties!.foo!.properties!).map((key) => ({
        type: 'Control',
        scope: `#/properties/data/properties/foo/properties/${key}`,
    })),
};
