import type { PropType } from 'vue';
import type { FormData } from '../../types';
import type { ExtractPropTypes } from '../../../types/vue';
import { props as jsonFormsBaseProps } from '../JsonFormsBase/props';

const { schema, uiSchema, i18nResource, additionalErrors } = jsonFormsBaseProps;

export const props = {
    data: {
        type: Object as PropType<FormData | GenericObject>,
        required: true,
    },
    schema,
    uiSchema,
    additionalErrors,
    i18nResource,
} as const;

export type Props = ExtractPropTypes<typeof props>;
