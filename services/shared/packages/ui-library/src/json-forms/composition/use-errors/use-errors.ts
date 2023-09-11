import type { Ref } from 'vue';
import { inject, computed } from 'vue';
import type { ControlBindings } from '../../types';
import type { JsonFormsSubStates, UISchemaElement } from '@jsonforms/core';
import { getErrorAt, getErrorTranslator, getTranslator } from '@jsonforms/core';
import { isString } from 'lodash';

export const useErrors = <T extends ControlBindings>(control: Ref<T>) => {
    const jsonforms = inject<JsonFormsSubStates>('jsonforms');
    if (!jsonforms) throw new Error('jsonforms is not defined');
    const translator = getTranslator()({ jsonforms });
    const errorTranslator = getErrorTranslator()({ jsonforms });

    return computed<string[]>(() => {
        const errors = getErrorAt(control.value.path, control.value.rootSchema)({ jsonforms });
        return errors
            .map((error) => {
                return errorTranslator(error, translator, control.value.uischema as unknown as UISchemaElement);
            })
            .filter(isString);
    });
};
