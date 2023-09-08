import type { ChildFormChangeEvent, FormChangeEvent, FormData, JsonSchemaType } from '../../types';

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function isFormData(data: any | FormData): data is FormData {
    return !!data && !!data.$links;
}

export function isSchemaType<T extends JsonSchemaType[] | JsonSchemaType>(
    value: string | string[] | undefined,
    types: T
): value is T extends JsonSchemaType[] ? T[number] : T {
    if (value === undefined) return false;
    if (!Array.isArray(types)) return value === types;
    return types.includes(value as JsonSchemaType);
}

export function isChildFormChangeEvent(event: FormChangeEvent | ChildFormChangeEvent): event is ChildFormChangeEvent {
    return !!(event as ChildFormChangeEvent).path;
}
