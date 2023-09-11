import { cloneDeep, isEqual } from 'lodash';
import type { Ref } from 'vue';
import { ref } from 'vue';
import type { FormChangeEvent, UiSchema } from '../../types';
import type { JsonFormsEditorChangeEvent } from '../../tools/JsonFormsEditor/types';
import type { JsonFormsControlStoryProps } from '../story-props';

/**
 * Provide easy json forms state management for stories with the editor.
 */
export function useJsonFormsStoryState(props: JsonFormsControlStoryProps) {
    const data = ref(cloneDeep(props.data));
    const schema = ref(cloneDeep(props.schema));
    const uiSchema = ref(cloneDeep(props.uiSchema) as unknown) as Ref<UiSchema>;
    const additionalErrors = ref(cloneDeep(props.additionalErrors));

    const handleFormChange = (event: FormChangeEvent) => {
        const { data: newData } = event;
        if (!isEqual(data.value, newData)) {
            data.value = newData;
        }
    };

    const handleEditorChange = ({
        schema: newSchema,
        data: newData,
        uiSchema: newUiSchema,
        additionalErrors: newAdditionalErrors,
    }: JsonFormsEditorChangeEvent) => {
        if (newSchema) schema.value = newSchema;
        if (newData !== undefined && !isEqual(data.value, newData)) data.value = newData;
        if (newUiSchema) uiSchema.value = newUiSchema as UiSchema;
        if (newAdditionalErrors) additionalErrors.value = newAdditionalErrors;
    };

    return {
        data,
        schema,
        uiSchema,
        additionalErrors,
        handleEditorChange,
        handleFormChange,
    };
}
