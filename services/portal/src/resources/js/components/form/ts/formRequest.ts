import type { AxiosError, AxiosResponse } from 'axios';
import axios from 'axios';
import { caseApi } from '@dbco/portal-api';
import formRequestQueue from './formRequestQueue';
import {
    lastUpdatedErrorInterceptor,
    lastUpdatedRequestInterceptor,
    lastUpdatedResponseInterceptor,
} from '@/interceptors/lastUpdatedInterceptor';
import { errorInterceptor } from '@/interceptors/errorInterceptor';
import type { CaseCreateUpdate } from '@dbco/portal-api/case.dto';
import type { ValidationResult, ValidationResultType } from '@dbco/portal-api/validation-result.dto';

type putAxiosResponse = AxiosResponse & {
    /**
     * Indicates if there is a request with the same identifier (updating the same fragments) in the queue (to be processed)
     */
    isDuplicated: boolean;
};

const axiosQueue = axios.create({
    timeout: 10000,
});
axiosQueue.interceptors.request.use(lastUpdatedRequestInterceptor);
axiosQueue.interceptors.response.use(undefined, errorInterceptor);
axiosQueue.interceptors.response.use(lastUpdatedResponseInterceptor, lastUpdatedErrorInterceptor);
formRequestQueue(axiosQueue, 1);

const getValidationResultType = (type: string, messages: Record<string, any>): ValidationResultType | null => {
    if (!messages) return null;
    if (!messages[type]) return null;
    const result = messages[type];

    return result;
};

/**
 * Returns all errors in JSON format grouped by field
 *
 * @param result validationResult
 * @returns JSON errors per field
 */
export const getAllErrors = (result: ValidationResult) => {
    const notice = getValidationResultType('notice', result);
    const warning = getValidationResultType('warning', result);
    const fatal = getValidationResultType('fatal', result);
    if (!notice && !warning && !fatal) return null;

    // Collect all errors by type
    const errorsByType: {
        notice: Record<string, string[]>;
        warning: Record<string, string[]>;
        fatal: Record<string, string[]>;
    } = {
        notice: notice?.errors ?? {},
        warning: warning?.errors ?? {},
        fatal: fatal?.errors ?? {},
    };
    const errorsByField: Record<string, Record<string, string[]>> = {};

    // Group the errors by field and add all types
    Object.entries(errorsByType).forEach(([type, fields]) => {
        Object.entries(fields).forEach(([key, fieldErrors]) => {
            if (!errorsByField[key]) {
                errorsByField[key] = {};
            }

            errorsByField[key][type] = fieldErrors;
        });
    });

    // Return the errors by field as json strings
    return {
        errors: Object.fromEntries(
            Object.entries(errorsByField).map(([key, nested]) => {
                return [key, JSON.stringify(nested)];
            })
        ),
    };
};

type Errors = Record<
    string,
    {
        errors: Record<string, string>;
    } | null
>;

/**
 * Returns all errors in JSON format grouped by fragment and field
 *
 * @param result validationResult by fragment
 * @returns JSON errors per field by fragment
 */
export const transformFragmentsValidationResult = (result?: { [path: string]: ValidationResult }): Errors => {
    if (!result) {
        return {};
    }

    const allErrors = Object.keys(result).reduce((acc, curr) => {
        return { ...acc, [curr]: getAllErrors(result[curr]) };
    }, {});
    return allErrors;
};

export const createCase = async (payload: Partial<CaseCreateUpdate>) => {
    try {
        const { data, validationResult } = await caseApi.createCase(payload);
        return {
            ...getAllErrors(validationResult),
            data,
        };
    } catch (error) {
        const { response } = error as AxiosError<{ validationResult: ValidationResult }>;
        return {
            ...(response ? getAllErrors(response.data.validationResult) : {}),
            data: {},
        };
    }
};

export const updateCase = async (uuid: string, payload: Partial<CaseCreateUpdate>) => {
    try {
        const { data, validationResult } = await caseApi.updatePlannerCase(uuid, payload);
        return {
            ...getAllErrors(validationResult),
            data,
        };
    } catch (error) {
        const { response } = error as AxiosError<{ validationResult: ValidationResult }>;
        return {
            ...(response ? getAllErrors(response.data.validationResult) : {}),
            data: {},
        };
    }
};

export const update = async ({
    type,
    id,
    values,
}: {
    type: string;
    id: string;
    values: null | Record<string, undefined | string | string[] | boolean>;
}): Promise<{ data?: any; isDuplicated: boolean; errors: Errors; computedData?: AnyObject }> => {
    try {
        const url = `/api/${type}/${id}/fragments`;
        const {
            isDuplicated,
            data: { data, validationResult, computedData },
        } = await axiosQueue.put<any, putAxiosResponse>(url, values);
        return {
            errors: transformFragmentsValidationResult(validationResult),
            data,
            isDuplicated,
            computedData,
        };
    } catch ({ response }) {
        const typedResponse = response as AxiosResponse<{
            validationResult: {
                [path: string]: ValidationResult;
            };
        }> & {
            isDuplicated: boolean;
        };

        return {
            errors: typedResponse ? transformFragmentsValidationResult(typedResponse.data.validationResult) : {},
            isDuplicated: typedResponse?.isDuplicated,
        };
    }
};
