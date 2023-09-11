declare module '@braid/vue-formulate-i18n';

declare module '@braid/vue-formulate' {
    import type { PluginFunction, PluginObject } from 'vue';

    interface FormulateErrors {
        inputErrors: Record<string, string[]>;
        formErrors: string[];
    }

    // @see: https://vueformulate.com/guide/validation/#custom-validation-rules
    export type ValidationRuleContext<T, FormType = unknown, GroupType = unknown> = {
        name: string;
        value: T;
        getFormValues: () => FormType;
        getGroupValues: () => GroupType;
    };

    export interface Formulate {
        handle: (
            err: {
                inputErrors: Record<string, string[]>;
                formErrors: string[];
            },
            formName: string,
            skip?: boolean
        ) => void | typeof Error;
        reset: <V>(formName: string, initialValue?: V) => void;
        resetValidation: (formName: string) => void;
        setValues: <V>(formName: string, values: V) => void;
        submit: (formName: string) => void;
    }

    export interface Context {
        addLabel: any;
        attributes: any;
        blurHandler: any;
        classification: any;
        disableErrors: any;
        errors: any;
        hasValue: any;
        hasLabel: any;
        hasValidationErrors: any;
        help: any;
        helpPosition: any;
        getValidationErrors: any;
        id: any;
        isValid: any;
        imageBehavior: any;
        isSubField: any;
        label: any;
        labelPosition: any;
        limit: any;
        minimum: any;
        model: any;
        name: any;
        options: any;
        performValidation: any;
        preventWindowDrops: any;
        removeLabel: any;
        repeatable: any;
        rootEmit: any;
        rules: any;
        setErrors: any;
        showValidationErrors: any;
        type: any;
        uploadBehavior: any;
        uploader: any;
        uploadUrl: any;
        validationErrors: any;
        value: any;
        visibleValidationErrors: any;
    }

    export interface ValidationEvent {
        name: string;
        errors: string[];
        hasErrors: boolean;
    }

    declare const _default: PluginObject<uknown> | PluginFunction<unknown>;

    export default _default;
}
