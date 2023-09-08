import { caseApi, contextApi, messageApi, taskApi } from '@dbco/portal-api';
import { transformFragmentsValidationResult, update } from '../components/form/ts/formRequest';
import { getPath, setPath } from '../utils/object';
import { StoreType } from './storeType';

import { SharedMutations } from './mutations';

export enum SharedActions {
    CHANGE = 'CHANGE',
    CLEAR = 'CLEAR',
    FILL = 'FILL',
    LOAD = 'LOAD',
    LOAD_MESSAGES = 'LOAD_MESSAGES',
    SET_MESSAGES = 'SET_MESSAGES',
    UPDATE_FORM_ERRORS = 'UPDATE_FORM_ERRORS',
    UPDATE_FORM_VALUE = 'UPDATE_FORM_VALUE',
    UPDATE_FRAGMENTS = 'UPDATE_FRAGMENTS',
}

export const sharedActions = (storeType: StoreType) => ({
    [SharedActions.CHANGE]: change,
    [SharedActions.CLEAR]: clear,
    [SharedActions.LOAD]: load(storeType),
    [SharedActions.LOAD_MESSAGES]: loadMessages(storeType),
    [SharedActions.FILL]: fill,
    [SharedActions.UPDATE_FRAGMENTS]: updateFragments,
    [SharedActions.UPDATE_FORM_VALUE]: updateFormValue(storeType),
});

export const change = ({ commit }: any, payload: any) => {
    commit(SharedMutations.CHANGE, payload);
};

export const clear = ({ commit }: any) => {
    commit(SharedMutations.CLEAR);
};

export const load =
    (type: string) =>
    async ({ dispatch }: any, uuid: string) => {
        const data = {
            errors: {},
            fragments: {},
            meta: {},
            computedData: {},
        };

        switch (type) {
            case StoreType.INDEX: {
                const [res, resMeta] = await Promise.all([caseApi.getFragments(uuid), caseApi.getMeta(uuid)]);
                data.errors = transformFragmentsValidationResult(res.validationResult);
                data.fragments = res.data;
                data.meta = resMeta.case;
                if (res.computedData) data.computedData = res.computedData;
                break;
            }
            case StoreType.CONTEXT: {
                const res = await contextApi.getFragments(uuid);
                data.errors = transformFragmentsValidationResult(res.validationResult);
                data.fragments = res.data;
                break;
            }
            case StoreType.TASK: {
                const res = await taskApi.getFragments(uuid);
                data.errors = transformFragmentsValidationResult(res.validationResult);
                data.fragments = res.data;
                break;
            }
            default:
                console.error(`ACTIONS.TS ${type}: Store action FILL unknown store type`);
        }

        dispatch(SharedMutations.FILL, data);
    };

export const fill = ({ commit }: any, payload: any) => {
    commit(SharedMutations.FILL, payload);
};

export const updateFragments = ({ commit }: any, payload: any) => {
    commit(SharedMutations.UPDATE_FRAGMENTS, payload);
};

export const loadMessages =
    (type: string) =>
    async ({ commit, state }: any) => {
        if (type !== StoreType.INDEX) {
            throw new Error(`ACTIONS.TS ${type}: This store type doesn't support messages`);
        }
        const { messages } = (await messageApi.getMessages(state.uuid)) || {}; // supresses: [Vue warn]: Error in v-on handler (Promise/async): "TypeError: Cannot destructure property 'messages' of '(intermediate value)' as it is undefined."
        commit(SharedMutations.SET_MESSAGES, messages);
    };

/* eslint-disable no-console */
export const updateFormValue =
    (type: string) =>
    async ({ commit, state, getters }: any, payload: any) => {
        // Find differences between payload and state
        const diff = Object.entries(payload).reduce((acc, [key, value]) => {
            if (JSON.stringify(payload[key]) === JSON.stringify(state.fragments[key])) return acc;
            return { ...acc, [key]: value };
        }, {});

        // If there is no difference, do not post
        if (!Object.keys(diff).length) {
            console.error(`ACTIONS.TS ${type}: No diff, not updating`);
            return;
        }

        // Presume the user has fixed the errors and clear them
        // We will only clear errors of fragments in this payload
        const errDiff = Object.entries(diff).reduce((acc, [key]) => {
            return { ...acc, [key]: null };
        }, {});
        commit(SharedMutations.UPDATE_FORM_ERRORS, errDiff);

        // Optimistically save changes
        // This prevents fields from jumping when a different handled PUT-request is updating the store
        commit(SharedMutations.UPDATE_FRAGMENTS, diff);

        const { data, isDuplicated, errors, computedData } = await update({
            type: getters.type,
            id: state.uuid,
            values: diff,
        });
        const hasErrors = Object.keys(errors).length !== 0;

        // If there is another request in the pipeline, don't handle this response
        if (isDuplicated) {
            console.error(`ACTIONS.TS ${type}: Duplicated request, ignoring`);
            return;
        }

        if (computedData?.calendarData) {
            commit(SharedMutations.CHANGE, {
                path: 'calendarData',
                values: computedData.calendarData.map(function (item: AnyObject) {
                    item.startDate = new Date(item.startDate);
                    item.endDate = new Date(item.endDate);
                    return item;
                }),
            });
            commit(SharedMutations.CHANGE, {
                path: 'calendarViews',
                values: computedData.calendarViews,
            });
        }

        // If there is a validation error on a field, keep its value
        if (data && hasErrors) {
            console.group(`ACTIONS.TS ${type}: Data + errors returned, persisting old values`);
            // formErrors contains for example: { "contact": { "errors": { "phone": "{\"warning\":[\"Telefoonnummer moet een geldig telefoonnummer zijn.\"]}" }}}
            Object.entries(errors).forEach(([fragment, fragmentValidation]) => {
                if (!fragmentValidation?.errors) return;
                Object.keys(fragmentValidation.errors).forEach((field) => {
                    const feValue = getPath(field, state.fragments[fragment]);

                    console.log(
                        `${fragment}.${field} » Set to "${feValue}" (FE) instead of "${getPath(
                            field,
                            data[fragment]
                        )}" (BE)`
                    );

                    setPath(field, data[fragment], feValue);
                });
            });

            console.groupEnd();
        }

        // If fragment data is in the response, commit it to the store
        if (data) {
            console.log(`ACTIONS.TS ${type}: Data returned, committing » UPDATE_FRAGMENTS`);
            commit(SharedMutations.UPDATE_FRAGMENTS, data);
        }

        // If errors have been returned, commit them to the store
        if (hasErrors) {
            console.log(`ACTIONS.TS ${type}: Errors returned, committing » UPDATE_FORM_ERRORS`);
            commit(SharedMutations.UPDATE_FORM_ERRORS, errors);
        }
    };
/* eslint-enable no-console */
