import type { Ref } from 'vue';
import { computed } from 'vue';
import type { ControlElement } from '@jsonforms/core';
import { findUISchema } from '@jsonforms/core';
import type { ArrayControlBindings } from '../../types';

export const useChildUiSchema = <T extends ArrayControlBindings>(control: Ref<T>) =>
    computed(() =>
        findUISchema(
            control.value.uischemas,
            control.value.schema,
            control.value.uischema.scope,
            control.value.path,
            undefined,
            control.value.uischema as ControlElement,
            control.value.rootSchema
        )
    );
