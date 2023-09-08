import type { PropType } from 'vue';
import type { FormActionHandler, FormRootData } from '../../types';
import type { ExtractPropTypes } from '../../../types/vue';
import { props as jsonFormsChildProps } from '../JsonFormsChild/props';

const { schema, uiSchema, additionalErrors, i18nResource } = jsonFormsChildProps;

export const props = {
    data: {
        type: Object as PropType<FormRootData | GenericObject>,
        required: true,
    },
    schema,
    uiSchema,
    additionalErrors,
    actionHandler: {
        type: Object as PropType<FormActionHandler>,
    },
    i18nResource,
} as const;

export type Props = ExtractPropTypes<typeof props>;
