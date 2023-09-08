import type {
    FormRootData as OpenApiFormRootData,
    FormData as OpenApiFormData,
    FormCollectionData as OpenApiFormCollectionData,
    FormRequestConfig,
    FormMetaData,
} from '@dbco/portal-open-api';

export { FormRequestConfig, FormMetaData };

export type FormRootData = OpenApiFormRootData & {
    [key: string]: unknown;
};

export interface FormData extends OpenApiFormData {
    [key: string]: unknown;
}

export interface FormCollectionData extends OpenApiFormCollectionData {
    items: FormData[];
}

export type FormRequestData<T extends FormData = FormData> = Omit<T, keyof FormMetaData>;

export type FormActionHandler<T extends FormData = FormData> = {
    create: (config: FormRequestConfig, data: FormRequestData<T>) => Promise<T>;
    read: (config: FormRequestConfig) => Promise<T>;
    update: (config: FormRequestConfig, data: FormRequestData<T>) => Promise<T>;
    delete: (config: FormRequestConfig, data: FormRequestData<T>) => Promise<void>;
};
