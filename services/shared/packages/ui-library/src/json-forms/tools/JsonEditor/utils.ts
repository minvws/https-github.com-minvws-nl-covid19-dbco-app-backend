import { isPlainObject } from 'lodash';
import type { Content } from 'vanilla-jsoneditor';
import { isJSONContent } from 'vanilla-jsoneditor';

export function safeParse(json: string) {
    try {
        return JSON.parse(json);
    } catch (error) {
        /* ignore */
    }
}

export function getPlainObjectValue(content: Content): GenericObject | null {
    const value = isJSONContent(content) ? content.json : safeParse(content.text);
    return isPlainObject(value) ? value : null;
}

export function jsonEditorContentToValue(content: Content): any {
    if (isJSONContent(content)) return content.json;
    return safeParse(content.text);
}
