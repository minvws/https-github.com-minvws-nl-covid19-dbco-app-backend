import type { ResourceLanguage } from 'i18next';
import type { PropType } from 'vue';
import type { JsonSchema, UiSchema } from '../../types';
import type { ExtractPropTypes } from '../../../types/vue';
import { JsonForms as JsonFormsVue2 } from '@jsonforms/vue2';
import { cells } from './cells';
import { renderers } from './renderers';

const { additionalErrors, cells: cellsProp, renderers: renderersProp } = JsonFormsVue2.props;

const defaultCells = Object.freeze(cells); // freeze renderers for performance gains
const defaultRenderers = Object.freeze(renderers); // freeze renderers for performance gains

export const props = {
    data: {
        type: Object as PropType<GenericObject>,
        required: true,
    },
    schema: {
        type: Object as PropType<JsonSchema>,
        required: true,
    },
    uiSchema: {
        type: [Object, Array] as PropType<UiSchema>,
        required: true,
    },
    cells: { ...cellsProp, required: false, default: () => defaultCells },
    renderers: { ...renderersProp, required: false, default: () => defaultRenderers },
    i18nResource: { type: Object as PropType<ResourceLanguage>, required: false },
    additionalErrors: {
        ...additionalErrors,
        default: () => [],
    },
} as const;

export type Props = ExtractPropTypes<typeof props>;
